<?php

namespace App\Http\Controllers\Admin\Compliance;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use App\Models\SanctionsScreening;
use App\Models\RestrictedItem;
use App\Models\HsCode;
use App\Models\VatRule;
use App\Models\ConsentTemplate;
use App\Models\GdprExport;
use App\Services\SanctionsScreeningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplianceController extends Controller
{
    /* ================= KYC ================= */

    public function kycIndex()
    {
        $kpis = [
            'pending'  => KycVerification::where('status', 'pending')->count(),
            'approved' => KycVerification::where('status', 'approved')->count(),
            'rejected' => KycVerification::where('status', 'rejected')->count(),
        ];

        return view('admin.compliance.kyc.index', ['kpis' => $kpis, 'statusMap' => config('admin_compliance.kyc_statuses')]);
    }

    public function kycData(Request $request)
    {
        $query = KycVerification::query()->with('user:id,name,email');
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('doc_number', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $query->orderByRaw("FIELD(status,'pending','approved','rejected','expired')")->orderByDesc('id');
        $map = config('admin_compliance.kyc_statuses');

        return response()->json($query->paginate(dp_per_page($request))->through(function (KycVerification $k) use ($map) {
            $m = $map[$k->status] ?? ['label' => $k->status, 'color' => 'bg-secondary'];
            return [
                'id'     => $k->id,
                'user'   => optional($k->user)->name ?: '—',
                'email'  => optional($k->user)->email,
                'doc'    => config('admin_compliance.doc_types.' . $k->doc_type, $k->doc_type) . ' ' . ($k->doc_number ? '#' . $k->doc_number : ''),
                'country' => $k->doc_country ?: '—',
                'status' => $m['label'],
                'color'  => $m['color'],
                'at'     => optional($k->created_at)->format('M d, Y'),
                'urls'   => ['show' => route('admin.compliance.kyc.show', $k->id)],
            ];
        })->toArray());
    }

    public function kycShow(KycVerification $kyc)
    {
        $kyc->load('user:id,name,email');

        return view('admin.compliance.kyc.show', ['kyc' => $kyc, 'statusMap' => config('admin_compliance.kyc_statuses')]);
    }

    public function kycApprove(Request $request, KycVerification $kyc)
    {
        $kyc->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'expires_at'  => $request->filled('expires_at') ? $request->input('expires_at') : $kyc->expires_at,
        ]);

        return redirect()->route('admin.compliance.kyc.index')->with('success', 'KYC approved.');
    }

    public function kycReject(Request $request, KycVerification $kyc)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:2000']);
        $kyc->update([
            'status'           => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return redirect()->route('admin.compliance.kyc.index')->with('success', 'KYC rejected.');
    }

    /* ================= Sanctions screening ================= */

    public function sanctionsIndex()
    {
        return view('admin.compliance.sanctions.index', [
            'history' => SanctionsScreening::latest('id')->take(15)->get(),
            'result'  => session('screening_result'),
            'name'    => session('screening_name', ''),
        ]);
    }

    public function sanctionsScreen(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'country' => 'nullable|string|max:8',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $result = app(SanctionsScreeningService::class)->screen($data['name'], $data['country'] ?? null);

        SanctionsScreening::create([
            'subject_user_id' => $data['user_id'] ?? null,
            'input_name'      => $data['name'],
            'match_count'     => $result['count'],
            'result'          => $result['matches'],
        ]);

        return redirect()->route('admin.compliance.sanctions')->with([
            'screening_result' => $result,
            'screening_name'   => $data['name'],
        ]);
    }

    /* ================= Restricted items ================= */

    public function restrictedIndex()
    {
        return view('admin.compliance.restricted.index');
    }

    public function restrictedData(Request $request)
    {
        $query = RestrictedItem::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where('name', 'like', '%' . $q . '%');
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }
        $query->orderBy('name');

        return response()->json($query->paginate(dp_per_page($request))->through(function (RestrictedItem $r) {
            return [
                'id'       => $r->id,
                'name'     => $r->name,
                'category' => $r->category ?: '—',
                'severity' => ucfirst($r->severity),
                'color'    => $r->severity === 'prohibited' ? 'bg-danger' : 'bg-warning',
                'decl'     => $r->requires_declaration ? 'Yes' : 'No',
                'urls'     => [
                    'edit'   => route('admin.compliance.restricted.edit', $r->id),
                    'delete' => route('admin.compliance.restricted.destroy', $r->id),
                ],
            ];
        })->toArray());
    }

    public function restrictedCreate()
    {
        return view('admin.compliance.restricted.form', ['item' => new RestrictedItem(['severity' => 'prohibited'])]);
    }

    public function restrictedStore(Request $request)
    {
        RestrictedItem::create($this->restrictedRules($request));

        return redirect()->route('admin.compliance.restricted.index')->with('success', 'Item created.');
    }

    public function restrictedEdit(RestrictedItem $item)
    {
        return view('admin.compliance.restricted.form', ['item' => $item]);
    }

    public function restrictedUpdate(Request $request, RestrictedItem $item)
    {
        $item->update($this->restrictedRules($request));

        return redirect()->route('admin.compliance.restricted.index')->with('success', 'Item updated.');
    }

    public function restrictedDestroy(RestrictedItem $item)
    {
        $item->delete();

        return redirect()->route('admin.compliance.restricted.index')->with('success', 'Item deleted.');
    }

    private function restrictedRules(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'category'             => 'nullable|string|max:100',
            'severity'             => 'required|in:prohibited,restricted',
            'reason'               => 'nullable|string|max:2000',
            'requires_declaration' => 'nullable|boolean',
        ]);
        $data['requires_declaration'] = $request->has('requires_declaration');
        $data['category'] = $data['category'] ?? null;

        return $data;
    }

    /* ================= HS codes ================= */

    public function hsIndex()
    {
        return view('admin.compliance.hs.index');
    }

    public function hsData(Request $request)
    {
        $query = HsCode::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('code', 'like', $like)->orWhere('description', 'like', $like);
            });
        }
        $query->orderBy('code');

        return response()->json($query->paginate(dp_per_page($request))->through(function (HsCode $h) {
            return [
                'id'     => $h->id,
                'code'   => $h->code,
                'desc'   => $h->description,
                'cat'    => $h->category ?: '—',
                'duty'   => $h->duty_hint !== null ? number_format($h->duty_hint, 2) . '%' : '—',
                'urls'   => [
                    'edit'   => route('admin.compliance.hs.edit', $h->id),
                    'delete' => route('admin.compliance.hs.destroy', $h->id),
                ],
            ];
        })->toArray());
    }

    public function hsCreate()
    {
        return view('admin.compliance.hs.form', ['hs' => new HsCode()]);
    }

    public function hsStore(Request $request)
    {
        HsCode::create($this->hsRules($request));

        return redirect()->route('admin.compliance.hs.index')->with('success', 'HS code created.');
    }

    public function hsEdit(HsCode $hs)
    {
        return view('admin.compliance.hs.form', ['hs' => $hs]);
    }

    public function hsUpdate(Request $request, HsCode $hs)
    {
        $hs->update($this->hsRules($request, $hs->id));

        return redirect()->route('admin.compliance.hs.index')->with('success', 'HS code updated.');
    }

    public function hsDestroy(HsCode $hs)
    {
        $hs->delete();

        return redirect()->route('admin.compliance.hs.index')->with('success', 'HS code deleted.');
    }

    private function hsRules(Request $request, $ignoreId = null)
    {
        return $request->validate([
            'code'        => 'required|string|max:10|unique:hs_codes,code' . ($ignoreId ? ',' . $ignoreId : ''),
            'description' => 'required|string|max:500',
            'category'    => 'nullable|string|max:100',
            'duty_hint'   => 'nullable|numeric|min:0|max:99.99',
        ]) + ['category' => null, 'duty_hint' => null];
    }

    /* ================= VAT rules ================= */

    public function vatIndex()
    {
        return view('admin.compliance.vat.index', ['schemes' => config('admin_compliance.vat_schemes')]);
    }

    public function vatData(Request $request)
    {
        $query = VatRule::query();
        if ($request->filled('scheme')) {
            $query->where('scheme', $request->input('scheme'));
        }
        $query->orderBy('scheme')->orderBy('country_code');

        return response()->json($query->paginate(dp_per_page($request))->through(function (VatRule $v) {
            return [
                'id'     => $v->id,
                'scheme' => config('admin_compliance.vat_schemes.' . $v->scheme, $v->scheme),
                'country' => $v->country_code ?: '—',
                'rate'   => number_format((float) $v->rate, 2) . '%',
                'reg'    => $v->registration_number ?: '—',
                'active' => (bool) $v->is_active,
                'urls'   => [
                    'edit'   => route('admin.compliance.vat.edit', $v->id),
                    'delete' => route('admin.compliance.vat.destroy', $v->id),
                ],
            ];
        })->toArray());
    }

    public function vatCreate()
    {
        return view('admin.compliance.vat.form', ['rule' => new VatRule(['is_active' => true, 'scheme' => 'standard'])]);
    }

    public function vatStore(Request $request)
    {
        VatRule::create($this->vatRules($request));

        return redirect()->route('admin.compliance.vat.index')->with('success', 'VAT rule created.');
    }

    public function vatEdit(VatRule $rule)
    {
        return view('admin.compliance.vat.form', ['rule' => $rule]);
    }

    public function vatUpdate(Request $request, VatRule $rule)
    {
        $rule->update($this->vatRules($request));

        return redirect()->route('admin.compliance.vat.index')->with('success', 'VAT rule updated.');
    }

    public function vatDestroy(VatRule $rule)
    {
        $rule->delete();

        return redirect()->route('admin.compliance.vat.index')->with('success', 'VAT rule deleted.');
    }

    private function vatRules(Request $request)
    {
        $data = $request->validate([
            'scheme'              => 'required|in:' . implode(',', array_keys(config('admin_compliance.vat_schemes'))),
            'country_code'        => 'nullable|string|max:8',
            'rate'                => 'required|numeric|min:0|max:100',
            'registration_number' => 'nullable|string|max:50',
            'is_active'           => 'nullable|boolean',
            'notes'               => 'nullable|string|max:2000',
        ]);
        $data['is_active'] = $request->has('is_active');

        return $data;
    }

    /* ================= Consent templates ================= */

    public function consentIndex()
    {
        return view('admin.compliance.consent.index', ['triggers' => config('admin_compliance.consent_triggers')]);
    }

    public function consentData(Request $request)
    {
        $query = ConsentTemplate::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where('name', 'like', '%' . $q . '%');
        }
        $query->orderBy('name')->orderByDesc('version');

        return response()->json($query->paginate(dp_per_page($request))->through(function (ConsentTemplate $c) {
            return [
                'id'       => $c->id,
                'name'     => $c->name . ' v' . $c->version,
                'trigger'  => config('admin_compliance.consent_triggers.' . $c->trigger_type, $c->trigger_type) . ($c->trigger_value ? ' (' . $c->trigger_value . ')' : ''),
                'active'   => (bool) $c->is_active,
                'sig'      => $c->requires_signature ? 'Yes' : 'No',
                'urls'     => [
                    'edit'   => route('admin.compliance.consent.edit', $c->id),
                    'delete' => route('admin.compliance.consent.destroy', $c->id),
                ],
            ];
        })->toArray());
    }

    public function consentCreate()
    {
        return view('admin.compliance.consent.form', ['template' => new ConsentTemplate(['is_active' => true, 'trigger_type' => 'always', 'requires_signature' => true])]);
    }

    public function consentStore(Request $request)
    {
        $data = $this->consentRules($request);
        $data['version'] = ConsentTemplate::where('slug', $data['slug'])->max('version') + 1;
        ConsentTemplate::create($data);

        return redirect()->route('admin.compliance.consent.index')->with('success', 'Template created (new version).');
    }

    public function consentEdit(ConsentTemplate $template)
    {
        return view('admin.compliance.consent.form', ['template' => $template]);
    }

    public function consentUpdate(Request $request, ConsentTemplate $template)
    {
        $template->update($this->consentRules($request, $template->id));

        return redirect()->route('admin.compliance.consent.index')->with('success', 'Template updated.');
    }

    public function consentDestroy(ConsentTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.compliance.consent.index')->with('success', 'Template deleted.');
    }

    private function consentRules(Request $request, $ignoreId = null)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'slug'               => 'required|string|max:255',
            'body'               => 'required|string|max:4294900000',
            'is_active'          => 'nullable|boolean',
            'trigger_type'       => 'required|in:' . implode(',', array_keys(config('admin_compliance.consent_triggers'))),
            'trigger_value'      => 'nullable|string|max:100',
            'requires_signature' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $data['requires_signature'] = $request->has('requires_signature');
        $data['trigger_value'] = $data['trigger_value'] ?? null;

        return $data;
    }

    /* ================= GDPR export ================= */

    public function gdprIndex(Request $request)
    {
        $user = null;
        $summary = null;
        if ($email = trim((string) $request->input('email', ''))) {
            $user = DB::table('users')->where('email', $email)->first();
            if ($user) {
                $summary = [
                    'profile'            => 1,
                    'orders'             => DB::table('orders')->where('user_id', $user->id)->count(),
                    'order_products'     => DB::table('orderproducts')->whereIn('order_id', DB::table('orders')->where('user_id', $user->id)->pluck('id'))->count(),
                    'offer_orders'       => DB::table('offerorders')->whereIn('order_id', DB::table('orders')->where('user_id', $user->id)->pluck('id'))->count(),
                    'shipping_addresses' => DB::table('shipping_addresses')->where('name', $user->name)->count(),
                    'order_chats'        => DB::table('order_chats')->where('from', $user->id)->count(),
                    'notifications'      => DB::table('notifications')->where('notifiable_id', $user->id)->count(),
                    'quotes'             => DB::table('request_quotes')->where('email', $user->email)->count(),
                    'returns'            => DB::table('return_requests')->where('user_id', $user->id)->count(),
                ];
            }
        }

        return view('admin.compliance.gdpr.index', [
            'email'   => $email,
            'user'    => $user,
            'summary' => $summary,
            'exports' => GdprExport::with('user:id,name,email')->latest('id')->take(10)->get(),
        ]);
    }

    public function gdprExport(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $user = DB::table('users')->where('id', $data['user_id'])->first();
        abort_if($user === null, 404);

        $orderIds = DB::table('orders')->where('user_id', $user->id)->pluck('id');

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'user'        => (array) $user,
            'orders'      => DB::table('orders')->where('user_id', $user->id)->get()->map(fn ($r) => (array) $r)->all(),
            'order_products' => DB::table('orderproducts')->whereIn('order_id', $orderIds)->get()->map(fn ($r) => (array) $r)->all(),
            'offer_orders'   => DB::table('offerorders')->whereIn('order_id', $orderIds)->get()->map(fn ($r) => (array) $r)->all(),
            'shipping_addresses' => DB::table('shipping_addresses')->where('name', $user->name)->get()->map(fn ($r) => (array) $r)->all(), // best-effort: table has no user_id, links by name
            'order_chats'    => DB::table('order_chats')->where('from', $user->id)->get()->map(fn ($r) => (array) $r)->all(),
            'notifications'  => DB::table('notifications')->where('notifiable_id', $user->id)->get()->map(fn ($r) => (array) $r)->all(),
            'request_quotes' => DB::table('request_quotes')->where('email', $user->email)->get()->map(fn ($r) => (array) $r)->all(),
            'returns'        => DB::table('return_requests')->where('user_id', $user->id)->get()->map(fn ($r) => (array) $r)->all(),
        ];

        /* Mask credential-ish fields. */
        unset($payload['user']['password'], $payload['user']['remember_token']);

        $rows = 0;
        foreach ($payload as $section) {
            if (is_array($section)) {
                $rows += count($section);
            }
        }
        GdprExport::create(['user_id' => $user->id, 'admin_id' => auth()->id(), 'file_rows' => $rows]);

        return response()
            ->json($payload, 200, ['Content-Disposition' => 'attachment; filename="gdpr-export-user-' . $user->id . '.json"']);
    }
}
