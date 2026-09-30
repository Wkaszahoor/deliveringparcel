@if (session('success'))
    <x-admin.alert type="success">{{ session('success') }}</x-admin.alert>
@endif
@if (session('error'))
    <x-admin.alert type="error">{{ session('error') }}</x-admin.alert>
@endif
@if (($errors ?? collect())->any())
    <x-admin.alert type="error">
        <ul class="list-disc space-y-0.5 pl-4">
            @foreach (($errors ?? collect())->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif
