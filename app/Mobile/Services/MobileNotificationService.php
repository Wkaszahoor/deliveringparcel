<?php

namespace App\Mobile\Services;

use App\Mobile\Models\NotificationLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Sends admin-broadcast notifications to mobile users.
 *
 * Delivery writes straight into the `notifications` table using the
 * SAME data shape App\Notifications\TaskNotification produces
 * ({title, greeting, order_number, description}) so the mobile app's
 * existing notification screens render these without any dependency
 * on web-side model classes.
 */
class MobileNotificationService
{
    /**
     * @param array $data validated SendNotificationRequest payload
     * @param int   $adminId
     * @return array{sent: bool, count: int, log_id: int, scheduled: bool}
     */
    public function send(array $data, int $adminId): array
    {
        $targetValue = match ($data['target_type'] ?? 'all') {
            'role'  => $data['target_role']  ?? null,
            'user'  => $data['target_user']  ?? null,
            'order' => $data['target_order'] ?? null,
            default => null,
        };

        $scheduled = ($data['schedule'] ?? 'now') === 'later';

        $log = NotificationLog::create([
            'sent_by'           => $adminId,
            'target_type'       => $data['target_type'],
            'target_value'      => $targetValue,
            'notification_type' => $data['type'],
            'title'             => $data['title'],
            'body'              => $data['body'],
            'scheduled_at'      => $scheduled ? $data['scheduled_at'] : null,
            'status'            => 'pending',
        ]);

        if ($scheduled) {
            return ['sent' => false, 'count' => 0, 'log_id' => $log->id, 'scheduled' => true];
        }

        $count = $this->deliverLog($log);

        return ['sent' => true, 'count' => $count, 'log_id' => $log->id, 'scheduled' => false];
    }

    /** Deliver one log row now; returns the recipient count. */
    public function deliverLog(NotificationLog $log): int
    {
        $count = 0;
        $now = now();

        $payload = json_encode([
            'title'        => $log->title,
            'greeting'     => 'Message from Delivering Parcel',
            'order_number' => $log->target_type === 'order' ? (string) $log->target_value : '',
            'description'  => (string) $log->body,
        ], JSON_UNESCAPED_UNICODE);

        foreach (array_chunk($log->recipientIds(), 200) as $chunk) {
            $rows = [];
            foreach ($chunk as $userId) {
                $rows[] = [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\Notifications\TaskNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id'   => $userId,
                    'data'            => $payload,
                    'read_at'         => null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }
            DB::table('notifications')->insert($rows);
            $count += count($rows);
        }

        $log->update([
            'status'          => $count > 0 ? 'sent' : 'failed',
            'sent_at'         => $now,
            'recipient_count' => $count,
        ]);

        return $count;
    }

    public function getHistory(int $perPage = 20): LengthAwarePaginator
    {
        return NotificationLog::query()->orderByDesc('id')->paginate($perPage);
    }
}
