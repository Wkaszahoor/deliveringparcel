@extends('layouts.tailwind.app')

@section('title', 'Orders')
@section('page_title', request('archived') === '1' ? 'Archived Orders' : 'Clients Orders')

@section('content')
<div x-data="{ advOpen: {{ request()->anyFilled(['order_id','name','email','shipfrom','shipto','status']) ? 'true' : 'false' }} }">
    <x-admin.card class="mb-4">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-800">
                @if(request('archived') === '1')
                    Archived Orders <span class="text-slate-400">({{ $order->total() }} order(s))</span>
                @else
                    Clients Orders <span class="text-slate-400">({{ $order->total() }} order(s))</span>
                @endif
            </h3>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin-orders', array_merge(request()->query(), ['archived' => request('archived') === '1' ? '0' : '1'])) }}"
                   class="rounded-md border px-3 py-1.5 text-xs font-medium {{ request('archived') === '1' ? 'border-green-300 text-green-700 hover:bg-green-50' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                    <i class="fas {{ request('archived') === '1' ? 'fa-rotate-left' : 'fa-archive' }}"></i>
                    {{ request('archived') === '1' ? 'Back to Active Orders' : 'Show Archived' }}
                </a>
                <button type="button" @click="advOpen = !advOpen" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-search"></i> Advanced Search
                </button>
            </div>
        </div>

        <div x-show="advOpen" x-cloak class="mb-4 rounded-md bg-slate-50 p-3">
            <form method="GET" action="{{ route('admin-orders') }}" autocomplete="off" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Order ID</label>
                    <input type="text" name="order_id" placeholder="Order ID (e.g. DP-1234 or 123)" value="{{ request('order_id') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Name</label>
                    <input type="text" name="name" placeholder="Client name" value="{{ request('name') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                    <input type="text" name="email" placeholder="Client email" value="{{ request('email') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ship From</label>
                    <input type="text" name="shipfrom" placeholder="Origin country / city" value="{{ request('shipfrom') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Ship To</label>
                    <input type="text" name="shipto" placeholder="Destination country / city" value="{{ request('shipto') }}"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Status</label>
                    @php $statusFilter = request('status'); @endphp
                    <select name="status" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                        <option value="">All Statuses</option>
                        <option value="__none__" {{ $statusFilter === '__none__' ? 'selected' : '' }}>Request Placed (no status)</option>
                        @foreach(['Offer Placed','Offer Accepted','Offer Rejected','Order placed','Order processing','pending','completed','received'] as $st)
                            <option value="{{ $st }}" {{ $statusFilter === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-full flex items-center justify-end gap-2">
                    @if(request('archived') === '1')
                        <input type="hidden" name="archived" value="1">
                    @endif
                    <x-admin.button variant="secondary" tag="a" :href="route('admin-orders')">Reset</x-admin.button>
                    <x-admin.button type="submit"><i class="fas fa-search"></i> Search</x-admin.button>
                </div>
            </form>
        </div>

        @if($order->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                            <th class="py-2 pr-4">Order ID</th>
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4">Email</th>
                            <th class="py-2 pr-4">Ship From</th>
                            <th class="py-2 pr-4">Ship To</th>
                            <th class="py-2 pr-4">Net Total</th>
                            <th class="py-2 pr-4">Approx Weight (g)</th>
                            <th class="py-2 pr-4">Created</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Payment</th>
                            <th class="py-2 pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($order as $add)
                            <tr>
                                <td class="py-2 pr-4"><a href="{{ route('order', $add->id) }}" class="text-brand hover:underline">{{ $add->order_id }}</a></td>
                                <td class="py-2 pr-4">{{ $add->name }}</td>
                                <td class="py-2 pr-4">{{ $add->email }}</td>
                                <td class="py-2 pr-4">{{ $add->shipfrom }}</td>
                                <td class="py-2 pr-4">{{ $add->shipto }}</td>
                                <td class="py-2 pr-4">${{ $add->total }}</td>
                                <td class="py-2 pr-4">{{ $add->approximate_weight }}</td>
                                <td class="py-2 pr-4">{{ $add->created_at }}</td>
                                <td class="py-2 pr-4">
                                    @if ($add->active_tab == 3)
                                        <x-admin.badge color="yellow">{{ $add->order_status }}</x-admin.badge>
                                    @elseif ($add->active_tab == 4)
                                        <x-admin.badge color="green">{{ $add->order_status }}</x-admin.badge>
                                    @elseif ($add->order_status == 'Offer Accepted')
                                        <x-admin.badge color="green">{{ $add->order_status }}</x-admin.badge>
                                    @elseif ($add->order_status == 'Offer Placed')
                                        <x-admin.badge color="blue">{{ $add->order_status }}</x-admin.badge>
                                    @elseif ($add->order_status == 'Order placed')
                                        <x-admin.badge color="slate">{{ $add->order_status }}</x-admin.badge>
                                    @elseif ($add->order_status != '')
                                        <x-admin.badge color="red">{{ $add->order_status }}</x-admin.badge>
                                    @else
                                        <x-admin.badge color="red">Request Placed</x-admin.badge>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    @php
                                        /* Operational payment/progress column — clearer than the
                                           raw stepper status for day-to-day triage. */
                                        $ps = $add->payment_status ?? null;
                                        if ($ps === 'paid') {
                                            $pl = ((int) $add->tracking_status === 1) ? 'Package Arrived' : 'Paid — Waiting Tracking detail';
                                            $pc = 'green';
                                        } elseif (in_array($ps, ['awaiting_payment', 'method_selected', 'pending'])) {
                                            $pl = 'Awaiting Payment'; $pc = 'yellow';
                                        } elseif ($ps === 'awaiting_verification') {
                                            $pl = 'Receipt Verification'; $pc = 'blue';
                                        } elseif ($ps === 'processing') {
                                            $pl = 'Payment Processing'; $pc = 'blue';
                                        } elseif (in_array($ps, ['failed', 'cancelled'])) {
                                            $pl = 'Payment ' . ucfirst($ps); $pc = 'red';
                                        } elseif ($ps !== null) {
                                            $pl = ucfirst(str_replace('_', ' ', $ps)); $pc = 'slate';
                                        } else {
                                            $pl = 'No Payment Yet'; $pc = 'slate';
                                        }
                                    @endphp
                                    <x-admin.badge :color="$pc">{{ $pl }}</x-admin.badge>
                                </td>
                                <td class="py-2 pr-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('order', $add->id) }}" class="text-slate-400 hover:text-brand" title="View order"><i class="fas fa-history"></i></a>

                                        @if(request('archived') === '1')
                                            <form method="POST" action="{{ route('archive.restore', $add->id) }}" class="inline"
                                                  onsubmit="return confirm('Restore order #{{ $add->order_id }} back to the active list?')">
                                                @csrf
                                                <input type="hidden" name="_return" value="{{ url('admin-orders', ['archived' => '1']) }}">
                                                <button type="submit" class="rounded-md border border-green-300 px-2 py-1 text-xs font-medium text-green-700 hover:bg-green-50" title="Restore order">
                                                    <i class="fas fa-rotate-left"></i> Restore
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('archive.order', $add->id) }}" class="inline"
                                                  onsubmit="return confirm('Archive order #{{ $add->order_id }}?\nIt will be hidden from the orders list (recoverable from Show Archived).')">
                                                @csrf
                                                <input type="hidden" name="_return" value="{{ url('admin-orders') }}">
                                                <button type="submit" class="rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50" title="Archive this order">
                                                    <i class="fas fa-archive"></i> Archive
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $order->appends(request()->query())->links('pagination.tailwind-admin') }}
            </div>
        @else
            <p class="text-center text-slate-400">No Record Found</p>
        @endif
    </x-admin.card>
</div>
@endsection
