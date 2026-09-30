{{-- New shared top nav — dark bar, brand mark, top-level sections, user menu. --}}
<nav class="bg-slate-900 text-slate-200" x-data="{ mobileOpen: false }">
    {{-- Chat/notification bell dropdowns (present in the old dp-header.blade.php partial)
         are intentionally deferred to the Phase 1+ batch that migrates the admin.inbox
         module — that module's own views are still AdminLTE-styled, so wiring its feed
         partials into this new shell now would pull AdminLTE-era markup into the new
         layout prematurely. Not an oversight. --}}
    <div class="mx-auto flex h-14 max-w-[1600px] items-center gap-4 px-4">
        <a href="{{ route('admin-dashbord') }}" class="flex items-center gap-2 font-bold text-white">
            <img src="{{ asset('images/deliveringlogo.png') }}" alt="DP" class="h-6 w-auto">
            <span class="hidden sm:inline">DeliveringParcel</span>
        </a>

        <button
            class="ml-auto rounded p-2 text-slate-300 hover:bg-slate-800 sm:hidden"
            @click="mobileOpen = !mobileOpen"
            aria-label="Toggle navigation"
        >
            <i class="fas fa-bars"></i>
        </button>

        <div class="hidden flex-1 items-center gap-1 overflow-x-auto sm:flex">
            @foreach (\App\Support\AdminNav::sections() as $section)
                <a
                    href="{{ route($section['route']) }}"
                    class="whitespace-nowrap rounded px-3 py-2 text-sm font-medium {{ $activeSectionKey === $section['key'] ? 'bg-brand text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    <i class="fas {{ $section['icon'] }} mr-1.5"></i>{{ $section['label'] }}
                </a>
            @endforeach
        </div>

        <div class="ml-auto hidden items-center gap-3 sm:flex" x-data="{ userOpen: false }">
            <div class="relative">
                <button @click="userOpen = !userOpen" @click.outside="userOpen = false" :aria-expanded="userOpen" aria-haspopup="true" class="flex items-center gap-1.5 rounded px-2 py-1.5 text-sm text-slate-200 hover:bg-slate-800">
                    <i class="far fa-user"></i>
                    <span>{{ optional(auth()->user())->name ?? 'Account' }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="userOpen" x-transition x-cloak role="menu" class="absolute right-0 z-50 mt-2 w-44 rounded-md border border-slate-200 bg-white py-1 text-sm text-slate-700 shadow-lg">
                    <a href="{{ url('/') }}" class="block px-3 py-2 hover:bg-slate-50"><i class="fas fa-globe mr-2 text-slate-400"></i>Website</a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a href="{{ route('logout') }}" class="block px-3 py-2 hover:bg-slate-50"
                       onclick="event.preventDefault(); document.getElementById('dp2-logout').submit();">
                        <i class="fas fa-sign-out-alt mr-2 text-slate-400"></i>Logout
                    </a>
                    <form id="dp2-logout" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile section list --}}
    <div x-show="mobileOpen" x-cloak class="border-t border-slate-800 sm:hidden">
        @foreach (\App\Support\AdminNav::sections() as $section)
            <a
                href="{{ route($section['route']) }}"
                class="block px-4 py-2.5 text-sm {{ $activeSectionKey === $section['key'] ? 'bg-brand text-white' : 'text-slate-300' }}"
            >
                <i class="fas {{ $section['icon'] }} mr-2"></i>{{ $section['label'] }}
            </a>
        @endforeach
    </div>
</nav>
