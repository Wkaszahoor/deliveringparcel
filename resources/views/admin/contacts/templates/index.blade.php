@extends('admin.layouts.app')

@section('title', 'Reply templates')
@section('page_title', 'Reply Templates')
@section('page_subtitle', 'Reusable reply skeletons with {name} {email} {message} {company} placeholders')

@section('content')
    <div class="dp-toolbar d-flex align-items-center py-2 px-3 mb-3 bg-light rounded">
        <span class="text-muted">{{ $templates->count() }} template(s)</span>
        <a href="{{ route('admin.contacts.templates.create') }}" class="btn btn-primary btn-sm ml-auto"><i class="fas fa-plus mr-1"></i> New template</a>
    </div>

    <div class="dp-table-wrap">
        <table class="table table-hover dp-table dp-card-mobile">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Body preview</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $tpl)
                    <tr>
                        <td data-label="Name"><strong>{{ $tpl->name }}</strong></td>
                        <td data-label="Subject">{{ $tpl->subject }}</td>
                        <td data-label="Body preview"><small class="text-muted">{{ Illuminate\Support\Str::limit($tpl->body, 90) }}</small></td>
                        <td data-label="Status">
                            <span class="dp-badge badge badge-{{ $tpl->is_active ? 'success' : 'secondary' }}">{{ $tpl->is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td data-label="Updated"><small>{{ optional($tpl->updated_at)->format('M d, Y H:i') }}</small></td>
                        <td data-label="Actions" class="text-right">
                            <a href="{{ route('admin.contacts.templates.edit', $tpl->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-dp-confirm
                                    data-url="{{ route('admin.contacts.templates.destroy', $tpl->id) }}"
                                    data-method="DELETE"
                                    data-title="Delete template '{{ $tpl->name }}'?">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No templates yet — seed them or create your first.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('admin_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.DP && typeof DP.bindConfirms === 'function') { DP.bindConfirms(); }
        });
    </script>
@endpush
