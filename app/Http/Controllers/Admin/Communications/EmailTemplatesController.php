<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Http\Controllers\Controller;
use App\Mail\SystemEmail;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Admin → Communications → Email Templates. CRUD + enable toggle + test send
 * (delivers the rendered template to the logged-in admin's own address).
 */
class EmailTemplatesController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::query()->orderBy('category')->orderBy('key')->get();

        return view('admin.emails.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.emails.create', ['template' => new EmailTemplate(['is_enabled' => true, 'category' => 'system'])]);
    }

    public function store(Request $request)
    {
        EmailTemplate::create($this->validated($request) + ['is_enabled' => $request->has('is_enabled')]);

        return redirect()->route('admin.emails.index')->with('success', 'Email template created.');
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.emails.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $template->update($this->validated($request) + ['is_enabled' => $request->has('is_enabled')]);

        return redirect()->route('admin.emails.index')->with('success', 'Email template updated.');
    }

    public function toggle(EmailTemplate $template)
    {
        $template->update(['is_enabled' => !$template->is_enabled]);

        return back()->with('success', $template->is_enabled ? 'Template enabled.' : 'Template disabled (sender falls back to the legacy email).');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.emails.index')->with('success', 'Template deleted (sender falls back to the legacy email).');
    }

    /** Send the rendered template to the admin themself. */
    public function test(Request $request, EmailTemplate $template)
    {
        $sample = [
            'name' => 'Sample Customer', 'email' => 'customer@example.com', 'title' => 'Sample Customer',
            'number' => '+1 555 0100', 'address' => '1 Main St', 'message' => 'Sample message text',
            'cargotype' => 'General', 'country' => 'United Kingdom', 'destination' => 'Australia',
            'weight' => '2.5 kg', 'width' => '30', 'height' => '20', 'detail' => 'Sample detail',
            'order_id' => '123', 'order_ref' => 'DP-123', 'link' => route('login'), 'url' => route('login'),
        ];

        $svc = app(EmailService::class);
        $vars = [];
        foreach ($sample as $k => $v) {
            $vars[strtolower($k)] = $v;
        }
        $ref = new \ReflectionMethod($svc, 'render');
        $ref->setAccessible(true);
        $subject = $ref->invoke($svc, $template->subject, $vars);
        $body = $ref->invoke($svc, $template->body, $vars);

        try {
            Mail::to($request->user()->email)->send(new SystemEmail($subject, $body));
        } catch (\Throwable $e) {
            return back()->with('error', 'Test send failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Test email sent to ' . $request->user()->email . ' — check the inbox (and spam folder).');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'key'      => 'required|string|max:100|regex:/^[a-z0-9_]+$/i|unique:email_templates,key' . ($request->route('template') ? ',' . $request->route('template')->id : ''),
            'name'     => 'required|string|max:191',
            'category' => 'required|in:' . implode(',', array_keys(EmailTemplate::CATEGORIES)),
            'subject'  => 'required|string|max:255',
            'body'     => 'required|string|max:20000',
            'placeholder_help' => 'nullable|string|max:2000',
        ]);
    }
}
