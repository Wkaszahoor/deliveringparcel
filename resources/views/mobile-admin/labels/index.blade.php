@extends('admin.layouts.app')

@section('title', 'Mobile App · Section Labels')
@section('page_title', 'Mobile App — Section Labels')
@section('page_subtitle', 'Rename the section titles shown across the mobile app screens. Values also sync to the live /api/mobile/labels endpoint the current app build reads.')

@section('content')
@include('mobile-admin.partials.subnav')

@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('mobile.admin.labels.update') }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Mobile App Section Labels</h3>
                    <div class="card-tools">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save mr-1"></i> Save All Labels</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:280px">Key</th>
                                <th>Description</th>
                                <th style="width:320px">Current Label</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($labels as $label)
                            <tr>
                                <td><code>{{ $label->label_key }}</code></td>
                                <td class="text-muted small">{{ $label->description }}</td>
                                <td>
                                    <input type="hidden" name="labels[{{ $loop->index }}][label_key]" value="{{ $label->label_key }}">
                                    <input type="text" class="form-control form-control-sm" name="labels[{{ $loop->index }}][label_value]" value="{{ $label->label_value }}" maxlength="255" required>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="#" onclick="event.preventDefault();if(confirm('Reset all labels to defaults?'))document.getElementById('resetForm').submit()" class="btn btn-outline-danger">
                        <i class="fas fa-undo mr-1"></i> Reset to Defaults
                    </a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save All Labels</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="resetForm" method="POST" action="{{ route('mobile.admin.labels.reset') }}">@csrf</form>
@endsection
