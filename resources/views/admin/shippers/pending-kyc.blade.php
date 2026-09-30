@extends('admin.layouts.app')
@section('title', 'Pending KYC')
@section('page_title', 'Pending KYC')
@section('page_subtitle', 'Shipper identity documents awaiting review')

@section('content')
<div class="card card-default">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> Pending KYC (@count($shippers ?? []))</h3></div>
    <div class="card-body">
        @forelse(($shippers ?? collect()) as $shipper)
            <div class="mb-4 border rounded p-3">
                <b>{{ $shipper->user->shipper_username ?? 'SHP' }}</b> — {{ $shipper->user->email ?? '' }}
                <span class="text-muted">(applied {{ $shipper->created_at->diffForHumans() }})</span>
                <div class="table-responsive mt-2">
                    <table class="table table-sm">
                        <thead><tr><th>Document</th><th>Uploaded</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach($shipper->kycDocuments->where('status', 'pending') as $d)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $d->document_type)) }}</td>
                                <td>{{ $d->created_at->diffForHumans() }}</td>
                                <td><span class="badge badge-warning">pending</span></td>
                                <td>
                                    <a href="{{ route('admin.shippers.kyc.download', $d->id) }}" class="btn btn-xs btn-secondary">Download</a>
                                    <form action="{{ route('admin.shippers.kyc.approve-doc', $d->id) }}" method="POST" class="d-inline">@csrf <button class="btn btn-xs btn-success">Approve</button></form>
                                    <form action="{{ route('admin.shippers.kyc.reject-doc', $d->id) }}" method="POST" class="d-inline">@csrf
                                        <input type="text" name="reason" placeholder="Reason" class="form-control form-control-xs d-inline-block" style="width:150px" required>
                                        <button class="btn btn-xs btn-danger">Reject</button></form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <a href="{{ route('admin.shippers.show', $shipper->id) }}" class="btn btn-sm btn-primary">Open full profile</a>
                <form action="{{ route('admin.shippers.approve', $shipper->id) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-sm btn-success" onclick="return confirm('Approve whole shipper?')">Approve shipper</button></form>
            </div>
        @empty
            <p class="text-muted">No pending KYC documents right now.</p>
        @endforelse
    </div>
</div>
@endsection
