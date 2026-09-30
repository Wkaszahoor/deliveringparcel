{{--
    Review widget (RV-006) — reusable, config-driven.
    Usage: <x-review-widget :widget="['status'=>'approved','rating_min'=>4,'verified'=>1,'limit'=>6,'sort'=>'newest','layout'=>'grid','title'=>'What customers say','show_body'=>true,'show_rating'=>true,'show_name'=>true,'show_date'=>true,'show_verified'=>true]" />
    Security: reviews are ALWAYS forced to status=approved; content escaped.
--}}
@php
    $w = $widget ?? [];
    $layout = $w['layout'] ?? 'grid';
    $limit  = min(24, max(1, (int) ($w['limit'] ?? 6)));
    $sort   = $w['sort'] ?? 'newest';
    $title  = $w['title'] ?? 'Customer reviews';

    $query = \App\Models\Review::query()->where('status', 'approved');
    if (!empty($w['rating_min'])) { $query->where('rating', '>=', (int) $w['rating_min']); }
    if (!empty($w['rating_max'])) { $query->where('rating', '<=', (int) $w['rating_max']); }
    if (!empty($w['verified']))   { $query->where('is_verified_purchase', true); }
    if (!empty($w['featured']))   { $query->where('is_featured', true); }
    if (!empty($w['type']))       { $query->where('review_type', $w['type']); }

    $query->with('user:id,name');
    switch ($sort) {
        case 'oldest':       $query->orderBy('created_at'); break;
        case 'rating_high':  $query->orderByDesc('rating')->orderByDesc('created_at'); break;
        case 'rating_low':   $query->orderBy('rating')->orderByDesc('created_at'); break;
        case 'random':       $query->inRandomOrder(); break;
        case 'featured':     $query->orderByDesc('is_featured')->orderByDesc('created_at'); break;
        default:             $query->orderByDesc('created_at');
    }
    $reviews = $query->limit($limit)->get();
    $showRating = $w['show_rating'] ?? true;
    $showBody   = $w['show_body'] ?? true;
    $showName   = $w['show_name'] ?? true;
    $showDate   = $w['show_date'] ?? true;
    $showVerif  = $w['show_verified'] ?? true;
@endphp
@if ($reviews->isNotEmpty())
<div class="h2-review-widget" data-layout="{{ $layout }}">
    @if (!empty($title))<h3 class="mb-3">{{ $title }}</h3>@endif
    <div class="{{ $layout === 'grid' ? 'h2-grid' : ($layout === 'cards' ? 'h2-grid' : '') }}">
        @foreach ($reviews as $r)
            <div class="h2-card">
                <div class="h2-card-body">
                    @if ($showRating)<div class="text-warning mb-1">{{ str_repeat('★', (int) $r->rating) . str_repeat('☆', 5 - (int) $r->rating) }}</div>@endif
                    @if ($r->title && ($w['show_title'] ?? true))<strong>{{ $r->title }}</strong>@endif
                    @if ($showBody && $r->body)<p class="muted mb-2" style="font-size:.92rem">{{ \Str::limit(strip_tags($r->body), 220) }}</p>@endif
                    <div class="d-flex justify-content-between align-items-center" style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap">
                        <span class="muted" style="font-size:.85rem">
                            @if ($showName){{ optional($r->user)->name ?: 'Customer' }}@endif
                            @if ($showVerif && $r->is_verified_purchase)<span class="h2-badge" style="margin-left:.4rem">✓ Verified purchase</span>@endif
                        </span>
                        @if ($showDate)<small class="muted">{{ optional($r->created_at)->format('M Y') }}</small>@endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
