@extends('admin.layouts.app')

@section('title', 'Dynamic Pages Manager')
@section('page_title', 'Dynamic Pages Manager')
@section('page_subtitle', 'Add / Edit / Toggle any page in the system')

@section('content')
<div class="container-fluid">

  @if(session('success'))
  <div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-triangle mr-2"></i>{{ session('error') }}
  </div>
  @endif
  @if($errors->any())
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    @foreach($errors->all() as $e)<p class="mb-0">{{ $e }}</p>@endforeach
  </div>
  @endif

  {{-- HOW TO ADD A NEW PAGE — GUIDE --}}
  <div class="card card-outline card-info mb-4">
    <div class="card-header">
      <h3 class="card-title"><i class="fa fa-info-circle mr-2"></i>How to Add a New Page (e.g. Careers)</h3>
      <div class="card-tools">
        <button class="btn btn-sm btn-light" onclick="document.getElementById('guide').classList.toggle('d-none')">
          Toggle Guide
        </button>
      </div>
    </div>
    <div class="card-body d-none" id="guide">
      <ol class="mb-0">
        <li><strong>Click "Add New Page"</strong> — fill Title, Slug (auto-generated), Section, Content (HTML).</li>
        <li><strong>Nav options</strong> — check "Show in Header", "Show in Footer", "Show in Main Nav" as needed.</li>
        <li><strong>Save</strong> — the page is instantly live at <code>/page/{slug}</code> and auto-added to Route Manager.</li>
        <li><strong>Route Manager</strong> — go to <a href="{{ route('admin.route-manager.index') }}">Admin → Route Manager</a> to customize the URL (e.g. change <code>/page/careers</code> to <code>/careers</code>).</li>
        <li><strong>Custom Logic</strong> — if the page needs its own controller (e.g. pulls job listings from DB), set "Controller Override" to the full class name.</li>
        <li><strong>SEO</strong> — fill Meta Title, Meta Description, Meta Keywords for each page.</li>
        <li><strong>Toggle Active</strong> — use the toggle button to hide/show without deleting.</li>
      </ol>
    </div>
  </div>

  {{-- TOP BAR --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 font-weight-bold">All Dynamic Pages</h5>
    <a href="{{ route('admin.dynamic-pages.create') }}" class="btn btn-primary">
      <i class="fa fa-plus mr-1"></i> Add New Page
    </a>
  </div>

  {{-- PAGES BY SECTION --}}
  @forelse($pages as $section => $sectionPages)
  <div class="card card-outline card-primary mb-4">
    <div class="card-header">
      <h3 class="card-title text-capitalize font-weight-bold">
        <i class="fa fa-folder mr-2 text-primary"></i>{{ ucfirst($section) }} Section
        <span class="badge badge-secondary ml-2">{{ $sectionPages->count() }}</span>
      </h3>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:30px">#</th>
              <th>Title</th>
              <th>Route Path</th>
              <th>Nav</th>
              <th>Status</th>
              <th>SEO</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sectionPages->sortBy('sort_order') as $page)
            <tr class="{{ $page->trashed() ? 'table-danger' : ($page->is_active ? '' : 'table-warning') }}"
                style="{{ $page->trashed() ? 'opacity:.55' : ($page->is_active ? '' : 'opacity:.8') }}">
              <td>{{ $page->sort_order }}</td>
              <td>
                @if($page->icon)<i class="fa {{ $page->icon }} mr-1 text-muted"></i>@endif
                <strong>{{ $page->title }}</strong>
                @if($page->is_locked)
                  <span class="badge badge-danger ml-1"><i class="fa fa-lock"></i> Locked</span>
                @endif
                @if($page->trashed())
                  <span class="badge badge-secondary ml-1">Deleted</span>
                @endif
              </td>
              <td>
                <code>{{ $page->route_path }}</code>
                <a href="{{ $page->url }}" target="_blank"
                   class="btn btn-sm btn-outline-secondary ml-1" title="Open">
                  <i class="fa fa-external-link-alt"></i>
                </a>
              </td>
              <td>
                @if($page->show_in_header)
                  <span class="badge badge-info mr-1" title="Header">H</span>
                @endif
                @if($page->show_in_main_nav)
                  <span class="badge badge-primary mr-1" title="Main Nav">N</span>
                @endif
                @if($page->show_in_footer)
                  <span class="badge badge-secondary mr-1" title="Footer">F</span>
                @endif
              </td>
              <td>
                @if($page->trashed())
                  <span class="badge badge-danger">Deleted</span>
                @elseif($page->is_active)
                  <span class="badge badge-success">Active</span>
                @else
                  <span class="badge badge-warning">Inactive</span>
                @endif
              </td>
              <td>
                @if($page->meta_title)
                  <i class="fa fa-check text-success" title="{{ $page->meta_title }}"></i>
                @else
                  <i class="fa fa-times text-muted"></i>
                @endif
              </td>
              <td class="text-right">
                @if($page->trashed())
                  <form action="{{ route('admin.dynamic-pages.restore', $page->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-success" title="Restore">
                      <i class="fa fa-undo"></i>
                    </button>
                  </form>
                  @if(!$page->is_locked)
                  <form action="{{ route('admin.dynamic-pages.force-delete', $page->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Permanently delete?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" title="Permanent Delete">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                  @endif
                @else
                  <a href="{{ route('admin.dynamic-pages.edit', $page) }}"
                     class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="fa fa-edit"></i>
                  </a>
                  <form action="{{ route('admin.dynamic-pages.toggle', $page) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm {{ $page->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                            title="{{ $page->is_active ? 'Deactivate' : 'Activate' }}">
                      <i class="fa {{ $page->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                    </button>
                  </form>
                  @if(!$page->is_locked)
                  <form action="{{ route('admin.dynamic-pages.destroy', $page) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this page?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                  @endif
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @empty
  <div class="alert alert-info">
    No pages yet. <a href="{{ route('admin.dynamic-pages.create') }}">Create your first page</a>.
  </div>
  @endforelse

</div>
@endsection
