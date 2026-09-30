<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogImportLog;
use App\Services\Blog\BlogImportService;
use Illuminate\Http\Request;

/**
 * Blog Import System (2026-09-02) — upload WXR/atom/RSS/sitemap files,
 * watch import results and manage import history logs.
 */
class BlogImportController extends Controller
{
    public function index()
    {
        $logs = BlogImportLog::latest()->paginate(20);
        return view('admin.blog.import', compact('logs'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            // NOTE: some .atom/.rss uploads report Content-Type application/xml
            // or text/xml; if the mimes rule is too strict locally the
            // extension fallback (.xml/.atom/.rss/.txt) still covers it.
            'import_file'    => 'required|file|mimes:xml,atom,txt,rss|max:51200',
            'default_status' => 'in:draft,published',
            'overwrite'      => 'boolean',
            'fetch_content'  => 'boolean',
        ]);

        $file     = $request->file('import_file');
        // getRealPath() can return false/'' (open_basedir or Windows tmp
        // resolution); getPathname() always carries PHP's tmp_name as-is.
        $tempPath = $file->getRealPath() ?: $file->getPathname();
        $origName = $file->getClientOriginalName();

        $options = [
            'default_status' => $request->input('default_status', 'draft'),
            'overwrite'      => $request->boolean('overwrite', false),
            'fetch_content'  => $request->boolean('fetch_content', true),
        ];

        $service = new BlogImportService();
        $log     = $service->importFromFile($tempPath, $origName, auth()->id(), $options);

        return redirect()
            ->route('admin.blog-import.index')
            ->with('import_result', [
                'batch_id' => $log->batch_id,
                'status'   => $log->status,
                'found'    => $log->total_found,
                'imported' => $log->total_imported,
                'skipped'  => $log->total_skipped,
                'failed'   => $log->total_failed,
            ]);
    }

    public function showLog(BlogImportLog $log)
    {
        return view('admin.blog.import-log', compact('log'));
    }

    public function deleteLog(BlogImportLog $log)
    {
        $log->delete();
        return back()->with('success', 'Import log deleted.');
    }
}
