<?php

namespace App\Http\Controllers\Home2;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use App\Models\ShopProduct;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopCheckoutController extends Controller
{
    protected PaymentService $payments;
    protected WalletService $wallets;

    public function __construct(PaymentService $payments, WalletService $wallets)
    {
        $this->payments = $payments;
        $this->wallets = $wallets;
    }

    public function form(ShopController $shop)
    {
        $cart = $shop->detailedCart();
        if ($cart['rows']->isEmpty()) {
            return redirect()->route('home2.shop.index')->with('error', 'Your cart is empty.');
        }
        $user = auth()->user();
        return view('home2.shop.checkout', [
            'cart'  => $cart,
            'name'  => $user->name ?? old('name'),
            'email' => $user->email ?? old('email'),
        ]);
    }

    public function place(Request $request, ShopController $shop)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:190',
            'address' => 'required|string|max:500',
            'city'    => 'required|string|max:120',
            'country' => 'required|string|max:120',
            'phone'   => 'nullable|string|max:40',
            'notes'   => 'nullable|string|max:1000',
        ]);

        $cart = $shop->detailedCart();
        if ($cart['rows']->isEmpty()) {
            return redirect()->route('home2.shop.index')->with('error', 'Your cart is empty.');
        }

        /* Re-validate stock inside the transaction; total computed server-side. */
        $order = DB::transaction(function () use ($cart, $data, $request) {
            $total = 0.0; $lines = []; $forced = null;
            foreach ($cart['rows'] as $row) {
                $fresh = ShopProduct::whereKey($row->id)->lockForUpdate()->first();
                if (!$fresh || !$fresh->is_active || (int) $fresh->stock < $row->qty) {
                    throw new \RuntimeException('"' . ($row->name) . '" just went out of stock — please review your cart.');
                }
                $fresh->decrement('stock', $row->qty);
                $total += $row->qty * (float) $fresh->price;
                $lines[] = ['product' => $fresh, 'qty' => $row->qty];
                // PM-015: first product that forces a payment method constrains
                // the whole cart (most restrictive member wins).
                if ($forced === null && trim((string) $fresh->forced_payment_method_code) !== '') {
                    $forced = trim((string) $fresh->forced_payment_method_code);
                }
            }

            $order = ShopOrder::create([
                'code'    => 'SHOP-' . strtoupper(substr(uniqid(), -6)) . '-' . random_int(10, 99),
                'user_id' => $request->user()->id,
                'total'   => round($total, 2),
                'status'  => 'pending',
                // Snapshot at checkout; admin can still override later.
                'forced_payment_method_code' => $forced,
            ]);
            foreach ($lines as $l) {
                ShopOrderItem::create([
                    'shop_order_id'   => $order->id,
                    'shop_product_id' => $l['product']->id,
                    'name'            => $l['product']->name,
                    'qty'             => $l['qty'],
                    'price'           => $l['product']->price,
                ]);
            }
            /* Shipping address snapshot on the order row keeps proof after edit. */
            $order->forceFill([
                'ship_name'    => $data['name'],
                'ship_address' => trim($data['address'] . ', ' . $data['city'] . ', ' . $data['country']),
                'ship_phone'   => $data['phone'] ?? '',
            ])->save();

            return $order;
        });

        session()->forget(ShopController::CART_KEY);
        return redirect()->route('home2.shop.confirmation', $order->code)
            ->with('success', 'Order placed — pay from the confirmation page.');
    }

    public function confirmation(string $code)
    {
        $order = ShopOrder::where('code', $code)->where('user_id', auth()->id())->with('items')->firstOrFail();

        $bank = [
            'bank_name'   => \App\Models\Setting::get('bank_name'),
            'account'     => \App\Models\Setting::get('bank_account_title'),
            'iban'        => \App\Models\Setting::get('bank_iban'),
        ];

        /* PM-015: in Advanced mode the confirmation page pays through the
           engine (stripe / wallet / bank transfer) honouring forced methods. */
        $engine = null;
        $bankPayment = null;
        if ($this->payments->engineEnabled() && $order->status === 'pending') {
            $methods = $this->payments->allowedMethods('shop', (float) $order->total);
            $methods = $this->wallets->decorateMethods($methods, (float) $order->total, auth()->user());

            $forced = $this->payments->forcedMethodForShopOrder($order);
            if ($forced) {
                $methods = array_values(array_filter($methods, fn ($e) => $e['method']->code === $forced->code));
            }

            $engine = [
                'methods'         => $methods,
                'forced'          => $forced?->code,
                'publishable_key' => trim((string) config('services.stripe.key')) ?: null,
            ];

            // An open bank-transfer attempt: surface its reference for the wire.
            $bankPayment = \App\Models\Payment::query()
                ->whereNull('order_id')
                ->where('user_id', auth()->id())
                ->where('metadata->purpose', 'shop_order')
                ->where('metadata->shop_order_id', (string) $order->id)
                ->where('gateway', 'bank_transfer')
                ->whereIn('status', [\App\Models\Payment::STATUS_AWAITING_PAYMENT, \App\Models\Payment::STATUS_AWAITING_VERIFICATION])
                ->orderByDesc('id')
                ->first();
            if ($bankPayment) {
                $gateway = $this->payments->gatewayFor($bankPayment);
                $engine['bank_instructions'] = $gateway ? $gateway->instructions($bankPayment) : null;
                $engine['bank_payment_id'] = $bankPayment->id;
            }
        }

        return view('home2.shop.confirmation', [
            'order' => $order,
            'bank'  => $bank,
            'engine'=> $engine,
        ]);
    }

    /* ---------------- POST home2/shop/pay/{code} — engine pay ---------------- */

    public function pay(Request $request, string $code)
    {
        $order = ShopOrder::where('code', $code)->where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate(['method' => 'required|string|max:30']);
        if (!$this->payments->engineEnabled()) {
            return response()->json(['ok' => false, 'message' => 'The advanced payment system is currently disabled.'], 422);
        }
        if ($order->status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'This shop order is already ' . $order->status . '.'], 422);
        }

        try {
            $result = $this->payments->initiateShopOrderPayment($order, $data['method'], $request->user());
        } catch (PaymentException $e) {
            if ($e->technical) {
                $this->payments->logTech('error', 'shop pay rejected: ' . $e->technical);
            }

            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->payments->logTech('error', 'shop pay failed: ' . $e->getMessage(), ['shop_order' => $order->id]);
            report($e);

            return response()->json(['ok' => false, 'message' => config('admin_payments_engine.friendly_errors.generic')], 422);
        }

        $payment = $result['payment'];

        return response()->json([
            'ok'         => true,
            'created'    => $result['created'],
            'payment_id' => $payment->id,
            'reference'  => $payment->reference,
            'status'     => $payment->status,
            'status_url' => route('home2.shop.pay.status', $order->code),
            'next'       => $result['payload']['kind'] ?? null,
            'payload'    => $result['payload'],
        ]);
    }

    /* ---------------- GET home2/shop/pay/{code}/status ---------------- */

    public function status(Request $request, string $code)
    {
        $order = ShopOrder::where('code', $code)->where('user_id', auth()->id())->firstOrFail();

        $payment = \App\Models\Payment::query()
            ->whereNull('order_id')
            ->where('user_id', auth()->id())
            ->where('metadata->purpose', 'shop_order')
            ->where('metadata->shop_order_id', (string) $order->id)
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'ok'          => true,
            'shop_status' => $order->status,
            'paid'        => $order->status === 'paid',
            'payment'     => $payment ? [
                'reference' => $payment->reference,
                'status'    => $payment->status,
                'method'    => $payment->payment_method_code,
            ] : null,
        ]);
    }
}
