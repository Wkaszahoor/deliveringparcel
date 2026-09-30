<?php

namespace App\Services\Payments;

use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;

/**
 * Agent PM — bank transfer adapter (PM-008/Phase 6).
 *
 * - Instructions now use BankAccount model (Phase 6) instead of settings.
 * - Falls back to legacy settings if no bank accounts exist.
 * - The customer's payment reference doubles as the transfer reference so
 *   the bank statement can be reconciled with the ledger row.
 * - Truth about "paid" arrives ONLY from an ADMIN verification of the
 *   uploaded proof — uploads NEVER auto-mark paid (PM-008/PM-013).
 */
class BankTransferGateway implements PaymentGatewayContract
{
    public function code(): string
    {
        return 'bank_transfer';
    }

    public function name(): string
    {
        return 'Bank transfer';
    }

    public function isConfigured(): bool
    {
        // Check if we have enabled bank accounts, otherwise fallback to settings
        if (BankAccount::enabled()->exists()) {
            return true;
        }

        // Legacy fallback: at least bank name + account/IBAN must be present
        return trim((string) Setting::get('bank_name')) !== ''
            && (trim((string) Setting::get('bank_account_number', '')) !== '' 
                || trim((string) Setting::get('bank_iban', '')) !== '');
    }

    public function initiate(Payment $payment, ?User $customer = null, array $context = []): array
    {
        // Client may pick a specific account (order offer page selector);
        // fall back to the first enabled account otherwise.
        $requestedId = (int) ($context['bank_account_id'] ?? 0);
        $bankAccount = $requestedId
            ? BankAccount::enabled()->ordered()->find($requestedId)
            : null;

        if (!$bankAccount) {
            $bankAccount = BankAccount::enabled()->ordered()->first();
        }

        // If no bank accounts exist, fall back to legacy settings
        if (!$bankAccount) {
            return [
                'kind'         => 'instructions',
                'instructions' => $this->legacyInstructions($payment),
                'proof_required' => true,
                'simulated'    => false,
                'fallback_mode' => true,
            ];
        }

        // Store the bank account association so admin verification knows
        // exactly which account the customer was told to pay.
        $payment->bank_account_id = $bankAccount->id;
        $payment->save();

        return [
            'kind'         => 'instructions',
            'instructions' => $this->instructionsFromAccount($bankAccount, $payment),
            'proof_required' => true,
            'simulated'    => false,
            'bank_account_id' => $bankAccount->id,
        ];
    }

    /** Get instructions from BankAccount model (Phase 6) */
    public function instructionsFromAccount(BankAccount $account, Payment $payment): array
    {
        $instructions = [
            'bank_name'        => $account->bank_name,
            'account_title'    => $account->account_title,
            'amount'           => (float) $payment->amount,
            'currency'         => (string) $payment->currency,
            'transfer_reference' => $payment->reference,
        ];

        // Add account number or IBAN
        if ($account->account_number) {
            $instructions['account_number'] = $account->account_number;
        }
        if ($account->iban) {
            $instructions['iban'] = $account->iban;
        }

        // Add optional fields
        if ($account->branch) {
            $instructions['branch'] = $account->branch;
        }
        if ($account->swift_code) {
            $instructions['swift_code'] = $account->swift_code;
        }
        if ($account->intermediary_bic) {
            $instructions['intermediary_bic'] = $account->intermediary_bic;
        }
        if ($account->recipient_address) {
            $instructions['recipient_address'] = $account->recipient_address;
        }
        if ($account->uk_account_number) {
            $instructions['uk_account_number'] = $account->uk_account_number;
        }
        if ($account->uk_sort_code) {
            $instructions['uk_sort_code'] = $account->uk_sort_code;
        }
        if ($account->instructions) {
            $instructions['additional_instructions'] = $account->instructions;
        }

        return $instructions;
    }

    /** Legacy fallback: get instructions from settings */
    protected function legacyInstructions(Payment $payment): array
    {
        return [
            'bank_name'        => (string) Setting::get('bank_name', ''),
            'account_title'    => (string) Setting::get('bank_account_title', ''),
            'iban'             => (string) Setting::get('bank_iban', ''),
            'account_number'   => (string) Setting::get('bank_account_number', ''),
            'amount'           => (float) $payment->amount,
            'currency'         => (string) $payment->currency,
            'transfer_reference' => $payment->reference,
        ];
    }

    /** Reconcilable instruction block rendered on the pay page */
    public function instructions(Payment $payment): array
    {
        // If payment has a bank account associated, use it
        if ($payment->bank_account_id) {
            $bankAccount = BankAccount::find($payment->bank_account_id);
            if ($bankAccount) {
                return $this->instructionsFromAccount($bankAccount, $payment);
            }
        }

        // Otherwise get the first enabled account or fall back to settings
        $bankAccount = BankAccount::enabled()->ordered()->first();
        if ($bankAccount) {
            return $this->instructionsFromAccount($bankAccount, $payment);
        }

        return $this->legacyInstructions($payment);
    }

    /**
     * Manual gateway: the only "verify" is the admin action on the proof.
     * We deliberately do not invent a status here.
     */
    public function verify(Payment $payment): array
    {
        return ['status' => $payment->status, 'raw' => ['manual' => true]];
    }

    /**
     * Manual gateway: refunds are bookkeeping entries (bank reversal done
     * out-of-band); PaymentService records the ledger state transition.
     */
    public function refund(Payment $payment, ?float $amount = null): array
    {
        return [
            'ok'       => true,
            'refunded' => $amount ?? (float) $payment->amount,
            'gateway_ref' => 'MANUAL-' . $payment->reference,
            'error'    => null,
            'manual'   => true,
        ];
    }
}
