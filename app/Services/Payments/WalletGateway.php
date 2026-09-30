<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;

/**
 * Agent WL — pay-from-balance gateway adapter (PM-014).
 *
 * Follows the same contract as Stripe/BankTransfer: controllers and Blade
 * never talk to gateways directly. The actual settlement (debit + paid
 * transition) is performed by PaymentService::driveGateway via
 * WalletService::payFromWallet() — this adapter only reports capability
 * and never writes payment status itself.
 */
class WalletGateway implements PaymentGatewayContract
{
    protected WalletService $wallets;

    public function __construct(WalletService $wallets)
    {
        $this->wallets = $wallets;
    }

    public function code(): string
    {
        return 'wallet';
    }

    public function name(): string
    {
        return 'Wallet';
    }

    /** Internal balance rails — always available unless the wallet feature is switched off. */
    public function isConfigured(): bool
    {
        return $this->wallets->enabled();
    }

    /**
     * Report wallet capability for the checkout UI. Kind 'wallet' tells
     * PaymentService::driveGateway to settle instantly from balance —
     * no redirect, no client secret, no browser trust involved.
     */
    public function initiate(Payment $payment, ?User $customer = null, array $context = []): array
    {
        return [
            'kind'             => 'wallet',
            'simulated'        => false,
            'available_balance' => $customer ? $this->wallets->balanceFor($customer) : null,
            'message'          => 'Paying from your wallet balance. The order is marked paid the moment the wallet is debited.',
        ];
    }

    /** Balance payments are instant — the ledger row is already the truth. */
    public function verify(Payment $payment): array
    {
        return ['status' => $payment->status, 'raw' => []];
    }

    /** Wallet-paid orders refund back INTO the wallet (original rails). */
    public function refund(Payment $payment, ?float $amount = null): array
    {
        $amount = $amount !== null ? (float) $amount : (float) $payment->amount;

        return $this->wallets->refundToWallet($payment, $amount);
    }
}
