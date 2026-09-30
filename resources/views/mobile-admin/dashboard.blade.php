@extends('admin.layouts.app')

@section('title', 'Mobile App · Overview')
@section('page_title', 'Mobile App Management')
@section('page_subtitle', 'Control what the mobile apps show — screens, labels, colors, features and notifications — without touching any web order-flow file.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('mobile.admin.screens.index') }}" class="small-box bg-info" style="text-decoration:none">
            <div class="inner"><h3>{{ $stats['screens'] }}</h3><p>Screens configured</p></div>
            <div class="icon"><i class="fas fa-mobile-screen"></i></div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('mobile.admin.labels.index') }}" class="small-box bg-success" style="text-decoration:none">
            <div class="inner"><h3>{{ $stats['labels'] }}</h3><p>Section labels</p></div>
            <div class="icon"><i class="fas fa-tags"></i></div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('mobile.admin.features.index') }}" class="small-box bg-warning" style="text-decoration:none">
            <div class="inner"><h3>{{ $stats['flags'] }}</h3><p>Feature flags</p></div>
            <div class="icon"><i class="fas fa-flag"></i></div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('mobile.admin.notifications.index') }}" class="small-box bg-danger" style="text-decoration:none">
            <div class="inner"><h3>{{ $stats['notifications'] }}</h3><p>Notifications sent</p></div>
            <div class="icon"><i class="fas fa-bell"></i></div>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-link mr-1"></i> App endpoints (v1 module API)</h3></div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <tr><td><code>GET /api/mobile/v1/settings</code></td><td>screens, colors, features, maintenance</td></tr>
                        <tr><td><code>GET /api/mobile/v1/labels</code></td><td>section titles (also mirrored to live /api/mobile/labels)</td></tr>
                        <tr><td><code>GET /api/mobile/v1/admin/orders/{id}</code></td><td>order detail for the admin app</td></tr>
                        <tr><td><code>GET /api/mobile/v1/chat/admin/{orderId}</code></td><td>order chat (paginated)</td></tr>
                    </tbody>
                </table>
                <small class="text-muted">All require a Sanctum bearer token. Settings are cached 5 min; every save here clears the cache instantly.</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> Recent notifications</h3></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Date</th><th>Title</th><th>To</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($recentNotifications as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('d M H:i') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($log->title, 40) }}</td>
                            <td>{{ ucfirst($log->target_type) }}{{ $log->target_value ? ': ' . $log->target_value : '' }}</td>
                            <td><span class="badge badge-{{ $log->status === 'sent' ? 'success' : ($log->status === 'pending' ? 'warning' : 'danger') }}">{{ $log->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Nothing sent yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
