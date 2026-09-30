<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Agent D — Payments admin module (read-only view over orders/offerorders,
 * config/admin_payments.php driven) + feature-detected refund action.
 *
 * Agent PM (additive extension) — NEW dynamic-payment ledger surface:
 *   indexLedger()/dataLedger()  : the authoritative `payments` table
 *   verify()                    : bank proof verification (PM-008/PM-013)
 *   refundLedger()              : gateway-aware refunds on the ledger (PM-004)
 * The legacy read-model above is untouched and keeps working.
 */
class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $statuses = config('admin_payments.statuses', []);
        $currency = (string) Setting::get('business_currency', 'USD');
        $kpis = $this->kpis();

        return view('admin.payments.index', [
            'statuses' => $statuses,
            'currency' => $currency,
            'kpis' => $kpis,
            'refundAvailable' => $this->refundAvailable(),
        ]);
    }

    /** GET /admin/payments/data — paginated joined rows with filters. */
    public function data(Request $request)
    {
        $query = $this->baseQuery();

        $this->applyFilters($request, $query);

        $page = $request->input('page', 1);
        $paginator = $query->orderByDesc('o.created_at')
            ->paginate(max(5, min(100, (int) $request->input('per_page', 15))))
            ->setPath(route('admin.payments.data'));

        $currency = (string) Setting::get('business_currency', 'USD');
        $statuses = config('admin_payments.statuses', []);
        $methods = config('admin_payments.methods', []);
        $refundAvailable = $this->refundAvailable();

        $paginator->getCollection()->transform(function ($row) use ($currency, $statuses, $methods, $refundAvailable) {
            $amount = (float) ($row->amount ?? 0);
            $status = $row->normalized_status ?: 'pending';
            $method = $row->offer_id ? 'offer' : 'direct';

            $customer = $row->user_name
                ? ((string) $row->user_name . (($row->user_email ?? '') !== '' ? ' <' . $row->user_email . '>' : ''))
                : (($row->user_email ?? '') !== '' ? (string) $row->user_email : 'Guest');

            return [
                'id' => $row->id,
                'order_ref' => (string) $row->order_id,
                'customer' => $customer,
                'amount' => number_format($amount, 2),
                'currency' => $currency,
                'method' => $methods[$method] ?? $method,
                'status' => $status,
                'status_label' => $statuses[$status]['label'] ?? ucfirst($status),
                'status_color' => $statuses[$status]['color'] ?? 'secondary',
                'created_at' => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y H:i') : '—',
                'refundable' => $refundAvailable && $status === 'paid',
                'refund_url' => route('admin.payments.refund', $row->id),
            ];
        });

        return response()->json($paginator->toArray());
    }

    /**
     * PUT /admin/payments/{order}/refund (data-dp-confirm).
     * Marks a paid order refunded via the first suitable column; performs a
     * real Stripe refund ONLY when services.stripe.secret is configured.
     */
    public function refund(Request $request, $id)
    {
        $order = DB::table('orders')->where('id', (int) $id)->first();

        if (!$order) {
            abort(404, 'Order not found.');
        }

        $status = $this->normalizeStatus($order->order_status);
        if ($status !== 'paid') {
            return response()->json(['ok' => false, 'message' => 'Only paid payments can be refunded (current status: ' . $status . ').'], 422);
        }

        // Real Stripe refund — only when a secret is configured (never hardcoded).
        $secret = trim((string) config('services.stripe.secret'));
        if ($secret !== '') {
            $chargeCol = $this->firstExistingColumn(config('admin_payments.refund.charge_column_candidates', []));
            $charge = $chargeCol ? trim((string) ($order->{$chargeCol} ?? '')) : '';

            if ($charge !== '') {
                try {
                    $response = Http::withToken($secret)
                        ->asForm()
                        ->post('https://api.stripe.com/v1/refunds', ['charge' => $charge]);

                    if ($response->failed()) {
                        return response()->json([
                            'ok' => false,
                            'message' => 'Stripe refund failed (' . $response->status() . '). Payment was NOT marked refunded.',
                        ], 422);
                    }
                } catch (\Throwable $e) {
                    report($e);

                    return response()->json([
                        'ok' => false,
                        'message' => 'Stripe refund request failed: ' . $e->getMessage(),
                    ], 422);
                }
            }
        }

        // Mark refunded — use the first suitable column; order_status is the fallback.
        $update = ['updated_at' => now()];
        $refundCol = $this->firstExistingColumn(config('admin_payments.refund.status_column_candidates', []));

        if ($refundCol === 'refunded_at') {
            $update[$refundCol] = now();
        } elseif ($refundCol !== null) {
            $update[$refundCol] = 'refunded';
        } else {
            $update['order_status'] = 'refunded';
        }

        DB::table('orders')->where('id', $order->id)->update($update);

        return response()->json(['ok' => true, 'message' => 'Payment marked as refunded.']);
    }

    /* ------------------------------------------------------------------
     * query building
     * ------------------------------------------------------------------ */

    protected function baseQuery()
    {
        $amount = $this->amountSql();
        $statusSql = $this->statusSql();

        return DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('offerorders as of', 'of.order_id', '=', 'o.id')
            ->selectRaw("o.id, o.order_id, u.name as user_name, u.email as user_email, {$amount} as amount, {$statusSql} as normalized_status, of.id as offer_id, o.created_at")
            ->groupBy('o.id', 'o.order_id', 'u.name', 'u.email', 'o.total', 'o.product_totalprice', 'o.order_status', 'of.id', 'o.created_at');
    }

    protected function applyFilters(Request $request, $query)
    {
        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('o.order_id', 'like', "%{$q}%")
                    ->orWhere('u.name', 'like', "%{$q}%")
                    ->orWhere('u.email', 'like', "%{$q}%");
            });
        }

        if (($status = (string) $request->input('status')) && $status !== '') {
            $query->havingRaw($this->statusSql() . ' = ?', [$status]);
        }

        if ($from = $this->normalizeDate($request->input('date_from'))) {
            $query->where('o.created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $this->normalizeDate($request->input('date_to'))) {
            $query->where('o.created_at', '<=', $to . ' 23:59:59');
        }

        return $query;
    }

    protected function kpis(): array
    {
        $amount = $this->amountSql();
        $statusSql = $this->statusSql();
        $monthStart = now()->startOfMonth()->toDateString() . ' 00:00:00';

        $collected = DB::table('orders as o')
            ->selectRaw("COALESCE(SUM({$amount}),0) as total")
            ->whereRaw("{$statusSql} = 'paid'")
            ->where('o.created_at', '>=', $monthStart)
            ->value('total');

        $pending = DB::table('orders as o')
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(' . $amount . '),0) as total')
            ->whereRaw("{$statusSql} = 'pending'")
            ->first();

        $refunded = DB::table('orders as o')
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(' . $amount . '),0) as total')
            ->whereRaw("{$statusSql} = 'refunded'")
            ->first();

        return [
            'collected_month' => (float) $collected,
            'pending_count' => (int) ($pending->cnt ?? 0),
            'pending_total' => (float) ($pending->total ?? 0),
            'refunded_count' => (int) ($refunded->cnt ?? 0),
            'refunded_total' => (float) ($refunded->total ?? 0),
        ];
    }

    /** Sanitized DECIMAL amount from orders.total (varchar) w/ product_totalprice fallback. */
    protected function amountSql(): string
    {
        $amountCol = (string) config('admin_payments.amount_column', 'total');
        $fallbackCol = (string) config('admin_payments.amount_fallback_column', 'product_totalprice');
        $clean = "REGEXP_REPLACE(TRIM(COALESCE(o.{$amountCol}, '')), '[^0-9.]', '')";

        return "CASE WHEN CAST({$clean} AS DECIMAL(12,2)) > 0 THEN CAST({$clean} AS DECIMAL(12,2)) ELSE CAST(COALESCE(o.{$fallbackCol}, 0) AS DECIMAL(12,2)) END";
    }

    /** SQL CASE mapping orders.order_status values to normalized payment statuses. */
    protected function statusSql(): string
    {
        $whens = [];
        foreach (config('admin_payments.statuses', []) as $key => $status) {
            foreach ((array) ($status['order_statuses'] ?? []) as $raw) {
                if ($raw === null || $raw === '') {
                    continue;
                }
                $whens[] = 'WHEN ' . strtolower(var_export((string) $raw, true)) . " THEN '" . $key . "'";
            }
        }

        return 'CASE LOWER(TRIM(COALESCE(o.order_status, \'\'))) ' . implode(' ', $whens) . " ELSE 'pending' END";
    }

    protected function normalizeStatus(?string $orderStatus): string
    {
        $raw = strtolower(trim((string) $orderStatus));
        if ($raw === '') {
            return 'pending';
        }

        foreach (config('admin_payments.statuses', []) as $key => $status) {
            foreach ((array) ($status['order_statuses'] ?? []) as $candidate) {
                if ($candidate !== null && strtolower((string) $candidate) === $raw) {
                    return $key;
                }
            }
        }

        return 'pending';
    }

    /** Whether the refund action may be offered (config: column + charge ref + secret + toggle). */
    protected function refundAvailable(): bool
    {
        $hasChargeColumn = $this->firstExistingColumn(config('admin_payments.refund.charge_column_candidates', [])) !== null;
        $secretSet = trim((string) config('services.stripe.secret')) !== '';

        return $hasChargeColumn && $secretSet && Setting::getBool('api_stripe_enabled');
    }

    protected function firstExistingColumn(array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn('orders', $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $ts = strtotime(str_replace('/', '-', $value));

        return $ts ? date('Y-m-d', $ts) : null;
    }

    /* ==================================================================
     * Agent PM — NEW dynamic payment ledger (payments table)
     * ================================================================== */

    /** GET /admin/payments/ledger — authoritative ledger view. */
    public function indexLedger()
    {
        $states = collect(config('admin_payments_engine.states', []))->map(fn ($s, $k) => [
            'key' => $k, 'label' => $s['label'], 'color' => $s['color'],
        ])->values();

        return view('admin.payments.ledger', [
            'states'   => $states,
            'currency' => (string) Setting::get('business_currency', 'USD'),
        ]);
    }

    /** GET /admin/payments/ledger/data — paginated + filterable ledger JSON. */
    public function dataLedger(Request $request)
    {
        $query = Payment::query()
            ->leftJoin('users as u', 'u.id', '=', 'payments.user_id')
            ->leftJoin('orders as o', 'o.id', '=', 'payments.order_id')
            ->select('payments.*', 'u.name as user_name', 'u.email as user_email', 'o.order_id as order_ref');

        if ($q = trim((string) $request->input('q'))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('payments.reference', 'like', "%{$q}%")
                    ->orWhere('o.order_id', 'like', "%{$q}%")
                    ->orWhere('u.name', 'like', "%{$q}%")
                    ->orWhere('u.email', 'like', "%{$q}%");
            });
        }

        if (($status = trim((string) $request->input('status'))) !== '') {
            $query->where('payments.status', $status);
        }

        if ($from = $this->normalizeDate($request->input('date_from'))) {
            $query->where('payments.created_at', '>=', $from . ' 00:00:00');
        }
        if ($to = $this->normalizeDate($request->input('date_to'))) {
            $query->where('payments.created_at', '<=', $to . ' 23:59:59');
        }

        $paginator = $query->orderByDesc('payments.created_at')
            ->paginate(dp_per_page($request))
            ->setPath(route('admin.payments.ledger.data'));

        $currency = (string) Setting::get('business_currency', 'USD');

        $paginator->getCollection()->transform(function (Payment $p) use ($currency) {
            return [
                'id'          => $p->id,
                'reference'   => $p->reference,
                'order_ref'   => $p->order_id
                    ? (string) ($p->order_ref ?? $p->order_id)
                    : (('shop_order' === ($p->metadata['purpose'] ?? '')) ? ('Shop ' . ($p->metadata['shop_order_code'] ?? 'order')) : 'Wallet top-up'),
                'customer'    => trim(($p->user_name ?? '') . ' <' . ($p->user_email ?? '') . '>') ?: '—',
                'amount'      => number_format((float) $p->amount, 2),
                'refunded'    => (float) $p->amount_refunded > 0 ? number_format((float) $p->amount_refunded, 2) : null,
                'currency'    => $currency,
                'method'      => $p->payment_method_code ?: '—',
                'status'      => $p->status,
                'status_label'=> $p->stateLabel(),
                'status_color'=> $p->stateColor(),
                'set_by'      => $p->status_set_by,
                'proof_url'   => $p->proof_path ? route('admin.payments.proof', $p->id) : null,
                'created_at'  => optional($p->created_at)->format('M d, Y H:i'),
                'urls'        => [
                    'verify' => route('admin.payments.verify', $p->id),
                    'refund' => route('admin.payments.ledger.refund', $p->id),
                ],
                'can_verify' => $p->status === Payment::STATUS_AWAITING_VERIFICATION,
                'can_refund' => in_array($p->status, [Payment::STATUS_PAID, Payment::STATUS_PARTIALLY_REFUNDED]),
            ];
        });

        return response()->json($paginator->toArray());
    }

    /**
     * GET /admin/payments/proof/{payment} — stream the stored bank receipt
     * from the PRIVATE local disk (C3: proofs are never public URLs).
     *
     * Content-Type is derived from the file's MAGIC BYTES first (Flysystem
     * content sniffing) with the filename extension as fallback, so a proof
     * stored without an extension still downloads as a viewable image and a
     * download name like "proof-REF.jpg" — never an extension-less file the
     * browser refuses to render.
     */
    public function downloadProof(Request $request, Payment $payment)
    {
        $resolved = $payment->proofDiskPath();
        if (!$resolved || !Storage::disk($resolved['disk'])->exists($resolved['path'])) {
            abort(404, 'No receipt on file for this payment.');
        }

        [$disk, $path] = array_values($resolved);
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $mime = $this->proofMimeType($disk, $path, $ext);

        if ($ext === '') {
            $ext = self::PROOF_MIME_EXT[$mime] ?? 'bin';
        }

        return Storage::disk($disk)->download(
            $path,
            'proof-' . $payment->reference . '.' . $ext,
            ['Content-Type' => $mime]
        );
    }

    /** Extension map for proof mime types (download-name fallback). */
    protected const PROOF_MIME_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
    ];

    /**
     * Robust mime resolution for a stored proof: content sniffing first
     * (Storage::mimeType uses finfo on the bytes), extension second, and
     * never a falsey/`text/plain` result leaking into response headers —
     * an empty Content-Type would make Symfony default to text/html and
     * browsers would refuse to render the image.
     */
    protected function proofMimeType(string $disk, string $path, string $ext): string
    {
        $mime = Storage::disk($disk)->mimeType($path);

        if (!is_string($mime) || $mime === '' || in_array($mime, ['application/x-empty', 'text/plain', 'text/x-asm'], true)) {
            $mime = ($extMap = [
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'webp' => 'image/webp', 'gif' => 'image/gif', 'pdf' => 'application/pdf',
            ])[$ext] ?? 'application/octet-stream';
        }

        return $mime;
    }

    /**
     * PUT /admin/payments/ledger/{payment}/verify (PM-008/PM-013).
     * Body: approve=1|0, note=?. The ONLY path from awaiting_verification to paid.
     */
    public function verify(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'approve' => 'required|boolean',
            'note'    => 'nullable|string|max:1000',
        ]);

        try {
            $payment = $this->paymentService->verifyBankPayment($payment, $request->user(), (bool) $data['approve'], $data['note'] ?? null);
        } catch (PaymentException $e) {
            $this->paymentService->logTech('error', 'admin verify rejected: ' . ($e->technical ?? ''), ['payment' => $payment->reference]);

            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Payment ' . $payment->reference . ' marked as ' . $payment->stateLabel() . '.',
            'status'  => $payment->status,
        ]);
    }

    /**
     * PUT /admin/payments/ledger/{payment}/refund (PM-004).
     * Body: amount=?(partial), note=?. Goes through the gateway adapter
     * (real Stripe refund when configured; manual bookkeeping for bank).
     */
    public function refundLedger(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'amount'     => 'nullable|numeric|min:0.01',
            'note'       => 'nullable|string|max:1000',
            'destination' => 'nullable|in:original,wallet', // PM-017 refund destination
        ]);

        try {
            $payment = $this->paymentService->refundPayment(
                $payment,
                $request->user(),
                isset($data['amount']) && $data['amount'] !== '' ? (float) $data['amount'] : null,
                $data['note'] ?? null,
                $data['destination'] ?? 'original'
            );
        } catch (PaymentException $e) {
            $this->paymentService->logTech('error', 'admin refund rejected: ' . ($e->technical ?? ''), ['payment' => $payment->reference]);

            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Refund recorded for ' . $payment->reference . ' — status: ' . $payment->stateLabel() . '.',
            'status'  => $payment->status,
        ]);
    }
}
