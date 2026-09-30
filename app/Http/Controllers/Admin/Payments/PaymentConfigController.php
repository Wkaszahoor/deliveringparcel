<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodRule;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;

/**
 * Agent PM — payment methods + routing rules config (PM-001/PM-008).
 *
 * CRUD-lite over payment_methods / payment_method_rules plus the
 * Settings-driven bank details block. Everything is audited through the
 * existing AuditLogger. Controllers stay gateway-agnostic (PM-002).
 */
class PaymentConfigController extends Controller
{
    protected PaymentService $payments;

    public function __construct(PaymentService $payments)
    {
        $this->payments = $payments;
    }

    public function index()
    {
        $methods = PaymentMethod::orderBy('priority')->orderBy('code')->get();
        $contexts = PaymentMethodRule::contexts();

        $rules = PaymentMethodRule::query()
            ->join('payment_methods as m', 'm.id', '=', 'payment_method_rules.payment_method_id')
            ->select('payment_method_rules.*', 'm.code as method_code')
            ->get()
            ->keyBy(fn ($r) => $r->service_context . '|' . $r->method_code);

        $bank = [];
        foreach (config('admin_payments_engine.bank_settings', []) as $key => $meta) {
            $bank[$key] = [
                'label'   => $meta['label'],
                'value'   => (string) Setting::get($key, $meta['default'] ?? ''),
            ];
        }

        $gatewayStatus = [];
        foreach ($methods as $method) {
            try {
                $gatewayStatus[$method->code] = $this->payments->gateway($method->gateway)->isConfigured();
            } catch (\Throwable $e) {
                $gatewayStatus[$method->code] = false;
            }
        }

        // Stripe keys presence only — values are NEVER read here (PM-007).
        $stripeConfigured = trim((string) config('services.stripe.secret')) !== '';
        $webhookConfigured = trim((string) config('services.stripe.webhook_secret')) !== '';

        return view('admin.payments.config', [
            'methods'         => $methods,
            'contexts'        => $contexts,
            'rules'           => $rules,
            'bank'            => $bank,
            'gatewayStatus'   => $gatewayStatus,
            'stripeConfigured'  => $stripeConfigured,
            'webhookConfigured' => $webhookConfigured,
            'currency'        => (string) Setting::get('business_currency', 'USD'),
        ]);
    }

    /** POST /admin/payments/config/methods/{method} — update one catalogue row. */
    public function updateMethod(Request $request, PaymentMethod $method)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_enabled'  => 'nullable|boolean',
            'priority'    => 'required|integer|min:0|max:9999',
            'min_amount'  => 'nullable|numeric|min:0',
            'max_amount'  => 'nullable|numeric|min:0|gte:min_amount',
        ]);

        $method->fill([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_enabled'  => (bool) ($data['is_enabled'] ?? false),
            'priority'    => (int) $data['priority'],
            'min_amount'  => $data['min_amount'] !== null && $data['min_amount'] !== '' ? $data['min_amount'] : null,
            'max_amount'  => $data['max_amount'] !== null && $data['max_amount'] !== '' ? $data['max_amount'] : null,
        ]);

        AuditLogger::log($method, 'updated');
        $method->save();

        return redirect()
            ->route('admin.payments.config.index')
            ->with('success', 'Payment method "' . $method->code . '" updated.');
    }

    /** POST /admin/payments/config/methods — create a catalogue row (new adapter). */
    public function storeMethod(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|alpha_dash|max:30|unique:payment_methods,code',
            'name'        => 'required|string|max:100',
            'gateway'     => 'required|string|max:50|in:' . implode(',', array_keys(config('admin_payments_engine.gateways', []))),
            'description' => 'nullable|string|max:255',
            'priority'    => 'required|integer|min:0|max:9999',
            'min_amount'  => 'nullable|numeric|min:0',
            'max_amount'  => 'nullable|numeric|min:0|gte:min_amount',
        ]);

        $method = new PaymentMethod($data);
        $method->is_enabled = false; // new methods start disabled until rules are wired
        $method->save();
        AuditLogger::log($method, 'created');

        return redirect()->route('admin.payments.config.index')->with('success', 'Payment method "' . $method->code . '" created (starts disabled).');
    }

    /**
     * POST /admin/payments/config/rules — the context × method matrix.
     * Payload: rules[<context>][<method_code>] = 1|0, priorities[<context>][<method_code>] = int.
     */
    public function updateRules(Request $request)
    {
        $rulesInput = (array) $request->input('rules', []);
        $priorities = (array) $request->input('priorities', []);
        $contexts = array_keys(PaymentMethodRule::contexts());
        $methods = PaymentMethod::pluck('id', 'code');

        foreach ($contexts as $context) {
            foreach ($methods as $code => $methodId) {
                $allowed = !empty($rulesInput[$context][$code]);
                $priority = isset($priorities[$context][$code]) && $priorities[$context][$code] !== ''
                    ? (int) $priorities[$context][$code]
                    : null;

                $rule = PaymentMethodRule::updateOrCreate(
                    ['service_context' => $context, 'payment_method_id' => $methodId],
                    ['is_allowed' => $allowed, 'priority' => $priority]
                );

                AuditLogger::log($rule, 'updated');
            }
        }

        return redirect()->route('admin.payments.config.index')->with('success', 'Payment routing rules saved.');
    }

    /** POST /admin/payments/config/bank — bank instruction settings (PM-008). */
    public function updateBank(Request $request)
    {
        $keys = array_keys(config('admin_payments_engine.bank_settings', []));

        $data = $request->validate(
            collect($keys)->mapWithKeys(fn ($k) => [$k => 'nullable|string|max:255'])->all()
        );

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value, 'payments', 'string');
        }

        return redirect()->route('admin.payments.config.index')->with('success', 'Bank transfer details saved.');
    }
}
