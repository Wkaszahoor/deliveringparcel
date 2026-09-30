{{-- Included from layouts/fmaster.blade.php (header, non-homepage pages) and
     from the homepage's top orders/utility bar — both reuse the same
     $dpLanguages/$dpCurrentLang set up in fmaster. Independent Alpine
     instances, same markup; pass ['dpLangVariant' => 'bar'] to render the
     compact white-on-dark trigger that fits the 36px utility bar instead of
     the default pill-on-header trigger. --}}
@php
    // Self-contained on purpose: included from fmaster.blade.php (header) and
    // from home.blade.php (the homepage's top orders/utility bar) — those are
    // separately-compiled Blade views, so $dpLanguages/$dpCurrentLang can't be
    // inherited from a parent layout's @php block and are computed here instead.
    $dpLangVariant = $dpLangVariant ?? 'header';
    $dpLanguages = [
        ['code' => 'en', 'label' => 'English', 'flag' => 'gb'],
        ['code' => 'es', 'label' => 'Español', 'flag' => 'es'],
        ['code' => 'fr', 'label' => 'Français', 'flag' => 'fr'],
        ['code' => 'de', 'label' => 'Deutsch', 'flag' => 'de'],
        ['code' => 'it', 'label' => 'Italiano', 'flag' => 'it'],
        ['code' => 'ar', 'label' => 'العربية', 'flag' => 'sa'],
        ['code' => 'zh', 'label' => '中文', 'flag' => 'cn'],
        ['code' => 'ja', 'label' => '日本語', 'flag' => 'jp'],
        ['code' => 'ko', 'label' => '한국어', 'flag' => 'kr'],
    ];
    $dpCurrentLang = collect($dpLanguages)->firstWhere('code', app()->getLocale()) ?? $dpLanguages[0];
@endphp
<div class="dp-lang-switcher relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" @click="open = !open"
            class="dp-lang-trigger flex items-center gap-1.5 {{ $dpLangVariant === 'bar' ? 'dp-lang-trigger-bar' : '' }}"
            :aria-expanded="open" aria-haspopup="listbox" aria-label="Choose language">
        <span class="flag-icon flag-icon-{{ $dpCurrentLang['flag'] }} rounded-xs shrink-0" style="width:1.2em;height:.9em"></span>
        @if ($dpLangVariant === 'bar')
            <span class="text-[11px] font-semibold uppercase tracking-wide">{{ $dpCurrentLang['code'] }}</span>
        @endif
        <i class="fas fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''" aria-hidden="true"></i>
    </button>
    <ul x-show="open" x-cloak x-transition
        class="dp-lang-menu absolute right-0 mt-2" role="listbox" aria-label="Languages">
        @foreach ($dpLanguages as $lang)
            <li>
                <a href="{{ route('lang.switch', $lang['code']) }}" role="option"
                   aria-current="{{ $lang['code'] === app()->getLocale() ? 'true' : 'false' }}"
                   class="dp-lang-option flex items-center gap-2.5 {{ $lang['code'] === app()->getLocale() ? 'is-active' : '' }}">
                    <span class="flag-icon flag-icon-{{ $lang['flag'] }} rounded-xs shrink-0" style="width:1.2em;height:.9em"></span>
                    <span>{{ $lang['label'] }}</span>
                    @if ($lang['code'] === app()->getLocale())
                        <i class="fas fa-check text-[10px] ml-auto" aria-hidden="true"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
