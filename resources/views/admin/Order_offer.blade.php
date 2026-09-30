@extends('layouts.tailwind.app')

@section('title', 'Order #' . $order->order_id)
@section('page_title', 'Order #' . $order->order_id)
@section('page_subtitle', 'Legacy order/offer workflow — placed ' . optional($order->created_at)->format('d M Y, H:i'))

{{-- This page still needs the stock (non-bs4) DataTables build for the custom-declaration
     table near the bottom of the page ($("#custom_table").DataTable(...)). No Bootstrap JS
     is loaded by layouts.tailwind.app, so the legacy Bootstrap tabs/modals/collapse widgets
     on this page have been re-implemented with Alpine.js below instead of relying on it. --}}
@push('admin_scripts')
<script src="{{ url('dashbord/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('dashbord/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
@endpush

{{-- The @include('chat') floating chat widget below (.chat, .chat_header, .fab_field,
     etc.) is styled entirely by this legacy stylesheet, which layouts.tailwind.app does
     not load globally. It's class-scoped (no bare body/a/table/.btn/.card rules), so it's
     safe to push just for this page rather than converting the widget's markup. --}}
@push('admin_styles')
<link rel="stylesheet" href="{{ url('dashbord/css/custom.css') }}">
@endpush

@section('content')

<section class="content">

    <div>
        <div class="flex flex-wrap gap-y-3 -mx-2" x-data="{ tab: {{ (int) ($order->active_tab ?: 1) }} }">
            <h5 class="w-full px-2 text-lg font-semibold text-slate-800">Order #{{$order->order_id}}</h5>

            {{-- ============ PM-016: payment verification panel ============ --}}
            @if (!empty($payment))
                @php
                    $pmStates = [
                        'awaiting_payment'      => ['Bank transfer started — awaiting payment', 'badge-warning'],
                        'awaiting_verification' => ['Waiting for bank payment verification', 'badge-warning'],
                        'processing'            => ['Card payment processing', 'badge-info'],
                        'paid'                  => ['Payment verified ✓', 'badge-success'],
                        'partially_refunded'    => ['Partially refunded', 'badge-info'],
                        'refunded'              => ['Refunded', 'badge-info'],
                        'failed'                => ['Payment failed', 'badge-danger'],
                        'cancelled'             => ['Payment cancelled', 'badge-secondary'],
                        'pending'               => ['Payment pending', 'badge-secondary'],
                        'method_selected'       => ['Payment method selected', 'badge-secondary'],
                    ];
                    $pmState = $pmStates[$payment->status] ?? [ucfirst($payment->status), 'badge-secondary'];
                    $pmCanAct = in_array($payment->status, ['awaiting_payment', 'awaiting_verification']);
                    $pmBadgeClasses = [
                        'badge-warning'   => 'bg-yellow-100 text-yellow-800',
                        'badge-info'      => 'bg-cyan-100 text-cyan-700',
                        'badge-success'   => 'bg-green-100 text-green-700',
                        'badge-danger'    => 'bg-red-100 text-red-700',
                        'badge-secondary' => 'bg-slate-100 text-slate-700',
                    ][$pmState[1]] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <div class="mb-4 w-full px-2">
                    <div class="rounded-lg border {{ $pmCanAct ? 'border-amber-300' : 'border-green-300' }} bg-white shadow-sm">
                        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-4 py-3">
                            <strong class="text-sm text-slate-800"><i class="fas fa-money-check-alt mr-1 text-slate-400"></i> Payment</strong>
                            <span class="text-sm text-slate-500">{{ $payment->reference }}</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pmBadgeClasses }}">{{ $pmState[0] }}</span>
                        </div>
                        <div class="p-4">
                            <div class="flex flex-wrap gap-y-3 -mx-2">
                                <div class="w-full md:w-7/12 px-2">
                                    <table class="w-full text-sm">
                                        <tr>
                                            <td class="text-slate-500" style="width:140px">Amount</td>
                                            <td><strong>{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-slate-500">Method</td>
                                            <td>{{ $payment->payment_method_code ?: '—' }}</td>
                                        </tr>
                                        @if ($payment->paid_at)
                                            <tr>
                                                <td class="text-slate-500">Received at</td>
                                                <td>{{ optional($payment->paid_at)->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endif
                                        @if ($pmCanAct)
                                            <tr>
                                                <td class="text-slate-500">Receipt</td>
                                                <td>
                                                    @if ($payment->proof_path)
                                                        <a href="{{ route('admin.payments.proof', $payment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50">
                                                            <i class="fas fa-file-download"></i> View uploaded receipt
                                                        </a>
                                                    @else
                                                        <span class="text-slate-500">no receipt uploaded (manual confirmation from bank statement)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                                <div class="w-full md:w-5/12 px-2">
                                    @if ($pmCanAct)
                                        <form method="POST" action="{{ route('admin.orders.payment-verify', $order->id) }}"
                                              onsubmit="return confirm('Confirm the money has been received for {{ $payment->reference }}?')">
                                            @csrf
                                            <input type="hidden" name="approve" value="1">
                                            <input type="hidden" name="note" value="Confirmed on bank statement from order page">
                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                                                <i class="fas fa-check mr-1"></i> @if($payment->status === 'awaiting_verification') Verify receipt — mark payment received @else Mark payment as received (manual) @endif
                                            </button>
                                        </form>
                                        @if ($payment->status === 'awaiting_verification')
                                            <form method="POST" action="{{ route('admin.orders.payment-verify', $order->id) }}" style="margin-top:.5rem"
                                                  onsubmit="return confirm('Reject this receipt? The customer will have to start a new payment.')">
                                                @csrf
                                                <input type="hidden" name="approve" value="0">
                                                <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-md border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-50">Reject receipt</button>
                                            </form>
                                        @endif
                                        <p class="text-slate-500 text-xs mt-2 mb-0">Marking received confirms the amount and lets the order move to the next step.</p>
                                    @elseif ($payment->status === 'paid')
                                        <div class="rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800"><i class="fas fa-check-circle mr-1"></i>Payment verified — the order flow can continue.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            {{-- ============ end payment panel ============ --}}
            <div class="w-full px-2">
                @include('admin.partials.order-attachments-tailwind', ['order' => $order, 'attachmentsRole' => 'admin'])
            </div>
            <div class="w-full px-2 board">
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <input type="hidden" name="active_tab" value="{{ $order->active_tab }}" class="active_tab">
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    <div class="border-b border-slate-100 px-4 py-3">
                        {{-- Legacy 4-step wizard (Bootstrap nav-pills originally). No Bootstrap JS is
                             loaded on this layout, so tab switching is driven by Alpine's `tab` state
                             (declared on the outer wrapper, seeded from $order->active_tab). A tab
                             pill is only clickable once the wizard has reached (or passed) it — this
                             mirrors the old jQuery init that disabled pills ahead of active_tab. --}}
                        <ul class="flex flex-wrap items-center gap-4" id="pills-tab" role="tablist">
                            <li class="flex items-center gap-2">
                                <a href="#pills-home" @click.prevent="tab = 1"
                                   class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-semibold"
                                   :class="tab === 1 ? 'border-brand text-brand' : 'border-slate-200 text-slate-400'">1</a>
                                <h3 class="text-sm font-medium text-slate-700">Make an Offer</h3>
                            </li>
                            <li class="flex items-center gap-2">
                                <a href="#pills-profile" @click.prevent="if (tab >= 2) tab = 2"
                                   class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-semibold"
                                   :class="[tab === 2 ? 'border-brand text-brand' : 'border-slate-200 text-slate-400', tab < 2 ? 'pointer-events-none opacity-50' : '']">2</a>
                                <h3 class="text-sm font-medium text-slate-700">Offer Response</h3>
                            </li>
                            <li class="flex items-center gap-2">
                                <a href="#pills-contact" @click.prevent="if (tab >= 3) tab = 3"
                                   class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-semibold"
                                   :class="[tab === 3 ? 'border-brand text-brand' : 'border-slate-200 text-slate-400', tab < 3 ? 'pointer-events-none opacity-50' : '']">3</a>
                                <h3 class="text-sm font-medium text-slate-700">Ready to Ship</h3>
                            </li>
                            <li class="flex items-center gap-2">
                                <a href="#pills-example" @click.prevent="if (tab >= 4) tab = 4"
                                   class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-sm font-semibold"
                                   :class="[tab === 4 ? 'border-brand text-brand' : 'border-slate-200 text-slate-400', tab < 4 ? 'pointer-events-none opacity-50' : '']">4</a>
                                <h3 class="text-sm font-medium text-slate-700">Order Status</h3>
                            </li>
                        </ul>
                    </div>
                    <div class="p-4">
                        <div id="pills-tabContent">
                            <div x-show="tab === 1" x-cloak id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                                <h2 class="text-center">Request Details</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full sm:w-1/3">
                                        <h4> Ship From</h4>
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <h5>{{$order->shipfrom}}</h5>
                                    </div>
                                </div>
                                <h3 class="my-3">Destination</h3>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full sm:w-1/4">
                                        <h4> Ship To</h4>
                                    </div>
                                    <div class="w-full sm:w-1/4">
                                        <h5>{{$order->shipto}}</h5>
                                    </div>
                                    <div class="w-full sm:w-1/4">
                                        <h4>Postal Code</h4>
                                    </div>
                                    <div class="w-full sm:w-1/4">
                                        <h5>{{$order->postalcode}}</h5>
                                    </div>
                                  </div>  
                                
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-3">
                                    <div class="w-full sm:w-1/4">
                                        <h4>Address</h4>
                                    </div>
                                    <div class="w-full sm:w-3/4">
                                        <h5>{{$order->address}}</h5>
                                    </div>
                                </div>
                                <hr>
                                <h3 class="my-4"> Cutomer Selected Services</h3>
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-2 Service_charges">
                                    <div class="w-full sm:w-1/3">
                                        <h4>Product Photo:</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_photo}}</h5>
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <h4>Removal Of Prohibited items:</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_prohibited}}</h5>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                    <div class="w-full sm:w-1/3">
                                        <h4>Custom Decleration</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_customs}}</h5>
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <h4>Package consolidation:</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_consolidation}}</h5>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                    <div class="w-full sm:w-1/3">
                                        <h4>Content Check:</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_check}}</h5>
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <h4>Forward Service Fee:</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_services}}</h5>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                    <div class="w-full sm:w-1/3">
                                        <h4>Product Disinfection</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_disinfection}}</h5>
                                    </div>
                                    @if($order->product_purchase == 0)
                                    @else
                                    <div class="w-full sm:w-1/3">
                                        <h4>Purchase Assistance</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$order->product_purchase}}</h5>
                                    </div>
                                    @endif
                                </div>
                                <hr>
                                @if(is_null($order->edit_offer))
                                @if(is_null($offer))
                                <form action="{{route('orderoffer.store')}}" method="post" id="tab-2" enctype="multipart/form-data">
                                    @csrf
                                    <div class="flex flex-wrap gap-y-3 -mx-2">
                                        <div class="w-full">
                                            <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                    <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                                </div>
                                                <!-- <form action="{{route('orderoffer.store')}}" method="post" id="tab-2" enctype="multipart/form-data">
                                                        @csrf -->
                                                @if($order->product_purchase == 0)
                                                <!-- /.card-header -->
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table id="example1" class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                            
                                                               
                                                                 
                                                                   <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Product Quantity</th>
                                                                    <th class="text-slate-500">Product price (USD$)</th>
                                                                     <th class="text-slate-500">Weight</th>
                                                             
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($products as $product)
                                                                <tr>
                                                         
                                                            <td>{{$product->productname}}</td>
                                                                    <td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
                                                                    <td>{{$product->productquantity}}</td>
                                                                    <td>{{$product->productprice}}</td>
                                                                    <td>{{$product->approximate_weight}}</td>
                                                              </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                                @else
                                                <!-- /.card-header -->
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Product Quantity</th>
                                                                    <th class="text-slate-500">Price/ per unit</th>
                                                                    <th class="text-slate-500">Spread</th>
                                                                    <th class="text-slate-500">Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($products as $product)
                                                                <tr>
                                                                    <td>{{$product->productname}}
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][productname]" value="{{$product->productname}}" class="quantity" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td><a href="{{$product->producturl}}" target="_blank">Go to link</a>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][producturl]" value="{{$product->producturl}}" class="quantity" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td>{{$product->productquantity}}
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][productquantity]" value="{{$product->productquantity}}" class="quantity" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][productquantity]" value="{{$product->productquantity}}" class="quantity" />
                                                                        <input type="number" name="product[{{$loop->index}}][productprice]" value="{{$product->productprice}}" class="product_input price w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm" min="0" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="number" name="product[{{$loop->index}}][spread]" value="0" class="product_input spread w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm" min="0" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="number" name="product[{{$loop->index}}][total]" value="" class="product_input producttotal w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm bg-slate-50" min="0" readonly />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tr>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td>
                                                                    <h3>Product Total</h3>
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="net_ttotal" value="" class="product_input net_total w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm font-semibold" min="0" readonly />
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <h2 class="text-center my-4">Make an Offer</h2>
                                    <h3 class="my-3">Offered Services</h3>
                                    <div class="flex flex-wrap gap-y-3 -mx-2">
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[0][servicename]" value="Product photo" type="hidden" />
                                            <input name="addmore[0][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[0][servicestatus]" value="1" type="checkbox" id="address_1" class="example" /> <label for="address_1">Product photo </label>
                                            <input name="addmore[0][servicevalue]" value="3" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[1][servicename]" value="Customs Declaration" type="hidden" />
                                            <input name="addmore[1][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[1][servicestatus]" value="1" type="checkbox" id="address_2" class="example" /> <label for="address_2">Customs Declaration </label>
                                            <input name="addmore[1][servicevalue]" value="4" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[2][servicename]" value="Content Check" type="hidden" />
                                            <input name="addmore[2][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[2][servicestatus]" value="1" type="checkbox" id="address_3" class="example" /> <label for="address_3">Content Check </label>
                                            <input name="addmore[2][servicevalue]" value="3" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[3][servicename]" value="Removal of Prohibited Items" type="hidden" />
                                            <input name="addmore[3][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[3][servicestatus]" value="1" type="checkbox" id="address_4" class="example" /> <label for="address_4">Removal of Prohibited Items </label>
                                            <input name="addmore[3][servicevalue]" value="1" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[4][servicename]" value="Product Disinfection" type="hidden" />
                                            <input name="addmore[4][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[4][servicestatus]" value="1" type="checkbox" id="address_5" class="example" /> <label for="address_5">Disinfection </label>
                                            <input name="addmore[4][servicevalue]" value="1" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[5][servicename]" value="Package Consolidation" type="hidden" />
                                            <input name="addmore[5][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[5][servicestatus]" value="1" type="checkbox" id="address_6" class="example" /> <label for="address_6">Package Consolidation </label>
                                            <input name="addmore[5][servicevalue]" value="6" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                        <div class="w-full sm:w-1/2 services_bold font-semibold">
                                            <input name="addmore[6][servicename]" value="Forwarding Service Fee" type="hidden" />
                                            <input name="addmore[6][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[6][servicestatus]" value="1" type="checkbox" id="address_7" class="example" checked readonly /> <label for="address_7">Forwarding Service Fee </label>
                                            <input name="addmore[6][servicevalue]" value="9" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2 services_bold font-semibold">
                                            <input name="addmore[8][servicename]" value="shipping fee" type="hidden" />
                                            <input name="addmore[8][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[8][servicestatus]" value="1" type="checkbox" id="address_8" class="example" /> <label for="address_8">Shipping Fee </label>
                                            <input name="addmore[8][servicevalue]" value="0" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        @if($order->product_purchase == 0)
                                        @else
                                        <div class="w-full sm:w-1/2 services_bold font-semibold">
                                            <input name="addmore[7][servicename]" value="Purchase Assistance" type="hidden" />
                                            <input name="addmore[7][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[7][servicestatus]" value="1" type="checkbox" id="purchase_7" class="example" checked readonly /> <label for="purchase_7">Purchase Assistance Fee </label>
                                            <input name="addmore[7][servicevalue]" value="10" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        @endif
                                    </div>
                                    <h3 class="my-3">Additional Services</h3>
                                    <div class="flex flex-wrap gap-y-3 -mx-2">
                                        <div class="w-full">
                                            <table class="w-full text-left text-sm border border-slate-200" id="dynamicTable">
                                                <tr>
                                                    <th>Service Name</th>
                                                    <th>Service Price</th>
                                                    <th>Add More</th>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" name="more[0][servicename]" value="" placeholder="Service Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" /></td>
                                                    <td><input type="number" name="more[0][servicevalue]" value="" id="address" placeholder="Service Price" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full additional-services" /></td>
                                                    <td><button type="button" name="add" id="add" class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-1.5 text-white hover:bg-green-700"><i class="far fa-plus"></i></button></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    @if($order->product_purchase == 0)
                                    <h3 class="my-3">Shipping Address</h3>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 mb-3">
                                        <div class="w-full">
                                            <table class="w-full text-left text-sm border border-slate-200" id="dynamicTable">
                                                <tr>
                                                    <th>Country</th>
                                                    <th>Shipping Address</th>
                                                </tr>
                                                <tr>
                                                    <td class="shipfrom_td">
                                                        <select name="shipfrom" id="shipfrom" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full country">
                                                            <!-- <option value="" disabled >Choose Employee</option> -->
                                                            <option disabled value="" selected>Select Country</option>
                                       <option value="Afghanistan">Afghanistan</option>
                                <option value="Åland Islands">Åland Islands</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="American Samoa">American Samoa</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Anguilla">Anguilla</option>
                                <option value="Antarctica">Antarctica</option>
                                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Aruba">Aruba</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bermuda">Bermuda</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Bouvet Island">Bouvet Island</option>
                                <option value="Brazil">Brazil</option>
                                <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                <option value="Brunei Darussalam">Brunei Darussalam</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Cape Verde">Cape Verde</option>
                                <option value="Cayman Islands">Cayman Islands</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Christmas Island">Christmas Island</option>
                                <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo">Congo</option>
                                <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                <option value="Cook Islands">Cook Islands</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Cote D'ivoire">Cote D'ivoire</option>
                                <option value="Croatia">Croatia</option>
                                <option value="Cuba">Cuba</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                <option value="Faroe Islands">Faroe Islands</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="French Guiana">French Guiana</option>
                                <option value="French Polynesia">French Polynesia</option>
                                <option value="French Southern Territories">French Southern Territories</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Gibraltar">Gibraltar</option>
                                <option value="Greece">Greece</option>
                                <option value="Greenland">Greenland</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guadeloupe">Guadeloupe</option>
                                <option value="Guam">Guam</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guernsey">Guernsey</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-bissau">Guinea-bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hong Kong">Hong Kong</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                <option value="Iraq">Iraq</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Isle of Man">Isle of Man</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jersey">Jersey</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                <option value="Korea, Republic of">Korea, Republic of</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Macao">Macao</option>
                                <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Martinique">Martinique</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mayotte">Mayotte</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                                <option value="Moldova, Republic of">Moldova, Republic of</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montenegro">Montenegro</option>
                                <option value="Montserrat">Montserrat</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="Netherlands Antilles">Netherlands Antilles</option>
                                <option value="New Caledonia">New Caledonia</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Niue">Niue</option>
                                <option value="Norfolk Island">Norfolk Island</option>
                                <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Pitcairn">Pitcairn</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Puerto Rico">Puerto Rico</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Reunion">Reunion</option>
                                <option value="Romania">Romania</option>
                                <option value="Russian Federation">Russian Federation</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="Saint Helena">Saint Helena</option>
                                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Serbia">Serbia</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia">Slovakia</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                <option value="Swaziland">Swaziland</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Timor-leste">Timor-leste</option>
                                <option value="Togo">Togo</option>
                                <option value="Tokelau">Tokelau</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Viet Nam">Viet Nam</option>
                                <option value="Virgin Islands, British">Virgin Islands, British</option>
                                <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                                <option value="Wallis and Futuna">Wallis and Futuna</option>
                                <option value="Western Sahara">Western Sahara</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="shipingaddress" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="shipingaddress" value="" list="addresses" required />
                                                        @include('admin.address')
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="shipingaddress" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="shipingaddress" value="0" required>
                                    @endif
                                    <div class="flex flex-wrap gap-y-3 -mx-2 mb-3">
                                        <label for="net_total" class="w-full sm:w-1/6 text-sm font-medium text-slate-700 sm:pt-2">Total Amount</label>
                                        <div class="w-full sm:w-5/6">
                                            <input type="text" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="net_total" value="" name="total" required>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 mb-3">
                                        <label for="shipping_detail" class="w-full sm:w-1/6 text-sm font-medium text-slate-700 sm:pt-2">Offer Description:</label>
                                        <div class="w-full sm:w-5/6">
                                            <textarea name="shipping_detail" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="shipping_detail" required></textarea>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 text-right">
                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                        <div class="w-full">
                                            <button type="submit" form="tab-2" class="offer-btn inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Make an Offer</button>
                                        </div>
                                    </div>
                                </form>
                                @else
                                @if (! (is_null($offer)))
                                <h2 class="text-center my-4">Offer Detail</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <!-- <form action="{{route('orderoffer.store')}}" method="post" id="tab-2" enctype="multipart/form-data">
                                                    @csrf -->
                                            @if($order->product_purchase == 0)
                                            <!-- /.card-header -->
                                            <div class="">
                                                <div class="overflow-x-auto">
                                                    <table id="example1" class="w-full text-left text-sm">
                                                        <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                            <tr>
                                                                <th class="text-slate-500">Product Name</th>
                                                                <th class="text-slate-500">Product Url</th>
                                                                <th class="text-slate-500">Product Quantity</th>
                                                                <th class="text-slate-500">Product price</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                            @foreach($products as $product)
                                                            <tr>
                                                                <td>{{$product->productname}}</td>
                                                                <td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
                                                                <td>{{$product->productquantity}}</td>
                                                                <td>{{$product->productprice}}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- /.table-responsive -->
                                            </div>
                                            @else
                                            <!-- /.card-header -->
                                            <div class="">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-sm">
                                                        <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                            <tr>
                                                                <th class="text-slate-500">Product Name</th>
                                                                <th class="text-slate-500">Product Url</th>
                                                                <th class="text-slate-500">Product Quantity</th>
                                                                <th class="text-slate-500">Price/ per unit</th>
                                                                <th class="text-slate-500">Spread</th>
                                                                <th class="text-slate-500">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                            @foreach($offer_products as $product)
                                                            <tr>
                                                                <td>{{$product->productname}}</td>
                                                                <td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
                                                                <td>{{$product->productquantity}}</td>
                                                                <td>{{$product->productprice}}</td>
                                                                <td>{{$product->productspread}}</td>
                                                                <td>{{$product->product_total}}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>
                                                                <h3>Product Total</h3>
                                                            </td>
                                                            <td>{{$offer->product_total}}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <!-- /.table-responsive -->
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($offer->shipingaddress != 0)
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full sm:w-1/4">
                                        <h3 class="my-">Shipping Address:</h3>
                                    </div>
                                    <div class="w-full sm:w-3/4">
                                        <h5>{{$offer->shipingaddress}}</h5>
                                    </div>
                                </div>
                                @else
                                @endif
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full sm:w-1/4">
                                        <h3 class="my-">Offer Description:</h3>
                                    </div>
                                    <div class="w-full sm:w-3/4">
                                        <h5>{{$offer->description}}</h5>
                                    </div>
                                </div>
                                <h3 class="text-center my-4">Additional Services</h3>
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-4">
                                    @foreach($offer_services as $services)
                                    <div class="w-full sm:w-1/3">
                                        <h4>{{$services->servicename}}</h4>
                                    </div>
                                    <div class="w-full sm:w-1/6">
                                        <h5>$ {{$services->servicevalue}}</h5>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="flex flex-wrap gap-y-3 -mx-2 my-4 total">
                                    <div class="w-full sm:w-1/3">
                                        <h4>Total</h4>
                                    </div>
                                    <div class="w-full sm:w-1/3">
                                        <h4>$ {{$offer->total}}</h4>
                                    </div>
                                </div>
                                @endif
                                <h2 class="text-center my-4">Offer has been submitted successfuly</h2>
                                @endif
                                @else
                                <form action="{{route('adminorders.update',$order->id)}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <h3 class="my-4">Previous Offered Services</h3>

                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-4">
                                        @foreach($offer_services as $services)
                                        <div class="w-full sm:w-1/3">
                                            <h4>{{$services->servicename}}</h4>
                                        </div>
                                        <div class="w-full sm:w-1/6">
                                            <h5>$ {{$services->servicevalue}}</h5>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-4 total">
                                        <div class="w-full sm:w-1/3">
                                            <h4>Offer Total</h4>
                                        </div>
                                        <div class="w-full sm:w-1/3">
                                            <h4>$ {{$offer->total}}</h4>
                                        </div>
                                    </div>
                                    <hr>
                                    <h2 class="text-center my-4">Update Offer</h2>
                                    <div class="flex flex-wrap gap-y-3 -mx-2">
                                        <div class="w-full">
                                            <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                    <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                                </div>
                                                <!-- <form action="{{route('orderoffer.store')}}" method="post" id="tab-2" enctype="multipart/form-data">
                                                        @csrf -->
                                                @if($order->product_purchase == 0)
                                                <!-- /.card-header -->
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table id="example1" class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Product Quantity</th>
                                                                    <th class="text-slate-500">Product price</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($products as $product)
                                                                <tr>
                                                                    <td>{{$product->productname}}</td>
                                                                    <td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
                                                                    <td>{{$product->productquantity}}</td>
                                                                    <td>{{$product->productprice}}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                                @else
                                                <!-- /.card-header -->
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Product Quantity</th>
                                                              <!--      <th class="text-slate-500">Price/ per unit</th>
                                                                  -->  <th class="text-slate-500">Spread</th>
                                                                    <th class="text-slate-500">Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($offer_products as $product)
                                                                <tr>
                                                                    <td>{{$product->productname}}
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][productname]" value="{{$product->productname}}" class="quantity" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td><a href="{{$product->producturl}}" target="_blank">Go to link</a>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][producturl]" value="{{$product->producturl}}" class="quantity" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td>{{$product->productquantity}}
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][productquantity]" value="{{$product->productquantity}}" class="quantity" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                  <!--  <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][productquantity]" value="{{$product->productquantity}}" class="quantity" />
                                                                        <input type="number" name="product[{{$loop->index}}][productprice]" value="{{$product->productprice}}" class="product_input price w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm" min="0" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    --> <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="number" name="product[{{$loop->index}}][spread]" value="{{$product->productspread}}" class="product_input spread w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm" min="0" />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="number" name="product[{{$loop->index}}][total]" value="{{$product->product_total}}" class="product_input producttotal w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm bg-slate-50" min="0" readonly />
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tr>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td>
                                                                    <h3>Product Total</h3>
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="net_ttotal" value="" class="product_input net_total w-full rounded-md border border-slate-300 px-2 py-1 text-center text-sm font-semibold" min="0" readonly />
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2">
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[0][servicename]" value="Product photo" type="hidden" />
                                            <input name="addmore[0][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[0][servicestatus]" value="1" type="checkbox" id="address_1" class="example" /> <label for="address_1">Product photo </label>
                                            <input name="addmore[0][servicevalue]" value="3" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[1][servicename]" value="Customs Declaration" type="hidden" />
                                            <input name="addmore[1][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[1][servicestatus]" value="1" type="checkbox" id="address_2" class="example" /> <label for="address_2">Customs Declaration </label>
                                            <input name="addmore[1][servicevalue]" value="4" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[2][servicename]" value="Content Check" type="hidden" />
                                            <input name="addmore[2][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[2][servicestatus]" value="1" type="checkbox" id="address_3" class="example" /> <label for="address_3">Content Check </label>
                                            <input name="addmore[2][servicevalue]" value="3" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[3][servicename]" value="Removal of Prohibited Items" type="hidden" />
                                            <input name="addmore[3][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[3][servicestatus]" value="1" type="checkbox" id="address_4" class="example" /> <label for="address_4">Removal of Prohibited Items </label>
                                            <input name="addmore[3][servicevalue]" value="1" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[4][servicename]" value="Product Disinfection" type="hidden" />
                                            <input name="addmore[4][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[4][servicestatus]" value="1" type="checkbox" id="address_5" class="example" /> <label for="address_5">Disinfection </label>
                                            <input name="addmore[4][servicevalue]" value="1" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2">
                                            <input name="addmore[5][servicename]" value="Package Consolidation" type="hidden" />
                                            <input name="addmore[5][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[5][servicestatus]" value="1" type="checkbox" id="address_6" class="example" /> <label for="address_6">Package Consolidation </label>
                                            <input name="addmore[5][servicevalue]" value="6" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 my-2">
                                        <div class="w-full sm:w-1/2 services_bold font-semibold">
                                            <input name="addmore[6][servicename]" value="Forwarding Service Fee" type="hidden" />
                                            <input name="addmore[6][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[6][servicestatus]" value="1" type="checkbox" id="address_7" class="example" checked readonly /> <label for="address_7">Forwarding Service Fee </label>
                                            <input name="addmore[6][servicevalue]" value="9" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        <div class="w-full sm:w-1/2 services_bold font-semibold">
                                            <input name="addmore[8][servicename]" value="shipping fee" type="hidden" />
                                            <input name="addmore[8][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[8][servicestatus]" value="1" type="checkbox" id="address_8" class="example" /> <label for="address_8">Shipping Fee </label>
                                            <input name="addmore[8][servicevalue]" value="0" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        @if($order->product_purchase == 0)
                                        @else
                                        <div class="w-full sm:w-1/2 services_bold font-semibold">
                                            <input name="addmore[7][servicename]" value="Purchase Assistance" type="hidden" />
                                            <input name="addmore[7][servicestatus]" value="0" type="hidden" />
                                            <input name="addmore[7][servicestatus]" value="1" type="checkbox" id="purchase_7" class="example" checked readonly /> <label for="purchase_7">Purchase Assistance Fee </label>
                                            <input name="addmore[7][servicevalue]" value="10" type="number" class="service_input mx-2 text-center example-input w-24 rounded-md border border-slate-300 px-2 py-1 text-sm" min="0" />
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2">
                                        <div class="w-full">
                                            <table class="w-full text-left text-sm border border-slate-200" id="dynamicTable">
                                                <tr>
                                                    <th>Service Name</th>
                                                    <th>Service Price</th>
                                                    <th>Add More</th>
                                                </tr>
                                                <tr>
                                                    <td><input type="text" name="more[0][servicename]" value="" placeholder="Service Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" /></td>
                                                    <td><input type="number" name="more[0][servicevalue]" value="" id="address" placeholder="Service Price" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full additional-services" /></td>
                                                    <td><button type="button" name="add" id="add" class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-1.5 text-white hover:bg-green-700"><i class="far fa-plus"></i></button></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    @if($order->product_purchase == 0)
                                    <div class="flex flex-wrap gap-y-3 -mx-2 mb-3">
                                        <div class="w-full">
                                            <table class="w-full text-left text-sm border border-slate-200" id="dynamicTable">
                                                <tr>
                                                    <th>Country</th>
                                                    <th>Shipping Address</th>
                                                </tr>
                                                <tr>
                                                    <td class="shipfrom_td">
                                                        <select name="shipfrom" id="shipfrom" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full country">
                                                            <!-- <option value="" disabled >Choose Employee</option> -->
                                                            <option disabled value="" selected>Select Country</option>
                                                                <option value="Afghanistan">Afghanistan</option>
                                    <option value="Åland Islands">Åland Islands</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="American Samoa">American Samoa</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Anguilla">Anguilla</option>
                                    <option value="Antarctica">Antarctica</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Aruba">Aruba</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bermuda">Bermuda</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Bouvet Island">Bouvet Island</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                    <option value="Brunei Darussalam">Brunei Darussalam</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Cape Verde">Cape Verde</option>
                                    <option value="Cayman Islands">Cayman Islands</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Christmas Island">Christmas Island</option>
                                    <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                    <option value="Cook Islands">Cook Islands</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Cote D'ivoire">Cote D'ivoire</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                    <option value="Faroe Islands">Faroe Islands</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="French Guiana">French Guiana</option>
                                    <option value="French Polynesia">French Polynesia</option>
                                    <option value="French Southern Territories">French Southern Territories</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Gibraltar">Gibraltar</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Greenland">Greenland</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guadeloupe">Guadeloupe</option>
                                    <option value="Guam">Guam</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guernsey">Guernsey</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guinea-bissau">Guinea-bissau</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                    <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Isle of Man">Isle of Man</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jersey">Jersey</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                    <option value="Korea, Republic of">Korea, Republic of</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Macao">Macao</option>
                                    <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Marshall Islands">Marshall Islands</option>
                                    <option value="Martinique">Martinique</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mayotte">Mayotte</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                                    <option value="Moldova, Republic of">Moldova, Republic of</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="Netherlands Antilles">Netherlands Antilles</option>
                                    <option value="New Caledonia">New Caledonia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Niue">Niue</option>
                                    <option value="Norfolk Island">Norfolk Island</option>
                                    <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palau">Palau</option>
                                    <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Pitcairn">Pitcairn</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Puerto Rico">Puerto Rico</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russian Federation">Russian Federation</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saint Helena">Saint Helena</option>
                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                    <option value="Saint Lucia">Saint Lucia</option>
                                    <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                    <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                    <option value="Swaziland">Swaziland</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Timor-leste">Timor-leste</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tokelau">Tokelau</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                    <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Viet Nam">Viet Nam</option>
                                    <option value="Virgin Islands, British">Virgin Islands, British</option>
                                    <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                                    <option value="Wallis and Futuna">Wallis and Futuna</option>
                                    <option value="Western Sahara">Western Sahara</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="shipingaddress" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="shipingaddress" value="{{$offer->shipingaddress}}" list="addresses" required />
                                                        @include('admin.address')
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    @else
                                    <input type="hidden" name="address_id" value="0">
                                    <input type="hidden" name="shipingaddress" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="shipingaddress" value="0" required>
                                    @endif

                                    <div class="flex flex-wrap gap-y-3 -mx-2 mb-3">
                                        <label for="net_total" class="w-full sm:w-1/6 text-sm font-medium text-slate-700 sm:pt-2">Total Amount</label>
                                        <div class="w-full sm:w-5/6">
                                            <input type="text" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="net_total" value="" name="total" required>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 mb-3">
                                        <label for="shipping_detail" class="w-full sm:w-1/6 text-sm font-medium text-slate-700 sm:pt-2">Offer Description:</label>
                                        <div class="w-full sm:w-5/6">
                                            <textarea name="shipping_detail" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="shipping_detail" required></textarea>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-y-3 -mx-2 text-right">
                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                        <div class="w-full">
                                            <button type="submit" class="offer-btn inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Update Offer</button>
                                        </div>
                                    </div>
                                </form>
                                @endif
                            </div>
                            <!-- </form> -->
                            <!-- Tab No 2 start from here -->

                            <div x-show="tab === 2" x-cloak id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                                @if(!(is_null($offer)))
                                @if (($offer->offer_status == 1))
                                @if($order->product_purchase == 0)
                                @if($order->tracking_status == 1)
                                <h2 class="text-center">Payment made by the shopper</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <h3 class="mt-3 mb-3">Tracking details(tracking link , tracking id) are added. Once we receive the product we will go to the next step</h3>
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <!-- /.card-header -->

                                            <div class="">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-sm">
                                                        <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                            <tr>
                                                                <th class="text-slate-500">Product Name</th>
                                                                <th class="text-slate-500">Product Url</th>
                                                                <th class="text-slate-500">Product Quantity</th>
                                                                <th class="text-slate-500">Product TrackingLink</th>
                                                                <th class="text-slate-500">Product TrackingId</th>
                                                                <th class="text-slate-500">Product Receipt</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                            @foreach($products as $pro)
                                                            <tr>
                                                                <td>{{$pro->productname}}</td>
                                                                <td><a href="{{$pro->producturl}}" target="_blank">Go to link</a></td>
                                                                <td>{{$pro->productquantity}}</td>
                                                                <td><a href="{{$pro->trackinglink}}" target="_blank">Go to link</a></td>
                                                                <td>{{$pro->trackingid}}</td>
                                        @if($pro->receipt == null)
                                                                <td>
                                                                    <h3>Not Added </h3>
                                                                </td>
                                                                @else
                                                                <td>
                                                                    <div class="inline-block" x-data="{ open: false }">
                                                                    <button type="button" @click="open = true" class="inline-block rounded border border-slate-200">
                                                                        <img id="imageresource" src="{{\App\Support\UploadUrl::product($pro->receipt)}}" width="60" height="60" class="h-[60px] w-[60px] rounded object-cover">
                                                                    </button>
                                                                    <div x-show="open" x-cloak @click.self="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
                                                                        <div class="relative w-full max-w-lg rounded-lg bg-white shadow-xl">
                                                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                                                <h5 class="text-sm font-semibold text-slate-800">Product Image</h5>
                                                                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600" aria-label="Close">&times;</button>
                                                                            </div>
                                                                            <div class="p-4">
                                                                                <a download="{{$pro->image}}" href="{{\App\Support\UploadUrl::product($pro->receipt)}}" title="ImageName">
                                                                                    <img id="imageresource" src="{{\App\Support\UploadUrl::product($pro->receipt)}}" class="max-w-full rounded">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                </td>
                                                                @endif
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- /.table-responsive -->
                                            </div>
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <button type="button" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Next</button>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <h2 class="text-center mb-5">Payment made by the shopper</h2>
                                <h2>Wait untill Shopper enter all tracking id's</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <div class="">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-sm">
                                                        <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                            <tr>
                                                                <th class="text-slate-500">Product Name</th>
                                                                <th class="text-slate-500">Product Url</th>
                                                                <th class="text-slate-500">Product Quantity</th>
                                                                <th class="text-slate-500">Product TrackingId</th>
                                                                <th class="text-slate-500">Product Receipt</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                            @foreach($products as $pro)
                                                            <tr>
                                                                <td>{{$pro->productname}}</td>
                                                                <td><a href="{{$pro->producturl}}" target="_blank">Go to link</a></td>
                                                                <td>{{$pro->productquantity}}</td>
                                                                @if((is_null($pro->trackingid)))
                                                                <td>
                                                                    <h3>Not Added </h3>
                                                                </td>
                                                                @else
                                                                @endif
                                                                @if($pro->receipt == null)
                                                                <td>
                                                                    <h3>Not Added </h3>
                                                                </td>
                                                                @else
                                                                <td>
                                                                    <div class="inline-block" x-data="{ open: false }">
                                                                    <button type="button" @click="open = true" class="inline-block rounded border border-slate-200">
                                                                        <img id="imageresource" src="{{\App\Support\UploadUrl::product($pro->receipt)}}" width="60" height="60" class="h-[60px] w-[60px] rounded object-cover">
                                                                    </button>
                                                                    <div x-show="open" x-cloak @click.self="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
                                                                        <div class="relative w-full max-w-lg rounded-lg bg-white shadow-xl">
                                                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                                                <h5 class="text-sm font-semibold text-slate-800">Product Image</h5>
                                                                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600" aria-label="Close">&times;</button>
                                                                            </div>
                                                                            <div class="p-4">
                                                                                <a download="{{$pro->image}}" href="{{\App\Support\UploadUrl::product($pro->receipt)}}" title="ImageName">
                                                                                    <img id="imageresource" src="{{\App\Support\UploadUrl::product($pro->receipt)}}" class="max-w-full rounded">
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                </td>
                                                                @endif
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- /.table-responsive -->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                @endif
                                @else
                                <h2 class="text-center mb-5">Payment made by the shopper</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        @if($order->tracking_status == 0)
                                        <h3 class="mt-3 mb-3">We need to add Tracking details(tracking link , tracking id).</h3>
                                        @else
                                        <h3 class="mt-3 mb-3">Tracking details(tracking link , tracking id) are added. Once we receive the product we will go to the next step</h3>
                                        @endif
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <form action="{{route('tracking_link',$order->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Quantity</th>
                                                                    <th class="text-slate-500">Tracking Link</th>
                                                                    <th class="text-slate-500">TrackingId</th>

                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($offer_products as $offer_product)
                                                                <tr>
                                                                    <td>{{$offer_product->productname}}</td>
                                                                    <td><a href="{{$offer_product->producturl}}" target="_blank">Go to link</a></td>
                                                                    <td>{{$offer_product->productquantity}}</td>
                                                                    @if(! (is_null($offer_product->trackingid)))
                                                                    <td><a href="{{$offer_product->trackinglink}}" target="_blank">Go to Tracking Link</a> </td>
                                                                    @else
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$offer_product->id}}" />
                                                                        <input type="text" name="product[{{$loop->index}}][trackinglink]" placeholder="Tracking Link" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" />
                                                                    </td>
                                                                    @endif
                                                                    @if(! (is_null($offer_product->trackingid)))
                                                                    <td>{{$offer_product->trackingid}}</td>
                                                                    @else
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$offer_product->id}}" />
                                                                        <input type="text" name="product[{{$loop->index}}][trackingid]" placeholder="Tracking Id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" />
                                                                    </td>
                                                                    @endif

                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                                @if($order->tracking_status == 1)
                                                <div class="w-full text-center mb-5">
                                                    <button type="button" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Next</button>
                                                </div>
                                                @else
                                                <div class="w-full text-center mb-5">
                                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                                    <button type="submit" class="offer-btn inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Submit</button>
                                                </div>
                                                @endif
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @elseif(($offer->offer_status == 2))
                                <h2 class="text-center my-4">Offer Rejected</h2>
                                @if(!(is_null($offer->rejections_note)))
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full sm:w-1/4">
                                        <h2>Shopper Response</h2>
                                    </div>
                                    <div class="w-full sm:w-3/4">
                                        <h3>{{$offer->rejections_note}}</h3>
                                    </div>
                                    <form action="{{route('edit_offer')}}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="flex flex-wrap gap-y-3 -mx-2">
                                            <div class="w-full text-center mt-5">
                                                <input type="hidden" name="order_id" value="{{$order->id}}">
                                                <input type="hidden" name="offer_id" value="{{$offer->id}}">
                                                <button type="submit" class="offer-btn inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Edit Offer</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @endif
                                @else
                                <h2 class="text-center">Offer Accepted</h2>
                                <h2>Wait untill Shopper process payment</h2>
                                @endif
                                @endif
                            </div>

                            <!-- Tab No 3 start from here -->

                            <div x-show="tab === 3" x-cloak id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                                @if($order->product_purchase == 0)
                                @if($order->tracking_status == 1)
                                @if($order->confirmation == 0)
                                <h2 class="text-center">Package Received</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <form action="{{route('image_upload',$product->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Product Quantity</th>
                                                                    <th class="text-slate-500">Product TrackingId</th>
                                                                    <th class="text-slate-500">Product Images</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($products as $product)
                                                                <tr>
                                                                    <td>{{$product->productname}}</td>
                                                                    <td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
                                                                    <td>{{$product->productquantity}}</td>
                                                                    <td>{{$product->trackingid}}</td>
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][image]" value="None" />
                                                                        <input type="file" name="product[{{$loop->index}}][image]" placeholder="Image" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" />
                                                                        <input type="hidden" name="active" value="3">
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                        </div>
                                        <div class="flex flex-wrap gap-y-3 -mx-2 my-4">
                                            @foreach($offer_services as $services)
                                            <div class="w-full sm:w-1/3">

                                                <input type="hidden" name="service[{{$loop->index}}][service_id]" value="{{$services->id}}" />
                                                <input name="service[{{$loop->index}}][servicestatus]" value="0" type="hidden" />
                                                <input name="service[{{$loop->index}}][servicestatus]" value="1" type="checkbox" id="service_{{$loop->index}}" /> <label for="service_{{$loop->index}}">
                                                    <h4>{{$services->servicename}}</h4>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <input type="hidden" name="confirmation" value="2">
                                            <button type="submit" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Submit</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                @elseif($order->confirmation == 2)
                                <h3>Shipping confirmation is send to the shopper </h3>
                                @else
                                @if($order->trackingid == null || $order->trackinglink == null)
                                <h2 class="text-center">Item Ready to Dispatch</h2>
                                <h3 class="mt-3 mb-3">We need to add Tracking details of Dispatch(tracking company name, tracking link , tracking id)</h3>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product Tracking details</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <form action="{{route('order_tracking',$order->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Company Name</th>
                                                                    <th class="text-slate-500">Tracking Link</th>
                                                                    <th class="text-slate-500">Tracking Id</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                <tr>
                                                                    <input type="hidden" name="confirmation" value="1">
                                                                    <input type="hidden" name="active" value="4">
                                                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    <td>
                                                                        <input type="text" name="companyname" placeholder="Company Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="companyname" value="" list="company" required />
                                                                        <datalist id="company">
                                                                            <option value="UPS">UPS</option>
                                                                            <option value="DHL">DHL</option>
                                                                            <option value="FEDX">FEDX</option>
                                                                        </datalist>
                                                                    </td>
                                                                    <td><input type="text" name="trackinglink" placeholder="Tracking Link" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                    <td><input type="text" name="trackingid" placeholder="Tracking Id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <button type="submit" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Submit</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                @else
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product Tracking details</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <form action="{{route('order_tracking',$order->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Company Name</th>
                                                                    <th class="text-slate-500">Tracking Link</th>
                                                                    <th class="text-slate-500">Tracking Id</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                <tr>
                                                                    <input type="hidden" name="confirmation" value="1">
                                                                    <input type="hidden" name="active" value="4">
                                                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    <td>
                                                                        <input type="text" name="companyname" placeholder="Company Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="companyname" value="" list="company" required />
                                                                        <datalist id="company">
                                                                            <option value="UPS">UPS</option>
                                                                            <option value="DHL">DHL</option>
                                                                            <option value="FEDX">FEDX</option>
                                                                        </datalist>
                                                                    </td>
                                                                    <td><input type="text" name="trackinglink" value="{{$order->trackinglink}}" placeholder="Tracking Link" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                    <td><input type="text" name="trackingid" value="{{$order->trackingid}}" placeholder="Tracking Id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <button type="submit" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Update</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                @endif
                                @endif
                                @else
                                @endif
                                @else
                                @if($order->tracking_status == 1)
                                <h2 class="text-center">Package Recieved</h2>
                                @if($order->confirmation == 0)
                                <h3>We have to send confirmation for shipping</h3>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <form action="{{route('purchase_image',$product->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Product Name</th>
                                                                    <th class="text-slate-500">Product Url</th>
                                                                    <th class="text-slate-500">Product Quantity</th>
                                                                    <th class="text-slate-500">Product TrackingId</th>
                                                                    <th class="text-slate-500">Product Images</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                @foreach($offer_products as $offer_product)
                                                                <tr>
                                                                    <td>{{$offer_product->productname}}</td>
                                                                    <td><a href="{{$offer_product->producturl}}" target="_blank">Go to link</a></td>
                                                                    <td>{{$offer_product->productquantity}}</td>
                                                                    <td>{{$offer_product->trackingid}}</td>
                                                                    <td>
                                                                        <input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$offer_product->id}}" />
                                                                        <input type="hidden" name="product[{{$loop->index}}][image]" value="None" />
                                                                        <input type="file" name="product[{{$loop->index}}][image]" placeholder="Image" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" />
                                                                        <input type="hidden" name="active" value="3">
                                                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                        </div>
                                        <div class="flex flex-wrap gap-y-3 -mx-2 my-4">
                                            @foreach($offer_services as $services)
                                            <div class="w-full sm:w-1/3">

                                                <input type="hidden" name="service[{{$loop->index}}][service_id]" value="{{$services->id}}" />
                                                <input type="hidden" name="service[{{$loop->index}}][servicestatus]" value="0" />
                                                <input name="service[{{$loop->index}}][servicestatus]" value="1" type="checkbox" id="service_{{$loop->index}}" /> <label for="service_{{$loop->index}}">
                                                    <h4>{{$services->servicename}}</h4>
                                                </label>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <input type="hidden" name="confirmation" value="2">
                                            <button type="submit" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Submit</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                @elseif($order->confirmation == 2)
                                <h3>Shipping confirmation is send to the shopper </h3>
                                @else
                                @if($order->trackingid == null || $order->trackinglink == null)
                                <h3>Confirmation Made by the shooper. Now we have to add Tracking details.</h3>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product Tracking details</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <form action="{{route('order_tracking',$order->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Company Name</th>
                                                                    <th class="text-slate-500">Tracking Link</th>
                                                                    <th class="text-slate-500">Tracking Id</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                <tr>
                                                                    <input type="hidden" name="confirmation" value="1">
                                                                    <input type="hidden" name="active" value="4">
                                                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    <td>
                                                                        <input type="text" name="companyname" placeholder="Company Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="companyname" value="" list="company" required />
                                                                        <datalist id="company">
                                                                            <option value="UPS">UPS</option>
                                                                            <option value="DHL">DHL</option>
                                                                            <option value="FEDX">FEDX</option>
                                                                        </datalist>
                                                                    </td>
                                                                    <td><input type="text" name="trackinglink" placeholder="Tracking Link" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                    <td><input type="text" name="trackingid" placeholder="Tracking Id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <button type="submit" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Submit</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                @else
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product Tracking details</h3>
                                            </div>
                                            <!-- /.card-header -->
                                            <form action="{{route('order_tracking',$order->id)}}" method="post" enctype="multipart/form-data">
                                                @csrf
                                                <div class="">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left text-sm">
                                                            <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                                <tr>
                                                                    <th class="text-slate-500">Company Name</th>
                                                                    <th class="text-slate-500">Tracking Link</th>
                                                                    <th class="text-slate-500">Tracking Id</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-center">
                                                                <tr>
                                                                    <input type="hidden" name="confirmation" value="1">
                                                                    <input type="hidden" name="active" value="4">
                                                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                                                    <td>
                                                                        <input type="text" name="companyname" placeholder="Company Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" id="companyname" value="" list="company" required />
                                                                        <datalist id="company">
                                                                            <option value="UPS">UPS</option>
                                                                            <option value="DHL">DHL</option>
                                                                            <option value="FEDX">FEDX</option>
                                                                        </datalist>
                                                                    </td>
                                                                    <td><input type="text" name="trackinglink" value="{{$order->trackinglink}}" placeholder="Tracking Link" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                    <td><input type="text" name="trackingid" value="{{$order->trackingid}}" placeholder="Tracking Id" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" required /></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <!-- /.table-responsive -->
                                                </div>
                                        </div>
                                        <div class="w-full text-center mb-5">
                                            <button type="submit" class="offer-Accept inline-flex items-center gap-1.5 rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-dark" @click="tab = 3">Update</button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                                @endif
                                @endif
                                @else
                                @endif
                                @endif
                                <!-- for custom decleration view -->
                                @if($order->custom_status != null)
                                <div class="mt-5">
                                    <div class="flex flex-wrap justify-center gap-y-3 -mx-2">
                                        <div class="w-full md:w-2/3">
                                            <div class="p-3 bg-white rounded">
                                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                                    <div class="w-full md:w-1/2">
                                                        <h2 class="uppercase">Custom Declaration</h2>
                                                        <div class="billed"><span class="font-semibold uppercase">Billed:</span><span class="ml-1">{{$order->name}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Date:</span><span class="ml-1">{{$order->created_at}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Order ID:</span><span class="ml-1">{{$order->order_id}}</span></div>
                                                    </div>
                                                    <div class="w-full md:w-1/2 text-right mt-3">
                                                        <h4 class="text-red-600 mb-0">Category</h4><span>{{$order->custom_category}}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div>
                                                        <table class="w-full text-left text-sm" id="custom_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Quantity</th>
                                                                    <th>Weight</th>
                                                                    <th>Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($products as $product)
                                                                <tr>
                                                                    <td>{{$product->productname}}</td>
                                                                    <td>{{$product->productquantity}}</td>
                                                                    <td>{{$product->custom_weight}}</td>
                                                                    <td>{{$product->custom_value}}</td>
                                                                </tr>
                                                                @endforeach
                                                                <tr>
                                                                    <td>
                                                                        <div class="billed"><span class="font-semibold uppercase">Total:</span></div>
                                                                    </td>
                                                                    <td>
                                                                        <h4 class="text-red-600 mb-0">{{$order->custom_total_quantity}} </h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 class="text-red-600 mb-0">{{$order->custom_total_weight}} Kg</h4>
                                                                    </td>
                                                                    <td>
                                                                        <h4 class="text-red-600 mb-0">$ {{$order->custom_total_value}} USD</h4>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <!-- <div class="text-right mb-3"><button class="btn btn-danger btn-sm mr-5" type="button">Pay Now</button></div> -->
                                            </div>
                                        </div>

                                        <div class="w-full md:w-1/3">
                                            <div class="p-3 bg-white rounded">
                                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                                    <div class="w-full">
                                                  
                                                       <h2 class="uppercase"> Shipping Address</h2>
                                                        <div class="billed"><span class="font-semibold uppercase">Name:</span><span class="ml-1">{{$order->ship_name}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Address Line 1:</span><span class="ml-1">{{$order->ship_address1}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Address line 2:</span><span class="ml-1">{{$order->ship_address2}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">City:</span><span class="ml-1">{{$order->ship_city}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">State:</span><span class="ml-1">{{$order->ship_state}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Postalcode:</span><span class="ml-1">{{$order->ship_postalcode}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Country:</span><span class="ml-1">{{$order->ship_country}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Number:</span><span class="ml-1">{{$order->ship_number}}</span></div>
                                                   <!-- disable old 
                                                        <h2 class="uppercase"> Shipping Address</h2>
                                                        <div class="billed"><span class="font-semibold uppercase">ShipTo:</span><span class="ml-1">{{$order->shipto}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Postal Code:</span><span class="ml-1">{{$order->postalcode}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Address:</span><span class="ml-1">{{$order->address}}</span></div>
                                                    --> </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="mt-5">
                                    <div class="flex flex-wrap justify-center gap-y-3 -mx-2">
                                        <div class="w-full md:w-1/2">
                                            <div class="p-3 bg-white rounded">
                                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                                    <div class="w-full">
                                                     
                                                     <!-- disabling old code  this code was disablded in old file.  
                                                        <h2 class="uppercase"> Shipping Address</h2>
                                                        <div class="billed"><span class="font-semibold uppercase">ShipTo:</span><span class="ml-1">{{$order->shipto}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Postal Code:</span><span class="ml-1">{{$order->postalcode}}</span></div>
                                                        <div class="billed"><span class="font-semibold uppercase">Address:</span><span class="ml-1">{{$order->address}}</span></div>
</-->                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <!-- code end here for custom decleration view  -->
                            </div>

                            <!-- Tab No 4 start from here -->

                            <div x-show="tab === 4" x-cloak id="pills-example" role="tabpanel" aria-labelledby="pills-about-tab">
                                <h2 class="text-center my-4">items are shipped to the customer</h2>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Product List</h3>
                                            </div>
                                            <!-- /.card-header -->

                                            <div class="">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-sm">
                                                        <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                            <tr>
                                                                <th class="text-slate-500">Product Name</th>
                                                                <th class="text-slate-500">Product Url</th>
                                                                <th class="text-slate-500">Product Quantity</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                            @foreach($products as $product)
                                                            <tr>
                                                                <td>{{$product->productname}}</td>
                                                                <td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
                                                                <td>{{$product->productquantity}}</td>

                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- /.table-responsive -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    <div class="w-full">
                                        <div class="rounded-lg border border-slate-200 bg-white shadow-sm mb-4">
                                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-800">Tracking Details</h3>
                                            </div>
                                            <!-- /.card-header -->

                                            <div class="">
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-left text-sm">
                                                        <thead class="border-b border-slate-100 text-xs uppercase text-slate-500 text-center">
                                                            <tr>
                                                                <th class="text-slate-500">Company Name</th>
                                                                <th class="text-slate-500">Tracking Link</th>
                                                                <th class="text-slate-500">Tracking Id</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                            <tr>
                                                                <td>{{$order->companyname}}</td>
                                                                <td><a href="{{$order->trackinglink}}" target="_blank">Go to link</a></td>
                                                                <td>{{$order->trackingid}}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- /.table-responsive -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-y-3 -mx-2">
                                    @if(!(is_null($order->order_status)))
                                    @if($order->order_status == 'completed')
                                    <div class="w-full text-center mt-4">
                                        <h2>Order Marked as Completed by Shipper</h2>
                                    </div>
                                    @else
                                    @endif
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fabs">
                @include('chat')
            </div>
            <div class="test">
            </div>
        </div>
    </div>
</section>
@endsection

{{-- All jQuery below is pushed to admin_scripts so it runs AFTER layouts.tailwind.app's
     jQuery <script> tag (the layout yields content, then loads jQuery, then this stack). --}}
@push('admin_scripts')
<!-- Script for calculating Total prices of product and services  -->
<script>
    // Polling intervals (milliseconds) — Admin → Settings → Preferences controls these live.
    // Setting::get reads the saved value; the config default is only a fallback.
    var notificationTimer = {{ (int) \App\Models\Setting::get('preferences_notification_timer', config('admin_settings.tabs.preferences.fields.preferences_notification_timer.default', 10000)) }};
    var messageTimer = {{ (int) \App\Models\Setting::get('preferences_message_timer', config('admin_settings.tabs.preferences.fields.preferences_message_timer.default', 5000)) }};
    var chatTimer = {{ (int) \App\Models\Setting::get('preferences_chat_timer', config('admin_settings.tabs.preferences.fields.preferences_chat_timer.default', 5000)) }};

    $(document).ready(function() {
        $(".fab").click(function() {
            var id = $('input[name="order_id"]').val();
            var user_id = $('input[name="user_id"]').val();
            $.ajax({
                url: "{{route('read_message')}}",
                type: "GET",
                data: {
                    'id': id,
                    'user_id': user_id,
                },
                success: function() {
                    // console.log('success read');
                }
            });
        });
        setInterval(fetchmessages, chatTimer);

        function fetchmessages() {
            var id = $('input[name="order_id"]').val();
            var user_id = $('input[name="user_id"]').val();
            $.ajax({
                url: "{{route('chat_messages')}}",
                type: "GET",
                data: {
                    'id': id,
                    'user_id': user_id,
                },
                success: function(chat) {
                    if (chat && chat != 'null') {
                        $('#view_messages').html(chat);
                        document.getElementById('ChatForm').reset();
                        var scroll_to = $('.chat_converse').offset().top + $('.chat_converse').height();
                        $(".chat_converse").animate({
                            scrollTop: 5000
                        }, 1000);
                        // console.log('success');
                    }
                }
            });
        }

        setInterval(fetchmessagescount, messageTimer);

        setInterval(fetchnotifications, notificationTimer);

        function fetchnotifications() {
            $.ajax({
                url: "{{ route('check_notification') }}",
                type: "GET",
                success: function(notification_count) {
                    $('.navbar-badge.badge-warning').html(notification_count);
                }
            });
        }

        function fetchmessagescount() {
            var id = $('input[name="order_id"]').val();
            var user_id = $('input[name="user_id"]').val();
            $.ajax({
                url: "{{route('chat_count')}}",
                type: "GET",
                data: {
                    'id': id,
                    'user_id': user_id,
                },
                success: function(chat_count) {
                    $('.count').html(chat_count);
                    // $('.test').html(chat_count);
                    // console.log(chat_count);

                }
            });
        }
        $('.hide').hide();
        // Chat scroll bar position after reload page
        $("#prime").click(function() {
            $(".chat_converse").animate({
                scrollTop: $('.chat_converse').get(0).scrollHeight
            }, 1000);
        });
        // Each Products Total code
        var net_total = 0;
        $('.quantity').each(function() {
            spread = parseFloat($(this).parent().next().next().find($('.spread')).val());
            if (isNaN(spread)) {
                spread = 0;
            }
            price = parseFloat($(this).parent().next().find($('.price')).val());
            quantity = parseFloat($(this).parent().find($('.quantity')).val());
            total = (price * quantity) + spread;
            $(this).parent().next().next().next().find($('.producttotal')).val(total);
        });
        // Products Total code
        $('.producttotal').each(function() {
            net_total += parseFloat($(this).val());
        });
        $('.net_total').val(net_total);
        // On price change Products Total code
        $(document).on("change keyup", ".price", function() {
            var net_total = 0;
            var grand_total = 0;
            var spread = parseFloat($(this).parent().next().find($('.spread')).val());
            var quantity = parseFloat($(this).parent().find($('.quantity')).val());
            var price = parseFloat($(this).val());
            total = (price * quantity) + spread;
            $(this).parent().next().next().find($('.producttotal')).val(total);
            $('.producttotal').each(function() {
                net_total += parseFloat($(this).val());
            });
            $('.net_total').val(net_total);

            $('.example').each(function() {
                if (this.checked) {
                    grand_total += parseFloat($(this).next().next().val());
                }
            });
            $('.additional-services').each(function() {
                if ($(this).val() != '') {
                    grand_total += parseFloat($(this).val());
                }
            });
            grand_total += net_total;
            $('#net_total').val(grand_total);

        });
        $(document).on("change keyup", ".spread", function() {
            var net_total = 0;
            var grand_total = 0;
            var price = parseFloat($(this).parent().prev().find($('.price')).val());
            var quantity = parseFloat($(this).parent().prev().find($('.quantity')).val());
            // alert(quantity);
            var spread = parseFloat($(this).val());
            // alert(spread);
            if (isNaN(spread)) {
                spread = 0;
            }
            total = (price * quantity) + spread;
            $(this).parent().next().find($('.producttotal')).val(total);
            $('.producttotal').each(function() {
                net_total += parseFloat($(this).val());
            });
            $('.net_total').val(net_total);
            $('.example').each(function() {
                if (this.checked) {
                    grand_total += parseFloat($(this).next().next().val());
                }
            });
            $('.additional-services').each(function() {
                if ($(this).val() != '') {
                    grand_total += parseFloat($(this).val());
                }
            });
            grand_total += net_total;
            $('#net_total').val(grand_total);
        });
        // Tab switching (nav-pills) and the .offer-Accept "Next" advance are now handled by
        // Alpine's `tab` state on the wizard wrapper (see the pills markup + @click="tab = N"
        // on each .offer-Accept button) instead of Bootstrap tab JS, which isn't loaded here.
        // Calculate total amount
        var total = 0;
        var net_total = 0;
        $('.example').each(function() {
            if (this.checked) {
                total += parseFloat($(this).next().next().val());
                // alert(total);
            }
        });
        if ($('#purchase_7').prop("checked")) {
            // total += parseFloat($(".net_total").val());
            $('.producttotal').each(function() {
                total += parseFloat($(this).val());
                // alert(net_total);
            });
        }
        $('#net_total').val(total);
        $(".example-input, .example").on('change', function() {
            var total = 0;
            $('.example').each(function() {
                if (this.checked) {
                    total += parseFloat($(this).next().next().val());
                }
            });
            if ($('#purchase_7').prop("checked")) {
                // total += parseFloat($(".net_total").val());
                $('.producttotal').each(function() {
                    total += parseFloat($(this).val());
                    // alert(net_total);
                });
            }
            $('.additional-services').each(function() {
                if ($(this).val() != '') {
                    total += parseFloat($(this).val());
                }
            });
            $('#net_total').val(total);
        });
        $(document).on("change keyup", ".additional-services", function() {
            var total = 0;
            $('.example').each(function() {
                if (this.checked) {
                    total += parseFloat($(this).next().next().val());
                }
            });
            if ($('#purchase_7').prop("checked")) {
                // total += parseFloat($(".net_total").val());
                $('.producttotal').each(function() {
                    total += parseFloat($(this).val());
                    // alert(net_total);
                });
            }
            $('.additional-services').each(function() {
                if ($(this).val() != '') {
                    total += parseFloat($(this).val());
                }
            });
            $('#net_total').val(total);
        });
        // remove service with calculating total
        $(document).on('click', '.remove-tr', function() {
            $(this).parents('tr').remove();
            var total = 0;
            $('.example').each(function() {
                if (this.checked) {
                    total += parseFloat($(this).next().next().val());
                }
            });
            if ($('#purchase_7').prop("checked")) {
                // total += parseFloat($(".net_total").val());
                $('.producttotal').each(function() {
                    total += parseFloat($(this).val());
                    // alert(net_total);
                });
            }
            $('.additional-services').each(function() {
                if ($(this).val() != '') {
                    total += parseFloat($(this).val());
                }
            });
            $('#net_total').val(total);
        });
    }); //document ready ending
</script>
<!-- scropt for Shipping Addresses -->
<script type="text/javascript">
    $('.country').on('change', function(event) {
        event.preventDefault();
        let data = $('.country').val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('countryaddress')}}",
            type: "POST",
            data: {
                "_token": "{{ csrf_token() }}",
                country: data,
            },
            success: function(chat) {
                // alert('hello');
                // $('#asd').html(chat);
                $('.showid').html(chat);
                console.log('success');
            },
        });
    });
</script>
<!-- Script for Chat process  -->
<script type="text/javascript">
    $('#ChatForm').on('submit', function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('chat')}}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(chat) {
                $('#view_messages').html(chat);
                document.getElementById('ChatForm').reset();
                var scroll_to = $('.chat_converse').offset().top + $('.chat_converse').height();
                $(".chat_converse").animate({
                    scrollTop: 5000
                }, 1000);
                console.log('success');
            },
        });
    });
</script>
<!-- Script for adding new services row in table -->
<script type="text/javascript">
    var i = 0;
    $("#add").click(function() {
        ++i;
        $("#dynamicTable").append('<tr><td><input type="text" name="more[' + i + '][servicename]" placeholder="Service Name" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full" /></td><td><input type="number" name="more[' + i + '][servicevalue]" placeholder="Service Price" min="0" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm w-full additional-services" /></td><td><button type="button" class="remove-tr inline-flex items-center justify-center rounded-md bg-red-600 px-2 py-1.5 text-white hover:bg-red-700"><i class="far fa-minus"></button></td></tr>');
    });
</script>
<!-- Script for chat system -->
<script type="text/javascript">
    hideChat(0);
    $('#prime').click(function() {
        toggleFab();
    });
    //Toggle chat and links
    function toggleFab() {
        $('.prime').toggleClass('zmdi-comment-outline');
        $('.prime').toggleClass('zmdi-close');
        $('.prime').toggleClass('is-active');
        $('.prime').toggleClass('is-visible');
        $('#prime').toggleClass('is-float');
        $('.chat').toggleClass('is-visible');
        $('.chat').toggleClass('visible');
        $('.fab').toggleClass('is-visible');
    }
    $('#chat_first_screen').click(function(e) {
        hideChat(1);
    });

    function hideChat(hide) {
        switch (hide) {
            case 0:
                $('#chat_converse').css('display', 'none');
                $('#chat_body').css('display', 'none');
                $('#chat_form').css('display', 'none');
                $('.chat_login').css('display', 'block');
                $('.chat_fullscreen_loader').css('display', 'none');
                $('#chat_fullscreen').css('display', 'none');
                break;
            case 1:
                $('#chat_converse').css('display', 'block');
                $('#chat_body').css('display', 'none');
                $('#chat_form').css('display', 'none');
                $('.chat_login').css('display', 'none');
                $('.chat_fullscreen_loader').css('display', 'block');
                break;
            case 2:
                $('#chat_converse').css('display', 'none');
                $('#chat_body').css('display', 'block');
                $('#chat_form').css('display', 'none');
                $('.chat_login').css('display', 'none');
                $('.chat_fullscreen_loader').css('display', 'block');
                break;
            case 3:
                $('#chat_converse').css('display', 'none');
                $('#chat_body').css('display', 'none');
                $('#chat_form').css('display', 'block');
                $('.chat_login').css('display', 'none');
                $('.chat_fullscreen_loader').css('display', 'block');
                break;
            case 4:
                $('#chat_converse').css('display', 'none');
                $('#chat_body').css('display', 'none');
                $('#chat_form').css('display', 'none');
                $('.chat_login').css('display', 'none');
                $('.chat_fullscreen_loader').css('display', 'block');
                $('#chat_fullscreen').css('display', 'block');
                break;
        }
    }
    
</script>
<script>
    $(function() {
        $("#custom_table").DataTable({
            "responsive": true,
            "autoWidth": false,
            "info": false,
            "ordering": false,
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": true,
            "bInfo": false,
            "dom": 'rtip'
        });
    });
</script>
@endpush
<!-- main content ends here -->