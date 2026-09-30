<?php

namespace App\Http\Controllers\Admin\Payments;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Agent PM — Bank Account Management Controller
 * 
 * CRUD operations for managing multiple bank accounts that can be used
 * for bank transfer payments in the payment system.
 */
class BankAccountController extends Controller
{
    /**
     * Display a listing of bank accounts.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $accounts = BankAccount::ordered()->get();
        
        return view('admin.payments.config_banks', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Store a newly created bank account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_title' => 'required|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
            'instructions' => 'nullable|string',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer|min:0|max:9999',
        ]);

        // Set default values
        $validated['is_enabled'] = $request->has('is_enabled');
        $validated['sort_order'] = $validated['sort_order'] ?? 100;
        $validated['currency'] = $validated['currency'] ?? 'USD';

        BankAccount::create($validated);

        return redirect()
            ->route('admin.payments.config.banks')
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Show the form for editing the specified bank account.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(BankAccount $bankAccount)
    {
        return response()->json($bankAccount);
    }

    /**
     * Update the specified bank account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_title' => 'required|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'required|string|max:3',
            'instructions' => 'nullable|string',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer|min:0|max:9999',
        ]);

        $validated['is_enabled'] = $request->has('is_enabled');

        $bankAccount->update($validated);

        return redirect()
            ->route('admin.payments.config.banks')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Remove the specified bank account.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(BankAccount $bankAccount)
    {
        // Check if bank account is being used by any payments
        if ($bankAccount->payments()->exists()) {
            return redirect()
                ->route('admin.payments.config.banks')
                ->with('error', 'Cannot delete bank account that is being used by payments.');
        }

        $bankAccount->delete();

        return redirect()
            ->route('admin.payments.config.banks')
            ->with('success', 'Bank account deleted successfully.');
    }

    /**
     * Toggle the enabled status of a bank account.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function toggle(BankAccount $bankAccount)
    {
        $bankAccount->update([
            'is_enabled' => !$bankAccount->is_enabled
        ]);

        return redirect()
            ->route('admin.payments.config.banks')
            ->with('success', 'Bank account status updated successfully.');
    }
}
