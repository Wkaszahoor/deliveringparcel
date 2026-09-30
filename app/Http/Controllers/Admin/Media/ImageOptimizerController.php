<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Controller;
use App\Services\Media\ImageOptimizerService;
use Illuminate\Http\Request;

class ImageOptimizerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.image-optimizer', ['path' => 'uploads']);
    }

    public function data(Request $request, ImageOptimizerService $optimizer)
    {
        $path = $request->input('path', 'uploads');
        $images = $optimizer->scan($path);
        $totalKb = round(array_sum(array_column($images, 'size_kb')), 1);

        return response()->json([
            'ok'       => true,
            'images'   => $images,
            'total'    => count($images),
            'total_kb' => $totalKb,
            'webp'     => count(array_filter($images, fn ($i) => $i['is_webp'] || $i['has_webp'])),
        ]);
    }

    public function optimize(Request $request, ImageOptimizerService $optimizer)
    {
        $data = $request->validate([
            'path'    => 'required|string|max:300',
            'quality' => 'nullable|integer|min:50|max:95',
        ]);
        $rel = str_replace(['..', "\0"], '', $data['path']);
        $result = $optimizer->optimize($rel, (int) ($data['quality'] ?? 82));

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    public function bulk(Request $request, ImageOptimizerService $optimizer)
    {
        $data = $request->validate([
            'path'    => 'nullable|string|max:100',
            'quality' => 'nullable|integer|min:50|max:95',
            'limit'   => 'nullable|integer|min:1|max:100',
        ]);
        $images = $optimizer->scan($data['path'] ?? 'uploads');
        $quality = (int) ($data['quality'] ?? 82);
        $limit = (int) ($data['limit'] ?? 40);

        $done = 0;
        $savedKb = 0;
        $failed = [];
        foreach (array_slice($images, 0, $limit) as $img) {
            if ($img['is_webp']) {
                continue;
            }
            $r = $optimizer->optimize($img['path'], $quality);
            if (($r['ok'] ?? false)) {
                $done++;
                $savedKb += max(0, $r['orig_kb'] - $r['new_kb']);
            } else {
                $failed[] = $img['path'];
            }
        }

        return response()->json([
            'ok'       => true,
            'optimized'=> $done,
            'saved_kb' => round($savedKb, 1),
            'failed'   => $failed,
        ]);
    }
}
