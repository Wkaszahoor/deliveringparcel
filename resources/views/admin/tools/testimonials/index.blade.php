@extends('layouts.tailwind.app')

@section('title', 'Testimonials & Reviews')
@section('page_title', 'Testimonials & Review Platforms')
@section('page_subtitle', 'Manage review platform visibility (Google, Trustpilot, SiteJabber) and customer testimonials')

@section('content')
    {{-- Platform Visibility & Trust Badges Card --}}
    <x-admin.card class="mb-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-800"><i class="fas fa-sliders text-brand mr-2"></i>Review Platforms &amp; Trust Badges</h3>
                <p class="text-xs text-slate-500 mt-0.5">Control which review platforms show on the homepage and testimonials page, and customize aggregate ratings.</p>
            </div>
            @if ($googleSyncAvailable)
                <form method="POST" action="{{ route('admin.testimonials.sync-google') }}" onsubmit="return confirm('Pull the latest Google reviews in as new (unpublished) testimonials?')">
                    @csrf
                    <x-admin.button type="submit" variant="secondary" size="sm"><i class="fab fa-google text-blue-600"></i> Sync from Google</x-admin.button>
                </form>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.testimonials.platforms') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Google --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-sm text-slate-800">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs"><i class="fab fa-google"></i></span>
                                Google
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reviews_google_enabled" value="1" {{ ($platforms['reviews_google_enabled'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <div class="space-y-2.5 text-xs">
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Rating (0.0 - 5.0)</label>
                                <input type="text" name="reviews_google_rating" value="{{ old('reviews_google_rating', $platforms['reviews_google_rating'] ?? '') }}" placeholder="e.g. 4.9" class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Review Count</label>
                                <input type="number" name="reviews_google_count" value="{{ old('reviews_google_count', $platforms['reviews_google_count'] ?? 0) }}" min="0" class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Profile / Place URL</label>
                                <input type="url" name="reviews_google_url" value="{{ old('reviews_google_url', $platforms['reviews_google_url'] ?? '') }}" placeholder="https://..." class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/70 text-[11px] text-slate-500">
                        {{ $countsBySource['google']->total ?? 0 }} reviews stored ({{ $countsBySource['google']->published ?? 0 }} published)
                    </div>
                </div>

                {{-- Trustpilot --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-sm text-slate-800">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs"><i class="fas fa-star"></i></span>
                                Trustpilot
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reviews_trustpilot_enabled" value="1" {{ ($platforms['reviews_trustpilot_enabled'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        <div class="space-y-2.5 text-xs">
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Rating (0.0 - 5.0)</label>
                                <input type="text" name="reviews_trustpilot_rating" value="{{ old('reviews_trustpilot_rating', $platforms['reviews_trustpilot_rating'] ?? '') }}" placeholder="e.g. 3.7" class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Review Count</label>
                                <input type="number" name="reviews_trustpilot_count" value="{{ old('reviews_trustpilot_count', $platforms['reviews_trustpilot_count'] ?? 0) }}" min="0" class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Trustpilot Profile URL</label>
                                <input type="url" name="reviews_trustpilot_url" value="{{ old('reviews_trustpilot_url', $platforms['reviews_trustpilot_url'] ?? '') }}" placeholder="https://www.trustpilot.com/..." class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/70 text-[11px] text-slate-500">
                        {{ $countsBySource['trustpilot']->total ?? 0 }} reviews stored ({{ $countsBySource['trustpilot']->published ?? 0 }} published)
                    </div>
                </div>

                {{-- SiteJabber --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-sm text-slate-800">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-100 text-orange-700 text-xs"><i class="fas fa-star"></i></span>
                                SiteJabber
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reviews_sitejabber_enabled" value="1" {{ ($platforms['reviews_sitejabber_enabled'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-600"></div>
                            </label>
                        </div>
                        <div class="space-y-2.5 text-xs">
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Rating (0.0 - 5.0)</label>
                                <input type="text" name="reviews_sitejabber_rating" value="{{ old('reviews_sitejabber_rating', $platforms['reviews_sitejabber_rating'] ?? '') }}" placeholder="e.g. 3.9" class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">Review Count</label>
                                <input type="number" name="reviews_sitejabber_count" value="{{ old('reviews_sitejabber_count', $platforms['reviews_sitejabber_count'] ?? 0) }}" min="0" class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-slate-600 font-medium mb-1">SiteJabber Profile URL</label>
                                <input type="url" name="reviews_sitejabber_url" value="{{ old('reviews_sitejabber_url', $platforms['reviews_sitejabber_url'] ?? '') }}" placeholder="https://www.sitejabber.com/..." class="w-full rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs focus:ring-1 focus:ring-brand">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/70 text-[11px] text-slate-500">
                        {{ $countsBySource['sitejabber']->total ?? 0 }} reviews stored ({{ $countsBySource['sitejabber']->published ?? 0 }} published)
                    </div>
                </div>

                {{-- Manual / Direct --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-sm text-slate-800">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-slate-700 text-xs"><i class="fas fa-quote-left"></i></span>
                                Direct Quotes
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reviews_manual_enabled" value="1" {{ ($platforms['reviews_manual_enabled'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-700"></div>
                            </label>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Manual feedback entered by admins. Toggle whether direct customer quotes are eligible to be displayed on the public site.
                        </p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/70 text-[11px] text-slate-500">
                        {{ $countsBySource['manual']->total ?? 0 }} quotes stored ({{ $countsBySource['manual']->published ?? 0 }} published)
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <x-admin.button type="submit"><i class="fas fa-check"></i> Save Platform Settings</x-admin.button>
            </div>
        </form>
    </x-admin.card>

    {{-- Testimonial List Filters --}}
    <x-admin.card class="mb-4">
        <form id="tmFilters" class="flex flex-wrap items-end gap-3" onsubmit="return false;">
            <div>
                <label for="tmFilterQ" class="mb-1 block text-xs font-medium text-slate-600">Search</label>
                <input type="text" id="tmFilterQ" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm" placeholder="Name or quote...">
            </div>
            <div>
                <label for="tmFilterSource" class="mb-1 block text-xs font-medium text-slate-600">Source</label>
                <select id="tmFilterSource" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All Sources</option>
                    <option value="google">Google</option>
                    <option value="trustpilot">Trustpilot</option>
                    <option value="sitejabber">SiteJabber</option>
                    <option value="manual">Manual / Direct</option>
                </select>
            </div>
            <div>
                <label for="tmFilterPublished" class="mb-1 block text-xs font-medium text-slate-600">State</label>
                <select id="tmFilterPublished" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                    <option value="">All States</option>
                    <option value="1">Published (Visible)</option>
                    <option value="0">Hidden</option>
                </select>
            </div>
            <div class="ml-auto flex items-center gap-2">
                <x-admin.button tag="a" :href="route('admin.testimonials.create')"><i class="fas fa-plus"></i> New Testimonial</x-admin.button>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase text-slate-500">
                        <th class="py-2 pr-4">Avatar</th>
                        <th class="py-2 pr-4">Name / Source</th>
                        <th class="py-2 pr-4">Quote</th>
                        <th class="py-2 pr-4">Rating</th>
                        <th class="py-2 pr-4">Source</th>
                        <th class="py-2 pr-4">Published</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tmTbody" class="divide-y divide-slate-100">
                    <tr class="dp-loading-row"><td colspan="7" class="py-6 text-center text-slate-400"><i class="fas fa-spinner fa-spin"></i> Loading testimonials...</td></tr>
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endsection

@push('admin_scripts')
    <script src="{{ asset('dashbord/js/dp-lazy.js') }}"></script>
    <script>
        (function () {
            var dataUrl = @json(route('admin.testimonials.data'));
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function esc(v) {
                return String(v == null ? '' : v)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function hiddenForm(formId, url, method) {
                return '<form id="' + formId + '" method="POST" action="' + esc(url) + '" class="hidden">' +
                    '<input type="hidden" name="_token" value="' + esc(csrf) + '">' +
                    (method !== 'POST' ? '<input type="hidden" name="_method" value="' + method + '">' : '') +
                    '</form>';
            }

            var SOURCE_BADGES = {
                google: '<span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700"><i class="fab fa-google"></i> Google</span>',
                trustpilot: '<span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700"><i class="fas fa-star"></i> Trustpilot</span>',
                sitejabber: '<span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700"><i class="fas fa-star"></i> SiteJabber</span>',
                manual: '<span class="text-xs text-slate-400">Direct / Manual</span>'
            };
            function sourceBadge(source) {
                return SOURCE_BADGES[source] || SOURCE_BADGES.manual;
            }

            window.togglePublish = function(url, btn) {
                btn.disabled = true;
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).then(function(r) { return r.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    if (data.ok) {
                        btn.className = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium cursor-pointer transition-colors ' +
                            (data.is_published ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200');
                        btn.textContent = data.is_published ? 'Yes' : 'No';
                        btn.title = data.is_published ? 'Click to hide' : 'Click to publish';
                    }
                }).catch(function() {
                    btn.disabled = false;
                    alert('Failed to update status');
                });
            };

            var phRow = document.querySelector('#tmTbody tr.dp-loading-row');
            function render(row) {
                if (phRow && phRow.parentNode) { phRow.parentNode.removeChild(phRow); phRow = null; }
                var upId = 'tm-form-' + row.id + '-up';
                var downId = 'tm-form-' + row.id + '-down';
                var delId = 'tm-form-' + row.id + '-delete';

                var html = '<tr>';
                html += '<td class="py-2 pr-4">' + (row.avatar ? '<img src="' + esc(row.avatar) + '" class="h-10 w-10 rounded-full object-cover" alt="">' : '<span class="text-slate-300">&mdash;</span>') + '</td>';
                html += '<td class="py-2 pr-4"><strong>' + esc(row.name) + '</strong>' + (row.role ? '<br><small class="text-slate-500">' + esc(row.role) + '</small>' : '') + '</td>';
                html += '<td class="py-2 pr-4 max-w-sm">' + esc(row.content) + '</td>';
                html += '<td class="py-2 pr-4 text-amber-500 font-medium">' + esc(row.rating) + '</td>';
                html += '<td class="py-2 pr-4">' + sourceBadge(row.source) + '</td>';
                html += '<td class="py-2 pr-4">' +
                    '<button type="button" onclick="togglePublish(\'' + esc(row.urls.toggle_publish) + '\', this)" title="' + (row.published ? 'Click to hide' : 'Click to publish') + '" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium cursor-pointer transition-colors ' +
                    (row.published ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200') + '">' +
                    (row.published ? 'Yes' : 'No') + '</button>' +
                    '</td>';
                html += '<td class="py-2 pr-4 text-right whitespace-nowrap">';
                html += '<button type="button" title="Move up" onclick="document.getElementById(\'' + upId + '\').submit()" class="px-1 text-slate-400 hover:text-brand"><i class="fas fa-arrow-up"></i></button>';
                html += '<button type="button" title="Move down" onclick="document.getElementById(\'' + downId + '\').submit()" class="px-1 text-slate-400 hover:text-brand"><i class="fas fa-arrow-down"></i></button>';
                html += '<a href="' + esc(row.urls.edit) + '" title="Edit" class="px-1 text-slate-400 hover:text-brand"><i class="fas fa-pen"></i></a>';
                html += '<button type="button" title="Delete" onclick="if(confirm(\'Delete this testimonial? This cannot be undone.\')){document.getElementById(\'' + delId + '\').submit();}" class="px-1 text-slate-400 hover:text-red-600"><i class="fas fa-trash"></i></button>';
                html += hiddenForm(upId, row.urls.up, 'POST');
                html += hiddenForm(downId, row.urls.down, 'POST');
                html += hiddenForm(delId, row.urls.delete, 'DELETE');
                html += '</td></tr>';
                return html;
            }

            function filters() {
                return {
                    q: document.getElementById('tmFilterQ').value,
                    source: document.getElementById('tmFilterSource').value,
                    published: document.getElementById('tmFilterPublished').value
                };
            }

            function loadList() {
                if (typeof DP === 'undefined' || typeof DP.infiniteScroll !== 'function') { return; }
                var url = dataUrl + '?' + new URLSearchParams(filters()).toString();
                DP.infiniteScroll({ url: url, target: '#tmTbody', render: render });
            }

            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('tmFilterQ').addEventListener('keyup', function (e) {
                    if (e.key === 'Enter') { loadList(); }
                });
                document.getElementById('tmFilterSource').addEventListener('change', loadList);
                document.getElementById('tmFilterPublished').addEventListener('change', loadList);
                loadList();
            });
        })();
    </script>
@endpush
