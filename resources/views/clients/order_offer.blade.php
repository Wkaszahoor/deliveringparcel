<!-- main layout start from here -->
@extends('layouts.client_dashbord_master')
<!-- main layout ens here -->
<!-- title for header start from here -->
@section('head')
<title>Orders | Deliveringparcel</title>
@endsection
<!-- title ends here -->
<!-- main content start from here -->
@section('content')
<section class="content">
	<div class="container">
		<div class="row">
			<h5> Order #{{$order->order_id}}</h5>
			@include('admin.partials.order-attachments', ['order' => $order, 'attachmentsRole' => 'client'])
			<div class="col-md-12 col-sm-12 board">
				@include('clients.flash-message')
				<div class="">
					<div class="border-transparent">
						<input type="hidden" name="active" value="{{$order->active_tab}}" class="active_tab">
						<input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
						<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
							<div class="liner"></div>
							<li class="nav-item text-center">
								<a class="nav-link circle" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">
									1
								</a>
								<h3>Accept an Offer</h3>
							</li>
							<li class="nav-item">
								<a class="nav-link circle left " id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">2</a>
								<h3 class='text-left'>Offer Response</h3>
							</li>
							<li class="nav-item">
								<a class="nav-link circle left " id="pills-contact-tab" data-toggle="pill" href="#pills-contact" role="tab" aria-controls="pills-contact" aria-selected="false">3</a>
								<h3 class='text-left'>Ready to Ship</h3>
							</li>
							<li class="nav-item">
								<a class="nav-link circle left " id="pills-about-tab" data-toggle="pill" href="#pills-example" role="tab" aria-controls="pills-contact" aria-selected="false">4</a>
								<h3 class='text-left'>Order Status</h3>
							</li>
						</ul>
					</div>
					<!-- /.card-header -->
					<div class="card-body p-0 order-process">
						<div class="tab-content" id="pills-tabContent ">
							<div class="tab-pane fade show" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
								<h2 class="text-center">Order Details</h2>
								<div class="row my-3">
									<div class="col-sm-3">
										<h4> Ship From</h4>
									</div>
									<div class="col-sm-3">
										<h5>{{$order->shipfrom}}</h5>
									</div>

									<div class="col-sm-3">
										<h4> Ship To</h4>
									</div>
									<div class="col-sm-3">
										<h5>{{$order->shipto}}</h5>
									</div>
								</div>
								<div class="row my-3">
									<div class="col-sm-3">
										<h4>Postal Codes</h4>
									</div>
									<div class="col-sm-3">
										<h5>{{$order->postalcode}}</h5>
									</div>
								</div>
								<div class="row my-3">
									<div class="col-sm-3">
										<h4>Address</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$order->address}}</h5>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Product List</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
												</div>
											</div>
											<!-- /.card-header -->
											@if($order->product_purchase == 0)
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head text-center">
															<tr>
																<th class="text-color">Product Name</th>
																<th class="text-color">Product Url</th>
																<th class="text-color">Product Quantity</th>
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
											@else
											@if (! (is_null($offer)))
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head">
															<tr>
																<th class="text-color">Product Name</th>
																<th class="text-color">Product Url</th>
																<th class="text-color">Quantity</th>
																<th class="text-color">Price/Unit in USD$</th>
																<th class="text-color">Spread</th>
																<th class="text-color">Total Price in USD$</th>
															</tr>
														</thead>
														<tbody class="">
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
															<td class="">
																<h3>Total</h3>
															</td>
															<td class="">
																<h2>{{$offer->product_total}}</h2>
															</td>
														</tr>
													</table>
												</div>
												<!-- /.table-responsive -->
											</div>
											@else
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head">
															<tr>
																<th class="text-color">Product Name</th>
																<th class="text-color">Product Url</th>
																<th class="text-color">Quantity</th>
																<th class="text-color">Price/Unit in USD$</th>
																<th class="text-color">Total Price in USD$</th>
															</tr>
														</thead>
														<tbody class="">
															@foreach($products as $product)
															<tr>
																<td>{{$product->productname}}</td>
																<td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
																<td>{{$product->productquantity}}</td>
																<td>{{$product->productprice}}</td>
																<td>{{$product->product_total}}</td>
															</tr>
															@endforeach
														</tbody>
													</table>
												</div>
												<!-- /.table-responsive -->
											</div>
											@endif
											@endif
										</div>
									</div>
								</div>
								<hr>
								@if (! (is_null($offer)))
								<h2 class="text-center my-4">Offer Detail</h2>
								<div class="row">
									<div class="col-sm-3">
										<h3 class="my-">Offer Description:</h3>
									</div>
									<div class="col-sm-9">
										<h5>{{$offer->description}}</h5>
									</div>
								</div>
								<h3 class="text-center my-4">Additional Services</h3>
								<div class="row my-4">
									@foreach($offer_services as $services)
									<div class="col-sm-4">
										<h4>{{$services->servicename}}</h4>
									</div>
									<div class="col-sm-2">
										<h5>$ {{$services->servicevalue}}</h5>
									</div>
									@endforeach
								</div>
								<div class="row my-4 total">
									<div class="col-sm-4 ">
										<h4>Total</h4>
									</div>
									<div class="col-sm-4">
										<h4>$ {{$offer->total}}</h4>
									</div>
								</div>
								@if($offer->offer_status == 0)
								@include('partials.order-callouts', ['statusKey' => 'offer_pending'])
								<div class="row buttons">
									<div class="col-md-3 col-sm-2 offset-md-3 mb-4">
										<form action="{{route('offer_accept',$offer->id)}}" method="post" enctype="multipart/form-data">
											@csrf
											<input type="hidden" name="offer_id" value="{{$offer->id}}">
											<!-- <input type="hidden" name="offer_status" value="1"> -->
											<input type="hidden" name="active" value="2">
											<input type="hidden" name="order_id" value="{{$order->id}}">
											<button type="submit" class="btn offer-Accept">Accept</button>
										</form>
									</div>
									<div class="col-md-3 col-sm-2">
										<form action="{{route('offer_reject',$offer->id)}}" method="post" enctype="multipart/form-data">
											@csrf
											<input type="hidden" name="offer_id" value="{{$offer->id}}">
											<input type="hidden" name="offer_status" value="2">
											<input type="hidden" name="active" value="2">
											<input type="hidden" name="order_id" value="{{$order->id}}">
											<button type="submit" class="btn offer-Reject">Reject</button>
										</form>
									</div>
								</div>
								@else
								@endif
								@else
								<h2 class="text-center my-4">Order Details Submitted...</h2>
								@endif
							</div>

							<!-- Tab No 2 start frpom here -->

							<div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
								@if(!(is_null($offer)))
								@if (($offer->offer_status == 1))
								@if($order->product_purchase == 0)
								<h2 class="text-center my-4">Offer Accepted</h2>
									<div class="row my-4">
									@if(is_null($offer->shippingaddress_id))
									<div class="col-sm-3">
										<h4>Shipping Address</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$offer->shipingaddress}}</h5>
									</div>
									@else
									@endif
									@foreach($shipping_address as $address)
									<div class="col-sm-3">
										<h4>Name</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->name}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>Address 1</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->address1}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>Address 2</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->address2}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>City</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->city}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>State/Province</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->state}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>Postal Code</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->postalcode}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>Country</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->country}}</h5>
									</div>
									<div class="col-sm-3">
										<h4>Number</h4>
									</div>
									<div class="col-sm-9">
										<h5>{{$address->number}}</h5>
									</div>
									@endforeach
								</div>
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Product List</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
												</div>
											</div>
											<form action="{{route('tracking_id',$order->id)}}" method="post" enctype="multipart/form-data">
												@csrf
												<div class="card-body p-0">

													<div class="table-responsive">
														<table class="table">
															<thead class="table_head text-center">
																<tr>
																	<th class="text-color">Product Name</th>
																	<th class="text-color">Product Url</th>
																	<th class="text-color">Product Quantity</th>
																	<th class="text-color">Tracking Link</th>
																	<th class="text-color">TrackingId</th>
																	<th class="text-color">Receipt Image</th>

																</tr>
															</thead>
															<tbody class="text-center">
																@foreach($products as $product)
																<tr>
																	<td>{{$product->productname}}</td>
																	<td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
																	<td>{{$product->productquantity}}</td>
																	@if(! (is_null($product->trackinglink)))
																	<td><a href="{{$product->trackinglink}}" target="_blank">Go to link</a></td>
																	@else
																	<td>
																		<input type="hidden" name="order_id" value="{{$order->id}}" />
																		<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																		<input type="text" name="product[{{$loop->index}}][trackinglink]" placeholder="Tracking Link" class="form-control" required />
																	</td>
																	@endif
																	@if(! (is_null($product->trackingid)))
																	<td>{{$product->trackingid}}</td>
																	@else
																	<td>
																		<input type="hidden" name="order_id" value="{{$order->id}}" />
																		<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																		<input type="text" name="product[{{$loop->index}}][trackingid]" placeholder="Tracking Id" class="form-control" required />
																	</td>
																	@endif
																		@if(! (is_null($product->receipt)))
																		<td>
																			<button type="button" class="btn" data-toggle="modal" data-target="#exampleModalCenter{{$product->id}}">
																				<img id="imageresource" src="{{\App\Support\UploadUrl::product($product->receipt)}}" width="60" height="60">
																			</button>
																			<!-- Modal -->
																			<div class="modal fade product_img" id="exampleModalCenter{{$product->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
																				<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
																					<div class="modal-content">
																						<div class="modal-header">
																							<h5 class="modal-title" id="exampleModalLongTitle">Product Image</h5>
																							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																								<span aria-hidden="true">&times;</span>
																							</button>
																						</div>
																						<div class="modal-body">
																							<a download="{{$product->image}}" href="{{\App\Support\UploadUrl::product($product->receipt)}}" title="ImageName">
																								<img id="imageresource" src="{{\App\Support\UploadUrl::product($product->receipt)}}">
																							</a>
																							<!-- <img id="imageresource" src="{{\App\Support\UploadUrl::product($product->image)}}"> -->
																						</div>
																					</div>
																				</div>
																			</div>
																		</td>
																		@else
																			@if($order->tracking_status == 1)
																				<td>
																					<h3>Not Added</h3>	
																				</td>
																				@else
																				<td>
																					<input type="hidden" name="order_id" value="{{$order->id}}" />
															<!-- /.				//			<input type="hidden" name="product[{{$loop->index}}][receipt]" value="None" />
																			-->	--	<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																					<input type="file" name="product[{{$loop->index}}][receipt]" class="form-control" />
																				</td>
																			@endif
																	@endif
																</tr>
																@endforeach
															</tbody>
														</table>
													</div>
													<!-- /.table-responsive -->
												</div>

												@if($order->active_tab == 2)
												@if($order->tracking_status == 1)
												@else
												<div class="col-sm-12 text-center mb-5">
													<button type="submit" class="btn offer-Accept">Submit</button>
												</div>
												@endif
												@else
												@endif
											</form>
										</div>
										@if($order->tracking_status == 0)
										<h3>You have accepted the offer and the order is started.</h3>
										@include('partials.order-callouts', ['statusKey' => 'forwarding_no_tracking'])
											@else
											@include('partials.order-callouts', ['statusKey' => 'forwarding_tracking_added'])
											@endif
										</div>
									</div>
									@else
									<div class="row">
										<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Product List</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
												</div>
											</div>
											<form action="{{route('tracking_id',$order->id)}}" method="post" enctype="multipart/form-data">
												@csrf
												<div class="card-body p-0">
													<div class="table-responsive">
														<table class="table">
															<thead class="table_head text-center">
																<tr>
																	<th class="text-color">Product Name</th>
																	<th class="text-color">Tracking Link</th>
																	<th class="text-color">Tracking Id</th>
																</tr>
															</thead>
															<tbody class="text-center">
																@foreach($offer_products as $offer_product)
																<tr>
																	<td>{{$offer_product->productname}}</td>
																	@if(!(is_null($offer_product->trackinglink)))
																	<td><a href="{{$offer_product->trackinglink}}" target="_blank">Go to Tracking Link</a> </td>
																	@else
																	@endif
																	<td>{{$offer_product->trackingid}}</td>
																</tr>
																@endforeach
															</tbody>
														</table>
													</div>
													<!-- /.table-responsive -->
												</div>
											</form>
										</div>
									</div>
								</div>
								@if($order->tracking_status == 0)
									<h3>You have accepted the offer and the order is started. Wait untill we add tracking details('tracking link , tracking id'). so you can follow up.</h3>
									@include('partials.order-callouts', ['statusKey' => 'purchase_no_tracking'])
									@else
									<h3>Tracking details('tracking link , tracking id') are added. Once we Recieve your products we will send you a confirmation.</h3>
									@endif
								<!-- <h2>Wait Untill we Recieve your products .....</h2> -->
								@endif
								@elseif(($offer->offer_status == 2))
								<h2 class="text-center my-4">Offer Rejected</h2>
								@if(!(is_null($offer->rejections_note)))
								<div class="row">
									<div class="col-sm-3">
										<h2>Your Response</h2>
									</div>
									<div class="col-sm-9">
										<h3>{{$offer->rejections_note}}</h3>
									</div>
								</div>
								@else
								<form action="{{route('rejected',$offer->id)}}" method="post" enctype="multipart/form-data">
									@csrf
									<div class="form-group row">
										<label for="shipingaddress" class="col-sm-2 col-form-label">Reason</label>
										<div class="col-sm-10">
											<!-- <input type="text" name="shipingaddress" class="form-control" id="shipingaddress" value="" required> -->
											<input type="text" name="reason" class="form-control" value="" required list="reason" />
											<datalist id="reason">
												<option value="PRICE'S ARE TOO HIGH">PRICE'S ARE TOO HIGH</option>
												<option value="I HAVE FOUND ANOTHER WAY FOE FORWARDING MY ITEM'S.">I HAVE FOUND ANOTHER WAY FOE FORWARDING MY ITEM'S.</option>
												<option value="NEED TO EDIT MY REQUEST.">NEED TO EDIT MY REQUEST.</option>
												<option value="I NEED MORE INFORMATION BEFORE ACCEPTING.">I NEED MORE INFORMATION BEFORE ACCEPTING.</option>
											</datalist>
										</div>
									</div>
									<div class="col-sm-12 text-center mb-5">
										<input type="hidden" name="offer_id" value="{{$offer->id}}">
										<button type="submit" class="btn offer-Accept">Submit</button>
									</div>
								</form>
								@endif
									@else
									<!-- payment section -->
									@php
										// PM-015: payment_mode (legacy|advanced) is the single switch;
										// falls back to payments_engine_enabled when unset.
										$paymentsEngineEnabled = \App\Services\Payments\PaymentService::engineEnabled();
									@endphp

									@if($paymentsEngineEnabled)
										<!-- NEW PAYMENT ENGINE PANEL -->
										<section class="container">
											<div class="col-xl-12 col-lg-12 col-md-12">
												<div class="card">
													<div class="card-header">
														<h4>Payment — Order #{{$order->order_id}}</h4>
													</div>
													<div class="card-body">
														<div id="payment-engine-loading" class="text-center py-4">
															<i class="fas fa-spinner fa-spin"></i> Loading payment options...
														</div>
														<div id="payment-engine-content" style="display:none">
															<div class="alert alert-info mb-3">
																<strong>Amount due:</strong> ${{number_format((float)$offer->total, 2)}} USD
															</div>

											<!-- Payment methods -->
											<div id="payment-methods-container"></div>

											<!-- SECURITY: card details are NEVER collected on the order page.
											     Stripe card payments continue on the dedicated secure
											     checkout page (own URL, Stripe Elements only there). -->

											<!-- Bank transfer instructions -->
															<div id="bank-instructions-container" style="display:none;margin-top:1.5rem">
																<h5>Bank transfer instructions</h5>
																<div id="bank-account-selector" style="display:none;margin-bottom:.75rem"></div>
																<div id="bank-instructions-table"></div>
																<form id="bank-proof-form" style="margin-top:1rem" enctype="multipart/form-data">
																	@csrf
																	<label for="proof-file">Upload payment proof (jpg/png/pdf, max 5MB)</label>
																	<input id="proof-file" type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
																	<button type="submit" class="btn btn-success" style="margin-top:1rem">Submit proof for verification</button>
																</form>
															</div>

															<!-- Payment status -->
															<div id="payment-status-container" style="display:none;margin-top:1.5rem"></div>
														</div>
													</div>
												</div>
											</div>
										</section>

										<!-- Payment Engine JavaScript -->
				<!-- Payment Engine JavaScript -->
<script>
(function() {
    var orderId = {{ $order->id }};
    var offerAmount = {{ $offer->total }};
    var methodsUrl = '{{ route("home2.pay.methods", $order->id) }}';
    var payUrl = '{{ route("home2.pay.pay", $order->id) }}';
    var statusUrlTemplate = '{{ route("home2.pay.status", "__PAYMENT_ID__") }}';
    var checkoutUrl = '{{ route("home2.pay.show", $order->id) }}';

    var selectedMethod = null;
    var currentPaymentId = null;
    var initiatePayBtn = null;

    var csrfTokenEl = document.querySelector('meta[name="csrf-token"]')
        || document.querySelector('meta[name="csrf_token"]');
    var csrfToken = csrfTokenEl ? csrfTokenEl.content : '';

    // ✅ FIX 1: Load payment methods — with AJAX header
    fetch(methodsUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(data) {
        if (!data.ok) {
            throw new Error(data.message || 'Failed to load payment methods');
        }

        if (data.methods && data.methods.find(function(m) { return m.code === 'stripe'; })) {
            // Stripe will be initialized when needed
        }

        renderPaymentMethods(data.methods, data.payment);
        document.getElementById('payment-engine-loading').style.display = 'none';
        document.getElementById('payment-engine-content').style.display = 'block';

        if (data.payment) {
            showPaymentStatus(data.payment);
        }
    })
    .catch(function(error) {
        console.error('Payment methods load error:', error);
        document.getElementById('payment-engine-loading').innerHTML =
            '<div class="alert alert-danger">Failed to load payment options. Please refresh the page.</div>';
    });

    function renderPaymentMethods(methods, payment) {
        var container = document.getElementById('payment-methods-container');
        if (!methods || methods.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">No payment methods available.</div>';
            return;
        }

        var html = '<h5 class="mb-3">Choose payment method</h5>';
        methods.forEach(function(method, index) {
            var disabled = method.disabled ? 'disabled' : '';
            var checked = ((!payment && index === 0) || method.selected) ? 'checked' : '';
            var reasonHtml = method.reason ? '<br><small class="text-muted">' + method.reason + '</small>' : '';
            var hint = method.code === 'stripe'
                ? '<br><small class="text-muted"><i class="fas fa-lock"></i> Card details are entered on our secure checkout page</small>'
                : '';

            html += '<label style="display:flex;align-items:center;gap:.75rem;padding:.75rem;border:1px solid #ddd;border-radius:8px;margin-bottom:.5rem;cursor:pointer' + (method.disabled ? ';opacity:.55;cursor:not-allowed' : '') + '">';
            html += '<input type="radio" name="payment-method" value="' + method.code + '" ' + checked + ' ' + disabled + ' data-method-code="' + method.code + '">';
            html += '<span><strong>' + method.name + '</strong>' + reasonHtml + hint + '</span>';
            html += '</label>';
        });

        container.innerHTML = html;

        container.querySelectorAll('input[name="payment-method"]').forEach(function(radio) {
            radio.addEventListener('change', handleMethodChange);
        });

        if (payment && payment.method_locked) {
            container.querySelectorAll('input[name="payment-method"]').forEach(function(r) {
                r.disabled = true;
            });
        }

        handleMethodChange();

        if (initiatePayBtn) {
            container.appendChild(initiatePayBtn);
        }
    }

    function handleMethodChange() {
        var selected = document.querySelector('input[name="payment-method"]:checked');
        if (!selected) return;

        selectedMethod = selected.dataset.methodCode;
        var bankContainer = document.getElementById('bank-instructions-container');

        if (selectedMethod === 'stripe') {
            bankContainer.style.display = 'none';
            if (initiatePayBtn) {
                initiatePayBtn.innerHTML = '<i class="fas fa-lock mr-1"></i> Continue to secure card payment';
            }
        } else if (selectedMethod === 'bank_transfer') {
            bankContainer.style.display = 'block';
            loadBankInstructions();
            if (initiatePayBtn) {
                initiatePayBtn.innerHTML = 'Pay $' + offerAmount;
            }
        } else {
            bankContainer.style.display = 'none';
            if (initiatePayBtn) {
                initiatePayBtn.innerHTML = 'Pay $' + offerAmount;
            }
        }
    }

    var bankAccounts = [];
    var selectedBankAccountId = null;

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text == null ? '' : text;
        return div.innerHTML;
    }

    function renderBankAccount(account) {
        var bankContainer = document.getElementById('bank-instructions-table');
        var html = '<p style="color:#dc3545;font-weight:600;margin:0 0 .5rem"><i class="fas fa-exclamation-circle"></i> Click on Pay button to initiate payment through bank account and submit Record.</p>';
        html += '<table class="table table-sm table-bordered">';
        html += '<tr><td><strong>Recipient</strong></td><td>' + escapeHtml(account.account_title) + '</td></tr>';
        if (account.recipient_address) {
            html += '<tr><td><strong>Recipient Address</strong></td><td>' + escapeHtml(account.recipient_address) + '</td></tr>';
        }
        html += '<tr><td><strong>Bank Name</strong></td><td>' + escapeHtml(account.bank_name) + '</td></tr>';
        if (account.account_number) {
            html += '<tr><td><strong>Account Number</strong></td><td>' + escapeHtml(account.account_number) + '</td></tr>';
        }
        if (account.iban) {
            html += '<tr><td><strong>IBAN</strong></td><td>' + escapeHtml(account.iban) + '</td></tr>';
        }
        if (account.branch) {
            html += '<tr><td><strong>Branch</strong></td><td>' + escapeHtml(account.branch) + '</td></tr>';
        }
        if (account.swift_code) {
            html += '<tr><td><strong>SWIFT/BIC Code</strong></td><td>' + escapeHtml(account.swift_code) + '</td></tr>';
        }
        if (account.intermediary_bic) {
            html += '<tr><td><strong>Intermediary BIC</strong></td><td>' + escapeHtml(account.intermediary_bic) + '</td></tr>';
        }
        if (account.uk_account_number) {
            html += '<tr><td><strong>UK Account Number</strong></td><td>' + escapeHtml(account.uk_account_number) + '</td></tr>';
        }
        if (account.uk_sort_code) {
            html += '<tr><td><strong>UK Sort Code</strong></td><td>' + escapeHtml(account.uk_sort_code) + '</td></tr>';
        }
        html += '<tr><td><strong>Amount Due</strong></td><td>$' + offerAmount + ' ' + escapeHtml(account.currency || 'USD') + '</td></tr>';
        html += '<tr><td><strong>Transfer Reference</strong></td><td><span class="text-primary">Will be provided after payment initiation</span></td></tr>';
        html += '</table>';

        if (account.instructions) {
            html += '<div class="mt-2 alert alert-info py-2"><small><strong>Additional Instructions:</strong><br>' + escapeHtml(account.instructions) + '</small></div>';
        }

        bankContainer.innerHTML = html;
        selectedBankAccountId = account.id || null;
    }

    function loadBankInstructions() {
        var bankContainer = document.getElementById('bank-instructions-table');
        var selectorWrap = document.getElementById('bank-account-selector');
        bankContainer.innerHTML = '<div class="text-center py-2"><i class="fas fa-spinner fa-spin"></i> Loading bank details...</div>';

        // ✅ FIX 2: Bank account details — with AJAX header
        fetch('/home2/api/bank-account-details', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            if (!data.ok || (!data.account && (!data.accounts || !data.accounts.length))) {
                bankContainer.innerHTML = '<div class="text-muted">No bank account configured. Please contact support.</div>';
                return;
            }

            bankAccounts = (data.accounts && data.accounts.length) ? data.accounts : [data.account];

            if (selectorWrap) {
                if (bankAccounts.length > 1) {
                    var selHtml = '<label for="bank-account-select"><strong>Select Bank Account:</strong></label> ';
                    selHtml += '<select id="bank-account-select" class="form-control form-control-sm" style="max-width:320px;display:inline-block;margin-left:.5rem">';
                    bankAccounts.forEach(function(acc, i) {
                        selHtml += '<option value="' + i + '">' + escapeHtml(acc.bank_name + ' - ' + acc.account_title) + '</option>';
                    });
                    selHtml += '</select>';
                    selectorWrap.innerHTML = selHtml;
                    selectorWrap.style.display = 'block';
                    document.getElementById('bank-account-select').addEventListener('change', function() {
                        renderBankAccount(bankAccounts[parseInt(this.value, 10)]);
                    });
                } else {
                    selectorWrap.innerHTML = '';
                    selectorWrap.style.display = 'none';
                }
            }

            renderBankAccount(bankAccounts[0]);

            var proofForm = document.getElementById('bank-proof-form');
            var proofFile = document.getElementById('proof-file');
            var proofBtn = proofForm.querySelector('button[type="submit"]');

            proofFile.disabled = true;
            proofBtn.disabled = true;
            proofBtn.innerHTML = 'Initiate payment first to upload proof';
            proofForm.querySelector('label').innerHTML = 'Upload payment proof (jpg/png/pdf, max 5MB) <small> - Click "Pay" button first to enable upload</small>';
        })
        .catch(function(error) {
            console.error('Failed to load bank details:', error);
            bankContainer.innerHTML = '<div class="text-muted">Failed to load bank details. Please refresh the page.</div>';
        });
    }

    // Handle payment initiation
    var initiatePayBtn = document.createElement('button');
    initiatePayBtn.className = 'btn btn-primary btn-lg btn-block mt-3';
    initiatePayBtn.innerHTML = 'Pay $' + offerAmount;
    initiatePayBtn.onclick = function(e) {
        e.preventDefault();

        if (!selectedMethod) {
            alert('Please select a payment method');
            return;
        }

        initiatePayBtn.disabled = true;
        initiatePayBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('method', selectedMethod);
        if (selectedMethod === 'bank_transfer' && selectedBankAccountId) {
            formData.append('bank_account_id', selectedBankAccountId);
        }
        formData.append('origin', 'legacy');

        // ✅ FIX 3: Pay initiation — already had header (kept as-is)
        fetch(payUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.ok) {
                throw new Error(data.message || 'Payment initiation failed');
            }

            currentPaymentId = data.payment_id || null;

            if (selectedMethod === 'stripe' && data.payload && data.payload.checkout_url) {
                window.location.href = data.payload.checkout_url;
                return;
            }

            if (data.payload && data.payload.simulated) {
                showPaymentStatus({ status: 'processing', reference: data.reference });
                return;
            } else if (selectedMethod === 'bank_transfer') {
                if (data.payload && data.payload.instructions) {
                    showBankInstructions(data.payload.instructions);
                }

                var proofForm = document.getElementById('bank-proof-form');
                var proofFile = document.getElementById('proof-file');
                var proofBtn = proofForm.querySelector('button[type="submit"]');
                var proofLabel = proofForm.querySelector('label');

                proofFile.disabled = false;
                proofBtn.disabled = false;
                proofBtn.innerHTML = 'Submit proof for verification';
                proofLabel.innerHTML = 'Upload payment proof (jpg/png/pdf, max 5MB) <strong>- Payment initiated! Reference: ' + data.reference + '</strong>';

                initiatePayBtn.innerHTML = '<i class="fas fa-check"></i> Payment Initiated - Upload Proof Below';
                initiatePayBtn.classList.remove('btn-primary');
                initiatePayBtn.classList.add('btn-success');

                showPaymentStatus({ status: 'awaiting_payment', reference: data.reference });
                return;
            }

            showPaymentStatus({ status: data.status, reference: data.reference });
        })
        .catch(function(error) {
            console.error('Payment error:', error);
            alert('Payment failed: ' + error.message);
            initiatePayBtn.disabled = false;
            initiatePayBtn.innerHTML = 'Pay $' + offerAmount;
        });
    };

    document.getElementById('payment-methods-container').appendChild(initiatePayBtn);

    function showBankInstructions(instructions) {
        var container = document.getElementById('bank-instructions-table');
        if (!instructions) {
            container.innerHTML = '<div class="text-muted">No bank instructions available. Please contact support.</div>';
            return;
        }

        var html = '<table class="table table-sm table-bordered">';
        if (instructions.account_title) {
            html += '<tr><td><strong>Recipient</strong></td><td>' + escapeHtml(instructions.account_title) + '</td></tr>';
        }
        if (instructions.recipient_address) {
            html += '<tr><td><strong>Recipient Address</strong></td><td>' + escapeHtml(instructions.recipient_address) + '</td></tr>';
        }
        if (instructions.bank_name) {
            html += '<tr><td><strong>Bank Name</strong></td><td>' + escapeHtml(instructions.bank_name) + '</td></tr>';
        }
        if (instructions.account_number) {
            html += '<tr><td><strong>Account Number</strong></td><td>' + escapeHtml(instructions.account_number) + '</td></tr>';
        }
        if (instructions.iban) {
            html += '<tr><td><strong>IBAN</strong></td><td>' + escapeHtml(instructions.iban) + '</td></tr>';
        }
        if (instructions.branch) {
            html += '<tr><td><strong>Branch</strong></td><td>' + escapeHtml(instructions.branch) + '</td></tr>';
        }
        if (instructions.swift_code) {
            html += '<tr><td><strong>SWIFT/BIC Code</strong></td><td>' + escapeHtml(instructions.swift_code) + '</td></tr>';
        }
        if (instructions.intermediary_bic) {
            html += '<tr><td><strong>Intermediary BIC</strong></td><td>' + escapeHtml(instructions.intermediary_bic) + '</td></tr>';
        }
        if (instructions.uk_account_number) {
            html += '<tr><td><strong>UK Account Number</strong></td><td>' + escapeHtml(instructions.uk_account_number) + '</td></tr>';
        }
        if (instructions.uk_sort_code) {
            html += '<tr><td><strong>UK Sort Code</strong></td><td>' + escapeHtml(instructions.uk_sort_code) + '</td></tr>';
        }
        if (instructions.amount) {
            html += '<tr><td><strong>Amount</strong></td><td>' + escapeHtml(instructions.currency) + ' ' + instructions.amount + '</td></tr>';
        }
        if (instructions.transfer_reference) {
            html += '<tr><td><strong>Transfer Reference</strong></td><td class="text-primary"><strong>' + escapeHtml(instructions.transfer_reference) + '</strong></td></tr>';
        }
        html += '</table>';

        if (instructions.additional_instructions) {
            html += '<div class="mt-2 alert alert-info py-2"><small><strong>Additional Instructions:</strong><br>' + escapeHtml(instructions.additional_instructions) + '</small></div>';
        }

        container.innerHTML = html;
    }

    function showPaymentStatus(payment) {
        var container = document.getElementById('payment-status-container');
        if (!payment) return;

        if (payment.status === 'awaiting_verification') {
            container.innerHTML = '<div class="alert alert-warning">' +
                '<h5><i class="fas fa-hourglass-half mr-2"></i>Waiting for bank payment verification</h5>' +
                '<p class="mb-1">We have received your transfer receipt and our team is verifying it with the bank.</p>' +
                '<p class="mb-0 text-muted">Once verified, your payment is confirmed and this order automatically moves to the next step. You will get a notification.</p>' +
                (payment.reference ? '<small class="d-block mt-2">Reference: ' + payment.reference + '</small>' : '') +
                '</div>';
            var methodsBox = document.getElementById('payment-methods-container');
            if (methodsBox) methodsBox.style.display = 'none';
            container.style.display = 'block';
            return;
        }

        if (payment.status === 'paid') {
            container.innerHTML = '<div class="alert alert-success">' +
                '<h5><i class="fas fa-check-circle mr-2"></i>Payment verified</h5>' +
                '<p class="mb-1">Your payment has been confirmed — thank you. This order is moving forward.</p>' +
                (payment.reference ? '<small class="d-block mt-2">Reference: ' + payment.reference + '</small>' : '') +
                '</div>';
            var methodsBox = document.getElementById('payment-methods-container');
            if (methodsBox) methodsBox.style.display = 'none';
            container.style.display = 'block';
            return;
        }

        var statusLabels = {
            'processing': 'Processing card payment…',
            'awaiting_payment': 'Awaiting payment',
            'failed': 'Payment failed',
            'cancelled': 'Payment cancelled'
        };

        var statusClass = payment.status === 'failed' ? 'danger' : 'info';

        container.innerHTML = '<div class="alert alert-' + statusClass + '">' +
            '<strong>Status:</strong> ' + (statusLabels[payment.status] || payment.status) +
            (payment.reference ? '<br><small>Reference: ' + payment.reference + '</small>' : '') +
            '</div>';

        container.style.display = 'block';
    }

    // ✅ FIX 4: Proof upload — with AJAX header AND comma after body
    document.getElementById('bank-proof-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentPaymentId) {
            alert('Please click the "Pay" button first to initiate payment before uploading proof.');
            return;
        }

        var formData = new FormData(this);
        formData.append('_token', csrfToken);

        var proofUrl = statusUrlTemplate.replace('__PAYMENT_ID__', currentPaymentId).replace('/status', '/proof');
        var submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        fetch(proofUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                alert('Proof uploaded successfully. Your payment is now awaiting verification.');
                showPaymentStatus({ status: 'awaiting_verification', reference: 'Awaiting verification' });
                submitBtn.innerHTML = 'Proof Uploaded';
            } else {
                alert('Upload failed: ' + (data.message || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Submit proof for verification';
            }
        })
        .catch(function(err) {
            console.error('Proof upload error:', err);
            alert('Upload failed. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit proof for verification';
        });
    });
})();
</script>
									@else
										<!-- LEGACY STRIPE FORM (unchanged) -->
										<section class="container">
											<div class="col-xl-12 col-lg-12 col-md-12">
												<div class="card">
													<div class="tab-pane active" id="address" role="tabpanel">
														<div class="row m-4 text-center">
															<div class="container icons text-center">
																<h2>Payment Process</h2>
																<img src="{{asset('images/Visa-icon (1).png')}}" alt="visa icon">
																<img src="{{asset('images/Master-Card-2-icon.png')}}" alt="master icon">
																<img src="{{asset('images/Maestro-icon.png')}}" alt="maestro icon">
															</div>
															<div class="container icons text-center">
																<h3 class="margin-bottom-md text-success">
																	<span class="fs1 icon" aria-hidden="true" data-icon=""></span>
																	Secure credit card payment<br>
																	<small>This is a secure 128-bit SSL encrypted payment</small><img src="{{asset('images/powered-by-stripe.png')}}" alt="" width="200px" height="70px">
																</h3>
															</div>
														</div>
														<div class="card-body">
															<p class="text-center mb-3">Card details are collected on Stripe's secure hosted payment page — nothing card-related is entered on this site.</p>
																<div id="dp-legacy-pay-status" class="alert" style="display:none"></div>
																<div class="row">
																	<div class="col-xs-12">
																		<button class="btn btn-primary btn-lg btn-block" type="button" id="dp-legacy-pay-btn">Proceed to Payment (${{$offer->total}})</button>
																	</div>
																</div>
																<script>
																(function() {
																	var btn = document.getElementById('dp-legacy-pay-btn');
																	if (!btn) return;
																	var status = document.getElementById('dp-legacy-pay-status');
																	btn.addEventListener('click', function() {
																		btn.disabled = true;
																		btn.innerHTML = 'Processing… do not click again';
																		var fd = new FormData();
																		fd.append('_token', '{{ csrf_token() }}');
																		fd.append('method', 'stripe');
																		fd.append('origin', 'legacy');
																		fetch("{{ route('home2.pay.pay', $order->id) }}", {
																			method: 'POST',
																			body: fd,
																			headers: { 'X-Requested-With': 'XMLHttpRequest' }
																		})
																		.then(function(r) { return r.json(); })
																		.then(function(data) {
																			if (data.ok && data.payload && data.payload.checkout_url) {
																				window.location.href = data.payload.checkout_url;
																				return;
																			}
																			throw new Error(data.message || 'Payment could not be started. Please try again or contact support.');
																		})
																		.catch(function(err) {
																			status.style.display = 'block';
																			status.className = 'alert alert-danger';
																			status.textContent = err.message;
																			btn.disabled = false;
																			btn.innerHTML = 'Proceed to Payment (${{$offer->total}})';
																		});
																	});
																})();
																</script>
														</div>
													</div>
												</div>
											</div>
										</section>
									@endif
									@endif
								@endif
							</div>
							<!-- Tab No 3 start from here -->
							<div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
								<h2 class="text-center my-4">Ready to Ship</h2>
								<div class="row">
									@if($order->product_purchase == 0)
									<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Product List</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
												</div>
											</div>
											<!-- /.card-header -->
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head text-center">
															<tr>
																<th class="text-color">Product Name</th>
																<th class="text-color">Product Url</th>
																<th class="text-color">Product Quantity</th>
																<th class="text-color">Product TrackingId</th>
																<th></th>
															</tr>
														</thead>
														<tbody class="text-center">
															@foreach($products as $product)
															<tr>
																<td>{{$product->productname}}</td>
																<td><a href="{{$product->producturl}}" target="_blank">Go to link</a></td>
																<td>{{$product->productquantity}}</td>
																<td>{{$product->trackingid}}</td>
																@if(is_null($product->image))
																<td></td>
																@else
																<td>
																	<button type="button" class="btn" data-toggle="modal" data-target="#product_image{{$product->id}}">
																		<img id="imageresource" src="{{\App\Support\UploadUrl::product($product->image)}}" width="60" height="60">
																	</button>
																	<!-- Modal -->
																	<div class="modal fade product_img" id="product_image{{$product->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
																		<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
																			<div class="modal-content">
																				<div class="modal-header">
																					<h5 class="modal-title" id="exampleModalLongTitle">Product Image</h5>
																					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																						<span aria-hidden="true">&times;</span>
																					</button>
																				</div>
																				<div class="modal-body">
																					<a download="{{$product->image}}" href="{{\App\Support\UploadUrl::product($product->image)}}" title="ImageName">
																						<img id="imageresource" src="{{\App\Support\UploadUrl::product($product->image)}}">
																					</a>
																					<!-- <img id="imageresource" src="{{\App\Support\UploadUrl::product($product->image)}}"> -->
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
										<h3>Forwarding Services</h3>
										<div class="row my-4 board">
											@foreach($offer_services as $services)
											@if($services->confirmation == 1)
											<div class="col-sm-4">
												<input name="" value="{{$services->confirmation}}" type="checkbox" id="service_{{$loop->index}}" readonly {{($services->confirmation == 1 ? ' checked' : '') }} /> <label for="service_{{$loop->index}}">
													<h4>{{$services->servicename}}</h4>
												</label>
											</div>
											@else
											@endif
											@endforeach
										</div>
										@if($order->confirmation == 2)
										<div class="row my-4">
											<div class="col-sm-3">
												<h2>Ready to Ship :</h2>
											</div>
											<div class="col-sm-9">
												<h3>Need your Confirmation for Shipping</h3>
											</div>
										</div>
										@include('partials.order-callouts', ['statusKey' => 'ready_to_ship'])
										<!-- Customs Declaration form and order confirmation code -->
									Test
										<form action="{{route('Order_confirmation',$order->id)}}" method="post" enctype="multipart/form-data">
											@csrf
											<div class="row">
												<div class="col-md-12">
													<!-- general form elements -->
													<div class="card">
														<div class="card-header">
															<h3 class="card-title">Shipping Address</h3>
														</div>
														<!-- /.card-header -->
														<!-- form start -->
														<div class="card-body">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="name">Name</label>
																		<input type="text" name="name" class="form-control" id="name" placeholder="Name" required />
																	</div>

																	<div class="form-group">
																		<label for="address1">Address 1</label>
																		<input type="text" name="address1" class="form-control" id="address1" placeholder="Address line 1" required />
																	</div>
																	<div class="form-group">
																		<label for="address2">Address 2</label>
																		<input type="text" name="address2" class="form-control" id="address2" placeholder="Address line 2" />
																	</div>
																	<div class="form-group">
																		<label for="city">City</label>
																		<input type="text" name="city" class="form-control" id="city" placeholder="city" required />
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="state">State/Province</label>
																		<input type="text" name="state" class="form-control" id="state" placeholder="State/Province" required />
																	</div>
																	<div class="form-group">
																		<label for="postalcode">Postal Code</label>
																		<input type="text" name="postalcode" class="form-control" id="postalcode" placeholder="Postal Code" required />
																	</div>
																	<div class="form-group">
																		<label for="country">Country</label>
																		<!-- <input type="text" name="country" class="form-control" id="country" placeholder="country" required /> -->
																		<select name="country" class="form-control" id="country" placeholder="country" required>
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
																	</div>
																	<div class="form-group">
																		<label for="number">Phone Number</label>
																		<input type="text" name="number" class="form-control" id="number" placeholder="number" required />
																	</div>
																</div>
															</div>
														</div>
														<!-- /.card-body -->
													</div>
													<!-- /.card -->
												</div>
												<div class="col-sm-12">
													<div class="card">
														<div class="card-header border-transparent">
															<h3 class="card-title" data-card-widget="collapse">Customs Declaration</h3>
															<div class="card-tools">
																<button type="button" class="btn btn-tool" data-card-widget="collapse">
																	<i class="fas fa-minus"></i>
																</button>
															</div>
														</div>
														<!-- /.card-header -->
														<div class="card-body p-0">
															<div class="table-responsive">
																<div class="col-sm-12 p-4">
																	<h3>Items Category:</h3>
																	<div class="form-check form-check-inline ">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox1" value="Documents" required>
																		<label class="form-check-label" for="inlineCheckbox1">Documents</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox2" value="Commercial Samples">
																		<label class="form-check-label" for="inlineCheckbox2">Commercial Samples</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox3" value="Gifts">
																		<label class="form-check-label" for="inlineCheckbox3">Gifts</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox4" value="Returned Items">
																		<label class="form-check-label" for="inlineCheckbox4">Returned Items</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox5" value="Other">
																		<label class="form-check-label" for="inlineCheckbox5">Other</label>
																	</div>
																</div>
																<table class="table">
																	<thead class="table_head text-center">
																		<tr>
																			<th class="text-color">Product Name</th>
																			<th class="text-color">Product Quantity</th>
																			<th class="text-color">Weight ,kg</th>
																			<th class="text-color">Custom value , USD</th>
																		</tr>
																	</thead>
																	<tbody class="text-center">
																		@foreach($products as $product)
																		<tr>
																			<td>{{$product->productname}}</td>
																			<td class="custom_quantity">{{$product->productquantity}}</td>
																			<td>
																				<input type="hidden" name="order_id" value="{{$order->id}}" />
																				<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																				<input type="text" name="product[{{$loop->index}}][weight]" placeholder="Weight kg" class="form-control custom_weight" required />
																			</td>
																			<td>
																				<input type="hidden" name="order_id" value="{{$order->id}}" />
																				<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																				<input type="text" name="product[{{$loop->index}}][value]" placeholder="Custom value" class="form-control custom_value" required />
																			</td>
																		</tr>
																		@endforeach
																		<tr>
																			<td>
																				<h3>Total:</h3>
																			</td>
																			<td>
																				<h3 class="total_quantity"></h3>
																				<input type="hidden" name="custom_total_quantity" value="">
																			</td>
																			<td>
																				<h3 class="total_weight"></h3>
																				<input type="hidden" name="custom_total_weight" value="">
																			</td>
																			<td>
																				<h3 class="total_value"></h3>
																				<input type="hidden" name="custom_total_value" value="">
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
															<!-- /.table-responsive -->
														</div>
													</div>
												</div>
												<div class="col-sm-12 text-right">
													<input type="hidden" name="offer_status" value="1">
													<input type="hidden" name="confirmation" value="1">
													<input type="hidden" name="active" value="4">
													<input type="hidden" name="order_id" value="{{$order->id}}">
													<input type="hidden" name="custom_status" value="1">
													<button type="submit" class="btn offer-Accept">Send Confirmation</button>
												</div>
											</div>
										</form>
										<!-- custom declaration code end here -->
										@else
										<div class="row">
											<div class="col-sm-12 text-center">
												<h2>we will ship items to your destination and add tracking details so you can easily follow up.</h2>
											</div>
										</div>
										@endif
									</div>
									@else
									<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Product List</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
												</div>
											</div>
											<!-- /.card-header -->
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head text-center">
															<tr>
																<th class="text-color">Product Name</th>
																<th class="text-color">Product Url</th>
																<th class="text-color">Product Quantity</th>

																<th></th>
															</tr>
														</thead>
														<tbody class="text-center">
															@foreach($offer_products as $offer_product)
															<tr>
																<td>{{$offer_product->productname}}</td>
																<td><a href="{{$offer_product->producturl}}" target="_blank">Go to link</a></td>
																<td>{{$offer_product->productquantity}}</td>

																@if(is_null($offer_product->image))
																<td></td>
																@else
																<td>
																	<button type="button" class="btn" data-toggle="modal" data-target="#purchase_image{{$offer_product->id}}">
																		<img id="imageresource" src="{{\App\Support\UploadUrl::product($offer_product->image)}}" width="60" height="60">
																	</button>
																	<!-- Modal -->
																	<div class="modal fade" id="purchase_image{{$offer_product->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
																		<div class="modal-dialog modal-dialog-centered" role="document">
																			<div class="modal-content">
																				<div class="modal-header">
																					<h5 class="modal-title" id="exampleModalLongTitle">Product Image</h5>
																					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																						<span aria-hidden="true">&times;</span>
																					</button>
																				</div>
																				<div class="modal-body">
																					<img id="imageresource" src="{{\App\Support\UploadUrl::product($offer_product->image)}}" width="350" height="350">
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
										<h3>Forwarding Services</h3>
										<div class="row my-4 board">
											@foreach($offer_services as $services)
											@if($services->confirmation == 1)
											<div class="col-sm-4">
												<input name="" value="{{$services->confirmation}}" type="checkbox" id="service_{{$loop->index}}" readonly {{($services->confirmation == 1 ? ' checked' : '') }} /> <label for="service_{{$loop->index}}">
													<h4>{{$services->servicename}}</h4>
												</label>
											</div>
											@else
											@endif
											@endforeach
										</div>
										@if($order->confirmation == 2)
										<div class="row my-4">
											<div class="col-sm-3">
												<h2>Ready to Ship :</h2>
											</div>
											<div class="col-sm-9">
												<h3>Need your Confirmation for Shipping</h3>
											</div>
										</div>
										@include('partials.order-callouts', ['statusKey' => 'ready_to_ship'])
										<!-- Customs Declaration form and order confirmation code -->
										<form action="{{route('Order_confirmation',$order->id)}}" method="post" enctype="multipart/form-data">
											@csrf
											@if(!is_null($offer))
											<input type="hidden" name="offer_id" value="{{$offer->id}}">
											@else
											@endif
											<div class="row">
												<div class="col-md-12">
													<!-- general form elements -->
													<div class="card">
														<div class="card-header">
															<h3 class="card-title">Shipping Address</h3>
														</div>
														<!-- /.card-header -->
														<!-- form start -->
														<div class="card-body">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="name">Name</label>
																		<input type="text" name="name" class="form-control" id="name" placeholder="Name" required />
																	</div>

																	<div class="form-group">
																		<label for="address1">Address 1</label>
																		<input type="text" name="address1" class="form-control" id="address1" placeholder="Address line 1" required />
																	</div>
																	<div class="form-group">
																		<label for="address2">Address 2</label>
																		<input type="text" name="address2" class="form-control" id="address2" placeholder="Address line 2" />
																	</div>
																	<div class="form-group">
																		<label for="city">City</label>
																		<input type="text" name="city" class="form-control" id="city" placeholder="city" required />
																	</div>
																</div>
																<div class="col-md-6">
																	<div class="form-group">
																		<label for="state">State/Province</label>
																		<input type="text" name="state" class="form-control" id="state" placeholder="State/Province" required />
																	</div>
																	<div class="form-group">
																		<label for="postalcode">Postal Code</label>
																		<input type="text" name="postalcode" class="form-control" id="postalcode" placeholder="Postal Code" required />
																	</div>
																	<div class="form-group">
																		<label for="country">Country</label>
																		<!-- <input type="text" name="country" class="form-control" id="country" placeholder="country" required /> -->
																		<select name="country" class="form-control" id="country" placeholder="country" required>
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
																	</div>
																	<div class="form-group">
																		<label for="number">Phone Number</label>
																		<input type="text" name="number" class="form-control" id="number" placeholder="number" required />
																	</div>
																</div>
															</div>
														</div>
														<!-- /.card-body -->
													</div>
													<!-- /.card -->
												</div>
												<div class="col-sm-12">
													<div class="card">
														<div class="card-header border-transparent">
															<h3 class="card-title" data-card-widget="collapse">Customs Declaration</h3>
															<div class="card-tools">
																<button type="button" class="btn btn-tool" data-card-widget="collapse">
																	<i class="fas fa-minus"></i>
																</button>
															</div>
														</div>
														<!-- /.card-header -->
														<div class="card-body p-0">
															<div class="table-responsive">
																<div class="col-sm-12 p-4">
																	<h3>Items Category:</h3>
																	<div class="form-check form-check-inline ">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox1" value="Documents" required>
																		<label class="form-check-label" for="inlineCheckbox1">Documents</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox2" value="Commercial Samples">
																		<label class="form-check-label" for="inlineCheckbox2">Commercial Samples</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox3" value="Gifts">
																		<label class="form-check-label" for="inlineCheckbox3">Gifts</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox4" value="Returned Items">
																		<label class="form-check-label" for="inlineCheckbox4">Returned Items</label>
																	</div>
																	<div class="form-check form-check-inline">
																		<input class="form-check-input" type="radio" name="category" id="inlineCheckbox5" value="Other">
																		<label class="form-check-label" for="inlineCheckbox5">Other</label>
																	</div>
																</div>
																<table class="table">
																	<thead class="table_head text-center">
																		<tr>
																			<th class="text-color">Product Name</th>
																			<th class="text-color">Product Quantity</th>
																			<th class="text-color">Weight ,kg</th>
																			<th class="text-color">Custom value , USD</th>
																		</tr>
																	</thead>
																	<tbody class="text-center">
																		@foreach($products as $product)
																		<tr>
																			<td>{{$product->productname}}</td>
																			<td class="custom_quantity">{{$product->productquantity}}</td>
																			<td>
																				<input type="hidden" name="order_id" value="{{$order->id}}" />
																				<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																				<input type="text" name="product[{{$loop->index}}][weight]" placeholder="Weight kg" class="form-control custom_weight" required />
																			</td>
																			<td>
																				<input type="hidden" name="order_id" value="{{$order->id}}" />
																				<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
																				<input type="text" name="product[{{$loop->index}}][value]" placeholder="Custom value" class="form-control custom_value" required />
																			</td>
																		</tr>
																		@endforeach
																		<tr>
																			<td>
																				<h3>Total:</h3>
																			</td>
																			<td>
																				<h3 class="total_quantity"></h3>
																				<input type="hidden" name="custom_total_quantity" value="">
																			</td>
																			<td>
																				<h3 class="total_weight"></h3>
																				<input type="hidden" name="custom_total_weight" value="">
																			</td>
																			<td>
																				<h3 class="total_value"></h3>
																				<input type="hidden" name="custom_total_value" value="">
																			</td>
																		</tr>
																	</tbody>

																</table>
															</div>
															<!-- /.table-responsive -->
														</div>
													</div>
												</div>
												<div class="col-sm-12 text-right">
													<input type="hidden" name="offer_status" value="1">
													<input type="hidden" name="confirmation" value="1">
													<input type="hidden" name="active" value="4">
													<input type="hidden" name="order_id" value="{{$order->id}}">
													<input type="hidden" name="custom_status" value="1">
													<button type="submit" class="btn offer-Accept">Send Confirmation</button>
												</div>
											</div>
										</form>
										<!-- custom declaration code end here -->
										<!-- <div class="row">
											<div class="col-sm-12 text-right">
												<form action="{{route('Order_confirmation',$order->id)}}" method="post" enctype="multipart/form-data">
													@csrf
													@if(!is_null($offer))
													<input type="hidden" name="offer_id" value="{{$offer->id}}">
													@else
													@endif
													<input type="hidden" name="offer_status" value="1">
													<input type="hidden" name="confirmation" value="1">
													<input type="hidden" name="active" value="4">
													<input type="hidden" name="order_id" value="{{$order->id}}">
													<button type="submit" class="btn offer-Accept">Send Confirmation</button>
												</form>
											</div>
										</div> -->
										@else
										<div class="row">
											<div class="col-sm-12 text-center">
												<h2>we will ship items to your destination and add tracking details so you can easily follow up.</h2>
											</div>
										</div>
										@endif
									</div>
									@endif
								</div>
								<!-- for custom decleration view -->
								@if($order->custom_status != null)
								<div class="container mt-5">
									<div class="d-flex justify-content-center row">
										<div class="col-md-8">
											<div class="p-3 bg-white rounded">
												<div class="row">
													<div class="col-md-6">
														<h2 class="text-uppercase">Custom Declaration</h2>
														<div class="billed"><span class="font-weight-bold text-uppercase">Billed:</span><span class="ml-1">{{$order->name}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Date:</span><span class="ml-1">{{$order->created_at}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Order ID:</span><span class="ml-1">{{$order->order_id}}</span></div>
													</div>
													<div class="col-md-6 text-right mt-3">
														<h4 class="text-danger mb-0">Category</h4><span>{{$order->custom_category}}</span>
													</div>
												</div>
												<div class="mt-3">
													<div>
														<table class="table" id="custom_table">
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
																		<div class="billed"><span class="font-weight-bold text-uppercase">Total:</span></div>
																	</td>
																	<td>
																		<h4 class="text-danger mb-0">{{$order->custom_total_quantity}} </h4>
																	</td>
																	<td>
																		<h4 class="text-danger mb-0">{{$order->custom_total_weight}} Kg</h4>
																	</td>
																	<td>
																		<h4 class="text-danger mb-0">$ {{$order->custom_total_value}} USD</h4>
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
												<!-- <div class="text-right mb-3"><button class="btn btn-danger btn-sm mr-5" type="button">Pay Now</button></div> -->
											</div>
										</div>
										<div class="col-md-4">
											<div class="p-3 bg-white rounded">
												<div class="row">
													<div class="col-md-12">
														<h2 class="text-uppercase"> Shipping Address</h2>
														<div class="billed"><span class="font-weight-bold text-uppercase">Name:</span><span class="ml-1">{{$order->ship_name}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Address Line 1:</span><span class="ml-1">{{$order->ship_address1}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Address line 2:</span><span class="ml-1">{{$order->ship_address2}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">City:</span><span class="ml-1">{{$order->ship_city}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">State:</span><span class="ml-1">{{$order->ship_state}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Postalcode:</span><span class="ml-1">{{$order->ship_postalcode}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Country:</span><span class="ml-1">{{$order->ship_country}}</span></div>
														<div class="billed"><span class="font-weight-bold text-uppercase">Number:</span><span class="ml-1">{{$order->ship_number}}</span></div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								@else
								@endif
								<!-- code end here for custom decleration view  -->
							</div>

							<!-- Tab No 4 start from here -->

							<div class="tab-pane fade" id="pills-example" role="tabpanel" aria-labelledby="pills-about-tab">
								<h2 class="text-center my-4">items are shipped</h2>
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Product List</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
												</div>
											</div>
											<!-- /.card-header -->
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head text-center">
															<tr>
																<th class="text-color">Name</th>
																<th class="text-color">Url</th>
																<th class="text-color">Quantity</th>
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
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-header border-transparent">
												<h3 class="card-title" data-card-widget="collapse">Tracking Details</h3>
												<div class="card-tools">
													<button type="button" class="btn btn-tool" data-card-widget="collapse">
														<i class="fas fa-minus"></i>
													</button>
													<!-- <button type="button" class="btn btn-tool" data-card-widget="remove">
                                                    <i class="fas fa-times"></i>
                                                    </button> -->
												</div>
											</div>
											<!-- /.card-header -->
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table">
														<thead class="table_head text-center">
															<tr>
																<th class="text-color">Company Name</th>
																<th class="text-color">Tracking Link</th>
																<th class="text-color">Tracking Id</th>
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
										<div class="row">
											@if(!is_null($order->order_status))
											@if($order->order_status != 'completed' && $order->order_status != 'received')
											<div class="col-sm-12 text-right">
												<form action="{{route('Order_complete',$order->id)}}" method="post" enctype="multipart/form-data">
													@csrf
													<input type="hidden" name="offer_status" value="1">
													<input type="hidden" name="confirmation" value="1">
													<input type="hidden" name="active" value="4">
													<input type="hidden" name="order_status" value="completed">
													<input type="hidden" name="order_id" value="{{$order->id}}">
													<button type="submit" class="btn offer-Accept">Recieved</button>
												</form>
											</div>
											@elseif($order->order_status == 'completed')
											<div class="col-sm-12 text-center mt-4">
												<h2>Your package has been delivered</h2>
												<p class="text-muted mb-4">Please confirm it arrived so we can close this order.</p>

												<form action="{{route('order_received',$order->id)}}" method="post" style="margin-bottom:1rem">
													@csrf
													<input type="hidden" name="order_status" value="received">
													<button type="submit" class="btn btn-success btn-lg">
														<i class="fas fa-check-circle mr-1"></i> I have received this package
													</button>
												</form>

												<!-- TrustBox widget - Review Collector -->
												<div class="trustpilot-widget" data-locale="en-GB" data-template-id="56278e9abfbbba0bdcd568bc" data-businessunit-id="5f5ff8b322ca0500015cc549" data-style-height="320px" data-style-width="100%">
													<a href="https://uk.trustpilot.com/review/deliveringparcel.com" target="_blank" rel="noopener">Trustpilot</a>
												</div>
												<p class="mt-3">
													<a href="{{ route('home2.reviews.create') }}" class="btn btn-outline-primary btn-sm mr-2"><i class="fas fa-star mr-1"></i> Write a review here</a>
													<a href="https://uk.trustpilot.com/review/deliveringparcel.com" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="fas fa-external-link-alt mr-1"></i> Review us on Trustpilot</a>
												</p>
											</div>
											@else
											<div class="col-sm-12 text-center mt-4">
												<h2><i class="fas fa-check-circle text-success"></i> Package Received — Thank you!</h2>
												<p class="text-muted mb-3">This order is complete. We'd love to hear how we did:</p>
												<!-- TrustBox widget - Review Collector -->
												<div class="trustpilot-widget" data-locale="en-GB" data-template-id="56278e9abfbbba0bdcd568bc" data-businessunit-id="5f5ff8b322ca0500015cc549" data-style-height="320px" data-style-width="100%">
													<a href="https://uk.trustpilot.com/review/deliveringparcel.com" target="_blank" rel="noopener">Trustpilot</a>
												</div>
												<p class="mt-3">
													<a href="{{ route('home2.reviews.create') }}" class="btn btn-outline-primary btn-sm mr-2"><i class="fas fa-star mr-1"></i> Write a review here</a>
													<a href="https://uk.trustpilot.com/review/deliveringparcel.com" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm"><i class="fas fa-external-link-alt mr-1"></i> Review us on Trustpilot</a>
												</p>
											</div>
											@endif
											@endif
										</div>
									</div>
								</div>
								@include('partials.order-callouts', ['statusKey' => 'shipped'])
							</div>
						</div>
					</div>
				</div>
				<div class="fabs">
					@include('chat')
				</div>
			</div>
		</div>
	</div>
</section>
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
	$(function() {
		var $form = $(".require-validation");
		$('form.require-validation').bind('submit', function(e) {
			var $form = $(".require-validation"),
				inputSelector = ['input[type=email]', 'input[type=password]',
					'input[type=text]', 'input[type=file]',
					'textarea'
				].join(', '),
				$inputs = $form.find('.required').find(inputSelector),
				$errorMessage = $form.find('div.error'),
				valid = true;
			$errorMessage.addClass('hide');
			$('.has-error').removeClass('has-error');
			$inputs.each(function(i, el) {
				var $input = $(el);
				if ($input.val() === '') {
					$input.parent().addClass('has-error');
					$errorMessage.removeClass('hide');
					e.preventDefault();
				}
			});
			if (!$form.data('cc-on-file')) {
				e.preventDefault();
				Stripe.setPublishableKey($form.data('stripe-publishable-key'));
				Stripe.createToken({
					number: $('.card-number').val(),
					cvc: $('.card-cvc').val(),
					exp_month: $('.card-expiry-month').val(),
					exp_year: $('.card-expiry-year').val()
				}, stripeResponseHandler);
			}
		});

		function stripeResponseHandler(status, response) {
			if (response.error) {
				$('.error')
					.removeClass('hide')
					.find('.alert')
					.text(response.error.message);
			} else {
				/* token contains id, last4, and card type */
				var token = response['id'];
				$form.find('input[type=text]').empty();
				$form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
				$form.get(0).submit();
			}
		}
	});
</script>

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
				$('#Message').val('');
				$('#file-input').val();
				$(".chat_converse").animate({
					scrollTop: 5000
				}, 1000);
				console.log('success');
			},
		});
	});
</script>
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
					console.log('success read');
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
						console.log('success');
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
				// 	 $('.test').html(chat_count);
					console.log(chat_count);

				}
			});
		}
		$("#prime").click(function() {
			$(".chat_converse").animate({
				scrollTop: $('.chat_converse').get(0).scrollHeight
			}, 1000);
		});
		// load active tab
		var active = $('.active_tab').val();
		if (active == 1) {
			$('#pills-home-tab').addClass("active");
			$('#pills-home').addClass("active");
			$('#pills-profile-tab').addClass("disabled");
			$('#pills-contact-tab').addClass("disabled");
			$('#pills-about-tab').addClass("disabled");
		} else if (active == 2) {
			$('#pills-profile-tab').addClass("active");
			$('#pills-profile').addClass('active show');
			$('#pills-contact-tab').addClass("disabled");
			$('#pills-about-tab').addClass("disabled");
		} else if (active == 3) {
			$('#pills-contact').addClass('active show');
			$('#pills-contact-tab').addClass("active");
			$('#pills-about-tab').addClass("disabled");
		} else if (active == 4) {
			$('#pills-example').addClass('active show');
			$('#pills-about-tab').addClass("active");
		}
		// Calculate total amount
		
		// calculate total quantity for custom Declaration
		t_quantity = 0;
		$(".custom_quantity").each(function() {
			t_quantity += parseInt($(this).html());
		});
		$('.total_quantity').html(t_quantity);
		$('input[name="custom_total_quantity"]').val(t_quantity);
		// console.log(t_quantity);
	}) //document ready ending
</script>
<script type="text/javascript">
	// Start with the conversation visible — the old login screen (hideChat(0))
	// toggled elements that don't exist in the markup, so history never showed.
	hideChat(1);
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
	$(document).on("change keyup", ".custom_weight", function() {

		var weight_total = 0;
		$('.custom_weight').each(function() {
			weight_total += parseFloat($(this).val());
		});
		// alert(weight_total);
		$('.total_weight').html(weight_total + "Kg");
		$('input[name="custom_total_weight"]').val(weight_total);
	});
	$(document).on("change keyup", ".custom_value", function() {

		var value_total = 0;
		$('.custom_value').each(function() {
			value_total += parseFloat($(this).val());
		});
		// alert(weight_total);
		$('.total_value').html("$" + value_total + "USD");
		$('input[name="custom_total_value"]').val(value_total);
	});
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
{{-- Trustpilot TrustBox loader — without this the widget divs render as a bare link --}}
<script type="text/javascript" src="//widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>
@endsection
<!-- mian content ends here -->