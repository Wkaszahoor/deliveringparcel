@extends('layouts.tailwind.app')

@section('title', 'Media Library')
@section('page_title', 'Media Library')
@section('page_subtitle', 'Upload and manage images and documents used across CMS content.')

@section('content')

{{-- Filter tabs --}}
<div class="mb-4 flex flex-wrap gap-2">
    @foreach (['all' => 'All', 'images' => 'Images', 'documents' => 'Documents'] as $key => $label)
    <a href="{{ route('admin.cms.media.index', ['filter' => $key]) }}"
       class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium {{ $filter === $key ? 'bg-brand text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- Upload dropzone --}}
<x-admin.card class="mb-4">
    <div class="cursor-pointer rounded-md border-2 border-dashed border-slate-300 py-6 text-center" id="cmsDropzone">
        <i class="fas fa-cloud-upload-alt mb-2 text-2xl text-brand"></i>
        <p class="mb-1 font-semibold text-slate-700">Drop files here or click to upload</p>
        <p class="mb-0 text-sm text-slate-500">Multiple files supported. Max 10 MB each.</p>
        <input type="file" id="cmsFileInput" multiple class="hidden">
    </div>
</x-admin.card>

{{-- Grid --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4" id="cmsMediaItems">
    @forelse ($media as $m)
    <x-admin.card class="flex h-full flex-col !p-0 overflow-hidden">
        <div class="flex h-[140px] items-center justify-center bg-slate-100">
            @if($m->isImage())
            <img src="{{ $m->url }}" alt="{{ $m->alt_text }}" class="max-h-[140px] max-w-full object-contain">
            @else
            <i class="fas fa-file-alt text-3xl text-slate-400"></i>
            @endif
        </div>
        <div class="flex-1 px-3 py-2">
            <div class="truncate text-sm" title="{{ $m->filename }}"><strong class="text-slate-900">{{ $m->filename }}</strong></div>
            <small class="text-xs text-slate-500">{{ $m->file_size_human }} · {{ optional($m->created_at)->format('d M Y') }}</small>
        </div>
        <div class="border-t border-slate-100 px-3 py-1.5 text-right">
            <button type="button" class="js-edit-media rounded-md border border-blue-200 px-2 py-1 text-xs font-medium text-blue-600 hover:bg-blue-50"
                    data-id="{{ $m->id }}" data-alt="{{ $m->alt_text }}" data-title="{{ $m->title }}"
                    data-caption="{{ $m->caption }}" data-url="{{ $m->url }}">
                <i class="fas fa-pen mr-1"></i>Edit
            </button>
            <button type="button" class="js-delete-media rounded-md border border-red-200 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50" data-url="{{ route('admin.cms.media.destroy', $m) }}">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </x-admin.card>
    @empty
    <div class="col-span-full py-6 text-center text-slate-400">No media uploaded yet.</div>
    @endforelse
</div>

<div class="mt-4">{{ $media->links() }}</div>
@endsection

{{-- Edit modal — plain JS-toggled overlay (no Bootstrap JS in the new layout). --}}
<div id="cmsMediaEditModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50" data-edit-modal-close></div>
    <div class="relative w-full max-w-md rounded-lg bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <h3 class="text-sm font-semibold text-slate-800">Edit Media</h3>
            <button type="button" class="text-slate-400 hover:text-slate-600" data-edit-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="p-4">
            <div class="mb-3 text-center">
                <img id="cmsEditPreview" src="" alt="" class="mx-auto hidden max-h-[180px] rounded-md border border-slate-200">
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">URL <button type="button" class="js-copy-url ml-1 rounded-md border border-slate-200 px-1.5 py-0.5 text-xs text-slate-600 hover:bg-slate-100"><i class="far fa-copy mr-1"></i>Copy</button></label>
                <input type="text" id="cmsEditUrl" class="block w-full rounded-md border border-slate-300 bg-slate-50 px-2.5 py-1.5 text-sm" readonly>
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Alt Text</label>
                <input type="text" id="cmsEditAlt" class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm">
            </div>
            <div class="mb-3">
                <label class="mb-1 block text-sm font-medium text-slate-700">Title</label>
                <input type="text" id="cmsEditTitle" class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm">
            </div>
            <div class="mb-0">
                <label class="mb-1 block text-sm font-medium text-slate-700">Caption</label>
                <textarea id="cmsEditCaption" rows="2" class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button type="button" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100" data-edit-modal-close>Close</button>
            <button type="button" class="js-save-media rounded-md bg-brand px-3 py-1.5 text-sm font-medium text-white hover:bg-brand-dark">Save changes</button>
        </div>
    </div>
</div>

@push('admin_scripts')
<script>
(function () {
    'use strict';
    var csrf = '{{ csrf_token() }}';
    var dropzone = document.getElementById('cmsDropzone');
    var fileInput = document.getElementById('cmsFileInput');

    // Click to pick files
    dropzone.addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () { uploadFiles(fileInput.files); });

    // Drag & drop
    ['dragenter', 'dragover'].forEach(function (ev) {
        dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.style.background = '#eef4ff'; });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
        dropzone.addEventListener(ev, function (e) { e.preventDefault(); dropzone.style.background = ''; });
    });
    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
    });

    function uploadFiles(fileList) {
        if (!fileList.length) return;
        var fd = new FormData();
        Array.prototype.forEach.call(fileList, function (f) { fd.append('file[]', f); });
        fd.append('_token', csrf);
        dropzone.innerHTML = '<p class="mb-0 py-3"><i class="fas fa-spinner fa-spin mr-2"></i>Uploading…</p>';
        fetch({{ json_encode(route('admin.cms.media.upload')) }}, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        }).then(function (r) { return r.json(); })
          .then(function (json) {
              if (json.ok) {
                  window.location.reload();
              } else {
                  alert('Upload failed.' + (json.failed && json.failed.length ? ' Failed: ' + json.failed.join(', ') : ''));
                  window.location.reload();
              }
          })
          .catch(function () { alert('Upload failed.'); window.location.reload(); });
    }

    // Edit modal
    var editId = null;
    var editModal = document.getElementById('cmsMediaEditModal');
    function openEditModal() { editModal.classList.remove('hidden'); editModal.classList.add('flex'); }
    function closeEditModal() { editModal.classList.add('hidden'); editModal.classList.remove('flex'); }
    document.querySelectorAll('[data-edit-modal-close]').forEach(function (el) { el.addEventListener('click', closeEditModal); });
    document.querySelectorAll('.js-edit-media').forEach(function (btn) {
        btn.addEventListener('click', function () {
            editId = btn.getAttribute('data-id');
            var url = btn.getAttribute('data-url');
            document.getElementById('cmsEditUrl').value = url;
            document.getElementById('cmsEditAlt').value = btn.getAttribute('data-alt') || '';
            document.getElementById('cmsEditTitle').value = btn.getAttribute('data-title') || '';
            document.getElementById('cmsEditCaption').value = btn.getAttribute('data-caption') || '';
            var preview = document.getElementById('cmsEditPreview');
            if (/\.(jpe?g|png|gif|webp|svg)$/i.test(url)) {
                preview.src = url;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }
            openEditModal();
        });
    });
    document.querySelector('.js-save-media').addEventListener('click', function () {
        if (!editId) return;
        var body = new URLSearchParams();
        body.set('alt_text', document.getElementById('cmsEditAlt').value);
        body.set('title', document.getElementById('cmsEditTitle').value);
        body.set('caption', document.getElementById('cmsEditCaption').value);
        body.set('_token', csrf);
        fetch({{ json_encode(url('admin/cms/media')) }} + '/' + editId, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        }).then(function (r) { return r.json(); })
          .then(function (json) {
              if (json.ok) { closeEditModal(); window.location.reload(); }
              else { alert('Save failed.'); }
          })
          .catch(function () { alert('Save failed.'); });
    });

    // Copy URL
    document.querySelector('.js-copy-url').addEventListener('click', function () {
        var el = document.getElementById('cmsEditUrl');
        el.select();
        try { document.execCommand('copy'); } catch (e) {}
        if (navigator.clipboard) navigator.clipboard.writeText(el.value);
    });

    // Delete
    document.querySelectorAll('.js-delete-media').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!window.confirm('Delete this file permanently?')) return;
            fetch(btn.getAttribute('data-url'), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).then(function () { window.location.reload(); });
        });
    });
})();
</script>
@endpush
