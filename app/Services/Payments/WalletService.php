<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\AuditLogger;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Agent WL — wallet core (PM-014).
 *
 * The ONLY writer of wallets.balance. Every mutation runs inside ONE
 * DB::transaction that holds the wallet row locked FOR UPDATE from the
 * balance check until the ledger row is written, so concurrent payments /
 * top-ups / refunds serialize and the balance can never go negative through
 * races. Each mutation appends a WalletTransaction row carrying the
 * balance_after snapshot (auditable running balance). When called from
 * PaymentService (which is itself transactional), Laravel nests these via
 * SAVEPOINTs — the lock survives until the outermost commit.
 *
 * Integration with the payment engine:
 *  - payFromWallet()   — settles an order payment from balance (called by
 *                        PaymentService::driveGateway when the wallet
 *                        gateway returns kind=wallet)
 *  - creditTopup()     — credits a top-up when its payment reaches paid
 *                        (hook inside PaymentService::transition(); the
 *                        payments state machine guarantees it runs once)
 *  - refundToWallet()  — credits back when a wallet-paid order is refunded
 *                        (called by WalletGateway::refund)
 *  - reverseTopup()    — debits the wallet when a top-up payment is
 *                        refunded back to the card (called by
 *                        PaymentService::refundPayment)
 */
class WalletService
{
    public function __construct(protected PaymentService $payments)
    {
    }

    /* ------------------------------------------------ read helpers */

    public function walletFor($user): Wallet
    {
        return Wallet::forUser($user);
    }

    public function balanceFor($user): float
    {
        return (float) Wallet::forUser($user)->balance;
    }

    public function enabled(): bool
    {
        try {
            return \App\Models\Setting::getBool('wallet_enabled', true);
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Patch wallet entries of an allowedMethods() result with the live
     * balance (display + availability), leaving other methods untouched.
     */
    public function decorateMethods(array $methods, ?float $amount, $user): array
    {
        if (!$this->enabled()) {
            return $methods;
        }

        $balance = $this->balanceFor($user);
        foreach ($methods as $i => $entry) {
            if (($entry['method']->code ?? null) !== 'wallet') {
                continue;
            }
            $methods[$i]['wallet_balance'] = $balance;
            if ($entry['available'] && $amount !== null && $balance < $amount) {
                $methods[$i]['available'] = false;
                $methods[$i]['reason'] = 'Insufficient wallet balance (' . number_format($balance, 2) . ' available). Top up your wallet first.';
            }
        }

        return $methods;
    }

    /* ------------------------------------------------ pay order from balance */

    /**
     * Debit the wallet for an order payment and mark it paid — one atomic
     * transaction (called from PaymentService::driveGateway).
     */
    public function payFromWallet(Payment $payment, ?User $customer = null): Payment
    {
        if ($payment->status === Payment::STATUS_PAID) {
            return $payment; // idempotent re-drive
        }

        return DB::transaction(function () use ($payment) {
            $wallet = $this->lockRow($payment->user_id);
            $this->assertUsable($wallet, (float) $payment->amount);

            $amount = round((float) $payment->amount, 2);
            $wallet->balance = round((float) $wallet->balance - $amount, 2);
            $wallet->save();

            $this->record($wallet, WalletTransaction::TYPE_ORDER_PAYMENT, WalletTransaction::DIRECTION_DEBIT, $amount, [
                'payment_id' => $payment->id,
                'order_id'   => $payment->order_id,
                'reference'  => 'WPAY-' . $payment->id, // unique → replay-proof
            ]);

            $this->payments->logTech('info', 'wallet debited for order payment', [
                'payment' => $payment->reference, 'amount' => $amount,
            ]);

            return $this->payments->transition($payment, Payment::STATUS_PAID, 'system', [
                'wallet' => ['debited' => $amount, 'at' => now()->toDateTimeString()],
            ]);
        });
    }

    /* ------------------------------------------------ top-up credit */

    /**
     * Credit the wallet when a top-up payment reaches paid. Called from
     * PaymentService::transition() BEFORE the payment row is saved, so a
     * credit failure aborts the paid transition (Stripe retries the
     * webhook; nothing is ever credited without the row saying paid).
     */
    public function creditTopup(Payment $payment): void
    {
        $reference = 'TOPUP-' . $payment->id;

        // Idempotency belt-and-braces: the unique index on reference makes
        // a replay impossible even if two paths ever raced here.
        if (WalletTransaction::where('reference', $reference)->exists()) {
            return;
        }

        DB::transaction(function () use ($payment, $reference) {
            $wallet = $this->lockRow($payment->user_id);
            $this->assertNotLocked($wallet);

            $amount = round((float) $payment->amount, 2);
            $wallet->balance = round((float) $wallet->balance + $amount, 2);
            $wallet->save();

            $this->record($wallet, WalletTransaction::TYPE_TOPUP, WalletTransaction::DIRECTION_CREDIT, $amount, [
                'payment_id'  => $payment->id,
                'reference'   => $reference,
                'description' => 'Wallet top-up via ' . ($payment->payment_method_code ?: 'gateway'),
            ]);

            $this->payments->logTech('info', 'wallet credited for top-up', [
                'payment' => $payment->reference, 'amount' => $amount,
            ]);
        });
    }

    /* ------------------------------------------------ refunds */

    /** Credit back to the wallet when a wallet-paid order payment is refunded. */
    public function refundToWallet(Payment $payment, float $amount): array
    {
        try {
            DB::transaction(function () use ($payment, $amount) {
                $wallet = $this->lockRow($payment->user_id);
                $this->assertNotLocked($wallet);

                $amount = round($amount, 2);
                $wallet->balance = round((float) $wallet->balance + $amount, 2);
                $wallet->save();

                $this->record($wallet, WalletTransaction::TYPE_REFUND, WalletTransaction::DIRECTION_CREDIT, $amount, [
                    'payment_id'  => $payment->id,
                    'order_id'    => $payment->order_id,
                    'description' => 'Refund of payment ' . $payment->reference,
                ]);

                $this->payments->logTech('info', 'wallet credited for refund', [
                    'payment' => $payment->reference, 'amount' => $amount,
                ]);
            });

            return ['ok' => true, 'refunded' => $amount, 'gateway_ref' => 'WALLET-' . $payment->reference, 'error' => null];
        } catch (PaymentException $e) {
            return ['ok' => false, 'refunded' => 0.0, 'gateway_ref' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Debit the wallet when a TOP-UP payment itself is refunded (money went
     * back to the card, so the credited balance must be reversed). Capped at
     * the current balance so it never drives the wallet negative.
     */
    public function reverseTopup(Payment $payment, float $amount): void
    {
        DB::transaction(function () use ($payment, $amount) {
            $wallet = $this->lockRow($payment->user_id);
            $amount = round(min($amount, (float) $wallet->balance), 2);

            if ($amount <= 0) {
                $this->payments->logTech('warning', 'top-up reversal skipped — wallet balance already spent', [
                    'payment' => $payment->reference,
                ]);

                return;
            }

            $wallet->balance = round((float) $wallet->balance - $amount, 2);
            $wallet->save();

            $this->record($wallet, WalletTransaction::TYPE_ADJUSTMENT, WalletTransaction::DIRECTION_DEBIT, $amount, [
                'payment_id'  => $payment->id,
                'description' => 'Reversal of refunded top-up ' . $payment->reference,
                'metadata'    => ['reason' => 'topup_refund_reversal'],
            ]);

            $this->payments->logTech('info', 'wallet debited for top-up reversal', [
                'payment' => $payment->reference, 'amount' => $amount,
            ]);
        });
    }

    /* ------------------------------------------------ admin adjustment */

    /** Admin manual credit (+) or debit (−) with an audit trail. */
    public function adjust($user, float $signedAmount, User $admin, ?string $note = null): Wallet
    {
        if (abs($signedAmount) < 0.01) {
            throw new PaymentException('The adjustment amount must be at least 0.01.', 'wallet adjust amount ~ 0');
        }

        $userId = is_object($user) ? $user->id : (int) $user;

        return DB::transaction(function () use ($userId, $signedAmount, $admin, $note) {
            $wallet = $this->lockRow($userId);
            $this->assertNotLocked($wallet);

            $direction = $signedAmount > 0 ? WalletTransaction::DIRECTION_CREDIT : WalletTransaction::DIRECTION_DEBIT;
            $amount = round(abs($signedAmount), 2);

            if ($direction === WalletTransaction::DIRECTION_DEBIT) {
                $this->assertUsable($wallet, $amount);
            }

            $wallet->balance = round((float) $wallet->balance + $signedAmount, 2);
            AuditLogger::log($wallet, 'wallet_adjustment');
            $wallet->save();

            $this->record($wallet, WalletTransaction::TYPE_ADJUSTMENT, $direction, $amount, [
                'description' => $note ?: 'Manual adjustment by admin',
                'performed_by' => $admin->id,
                'metadata'    => ['admin' => $admin->id, 'note' => $note],
            ]);

            return $wallet->refresh();
        });
    }

    /** Admin freeze/unfreeze — a locked wallet can neither pay nor be credited. */
    public function setLocked(Wallet $wallet, bool $locked, ?string $reason = null): Wallet
    {
        return DB::transaction(function () use ($wallet, $locked, $reason) {
            $wallet = $this->lockRow($wallet->user_id);
            $wallet->is_locked = $locked;
            $wallet->locked_reason = $locked ? ($reason ?: 'Locked by admin') : null;
            AuditLogger::log($wallet, $locked ? 'wallet_locked' : 'wallet_unlocked');
            $wallet->save();

            return $wallet->refresh();
        });
    }

    /* ------------------------------------------------ internals */

    /** Wallet row locked FOR UPDATE — MUST be called inside a transaction. */
    protected function lockRow(int $userId): Wallet
    {
        $wallet = Wallet::query()->where('user_id', $userId)->lockForUpdate()->first();

        if (!$wallet) {
            try {
                $wallet = Wallet::create(['user_id' => $userId, 'balance' => 0]);
            } catch (QueryException $e) {
                // Unique(user_id) race — another request created it first.
                $wallet = Wallet::query()->where('user_id', $userId)->lockForUpdate()->first();
            }
            if (!$wallet) {
                throw new PaymentException('Your wallet could not be opened. Please try again.', 'wallet create failed for user ' . $userId);
            }
        }

        return $wallet;
    }

    protected function assertNotLocked(Wallet $wallet): void
    {
        if ($wallet->is_locked) {
            throw new PaymentException(
                'This wallet is currently locked. Please contact support.',
                'wallet ' . $wallet->id . ' locked: ' . $wallet->locked_reason
            );
        }
    }

    protected function assertUsable(Wallet $wallet, float $amount): void
    {
        $this->assertNotLocked($wallet);
        if (round((float) $wallet->balance, 2) < round($amount, 2)) {
            throw new PaymentException(
                'Insufficient wallet balance. Available: ' . number_format((float) $wallet->balance, 2) . ' — needed: ' . number_format($amount, 2) . '. Please top up your wallet or choose another payment method.',
                'insufficient wallet balance: ' . $wallet->balance . ' < ' . $amount
            );
        }
    }

    protected function record(Wallet $wallet, string $type, string $direction, float $amount, array $attrs = []): WalletTransaction
    {
        return $wallet->transactions()->create($attrs + [
            'user_id'       => $wallet->user_id,
            'type'          => $type,
            'direction'     => $direction,
            'amount'        => $amount,
            'balance_after' => $wallet->balance,
            'metadata'      => $attrs['metadata'] ?? null,
        ]);
    }
}
