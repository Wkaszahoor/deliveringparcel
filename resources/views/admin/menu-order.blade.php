@extends('admin.layouts.app')

@section('title', 'Menu Order')
@section('page_title', 'Menu Order')
@section('page_subtitle', 'Drag to reorder every bar — serial # is the live position in that bar')

@section('content')
<ul class="nav nav-tabs mb-3" id="dpBarTabs" role="tablist">
    @foreach ($bars as $i => $bar)
        <li class="nav-item">
            <a class="nav-link {{ $i === 0 ? 'active' : '' }}" id="tab-{{ $bar['key'] }}" data-toggle="tab" href="#pane-{{ $bar['key'] }}" role="tab">
                {{ $bar['title'] }}
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    @foreach ($bars as $bar)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $bar['key'] }}" role="tabpanel">
            <div class="row">
                <div class="col-lg-7">
                    <div class="card card-primary">
                        <div class="card-header"><h3 class="card-title">{{ $bar['title'] }} — drag &amp; drop</h3></div>
                        <div class="card-body">
                            @if (count($bar['items']) === 0)
                                <p class="text-muted mb-2">No items in this bar yet — add them in CMS → Menus.</p>
                            @else
                                <ol class="dp-menu-list list-unstyled mb-0" data-bar="{{ $bar['key'] }}">
                                    @foreach ($bar['items'] as $e)
                                        <li class="dp-menu-item" data-key="{{ $e['key'] }}" draggable="true">
                                            <span class="dp-menu-pos">{{ $e['pos'] }}</span>
                                            <i class="fas fa-grip-vertical text-muted mr-2"></i>
                                            <span class="dp-menu-label">{{ $e['label'] }}</span>
                                            <small class="text-muted ml-2">#{{ $e['key'] }}</small>
                                        </li>
                                    @endforeach
                                </ol>
                                <button type="button" class="btn btn-primary mt-3 dp-menu-save" data-bar="{{ $bar['key'] }}">
                                    <i class="fas fa-save"></i> Save {{ $bar['title'] }} order
                                </button>
                                <span class="text-muted ml-2 dp-menu-msg"></span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card card-default">
                        <div class="card-header"><h3 class="card-title">How it works</h3></div>
                        <div class="card-body text-sm">
                            <p class="mb-2">{{ $bar['hint'] }}.</p>
                            <p class="mb-2">Grab a row by the <b>grip handle</b> and drop it — the <b>serial numbers renumber instantly</b>.</p>
                            <p class="mb-0">Click <b>Save</b> and the live bar reorders to match.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('admin_styles')
<style>
.dp-menu-list { counter-reset: dporder; }
.dp-menu-item {
    display: flex; align-items: center; padding: .55rem .75rem; margin-bottom: .4rem;
    background: #fff; border: 1px solid #e3e6ea; border-radius: .45rem; cursor: grab;
    transition: box-shadow .15s ease, opacity .15s ease; user-select: none;
}
.dp-menu-item:hover { box-shadow: 0 2px 10px rgba(0,0,0,.08); }
.dp-menu-item.dp-dragging { opacity: .45; box-shadow: 0 6px 18px rgba(0,0,0,.18); }
.dp-menu-pos {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px; margin-right: .6rem; border-radius: 50%;
    background: #0b5fff; color: #fff; font-weight: 700; font-size: .8rem;
}
</style>
@endpush

@push('admin_scripts')
<script>
(function () {
    var CSRF = '{{ csrf_token() }}';

    function bindList(list) {
        var dragEl = null;

        function renumber() {
            list.querySelectorAll('.dp-menu-item').forEach(function (li, i) {
                li.querySelector('.dp-menu-pos').textContent = i + 1;
            });
        }

        list.addEventListener('dragstart', function (e) {
            dragEl = e.target.closest('.dp-menu-item');
            if (dragEl) dragEl.classList.add('dp-dragging');
        });
        list.addEventListener('dragend', function () {
            if (dragEl) dragEl.classList.remove('dp-dragging');
            list.querySelectorAll('.dp-over').forEach(function (li) { li.classList.remove('dp-over'); });
            dragEl = null;
            renumber();
        });
        list.addEventListener('dragover', function (e) {
            e.preventDefault();
            var over = e.target.closest('.dp-menu-item');
            if (!over || over === dragEl || !dragEl) return;
            list.querySelectorAll('.dp-over').forEach(function (li) { li.classList.remove('dp-over'); });
            over.classList.add('dp-over');
            var rect = over.getBoundingClientRect();
            var after = (e.clientY - rect.top) > rect.height / 2;
            list.insertBefore(dragEl, after ? over.nextSibling : over);
            renumber();
        });
    }

    document.querySelectorAll('.dp-menu-list').forEach(bindList);

    document.querySelectorAll('.dp-menu-save').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var bar = btn.getAttribute('data-bar');
            var list = document.querySelector('.dp-menu-list[data-bar="' + bar + '"]');
            if (!list) return;
            var order = [];
            list.querySelectorAll('.dp-menu-item').forEach(function (li) { order.push(li.getAttribute('data-key')); });
            var fd = new FormData();
            fd.append('_token', CSRF);
            fd.append('bar', bar);
            order.forEach(function (k) { fd.append('order[]', k); });
            btn.disabled = true;
            fetch('{{ route('admin.menu-order.save') }}', {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); }).then(function (resp) {
                btn.disabled = false;
                var msg = btn.nextElementSibling;
                if (msg) msg.textContent = resp.ok ? 'Saved — live bar updated.' : 'Save failed.';
            }).catch(function () {
                btn.disabled = false;
                var msg = btn.nextElementSibling;
                if (msg) msg.textContent = 'Save failed (network).';
            });
        });
    });
})();
</script>
@endpush
