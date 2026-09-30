<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per blog import run (Blog Import System, 2026-09-02).
 */
class BlogImportLog extends Model
{
    protected $table = 'blog_import_logs';

    protected $fillable = [
        'batch_id', 'filename', 'file_type', 'status',
        'total_found', 'total_imported', 'total_skipped',
        'total_failed', 'error_log', 'started_at', 'completed_at',
        'imported_by',
    ];

    protected $casts = [
        'error_log'    => 'array',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];
}
