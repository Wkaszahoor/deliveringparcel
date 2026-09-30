<!-- main layout start from here -->
@extends('layouts.fmaster')
<!-- main layout ends here -->
<!-- title for header start from here -->
@section('title', 'About Us | Delivering Parcel')
@section('keywords', 'Parcel Forwarding Service')
<!-- title ends here -->
<!-- main content start from here -->
@section('content')

<div class="breadcrumbs">
    <div class="page-header flex items-center" style="background-image: url('{{ asset('frontend/assets/img/page-header.jpg') }}');">
        <div class="container mx-auto px-4 relative">
            <div class="flex justify-center">
                <div class="w-full lg:w-1/2 text-center">
                    <h1 style="color: white;">About Us</h1>
                </div>
            </div>
        </div>
    </div>
    <nav>
        <div class="container mx-auto px-4">
            <ol>
                <li><a href="{{ route('/') }}">Home</a></li>
                <li>About</li>
            </ol>
        </div>
    </nav>
</div>

<section class="container mx-auto px-4 py-10 md:py-14">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <div>
            <h2 class="text-2xl font-bold mb-4" style="color:#0e1d34;">How does it provide the best Parcel forwarding service?</h2>
            <p class="mb-4 text-gray-600 leading-relaxed">Deliveringparcel.com is the best parcel forwarding service which connects you globally with us and you get access to online stores in every country across the world.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">You can access any online stores and even request in-store shopping assistance from any country and delivering parcel will provide you the best parcel forwarding service.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">You can enjoy discounts and offer all over the world and get hold of pre-release products, pre-orders, and items from any top shopping destinations like the USA, AUSTRALIA, EUROPE, ASIA, naming all the countries, we are Everywhere for you. International shopping can help you save money if you shop sales and use discount coupons as if you lived in the store's country.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">Enjoy shopping at huge international and national retailers like AMAZON or EBAY of any country or shops and get it shipped to any part of the world with us with no limitations at all.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">SHOP and SHIP from any online store like <strong class="text-brand">APPLE, WALLMART, CARREFOUR, TESCO, WOOLWORTHS</strong> with the help of our members and shippers who can provide shopping assistance with the best interpersonal skills.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">Your Package will be handled by the world's best carrier to handle your packages and shipments. Our logistics team works in the best relationship with these partners and give you the best shipping rates.</p>
            <p class="mb-0 text-gray-600 leading-relaxed">Get the best service from us by using our good terms and business deals with shipping companies like <b>USPS, ROYAL MAIL, DHL, FEDEX, UPS, TNT</b></p>
        </div>
        <div>
            <img src="{{ asset('images/aboutuspage1.png') }}" class="max-w-full h-auto rounded-lg" alt="About Delivering Parcel">
        </div>
    </div>
</section>

<section class="container mx-auto px-4 py-10 md:py-14">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <div class="order-2 lg:order-1">
            <img src="{{ asset('images/aboutuspage2.png') }}" class="max-w-full h-auto rounded-lg pb-4 lg:pb-0" alt="How Delivering Parcel started">
        </div>
        <div class="order-1 lg:order-2">
            <h2 class="text-2xl font-bold mb-4" style="color:#0e1d34;">How Delivering Parcel forwarding service Started?</h2>
            <p class="mb-4 text-gray-600 leading-relaxed">Trading is been there from the beginning of mankind and the stone ages but since the human created borders have restricted the free flowing transaction. Its been a need of time that such a solution was imminent.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">we did a lot of discussion and research to find a solution where people don't have to rely on companies to charge them heavily for international shipping and also not to deprive them of their much-needed products.</p>
            <p class="mb-4 text-gray-600 leading-relaxed">Company after good internal marketing manage to form an alliance with reliable members who are living all over the globe and started this venture initially operated from few countries and slowly expanding the vision and reaching out to the mass market.</p>
            <p class="mb-0 text-gray-600 leading-relaxed">We can proudly say we are available to provide the best parcel forwarding service in every continent and trying to fill the gaps with our added "shippers".</p>
        </div>
    </div>
</section>

<section class="about-mission py-10 md:py-14" style="background:#f8f9fc;">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div>
                <h2 class="text-2xl font-bold mb-2" style="color:#0e1d34;">Our Mission</h2>
                <p class="mb-4 mt-4"><b>You order, we deliver</b></p>
                <p class="mb-4 text-gray-600 leading-relaxed">Our mission is to provide the best parcel forwarding service and we are providing a platform where the community can rely on to get the items and products from any International famous brands and retailers they wish for and on the other hand, help the needy make some revenue.</p>
                <p class="mb-0 text-gray-600 leading-relaxed">From "shop" to "ship" providing all the solutions at one place is surely a perfect idea. Providing a solution to bypass all the custom made barriers, International trade barriers, VAT surcharges and other hidden cost.</p>
            </div>
            <div class="mb-5">
                <img src="{{ asset('images/Group 2495.png') }}" class="max-w-full h-auto" alt="Our mission">
            </div>
        </div>
    </div>
</section>

@include('contactus-form')

@endsection
<!-- main content ends here -->
