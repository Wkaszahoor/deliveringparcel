@extends('home2.layouts.app')
@section('title')Order #{{ $order->order_id }}@endsection

@section('content')
<section class="h2-section">
    <div class="h2-container">

        
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem">
            <div>
                <h2>Order #{{ $order->order_id }}</h2>
                <p class="muted" style="margin:.2rem 0 0">
                    {{ $order->shipfrom }} → {{ $order->shipto }}
                    · placed {{ optional($order->created_at)->format('M d, Y H:i') }}
                    · approx {{ $order->approximate_weight }} kg
                </p>
                @if (trim((string) $order->address) !== '')
                    <p class="muted small" style="margin:.2rem 0 0">Pickup/address on file: {{ $order->address }}</p>
                @endif
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.4rem">
                <span class="h2-badge">{{ $order->order_status ?: 'pending' }}</span>
                <a class="muted small" href="{{ url('orders/' . $order->id) }}">Open classic view</a>
            </div>
        </div>

        @php($svcMap = ['consolidation' => 'Consolidation', 'customs' => 'Customs handling', 'check' => 'Item check & photos', 'disinfection' => 'Disinfection', 'purchase' => 'Purchase assistance', 'photo' => 'Product photos'])
        @php($svcs = array_filter(array_map('trim', explode(',', (string) $order->product_services))))
        @if (count($svcs))
            <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.6rem">
                <span class="muted small">Requested services:</span>
                @foreach ($svcs as $s)
                    <span class="h2-badge">{{ $svcMap[$s] ?? $s }}</span>
                @endforeach
            </div>
        @endif

        
        @include('partials.order-callouts', ['statusKey' => (string) ($order->order_status ?? '')])

        
        <div class="h2-card" style="margin-top:1rem">
            <div class="h2-card-body">
                <h3 style="font-size:1.05rem;margin:0 0 .5rem">Products</h3>
                @if ($products->isEmpty())
                    <p class="muted small" style="margin:0">No products recorded yet.</p>
                @else
                    <div class="h2-table-wrap">
                        <table class="h2-table">
                            <thead><tr><th>Item</th><th>Qty</th><th>Unit price</th><th>Tracking</th></tr></thead>
                            <tbody>
                                @foreach ($products as $p)
                                    <tr>
                                        <td data-label="Item">
                                            @if ($p->image)
                                                <img src="{{ url('uploads/productsimages/' . $p->image) }}" alt="{{ $p->productname }}" width="48" height="48" style="object-fit:cover;border-radius:6px;margin-right:.5rem;vertical-align:middle">
                                            @endif
                                            @if ($p->producturl)
                                                <a href="{{ $p->producturl }}" target="_blank" rel="noopener">{{ $p->productname ?: $p->producturl }}</a>
                                            @else
                                                {{ $p->productname }}
                                            @endif
                                            @if ($p->receipt)
                                                <span class="muted small">· <a href="{{ url('uploads/productsimages/' . $p->receipt) }}">receipt</a></span>
                                            @endif
                                        </td>
                                        <td data-label="Qty">{{ $p->productquantity }}</td>
                                        <td data-label="Unit price">{{ $p->productprice !== null ? '$' . number_format((float) $p->productprice, 2) : '—' }}</td>
                                        <td data-label="Tracking">
                                            @if ($p->trackingid)
                                                @if ($p->trackinglink)
                                                    <a href="{{ $p->trackinglink }}" target="_blank" rel="noopener">{{ $p->trackingid }}</a>
                                                @else
                                                    {{ $p->trackingid }}
                                                @endif
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                @if ($order->trackingid || $order->companyname)
                    <p class="small" style="margin:.6rem 0 0">
                        Shipment tracking:
                        @if ($order->trackinglink)<a href="{{ $order->trackinglink }}" target="_blank" rel="noopener"><strong>{{ $order->trackingid ?: 'link' }}</strong></a>
                        @else<strong>{{ $order->trackingid }}</strong>@endif
                        @if ($order->companyname)<span class="muted"> · {{ $order->companyname }}</span>@endif
                    </p>
                @endif
            </div>
        </div>

        
        <div class="h2-card" style="margin-top:1rem">
            <div class="h2-card-body">
                <h3 style="font-size:1.05rem;margin:0 0 .5rem">Offer</h3>
                @if (!$latestOffer)
                    <p class="muted small" style="margin:0">No offer yet — our team is preparing one. You will get a notification when it arrives.</p>
                @else
                    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
                        <div>
                            <strong>Total: ${{ number_format((float) $latestOffer->total, 2) }}</strong>
                            @if ($latestOffer->product_total !== null)
                                <span class="muted small">· products ${{ number_format((float) $latestOffer->product_total, 2) }}</span>
                            @endif
                        </div>
                        @if ((int) $latestOffer->offer_status === 1)
                            <span class="h2-badge" style="background:#137333;color:#fff">accepted</span>
                        @elseif ((int) $latestOffer->offer_status === 2)
                            <span class="h2-badge" style="background:#b3261e;color:#fff">rejected</span>
                        @else
                            <span class="h2-badge" style="background:#0b5fff;color:#fff">awaiting your reply</span>
                        @endif
                    </div>

                    @if (count($offerServices))
                        <div class="h2-table-wrap" style="margin-top:.6rem">
                            <table class="h2-table">
                                <thead><tr><th>Offered service</th><th>Charge</th></tr></thead>
                                <tbody>
                                    @foreach ($offerServices as $s)
                                        <tr>
                                            <td data-label="Service">{{ $s->servicename }}</td>
                                            <td data-label="Charge">${{ number_format((float) $s->servicevalue, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if (count($offerProducts))
                        <div class="h2-table-wrap" style="margin-top:.6rem">
                            <table class="h2-table">
                                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Spread</th><th>Line total</th><th>Tracking</th></tr></thead>
                                <tbody>
                                    @foreach ($offerProducts as $op)
                                        <tr>
                                            <td data-label="Product">{{ $op->productname }}</td>
                                            <td data-label="Qty">{{ $op->productquantity }}</td>
                                            <td data-label="Price">${{ number_format((float) $op->productprice, 2) }}</td>
                                            <td data-label="Spread">${{ number_format((float) $op->productspread, 2) }}</td>
                                            <td data-label="Line total">${{ number_format((float) $op->product_total, 2) }}</td>
                                            <td data-label="Tracking">
                                                @if ($op->trackingid)
                                                    @if ($op->trackinglink)<a href="{{ $op->trackinglink }}" target="_blank" rel="noopener">{{ $op->trackingid }}</a>
                                                    @else{{ $op->trackingid }}@endif
                                                @else—@endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if (trim((string) $latestOffer->description) !== '')
                        <p class="small" style="margin:.6rem 0 0">{{ $latestOffer->description }}</p>
                    @endif

                    @php($shipText = trim((string) $latestOffer->shipingaddress))
                    @if ($shippingAddress || $shipText !== '')
                        <p class="muted small" style="margin:.4rem 0 0">
                            Ship to:
                            @if ($shippingAddress)
                                {{ $shippingAddress->name }}, {{ $shippingAddress->address1 }} {{ $shippingAddress->address2 }}, {{ $shippingAddress->city }} {{ $shippingAddress->state }} {{ $shippingAddress->postalcode }}, {{ $shippingAddress->country }}
                            @else
                                {{ $shipText }}
                            @endif
                        </p>
                    @endif

                    @if ($latestOffer->rejections_note)
                        <p class="muted small" style="margin:.4rem 0 0">Your note on this offer: {{ $latestOffer->rejections_note }}</p>
                    @endif

                    @if (!in_array((int) $latestOffer->offer_status, [1, 2]))
                        <div style="display:flex;gap:.5rem;margin-top:.75rem;flex-wrap:wrap">
                            <form method="POST" action="{{ route('home2.offers.accept', $latestOffer->id) }}">
                                @csrf
                                <button class="h2-btn h2-btn-primary">Accept &amp; pay</button>
                            </form>
                            <form method="POST" action="{{ route('home2.offers.reject', $latestOffer->id) }}" onsubmit="var n=prompt('Reason (optional):'); if(n!==null)this.rejections_note.value=n; return true;">
                                @csrf
                                <input type="hidden" name="rejections_note" value="">
                                <button class="h2-btn h2-btn-outline">Ask for changes</button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        
        <div class="h2-card" style="margin-top:1rem">
            <div class="h2-card-body">
                <h3 style="font-size:1.05rem;margin:0 0 .5rem">Payment</h3>
                @if (!$latestPayment && !$payable)
                    <p class="muted small" style="margin:0">Payment becomes available once you accept an offer.</p>
                @endif
                @if ($payable)
                    <p class="small" style="margin:0 0 .6rem">Amount due: <strong>${{ number_format((float) $latestOffer->total, 2) }}</strong></p>
                    <a class="h2-btn h2-btn-primary" href="{{ route('home2.pay.show', $order->id) }}">Choose payment method</a>
                @elseif ($latestPayment)
                    @php($code = $latestPayment->payment_method_code)
                    @php($st = $latestPayment->status)
                    <p class="small" style="margin:0 0 .6rem">
                        State: <strong>{{ ucfirst(str_replace('_', ' ', $st)) }}</strong>
                        @if ($code) · method {{ str_replace('_', ' ', $code) }}@endif
                        @if ($latestPayment->reference !== null && $latestPayment->reference !== '')<br><span class="muted small">Reference: {{ $latestPayment->reference }}</span>@endif
                    </p>
                    <div style="border-left:4px solid #d97706;background:#fffbeb;border-radius:6px;padding:.6rem .75rem;margin-bottom:.6rem">
                        @if ($st === 'awaiting_payment' && $code === 'cod')
                            <strong>Cash on delivery.</strong>
                            <span class="muted small">No online payment needed — pay the courier in cash when your parcel arrives. The order moves forward once delivery is confirmed.</span>
                        @elseif ($st === 'awaiting_payment' && $code === 'bank_transfer')
                            <strong>Bank transfer initiated.</strong>
                            <span class="muted small">Send the transfer using the bank details shown at checkout, then upload your payment proof below or from the <a href="{{ route('home2.pay.show', $order->id) }}">payment page</a>. Our team verifies transfers manually.</span>
                        @elseif ($st === 'awaiting_payment' && $code === 'stripe')
                            <strong>Card payment started but not completed.</strong>
                            <span class="muted small">Finish the secure card checkout to confirm this order: <a href="{{ route('home2.pay.show', $order->id) }}" target="_blank" rel="noopener">open secure card payment</a>.</span>
                        @elseif ($st === 'awaiting_payment')
                            <strong>Waiting for your payment.</strong>
                            <span class="muted small">Choose a method and complete the payment from the <a href="{{ route('home2.pay.show', $order->id) }}">payment page</a>.</span>
                        @elseif ($st === 'awaiting_verification')
                            <strong>Waiting for payment verification.</strong>
                            <span class="muted small">@if ($latestPayment->proof_uploaded_at)Your payment proof was received {{ optional($latestPayment->proof_uploaded_at)->format('M d, Y H:i') }}. @endif Our team is reviewing it — this usually takes a few hours. You'll get a notification once verified.</span>
                        @elseif ($st === 'processing')
                            <strong>Processing payment.</strong>
                            <span class="muted small">The card payment is being confirmed with Stripe. This page updates automatically within a minute.</span>
                        @elseif ($st === 'paid')
                            <div style="color:#047857"><strong>✓ Payment verified &amp; received.</strong></div>
                            <span class="muted small">@if ($latestPayment->paid_at)Verified {{ optional($latestPayment->paid_at)->format('M d, Y H:i') }}. @endif Thank you — your order is fully paid.@if ($code === 'wallet') Paid from your wallet balance.@endif</span>
                        @elseif ($st === 'failed')
                            <div style="color:#b91c1c"><strong>Payment failed.</strong></div>
                            <span class="muted small">The payment did not go through. You can retry from the <a href="{{ route('home2.pay.show', $order->id) }}">payment page</a>.</span>
                        @elseif ($st === 'cancelled')
                            <div style="color:#b91c1c"><strong>Payment cancelled.</strong></div>
                            <span class="muted small">You can start again from the <a href="{{ route('home2.pay.show', $order->id) }}">payment page</a>.</span>
                        @elseif (str_contains($st, 'refund'))
                            <strong>Refunded.</strong>
                            <span class="muted small">A refund of <strong>${{ number_format((float) $latestPayment->amount_refunded, 2) }}</strong> was issued on this payment{{ ($code === 'wallet' || str_contains((string) data_get($latestPayment->metadata, 'last_refund_destination', ''), 'wallet')) ? ' and credited to your wallet' : '' }}.</span>
                        @else
                            <span class="muted small">{{ ucfirst(str_replace('_', ' ', $st)) }}.</span>
                        @endif
                    </div>
                    @if ($latestPayment->paid_at && !str_contains($st, 'refund'))
                        <span class="muted small">Paid {{ optional($latestPayment->paid_at)->format('M d, Y H:i') }}.</span>
                    @endif
                @endif
            </div>
        </div>

        
        <div class="h2-card" style="margin-top:1rem">
            <div class="h2-card-body">
                <h3 style="font-size:1.05rem;margin:0 0 .5rem">Actions</h3>

                @if ($products->isEmpty())
                    <p class="muted small" style="margin:0 0 .75rem">Shipment confirmation and tracking become available once products are registered.</p>
                @endif

                @if (count($products) && (int) $order->confirmation !== 1)
                    <details style="border:1px solid var(--h2-border,#e5e9f0);border-radius:8px;padding:.75rem;margin-bottom:.75rem">
                        <summary style="cursor:pointer;font-weight:600">Confirm shipment &amp; customs details</summary>
                        <form method="POST" action="{{ route('Order_confirmation', $order->id) }}" style="margin-top:.75rem">
                            @csrf
                            @foreach ($products as $i => $p)
                                <input type="hidden" name="product[{{ $i }}][product_id]" value="{{ $p->id }}">
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem">
                                    <label class="small" style="min-width:180px;flex:1">{{ $p->productname ?: ('Product #' . $p->id) }}
                                        <input type="number" step="0.01" min="0" name="product[{{ $i }}][weight]" value="{{ $p->custom_weight ?? $p->productweight }}" placeholder="Weight (kg)" required style="width:100%">
                                    </label>
                                    <label class="small" style="min-width:140px;flex:1">Declared value ($)
                                        <input type="number" step="0.01" min="0" name="product[{{ $i }}][value]" value="{{ $p->custom_value ?? $p->productprice }}" placeholder="Value" required style="width:100%">
                                    </label>
                                </div>
                            @endforeach
                            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                <label class="small">Category
                                    <input type="text" name="category" value="{{ $order->custom_category }}" required style="width:100%">
                                </label>
                                <label class="small">Recipient name
                                    <input type="text" name="name" value="{{ $order->ship_name }}" required style="width:100%">
                                </label>
                                <label class="small">Address line 1
                                    <input type="text" name="address1" value="{{ $order->ship_address1 }}" required style="width:100%">
                                </label>
                                <label class="small">Address line 2
                                    <input type="text" name="address2" value="{{ $order->ship_address2 }}" style="width:100%">
                                </label>
                                <label class="small">City
                                    <input type="text" name="city" value="{{ $order->ship_city }}" required style="width:100%">
                                </label>
                                <label class="small">State
                                    <input type="text" name="state" value="{{ $order->ship_state }}" required style="width:100%">
                                </label>
                                <label class="small">Postal code
                                    <input type="text" name="postalcode" value="{{ $order->ship_postalcode }}" required style="width:100%">
                                </label>
                                <label class="small">Country
                                    <select name="country" required style="width:100%">
                                        <option value="">— select —</option>
                                        @foreach ($countries as $c)
                                            <option value="{{ $c }}"{{ $order->ship_country === $c ? ' selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="small">Phone number
                                    <input type="text" name="number" value="{{ $order->ship_number }}" required style="width:100%">
                                </label>
                            </div>
                            <input type="hidden" name="confirmation" value="1">
                            <input type="hidden" name="custom_status" value="1">
                            <input type="hidden" name="custom_total_quantity" id="dp-cq" value="">
                            <input type="hidden" name="custom_total_weight" id="dp-cw" value="">
                            <input type="hidden" name="custom_total_value" id="dp-cv" value="">
                            <button type="button" class="h2-btn h2-btn-outline" style="margin-top:.5rem" onclick="dpSumCustoms()">Recalculate totals</button>
                            <button class="h2-btn h2-btn-primary" style="margin-top:.5rem" onclick="dpSumCustoms()">Submit confirmation</button>
                        </form>
                    </details>
                @elseif ((int) $order->confirmation === 1)
                    <p class="small" style="margin:0 0 .75rem"><span class="h2-badge" style="background:#137333;color:#fff">shipment confirmed</span> Thank you — customs details are on file.</p>
                @endif

                @if (count($products))
                    <details style="border:1px solid var(--h2-border,#e5e9f0);border-radius:8px;padding:.75rem;margin-bottom:.75rem">
                        <summary style="cursor:pointer;font-weight:600">Add tracking numbers (after you shipped)</summary>
                        <form method="POST" action="{{ route('tracking_id', $order->id) }}" enctype="multipart/form-data" style="margin-top:.75rem">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            @foreach ($products as $i => $p)
                                <input type="hidden" name="product[{{ $i }}][product_id]" value="{{ $p->id }}">
                                <input type="hidden" name="product[{{ $i }}][receipt]" value="None">
                                <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.4rem">
                                    <span class="small muted" style="min-width:160px;padding-top:.5rem">{{ $p->productname ?: ('Product #' . $p->id) }}</span>
                                    <label class="small" style="flex:1;min-width:160px">Tracking ID
                                        <input type="text" name="product[{{ $i }}][trackingid]" value="{{ $p->trackingid }}" required style="width:100%">
                                    </label>
                                    <label class="small" style="flex:1;min-width:220px">Tracking link
                                        <input type="url" name="product[{{ $i }}][trackinglink]" value="{{ $p->trackinglink }}" required style="width:100%">
                                    </label>
                                    <label class="small" style="flex:1;min-width:180px">Receipt image (optional)
                                        @if ($p->receipt && $p->receipt !== 'None')
                                            <span class="small muted" style="display:block">on file: <a href="{{ url('uploads/productsimages/' . $p->receipt) }}" target="_blank" rel="noopener">{{ $p->receipt }}</a></span>
                                        @endif
                                        <input type="file" name="product[{{ $i }}][receipt]" accept="image/*" style="width:100%">
                                    </label>
                                </div>
                            @endforeach
                            <button class="h2-btn h2-btn-primary" style="margin-top:.5rem">Save tracking</button>
                        </form>
                    </details>
                @endif

                @if ($order->order_status !== 'completed')
                    <form method="POST" action="{{ route('Order_complete', $order->id) }}" onsubmit="return confirm('Mark this order as completed?');">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <input type="hidden" name="order_status" value="completed">
                        <button class="h2-btn h2-btn-outline">Mark order completed</button>
                    </form>
                @else
                    <p class="small" style="margin:0"><span class="h2-badge" style="background:#137333;color:#fff">completed</span> Thanks for shipping with us!</p>
                @endif
            </div>
        </div>

        
        <div class="h2-card" style="margin-top:1rem">
            <div class="h2-card-body">
                <h3 style="font-size:1.05rem;margin:0 0 .5rem">Messages about this order</h3>
                <div id="dp-chat-list" style="max-height:320px;overflow-y:auto;border:1px solid var(--h2-border,#e5e9f0);border-radius:8px;padding:.75rem;display:flex;flex-direction:column;gap:.5rem"></div>
                <form id="dp-chat-form" style="display:flex;gap:.5rem;margin-top:.6rem">
                    <input type="text" id="dp-chat-input" maxlength="2000" placeholder="Write a message…" required style="flex:1">
                    <button class="h2-btn h2-btn-primary">Send</button>
                </form>
                <meta name="dp-chat-order" content="{{ $order->id }}">
                <meta name="dp-chat-url" content="{{ route('home2.orders.chat', $order->id) }}">
                <meta name="dp-chat-send" content="{{ route('home2.orders.chat.send', $order->id) }}">
            </div>
        </div>

    </div>
</section>


<?php /* SHIPPER SYSTEM (2026-09-10) customer sections — directive-free
       block: raw PHP control structures + Blade echoes only, so no
       directive pairing can ever break this block again. */ ?>

<div id="dp-shipper-marker" style="display:none">MARKER-XYZ</div>

<?php
    $dpAssignment = $order->has_shipper_assignment
        ? \App\Models\ShipperOrderAssignment::with(['proofs' => fn ($q) => $q->where('customer_visible', true)])
            ->where('order_id', $order->id)->first()
        : null;
    $dpTracking = $order->shipper_tracking_shared
        ? \App\Models\ShipperTrackingDetail::whereHas('assignment', fn ($q) => $q->where('order_id', $order->id))
            ->where('shared_with_customer', true)->first()
        : null;
    $dpRateable = $dpAssignment && in_array($dpAssignment->status, ['tracking_shared', 'delivered', 'completed']);
    $dpRating   = $dpRateable ? $dpAssignment->rating()->first() : null;
?>

<?php if ($dpAssignment && $dpAssignment->proofs->count()): ?>
<section class="h2-section">
    <h4>📦 Package Photos from Our Partner</h4>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px">
        <?php foreach ($dpAssignment->proofs as $dpProof): ?>
            <figure style="margin:0;text-align:center">
                <a href="{{ route('shipper.proofs.customer', [$order->id, $dpProof->id]) }}" target="_blank">
                    <img src="{{ route('shipper.proofs.customer', [$order->id, $dpProof->id]) }}" alt="{{ $dpProof->proof_type }}"
                         style="width:100%;height:130px;object-fit:cover;border-radius:8px;border:1px solid var(--h2-border)">
                </a>
                <figcaption style="font-size:12px;color:var(--h2-muted);margin-top:4px">
                    {{ ucwords(str_replace('_', ' ', $dpProof->proof_type)) }}
                </figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if ($order->shipper_proof_approved && !$order->delivery_address_submitted): ?>
<section class="h2-section" style="border-left:4px solid var(--h2-primary)">
    <h4>📍 Provide Your Delivery Address</h4>
    <p style="color:var(--h2-muted)">Your package is ready. We forward your address securely to the shipping partner — never directly exposed.</p>
    <form action="{{ route('shipper.delivery-address.submit', $order->id) }}" method="POST" class="h2-form h2-form-wide">
        @csrf
        <div class="h2-field-grid">
            <div><label>Full name *</label><input type="text" name="recipient_name" required></div>
            <div><label>Phone</label><input type="text" name="phone"></div>
            <div style="grid-column:1/-1"><label>Address line 1 *</label><input type="text" name="address_line_1" required></div>
            <div style="grid-column:1/-1"><label>Address line 2</label><input type="text" name="address_line_2"></div>
            <div><label>City *</label><input type="text" name="city" required></div>
            <div><label>State</label><input type="text" name="state"></div>
            <div><label>Postal code *</label><input type="text" name="postal_code" required></div>
            <div><label>Country *</label><input type="text" name="country" required></div>
        </div>
        <label>Delivery instructions (optional)</label>
        <textarea name="delivery_instructions" rows="2"></textarea>
        <button class="h2-btn h2-btn-primary" style="margin-top:12px">Submit Delivery Address</button>
    </form>
</section>
<?php endif; ?>

<?php if ($dpTracking): ?>
<section class="h2-section" style="border-left:4px solid #198754">
    <h4>🚀 Your Package is on the Way</h4>
    <p>
        Carrier: <b>{{ $dpTracking->carrier }}</b> ·
        Tracking: <code>{{ $dpTracking->tracking_number }}</code> ·
        Shipped: {{ $dpTracking->ship_date?->format('d M Y') }}
        @if($dpTracking->estimated_delivery) · Est. delivery: {{ $dpTracking->estimated_delivery->format('d M Y') }}@endif
    </p>
    @if($dpTracking->tracking_url)
        <a class="h2-btn h2-btn-primary" href="{{ $dpTracking->tracking_url }}" target="_blank" rel="noopener">Track Package →</a>
    @endif
</section>
<?php endif; ?>

<?php if ($dpAssignment): ?>
<?php
    $dpSteps = [
        ['Assigned to partner', true],
        [$order->product_purchase ? 'Purchased' : 'Package received', in_array($dpAssignment->status, ['purchased','awaiting_package','package_received','proof_uploaded','proof_approved','address_received','address_forwarded','dispatched','tracking_added','tracking_shared','delivered','completed'])],
        ['Photos approved', in_array($dpAssignment->status, ['proof_approved','address_received','address_forwarded','dispatched','tracking_added','tracking_shared','delivered','completed'])],
        ['Address forwarded', in_array($dpAssignment->status, ['address_forwarded','dispatched','tracking_added','tracking_shared','delivered','completed'])],
        ['In transit', in_array($dpAssignment->status, ['tracking_added','tracking_shared','delivered','completed'])],
        ['Completed', $dpAssignment->status === 'completed'],
    ];
    $dpPartner = $dpAssignment->shipper;
?>
<section class="h2-section" style="border-left:4px solid var(--h2-primary)">
    <h4>📦 Your Order Progress</h4>
    <ol style="margin:0;padding-left:18px;color:var(--h2-muted)">
        <?php foreach ($dpSteps as $dpStep): ?>
            <li style="{{ $dpStep[1] ? 'color:#137333;font-weight:600' : '' }}">{{ $dpStep[1] ? '✅' : '⏳' }} {{ $dpStep[0] }}</li>
        <?php endforeach; ?>
    </ol>
    <?php if ($dpTracking?->estimated_delivery): ?>
        <p style="margin:8px 0 0;color:var(--h2-muted)">Estimated delivery: <b style="color:#16324f">{{ $dpTracking->estimated_delivery->format('d M Y') }}</b></p>
    <?php endif; ?>
    <?php if ($dpPartner && $dpPartner->total_ratings > 0): ?>
        <p style="margin:4px 0 0;color:var(--h2-muted)">Partner performance:
            <span class="star">⭐ {{ $dpPartner->rating }}</span>
            ({{ ['','Starter','Verified','Elite'][$dpPartner->level] ?? '' }} partner, {{ $dpPartner->total_completed }} deliveries)</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($dpRateable): ?>
<section class="h2-section" style="border-left:4px solid #f5b301">
    <h4>⭐ Rate Your Shipping Partner</h4>
    <?php if ($dpRating): ?>
        <p style="color:var(--h2-muted)">You rated this delivery <b class="star">{{ str_repeat('★', (int) $dpRating->overall_rating) }}{{ str_repeat('☆', 5 - (int) $dpRating->overall_rating) }}</b> — thank you!</p>
        <?php if ($dpRating->review_text): ?><p style="font-style:italic">“{{ $dpRating->review_text }}”</p><?php endif; ?>
    <?php else: ?>
        <form action="{{ route('shipper.rate', $order->id) }}" method="POST" class="h2-form h2-form-wide">
            @csrf
            <label>Your rating *</label>
            <div style="display:flex;gap:6px;margin-bottom:8px" id="dpStars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label style="margin:0;cursor:pointer;font-size:28px" title="{{ $i }}">
                        <input type="radio" name="overall_rating" value="{{ $i }}" required style="position:absolute;opacity:0" {{ old('overall_rating') == $i ? 'checked' : '' }}>
                        <span class="star" data-star="{{ $i }}" style="color:#cfd8e3">★</span>
                    </label>
                <?php endfor; ?>
            </div>
            <script>
                (function () {
                    var wrap = document.getElementById('dpStars');
                    if (!wrap) return;
                    function paint(n) {
                        wrap.querySelectorAll('[data-star]').forEach(function (s) {
                            s.style.color = Number(s.dataset.star) <= n ? '#f5b301' : '#cfd8e3';
                        });
                    }
                    wrap.addEventListener('change', function (e) { paint(Number(e.target.value)); });
                    var checked = wrap.querySelector('input:checked');
                    if (checked) paint(Number(checked.value));
                })();
            </script>
            <label>Review (optional)</label>
            <textarea name="review_text" rows="3" placeholder="How was the shipping partner's service?"></textarea>
            <label>May we publish your words as a testimonial?</label>
            <select name="consent_testimonial" required>
                <option value="no">No — keep it private</option>
                <option value="anonymous" selected>Yes — anonymously</option>
                <option value="first_name_only">Yes — first name only</option>
                <option value="full_name">Yes — with my full name</option>
            </select>
            <button class="h2-btn h2-btn-primary" style="margin-top:12px">Submit Rating</button>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>
@endsection

@push('scripts')
<script>
(function () {
    var list = document.getElementById('dp-chat-list');
    var input = document.getElementById('dp-chat-input');
    var form = document.getElementById('dp-chat-form');
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var getUrl = document.querySelector('meta[name="dp-chat-url"]').getAttribute('content');
    var sendUrl = document.querySelector('meta[name="dp-chat-send"]').getAttribute('content');

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s === null || s === undefined ? '' : String(s);
        return d.innerHTML;
    }

    function render(messages) {
        if (!messages.length) {
            list.innerHTML = '<span class="muted small">No messages yet — ask us anything about this order.</span>';
            return;
        }
        var html = '';
        messages.forEach(function (m) {
            var body = m.body ? esc(m.body) : '';
            var img = m.image ? '<a href="' + m.image + '" target="_blank" rel="noopener"><img src="' + m.image + '" alt="attachment" style="max-width:140px;border-radius:8px;display:block;margin-top:.25rem"></a>' : '';
            html += '<div style="align-self:' + (m.mine ? 'flex-end' : 'flex-start') + ';max-width:80%;background:' + (m.mine ? 'var(--h2-accent,#0b5fff)' : '#f1f4f9') + ';color:' + (m.mine ? '#fff' : '#1c2430') + ';padding:.5rem .75rem;border-radius:12px">'
                + body + img
                + '<div style="font-size:.7rem;opacity:.7;margin-top:.2rem">' + esc(m.created_at) + '</div>'
                + '</div>';
        });
        list.innerHTML = html;
        list.scrollTop = list.scrollHeight;
    }

    function refresh() {
        fetch(getUrl, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (d) { render(d.messages || []); })
            .catch(function () {});
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = input.value.trim();
        if (!msg) return;
        fetch(sendUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ message: msg })
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.ok) { input.value = ''; refresh(); }
        }).catch(function () {});
    });

    refresh();
    setInterval(refresh, 8000);
})();
</script>

<script>
function dpSumCustoms() {
    var w = document.querySelectorAll('input[name^="product"][name$="[weight]"]');
    var v = document.querySelectorAll('input[name^="product"][name$="[value]"]');
    var tw = 0, tv = 0;
    w.forEach(function (el) { tw += parseFloat(el.value) || 0; });
    v.forEach(function (el) { tv += parseFloat(el.value) || 0; });
    var cq = document.getElementById('dp-cq'), cw = document.getElementById('dp-cw'), cv = document.getElementById('dp-cv');
    if (cq) cq.value = w.length;
    if (cw) cw.value = tw.toFixed(2);
    if (cv) cv.value = tv.toFixed(2);
}
document.addEventListener('DOMContentLoaded', function () {
    var f = document.querySelector('form[action*="Order_confirmation"]');
    if (f) f.addEventListener('submit', dpSumCustoms);
});
</script>
@endpush
