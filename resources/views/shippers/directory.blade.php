@extends('layouts.fmaster')

@section('title', 'Shipper Directory — DeliveringParcel')

@section('content')
<section class="container" style="padding:34px 0 60px;">
    <div style="max-width:980px;margin:0 auto;">
        <div class="text-center mb-4">
            <h1 style="font-weight:800;">Shipper Directory</h1>
            <p class="text-muted" style="font-size:16px;">
                Our verified shippers work under platform escrow with KYC-verified identities.
                Only anonymized profiles are shown — personal details always stay private.
            </p>
            <a href="{{ route('shipper.register.form') }}" class="btn btn-primary">
                <i class="fas fa-rocket mr-1"></i> Become a Shipper
            </a>
        </div>

        <div class="row">
            @forelse ($shippers as $s)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm border-0 text-center">
                        <div class="card-body">
                            <div style="width:64px;height:64px;border-radius:50%;background:#eef4ff;color:#0d6efd;font-weight:800;font-size:20px;line-height:64px;margin:0 auto 10px;">
                                {{ strtoupper(substr($s->display_name, 0, 2)) }}
                            </div>
                            <h5 class="mb-0">{{ $s->display_name }}</h5>
                            <div class="text-muted mb-2" style="font-size:13px;">
                                {{ implode(', ', $s->countries_list) }}
                            </div>
                            <div class="mb-2">
                                <span class="badge badge-primary">Level {{ $s->level }}</span>
                                @if ($s->verified_at)
                                    <span class="badge badge-success" title="KYC verified"><i class="fas fa-check-circle"></i> Verified</span>
                                @endif
                            </div>
                            <div style="font-size:14px;">
                                <span class="text-warning">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="{{ $i < round($s->rating) ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </span>
                                <span class="text-muted">{{ number_format((float) $s->rating, 1) }}</span>
                            </div>
                            <div class="text-muted mt-1" style="font-size:13px;">
                                {{ number_format((int) $s->total_completed) }} deliveries completed
                            </div>
                            <div class="text-muted" style="font-size:12px;">Member since {{ optional($s->created_at)->format('M Y') }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">
                    Our shipper network is growing — be the <a href="{{ route('shipper.register.form') }}">first in your country</a>.
                </div>
            @endforelse
        </div>

        <div class="text-muted text-center mt-3" style="font-size:13px;">
            <i class="fas fa-shield-alt mr-1"></i>
            Every shipper above passed platform KYC verification. Ratings come only from completed, escrow-protected deliveries.
        </div>
    </div>
</section>
@endsection
