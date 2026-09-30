<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Services\Cms\CmsMediaService;
use Illuminate\Http\Request;

class CmsMediaAdminController extends Controller
{
    // ── INDEX ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = CmsMedia::query()->orderByDesc('created_at');

        $filter = $request->get('filter', 'all');
        if ($filter === 'images') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($filter === 'documents') {
            $query->where('mime_type', 'not like', 'image/%');
        }

        $media = $query->paginate(20)->withQueryString();

        return view('admin.cms.media.index', compact('media', 'filter'));
    }

    // ── UPLOAD ─────────────────────────────────────────────────────────────
    public function upload(Request $request)
    {
        $request->validate([
            'file'   => 'required|array',
            'file.*' => 'required|file|max:10240',
        ]);

        $service = app(CmsMediaService::class);
        $stored  = [];
        $failed  = [];

        foreach ($request->file('file') as $file) {
            try {
                $media   = $service->store($file, auth()->id());
                $stored[] = [
                    'id'   => $media->id,
                    'url'  => $media->url,
                    'name' => $media->filename,
                ];
            } catch (\Throwable $e) {
                $failed[] = $file->getClientOriginalName();
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'     => count($stored) > 0,
                'stored' => $stored,
                'failed' => $failed,
                'count'  => count($stored),
            ]);
        }

        $msg = count($stored) . ' file(s) uploaded.'
            . (count($failed) ? ' Failed: ' . implode(', ', $failed) : '');
        return back()->with(count($stored) ? 'success' : 'error', $msg);
    }

    // ── UPDATE (alt/title/caption) ─────────────────────────────────────────
    public function update(Request $request, CmsMedia $media)
    {
        $data = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'title'    => 'nullable|string|max:255',
            'caption'  => 'nullable|string',
        ]);

        $media->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Media updated.');
    }

    // ── DELETE ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, CmsMedia $media)
    {
        app(CmsMediaService::class)->delete($media);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Media deleted.');
    }

    // ── BROWSE (modal picker) ──────────────────────────────────────────────
    public function browse(Request $request)
    {
        $media = CmsMedia::where('mime_type', 'like', 'image/%')
            ->orderByDesc('created_at')
            ->take(24)
            ->get(['id', 'url', 'filename', 'path', 'alt_text']);

        return response()->json(['ok' => true, 'media' => $media]);
    }
}
