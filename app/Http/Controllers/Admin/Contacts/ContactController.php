<?php

namespace App\Http\Controllers\Admin\Contacts;

use App\Http\Controllers\Controller;
use App\Models\ContactReplyTemplate;
use App\Models\Contactus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Agent D — Contact/Messages admin module.
 *
 * index  : list page (KPIs + lazy infinite-scroll table)
 * data   : paginated JSON ({data, last_page}) for DP.infiniteScroll
 * show   : detail page + reply composer
 * reply  : store reply (+ optional email when mail.host is configured)
 * bulk   : mark_read | archive for a set of ids
 */
class ContactController extends Controller
{
    public function index()
    {
        $statuses = collect(config('admin_contacts.statuses', []))
            ->map(fn ($s) => $s['label'])
            ->toArray();
        $categories = collect(config('admin_contacts.categories', []))
            ->map(fn ($c) => $c['label'])
            ->toArray();

        $kpis = [
            'unread' => Contactus::where('status', 'new')->count(),
            'read' => Contactus::where('status', 'read')->count(),
            'replied' => Contactus::where('status', 'replied')->count(),
            'archived' => Contactus::where('status', 'archived')->count(),
            'total' => Contactus::count(),
        ];

        return view('admin.contacts.index', compact('statuses', 'categories', 'kpis'));
    }

    /** GET /admin/contacts/data — paginated JSON with filters. */
    public function data(Request $request)
    {
        $query = Contactus::query()->orderByDesc('id');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('detail', 'like', "%{$q}%");
            });
        }

        if ($category = (string) $request->input('category')) {
            $query->where('category', $category);
        }

        if ($status = (string) $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $this->normalizeDate($request->input('date_from'))) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $this->normalizeDate($request->input('date_to'))) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $page = $request->input('page', 1);
        $paginator = $query->paginate(max(5, min(100, (int) $request->input('per_page', 15))))
            ->setPath(route('admin.contacts.data'));

        $paginator->getCollection()->transform(function (Contactus $c) {
            return [
                'id' => $c->id,
                'name' => (string) $c->name,
                'email' => (string) $c->email,
                'detail' => (string) $c->detail,
                'detail_short' => e(Str::limit(strip_tags((string) $c->detail), 90)),
                'category' => (string) $c->category,
                'category_label' => $c->categoryLabel(),
                'category_color' => $c->categoryColor(),
                'status' => (string) $c->status,
                'status_label' => $c->statusLabel(),
                'status_color' => $c->statusColor(),
                'replied' => !empty($c->reply_body),
                'created_at' => optional($c->created_at)->format('M d, Y H:i'),
                'show_url' => route('admin.contacts.show', $c->id),
            ];
        });

        return response()->json($paginator->toArray());
    }

    public function show($id)
    {
        $contact = Contactus::with('replier')->findOrFail($id);

        // Viewing a "new" message marks it as read.
        if ($contact->status === 'new') {
            $contact->status = 'read';
            $contact->save();
        }

        $templates = ContactReplyTemplate::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.contacts.show', compact('contact', 'templates'));
    }

    /** POST /admin/contacts/{contact}/reply */
    public function reply(Request $request, $id)
    {
        $contact = Contactus::findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:6000',
        ]);

        $contact->reply_body = $validated['body'];
        $contact->replied_by = optional(auth()->user())->id;
        $contact->replied_at = now();
        $contact->status = 'replied';
        $contact->save();

        $emailed = false;
        if (!empty(config('mail.host'))) {
            try {
                $subject = 'Re: your message — ' . (string) \App\Models\Setting::get('business_company_name', config('app.name'));
                Mail::raw($validated['body'], function ($message) use ($contact, $subject) {
                    $message->to($contact->email)
                        ->subject($subject);
                    if (config('mail.from.address')) {
                        $message->from(config('mail.from.address'), config('mail.from.name'));
                    }
                });
                $emailed = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'emailed' => $emailed,
                'message' => $emailed ? 'Reply saved and emailed.' : 'Reply saved (email not sent — mail is not configured).',
            ]);
        }

        return redirect()
            ->route('admin.contacts.show', $contact->id)
            ->with('success', $emailed ? 'Reply saved and emailed to the customer.' : 'Reply saved (email delivery skipped — mail is not configured).');
    }

    /** POST /admin/contacts/bulk — action: mark_read|archive with ids[] */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:mark_read,archive',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:contactuses,id',
        ]);

        $status = $validated['action'] === 'mark_read' ? 'read' : 'archived';
        $count = Contactus::whereIn('id', $validated['ids'])->update(['status' => $status, 'updated_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'updated' => $count]);
        }

        return redirect()->route('admin.contacts.index')->with('success', "{$count} message(s) marked as {$status}.");
    }

    /** Accept d/m/Y or Y-m-d style inputs; returns Y-m-d or null. */
    protected function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $ts = strtotime(str_replace('/', '-', $value));

        return $ts ? date('Y-m-d', $ts) : null;
    }
}
