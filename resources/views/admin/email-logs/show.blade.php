@extends('admin.layouts.app')

@section('title', 'Email Log #' . $log->id)
@section('page_title', 'Email Log #' . $log->id)
@section('page_subtitle', $log->template_key . ' → ' . $log->to_email)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Delivery record</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th style="width:140px">Status</th><td><span class="badge badge-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }} p-1">{{ $log->status }}</span></td></tr>
                    <tr><th>Template key</th><td><code>{{ $log->template_key }}</code></td></tr>
                    <tr><th>Recipient</th><td>{{ $log->to_email }} {{ $log->to_name ? '(' . $log->to_name . ')' : '' }}</td></tr>
                    <tr><th>Order</th><td>{{ $log->order_id ?: '—' }}</td></tr>
                    <tr><th>Subject</th><td>{{ $log->subject }}</td></tr>
                    <tr><th>Attempts</th><td>{{ $log->attempts }}</td></tr>
                    <tr><th>Queued at</th><td>{{ $log->queued_at }}</td></tr>
                    <tr><th>Sent at</th><td>{{ $log->sent_at ?: '—' }}</td></tr>
                    <tr><th>Error</th><td class="text-danger" style="word-break:break-all">{{ $log->error ?: '—' }}</td></tr>
                </table>
                @if (in_array($log->status, ['failed', 'queued']))
                <form action="{{ route('admin.email-logs.retry', $log) }}" method="POST">
                    @csrf
                    <button class="btn btn-warning"><i class="fas fa-redo mr-1"></i> Retry delivery</button>
                </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">What now?</h3></div>
            <div class="card-body small">
                <p>Failed emails usually mean SMTP settings are wrong or the mail server was unreachable. Check <code>.env</code> MAIL_HOST (should be <code>business63.web-hosting.com</code> on this account) and run <code>php artisan config:clear</code> after changes.</p>
                <p>Retry re-reads the CURRENT template — fix the template first if the error mentions placeholders.</p>
            </div>
        </div>
    </div>
</div>
@endsection
