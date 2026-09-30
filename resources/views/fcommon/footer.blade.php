<!-- ======= Footer ======= -->
<section class="dp-carriers" aria-label="Carrier partners">
  <style>
    .dp-carriers { background: #0d2a52; padding: 26px 0 22px; overflow: hidden; }
    .dp-carriers-caption { text-align: center; color: rgba(255,255,255,.75); letter-spacing: .18em;
        text-transform: uppercase; font-size: .72rem; font-weight: 700; margin-bottom: 16px; }
    .dp-carriers-track { display: flex; width: max-content; animation: dp-carriers-scroll 34s linear infinite; }
    .dp-carriers:hover .dp-carriers-track { animation-play-state: paused; }
    .dp-carriers-row { display: flex; align-items: center; gap: 64px; padding-right: 64px; }
    .dp-carriers-row a { display: flex; align-items: center; justify-content: center; height: 52px;
        filter: grayscale(1) brightness(1.6); opacity: .85; transition: filter .25s ease, opacity .25s ease, transform .25s ease; }
    .dp-carriers-row a:hover { filter: none; opacity: 1; transform: translateY(-3px); }
    .dp-carriers-row img { max-height: 44px; width: auto; }
    @keyframes dp-carriers-scroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    @media (prefers-reduced-motion: reduce) { .dp-carriers-track { animation: none; } }
    @media (max-width: 575.98px) { .dp-carriers { padding: 18px 0 14px; } .dp-carriers-row img { max-height: 30px; } .dp-carriers-row { gap: 40px; padding-right: 40px; } }
  </style>
  <div class="dp-carriers-caption">Trusted carriers worldwide</div>
  <div class="dp-carriers-track">
    <div class="dp-carriers-row">
      <a href="https://www.canadapost-postescanada.ca/cpc/en/home.page" rel="noopener"><img src="{{ asset('images/canada-post.png') }}" alt="Canada Post" loading="lazy"></a>
      <a href="https://www.fedex.com/en-us/home.html" rel="noopener"><img src="{{ asset('images/Fedex-02 [Converted].png') }}" alt="FedEx" loading="lazy"></a>
      <a href="{{ url('/') }}"><img src="{{ asset('images/shipp.png') }}" alt="DeliveringParcel shipping" loading="lazy"></a>
      <a href="https://www.ups.com/pk/en/Home.page" rel="noopener"><img src="{{ asset('images/Ups-01 [Converted].png') }}" alt="UPS" loading="lazy"></a>
      <a href="https://www.dhl.com/pk-en/home.html?locale=true" rel="noopener"><img src="{{ asset('images/Dhl-01 [Converted].png') }}" alt="DHL" loading="lazy"></a>
    </div>
    <div class="dp-carriers-row" aria-hidden="true">
      <a href="https://www.canadapost-postescanada.ca/cpc/en/home.page" tabindex="-1"><img src="{{ asset('images/canada-post.png') }}" alt="" loading="lazy"></a>
      <a href="https://www.fedex.com/en-us/home.html" tabindex="-1"><img src="{{ asset('images/Fedex-02 [Converted].png') }}" alt="" loading="lazy"></a>
      <a href="{{ url('/') }}" tabindex="-1"><img src="{{ asset('images/shipp.png') }}" alt="" loading="lazy"></a>
      <a href="https://www.ups.com/pk/en/Home.page" tabindex="-1"><img src="{{ asset('images/Ups-01 [Converted].png') }}" alt="" loading="lazy"></a>
      <a href="https://www.dhl.com/pk-en/home.html?locale=true" tabindex="-1"><img src="{{ asset('images/Dhl-01 [Converted].png') }}" alt="" loading="lazy"></a>
    </div>
  </div>
</section>
<footer id="footer" class="footer">

  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 gap-y-8 md:grid-cols-12 md:gap-x-6">
      <div class="footer-info md:col-span-4">
        <a href="/" class="logo flex items-center">
          <span>Delivering Parcel</span>
        </a>
        <p>Deliveringparcel is the  Best International Shopper and Shipping Forwarder Company Worldwide . Shop Online from USA UK EUROPE Australia Asia and Ship to Any Country Globally.Free address In USA UK Spain Australia
        <br> Shop and Ship From anywhere to Everywhere.</p>
        <p>Best Parcel Forwarding Service</p>
        <div class="social-links flex mt-4">
          <a href="#" class="twitter"><i class="fab fa-twitter"></i></a>
          <a href="https://www.facebook.com/deliveringparcel" class="facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.instagram.com/delivering_parcel" class="instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://www.youtube.com/@deliveringparcel-ur8wc" class="youtube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <div class="footer-links md:col-span-2 col-span-1">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="{{ route('/') }}">Home</a></li>
          <li><a href="{{ url('services') }}">Services</a></li>
          <li><a href="{{ url('blog') }}">Blog</a></li>
          <li><a href="{{ route('testimonials') }}">Testimonials</a></li>
          <li><a href="{{ url('track-order') }}">Track Order</a></li>
          <li><a href="{{ route('contact-details') }}">Contact Us</a></li>
        </ul>
      </div>

      <div class="footer-links md:col-span-2 col-span-1">
        <h4>Our Services</h4>
        <ul>
          <li><a href="{{ url('services') }}">All Services</a></li>
          <li><a href="/request">Get a Quote</a></li>
          <li><a href="{{ url('track-order') }}">Track Shipment</a></li>
          <li><a href="{{ route('testimonials') }}">Customer Reviews</a></li>
          <li><a href="{{ route('contact-details') }}">Support 24/7</a></li>
        </ul>
      </div>

      <div class="footer-links md:col-span-2 col-span-1">
        <h4>Legal</h4>
        <ul>
          <li><a href="{{ route('privacy-policy') }}">Privacy policy</a></li>
          <li><a href="{{ route('refund-policy') }}">Refund policy</a></li>
          <li><a href="{{ route('terms-and-conditions') }}">Terms of business</a></li>
        </ul>
      </div>

      <div class="footer-contact md:col-span-2 text-center md:text-left">
        <h4>Contact Us</h4>
        <p>
          DELIVERINGPARCEL LTD, <br>
          27 Old Gloucester Street<br>
          London, United Kingdom, WC1N 3AX<br><br>
          <strong>Phone:</strong> +44-2039875200<br>
          <strong>Email:</strong> admin@deliveringparcel.com<br>
        </p>

      </div>

      <div class="col-span-1 md:col-span-12 flex flex-wrap items-center justify-center gap-4 pt-2">
        <img src="{{asset('images/image-22-1-p3yls2m3iqkx6cxxalr5fokdpgjf7elpx7vmrr06s8.png')}}" width="60" height="60" title="deliveringparcelsecure" alt="moneydeliverngparcel">
        <img src="{{asset('images/safep.png')}}" class="max-w-full h-auto" width="376" height="42" title="deliveringparcelsafe" alt="deliveringParcelsafe">
      </div>

      <div class="col-span-1 md:col-span-12">
        <hr class="border-t border-white/20 my-2">
      </div>

      <div class="col-span-1 md:col-span-12">
        <p class="text-right"><span style="color:#ffffff;">COMPANY NO. 12657292</span></p>
      </div>
    </div>
  </div>

  {{-- Website Bottom Bar — links managed in Admin -> Settings -> Menu Order (Bottom Bar) --}}
  <?php $dpBottomMenu = app(\App\Services\Cms\CmsNavService::class)->getMenu('footer_bottom'); ?>
  @if ($dpBottomMenu->isNotEmpty())
  <div class="container mx-auto px-4 mt-3">
    <div class="dp-bottom-bar flex flex-wrap items-center justify-center gap-3 py-2 border-t border-white/20">
      @foreach ($dpBottomMenu as $dpBItem)
        <a href="{{ url($dpBItem->url) }}" class="no-underline text-gray-400 text-sm">{{ $dpBItem->label }}</a>
      @endforeach
    </div>
  </div>
  @endif

  <div class="container mx-auto px-4 mt-4">
    <div class="copyright">
      &copy; Copyright <strong><span>DeliveringParcel</span></strong>. All Rights Reserved

    </div>

    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/logis-bootstrap-logistics-website-template/ -->
      Developed by <a href="https://deliveringparcel.com/">DeliveringParcel IT team</a>

   </div>
  </div>

</footer><!-- End Footer -->
<!-- End Footer -->
<a href="#" class="scroll-top flex items-center justify-center"><i class="fas fa-arrow-up"></i></a>

  <div id="preloader"></div>

 
  {{-- Root-relative (via asset()), not "frontend/..." bare relative paths —
       a bare relative src resolves against the CURRENT URL, not site root:
       it happened to work on "/" (no path segments to confuse it) but 404'd
       on any nested route like /services/{slug}, where the browser resolved
       it as /services/frontend/... instead. Since these are blocking classic
       <script> tags, every 404 here also stalled all HTML parsing after it —
       main.js (and its preloader-removal code) never even ran. --}}
  <script src="{{ asset('frontend/assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
  <script src="{{ asset('frontend/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('frontend/assets/vendor/php-email-form/validate.js') }}"></script>

  <!-- Template Main JS File -->
  <script src="{{ asset('frontend/assets/js/main.js') }}?v=20260929c"></script>
</html>
