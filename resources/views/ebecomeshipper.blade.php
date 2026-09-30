<!-- main layout start from here -->
@extends('layouts.master')
<!-- main layout ends here -->
<!-- title for header start from here -->
@section('title','Shipper')
@section('keywords', 'Become a Shipper with delivering parcel , Parcel forwarding service the USA , best parcel forwarding services')
<!-- title ends here -->
<!-- main content start from here -->
@section('content')
<section class="shopper-page">
    <section class="shipper-banner">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-12 col-md-12 col-sm-10">
                    <h1 class="banner-h1">Become a Shipper</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="container shopper-work">
        <h1 class="style-h1">How it works for Shipper?</h1>
        <div class="row">
            <div class="col-md-12 col-sm-12 ">
                <img src="{{asset('images/pexels-negative-space-34577@2x-1.jpg')}}" alt="How it works for Shipper" class="img-fluid">
            </div>
            <div class="col-md-12 pt-5 ">
                <p class="style-p">
                    Deliveringparcel.com is a community where people need a solution to shop and ship from any country or any international store or website.
                </p>
                <p class="style-p"> Delivering parcel is offering them solutions where we can help them get their desired product or item from any part of the world. it can be in-store or any genuine goods to be sold.</p>
                <p class="style-p">Shippers are individuals or local businesses who can provide their address and services for shopping and help with international mail or parcel forwarding.</p>
                <p class="style-p">Deliveringparcel.com is a much-desired platform created to help Shippers make money while at home, at their own comfort while making this world a borderless market.</p>
            </div>
        </div>
    </section>
    <section class="container shiper-padding">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-8">
                <p class="style-p"> All the payment systems created for shippers will be dealt with by Deliveringparcel.com delegated accountants and the payment system will be maintained at a virtual database system stored in the account of every shipper.</p>
                <p class="style-p">All transactions at Deliveringparcel.com are securely processed either by Paypal or Bank transfer.</p>
                <p class="style-p">The Schedule of payment to each shipper is notified by email in advance of any offer accepted by the shipper.</p>
                <p class="style-p">Start by creating your profile page and complete verifications. You’ll fill out a description, and state the additional services you serve. Your profile helps us understand your availability and we offer you services that suit you in the best of your interest.</p>
                <p class="style-p">We’re here to help. From getting you ready and understanding your responsibilities, we’ve got tools and resources for you.</p>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-8 ">
                <img src="{{asset('images/cardmapr-hTUZW7E7krg-unsplash@2x.jpg')}}" alt="How it works for Shipper" class="img-fluid">
            </div>
        </div>
    </section>
    <section class="container">
        <p class="style-p">Keeping the community/market place safe is important for everyone. To make sure that all our Shippers are real, we require individuals to upload a copy of their ID and companies to submit a copy of their business license or another similar document. This helps prevent fraud and ensures a high level of trust within the deliveringparcel.com community. Individual ID’s should show your name, your address, and the name of the issuing authority. If you represent a company, provide a company document (business license, certificate, or similar) that should list your company name, address, and the name of the issuing authority. Any documents you submit are only visible to the administration, stored in a secure database, and never released to any third-parties.</p>
        <p class="style-p">Carefully study Customs regulations of your country and the country you need to ship the package to in relation to the items requested. This will help you prevent disputes and situations when the items are seized at customs. good knowledge of the logistics process, decent presentation, and awareness of the procurement process can be a great essence to a good business venture.</p>
        <p class="style-p">Better the service you provide, the more likely you will get more orders in the future.</p>
        <p class="style-p">Deliveringparcel.com will be updating you with the transit and processing of any package directed towards you all the time in case you miss any of the details regarding the parcel.</p>
        <p class="style-p">Shippers are advised to keep an eye on their emails for any offer of package handling, in most cases they can be more than one shopper from the same country or region, requests can be handled to the one who been proactive in accepting the offer.</p>
        <p class="style-p">All the payment systems and invoices are managed by our administration, so shippers don’t have to stress about the payment process and dealing with invoices. shippers will be notified about particular deliveries and packages through their database and portal. from accepting the offer, handling, printing the labels, all will be through the channel, and the shipper will be kept updated with each and every process.</p>
        <p class="style-p">Package forwarding with Deliervingparcel.com is a safe and promising business. Thanks to the multifunctional half-automated mechanism you can receive payments for your services, and manage the order throughout the process. If you have any specific questions about package shipping at deliveringparcel.com, please feel free to ask us at <strong class="color">admin@deliveringparcel.com</strong> </p>
    </section>
    <section class="container ">
        <div class="row">
            <div class="col-xl-4 col-lg-6 col-md-6 col-8 offset-xl-4 offset-md-3 offset-2">
                <a href="{{route('contact-details')}}"><button type="button" class="btn btn-block ">Contact us</button></a>
            </div>
        </div>
    </section>
</section>
@endsection
<!-- main content ends here -->