{{-- Dropdown items for the navbar feed (also used by the scroll-loader).
    $items = collection of database notifications, $type = 'task'|'chat' --}}
@foreach($items as $notification)
@if($type === 'chat')
@php $nOrder = $notification->data['id'] ?? null; @endphp
<a href="{{ $nOrder ? route('order', $nOrder) : route('admin.inbox.messages') }}" class="dropdown-item s_notification" id="{{ $notification->id }}">
    <div class="media">
        <img src="{{ asset('dashbord/img/user1-128x128.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
        <div class="media-body">
            <h3 class="dropdown-item-title">{{ $notification->data['greeting'] ?? '' }}</h3>
            <p class="text-sm p mb-0">{{ \Illuminate\Support\Str::limit($notification->data['body'] ?? '', 80) }}</p>
            <p class="text-sm text-muted mb-0"><i class="far fa-clock mr-1"></i>{{ $notification->created_at->format('d M Y H:i') }}</p>
        </div>
    </div>
</a>
@else
@php $nOrder = $notification->data['order_id'] ?? null; @endphp
<a href="{{ $nOrder ? route('order', $nOrder) : route('admin.inbox.notifications') }}" class="dropdown-item s_notification {{ $notification->read_at ? '' : 'unread_notification' }}" id="{{ $notification->id }}">
    <div class="media">
        <i class="{{ $notification->read_at ? 'fas fa-envelope-open-text' : 'fas fa-envelope' }} mr-2 mt-1"></i>
        <p class="text-sm p mb-0">{{ $notification->data['greeting'] ?? '' }}</p>
        <span class="float-right text-muted text-sm ml-auto">{{ $notification->created_at->format('d M H:i') }}</span>
    </div>
</a>
@endif
<div class="dropdown-divider"></div>
@endforeach
