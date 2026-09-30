@extends('admin.layouts.app')

@section('title', 'Wallet — ' . $user->name)
@section('page_title', 'Wallet')
@section('page_subtitle', $user->name . ' <' . $user->email . '>')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline {{ $wallet->is_locked ? 'card-danger' : 'card-primary' }}">
                <div class="card-header">
                    <h3 class="card-title">Balance</h3>
                </div>
                <div class="card-body">
                    <div style="font-size:2rem;font-weight:700">
                        {{ $wallet->currency }} {{ number_format((float) $wallet->balance, 2) }}
                    </div>
                    @if ($wallet->is_locked)
                        <p class="text-danger mb-1"><strong>Locked:</strong> {{ $wallet->locked_reason }}</p>
                    @endif
                    <p class="text-muted mb-0 small">Wallet #{{ $wallet->id }} — created {{ optional($wallet->created_at)->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title">Manual adjustment</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.wallets.adjust', $user->id) }}">
                        @csrf
                        <div class="form-group">
                            <label for="wa-amount">Amount (use − to debit)</label>
                            <input id="wa-amount" type="number" name="amount" step="0.01" required
                                   class="form-control" placeholder="e.g. 25.00 or -10.00">
                        </div>
                        <div class="form-group">
                            <label for="wa-note">Note (stored in the ledger + audit log)</label>
                            <input id="wa-note" type="text" name="note" maxlength="1000" class="form-control"
                                   placeholder="Reason for the adjustment">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" {{ $wallet->is_locked ? 'disabled' : '' }}>
                            <i class="fas fa-balance-scale mr-1"></i> Apply adjustment
                        </button>
                        @if ($wallet->is_locked)
                            <small class="text-muted">Unlock the wallet first to make adjustments.</small>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Freeze</h3></div>
                <div class="card-body">
                    @if ($wallet->is_locked)
                        <form method="POST" action="{{ route('admin.wallets.unlock', $user->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-unlock mr-1"></i> Unlock wallet
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.wallets.lock', $user->id) }}">
                            @csrf
                            <div class="form-group">
                                <label for="wl-reason">Reason</label>
                                <input id="wl-reason" type="text" name="reason" maxlength="255" class="form-control"
                                       placeholder="Why is this wallet being locked?">
                            </div>
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-lock mr-1"></i> Lock wallet
                            </button>
                        </form>
                    @endif
                    <p class="text-muted small mt-2 mb-0">A locked wallet can neither pay orders nor receive credits/top-ups.</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Transaction ledger (append-only)</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover dp-table dp-card-mobile">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Balance after</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transactions as $t)
                                <tr>
                                    <td data-label="Date">{{ optional($t->created_at)->format('M d, Y H:i') }}</td>
                                    <td data-label="Type">{{ $t->label() }}</td>
                                    <td data-label="Description">
                                        {{ $t->description ?: ($t->reference ?: '—') }}
                                        @if ($t->payment_id)<small class="text-muted"> · payment #{{ $t->payment_id }}</small>@endif
                                    </td>
                                    <td data-label="Amount" class="text-right" style="color:{{ $t->direction === 'credit' ? '#28a745' : '#dc3545' }}">
                                        {{ $t->direction === 'credit' ? '+' : '−' }} {{ number_format((float) $t->amount, 2) }}
                                    </td>
                                    <td data-label="Balance after" class="text-right">{{ number_format((float) $t->balance_after, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3">{{ $transactions->links() }}</div>
        </div>
    </div>
@endsection
