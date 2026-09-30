@extends('admin.layouts.app')

@section('title', 'Email Templates')
@section('page_title', 'Email Templates')
@section('page_subtitle', 'Admin-managed subject/body for every templated email. Disabling or deleting a template makes its flow fall back to the original built-in email — nothing breaks.')

@section('content')
<div class="row">
    <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
        <div>
            <span class="badge {{ \App\Services\EmailService::masterEnabled() ? 'badge-success' : 'badge-danger' }} p-1">
                Master switch: {{ \App\Services\EmailService::masterEnabled() ? 'ON' : 'OFF (no emails sent)' }}
            </span>
            <span class="badge {{ \App\Services\EmailService::useQueue() ? 'badge-info' : 'badge-secondary' }} p-1 ml-1">
                Sending: {{ \App\Services\EmailService::useQueue() ? 'queued (cron worker)' : 'inline' }}
            </span>
        </div>
        <a href="{{ route('admin.emails.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> New template</a>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Key</th><th>Name</th><th>Category</th><th>Subject</th>
                            <th style="width:110px">Enabled</th><th style="width:250px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $t)
                        <tr>
                            <td><code>{{ $t->key }}</code></td>
                            <td>{{ $t->name }}</td>
                            <td><span class="badge badge-light">{{ $t->category }}</span></td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($t->subject, 70) }}</td>
                            <td>
                                <form action="{{ route('admin.emails.toggle', $t) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-xs btn-{{ $t->is_enabled ? 'success' : 'secondary' }}">{{ $t->is_enabled ? 'On' : 'Off' }}</button>
                                </form>
                            </td>
                            <td>
                                <a href="{{ route('admin.emails.edit', $t) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                <form action="{{ route('admin.emails.test', $t) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button class="btn btn-xs btn-info"><i class="fas fa-paper-plane"></i> Test send</button>
                                </form>
                                <form action="{{ route('admin.emails.destroy', $t) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this template? The flow will use the built-in email again.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-muted p-3">No templates — every flow uses its built-in legacy email.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
