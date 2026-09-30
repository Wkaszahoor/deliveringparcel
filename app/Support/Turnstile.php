<?php

namespace App\Support;

/**
 * Cloudflare Turnstile captcha helper shared by all public forms
 * (request, register, login, password reset, contact us, free quote).
 *
 * Keys default to the live pair already used by the request form; override
 * per environment with TURNSTILE_KEY / TURNSTILE_SECRET in .env via
 * config('services.turnstile.*') if ever needed.
 *
 * Two-layer switching, all admin-managed (Settings → API Integrations):
 *  - api_turnstile_enabled       master kill-switch for every captcha
 *  - turnstile_on_<page>         per-page toggle (login, register,
 *                                password_reset) — only checked when the
 *                                master switch is on
 */
class Turnstile
{
    public static function enabled(): bool
    {
        return \App\Models\Setting::getBool('api_turnstile_enabled', true);
    }

    /**
     * Master + per-page decision. null page = master switch only
     * (contact / freequote / legacy request forms keep that behavior).
     */
    public static function pageEnabled(?string $page = null): bool
    {
        if (!self::enabled()) {
            return false;
        }
        if ($page === null || $page === '') {
            return true;
        }

        return \App\Models\Setting::getBool('turnstile_on_' . $page, true);
    }

    public static function siteKey(): string
    {
        return (string) config('services.turnstile.key', '0x4AAAAAABdxU2xNnEwFvrQl');
    }

    public static function secret(): string
    {
        return (string) config('services.turnstile.secret', '0x4AAAAAABdxU-lYdgite5ftC53Vs1W_6A0');
    }

    /**
     * Verify the request's turnstile token with Cloudflare.
     * $page names the per-page toggle (login|register|password_reset);
     * null keeps the legacy master-switch-only behavior.
     * Returns null when the captcha is disabled (skip the check),
     * true when verified, false when failed/unreachable.
     */
    public static function verify($request, ?string $page = null): ?bool
    {
        if (!self::pageEnabled($page)) {
            return null;
        }

        try {
            $client = new \GuzzleHttp\Client(['connect_timeout' => 4, 'timeout' => 8]);
            $response = $client->request('POST', 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'form_params' => [
                    'secret'   => self::secret(),
                    'response' => $request->input('cf-turnstile-response'),
                    'remoteip' => $request->ip(),
                ],
            ]);

            $body = json_decode((string) $response->getBody());

            return !empty($body->success);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('turnstile verify failed: ' . $e->getMessage());
            return false;
        }
    }
}
