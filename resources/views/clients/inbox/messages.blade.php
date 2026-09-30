@extends('layouts.client_dashbord_master')
@section('head')
<title>My Messages | Deliveringparcel</title>
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
                    <div class="card-header">
                        <h3 class="card-title">My Messages <small class="text-muted">(conversations with our team)</small></h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('client.inbox.messages') }}" class="form-row align-items-end mb-3">
                            <div class="form-group col-md-3 mb-2">
                                <label for="f-order">Order #</label>
                                <input id="f-order" type="text" name="order" class="form-control form-control-sm"
                                       value="{{ $filters['order'] ?? '' }}" placeholder="e.g. 1018">
                            </div>
                            <div class="form-group col-md-3 mb-2">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                                <a href="{{ route('client.inbox.messages') }}" class="btn btn-sm btn-default">Reset</a>
                            </div>
                        </form>

                        @if($messages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr style="background-color: #d0e7ff; color: #000;">
                                        <th>Order</th>
                                        <th>Direction</th>
                                        <th>Message</th>
                                        <th>Sent</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $m)
                                    <tr>
                                        <td><a href="{{ route('orders.show', $m->order_id) }}">#{{ $m->order_number }}</a></td>
                                        <td>
                                            @if($m->from == auth()->id())
                                            <span class="badge badge-primary">You → Team</span>
                                            @else
                                            <span class="badge badge-success">Team → You</span>
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
                                            <button type="button" class="btn btn-sm btn-primary reply-btn"
                                                    data-order="{{ $m->order_id }}" data-number="{{ $m->order_number }}"
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
                        <p class="text-center text-lg text-muted">No messages yet — open one of your orders and use the chat bubble, or reply from here once the team messages you.</p>
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
            <form action="{{ route('client.inbox.reply') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Message us about order <span id="reply-order-number"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="reply-order-id" value="">
                    <div class="form-group">
                        <label for="reply-message">Message</label>
                        <textarea name="message" id="reply-message" class="form-control" rows="4" required maxlength="5000"
                                  placeholder="Type your message to our team…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Send message</button>
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
            $('#reply-message').val('');
        });
    });
</script>
@endsection
