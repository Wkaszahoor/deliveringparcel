{{-- Cloudflare Turnstile widget. Place INSIDE the <form> before the submit
     button; the hidden cf-turnstile-response field is injected automatically
     and submitted with the form.
     Optional $page variable ('login'|'register'|'password_reset') enables
     per-page admin control; without it the master api_turnstile_enabled
     setting alone decides (off = renders nothing, server check also skips). --}}
@php $turnstilePage = $page ?? null; @endphp
@if(\App\Support\Turnstile::pageEnabled($turnstilePage))
<div class="cf-turnstile" data-sitekey="{{ \App\Support\Turnstile::siteKey() }}" style="margin-bottom:.75rem"></div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
