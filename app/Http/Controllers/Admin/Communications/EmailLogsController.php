<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Http\Request;

/**
 * Admin → Communications → Email Logs: business-level delivery history
 * (recipient, type, status, attempts, error) with filters + retry.
 */
class EmailLogsController extends Controller
{
    public function index(Request $request)
    {
        $q = EmailLog::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('key'), fn ($query) => $query->where('template_key', 'like', '%' . $request->key . '%'))
            ->when($request->filled('to'), fn ($query) => $query->where('to_email', 'like', '%' . $request->to . '%'))
            ->when($request->filled('order'), fn ($query) => $query->where('order_id', (int) $request->order))
            ->orderByDesc('id');

        $logs = $q->paginate(25)->appends($request->query());

        $stats = [
            'sent'   => EmailLog::where('status', 'sent')->whereDate('created_at', today())->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'queued' => EmailLog::where('status', 'queued')->count(),
        ];

        return view('admin.email-logs.index', compact('logs', 'stats'));
    }

    public function show(EmailLog $log)
    {
        return view('admin.email-logs.show', compact('log'));
    }

    public function retry(EmailLog $log)
    {
        $ok = app(EmailService::class)->retry($log);

        return back()->with($ok ? 'success' : 'error', $ok
            ? 'Retry delivered — log #' . $log->id . ' marked sent.'
            : 'Retry failed — see the error column on log #' . $log->id . '.');
    }
}
