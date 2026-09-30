{{-- Slim contextual sub-sidebar for the active top-nav section. Hidden entirely when the
     active section has no sub-links (e.g. Dashboard). --}}
@if (!empty($activeSection['links']))
    <aside class="hidden w-56 shrink-0 border-r border-slate-200 bg-white md:block">
        <nav class="sticky top-14 p-3">
            <p class="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                {{ $activeSection['label'] }}
            </p>
            @foreach ($activeSection['links'] as $link)
                <a
                    href="{{ route($link['route']) }}"
                    data-active="{{ request()->routeIs($link['route']) ? 'true' : 'false' }}"
                    class="dp2-subnav-link mb-0.5 block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-50"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </aside>
@endif
