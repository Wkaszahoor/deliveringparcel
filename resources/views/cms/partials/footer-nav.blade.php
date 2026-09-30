{{-- CMS footer navigation partial (spec SECTION 9).
     Renders the $cmsFooterMenus array injected by CmsServiceProvider's view composer:
     ['col1' => Collection, 'col2' => Collection, 'col3' => Collection, 'bottom' => Collection].
     Renders NOTHING if the variable is missing. --}}
@isset($cmsFooterMenus)
<div class="cms-footer-nav">
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:1.5rem">
        @foreach (['col1', 'col2', 'col3'] as $cmsFooterColumn)
            @isset($cmsFooterMenus[$cmsFooterColumn])
                @if (count($cmsFooterMenus[$cmsFooterColumn]))
                    <div class="cms-footer-col">
                        <ul class="list-unstyled" style="margin:0; padding:0; list-style:none">
                            @foreach ($cmsFooterMenus[$cmsFooterColumn] as $item)
                                <li style="margin:.35rem 0">
                                    <a href="{{ $item->resolved_url }}" target="{{ $item->target }}">
                                        @if ($item->icon)<i class="fa {{ $item->icon }} me-2"></i>@endif
                                        {{ $item->label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endisset
        @endforeach
    </div>

    @isset($cmsFooterMenus['bottom'])
        @if (count($cmsFooterMenus['bottom']))
            <div class="cms-footer-bottom" style="display:flex; gap:.6rem; flex-wrap:wrap; align-items:center; margin-top:1.5rem">
                @foreach ($cmsFooterMenus['bottom'] as $item)
                    @if (! $loop->first)
                        <span class="text-muted">·</span>
                    @endif
                    <a href="{{ $item->resolved_url }}" target="{{ $item->target }}">{{ $item->label }}</a>
                @endforeach
            </div>
        @endif
    @endisset
</div>
@endisset
