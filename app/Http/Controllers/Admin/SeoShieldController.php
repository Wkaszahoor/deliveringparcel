<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPost;
use App\Models\SeoAnalysis;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Admin → SEO Shield (2026-09-21).
 * Infrastructure for the content health analyzer: KPIs + run tool
 * (existing post / URL / pasted content), persisted via seo_analyses.
 * The analyzer brain itself lives in App\Services\Seo\SeoAnalyzerService.
 */
class SeoShieldController extends Controller
{
    public function index()
    {
        $avg = SeoAnalysis::query()->avg('overall_score');

        $stats = [
            'total'     => SeoAnalysis::query()->count(),
            'avg_score' => $avg === null ? null : round((float) $avg),
            'recent'    => SeoAnalysis::query()
                ->latest('created_at')
                ->limit(15)
                ->get(['id', 'source_type', 'subject_title', 'source_url', 'overall_score', 'grade', 'created_at']),
        ];

        $posts = CmsPost::query()
            ->select('id', 'title', 'post_type', 'slug', 'status')
            ->orderByDesc('updated_at')
            ->limit(200)
            ->get();

        return view('admin.seo-shield.index', compact('stats', 'posts'));
    }

    public function analyze(Request $request)
    {
        $service = app(\App\Services\Seo\SeoAnalyzerService::class);

        $mode = null;
        $fetch = null;   // only set for url mode
        $report = [];
        $subjectId = null;

        try {
            $request->validate(['mode' => ['required', 'in:post,url,content']]);
            $mode = $request->input('mode');

            if ($mode === 'post') {
                $request->validate(['post_id' => ['required', 'integer', 'exists:cms_posts,id']]);
                $subjectId = (int) $request->input('post_id');
                $report = $service->analyzePost(CmsPost::findOrFail($subjectId));
            } elseif ($mode === 'url') {
                $request->validate(['url' => ['required', 'url']]);
                $fetch = $service->fetchUrl($request->input('url'));
                if (empty($fetch['ok'])) {
                    return response()->json([
                        'ok'    => false,
                        'error' => $fetch['error'] ?? 'Could not fetch the URL.',
                    ], 422);
                }
                $final = $fetch['final_url'] ?? $request->input('url');
                $report = $service->analyzeHtml([
                    'title'            => null,
                    'meta_title'       => null,
                    'meta_description' => null,
                    'slug'             => basename((string) parse_url($final, PHP_URL_PATH)),
                    'content_html'     => $fetch['html'] ?? '',
                    'topic'            => null,
                    'fetched'          => $fetch,
                ]);
            } else {
                $request->validate([
                    'title'            => ['required', 'string', 'max:255'],
                    'content'          => ['required', 'string'],
                    'meta_title'       => ['nullable', 'string'],
                    'meta_description' => ['nullable', 'string'],
                    'slug'             => ['nullable', 'string', 'max:255'],
                    'topic'            => ['nullable', 'string', 'max:255'],
                ]);
                $report = $service->analyzeHtml([
                    'title'            => $request->input('title'),
                    'meta_title'       => $request->input('meta_title'),
                    'meta_description' => $request->input('meta_description'),
                    'slug'             => $request->input('slug'),
                    'content'          => $request->input('content'),
                    'topic'            => $request->input('topic'),
                ]);
            }
        } catch (ValidationException $e) {
            return response()->json(['ok' => false, 'errors' => $e->errors()], 422);
        }

        if (!is_array($report)) {
            $report = [];
        }

        $row = SeoAnalysis::create([
            'uuid'              => (string) Str::uuid(),
            'user_id'           => auth()->id(),
            'source_type'       => $mode,
            'source_url'        => $mode === 'url' ? $request->input('url') : null,
            'content_type'      => $report['post']['type'] ?? null,
            'subject_id'        => $mode === 'post' ? $subjectId : null,
            'subject_title'     => $report['post']['title'] ?? $request->input('title'),
            'target_keyword'    => $report['stats']['topic'] ?? null,
            'overall_score'     => $report['score'] ?? null,
            'grade'             => $report['grade'] ?? null,
            'categories'        => $report['categories'] ?? null,
            'report'            => $report,
            'word_count'        => $report['stats']['words'] ?? 0,
            'reading_time'      => $report['stats']['reading_time'] ?? null,
            'analyzer_version'  => $report['analyzer_version'] ?? '2.0.0',
            'fetch_status'      => isset($fetch) ? (!empty($fetch['ok']) ? 'ok' : 'failed') : null,
            'fetch_http_status' => $fetch['status'] ?? null,
        ]);

        return response()->json(['ok' => true, 'analysis_id' => $row->id, 'data' => $report]);
    }

    public function show(SeoAnalysis $analysis)
    {
        return response()->json(['ok' => true, 'data' => $analysis->report]);
    }
}
