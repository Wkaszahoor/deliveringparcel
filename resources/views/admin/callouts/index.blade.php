@extends('admin.layouts.app')

@section('title', 'Order Callouts')
@section('page_title', 'Order Callouts')
@section('page_subtitle', 'Customer-facing callout cards on the order pages — edit, reorder, disable or extend per order status.')

@section('content')
<div class="row">
    <div class="col-12 mb-3 text-right">
        <a href="{{ route('admin.callouts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> New callout
        </a>
    </div>

    @foreach (\App\Models\OrderCallout::SLOTS as $slotKey => $slotLabel)
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tag mr-1"></i> {{ $slotLabel }}</h3>
                <div class="card-tools"><code>{{ $slotKey }}</code></div>
            </div>
            <div class="card-body p-0">
                @if (isset($callouts[$slotKey]) && $callouts[$slotKey]->count())
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:90px">Sort</th>
                            <th style="width:110px">Style</th>
                            <th style="width:160px">Heading</th>
                            <th>Text</th>
                            <th style="width:130px">Enabled</th>
                            <th style="width:170px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($callouts[$slotKey] as $c)
                        <tr>
                            <td>{{ $c->sort_order }}</td>
                            <td><span class="badge badge-{{ $c->style === 'danger' ? 'danger' : ($c->style === 'warning' ? 'warning' : ($c->style === 'success' ? 'success' : 'info')) }}">{{ $c->style }}</span></td>
                            <td>{{ $c->heading }}</td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($c->body, 140) }}</td>
                            <td>
                                <form action="{{ route('admin.callouts.toggle', $c) }}" method="POST">
                                    @csrf @method('POST')
                                    <button type="submit" class="btn btn-xs btn-{{ $c->is_enabled ? 'success' : 'secondary' }}">
                                        {{ $c->is_enabled ? 'Enabled' : 'Disabled' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.callouts.edit', $c) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <form action="{{ route('admin.callouts.destroy', $c) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this callout?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="p-3 mb-0 text-muted">No callouts — the card is hidden on this stage until you add one.</p>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    @php($customGroups = $callouts->filter(fn($rows, $key) => !array_key_exists($key, \App\Models\OrderCallout::SLOTS)))
    @if ($customGroups->isNotEmpty())
    <div class="col-12 mt-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-1"></i> Custom status keys (new-design order page)</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr><th>Status key</th><th style="width:110px">Style</th><th>Text</th><th style="width:130px">Enabled</th><th style="width:170px">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($customGroups as $key => $rows)
                        @foreach ($rows as $c)
                        <tr>
                            <td><code>{{ $key }}</code></td>
                            <td>{{ $c->style }}</td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($c->body, 140) }}</td>
                            <td>
                                <form action="{{ route('admin.callouts.toggle', $c) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-{{ $c->is_enabled ? 'success' : 'secondary' }}">{{ $c->is_enabled ? 'Enabled' : 'Disabled' }}</button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.callouts.edit', $c) }}" class="btn btn-xs btn-warning">Edit</a>
                                <form action="{{ route('admin.callouts.destroy', $c) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this callout?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
