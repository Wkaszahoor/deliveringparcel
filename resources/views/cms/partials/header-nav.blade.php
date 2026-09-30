{{-- CMS header navigation partial (spec SECTION 9).
     Renders the $cmsHeaderNav collection (NavMenuItem tree with children) injected by
     CmsServiceProvider's view composer. Renders NOTHING if the variable is missing.
     NOTE: markup is Bootstrap 5 (data-bs-toggle dropdowns) + FontAwesome icons —
     include Bootstrap 5 CSS/JS where this partial is used, or restyle to the host theme. --}}
@isset($cmsHeaderNav)
<ul class="navbar-nav ms-auto">
    @foreach ($cmsHeaderNav as $item)
        @if ($item->children->count())
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="{{ $item->resolved_url }}"
                   id="navDD{{ $item->id }}" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    @if ($item->icon)<i class="fa {{ $item->icon }} me-1"></i>@endif
                    {{ $item->label }}
                    @if ($item->show_badge)
                        <span class="badge {{ $item->badge_color ?? 'bg-primary' }} ms-1">
                            {{ $item->badge_text }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu" aria-labelledby="navDD{{ $item->id }}">
                    @foreach ($item->children as $child)
                        <li>
                            <a class="dropdown-item" href="{{ $child->resolved_url }}"
                               target="{{ $child->target }}">
                                @if ($child->icon)<i class="fa {{ $child->icon }} me-2 text-muted"></i>@endif
                                {{ $child->label }}
                                @if ($child->show_badge)
                                    <span class="badge {{ $child->badge_color ?? 'bg-secondary' }} ms-1">
                                        {{ $child->badge_text }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>
        @else
            <li class="nav-item {{ request()->is(ltrim($item->url, '/') . '*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ $item->resolved_url }}"
                   target="{{ $item->target }}">
                    @if ($item->icon)<i class="fa {{ $item->icon }} me-1"></i>@endif
                    {{ $item->label }}
                    @if ($item->show_badge)
                        <span class="badge {{ $item->badge_color ?? 'bg-primary' }} ms-1">
                            {{ $item->badge_text }}
                        </span>
                    @endif
                </a>
            </li>
        @endif
    @endforeach
</ul>
@endisset
