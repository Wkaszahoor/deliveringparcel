@extends('layouts.portal')

@section('title', 'Service Countries')
@section('page_title', 'Service Countries')
@section('page_subtitle', 'Where you can accept shipping work — changes need admin approval')

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Request a countries change</h3></div>
            <div class="card-body">
                @if ($pending)
                    <div class="alert alert-warning">
                        <strong>Change request pending admin approval.</strong><br>
                        Requested countries: <b>{{ implode(', ', $pending->countries) }}</b>
                        @if($pending->note)<br>Your note: {{ $pending->note }}@endif
                        <br><small class="text-muted">Submitted {{ $pending->created_at->format('d M Y, H:i') }} — you can submit again below to replace it.</small>
                    </div>
                @endif

                <form action="{{ route('shipper.countries.request') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="mb-2"><b>Countries you want to operate in</b>
                            <small class="text-muted d-block">Only admin-enabled countries are listed. Tick all that apply.</small>
                        </label>
                        <div class="dp-check-grid">
                            @foreach ($activeCountries as $c)
                                <label class="dp-check-item">
                                    <input type="checkbox" name="service_countries[]" value="{{ $c->iso2 }}"
                                        @if (in_array($c->iso2, $pending->countries ?? $profile->service_countries ?? [])) checked @endif>
                                    <span>{{ $c->name }}</span>
                                    <small class="text-muted">({{ $c->iso2 }})</small>
                                </label>
                            @endforeach
                        </div>
                        @error('service_countries')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Note to admin (optional)</label>
                        <textarea name="note" rows="2" class="form-control" maxlength="500"
                                  placeholder="e.g. I moved to Spain and can now handle ES deliveries">{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        {{ $pending ? 'Update my pending request' : 'Send request to admin' }}
                    </button>
                    <small class="text-muted d-block mt-2">
                        Your current countries stay active until admin approves. Requests for blocked countries are never visible on the marketplace.
                    </small>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">Currently approved countries</h3></div>
            <div class="card-body">
                @if (count($profile->service_countries ?? []) > 0)
                    @foreach ($profile->service_countries as $iso)
                        <span class="badge badge-light border mr-1 mb-1 p-2">{{ $iso }}</span>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No countries yet.</p>
                @endif
            </div>
        </div>
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">How it works</h3></div>
            <div class="card-body text-sm">
                <ol class="pl-3 mb-0">
                    <li class="mb-1">Tick the countries you can serve and send the request.</li>
                    <li class="mb-1">Admin reviews and approves or rejects it.</li>
                    <li class="mb-1">Approved countries appear on your profile instantly.</li>
                    <li class="mb-1">Admin can also block a whole country network-wide — requests for blocked countries are hidden from every shipper.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@push('portal_styles')
<style>
.dp-check-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: .5rem;
    max-height: 320px; overflow-y: auto; border: 1px solid #e3e6ea; border-radius: .5rem; padding: .75rem; }
.dp-check-item { display: flex; align-items: center; gap: .4rem; font-size: .92rem; margin: 0; }
.dp-check-item input { margin: 0; }
</style>
@endpush
