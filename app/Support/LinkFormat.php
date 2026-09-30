<?php

namespace App\Support;

/**
 * URL normalization for user-pasted link fields (tracking links, product
 * URLs, dispatch links). Users paste mixed formats:
 *   "http://www.google.com", "https://shop.io/x?q=1", "www.google.com",
 *   "  google.com  "
 * All are normalized to ONE canonical format:
 *   "https://www.google.com"  (scheme + host + path, trimmed, no trailing slash)
 *
 * Display side: Blade fields strip the scheme for a clean look
 * (preg_replace('#^https?://#i', '', $value)) — the round trip is stable
 * because normalize() re-adds the scheme on save.
 */
class LinkFormat
{
    public static function normalize($value): string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return '';
        }

        $v = preg_replace('/\s+/', '', $v);          // pasted values often carry spaces
        $v = preg_replace('#^https?://#i', '', $v);   // strip any pasted scheme
        $v = rtrim($v, '/');                          // cosmetic: no trailing slash

        if ($v === '' || !str_contains($v, '.')) {
            // Not a usable host/path — return as trimmed input so validation
            // upstream can still judge it.
            return trim((string) $value);
        }

        return 'https://' . $v;
    }

    /** Display variant: same URL without the scheme (for text fields). */
    public static function display($value): string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return '';
        }

        return preg_replace('#^https?://#i', '', $v);
    }
}
