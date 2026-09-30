/**
* Template Name: Logis - v1.3.0
* Template URL: https://bootstrapmade.com/logis-bootstrap-logistics-website-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*
* FIXED: Wrapped PureCounter, GLightbox, Swiper in typeof checks
* so a missing vendor no longer crashes the entire DOMContentLoaded
* callback — meaning the mobile nav toggle now always works.
*/
document.addEventListener('DOMContentLoaded', () => {
  "use strict";

  /**
   * Preloader
   * Removed on DOMContentLoaded, not window.load — window.load waits on
   * every subresource including third-party scripts (Google Tag Manager,
   * Facebook pixel, in @vite('fcommon/head.blade.php')), so a slow or
   * unreachable third party used to leave this preloader stuck on screen
   * indefinitely. Render-blocking stylesheets in <head> are already applied
   * by the time DOMContentLoaded fires, so there's nothing to wait for.
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    preloader.remove();
  }

  /**
   * Sticky header on scroll
   */
  const selectHeader = document.querySelector('#header');
  if (selectHeader) {
    document.addEventListener('scroll', () => {
      window.scrollY > 100 ? selectHeader.classList.add('sticked') : selectHeader.classList.remove('sticked');
    });
  }

  /**
   * Scroll top button
   */
  const scrollTop = document.querySelector('.scroll-top');
  if (scrollTop) {
    const togglescrollTop = function() {
      // window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
    window.addEventListener('load', togglescrollTop);
    document.addEventListener('scroll', togglescrollTop);
    scrollTop.addEventListener('click', window.scrollTo({
      top: 0,
      behavior: 'smooth'
    }));
  }

  /**
   * Mobile nav toggle
   * FIX: This now runs first and is isolated from vendor failures below
   */
  const mobileNavShow = document.querySelector('.mobile-nav-show');
  const mobileNavHide = document.querySelector('.mobile-nav-hide');

  document.querySelectorAll('.mobile-nav-toggle').forEach(el => {
    el.addEventListener('click', function(event) {
      event.preventDefault();
      mobileNavToogle();
    });
  });

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavShow.classList.toggle('d-none');
    mobileNavHide.classList.toggle('d-none');
  }

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navbar a').forEach(navbarlink => {
    if (!navbarlink.hash) return;
    let section = document.querySelector(navbarlink.hash);
    if (!section) return;
    navbarlink.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });
  });

  /**
   * Toggle mobile nav dropdowns
   */
  const navDropdowns = document.querySelectorAll('.navbar .dropdown > a');
  navDropdowns.forEach(el => {
    el.addEventListener('click', function(event) {
      if (document.querySelector('.mobile-nav-active')) {
        event.preventDefault();
        this.classList.toggle('active');
        this.nextElementSibling.classList.toggle('dropdown-active');
        let dropDownIndicator = this.querySelector('.dp-nav-caret');
        dropDownIndicator.classList.toggle('bi-chevron-up');
        dropDownIndicator.classList.toggle('bi-chevron-down');
        // This project uses FontAwesome (fas fa-chevron-*), not the Bootstrap
        // Icons this template ships with — toggle those classes too so the
        // indicator actually flips instead of silently no-op'ing.
        dropDownIndicator.classList.toggle('fa-chevron-up');
        dropDownIndicator.classList.toggle('fa-chevron-down');
      }
    });
  });

  /**
   * Desktop dropdown hover-intent
   * Plain CSS :hover closes a flyout the instant the pointer leaves the
   * trigger — any brief wobble on the way down into the menu (now more
   * likely with two dropdown triggers, Destinations and Resources, sitting
   * side by side) closes it before a link can be reached. A short close
   * delay keeps the menu open through that wobble without touching the
   * CSS open animation, and .dp-dropdown-open only adds to the existing
   * :hover rules in main.css — nothing here replaces them, so this degrades
   * safely if JS fails to load.
   */
  document.querySelectorAll('.navbar .dropdown').forEach(dropdown => {
    let closeTimer;
    dropdown.addEventListener('mouseenter', () => {
      clearTimeout(closeTimer);
      dropdown.classList.add('dp-dropdown-open');
    });
    dropdown.addEventListener('mouseleave', () => {
      closeTimer = setTimeout(() => dropdown.classList.remove('dp-dropdown-open'), 300);
    });
  });

  /**
   * Initiate PureCounter
   * FIX: Wrapped in typeof check — if vendor missing, skip gracefully
   */
  if (typeof PureCounter !== 'undefined') {
    new PureCounter();
  }

  /**
   * Initiate GLightbox
   * FIX: Wrapped in typeof check — if vendor missing, skip gracefully
   */
  if (typeof GLightbox !== 'undefined') {
    const glightbox = GLightbox({
      selector: '.glightbox'
    });
  }

  /**
   * Init Swiper slider
   * FIX: Wrapped in typeof check — if vendor missing, skip gracefully
   */
  if (typeof Swiper !== 'undefined') {
    new Swiper('.slides-1', {
      speed: 600,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false
      },
      slidesPerView: 'auto',
      pagination: {
        el: '.swiper-pagination',
        type: 'bullets',
        clickable: true
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      }
    });
  }

  /**
   * Animation on scroll
   * FIX: Wrapped in typeof check — if vendor missing, skip gracefully.
   * Called directly here on DOMContentLoaded, not from a nested
   * window.load listener — window.load waits on every subresource
   * (fonts, images, third-party scripts), so on a slow connection every
   * data-aos element sat invisible (opacity: 0, its pre-animation state)
   * until load finally fired, sometimes several seconds after the page
   * was already visible and scrollable. See the preloader fix above for
   * the same reasoning.
   */
  function aos_init() {
    if (typeof AOS !== 'undefined') {
      AOS.init({
        duration: 1000,
        easing: 'ease-in-out',
        once: true,
        mirror: false
      });
    }
  }
  aos_init();

});