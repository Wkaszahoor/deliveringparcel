@extends('shipper.layouts.app')
@section('title', 'Assignment #' . $assignment->id)

@section('content')
<h2 style="margin:8px 0">Assignment — Order #{{ $assignment->order_id }}
    <span class="h2-badge">{{ str_replace('_', ' ', $assignment->status) }}</span>
</h2>

<div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px">
    <h4 style="margin-top:0">Brief</h4>
    <pre style="white-space:pre-wrap;font-family:inherit;margin:0">{{ $assignment->request?->brief_text ?? 'No brief.' }}</pre>
    <p style="margin:10px 0 0"><b>Your fee:</b> ${{ number_format($assignment->shipper_fee, 2) }}
        · <b>On completion:</b> ${{ number_format($assignment->wallet_credit_amount, 2) }} now + ${{ number_format($assignment->wallet_hold_amount, 2) }} released after 7 days</p>
</div>

@if($assignment->request?->service_type === 'buy_for_me' && in_array($assignment->status, ['assigned','accepted']))
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px;border-left:4px solid #f0a800">
        <h4 style="margin-top:0">Purchase Step</h4>
        <p>Purchase deadline: <b>{{ $assignment->purchase_deadline?->diffForHumans() ?? '—' }}</b></p>
        <form action="{{ route('shipper.assignments.purchased', $assignment->id) }}" method="POST">@csrf
            <button class="h2-btn h2-btn-primary">I Have Purchased the Item</button></form>
    </div>
@endif

@if(in_array($assignment->status, ['awaiting_package','assigned','accepted','purchased']) && $assignment->request?->service_type === 'ship_for_me')
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px">
        <h4 style="margin-top:0">Package Received?</h4>
        <p>Confirm once the package physically arrives at your address.</p>
        <form action="{{ route('shipper.assignments.package-received', $assignment->id) }}" method="POST">@csrf
            <button class="h2-btn h2-btn-primary">Package Received</button></form>
    </div>
@endif

@if(in_array($assignment->status, ['package_received','proof_uploaded','purchased']))
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px">
        <h4 style="margin-top:0">Upload Proof Photos</h4>
        <form action="{{ route('shipper.assignments.proof', $assignment->id) }}" method="POST" enctype="multipart/form-data" class="h2-form" style="max-width:none;padding:0;border:0">
            @csrf
            <label>Type</label>
            <select name="proof_type" required>
                <option value="item_received">Item received</option>
                <option value="before_repack">Before repackaging</option>
                <option value="after_repack">After repackaging</option>
                <option value="dispatch_receipt">Courier receipt</option>
                <option value="damage_report">Damage documentation</option>
                <option value="purchase_receipt">Purchase receipt (Buy for Me)</option>
            </select>
            <label>File (JPG/PNG/PDF, max 5MB)</label>
            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required>
            <label>Notes (optional)</label>
            <input type="text" name="notes">
            <button class="h2-btn h2-btn-primary" style="margin-top:12px">Upload Proof</button>
        </form>
        @if($assignment->proofs->count())
            <table class="h2-table" style="margin-top:12px">
                <thead><tr><th>Type</th><th>Status</th><th>When</th></tr></thead>
                <tbody>
                @foreach($assignment->proofs as $p)
                    <tr><td data-label="Type">{{ ucwords(str_replace('_', ' ', $p->proof_type)) }}</td>
                        <td data-label="Status">{{ $p->admin_approved ? '✅ approved' : '⏳ pending' }}</td>
                        <td data-label="When">{{ $p->created_at->diffForHumans() }}</td></tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif

@if($assignment->status === 'proof_approved' || $assignment->status === 'address_received')
    <div class="h2-alert" style="background:#e6f6ec;color:#137333">Photos approved by admin — waiting for the customer's delivery address to be forwarded.</div>
@endif

@if($assignment->deliveryAddress?->forwarded_to_shipper)
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px;border-left:4px solid #0d6efd">
        <h4 style="margin-top:0">Delivery Address (forwarded by admin)</h4>
        @php($m = $assignment->deliveryAddress->maskedForForwardLevel())
        <table class="h2-table">
            @foreach($m as $k => $v)
                <tr><td data-label="Field"><b>{{ ucfirst(str_replace('_', ' ', $k)) }}</b></td><td data-label="Value">{{ $v }}</td></tr>
            @endforeach
        </table>
    </div>
@endif

@if($assignment->status === 'address_forwarded')
    <div class="h2-section" style="background:#fff;border-radius:12px;padding:16px;margin-bottom:14px">
        <h4 style="margin-top:0">Submit Tracking</h4>
        <form action="{{ route('shipper.assignments.tracking', $assignment->id) }}" method="POST" class="h2-form" style="max-width:none;padding:0;border:0">
            @csrf
            <div class="h2-field-grid">
                <div><label>Carrier</label><input type="text" name="carrier" required placeholder="DHL / FedEx / UPS…"></div>
                <div><label>Tracking number</label><input type="text" name="tracking_number" required></div>
                <div><label>Ship date</label><input type="date" name="ship_date" required value="{{ now()->toDateString() }}"></div>
                <div><label>Est. delivery</label><input type="date" name="estimated_delivery"></div>
            </div>
            <label>Tracking URL (optional)</label><input type="url" name="tracking_url" placeholder="https://…">
            <button class="h2-btn h2-btn-primary" style="margin-top:12px">Submit Tracking</button>
        </form>
    </div>
@endif

@if($assignment->trackingDetails)
    <div class="h2-alert" style="background:#e7f1ff;color:#0a58ca">
        Tracking submitted ({{ $assignment->trackingDetails->carrier }} {{ $assignment->trackingDetails->tracking_number }}) —
        {{ $assignment->trackingDetails->shared_with_customer ? 'shared with customer.' : 'admin will share it with the customer.' }}
    </div>
@endif

@if($assignment->status === 'completed')
    <div class="h2-alert h2-alert-success">Assignment completed — ${{ number_format($assignment->wallet_credit_amount, 2) }} credited to your wallet.</div>
@endif

<div class="h2-section" style="background:#fff;border-radius:12px;padding:16px">
    <h4 style="margin-top:0">Admin Chat</h4>
    <div style="max-height:260px;overflow:auto;border:1px solid var(--h2-border);border-radius:8px;padding:10px" class="mb-2">
        @forelse($assignment->adminChat->sortBy('created_at') as $m)
            <div class="shp-msg {{ $m->from_type }}">
                <b>{{ $m->from_type === 'admin' ? 'Admin' : 'You' }}</b>
                <small style="color:var(--h2-muted)">{{ $m->created_at->diffForHumans() }}</small>
                <div style="white-space:pre-wrap">{{ $m->message }}</div>
            </div>
        @empty<p class="shp-msg admin">Admin: Welcome! Ask anything about this assignment here.</p>@endforelse
    </div>
    <p style="margin-top:10px"><a href="{{ route('shipper.assignments.index') }}">← Back to assignments</a></p>
</div>
@endsection
