@extends('layouts.master')

@section('title','Contact Us')
@section('keywords', 'Contact Details of delivering parcel , Parcel forwarding service europe, USA, UK, WorldWide')
@section('content')
<section class="contactus-banner">
	<div class="container">
		<div class="row text-center">
			<div class="col-lg-12 col-md-12 col-sm-10 ">
				<h1 class="banner-h1">Get In Touch</h1>
			</div>

		</div>
	</div>
</section>
<section>
	<div class="container">
		<div class="row m-auto contactmap">
			<div class="col-md-6 col-sm-6 ">
				<h1 class="style-h1">Where to find us?</h1>
				<div>
					<h2 class="style-h4">ADDRESS</h2>
					<span itemprop="streetAddress" class="style-p">DELIVERINGPARCEL LTD,<br>27 Old Gloucester Street, <br>London, United Kingdom, WC1N 3AX</span>
				</div>
				<br>
				<div>
					<h2 class="style-h4">COMPANY INFORMATION</h2>

					<span itemprop="streetAddress" class="style-p">
						Company Number: 12657292 ,<br>
						Registered in UK<br>
						Phone: +44-2039875200<br>
						Email: sales@deliveringparcel.com
					</span>

				</div>


			</div>

			<div class="col-md-6 col-sm-6">
				<div style="width: 100%"><iframe width="100%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=27%20Old%20Gloucester%20St%20Holborn,%20London%20WC1N%203AF%20UK+(%20Business)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe><a href="https://www.maps.ie/route-planner.htm"></a></div>
			</div>

		</div>
	</div>
</section>


@include('contactus-form')


@endsection