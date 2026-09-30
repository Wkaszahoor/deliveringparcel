<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Push notification sender using Firebase Cloud Messaging HTTP v1 API.
 *
 * Requires FCM_SERVER_KEY in .env (legacy) or service-account JSON (v1).
 * For simplicity, uses the legacy HTTP API which works with a plain server key.
 * Upgrade path: switch to google/auth + v1 endpoint for project-scoped sends.
 */
class PushNotificationService
{
    private const LEGACY_URL = 'https://fcm.googleapis.com/fcm/send';

    /**
     * Send a push notification to all registered devices of a user.
     *
     * @param User   $user
     * @param string $title
     * @param string $body
     * @param array  $data      extra payload (order_id, link, etc.)
     * @return int   number of successful sends
     */
    public static function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        $tokens = $user->fcm_tokens ?? [];
        if (empty($tokens)) {
            return 0;
        }

        $serverKey = config('services.fcm.server_key', env('FCM_SERVER_KEY'));
        if (empty($serverKey)) {
            Log::warning('PushNotificationService: FCM_SERVER_KEY not configured');
            return 0;
        }

        $deviceTokens = array_column($tokens, 'token');
        $successCount = 0;

        // FCM legacy API accepts up to 1000 tokens per request
        $chunks = array_chunk($deviceTokens, 1000);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(10)->post(self::LEGACY_URL, [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_merge($data, [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ]),
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $successCount += $result['success'] ?? 0;

                    // Clean up invalid tokens
                    if (!empty($result['results'])) {
                        self::pruneInvalidTokens($user, $chunk, $result['results']);
                    }
                }
            } catch (\Exception $e) {
                Log::error('PushNotificationService send failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return $successCount;
    }

    /**
     * Send to multiple users at once.
     */
    public static function sendToUsers(array $users, string $title, string $body, array $data = []): int
    {
        $total = 0;
        foreach ($users as $user) {
            $total += self::sendToUser($user, $title, $body, $data);
        }
        return $total;
    }

    /**
     * Remove tokens that FCM reported as invalid (NotRegistered, InvalidRegistration).
     */
    private static function pruneInvalidTokens(User $user, array $sentTokens, array $results): void
    {
        $tokensToKeep = [];
        $userTokens = $user->fcm_tokens ?? [];

        foreach ($userTokens as $deviceToken) {
            $tokenValue = $deviceToken['token'] ?? '';
            $index = array_search($tokenValue, $sentTokens, true);

            if ($index === false) {
                // Not in this batch, keep it
                $tokensToKeep[] = $deviceToken;
                continue;
            }

            $result = $results[$index] ?? null;
            if (isset($result['error']) && in_array($result['error'], ['NotRegistered', 'InvalidRegistration'], true)) {
                // Token is dead — skip it (don't add to keep list)
                continue;
            }

            $tokensToKeep[] = $deviceToken;
        }

        if (count($tokensToKeep) !== count($userTokens)) {
            $user->fcm_tokens = array_values($tokensToKeep);
            $user->saveQuietly();
        }
    }
}
