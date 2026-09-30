@php
    $reviewBadges = collect([
        [
            'key' => 'google',
            'label' => 'Google',
            'enabled' => \App\Models\Setting::getBool('reviews_google_enabled', false),
            'rating' => \App\Models\Setting::get('reviews_google_rating'),
            'count' => (int) \App\Models\Setting::get('reviews_google_count', 0),
            'url' => \App\Models\Setting::get('reviews_google_url'),
            'classes' => 'border-blue-100 bg-blue-50 text-blue-700',
            'icon' => '<i class="fab fa-google"></i>',
        ],
        [
            'key' => 'trustpilot',
            'label' => 'Trustpilot',
            'enabled' => \App\Models\Setting::getBool('reviews_trustpilot_enabled', false),
            'rating' => \App\Models\Setting::get('reviews_trustpilot_rating'),
            'count' => (int) \App\Models\Setting::get('reviews_trustpilot_count', 0),
            'url' => \App\Models\Setting::get('reviews_trustpilot_url'),
            'classes' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
            'icon' => '<i class="fas fa-star"></i>',
        ],
        [
            'key' => 'sitejabber',
            'label' => 'SiteJabber',
            'enabled' => \App\Models\Setting::getBool('reviews_sitejabber_enabled', false),
            'rating' => \App\Models\Setting::get('reviews_sitejabber_rating'),
            'count' => (int) \App\Models\Setting::get('reviews_sitejabber_count', 0),
            'url' => \App\Models\Setting::get('reviews_sitejabber_url'),
            'classes' => 'border-orange-100 bg-orange-50 text-orange-700',
            'icon' => '<i class="fas fa-star"></i>',
        ],
    ])->filter(fn ($b) => $b['enabled'] && filled($b['rating']));
@endphp

@if ($reviewBadges->isNotEmpty())
    <div class="flex flex-wrap items-center justify-center gap-2">
        @foreach ($reviewBadges as $badge)
            @php
                $badgeInner = $badge['icon']
                    . '<span>' . e($badge['label']) . '</span>'
                    . '<span class="font-semibold">' . e($badge['rating']) . '<i class="fas fa-star ml-0.5 text-[0.7em]"></i></span>'
                    . ($badge['count'] > 0 ? '<span class="text-xs opacity-75 whitespace-nowrap">(' . number_format($badge['count']) . ' ' . __('reviews') . ')</span>' : '');
                $badgeClasses = 'inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border ' . $badge['classes'] . ' px-3 py-1.5 text-sm font-medium';
            @endphp
            @if ($badge['url'])
                <a href="{{ $badge['url'] }}" target="_blank" rel="noopener nofollow" class="{{ $badgeClasses }} hover:opacity-80">{!! $badgeInner !!}</a>
            @else
                <span class="{{ $badgeClasses }}">{!! $badgeInner !!}</span>
            @endif
        @endforeach
    </div>
@endif
