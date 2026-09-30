@extends('layouts.fmaster')
<!--@section('title','Contact Us | Get Free Address by Mail And Package Forwarding Services')-->
<!--@section('keywords', 'b&h store usa,shipping overseas cheapest,delivering parcel,uk dress size to us,envia a mexico,shop and ship calculator,planetexpress')-->
<!--@section('meta_description', 'Best parcel forwarding service Europe,ship for you,what to buy in us,packaging companies,myus,consolidation service')-->
@section('title')
Contact Us | Parcel & Package Forwarding Support
@endsection

@section('description')
Contact our parcel forwarding support team for help with international package forwarding, mail forwarding services, shipping assistance,Shop and ship,proxy buying and forwarding address support.
@endsection

@section('keywords')
contact parcel forwarding, package forwarding support, mail forwarding service support, international parcel forwarding help, forwarding address support, package receiving service support, reshipping service help, international shipping assistance, shop and ship support, buy and ship support, package consolidation help,Free usa address,Norway parcel forwarder,Denmark package reshipper,France forwarding address,Italy parcel forwarding,Romania reship,Serbia package forwarder,Bulgaria mail forwarding,Malaysia proxy buyer,Indonesia reshipper,Shopee forwarding service,Spain package forwarding,Europe best mail forwarding service,Europe personal shopper,Buy hermes france ship to japan,Gucci ship to japan,Vinted reshipping service,Buy from finnno from usa,Buy iqoro,Parcel forwarding germany,forwarding agent

@endsection
@section('meta_description', 'Best parcel forwarding service Europe,ship for you,what to buy in us,packaging companies,myus,consolidation service')


@section('content')
<div class="breadcrumbs">
      <div class="page-header flex items-center" style="background-image: url('frontend/assets/img/page-header.jpg');">
        <div class="container mx-auto px-4 relative">
          <div class="flex justify-center">
            <div class="w-full lg:w-1/2 text-center">
<h1 style="color: white;">Contact Us</h1>
<p>Get the Best Parcel forwarding Service by Becoming  a shopper with Deliveringparcel .New to Our site and Need more information about Our Service Works and any  questions related to Shopping , Shipping and Package Forwarding , Please feel free to Ask by filling up the form Below</p>
            </div>
          </div>
        </div>
      </div>
      <nav>
        <div class="container mx-auto px-4">
          <ol>
            <li><a href={{ route('/') }}>Home</a></li>
            <li>Contact</li>
          </ol>
        </div>
      </nav>
    </div>

	    
		 
@include('contactus-form')
		  
	</div>
</section>




@endsection