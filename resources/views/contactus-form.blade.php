@section('title','Contact Us')
@section('keywords', 'Contact Details of delivering parcel , Parcel forwarding service europe, USA, UK, WorldWide')
<section id="contact" class="contact">
  <div class="container mx-auto px-4" data-aos="fade-up">
@include('flash-message')
    <div>
      <iframe style="border:0; width: 100%; height: 340px;" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=27%20Old%20Gloucester%20St%20Holborn,%20London%20WC1N%203AF%20UK+(%20Business)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" frameborder="0" allowfullscreen></iframe>
    </div><!-- End Google Maps -->

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-y-4 lg:gap-x-6 mt-4">

      <div class="lg:col-span-4">

        <div class="info-item flex">
          <i class="fas fa-map-marker-alt flex-shrink-0"></i>
          <div>
            <h4>Location:</h4>
           <p> DELIVERINGPARCEL LTD,<br>27 Old Gloucester Street, <br>London, United Kingdom, WC1N 3AX</p>
          </div>
        </div><!-- End Info Item -->

        <div class="info-item flex">
          <i class="fas fa-envelope flex-shrink-0"></i>
          <div>
            <h4>Email:</h4>
            <p>info@deliveringparcel.com</p>
          </div>
        </div><!-- End Info Item -->

        <div class="info-item flex">
          <i class="fas fa-phone flex-shrink-0"></i>
          <div>
            <h4>Call:</h4>
            <p> +44-2039875200</p>
          </div>
        </div><!-- End Info Item -->

      </div>

      <div class="lg:col-span-8">
        <form action="{{route('contactus.store')}}" id="contact-form" method="post">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
            </div>
            <div class="form-group mt-3 md:mt-0">
              <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
            </div>
          </div>
          <div class="form-group mt-3">
            <input type="text" class="form-control" name="address" id="address" placeholder="Subject" required>
          </div>
          <div class="form-group">
                        <input type="number" class="form-control" name="number" placeholder="Phone Number" required />

          </div>
          <div class="form-group mt-3">
            <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
          </div>

          @include('partials.turnstile')
          <div class="text-center"><button type="submit" class="btn btn-primary">Send Message</button></div>
        </form>
      </div><!-- End Contact Form -->

    </div>

  </div>
</section>
          
<div id="loader"></div>
    <script>
        $('#contact-form').submit(function() {
            $('#loader').css('visibility', 'visible');
        });
    </script>
        </div>
 

    </div>
