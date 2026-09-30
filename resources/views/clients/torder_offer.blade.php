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
										<h4>Postal Code</h4>
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
								<div class="col-md-12 mt-4">
									<div class="card card-default">
										<div class="card-header">
											<h3 class="card-title">
												<i class="fas fa-bullhorn"></i>
												Callouts
											</h3>
										</div>
										<!-- /.card-header -->
										<div class="card-body">
											<div class="callout callout-danger">
												<h5>Hi there.</h5>
												<p>we will be happy to help you get your package. Sending you Our offer including all cost. If it sounds good, please click ‘Accept offer’.</p>
												<p>Looking forward to shipping your order!</p>
											</div>
											<div class="callout callout-info">
												<p>Otherwise you can decline the offer and get back to us if you think service fee and shipping fees are bit high for your budget , we will certainly help you try to accommodate you and give you the best price according to your budget </p>
											</div>
										</div>
										<!-- /.card-body -->
									</div>
									<!-- /.card -->
								</div>
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
											<form action="{{route('tracking_id',$product->id)}}" method="post" enctype="multipart/form-data">
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
																				<img id="imageresource" src="{{asset('uploads/productsimages/'.$product->receipt)}}" width="60" height="60">
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
																							<a download="{{$product->image}}" href="{{asset('uploads/productsimages/'.$product->receipt)}}" title="ImageName">
																								<img id="imageresource" src="{{asset('uploads/productsimages/'.$product->receipt)}}">
																							</a>
																							<!-- <img id="imageresource" src="{{asset('uploads/productsimages/'.$product->image)}}"> -->
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
																					<input type="hidden" name="product[{{$loop->index}}][receipt]" value="None" />
																					<input type="hidden" name="product[{{$loop->index}}][product_id]" value="{{$product->id}}" />
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
										<div class="col-md-12 mt-4">
											<div class="card card-default">
												<div class="card-header">
													<h3 class="card-title">
														<i class="fas fa-bullhorn"></i>
														Callouts
													</h3>
												</div>
												<!-- /.card-header -->
												<div class="card-body">
													<div class="callout callout-danger">
														<h5>Hi there.</h5>
														<p>As you have selected only "Forwarding services". you can proceed and order your items on the given deliveringparcel residential address and add tracking details here so we can easily follow up the shipment.</p>
													</div>
													<div class="callout callout-info">
														<p>Please make sure you have put on all the credentials and information properly and correctly. we strongly recommended to use the same information in order to avoid confusion and conflicts with shipments.</p>
													</div>
													<div class="callout callout-warning">
														<p>Please do instruct the couriers to use door to door service and with authorised signature release. we will not be responsible if the shipment is dropped off to pick up point or dropped at mailbox/mailroom.</p>
													</div>
												</div>
												<!-- /.card-body -->
											</div>
											<!-- /.card -->
										</div>
										@else
										<div class="col-md-12 mt-4">
											<div class="card card-default">
												<div class="card-header">
													<h3 class="card-title">
														<i class="fas fa-bullhorn"></i>
														Callouts
													</h3>
												</div>
												<!-- /.card-header -->
												<div class="card-body">
													<div class="callout callout-danger">
														<h5>Hi there.</h5>
														<p>As you have ordered items and place tracking details. Once we receive we receive items we will send you a confirmation. </p>
													</div>
												</div>
												<!-- /.card-body -->
											</div>
											<!-- /.card -->
										</div>
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
											<form action="{{route('tracking_id',$product->id)}}" method="post" enctype="multipart/form-data">
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
								<div class="col-md-12 mt-4">
									<div class="card card-default">
										<div class="card-header">
											<h3 class="card-title">
												<i class="fas fa-bullhorn"></i>
												Callouts
											</h3>
										</div>
										<!-- /.card-header -->
										<div class="card-body">
											<div class="callout callout-danger">
												<h5>Hi there.</h5>
												<p>As you have opted to go for "Purchase Assistance". we will purchase the given items for you within 2 working days.</p>
											</div>
											<div class="callout callout-info">
												<p>In case of limited stock or unavailability, we will refund you the amount charged for the items which can not be purchase in given time.</p>
											</div>
										</div>
										<!-- /.card-body -->
									</div>
									<!-- /.card -->
								</div>
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
								<!-- payment section using stripe payment -->
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
													<form role="form" id="payment" action="{{route('stripe')}}" method="post" class="require-validation" data-cc-on-file="false" data-stripe-publishable-key="{{ config('services.stripe.key') }}" id="payment-form">
														@csrf
														<div class='form-row row'>
															<div class="form-group col-md-6 col-xs-12">
																<label class='control-label'>Name on Card</label> <input class='form-control' size='4' type='text'>
															</div>
															<div class='col-xs-12 col-md-6 form-group required'>
																<label class='control-label'>Card Number</label> <input autocomplete='off' class='form-control card-number' size='20' type='text'>
															</div>
														</div>
														<div class='form-row row'>
															<div class='col-xs-12 col-md-4 form-group cvc required'>
																<label class='control-label'>CVC</label> <input autocomplete='off' class='form-control card-cvc' placeholder='ex. 311' size='4' type='text'>
															</div>
															<div class='col-xs-12 col-md-4 form-group expiration required'>
																<label class='control-label'>Expiration Month</label> <input class='form-control card-expiry-month' placeholder='MM' size='2' type='text'>
															</div>
															<div class='col-xs-12 col-md-4 form-group expiration required'>
																<label class='control-label'>Expiration Year</label> <input class='form-control card-expiry-year' placeholder='YYYY' size='4' type='text'>
															</div>
														</div>
														<div class='form-row row'>
															<div class='col-md-12 error form-group hide'>
																<div class='alert-danger alert'>Please correct the errors and try
																	again.
																</div>
															</div>
														</div>
														<p>* CVV or CVC is the card security code, unique three digits number on the back of your card seperate from its number.</p>
														<div class="row">
															<div class="col-xs-12">
																<input type="hidden" name="offer_id" class="offer_id" value="{{$offer->id}}">
																<input type="hidden" name="offer_status" class="offer_status" value="1">
																<input type="hidden" name="order_id" value="{{$order->id}}" />
																<input type="hidden" name="total" class="payment_total" value="{{$offer->total}}">
																<button class="btn btn-primary btn-lg btn-block" type="submit">Pay Now (${{$offer->total}})</button>
															</div>
														</div>
													</form>
												</div>
											</div>
										</div>
									</div>
								</section>
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
																		<img id="imageresource" src="{{asset('uploads/productsimages/'.$product->image)}}" width="60" height="60">
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
																					<a download="{{$product->image}}" href="{{asset('uploads/productsimages/'.$product->image)}}" title="ImageName">
																						<img id="imageresource" src="{{asset('uploads/productsimages/'.$product->image)}}">
																					</a>
																					<!-- <img id="imageresource" src="{{asset('uploads/productsimages/'.$product->image)}}"> -->
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
										<div class="col-md-12 mt-4">
											<div class="card card-default">
												<div class="card-header">
													<h3 class="card-title">
														<i class="fas fa-bullhorn"></i>
														Callouts
													</h3>
												</div>
												<!-- /.card-header -->
												<div class="card-body">
													<div class="callout callout-danger">
														<h5>Hi there.</h5>
														<p>“ your items have been packed according to the services , if you are content and happy with the services provided , please confirm the item and package to be shipped so we can get this shipment on its way to your given destination “ </p>
													</div>
													<div class="callout callout-info">
														<p>Incase you need to change the address or need to instruct us with any thing , you can leave a note for us in chat box or get intouch with us any time quoting the order number “</p>
													</div>
												</div>
												<!-- /.card-body -->
											</div>
											<!-- /.card -->
										</div>
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
																		<img id="imageresource" src="{{asset('uploads/productsimages/'.$offer_product->image)}}" width="60" height="60">
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
																					<img id="imageresource" src="{{asset('uploads/productsimages/'.$offer_product->image)}}" width="350" height="350">
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
										<div class="col-md-12 mt-4">
											<div class="card card-default">
												<div class="card-header">
													<h3 class="card-title">
														<i class="fas fa-bullhorn"></i>
														Callouts
													</h3>
												</div>
												<!-- /.card-header -->
												<div class="card-body">
													<div class="callout callout-danger">
														<h5>Hi there.</h5>
														<p>“ your items have been packed according to the services , if you are content and happy with the services provided , please confirm the item and package to be shipped so we can get this shipment on its way to your given destination “ </p>
													</div>
													<div class="callout callout-info">
														<p>Incase you need to change the address or need to instruct us with any thing , you can leave a note for us in chat box or get intouch with us any time quoting the order number “</p>
													</div>
												</div>
												<!-- /.card-body -->
											</div>
											<!-- /.card -->
										</div>
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
											@if(!(is_null($order->order_status)))
											@if($order->order_status != 'completed')
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
											@else
											<div class="col-sm-12 text-center mt-4">
												<h2>Order Marked as Completed</h2>
												<!-- TrustBox widget - Review Collector -->
												<div class="trustpilot-widget" data-locale="en-GB" data-template-id="56278e9abfbbba0bdcd568bc" data-businessunit-id="5f5ff8b322ca0500015cc549" data-style-height="320px" data-style-width="100%">
													<a href="https://uk.trustpilot.com/review/deliveringparcel.com" target="_blank" rel="noopener">Trustpilot</a>
												</div>
												<!-- End TrustBox widget -->
											</div>
											@endif
											@endif
										</div>
									</div>
								</div>
								<div class="col-md-12 mt-4">
									<div class="card card-default">
										<div class="card-header">
											<h3 class="card-title">
												<i class="fas fa-bullhorn"></i>
												Callouts
											</h3>
										</div>
										<!-- /.card-header -->
										<div class="card-body">
											<div class="callout callout-danger">
												<p>“ Please confirm when you receive the package . “ </p>
											</div>
											<div class="callout callout-info">
												<p>And please do let us know how you feel about our service provided with your honest review . </p>
											</div>
										</div>
										<!-- /.card-body -->
									</div>
									<!-- /.card -->
								</div>
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
				// alert(response);
				$('#view_messages').html(chat);
				document.getElementById('ChatForm').reset();
				// $('#Message').val('');
				// $('#file-input').val();
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
					// $('.test').html(chat_count);
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
@endsection
<!-- mian content ends here -->