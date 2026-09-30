@extends('layouts.admin_dashbord_master')
@section('head')
<title>Messages Inbox | Deliveringparcel</title>
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
                        <h3 class="card-title">Messages Inbox <small class="text-muted">(client chat messages)</small></h3>
                        <form action="{{ route('admin.inbox.dedupe') }}" method="POST" class="ml-auto"
                              onsubmit="return confirm('Delete duplicate burst messages (same text sent repeatedly within 15 seconds)? Only the first of each burst is kept.');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-broom mr-1"></i> Clean up duplicates</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.inbox.messages') }}" class="form-row align-items-end mb-3">
                            <div class="form-group col-md-2 mb-2">
                                <label for="f-email">Client email</label>
                                <input id="f-email" type="text" name="email" class="form-control form-control-sm"
                                       value="{{ $filters['email'] ?? '' }}" placeholder="user@example.com">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label for="f-name">Client name</label>
                                <input id="f-name" type="text" name="name" class="form-control form-control-sm"
                                       value="{{ $filters['name'] ?? '' }}" placeholder="Name contains…">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label for="f-order">Order #</label>
                                <input id="f-order" type="text" name="order" class="form-control form-control-sm"
                                       value="{{ $filters['order'] ?? '' }}" placeholder="e.g. 1014">
                            </div>
                            <div class="form-group col-md-2 mb-2">
                                <label for="f-userid">Client ID</label>
                                <input id="f-userid" type="number" name="user_id" min="1" class="form-control form-control-sm"
                                       value="{{ $filters['user_id'] ?? '' }}" placeholder="e.g. 19132">
                            </div>
                            <div class="form-group col-md-4 mb-2 text-right">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                                <a href="{{ route('admin.inbox.messages') }}" class="btn btn-sm btn-default">Reset</a>
                            </div>
                        </form>

                        @if($messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr style="background-color: #d0e7ff; color: #000;">
                                        <th>Order</th>
                                        <th>Client</th>
                                        <th>Direction</th>
                                        <th>Message</th>
                                        <th>Sent</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $m)
                                    <tr>
                                        <td><a href="{{ route('order', $m->order_id) }}">#{{ $m->order_number }}</a></td>
                                        <td>{{ $m->client_name }} <span class="badge badge-light">ID #{{ $m->client_id }}</span><br><small class="text-muted">{{ $m->client_email }}</small></td>
                                        <td>
                                            @if($m->from == $m->client_id)
                                            <span class="badge badge-primary">Client → Admin</span>
                                            @else
                                            <span class="badge badge-success">Admin → Client</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ \Illuminate\Support\Str::limit($m->body, 120) }}
                                            @if($m->image)
                                            <br><a href="{{ asset('uploads/chatimages/'.$m->image) }}" target="_blank"><i class="fas fa-paperclip"></i> {{ $m->image }}</a>
                                            @endif
                                        </td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($m->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            @if($m->read)
                                            <span class="badge badge-light">Read</span>
                                            @else
                                            <span class="badge badge-warning">Unread</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary reply-btn"
                                                    data-order="{{ $m->order_id }}" data-number="{{ $m->order_number }}"
                                                    data-client="{{ $m->client_name }}"
                                                    data-toggle="modal" data-target="#replyModal">
                                                <i class="fas fa-reply"></i> Reply
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $messages->links('pagination::bootstrap-4') }}
                        </div>
                        @else
                        <p class="text-center text-lg text-muted">No messages match these filters.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reply modal -->
<div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.inbox.reply') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Reply on order <span id="reply-order-number"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="reply-order-id" value="">
                    <p class="text-muted mb-2">To: <span id="reply-client-name"></span></p>
                    <div class="form-group">
                        <label for="reply-message">Message</label>
                        <textarea name="message" id="reply-message" class="form-control" rows="4" required maxlength="5000"
                                  placeholder="Type your reply to the client…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Send reply</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('.reply-btn').on('click', function() {
            $('#reply-order-id').val($(this).data('order'));
            $('#reply-order-number').text('#' + $(this).data('number'));
            $('#reply-client-name').text($(this).data('client'));
            $('#reply-message').val('');
        });
    });
</script>
@endsection
