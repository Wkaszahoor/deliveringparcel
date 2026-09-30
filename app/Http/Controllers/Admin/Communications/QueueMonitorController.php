<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Support\Facades\DB;

/**
 * Admin → Communications → Queue Monitor: pending/failed jobs, email
 * throughput, last activity. Works with the cPanel-cron worker
 * (dp:queue-drain) or the scheduler entry already in Console/Kernel.
 */
class QueueMonitorController extends Controller
{
    public function index()
    {
        $jobsTable = 'missing';
        $pending = $failed = 0;
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            $jobsTable = 'ok';
        } catch (\Throwable $e) {
        }

        $stats = [
            'queue_driver'  => config('queue.default'),
            'jobs_table'    => $jobsTable,
            'pending_jobs'  => $pending,
            'failed_jobs'   => $failed,
            'emails_sent'   => EmailLog::where('status', 'sent')->count(),
            'emails_sent_today' => EmailLog::where('status', 'sent')->whereDate('sent_at', today())->count(),
            'emails_failed' => EmailLog::where('status', 'failed')->count(),
            'emails_queued' => EmailLog::where('status', 'queued')->count(),
            'master_enabled' => EmailService::masterEnabled(),
            'queued_sending' => EmailService::useQueue(),
            'last_sent_at'  => optional(EmailLog::where('status', 'sent')->latest('sent_at')->first())->sent_at,
            'last_queued_at' => optional(EmailLog::latest('id')->first())->created_at,
        ];

        return view('admin.queue.monitor', compact('stats'));
    }
}
