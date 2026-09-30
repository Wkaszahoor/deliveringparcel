{{-- type: html — sanitized on save via HtmlSanitizer (scripts/iframes removed) --}}
<div class="h2-container" style="padding-block:1rem">
    @if (!empty($widget['title']))<h3>{{ $widget['title'] }}</h3>@endif
    {!! $widget['html'] ?? '' !!}
</div>
