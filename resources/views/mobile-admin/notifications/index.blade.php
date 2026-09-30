@extends('admin.layouts.app')

@section('title', 'Mobile App · Notifications')
@section('page_title', 'Mobile App — Notifications')
@section('page_subtitle', 'Send in-app notifications to mobile users (all, a role, one user, or an order’s client), reuse templates, and review delivery history.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link {{ $tab === 'send' ? 'active' : '' }}" data-toggle="tab" href="#tab-send"><i class="fas fa-paper-plane mr-1"></i> Send</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'history' ? 'active' : '' }}" data-toggle="tab" href="#tab-history"><i class="fas fa-history mr-1"></i> History</a></li>
    <li class="nav-item"><a class="nav-link {{ $tab === 'templates' ? 'active' : '' }}" data-toggle="tab" href="#tab-templates"><i class="fas fa-folder mr-1"></i> Templates</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane {{ $tab === 'send' ? 'active' : '' }}" id="tab-send">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-paper-plane mr-1"></i> New notification</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('mobile.admin.notifications.send') }}" id="sendForm">
                            @csrf
                            <div class="form-group">
                                <label>Send to</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="tt_all" value="all" checked onchange="tgtChange()">
                                        <label class="form-check-label" for="tt_all">All Users</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="tt_role" value="role" onchange="tgtChange()">
                                        <label class="form-check-label" for="tt_role">By Role</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="tt_user" value="user" onchange="tgtChange()">
                                        <label class="form-check-label" for="tt_user">Specific User</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="tt_order" value="order" onchange="tgtChange()">
                                        <label class="form-check-label" for="tt_order">Order Related</label>
                                    </div>
                                </div>
                                <select class="form-control mb-1" name="target_role" id="tgt_role" style="display:none">
                                    <option value="client">All clients</option>
                                    <option value="admin">All admins</option>
                                </select>
                                <input type="number" class="form-control mb-1" name="target_user" id="tgt_user" placeholder="User ID (from Clients page)" style="display:none">
                                <input type="text" class="form-control" name="target_order" id="tgt_order" placeholder="Order number (e.g. DP-1024)" style="display:none">
                            </div>

                            <div class="form-group">
                                <label>Type</label>
                                <div>
                                    @foreach (['order_update' => 'Order Update', 'message' => 'New Message', 'announcement' => 'Announcement', 'alert' => 'Alert', 'promotional' => 'Promotional'] as $val => $lbl)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="type" id="ty_{{ $val }}" value="{{ $val }}" {{ $val === 'announcement' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ty_{{ $val }}">{{ $lbl }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Title <small class="text-muted" id="titleCount">0/200</small></label>
                                <input type="text" class="form-control" name="title" maxlength="200" required oninput="document.getElementById('titleCount').textContent=this.value.length+'/200'">
                            </div>
                            <div class="form-group">
                                <label>Message <small class="text-muted" id="bodyCount">0/1000</small></label>
                                <textarea class="form-control" name="body" rows="4" maxlength="1000" required oninput="document.getElementById('bodyCount').textContent=this.value.length+'/1000'"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Schedule</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="schedule" id="sc_now" value="now" checked onchange="document.getElementById('sc_at').style.display='none'">
                                        <label class="form-check-label" for="sc_now">Send Now</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="schedule" id="sc_later" value="later" onchange="document.getElementById('sc_at').style.display='block'">
                                        <label class="form-check-label" for="sc_later">Schedule For</label>
                                    </div>
                                </div>
                                <input type="datetime-local" class="form-control" name="scheduled_at" id="sc_at" style="display:none">
                                <small class="text-muted">Scheduled sends go out the next time this page (or the History tab) is opened after their time.</small>
                            </div>

                            <div class="form-group">
                                <label>Start from template</label>
                                <select class="form-control" id="tplPicker">
                                    <option value="">— Select template… —</option>
                                    @foreach ($templates as $tpl)
                                    <option value="{{ $tpl->id }}" data-title="{{ htmlspecialchars($tpl->title) }}" data-body="{{ htmlspecialchars($tpl->body) }}" data-type="{{ $tpl->notification_type }}">{{ $tpl->template_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane mr-1"></i> Send Notification</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-save mr-1"></i> Save current form as template</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('mobile.admin.notifications.templates.save') }}" id="tplForm">
                            @csrf
                            <div class="form-group">
                                <label>Template name</label>
                                <input type="text" class="form-control" name="template_name" maxlength="100" required>
                            </div>
                            <input type="hidden" name="notification_type" id="tpl_type" value="announcement">
                            <input type="hidden" name="title" id="tpl_title">
                            <input type="hidden" name="body" id="tpl_body">
                            <button type="submit" class="btn btn-outline-primary btn-block"><i class="fas fa-save mr-1"></i> Save as Template</button>
                        </form>
                        <p class="text-muted small mt-2">Fills from the Send form — type your title &amp; message first, then save.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $tab === 'history' ? 'active' : '' }}" id="tab-history">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> Delivery history</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Sent To</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th style="width:90px">Recipients</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                @if ($log->target_type === 'all') All users
                                @elseif ($log->target_type === 'role') Role: {{ ucfirst($log->target_value) }}
                                @elseif ($log->target_type === 'user') User #{{ $log->target_value }}
                                @else Order: {{ $log->target_value }}
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($log->notification_type)) }}</span></td>
                            <td>{{ $log->title }}</td>
                            <td>
                                <span class="badge badge-{{ $log->status === 'sent' ? 'success' : ($log->status === 'pending' ? 'warning' : 'danger') }}">{{ $log->status }}</span>
                                @if ($log->status === 'pending' && $log->scheduled_at)
                                <small class="text-muted d-block">for {{ $log->scheduled_at->format('d M H:i') }}</small>
                                @endif
                            </td>
                            <td>{{ $log->recipient_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Nothing sent yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $history->links() }}</div>
        </div>
    </div>

    <div class="tab-pane {{ $tab === 'templates' ? 'active' : '' }}" id="tab-templates">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-folder mr-1"></i> Templates</h3></div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th style="width:200px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($templates as $tpl)
                        <tr>
                            <td><strong>{{ $tpl->template_name }}</strong></td>
                            <td><span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($tpl->notification_type)) }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($tpl->title, 50) }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="useTemplate('{{ addslashes($tpl->title) }}', {{ json_encode($tpl->body) }}, '{{ $tpl->notification_type }}')">
                                    <i class="fas fa-fill"></i> Use
                                </button>
                                <form method="POST" action="{{ route('mobile.admin.notifications.templates.delete', $tpl->id) }}" class="d-inline" onsubmit="return confirm('Delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No templates saved yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('admin_scripts')
<script>
function tgtChange() {
    var t = document.querySelector('input[name="target_type"]:checked').value;
    document.getElementById('tgt_role').style.display  = t === 'role' ? 'block' : 'none';
    document.getElementById('tgt_user').style.display  = t === 'user' ? 'block' : 'none';
    document.getElementById('tgt_order').style.display = t === 'order' ? 'block' : 'none';
}

document.getElementById('tplForm').addEventListener('submit', function () {
    document.getElementById('tpl_title').value = document.querySelector('#sendForm [name=title]').value;
    document.getElementById('tpl_body').value  = document.querySelector('#sendForm [name=body]').value;
    var ty = document.querySelector('#sendForm input[name="type"]:checked');
    document.getElementById('tpl_type').value = ty ? ty.value : 'announcement';
});

document.getElementById('tplPicker').addEventListener('change', function () {
    var opt = this.selectedOptions[0];
    if (!opt || !opt.dataset.title) return;
    useTemplate(opt.dataset.title, opt.dataset.body, opt.dataset.type);
});

function useTemplate(title, body, type) {
    document.querySelector('#sendForm [name=title]').value = title;
    document.querySelector('#sendForm [name=body]').value  = body;
    var radio = document.querySelector('#sendForm input[name="type"][value="' + type + '"]');
    if (radio) radio.checked = true;
    document.querySelector('a[href="#tab-send"]').click();
}
</script>
@endpush
@endsection
