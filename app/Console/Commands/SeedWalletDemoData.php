<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Agent WL — demo wallet data (seed for admin/dev walkthroughs).
 *
 * Inserts a realistic, internally-consistent wallet history for the
 * [TEST] demo users created by dp:seed-demo:
 *   - Stripe card top-ups settled through the webhook path (paid by
 *     'webhook' with processed_event_ids + webhook_event metadata, just
 *     like real settlement)
 *   - a wallet order payment (paid from balance) that was later fully
 *     refunded back into the wallet
 *   - matching append-only wallet_transactions with balance_after snapshots
 *
 * Every seeded row is flagged metadata->demo_seed = true so this command
 * can re-run cleanly (its own rows are wiped and rebuilt first).
 */
class SeedWalletDemoData extends Command
{
    protected $signature = 'dp:seed-wallet-demo';

    protected $description = 'Seed consistent demo wallet data (top-ups via Stripe/webhook, wallet payment + refund) for the [TEST] users. Re-runnable.';

    public function handle()
    {
        $alice = User::where('email', 'alice@dptest.local')->first();
        $bob   = User::where('email', 'bob@dptest.local')->first();

        if (!$alice || !$bob) {
            $this->error('[TEST] demo users not found — run `php artisan dp:seed-demo` first.');

            return 1;
        }

        $stripeMethod = PaymentMethod::where('code', 'stripe')->first();
        $walletMethod = PaymentMethod::where('code', 'wallet')->first();
        if (!$stripeMethod || !$walletMethod) {
            $this->error('payment_methods rows missing (stripe/wallet) — run migrations first.');

            return 1;
        }

        // An order owned by bob for the wallet-payment demo (any accepted-offer order works).
        $order = DB::table('orders')->where('user_id', $bob->id)->orderByDesc('id')->first();
        if (!$order) {
            $this->error('Bob has no orders — run `php artisan dp:seed-demo` first.');

            return 1;
        }

        $this->cleanupPreviousRun($alice, $bob, $order->id);

        DB::transaction(function () use ($alice, $bob, $order, $stripeMethod, $walletMethod) {
            // ---------- wallets ----------
            $walletAlice = Wallet::firstOrCreate(['user_id' => $alice->id], ['balance' => 0, 'currency' => 'USD']);
            $walletBob   = Wallet::firstOrCreate(['user_id' => $bob->id],   ['balance' => 0, 'currency' => 'USD']);

            // ---------- top-ups settled through the webhook ----------
            $this->seedStripeTopup($walletAlice, 50.00, $stripeMethod, now()->subDays(6));
            $this->seedStripeTopup($walletBob, 50.00, $stripeMethod, now()->subDays(5));

            // ---------- wallet order payment (paid) then full refund ----------
            $this->seedWalletPaymentWithRefund($walletBob, $order->id, 34.00, $walletMethod);
        });

        $this->info('Demo wallet data seeded:');
        $this->line('  - alice@dptest.local: $50.00 top-up (Stripe/webhook paid) → balance 50.00');
        $this->line('  - bob@dptest.local:   $50.00 top-up (Stripe/webhook paid) → wallet paid order #' . $order->id . ' ($34) → refunded back → balance 50.00');
        $this->line('  - ledger rows carry processed_event_ids / webhook_event metadata like real settlement');

        return 0;
    }

    /* ------------------------------------------------ internals */

    /** Paid Stripe top-up with realistic webhook-settlement metadata. */
    protected function seedStripeTopup(Wallet $wallet, float $amount, PaymentMethod $method, $at): Payment
    {
        $paidAt = $at->copy()->addMinutes(2);
        $eventId = 'evt_demo_' . strtolower(Str::random(14));

        $payment = Payment::create([
            'reference'           => 'DEMO-WT-' . $wallet->user_id . '-' . strtoupper(Str::random(4)),
            'order_id'            => null,
            'user_id'             => $wallet->user_id,
            'attempt'             => 1,
            'amount'              => $amount,
            'currency'            => 'USD',
            'payment_method_id'   => $method->id,
            'payment_method_code' => $method->code,
            'gateway'             => 'stripe',
            'gateway_transaction_id' => 'pi_demo_' . strtolower(Str::random(12)),
            'status'              => Payment::STATUS_PAID,
            'status_set_by'       => 'webhook',
            'initiated_at'        => $at,
            'paid_at'             => $paidAt,
            'metadata'            => [
                'purpose'      => 'wallet_topup',
                'demo_seed'    => true,
                'gateway_kind' => 'intent',
                'processed_event_ids' => [$eventId],
                'webhook_event' => [
                    'id'   => $eventId,
                    'type' => 'payment_intent.succeeded',
                    'at'   => $paidAt->toDateTimeString(),
                ],
            ],
            'created_at' => $at,
            'updated_at' => $paidAt,
        ]);

        $wallet->balance = round((float) $wallet->balance + $amount, 2);
        $wallet->save();

        $wallet->transactions()->create([
            'user_id'       => $wallet->user_id,
            'type'          => WalletTransaction::TYPE_TOPUP,
            'direction'     => WalletTransaction::DIRECTION_CREDIT,
            'amount'        => $amount,
            'balance_after' => $wallet->balance,
            'payment_id'    => $payment->id,
            'reference'     => 'TOPUP-' . $payment->id,
            'description'   => 'Wallet top-up via stripe (demo)',
            'metadata'      => ['demo_seed' => true, 'webhook_event' => $eventId],
            'created_at'    => $paidAt,
            'updated_at'    => $paidAt,
        ]);

        return $payment;
    }

    /** Order paid from wallet balance, then fully refunded back to the wallet. */
    protected function seedWalletPaymentWithRefund(Wallet $wallet, int $orderId, float $amount, PaymentMethod $method): Payment
    {
        $paidAt     = now()->subDays(4);
        $refundedAt = now()->subDays(3);

        $payment = Payment::create([
            'reference'           => 'DEMO-WP-' . $orderId . '-' . strtoupper(Str::random(4)),
            'order_id'            => $orderId,
            'user_id'             => $wallet->user_id,
            'attempt'             => 1,
            'amount'              => $amount,
            'amount_refunded'     => $amount,
            'currency'            => 'USD',
            'payment_method_id'   => $method->id,
            'payment_method_code' => $method->code,
            'gateway'             => 'wallet',
            'gateway_transaction_id' => 'WALLET-DEMO-' . strtoupper(Str::random(6)),
            'status'              => Payment::STATUS_REFUNDED,
            'status_set_by'       => 'admin',
            'initiated_at'        => $paidAt,
            'paid_at'             => $paidAt,
            'refunded_at'         => $refundedAt,
            'metadata'            => [
                'demo_seed' => true,
                'wallet'    => ['debited' => $amount, 'at' => $paidAt->toDateTimeString()],
                'refunds'   => [[
                    'amount'      => $amount,
                    'by'          => 1,
                    'at'          => $refundedAt->toDateTimeString(),
                    'note'        => 'Demo refund back to wallet',
                    'gateway_ref' => 'WALLET-DEMO',
                ]],
            ],
            'created_at' => $paidAt,
            'updated_at' => $refundedAt,
        ]);

        // debit on pay …
        $wallet->balance = round((float) $wallet->balance - $amount, 2);
        $wallet->save();
        $wallet->transactions()->create([
            'user_id'       => $wallet->user_id,
            'type'          => WalletTransaction::TYPE_ORDER_PAYMENT,
            'direction'     => WalletTransaction::DIRECTION_DEBIT,
            'amount'        => $amount,
            'balance_after' => $wallet->balance,
            'payment_id'    => $payment->id,
            'order_id'      => $orderId,
            'reference'     => 'WPAY-' . $payment->id,
            'description'   => 'Order payment from wallet (demo)',
            'metadata'      => ['demo_seed' => true],
            'created_at'    => $paidAt,
            'updated_at'    => $paidAt,
        ]);

        // … credit back on refund.
        $wallet->balance = round((float) $wallet->balance + $amount, 2);
        $wallet->save();
        $wallet->transactions()->create([
            'user_id'       => $wallet->user_id,
            'type'          => WalletTransaction::TYPE_REFUND,
            'direction'     => WalletTransaction::DIRECTION_CREDIT,
            'amount'        => $amount,
            'balance_after' => $wallet->balance,
            'payment_id'    => $payment->id,
            'order_id'      => $orderId,
            'description'   => 'Refund of payment ' . $payment->reference . ' (demo)',
            'metadata'      => ['demo_seed' => true],
            'created_at'    => $refundedAt,
            'updated_at'    => $refundedAt,
        ]);

        // The paid cycle completed — reflect it on the legacy order flow.
        DB::table('offerorders')->where('order_id', $orderId)->update(['offer_status' => 1]);

        return $payment;
    }

    /** Wipe only rows this command previously created (flagged demo_seed). */
    protected function cleanupPreviousRun(User $alice, User $bob, int $demoOrderId): void
    {
        $userIds = [$alice->id, $bob->id];

        $demoPaymentIds = Payment::where('metadata->demo_seed', true)->pluck('id')->all();
        $affectedOrders = Payment::whereIn('id', $demoPaymentIds)->whereNotNull('order_id')->pluck('order_id')->all();

        WalletTransaction::whereIn('user_id', $userIds)->delete();
        Wallet::whereIn('user_id', $userIds)->delete();
        if ($demoPaymentIds) {
            Payment::whereIn('id', $demoPaymentIds)->delete();
        }

        // Reset the demo order's paid marker unless another real payment exists.
        $allAffected = array_unique(array_merge($affectedOrders, [$demoOrderId]));
        foreach ($allAffected as $orderId) {
            $stillPaid = Payment::where('order_id', $orderId)
                ->where('status', Payment::STATUS_PAID)
                ->exists();
            if (!$stillPaid) {
                DB::table('offerorders')->where('order_id', $orderId)->update(['offer_status' => 0]);
            }
        }
    }
}
