<!DOCTYPE html>
<html lang="en">  
    <!-- BEGIN: Head -->
    @include('common.head')
    <!-- END: Head -->
    <body>
    @include('common.noscript')
        @include('partials.marquee')

        <!-- BEGIN: Navigation + top head -->
        @include('common.navbar')
        <!-- END: Navigation + top head -->
        <!-- BEGIN: Content -->
        @yield('content')
        <!-- END: Content -->
        <!--- Brands sliders ---->
        @include('brands')
        <!-- END: Brands -->
        @include('common.footer')
        <script src="{{url('dashbord/plugins/jquery/jquery-3.6.0.min.js')}}"></script>
        <script src="{{url('assets/OwlCarousel2/owl.carousel.min.js')}}"></script>
        <script>
            var owl = $('.owl-carousel');
            owl.owlCarousel({
                items:6,
                loop:true,
                margin:10,
                autoplay:true,
                arrows: true,
                nav: true,
                autoplayTimeout:1500,
                autoplayHoverPause:true,
                responsiveClass:true,
                navText: ["<div class='nav-button owl-prev'>‹</div>", "<div class='nav-button owl-next'>›</div>"],
                responsive: {
                0: {
                    items: 1
                },
                768: {
                    items: 2
                },
                1024: {
                    items: 3
                },
                1200: {
                    items: 5
                }
            }
            });
            $('.play').on('click',function(){
                owl.trigger('play.owl.autoplay',[1500])
            })
            $('.stop').on('click',function(){
                owl.trigger('stop.owl.autoplay')
            })
        </script>
    </body>
</html>