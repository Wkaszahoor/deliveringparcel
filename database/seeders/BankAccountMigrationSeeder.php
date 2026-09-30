<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountMigrationSeeder extends Seeder
{
    /**
     * Migrate existing bank settings from settings table to bank_accounts table.
     *
     * This seeder should be run after creating the bank_accounts table to
     * preserve any existing bank configuration that was stored in settings.
     *
     * @return void
     */
    public function run()
    {
        // Only run if no bank accounts exist yet
        if (BankAccount::count() > 0) {
            $this->command->info('Bank accounts already exist. Skipping migration.');
            return;
        }

        // Get existing bank settings
        $bankName = Setting::get('bank_name');
        $accountTitle = Setting::get('bank_account_title');
        $accountNumber = Setting::get('bank_account_number');
        $iban = Setting::get('bank_iban');
        $currency = Setting::get('business_currency', 'USD');

        // Only create bank account if we have at least a bank name
        if (empty($bankName)) {
            $this->command->info('No bank settings found to migrate.');
            return;
        }

        // Create the bank account from existing settings
        BankAccount::create([
            'bank_name' => $bankName,
            'account_title' => $accountTitle ?? 'Account',
            'account_number' => $accountNumber,
            'iban' => $iban,
            'branch' => null,
            'swift_code' => null,
            'currency' => $currency,
            'instructions' => null,
            'is_enabled' => true,
            'sort_order' => 100,
        ]);

        $this->command->info('Successfully migrated bank settings to bank account.');
        $this->command->info('Bank: ' . $bankName);
        $this->command->info('You can now manage multiple bank accounts in Admin > Payments > Config > Banks');
    }
}
