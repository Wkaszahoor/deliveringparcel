@extends('admin.layouts.app')

@section('title', 'Message detail')
@section('page_title', 'Message from ' . e($contact->name)) {{-- @yield does not escape; customer name must (SE-003) --}}
@section('page_subtitle', 'Received ' . optional($contact->created_at)->format('M d, Y H:i'))

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-envelope mr-1"></i> Original message</h3>
                    <div class="card-tools">
                        <span class="dp-badge badge badge-{{ $contact->categoryColor() }}">{{ $contact->categoryLabel() }}</span>
                        <span class="dp-badge badge badge-{{ $contact->statusColor() }}">{{ $contact->statusLabel() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $contact->name }}</dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></dd>
                        @if ($contact->number)
                            <dt class="col-sm-3">Phone</dt>
                            <dd class="col-sm-9">{{ $contact->number }}</dd>
                        @endif
                        @if ($contact->address)
                            <dt class="col-sm-3">Address</dt>
                            <dd class="col-sm-9">{{ $contact->address }}</dd>
                        @endif
                        <dt class="col-sm-3">Received</dt>
                        <dd class="col-sm-9">{{ optional($contact->created_at)->format('M d, Y H:i') }}</dd>
                        @if ($contact->classified_at)
                            <dt class="col-sm-3">Classified</dt>
                            <dd class="col-sm-9">{{ optional($contact->classified_at)->format('M d, Y H:i') }}</dd>
                        @endif
                    </dl>
                    <hr>
                    <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">{{ $contact->detail }}</div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Back to messages</a>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($contact->reply_body)
                <div class="card card-outline card-success mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-reply mr-1"></i> Reply sent</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">
                            @if ($contact->replied_at)<small>{{ $contact->replied_at->format('M d, Y H:i') }}</small>@endif
                            @if ($contact->replier)<small> — by {{ $contact->replier->name }}</small>@endif
                        </p>
                        <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">{{ $contact->reply_body }}</div>
                    </div>
                </div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-paper-plane mr-1"></i> {{ $contact->reply_body ? 'Send another reply' : 'Reply to this message' }}</h3>
                </div>
                <div class="card-body">
                    <form id="dpReplyForm" method="POST" action="{{ route('admin.contacts.reply', $contact->id) }}">
                        @csrf
                        <div class="form-group">
                            <label for="dpTemplate">Start from a template</label>
                            <select id="dpTemplate" class="form-control">
                                <option value="">— Write from scratch —</option>
                                @foreach ($templates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Placeholders <code>{name} {email} {message} {company}</code> are filled in automatically.</small>
                        </div>
                        <div class="form-group">
                            <label for="body">Reply <span class="text-danger">*</span></label>
                            <textarea name="body" id="body" rows="10" class="form-control @error('body') is-invalid @enderror" placeholder="Type your reply...">{{ old('body', $contact->reply_body) }}</textarea>
                            @error('body')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Send reply</button>
                        <span class="text-muted small ml-2" id="dpMailHint"></span>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('admin_scripts')
    <script>
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var contactId = {{ (int) $contact->id }};

            document.getElementById('dpTemplate').addEventListener('change', function () {
                var id = this.value;
                if (!id) { return; }
                fetch('{{ route('admin.contacts.templates.index') }}/' + id + '?contact_id=' + contactId, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (tpl) {
                        var body = document.getElementById('body');
                        body.value = tpl.body || '';
                        var hint = document.getElementById('dpMailHint');
                        hint.textContent = tpl.subject ? 'Subject preview: ' + tpl.subject : '';
                    })
                    .catch(function () {
                        if (window.DP && DP.toast) { DP.toast.error('Could not load the template.'); }
                    });
            });
        })();
    </script>
@endpush
