<header>
    <section class="partners">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-12 col-md-12 col-sm-10 col-12">
                    <img src="{{asset('images/brands/Group 2517.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>
    <section class="topbar container">
        <div class='row text-center'>
            <div class="col-lg-4 col-md-5 col-12 pt-3 spacing text-md-left email">
                <h6 class="email"><a href="mailto:sales@deliveringparcel.com">sales@deliveringparcel.com</a></h6>
            </div>
            <div class="col-lg-4 col-md-3 col-6 spacing">
                <a href="#"><img src="{{asset('images/deliveringlogo.png')}}" alt=""></a>
            </div>
            <div class="col-lg-4 col-md-4 col-6 spacing">
                @if(Auth::check())
                @if(Auth::user()->roles[0]->slug == 'admin')
                <a class="nav-link" href="{{route('admin-orders')}}">
                    <button type="button" class="btn " id="button">Dashboard</button>
                </a>
                @else
                <a class="nav-link" href="{{route('dashboard')}}">
                    <button type="button" class="btn " id="button">Dashboard</button>
                </a>
                @endif
                @else
                <a class="nav-link" href="{{ route('login') }}">
                    <button type="button" class="btn" id="button">Login/Register</button>
                </a>
                @endif
                <!-- <button type="button" class="btn " id="button">Login</button> -->
            </div>
        </div>
    </section>
    <section class="topnav">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler navbar-light offset-5 bt" type="button" data-toggle="collapse" data-target="#collapsenavbar">
                    <span class="navbar-toggler-icon  "></span>
                </button>
                <div class="collapse navbar-collapse text-center" id="collapsenavbar">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is('/')) ? 'active' : '' }} " aria-current="page" href="{{route('/')}}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::segment(1) === 'become-a-shopper' ? 'active' : null }}" href="{{route('become-a-shopper')}}">Become a Shopper</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  @if(Request::is('become-a-shipper') || Request::is('how_it_works_for_shipper.php')|| Request::is('become-a-shipper-for-delivering-parcel')) active @endif" href="{{route('become-a-shipper')}}">Become a Shipper</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::segment(1) === 'aboutus' ? 'active' : null }}" href="{{route('aboutus')}}">About us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::segment(1) === 'contact-details' ? 'active' : null }}" href="{{route('contact-details')}}">Contact us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::segment(1) === 'pick-and-pack-fulfillment-services' ? 'active' : null  }}" href="{{route('pick-and-pack-fulfillment-services')}}">Pick and Pack</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::segment(1) === 'fulfillment-service' ? 'active' : null }}" href="{{route('fulfillment-service')}}">Fulfillment Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::segment(1) === 'special-request' ? 'active' : null }}" href="{{route('special-request')}}">Special Request</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </section>
</header>