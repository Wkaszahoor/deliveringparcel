@extends('admin.layouts.app')

@section('title', 'Queue Monitor')
@section('page_title', 'Queue Monitor')
@section('page_subtitle', 'Queue health + email throughput at a glance.')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Queue</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Driver</th><td><code>{{ $stats['queue_driver'] }}</code></td></tr>
                    <tr><th>jobs table</th><td>{{ $stats['jobs_table'] === 'ok' ? 'available' : 'MISSING (migration pending?)' }}</td></tr>
                    <tr><th>Pending jobs</th><td>{{ $stats['pending_jobs'] }}</td></tr>
                    <tr><th>Failed jobs</th><td>{{ $stats['failed_jobs'] }}</td></tr>
                    <tr><th>Email sending mode</th><td>{{ $stats['queued_sending'] ? 'QUEUED (emails queue, drained by cron)' : 'INLINE (sent during the request)' }}</td></tr>
                    <tr><th>Master switch</th><td>{{ $stats['master_enabled'] ? 'ON' : 'OFF — no emails are being sent' }}</td></tr>
                </table>
                <p class="small text-muted mb-0">Worker cron for cPanel (every minute):<br>
                <code>/opt/alt/php81/usr/bin/php /home/&lt;user&gt;/public_html/deliveringparcel/artisan schedule:run</code><br>
                or drain directly:<br>
                <code>/opt/alt/php81/usr/bin/php /home/&lt;user&gt;/public_html/deliveringparcel/artisan dp:queue-drain</code>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Emails</h3></div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Sent (all time)</th><td>{{ $stats['emails_sent'] }}</td></tr>
                    <tr><th>Sent today</th><td>{{ $stats['emails_sent_today'] }}</td></tr>
                    <tr><th>Failed</th><td>{{ $stats['emails_failed'] }}</td></tr>
                    <tr><th>Queued (waiting)</th><td>{{ $stats['emails_queued'] }}</td></tr>
                    <tr><th>Last sent</th><td>{{ $stats['last_sent_at'] ?: '—' }}</td></tr>
                    <tr><th>Last queued</th><td>{{ $stats['last_queued_at'] ?: '—' }}</td></tr>
                </table>
                <a href="{{ route('admin.email-logs.index') }}" class="btn btn-sm btn-primary">Open Email Logs</a>
            </div>
        </div>
    </div>
</div>
@endsection
