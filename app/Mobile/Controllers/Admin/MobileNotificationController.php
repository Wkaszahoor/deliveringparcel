<?php

namespace App\Mobile\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mobile\Models\NotificationLog;
use App\Mobile\Models\NotificationTemplate;
use App\Mobile\Requests\SendNotificationRequest;
use App\Mobile\Services\MobileNotificationService;
use Illuminate\Http\Request;

class MobileNotificationController extends Controller
{
    public function index(Request $request)
    {
        // Flush any scheduled notifications that are due (no cron needed).
        NotificationLog::dispatchDue();

        $history   = app(MobileNotificationService::class)->getHistory(20)->withQueryString();
        $templates = NotificationTemplate::active()->orderBy('template_name')->get();

        return view('mobile-admin.notifications.index', [
            'history'   => $history,
            'templates' => $templates,
            'tab'       => $request->query('tab', 'send'),
        ]);
    }

    public function send(SendNotificationRequest $request)
    {
        $result = app(MobileNotificationService::class)->send($request->validated(), (int) auth()->id());

        $message = $result['scheduled']
            ? 'Notification scheduled.'
            : ($result['count'] > 0
                ? 'Notification delivered to ' . $result['count'] . ' user(s).'
                : 'Notification saved, but no matching recipients were found.');

        return redirect()->route('mobile.admin.notifications.index', ['tab' => 'history'])
            ->with('success', $message);
    }

    /** JSON — ajax pagination of the History tab. */
    public function history()
    {
        return response()->json([
            'data' => app(MobileNotificationService::class)->getHistory(20),
        ]);
    }

    /** JSON — template list for the ajax selectors. */
    public function templates()
    {
        return response()->json([
            'data' => NotificationTemplate::active()->orderBy('template_name')->get(),
        ]);
    }

    public function saveTemplate(Request $request)
    {
        $data = $request->validate([
            'template_name'     => 'required|string|max:100',
            'notification_type' => 'required|in:order_update,message,announcement,alert,promotional',
            'title'             => 'required|string|max:200',
            'body'              => 'required|string|max:1000',
        ]);

        NotificationTemplate::updateOrCreate(
            ['template_name' => $data['template_name']],
            $data + ['is_active' => true]
        );

        return redirect()->route('mobile.admin.notifications.index', ['tab' => 'templates'])
            ->with('success', 'Template "' . $data['template_name'] . '" saved.');
    }

    public function deleteTemplate(Request $request, int $id)
    {
        NotificationTemplate::where('id', $id)->delete();

        return redirect()->route('mobile.admin.notifications.index', ['tab' => 'templates'])
            ->with('success', 'Template deleted.');
    }
}
