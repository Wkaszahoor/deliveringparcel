<?php

namespace App\Http\Controllers\Admin\Wallets;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Payments\PaymentException;
use App\Services\Payments\WalletService;
use Illuminate\Http\Request;

/**
 * Agent WL — admin wallet management (PM-014).
 *
 * Read-only balance/ledger views + two guarded write actions:
 *  - adjust (manual credit/debit, audited via AuditLogger inside the service)
 *  - lock/unlock (freeze a wallet — blocks paying AND crediting)
 * Wallet balances are never edited directly; every mutation goes through
 * WalletService so the append-only transaction ledger stays complete.
 */
class WalletController extends Controller
{
    protected WalletService $wallets;

    public function __construct(WalletService $wallets)
    {
        $this->wallets = $wallets;
    }

    /** GET /admin/wallets — all wallets with owner + totals. */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $wallets = \App\Models\Wallet::query()
            ->join('users as u', 'u.id', '=', 'wallets.user_id')
            ->select('wallets.*', 'u.name as user_name', 'u.email as user_email', 'u.type as user_type')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('u.name', 'like', "%{$q}%")
                        ->orWhere('u.email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('wallets.balance')
            ->paginate(25)
            ->withQueryString();

        return view('admin.wallets.index', [
            'wallets'  => $wallets,
            'q'        => $q,
            'currency' => (string) \App\Models\Setting::get('business_currency', 'USD'),
        ]);
    }

    /** GET /admin/wallets/{user} — one wallet: balance, ledger, actions. */
    public function show(Request $request, User $user)
    {
        $wallet = $this->wallets->walletFor($user);
        $transactions = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.wallets.show', [
            'user'         => $user,
            'wallet'       => $wallet,
            'transactions' => $transactions,
            'currency'     => $wallet->currency,
        ]);
    }

    /** POST /admin/wallets/{user}/adjust — body: amount (signed), note? */
    public function adjust(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|not_in:0|between:-1000000,1000000',
            'note'   => 'nullable|string|max:1000',
        ]);

        try {
            $wallet = $this->wallets->adjust($user, (float) $data['amount'], $request->user(), $data['note'] ?? null);
        } catch (PaymentException $e) {
            return redirect()->route('admin.wallets.show', $user->id)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.wallets.show', $user->id)
            ->with('success', 'Wallet adjusted — new balance: ' . number_format((float) $wallet->balance, 2) . ' ' . $wallet->currency);
    }

    /** POST /admin/wallets/{user}/lock — body: reason? */
    public function lock(Request $request, User $user)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);
        $this->wallets->setLocked($this->wallets->walletFor($user), true, $data['reason'] ?? null);

        return redirect()->route('admin.wallets.show', $user->id)->with('success', 'Wallet locked.');
    }

    /** POST /admin/wallets/{user}/unlock */
    public function unlock(Request $request, User $user)
    {
        $this->wallets->setLocked($this->wallets->walletFor($user), false);

        return redirect()->route('admin.wallets.show', $user->id)->with('success', 'Wallet unlocked.');
    }
}
