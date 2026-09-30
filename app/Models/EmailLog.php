<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'template_key', 'to_email', 'to_name', 'order_id', 'user_id',
        'subject', 'status', 'attempts', 'error', 'queued_at', 'sent_at',
    ];

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT   = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $log) {
            if (!$log->queued_at) {
                $log->queued_at = now();
            }
        });
    }
}
