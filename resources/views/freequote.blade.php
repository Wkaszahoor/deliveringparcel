@extends('layouts.master')
@section('title','Special Request')
@section('keywords', 'Special Request for parcel delivery , Parcel forwarding service Australia')
@section('content')
<section class="container special-request">
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="tab-pane active" id="address" role="tabpanel">
                <h1 class="text-center">Request a free quote</h1>
                @include('flash-message')
                <div class="card-body">
                    <form action="{{route('freequote.store')}}" id="freequoteform" method="post" enctype="multipart/form-data">
                        <div class="row">
                            @csrf
                            @if(Auth::user())
                            <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                            @else
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="" placeholder="Name" data-height="40" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="" placeholder="Email" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="phone">Phone Number</label>
                                <input type="text" class="form-control" name="number" id="phone" value="" placeholder="Mobile" required />
                            </div>
                            <!-- <input id="password" type="hidden" class="form-control @error('password') is-invalid @enderror" name="password" value="" required autocomplete="new-password"> -->
                            <input type="hidden" name="user_id" value="user-invalid">
                            @endif

                            <div class="form-group col-md-6 col-sm-12">
                                <label for="cargotype">Cargo Type</label>
                                <input type="text" class="form-control" name="cargotype" id="cargotype" value="" placeholder="Cargo type" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="country">Country of Origin</label>
                                <input type="text" class="form-control" name="country" id="country" value="" placeholder="country" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="destination">Destination</label>
                                <input type="text" class="form-control" name="destination" id="destination" value="" placeholder="destination" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="shipping_Quantity">Quantity</label>
                                <input type="number" class="form-control" name="shipping_Quantity" id="shipping_Quantity" value="" placeholder="" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="weight">Weight</label>
                                <input type="text" class="form-control" name="weight" id="weight" value="" placeholder="2 kg" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="width">Width</label>
                                <input type="text" class="form-control" name="width" id="width" value="" placeholder="cm/mm/m" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="height">Height</label>
                                <input type="text" class="form-control" name="height" id="height" value="" placeholder="cm/mm/m" required />
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="detail">Please describe your requirement in detail including Product URL, special needs and handling related to the request</label><br>
                                <textarea name="detail" class="form-control" id="detail" required></textarea>

                            </div>
                            <div class="col-md-12 col-sm-12">
                                @include('partials.turnstile')
                                <button type="submit" class="btn shipping_btn">SEND</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="loader"></div>

    </div>
</section>
<script>
    $('#freequoteform').submit(function() {
        $('#loader').css('visibility', 'visible');
    });
</script>

@endsection