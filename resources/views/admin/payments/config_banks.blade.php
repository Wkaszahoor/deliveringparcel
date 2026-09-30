@extends('layouts.tailwind.app')

@section('title', 'Bank Accounts Management')
@section('page_title', 'Bank Accounts')
@section('page_subtitle', 'Manage bank accounts for bank transfer payments')

@section('content')
    <x-admin.card title="Bank Accounts" class="mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Sort</th>
                        <th class="py-2 pr-4">Bank Name</th>
                        <th class="py-2 pr-4">Account Title</th>
                        <th class="py-2 pr-4">Account Number/IBAN</th>
                        <th class="py-2 pr-4">Currency</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if($accounts->isEmpty())
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                No bank accounts found. Add your first bank account below.
                            </td>
                        </tr>
                    @else
                        @foreach($accounts as $account)
                            <tr>
                                <td class="py-2 pr-4 text-center">{{ $account->sort_order }}</td>
                                <td class="py-2 pr-4"><strong>{{ $account->bank_name }}</strong></td>
                                <td class="py-2 pr-4">{{ $account->account_title }}</td>
                                <td class="py-2 pr-4">
                                    @if($account->account_number)
                                        {{ $account->account_number }}
                                    @elseif($account->iban)
                                        {{ $account->iban }}
                                    @else
                                        <span class="text-slate-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">{{ $account->currency }}</td>
                                <td class="py-2 pr-4">
                                    @if($account->is_enabled)
                                        <x-admin.badge color="green">Enabled</x-admin.badge>
                                    @else
                                        <x-admin.badge color="slate">Disabled</x-admin.badge>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button"
                                            class="inline-flex items-center rounded-md border border-blue-300 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"
                                            onclick="editBankAccount({{ $account->id }})"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            class="inline-flex items-center rounded-md border border-yellow-300 px-2 py-1 text-xs font-medium text-yellow-700 hover:bg-yellow-50"
                                            onclick="toggleBankAccount({{ $account->id }})"
                                            title="{{ $account->is_enabled ? 'Disable' : 'Enable' }}">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        <button type="button"
                                            class="inline-flex items-center rounded-md border border-red-300 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50"
                                            onclick="deleteBankAccount({{ $account->id }}, '{{ $account->bank_name }}')"
                                            title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </x-admin.card>

    <x-admin.card title="Add New Bank Account">
        <form id="bank-account-form" action="{{ route('admin.payments.config.banks.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-x-4 md:grid-cols-2">
                <x-admin.input label="Bank Name" name="bank_name" required />
                <x-admin.input label="Account Title" name="account_title" required />
            </div>

            <div class="grid grid-cols-1 gap-x-4 md:grid-cols-2">
                <x-admin.input label="Account Number" name="account_number" />
                <x-admin.input label="IBAN" name="iban" />
            </div>

            <div class="grid grid-cols-1 gap-x-4 md:grid-cols-3">
                <x-admin.input label="Branch" name="branch" />
                <x-admin.input label="SWIFT Code" name="swift_code" />
                <div class="mb-3">
                    <label for="currency" class="mb-1 block text-sm font-medium text-slate-700">Currency <span class="text-red-500">*</span></label>
                    <select id="currency" name="currency" required
                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                        <option value="CAD">CAD</option>
                        <option value="AUD">AUD</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="instructions" class="mb-1 block text-sm font-medium text-slate-700">Additional Instructions</label>
                <textarea id="instructions" name="instructions" rows="3"
                    class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-light"></textarea>
            </div>

            <div class="grid grid-cols-1 gap-x-4 md:grid-cols-2">
                <div class="mb-3 flex items-start gap-2">
                    <input type="checkbox" id="is_enabled" name="is_enabled" value="1" checked
                        class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand focus:ring-brand-light">
                    <label for="is_enabled" class="text-sm text-slate-700">Enable this bank account for payments</label>
                </div>
                <x-admin.input label="Sort Order (display priority)" name="sort_order" type="number" value="100" />
            </div>

            <div class="flex items-center gap-2">
                <x-admin.button type="submit">
                    <i class="fas fa-plus"></i> Add Bank Account
                </x-admin.button>
            </div>
        </form>
    </x-admin.card>
@endsection

{{-- This page's JS uses only fetch()/DOM APIs — no jQuery, DataTables, toastr or SweetAlert2 —
     but is still pushed to @stack('admin_scripts') for consistency with the rest of the module
     and so it always executes after the layout's core scripts. --}}
@push('admin_scripts')
<script>
function editBankAccount(id) {
    // Load bank account data via AJAX and populate form
    fetch('{{ route('admin.payments.config.banks.edit', '__ID__') }}'.replace('__ID__', id))
        .then(response => response.json())
        .then(data => {
            // Populate form fields
            document.getElementById('bank_name').value = data.bank_name || '';
            document.getElementById('account_title').value = data.account_title || '';
            document.getElementById('account_number').value = data.account_number || '';
            document.getElementById('iban').value = data.iban || '';
            document.getElementById('branch').value = data.branch || '';
            document.getElementById('swift_code').value = data.swift_code || '';
            document.getElementById('currency').value = data.currency || 'USD';
            document.getElementById('instructions').value = data.instructions || '';
            document.getElementById('sort_order').value = data.sort_order || 100;
            document.getElementById('is_enabled').checked = data.is_enabled;

            // Change form to update mode
            const form = document.getElementById('bank-account-form');
            form.action = '{{ route('admin.payments.config.banks.update', '__ID__') }}'.replace('__ID__', id);
            form.method = 'POST';

            // Add PUT method override
            let methodField = document.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';

            // Change submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Update Bank Account';

            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error loading bank account:', error);
            alert('Failed to load bank account data.');
        });
}

function toggleBankAccount(id) {
    if (confirm('Are you sure you want to toggle the status of this bank account?')) {
        fetch('{{ route('admin.payments.config.banks.toggle', '__ID__') }}'.replace('__ID__', id), {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update bank account status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update bank account status.');
        });
    }
}

function deleteBankAccount(id, bankName) {
    if (confirm(`Are you sure you want to delete the bank account "${bankName}"? This action cannot be undone.`)) {
        fetch('{{ route('admin.payments.config.banks.destroy', '__ID__') }}'.replace('__ID__', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to delete');
        })
        .then(data => {
            if (data.success || response.ok) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete bank account.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete bank account.');
        });
    }
}

// Reset form when clicking "Add New" (optional enhancement)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bank-account-form');
    const resetBtn = document.createElement('button');
    resetBtn.type = 'button';
    resetBtn.className = 'inline-flex items-center gap-1.5 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50';
    resetBtn.innerHTML = '<i class="fas fa-redo mr-1"></i> Reset Form';
    resetBtn.onclick = function() {
        form.reset();
        form.action = '{{ route('admin.payments.config.banks.store') }}';
        form.method = 'POST';

        const methodField = document.querySelector('input[name="_method"]');
        if (methodField) methodField.remove();

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-plus mr-1"></i> Add Bank Account';
    };

    const submitBtn = form.querySelector('button[type="submit"]').parentNode;
    submitBtn.appendChild(resetBtn);
});
</script>
@endpush
