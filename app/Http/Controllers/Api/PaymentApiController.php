<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

/**
 * Payment API Controller
 * 
 * Provides API endpoints for payment-related frontend integration
 */
class PaymentApiController extends Controller
{
    /**
     * Get bank account details for bank transfer payments
     *
     * Returns ALL enabled accounts in `accounts` (id + full details) plus the
     * first one in `account` for backward compatibility with older pages.
     */
    public function bankAccountDetails(): JsonResponse
    {
        $bankAccounts = BankAccount::enabled()->ordered()->get();

        if ($bankAccounts->isEmpty()) {
            return response()->json([
                'ok' => false,
                'account' => null,
                'accounts' => [],
                'message' => 'No enabled bank account found'
            ], 404);
        }

        $mapAccount = function (BankAccount $a): array {
            return [
                'id' => $a->id,
                'bank_name' => $a->bank_name,
                'account_title' => $a->account_title,
                'account_number' => $a->account_number,
                'iban' => $a->iban,
                'branch' => $a->branch,
                'swift_code' => $a->swift_code,
                'intermediary_bic' => $a->intermediary_bic,
                'recipient_address' => $a->recipient_address,
                'uk_account_number' => $a->uk_account_number,
                'uk_sort_code' => $a->uk_sort_code,
                'currency' => $a->currency,
                'instructions' => $a->instructions,
            ];
        };

        return response()->json([
            'ok' => true,
            'account' => $mapAccount($bankAccounts->first()),
            'accounts' => $bankAccounts->map($mapAccount)->values()->all(),
        ]);
    }

    /**
     * Get Stripe configuration for frontend Elements integration
     * 
     * @return JsonResponse
     */
    public function stripeConfig(): JsonResponse
    {
        $publishableKey = trim((string) config('services.stripe.key'));

        return response()->json([
            'ok' => true,
            'publishable_key' => $publishableKey ?: null,
            'configured' => !empty($publishableKey),
        ]);
    }
}
