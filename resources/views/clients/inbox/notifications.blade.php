@extends('layouts.client_dashbord_master')
@section('head')
<title>My Notifications | Deliveringparcel</title>
@endsection
@section('content')
<section class="content order">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                @endif
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="card-title">My Notifications</h3>
                        <form action="{{ route('client.inbox.markAllRead') }}" method="POST" class="ml-auto"
                              onsubmit="return confirm('Mark ALL your notifications as read?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double mr-1"></i> Mark all as read</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('client.inbox.notifications') }}" class="form-row align-items-end mb-3">
                            <div class="form-group col-md-3 mb-2">
                                <label for="f-type">Type</label>
                                <select id="f-type" name="type" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="task" @if(($filters['type'] ?? '') === 'task') selected @endif>Order events</option>
                                    <option value="chat" @if(($filters['type'] ?? '') === 'chat') selected @endif>Chat messages</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <label for="f-order">Order #</label>
                                <input id="f-order" type="text" name="order" class="form-control form-control-sm"
                                       value="{{ $filters['order'] ?? '' }}" placeholder="e.g. 1018">
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                                <a href="{{ route('client.inbox.notifications') }}" class="btn btn-sm btn-default">Reset</a>
                            </div>
                        </form>

                        @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr style="background-color: #d0e7ff; color: #000;">
                                        <th>Type</th>
                                        <th>Order</th>
                                        <th>Message</th>
                                        <th>Received</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $n)
                                    @php
                                        $nOrder = $n->data['order_id'] ?? $n->data['id'] ?? null;
                                        $nOrderNumber = $n->data['order_number'] ?? $nOrder;
                                        $isTask = Str::contains($n->type, 'TaskNotification');
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($isTask)
                                            <span class="badge badge-info">Order event</span>
                                            @else
                                            <span class="badge badge-secondary">Chat</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($nOrder)
                                            <a href="{{ route('orders.show', $nOrder) }}">#{{ $nOrderNumber }}</a>
                                            @else
                                            —
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $n->data['greeting'] ?? '' }}</strong>
                                            @if(!empty($n->data['body']))
                                            <br><span class="text-muted">{{ \Illuminate\Support\Str::limit($n->data['body'], 120) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $n->created_at->format('d M Y H:i') }}</td>
                                        <td>
                                            @if($n->read_at)
                                            <span class="badge badge-light">Read {{ $n->read_at->format('d M H:i') }}</span>
                                            @else
                                            <span class="badge badge-warning">Unread</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $notifications->links('pagination::bootstrap-4') }}
                        </div>
                        @else
                        <p class="text-center text-lg text-muted">No notifications match these filters.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
