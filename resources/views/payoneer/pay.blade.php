@extends('layouts.client_dashbord_master')

@section('title', 'Payoneer Payment | DeliveringParcel')

@section('content')
<div class="container py-4" style="max-width:860px;">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
                <div>
                    <h4 class="mb-1" style="color:#0d6efd;">
                        <i class="fas fa-link mr-2"></i> Pay Through Payoneer Link
                    </h4>
                    <div class="text-muted" style="font-size:14px;">
                        Order #{{ $order->order_id }}
                        @if (!is_null($amount))
                            &middot; Amount due:
                            <strong>{{ $currency }} {{ number_format($amount, 2) }}</strong>
                        @endif
                    </div>
                </div>
                <a href="{{ url('/orders/' . $order->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to order
                </a>
            </div>
        </div>
    </div>

    @if (!$enabled)
        <div class="alert alert-warning">
            Payoneer payments are currently unavailable. Please choose another payment method on your order page.
        </div>
    @elseif (!is_null($amountError))
        <div class="alert alert-warning">{{ $amountError }}</div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">

                <p class="text-muted">{{ $instructions }}</p>

                {{-- ---------- STATE: no request yet ---------- --}}
                @if (is_null($payoneerRequest) || in_array($payoneerRequest->status, ['rejected', 'cancelled']))
                    @if ($payoneerRequest && $payoneerRequest->status === 'rejected')
                        <div class="alert alert-danger">
                            <strong>Your previous request was rejected.</strong>
                            @if ($payoneerRequest->reject_reason)
                                <div>{{ $payoneerRequest->reject_reason }}</div>
                            @endif
                        </div>
                    @endif
                    <div class="text-center py-4">
                        <p class="mb-3">Click the button below and we will generate a secure Payoneer payment link for
                            <strong>{{ $currency }} {{ number_format($amount, 2) }}</strong> — you will receive it on this page.</p>
                        <form method="POST" action="{{ route('payoneer.request', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-paper-plane mr-2"></i> Request Your Payoneer Payment Link
                            </button>
                        </form>
                    </div>

                {{-- ---------- STATE: requested, waiting for admin ---------- --}}
                @elseif ($payoneerRequest->status === 'requested')
                    <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
                        <div>
                            <i class="fas fa-hourglass-half mr-2"></i>
                            <strong>Request sent for {{ $currency }} {{ number_format($payoneerRequest->amount, 2) }}.</strong>
                            We are generating your payment link — this page updates as soon as it is ready.
                        </div>
                        <form method="POST" action="{{ route('payoneer.cancel', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Cancel request</button>
                        </form>
                    </div>

                {{-- ---------- STATE: link ready ---------- --}}
                @elseif ($payoneerRequest->status === 'link_sent')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Your Payoneer payment link is ready!</strong>
                        Pay <strong>{{ $payoneerRequest->currency }} {{ number_format($payoneerRequest->amount, 2) }}</strong> through the link, then come back to
                        @if ($proofRequired)
                            upload the payment screenshot below.
                        @else
                            mark the payment as done below.
                        @endif
                    </div>

                    <div class="text-center mb-4">
                        <a href="{{ $payoneerRequest->link_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-lg px-4">
                            <i class="fas fa-external-link-alt mr-2"></i> Open Payoneer Payment Link
                        </a>
                        @if ($payoneerRequest->link_note)
                            <div class="text-muted mt-2" style="font-size:13px;">{{ $payoneerRequest->link_note }}</div>
                        @endif
                    </div>

                    @if ($proofRequired)
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-3"><i class="fas fa-upload mr-2"></i> Upload your payment screenshot</h6>
                            <form method="POST" action="{{ route('payoneer.proof', $order->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <input type="file" name="proof" class="form-control-file" required accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="form-text text-muted">JPG, PNG or PDF — max 5 MB.</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check mr-1"></i> Submit payment proof
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-center">
                            <form method="POST" action="{{ route('payoneer.markPaid', $order->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-double mr-2"></i> I have paid — mark payment done
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">Optional: you may still be asked for a screenshot by our team.</small>
                        </div>
                    @endif

                {{-- ---------- STATE: proof / payment-done under verification ---------- --}}
                @elseif (in_array($payoneerRequest->status, ['proof_submitted', 'marked_paid']))
                    <div class="alert alert-warning">
                        <i class="fas fa-user-shield mr-2"></i>
                        <strong>Payment {{ $payoneerRequest->status === 'proof_submitted' ? 'proof received' : 'marked as done' }} — awaiting verification.</strong>
                        We are confirming your Payoneer payment. You will be notified as soon as the order is marked paid.
                    </div>

                {{-- ---------- STATE: verified ---------- --}}
                @elseif ($payoneerRequest->status === 'verified')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Payment verified — this order is paid.</strong>
                        @if ($payoneerRequest->verified_at)
                            Verified {{ $payoneerRequest->verified_at->format('d M Y, h:i A') }}.
                        @endif
                    </div>
                @endif

            </div>
        </div>

        <div class="text-muted mt-3" style="font-size:13px;">
            <i class="fas fa-shield-alt mr-1"></i>
            Never share your Payoneer password or verification codes. DeliveringParcel will only ever ask you to pay through an official Payoneer payment link.
        </div>
    @endif
</div>
@endsection
