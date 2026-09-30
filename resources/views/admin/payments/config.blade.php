{{--
    Agent PM — Payment methods + routing rules configuration (PM-001/PM-008).
    Plain form POSTs (no JS dependency): methods catalogue, context matrix,
    bank instruction settings, gateway readiness indicators.
--}}
@extends('layouts.tailwind.app')

@section('title', 'Payment Configuration')
@section('page_title', 'Payment Configuration')
@section('page_subtitle', 'Methods, service routing rules and bank transfer details')

@section('content')
    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
        <x-admin.alert :type="$stripeConfigured ? 'success' : 'info'">
            <i class="fas {{ $stripeConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }} mr-1"></i>
            <strong>Stripe:</strong>
            {{ $stripeConfigured ? 'secret key configured (env only — value never displayed).' : 'no secret key configured — the Stripe adapter runs in safe mock mode and is shown disabled to customers.' }}
            @if (!$webhookConfigured)
                <br><small>Webhook signature secret (STRIPE_WEBHOOK_SECRET) is not set — incoming webhooks will be rejected.</small>
            @endif
        </x-admin.alert>
        <x-admin.alert :type="($gatewayStatus['bank_transfer'] ?? false) ? 'success' : 'info'">
            <i class="fas {{ ($gatewayStatus['bank_transfer'] ?? false) ? 'fa-check-circle' : 'fa-exclamation-triangle' }} mr-1"></i>
            <strong>Bank transfer:</strong>
            {{ ($gatewayStatus['bank_transfer'] ?? false) ? 'bank details are configured below.' : 'bank name / account number missing — complete the Bank transfer details form below.' }}
        </x-admin.alert>
    </div>

    <x-admin.card title="Payment methods" class="mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Code</th>
                        <th class="py-2 pr-4">Gateway</th>
                        <th class="py-2 pr-4" style="min-width:220px">Label &amp; hint</th>
                        <th class="py-2 pr-4">Enabled</th>
                        <th class="py-2 pr-4">Priority</th>
                        <th class="py-2 pr-4">Min ({{ $currency }})</th>
                        <th class="py-2 pr-4">Max ({{ $currency }})</th>
                        <th class="py-2 pr-4">Ready</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($methods as $m)
                        <tr>
                            <form method="POST" action="{{ route('admin.payments.config.methods.update', $m) }}" class="contents">
                                @csrf
                                <td class="py-2 pr-4"><code class="text-xs">{{ $m->code }}</code></td>
                                <td class="py-2 pr-4"><span class="text-xs text-slate-500">{{ $m->gateway }}</span></td>
                                <td class="py-2 pr-4">
                                    <input type="text" name="name" value="{{ $m->name }}" required
                                        class="mb-1 block w-full rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                    <input type="text" name="description" value="{{ $m->description }}" placeholder="Customer hint (optional)"
                                        class="block w-full rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                </td>
                                <td class="py-2 pr-4 text-center">
                                    <input type="hidden" name="is_enabled" value="0">
                                    <input type="checkbox" name="is_enabled" value="1" {{ $m->is_enabled ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                                </td>
                                <td class="py-2 pr-4">
                                    <input type="number" name="priority" value="{{ $m->priority }}" min="0" max="9999" required
                                        class="w-20 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                </td>
                                <td class="py-2 pr-4">
                                    <input type="number" name="min_amount" value="{{ $m->min_amount }}" step="0.01" min="0"
                                        class="w-24 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                </td>
                                <td class="py-2 pr-4">
                                    <input type="number" name="max_amount" value="{{ $m->max_amount }}" step="0.01" min="0"
                                        class="w-24 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                </td>
                                <td class="py-2 pr-4">
                                    @if (($gatewayStatus[$m->code] ?? false))
                                        <x-admin.badge color="green">ready</x-admin.badge>
                                    @else
                                        <x-admin.badge color="slate" title="Gateway keys/details incomplete — offered disabled">not ready</x-admin.badge>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-brand px-2 py-1 text-xs font-medium text-brand hover:bg-brand-light">
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                    <tr>
                        <form method="POST" action="{{ route('admin.payments.config.methods.store') }}" class="contents">
                            @csrf
                            <td class="py-2 pr-4">
                                <input type="text" name="code" placeholder="cod" required
                                    class="w-24 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            </td>
                            <td class="py-2 pr-4">
                                <select name="gateway" required class="rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                    @foreach (array_keys(config('admin_payments_engine.gateways', [])) as $gKey)
                                        <option value="{{ $gKey }}">{{ $gKey }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="py-2 pr-4">
                                <input type="text" name="name" placeholder="Label" required
                                    class="block w-full rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            </td>
                            <td class="py-2 pr-4 text-center"><span class="text-xs text-slate-400">starts off</span></td>
                            <td class="py-2 pr-4">
                                <input type="number" name="priority" value="100" min="0" max="9999" required
                                    class="w-20 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            </td>
                            <td class="py-2 pr-4">
                                <input type="number" name="min_amount" step="0.01" min="0"
                                    class="w-24 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            </td>
                            <td class="py-2 pr-4">
                                <input type="number" name="max_amount" step="0.01" min="0"
                                    class="w-24 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                            </td>
                            <td class="py-2 pr-4 text-xs text-slate-400">—</td>
                            <td class="py-2 pr-4">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-md border border-green-300 px-2 py-1 text-xs font-medium text-green-600 hover:bg-green-50">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </td>
                        </form>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-slate-500">Lower priority = offered first. Methods whose gateway is "not ready" are shown to customers as disabled, never silently hidden. Adding a real new gateway requires its adapter class registered in <code>config/admin_payments_engine.php</code>.</p>
    </x-admin.card>

    <x-admin.card title="Service routing rules" class="mb-6">
        <form method="POST" action="{{ route('admin.payments.config.rules.update') }}">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Service</th>
                            @foreach ($methods as $m)
                                <th class="py-2 pr-4 text-center">{{ $m->name }} <br><code class="text-xs normal-case">{{ $m->code }}</code></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($contexts as $ctxKey => $ctxLabel)
                            <tr>
                                <td class="py-2 pr-4"><strong class="text-slate-800">{{ $ctxLabel }}</strong><br><span class="text-xs text-slate-400">{{ $ctxKey }}</span></td>
                                @foreach ($methods as $m)
                                    @php $rule = $rules->get($ctxKey . '|' . $m->code); @endphp
                                    <td class="py-2 pr-4 text-center">
                                        <label class="mb-1 block">
                                            <input type="hidden" name="rules[{{ $ctxKey }}][{{ $m->code }}]" value="0">
                                            <input type="checkbox" name="rules[{{ $ctxKey }}][{{ $m->code }}]" value="1" {{ $rule && $rule->is_allowed ? 'checked' : '' }}
                                                class="h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                                        </label>
                                        <input type="number" name="priorities[{{ $ctxKey }}][{{ $m->code }}]"
                                            value="{{ $rule && $rule->priority !== null ? $rule->priority : '' }}"
                                            title="Per-service order (optional)" min="0" max="9999"
                                            class="w-20 rounded-md border border-slate-300 px-2 py-1 text-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <x-admin.button type="submit"><i class="fas fa-save"></i> Save routing rules</x-admin.button>
                <span class="text-xs text-slate-500">Checked = offered for that service. Optional per-service priority overrides the method's global priority when ordering the customer selector.</span>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card class="mb-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-university mr-1"></i> Bank transfer details</h3>
            <a href="{{ route('admin.payments.config.banks') }}" title="Manage multiple bank accounts"
                class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">
                <i class="fas fa-list"></i> Manage Bank Accounts
            </a>
        </div>
        <form method="POST" action="{{ route('admin.payments.config.bank.update') }}">
            @csrf
            <x-admin.alert type="info" class="mb-4">
                <strong>Note:</strong> For multiple bank accounts, use the <a href="{{ route('admin.payments.config.banks') }}" class="underline">Bank Accounts Management</a> section. This form is used as a fallback when no bank accounts are configured.
            </x-admin.alert>
            <div class="grid grid-cols-1 gap-x-4 md:grid-cols-2">
                @foreach ($bank as $key => $field)
                    <x-admin.input :label="$field['label']" :name="$key" :value="$field['value']" />
                @endforeach
            </div>
            <div class="mt-2 flex items-center gap-3">
                <x-admin.button type="submit"><i class="fas fa-save"></i> Save bank details</x-admin.button>
                <span class="text-xs text-slate-500">Stored in the settings store; shown to customers on the bank payment page. The transfer reference is always the payment reference for reconciliation.</span>
            </div>
        </form>
    </x-admin.card>

    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
            <i class="fas fa-arrow-left"></i> Payments overview
        </a>
        <a href="{{ route('admin.payments.ledger') }}" class="inline-flex items-center gap-1.5 rounded-md border border-brand px-3 py-1.5 text-sm font-medium text-brand hover:bg-brand-light">
            <i class="fas fa-book"></i> Payment ledger &amp; verification
        </a>
        <a href="{{ route('admin.payments.config.banks') }}" class="inline-flex items-center gap-1.5 rounded-md border border-green-300 px-3 py-1.5 text-sm font-medium text-green-600 hover:bg-green-50">
            <i class="fas fa-university"></i> Manage Bank Accounts
        </a>
    </div>
@endsection
