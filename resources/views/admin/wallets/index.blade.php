@extends('admin.layouts.app')

@section('title', 'Customer Wallets')
@section('page_title', 'Customer Wallets')
@section('page_subtitle', 'Wallet balances with full transaction ledgers')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info py-2">
                <i class="fas fa-info-circle mr-1"></i>
                Customers top up their wallet by card and pay orders from the balance instantly. Every balance change is written by the wallet service under a row lock and recorded in an append-only ledger.
            </div>
        </div>
    </div>

    <div class="dp-toolbar card card-outline card-primary">
        <div class="card-body">
            <form class="row align-items-end" method="GET" action="{{ route('admin.wallets.index') }}">
                <div class="col-md-6 col-sm-8 form-group mb-2">
                    <label for="dpFilterQ">Search customer</label>
                    <input type="text" id="dpFilterQ" name="q" class="form-control" value="{{ $q }}" placeholder="Name or email...">
                </div>
                <div class="col-md-3 col-sm-4 form-group mb-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="dp-table-wrap">
        <table class="table table-hover dp-table dp-card-mobile">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($wallets as $w)
                    <tr>
                        <td data-label="Customer">
                            <strong>{{ $w->user_name }}</strong><br>
                            <small class="text-muted">{{ $w->user_email }}</small>
                        </td>
                        <td data-label="Balance">
                            <strong>{{ $w->currency }} {{ number_format((float) $w->balance, 2) }}</strong>
                        </td>
                        <td data-label="Status">
                            @if ($w->is_locked)
                                <span class="badge badge-danger">Locked</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td data-label="Updated">{{ optional($w->updated_at)->format('M d, Y H:i') }}</td>
                        <td class="text-right" data-label="Actions">
                            <a href="{{ route('admin.wallets.show', $w->user_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-wallet mr-1"></i> Open
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No wallets yet — wallets are created the first time a customer opens their wallet page or pays.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $wallets->links() }}</div>
@endsection
