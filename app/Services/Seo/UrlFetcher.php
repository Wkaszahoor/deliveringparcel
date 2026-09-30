<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Hardened outbound HTML fetcher for the SEO analyzer.
 *
 *  - Only http(s) URLs; explicit ports other than 80/443 are rejected.
 *  - SSRF guard: rejects bare "localhost" and .local/.localhost/.test hosts,
 *    private / loopback / link-local / unspecified IP literals, and ANY DNS
 *    name that resolves to one of those (gethostbynamel + dns_get_record).
 *  - Follows redirects MANUALLY (Illuminate Http withoutRedirecting) so every
 *    hop is re-validated with the same guard — a redirect into a private
 *    network is refused.
 *  - Enforces a response size cap (config seo-shield.fetch.max_bytes);
 *    larger bodies are truncated and flagged 'truncated' => true.
 *  - Measures TTFB as the wall-clock time around the request (approximation).
 *  - Successful fetches are cached under seo-shield:url:{sha1} for
 *    config('seo-shield.cache_minutes', 360) minutes.
 */
class UrlFetcher
{
    /** Statuses treated as redirects by the manual loop. */
    protected const REDIRECT_STATUSES = [301, 302, 303, 307, 308];

    /** Only these explicit ports are allowed. */
    protected const ALLOWED_PORTS = [80, 443];

    public function fetch(string $url, bool $allowPrivate = false): array
    {
        if (!$allowPrivate) {
            $error = $this->guardUrl($url);
            if ($error !== null) {
                return ['ok' => false, 'url' => $url, 'error' => $error];
            }
        }

        $urlKey = 'seo-shield:url:' . sha1($url);

        // A previous fetch of this exact URL may already be cached (we store
        // under both the original and the final post-redirect URL).
        $cached = Cache::get($urlKey);
        if (is_array($cached) && ($cached['ok'] ?? false) === true) {
            return $cached;
        }

        $result = $this->doFetch($url, $allowPrivate);

        if (($result['ok'] ?? false) === true) {
            $minutes = max(1, (int) config('seo-shield.cache_minutes', 360));
            $ttl = now()->addMinutes($minutes);
            $finalKey = 'seo-shield:url:' . sha1((string) ($result['final_url'] ?? $url));

            $result = Cache::remember($finalKey, $ttl, fn () => $result);
            Cache::put($urlKey, $result, $ttl);
        }

        return $result;
    }

    /* ================= fetch + redirect loop ================= */

    protected function doFetch(string $url, bool $allowPrivate = false): array
    {
        $timeout = max(1, (int) config('seo-shield.fetch.timeout', 8));
        $maxRedirects = max(0, (int) config('seo-shield.fetch.max_redirects', 5));
        $maxBytes = (int) config('seo-shield.fetch.max_bytes', 5242880);
        if ($maxBytes <= 0) {
            $maxBytes = 5242880;
        }

        $currentUrl = $url;
        $hops = 0;
        $ttfb = null;
        $response = null;

        while (true) {
            // Re-validate EVERY hop (scheme, port, host, resolved IPs).
            $error = $this->guardUrl($currentUrl, $allowPrivate);
            if ($error !== null) {
                return ['ok' => false, 'url' => $url, 'final_url' => $currentUrl, 'error' => $error];
            }

            if ($hops > $maxRedirects) {
                return ['ok' => false, 'url' => $url, 'final_url' => $currentUrl, 'error' => 'Too many redirects.'];
            }
            $hops++;

            $start = microtime(true);
            try {
                $response = Http::timeout($timeout)->withoutRedirecting()->get($currentUrl);
            } catch (Throwable $e) {
                return ['ok' => false, 'url' => $url, 'final_url' => $currentUrl, 'error' => 'Request failed: ' . $e->getMessage()];
            }
            $ttfb = (int) round((microtime(true) - $start) * 1000);

            $status = $response->status();
            if (in_array($status, self::REDIRECT_STATUSES, true)) {
                $location = trim((string) $response->header('Location'));
                if ($location === '') {
                    break; // redirect without Location — treat as final response
                }
                $next = $this->absoluteUrl($currentUrl, $location);
                if ($next === null) {
                    return ['ok' => false, 'url' => $url, 'final_url' => $currentUrl, 'error' => 'Invalid redirect Location header.'];
                }
                $currentUrl = $next;
                continue;
            }

            break;
        }

        $status = $response->status();
        if ($status >= 400) {
            return ['ok' => false, 'url' => $url, 'final_url' => $currentUrl, 'status' => $status, 'error' => "HTTP {$status}"];
        }

        $contentType = strtolower(trim((string) $response->header('Content-Type')));
        if (!str_starts_with($contentType, 'text/html')) {
            return ['ok' => false, 'url' => $url, 'final_url' => $currentUrl, 'status' => $status, 'error' => 'URL did not return HTML'];
        }

        $body = (string) $response->body();
        if (strlen($body) > $maxBytes) {
            $body = substr($body, 0, $maxBytes);
            $result = $this->success($url, $currentUrl, $status, $body, $ttfb);
            $result['truncated'] = true;

            return $result;
        }

        return $this->success($url, $currentUrl, $status, $body, $ttfb);
    }

    protected function success(string $url, string $finalUrl, int $status, string $body, ?int $ttfb): array
    {
        return [
            'ok'         => true,
            'url'        => $url,
            'final_url'  => $finalUrl,
            'status'     => $status,
            'html'       => $body,
            'ttfb_ms'    => $ttfb,
            'size_bytes' => strlen($body),
        ];
    }

    /* ================= SSRF guards ================= */

    protected function guardUrl(string $url, bool $allowPrivate = false): ?string
    {
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return 'Only http(s) URLs are supported.';
        }
        if (!in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true)) {
            return 'Only http(s) URLs are supported.';
        }
        if (isset($parts['port']) && !$allowPrivate && !in_array((int) $parts['port'], self::ALLOWED_PORTS, true)) {
            return 'Only ports 80 and 443 are supported.';
        }

        if ($allowPrivate) {
            return null;
        }

        return $this->guardHost((string) $parts['host']);
    }

    protected function guardHost(string $host): ?string
    {
        $host = strtolower(trim($host, " \t\n\r\0\x0B[]"));
        if ($host === '') {
            return 'URL has no host.';
        }

        if (
            $host === 'localhost'
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.test')
        ) {
            return 'Local and private hostnames are not allowed.';
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPublicIp($host)
                ? null
                : 'Requests to private, loopback or link-local IP addresses are not allowed.';
        }

        $ips = $this->resolveIps($host);
        if ($ips === []) {
            return 'Host could not be resolved.';
        }
        foreach ($ips as $ip) {
            if (!$this->isPublicIp($ip)) {
                return 'Host resolves to a private, loopback or link-local IP address.';
            }
        }

        return null;
    }

    /** All A/AAAA records for a hostname ([] when resolution fails). */
    protected function resolveIps(string $host): array
    {
        $ips = @gethostbynamel($host);
        if (is_array($ips) && $ips !== []) {
            return array_values(array_unique($ips));
        }

        $ips = [];
        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            foreach (is_array($records) ? $records : [] as $record) {
                foreach (['ip', 'ipv6'] as $field) {
                    if (!empty($record[$field]) && filter_var($record[$field], FILTER_VALIDATE_IP)) {
                        $ips[] = $record[$field];
                    }
                }
            }
        }

        return array_values(array_unique($ips));
    }

    /**
     * True only for public routable IPs. Rejects 0.0.0.0/8, 10/8, 127/8,
     * 169.254/16 (cloud metadata 169.254.169.254 included), 172.16/12,
     * 192.168/16, ::1, ::, fe80::/10, fc00::/7 and IPv4-mapped IPv6.
     */
    protected function isPublicIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            foreach ([
                ['0.0.0.0', 8],
                ['10.0.0.0', 8],
                ['127.0.0.0', 8],
                ['169.254.0.0', 16],
                ['172.16.0.0', 12],
                ['192.168.0.0', 16],
            ] as [$subnet, $prefix]) {
                if ($this->inIpv4Cidr($ip, $subnet, $prefix)) {
                    return false;
                }
            }

            return true;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $bin = @inet_pton($ip);
            if ($bin === false || strlen($bin) !== 16) {
                return false;
            }
            if ($bin === str_repeat("\x00", 15) . "\x01") {
                return false; // ::1 loopback
            }
            if ($bin === str_repeat("\x00", 16)) {
                return false; // :: unspecified
            }
            if (ord($bin[0]) === 0xFE && (ord($bin[1]) & 0xC0) === 0x80) {
                return false; // fe80::/10 link-local
            }
            if ((ord($bin[0]) & 0xFE) === 0xFC) {
                return false; // fc00::/7 unique-local
            }
            if (substr($bin, 0, 10) === str_repeat("\x00", 10) && substr($bin, 10, 2) === "\xFF\xFF") {
                // ::ffff:a.b.c.d — apply the IPv4 rules to the embedded address
                $embedded = long2ip((int) current(unpack('N', substr($bin, 12, 4))));

                return $this->isPublicIp($embedded);
            }

            return true;
        }

        return false;
    }

    protected function inIpv4Cidr(string $ip, string $subnet, int $prefix): bool
    {
        $ipLong = ip2long($ip);
        $netLong = ip2long($subnet);
        if ($ipLong === false || $netLong === false) {
            return false;
        }
        $mask = $prefix <= 0 ? 0 : ((-1 << (32 - $prefix)) & 0xFFFFFFFF);

        return (($ipLong & $mask) === ($netLong & $mask));
    }

    /* ================= helpers ================= */

    /** Resolve a Location header (absolute, scheme-relative or relative). */
    protected function absoluteUrl(string $baseUrl, string $location): ?string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }
        if (str_starts_with($location, '//')) {
            $scheme = strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME) ?: 'http');

            return $scheme . ':' . $location;
        }

        $parts = parse_url($baseUrl);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }
        $base = strtolower($parts['scheme']) . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '');

        if ($location !== '' && $location[0] === '/') {
            return $base . $location;
        }

        $path = isset($parts['path']) ? preg_replace('#/[^/]*$#', '/', $parts['path']) : '/';

        return $base . $path . $location;
    }
}
