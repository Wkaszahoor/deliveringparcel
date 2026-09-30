<?php

namespace App\Mobile\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationLog extends Model
{
    protected $table = 'mobile_notification_logs';

    protected $fillable = [
        'sent_by', 'target_type', 'target_value', 'notification_type',
        'title', 'body', 'scheduled_at', 'sent_at', 'status', 'recipient_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /** Recipient user ids for this row (raw DB — no web models). */
    public function recipientIds(): array
    {
        switch ($this->target_type) {
            case 'role':
                return DB::table('users')->where('type', $this->target_value)->pluck('id')->toArray();
            case 'user':
                return DB::table('users')->where('id', $this->target_value)->pluck('id')->toArray();
            case 'order':
                $order = DB::table('orders')
                    ->where('order_id', $this->target_value)
                    ->orWhere('id', $this->target_value)
                    ->first();

                return $order && $order->user_id
                    ? DB::table('users')->where('id', $order->user_id)->pluck('id')->toArray()
                    : [];
            default:
                return DB::table('users')->pluck('id')->toArray();
        }
    }

    /** Send every pending row whose schedule is due. */
    public static function dispatchDue(): int
    {
        $due = static::pending()
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->get();

        $sent = 0;
        foreach ($due as $log) {
            app(\App\Mobile\Services\MobileNotificationService::class)->deliverLog($log);
            $sent++;
        }

        return $sent;
    }
}
