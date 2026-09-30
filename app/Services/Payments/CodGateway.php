<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;

/**
 * Agent WL — Cash on Delivery gateway (PM-017).
 *
 * No online charge: the customer pays in cash when the parcel is delivered.
 * The payment row sits in awaiting_payment until an admin confirms the cash
 * was received (same "mark payment received" path as bank transfers), which
 * then runs the normal paid sync (offer_status/shop order paid).
 */
class CodGateway implements PaymentGatewayContract
{
    public function code(): string
    {
        return 'cod';
    }

    public function name(): string
    {
        return 'Cash on Delivery';
    }

    /** Internal rails — always available. */
    public function isConfigured(): bool
    {
        return true;
    }

    public function initiate(Payment $payment, ?User $customer = null, array $context = []): array
    {
        return [
            'kind'           => 'instructions',
            'simulated'      => false,
            'proof_required' => false,
            'instructions'   => [
                'method'  => 'Cash on Delivery',
                'amount'  => number_format((float) $payment->amount, 2),
                'message' => 'Keep the exact amount ready. Pay in cash when your parcel is delivered — our courier confirms receipt and the order is marked paid automatically after confirmation.',
            ],
        ];
    }

    /** Truth is the ledger row (admin confirms delivery payment). */
    public function verify(Payment $payment): array
    {
        return ['status' => $payment->status, 'raw' => []];
    }

    /** COD refunds are handled manually (cash returned / adjusted) — bookkeeping only. */
    public function refund(Payment $payment, ?float $amount = null): array
    {
        return [
            'ok'          => true,
            'refunded'    => $amount ?? (float) $payment->amount,
            'gateway_ref' => 'MANUAL-COD-' . $payment->reference,
            'error'       => null,
            'simulated'   => true,
        ];
    }
}
