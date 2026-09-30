{{-- ==========================================================================
    Order Attachments — additive, read-only shared partial (2026-08-31).

    Expects (passed via @include):
      $order            App\Models\Orders
      $attachmentsRole  'admin' | 'client'
                        (only affects which payment-proof URL is used:
                         admin.payments.proof vs home2.pay.proof.download)

    Renders an AdminLTE card with two blocks:
      "Admin Attachments"  — order product images + first-offer product images
      "Client Attachments" — payment receipts (proof) + product purchase receipts
    All data access is lazy-loaded read-only; all output is Blade-escaped.
    ========================================================================== --}}
@php
    $attAdminItems  = collect();
    $attClientItems = collect();

    /* Product file columns hold TWO formats: legacy rows a bare filename,
       UploadGuard rows the full web-relative path ("uploads/productsimages/
       x.jpg"). Resolve without double-prefixing either. */
    $attFileUrl = function ($attValue) {
        $attV = trim((string) $attValue);
        if (preg_match('/^https?:\/\//i', $attV)) return $attV;
        $attRel = ltrim($attV, '/');
        return str_starts_with($attRel, 'uploads/')
            ? asset($attRel)
            : asset('uploads/productsimages/' . $attRel);
    };

    /* ---- Admin attachments: ordered products with an uploaded image. ---- */
    foreach ($order->orderproducts as $attOp) {
        if (trim((string) $attOp->image) !== '') {
            $attAdminItems->push([
                'label' => (string) $attOp->productname,
                'url'   => $attFileUrl($attOp->image),
                'icon'  => strtolower(pathinfo($attOp->image, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'image',
            ]);
        }
    }

    /* ---- Admin attachments: products on the first offer, if any. ---- */
    $attFirstOffer = $order->offers->first();
    if ($attFirstOffer) {
        foreach ($attFirstOffer->offerorderproducts as $attOop) {
            if (trim((string) $attOop->image) !== '') {
                $attAdminItems->push([
                    'label' => (string) $attOop->productname,
                    'url'   => $attFileUrl($attOop->image),
                    'icon'  => strtolower(pathinfo($attOop->image, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'image',
                ]);
            }
        }
    }

    /* ---- Client attachments: payment receipts (private disk, streamed by an
       authorized download route, so they always render as file-icon links). ---- */
    $attPayments = \App\Models\Payment::where('order_id', $order->id)
        ->whereNotNull('proof_path')
        ->where('proof_path', '!=', '')
        ->orderBy('id')
        ->get();
    foreach ($attPayments as $attP) {
        $attClientItems->push([
            'label' => 'Payment Receipt' . ($attP->reference ? ' · ' . $attP->reference : ''),
            'url'   => $attachmentsRole === 'admin'
                ? route('admin.payments.proof', $attP->id)
                : route('home2.pay.proof.download', $attP->id),
            'icon'  => strtolower(pathinfo((string) $attP->proof_path, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'download',
        ]);
    }

    /* ---- Client attachments: product purchase receipts. ---- */
    foreach ($order->orderproducts as $attOp) {
        if (trim((string) $attOp->receipt) !== '') {
            $attClientItems->push([
                'label' => 'Receipt — ' . (string) $attOp->productname,
                'url'   => $attFileUrl($attOp->receipt),
                'icon'  => strtolower(pathinfo($attOp->receipt, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'image',
            ]);
        }
    }
@endphp
<div class="col-md-12" style="margin-bottom:1rem">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-paperclip mr-1"></i> <strong>Attachments</strong></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-2"><i class="fas fa-user-shield text-primary mr-1"></i>Admin Attachments</h6>
                    @if ($attAdminItems->isEmpty())
                        <p class="text-muted mb-0"><em>No attachments yet</em></p>
                    @else
                        <div class="d-flex flex-wrap">
                            @foreach ($attAdminItems as $attItem)
                                <a href="{{ $attItem['url'] }}" target="_blank" rel="noopener"
                                   class="text-center mr-2 mb-2" style="width:76px"
                                   title="{{ $attItem['label'] }}">
                                    @if ($attItem['icon'] === 'image')
                                        <img src="{{ $attItem['url'] }}" alt="{{ $attItem['label'] }}"
                                             class="rounded border bg-white"
                                             style="width:72px;height:72px;object-fit:cover"
                                             onerror="this.style.display='none'">
                                    @elseif ($attItem['icon'] === 'pdf')
                                        <span class="d-flex align-items-center justify-content-center rounded border bg-white"
                                              style="width:72px;height:72px">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        </span>
                                    @else
                                        <span class="d-flex align-items-center justify-content-center rounded border bg-white"
                                              style="width:72px;height:72px">
                                            <i class="fas fa-file-download fa-2x text-secondary"></i>
                                        </span>
                                    @endif
                                    <span class="d-block text-muted text-truncate" style="font-size:.72rem">{{ $attItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2"><i class="fas fa-user text-success mr-1"></i>Client Attachments</h6>
                    @if ($attClientItems->isEmpty())
                        <p class="text-muted mb-0"><em>No attachments yet</em></p>
                    @else
                        <div class="d-flex flex-wrap">
                            @foreach ($attClientItems as $attItem)
                                <a href="{{ $attItem['url'] }}" target="_blank" rel="noopener"
                                   class="text-center mr-2 mb-2" style="width:76px"
                                   title="{{ $attItem['label'] }}">
                                    @if ($attItem['icon'] === 'image')
                                        <img src="{{ $attItem['url'] }}" alt="{{ $attItem['label'] }}"
                                             class="rounded border bg-white"
                                             style="width:72px;height:72px;object-fit:cover"
                                             onerror="this.style.display='none'">
                                    @elseif ($attItem['icon'] === 'pdf')
                                        <span class="d-flex align-items-center justify-content-center rounded border bg-white"
                                              style="width:72px;height:72px">
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                        </span>
                                    @else
                                        <span class="d-flex align-items-center justify-content-center rounded border bg-white"
                                              style="width:72px;height:72px">
                                            <i class="fas fa-file-download fa-2x text-secondary"></i>
                                        </span>
                                    @endif
                                    <span class="d-block text-muted text-truncate" style="font-size:.72rem">{{ $attItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
