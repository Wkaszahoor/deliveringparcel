@extends('shipper.layouts.app')
@section('title', 'Profile')

@section('content')
<h2 style="margin:8px 0">My Shipper Profile</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px">
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
        <h4 style="margin-top:0">Account</h4>
        <table class="h2-table">
            <tr><td data-label="Field"><b>Shipper ID</b></td><td data-label="Value">{{ $profile->user->shipper_username ?? '—' }} <span class="h2-badge">read-only</span></td></tr>
            <tr><td><b>Level / Status</b></td><td>L{{ $profile->level }} · {{ $profile->status }} · KYC {{ $profile->kyc_status }}</td></tr>
            <tr><td><b>Countries</b> <span class="h2-badge">admin-controlled</span></td><td>{{ implode(', ', $profile->service_countries ?? []) }}</td></tr>
            <tr><td><b>Rating</b></td><td><span class="star">⭐ {{ $profile->rating }}</span> ({{ $profile->total_ratings }}) · {{ $profile->total_completed }} completed</td></tr>
        </table>
        <form action="{{ route('shipper.profile.update') }}" method="POST" class="h2-form" style="max-width:none;padding:0;border:0">
            @csrf @method('PUT')
            <label>Residence type</label>
            <select name="residence_type">
                @foreach(['apartment','house','villa','office'] as $rt)
                    <option value="{{ $rt }}" {{ $profile->residence_type === $rt ? 'selected' : '' }}>{{ ucfirst($rt) }}</option>
                @endforeach
            </select>
            <label style="margin-top:12px"><input type="checkbox" name="has_storage" value="1" {{ $profile->has_storage ? 'checked' : '' }}> I have storage space</label>
            <label style="margin-top:12px">Services offered</label>
            @foreach(['buy_for_me','ship_for_me','storage','personal_shopper','luxury'] as $svc)
                <label style="font-weight:400"><input type="checkbox" name="services_offered[]" value="{{ $svc }}"
                    {{ in_array($svc, array_keys(array_filter($profile->services_offered ?? []))) || in_array($svc, $profile->services_offered ?? []) ? 'checked' : '' }}> {{ ucwords(str_replace('_', ' ', $svc)) }}</label>
            @endforeach
            <button class="h2-btn h2-btn-primary" style="margin-top:12px">Save Profile</button>
        </form>
    </div>
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
        <h4 style="margin-top:0">KYC Documents</h4>
        <table class="h2-table">
            <thead><tr><th>Type</th><th>Status</th><th>When</th></tr></thead>
            <tbody>
            @forelse($profile->kycDocuments as $d)
                <tr><td data-label="Type">{{ ucwords(str_replace('_', ' ', $d->document_type)) }}</td>
                    <td data-label="Status">{{ $d->status === 'approved' ? '✅' : ($d->status === 'rejected' ? '❌' : '⏳') }} {{ $d->status }}</td>
                    <td data-label="When">{{ $d->created_at->diffForHumans() }}</td></tr>
            @empty<tr><td colspan="3">No documents uploaded.</td></tr>@endforelse
            </tbody>
        </table>
        <form action="{{ route('shipper.kyc.upload') }}" method="POST" enctype="multipart/form-data" class="h2-form" style="max-width:none;padding:0;border:0">
            @csrf
            <label>Upload new document</label>
            <select name="document_type" required>
                <option value="government_id">Government ID</option>
                <option value="selfie_photo">Selfie photo</option>
                <option value="address_proof">Address proof</option>
                <option value="social_media">Social media profile</option>
                <option value="consent_form">Consent form</option>
            </select>
            <label>File (JPG/PNG/PDF, max 5MB)</label>
            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
            <button class="h2-btn h2-btn-primary" style="margin-top:12px">Upload</button>
        </form>
    </div>
</div>
@endsection
