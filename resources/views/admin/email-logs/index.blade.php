@extends('admin.layouts.app')

@section('title', 'Email Logs')
@section('page_title', 'Email Logs')
@section('page_subtitle', 'Business-level delivery history — queued / sent / failed, attempts and errors, with retry.')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row w-100">
                    <div class="col-md-8">
                        <span class="badge badge-success p-1">Sent today: {{ $stats['sent'] }}</span>
                        <span class="badge badge-warning p-1 ml-1">Queued: {{ $stats['queued'] }}</span>
                        <span class="badge badge-danger p-1 ml-1">Failed (all time): {{ $stats['failed'] }}</span>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('admin.queue.monitor') }}" class="btn btn-sm btn-outline-info">Queue Monitor</a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" class="form-inline mb-3">
                    <select name="status" class="form-control form-control-sm mr-2">
                        <option value="">All statuses</option>
                        @foreach (['sent', 'failed', 'queued'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="to" class="form-control form-control-sm mr-2" placeholder="Recipient email" value="{{ request('to') }}">
                    <input type="text" name="key" class="form-control form-control-sm mr-2" placeholder="Template key" value="{{ request('key') }}">
                    <input type="number" name="order" class="form-control form-control-sm mr-2" placeholder="Order ID" value="{{ request('order') }}">
                    <button class="btn btn-sm btn-primary mr-1">Filter</button>
                    <a href="{{ route('admin.email-logs.index') }}" class="btn btn-sm btn-default border">Reset</a>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr><th>#</th><th>Queued at</th><th>Type</th><th>Recipient</th><th>Order</th><th>Subject</th><th>Status</th><th>Att.</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $l)
                            <tr>
                                <td>{{ $l->id }}</td>
                                <td class="small">{{ optional($l->created_at)->format('M d, H:i') }}</td>
                                <td><code class="small">{{ $l->template_key }}</code></td>
                                <td class="small">{{ $l->to_email }}</td>
                                <td>{{ $l->order_id ?: '—' }}</td>
                                <td class="small">{{ \Illuminate\Support\Str::limit($l->subject, 50) }}</td>
                                <td>
                                    <span class="badge badge-{{ $l->status === 'sent' ? 'success' : ($l->status === 'failed' ? 'danger' : 'warning') }} p-1">{{ $l->status }}</span>
                                </td>
                                <td>{{ $l->attempts }}</td>
                                <td>
                                    <a href="{{ route('admin.email-logs.show', $l) }}" class="btn btn-xs btn-info">View</a>
                                    @if (in_array($l->status, ['failed', 'queued']))
                                    <form action="{{ route('admin.email-logs.retry', $l) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button class="btn btn-xs btn-warning">Retry</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-muted p-3">No emails logged yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $logs->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
