<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\PaymentMethodRule;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        if (PaymentMethod::exists()) {
            return;
        }

        $stripe = PaymentMethod::create([
            'code'        => 'stripe',
            'name'        => 'Card payment (Stripe)',
            'gateway'     => 'stripe',
            'description' => 'Pay securely by card — we never store card details.',
            'is_enabled'  => true,
            'priority'    => 10,
            'min_amount'  => 1.00,
            'max_amount'  => null,
        ]);

        $bank = PaymentMethod::create([
            'code'        => 'bank_transfer',
            'name'        => 'Bank transfer',
            'gateway'     => 'bank_transfer',
            'description' => 'Transfer to our business account and upload the proof; verified by our team.',
            'is_enabled'  => true,
            'priority'    => 20,
            'min_amount'  => null,
            'max_amount'  => null,
        ]);

        /* Service-context routing rules (PM-001/PM-014) — admin editable. */
        foreach (['ship_for_me', 'custom'] as $context) {
            PaymentMethodRule::create(['service_context' => $context, 'payment_method_id' => $stripe->id, 'is_allowed' => true, 'priority' => 1]);
            PaymentMethodRule::create(['service_context' => $context, 'payment_method_id' => $bank->id, 'is_allowed' => true, 'priority' => 2]);
        }
        foreach (['shop_for_me', 'default'] as $context) {
            PaymentMethodRule::create(['service_context' => $context, 'payment_method_id' => $bank->id, 'is_allowed' => true, 'priority' => 1]);
            PaymentMethodRule::create(['service_context' => $context, 'payment_method_id' => $stripe->id, 'is_allowed' => true, 'priority' => 2]);
        }

        /* Bank instructions shown at checkout (PM-008) via Settings. */
        $bankKeys = [
            'bank_name'          => 'First National Bank',
            'bank_account_title' => 'DeliveringParcel LLC',
            'bank_iban'          => 'US12 ABCD 3456 EFGH 7890',
            'bank_account_number' => '000123456789',
            'bank_reference_prefix' => 'DP',
        ];
        foreach ($bankKeys as $key => $value) {
            try {
                Setting::set($key, $value, 'business');
            } catch (\Throwable $e) {
                // Setting store signature variance — fall back to direct model use.
                \DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'business', 'type' => 'string', 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
