@extends('admin.layouts.app')

@section('title', 'Blog Posts (New)')
@section('page_title', 'Blog Posts (New)')
@section('page_subtitle', 'Posts from the WordPress import system — served publicly at /new/blog.')

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
@endif

<div class="row mb-2">
    <div class="col-6">
        {{-- stats cards --}}
        <span class="badge badge-secondary p-2 mr-1">Total: {{ $stats['total'] }}</span>
        <span class="badge badge-success p-2 mr-1">Published: {{ $stats['published'] }}</span>
        <span class="badge badge-warning p-2 mr-1">Draft: {{ $stats['draft'] }}</span>
        <span class="badge badge-danger p-2">Archived: {{ $stats['archived'] }}</span>
    </div>
    <div class="col-6 text-right">
        <a href="{{ route('admin.blog-import.index') }}" class="btn btn-outline-primary mr-1">
            <i class="fas fa-file-import mr-1"></i> Import Posts
        </a>
        <a href="{{ route('admin.blog-posts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> New Post
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.blog-posts.index') }}" class="form-inline">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control mr-2 mb-1" placeholder="Search title…">
            <select name="status" class="form-control mr-2 mb-1">
                <option value="">All statuses</option>
                @foreach(['published','draft','archived'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="text" name="batch" value="{{ request('batch') }}" class="form-control mr-2 mb-1" placeholder="Batch ID">
            <button type="submit" class="btn btn-outline-primary mb-1"><i class="fas fa-filter mr-1"></i> Filter</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.blog-posts.bulk') }}">
        @csrf
        <div class="card-body p-0">
            <div class="p-2 d-flex align-items-center">
                <select name="action" class="form-control-sm form-control mr-2" style="width:auto">
                    <option value="">Bulk action…</option>
                    <option value="publish">Publish</option>
                    <option value="draft">Move to draft</option>
                    <option value="archive">Archive</option>
                    <option value="delete">Delete (trash)</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Apply this bulk action to the selected posts?')">Apply</button>
            </div>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width:30px"><input type="checkbox" onclick="document.querySelectorAll('input[name=\'post_ids[]\']').forEach(function(c){c.checked=this.checked;}, this)"></th>
                        <th style="width:70px">Image</th>
                        <th>Title</th>
                        <th>Categories</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Published</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td><input type="checkbox" name="post_ids[]" value="{{ $post->id }}"></td>
                        <td>
                            @if($post->featured_image_src)
                            <img src="{{ $post->featured_image_src }}" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:4px">
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($post->title, 70) }}</strong>
                            <br><small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: ''), 80) }}</small>
                        </td>
                        <td>
                            @foreach($post->categories as $cat)
                            <span class="badge badge-light">{{ $cat->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge badge-{{ $post->status==='published' ? 'success' : ($post->status==='draft' ? 'warning' : 'danger') }}">{{ ucfirst($post->status) }}</span>
                        </td>
                        <td>{{ $post->view_count }}</td>
                        <td>{{ optional($post->published_at)->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('admin.blog-posts.edit', $post) }}" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('admin.blog-posts.destroy', $post) }}" class="d-inline" onsubmit="return confirm('Move this post to trash?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        No posts yet. <a href="{{ route('admin.blog-import.index') }}">Import from WordPress</a> or create one.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </form>

    @if($posts->hasPages())
    <div class="card-footer clearfix">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
