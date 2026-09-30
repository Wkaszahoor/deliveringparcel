<div x-data="{ open: false }" class="inline">
    <button type="button" @click="open = true" class="text-red-600 hover:text-red-800" title="Delete">
        <i class="fas fa-trash"></i>
    </button>

    <x-admin.modal title="Delete Confirmation">
        <p class="mb-4 text-sm text-slate-600">Are you sure you want to delete this quote request?</p>
        <form action="{{ route('freequote.destroy', $quote->id) }}" method="POST" class="flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <x-admin.button variant="secondary" type="button" @click="open = false">No</x-admin.button>
            <x-admin.button variant="danger" type="submit">Yes, delete</x-admin.button>
        </form>
    </x-admin.modal>
</div>
