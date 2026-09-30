{{-- ==========================================================================
    Order Attachments (Tailwind) — admin-only variant of order-attachments.blade.php.

    Kept as a separate file rather than editing the shared partial in place:
    admin.partials.order-attachments is also @included from
    clients/order_offer.blade.php, which still extends the old Bootstrap
    layouts.client_dashbord_master — converting the shared file to Tailwind
    classes would break that client-facing page. Same data/logic, Tailwind
    markup only.

    Expects (passed via @include):
      $order            App\Models\Orders
      $attachmentsRole  'admin' | 'client'
                        (only affects which payment-proof URL is used:
                         admin.payments.proof vs home2.pay.proof.download)
    ========================================================================== --}}
@php
    $attAdminItems  = collect();
    $attClientItems = collect();

    $attFileUrl = function ($attValue) {
        $attV = trim((string) $attValue);
        if (preg_match('/^https?:\/\//i', $attV)) return $attV;
        $attRel = ltrim($attV, '/');
        return str_starts_with($attRel, 'uploads/')
            ? asset($attRel)
            : asset('uploads/productsimages/' . $attRel);
    };

    foreach ($order->orderproducts as $attOp) {
        if (trim((string) $attOp->image) !== '') {
            $attAdminItems->push([
                'label' => (string) $attOp->productname,
                'url'   => $attFileUrl($attOp->image),
                'icon'  => strtolower(pathinfo($attOp->image, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'image',
            ]);
        }
    }

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
<div class="w-full mb-4">
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800"><i class="fas fa-paperclip mr-1.5 text-slate-400"></i>Attachments</h3>
        </div>
        <div class="p-4">
            <div class="flex flex-wrap gap-y-4 -mx-2">
                <div class="w-full md:w-1/2 px-2">
                    <h6 class="mb-2 text-sm font-medium text-slate-700"><i class="fas fa-user-shield mr-1.5 text-blue-500"></i>Admin Attachments</h6>
                    @if ($attAdminItems->isEmpty())
                        <p class="text-sm text-slate-400 italic">No attachments yet</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($attAdminItems as $attItem)
                                <a href="{{ $attItem['url'] }}" target="_blank" rel="noopener"
                                   class="w-[76px] text-center"
                                   title="{{ $attItem['label'] }}">
                                    @if ($attItem['icon'] === 'image')
                                        <img src="{{ $attItem['url'] }}" alt="{{ $attItem['label'] }}"
                                             class="h-[72px] w-[72px] rounded border border-slate-200 bg-white object-cover"
                                             onerror="this.style.display='none'">
                                    @elseif ($attItem['icon'] === 'pdf')
                                        <span class="flex h-[72px] w-[72px] items-center justify-center rounded border border-slate-200 bg-white">
                                            <i class="fas fa-file-pdf fa-2x text-red-500"></i>
                                        </span>
                                    @else
                                        <span class="flex h-[72px] w-[72px] items-center justify-center rounded border border-slate-200 bg-white">
                                            <i class="fas fa-file-download fa-2x text-slate-400"></i>
                                        </span>
                                    @endif
                                    <span class="mt-1 block truncate text-[11px] text-slate-500">{{ $attItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="w-full md:w-1/2 px-2">
                    <h6 class="mb-2 text-sm font-medium text-slate-700"><i class="fas fa-user mr-1.5 text-green-500"></i>Client Attachments</h6>
                    @if ($attClientItems->isEmpty())
                        <p class="text-sm text-slate-400 italic">No attachments yet</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($attClientItems as $attItem)
                                <a href="{{ $attItem['url'] }}" target="_blank" rel="noopener"
                                   class="w-[76px] text-center"
                                   title="{{ $attItem['label'] }}">
                                    @if ($attItem['icon'] === 'image')
                                        <img src="{{ $attItem['url'] }}" alt="{{ $attItem['label'] }}"
                                             class="h-[72px] w-[72px] rounded border border-slate-200 bg-white object-cover"
                                             onerror="this.style.display='none'">
                                    @elseif ($attItem['icon'] === 'pdf')
                                        <span class="flex h-[72px] w-[72px] items-center justify-center rounded border border-slate-200 bg-white">
                                            <i class="fas fa-file-pdf fa-2x text-red-500"></i>
                                        </span>
                                    @else
                                        <span class="flex h-[72px] w-[72px] items-center justify-center rounded border border-slate-200 bg-white">
                                            <i class="fas fa-file-download fa-2x text-slate-400"></i>
                                        </span>
                                    @endif
                                    <span class="mt-1 block truncate text-[11px] text-slate-500">{{ $attItem['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
