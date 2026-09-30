@extends('admin.layouts.app')
@section('title', 'Review #' . $review->id)
@section('page_title', 'Review #' . $review->id)
@section('page_subtitle', $review->title ?: \Illuminate\Support\Str::limit($review->body, 60))

@section('content')
<div class="row mb-3"><div class="col-12">
    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i> Back to reviews</a>
</div></div>

@php $meta = $statusMap[$review->status] ?? ['label' => $review->status, 'color' => 'bg-secondary', 'next' => []]; @endphp

<div class="row">
    <div class="col-12 col-lg-5 mb-3">
        <div class="card shadow-sm">
            <div class="card-header py-2"><i class="fas fa-info mr-1"></i> Details</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted" style="width:38%">Customer</th><td>{{ optional($review->user)->name ?: '—' }} <small class="text-muted">(#{{ $review->user_id }})</small></td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ optional($review->user)->email ?: '—' }}</td></tr>
                    <tr><th class="text-muted">Type</th><td>{{ $review->typeLabel() }}</td></tr>
                    <tr><th class="text-muted">Rating</th>
                        <td>@for ($i = 1; $i <= config('admin_reviews.rating_max'); $i++)
                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                        @endfor
                            <span class="ml-1">{{ (int) $review->rating }}/5</span></td></tr>
                    <tr><th class="text-muted">Order</th><td>{{ $review->order_id ? '#' . $review->order_id : '—' }}</td></tr>
                    @if ($review->order_item_id)<tr><th class="text-muted">Order item</th><td>#{{ $review->order_item_id }}</td></tr>@endif
                    @if ($review->product)<tr><th class="text-muted">Product</th><td>{{ $review->product->name }}</td></tr>@endif
                    @if ($review->service)<tr><th class="text-muted">Service</th><td>{{ $review->service->title }}</td></tr>@endif
                    <tr><th class="text-muted">Status</th><td><span class="dp-badge {{ $meta['color'] }} text-white">{{ $meta['label'] }}</span></td></tr>
                    <tr><th class="text-muted">Verified purchase</th>
                        <td>{!! $review->is_verified_purchase ? '<span class="badge badge-success"><i class="fas fa-certificate"></i> Yes (server-derived)</span>' : '<span class="badge badge-secondary">No</span>' !!}</td></tr>
                    <tr><th class="text-muted">Featured</th><td>{{ $review->is_featured ? 'Yes' : 'No' }}</td></tr>
                    <tr><th class="text-muted">Published</th><td>{{ $review->is_published ? 'Yes' : 'No (approval publishes)' }}</td></tr>
                    <tr><th class="text-muted">Created</th><td>{{ optional($review->created_at)->format('M d, Y H:i') }}</td></tr>
                    <tr><th class="text-muted">Approved by</th><td>{{ $review->approved_by ? ('#' . $review->approved_by . ' · ' . optional($review->approver)->name) : '—' }} {{ optional($review->approved_at)->format('(M d, Y H:i)') }}</td></tr>
                    <tr><th class="text-muted">Rejected by</th><td>{{ $review->rejected_by ? ('#' . $review->rejected_by . ' · ' . optional($review->rejecter)->name) : '—' }} {{ optional($review->rejected_at)->format('(M d, Y H:i)') }}</td></tr>
                </table>

                @if ($review->title)
                    <hr><h6 class="text-muted">Title</h6><p class="mb-0">{{ $review->title }}</p>
                @endif
                {{-- RV-008: review content ALWAYS escaped --}}
                <hr><h6 class="text-muted">Body</h6><p class="mb-0">{!! nl2br(e($review->body)) !!}</p>

                @if ($review->admin_notes)
                    <hr><h6 class="text-muted">Admin notes</h6><p class="mb-0">{!! nl2br(e($review->admin_notes)) !!}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        {{-- Moderation state machine (RV-003) --}}
        <div class="card shadow-sm">
            <div class="card-header py-2"><i class="fas fa-gavel mr-1"></i> Moderate</div>
            <div class="card-body">
                @if (empty($meta['next']))
                    <p class="text-muted mb-0">No transitions available from <strong>{{ $meta['label'] }}</strong>.</p>
                @else
                    <form method="POST" action="{{ route('admin.reviews.approve', $review->id) }}" id="rv-transition-form">
                        @csrf @method('PUT')
                        <input type="hidden" name="_rv_target" id="rv-target" value="{{ $meta['next'][0] }}">
                        <div class="form-group">
                            <label>Move to status</label>
                            <select name="status" id="rv-status" class="custom-select" onchange="rvRetarget(this.value)">
                                @foreach ($meta['next'] as $n)
                                    <option value="{{ $n }}">{{ $statusMap[$n]['label'] ?? $n }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Transitions constrained by config/admin_reviews.php state machine.</small>
                        </div>
                        <div class="form-group">
                            <label>Moderation note (stored in admin_notes)</label>
                            <textarea name="note" rows="2" class="form-control" maxlength="2000"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-arrow-right mr-1"></i> Apply</button>
                    </form>
                    <script>
                        function rvRetarget(status) {
                            var routeFor = @json([
                                'approved' => route('admin.reviews.approve', $review->id),
                                'rejected' => route('admin.reviews.reject', $review->id),
                                'hidden'   => route('admin.reviews.hide', $review->id),
                                'pending'  => route('admin.reviews.restore', $review->id),
                                'spam'     => route('admin.reviews.spam', $review->id),
                            ]);
                            var form = document.getElementById('rv-transition-form');
                            if (routeFor[status]) form.action = routeFor[status];
                        }
                    </script>
                @endif
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header py-2"><i class="fas fa-star mr-1"></i> Featured / delete</div>
            <div class="card-body d-flex flex-wrap">
                @if ($review->is_featured)
                    <form method="POST" action="{{ route('admin.reviews.unfeature', $review->id) }}">
                        @csrf @method('PUT')
                        <button class="btn btn-outline-warning"><i class="far fa-star mr-1"></i> Unfeature</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.reviews.feature', $review->id) }}">
                        @csrf @method('PUT')
                        <button class="btn btn-outline-warning"><i class="fas fa-star mr-1"></i> Feature</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" class="ml-2"
                      onsubmit="return confirm('Delete review #{{ $review->id }} permanently?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger"><i class="fas fa-trash mr-1"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
