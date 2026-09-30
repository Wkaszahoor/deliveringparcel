<?php

namespace App\Console\Commands;

use App\Services\Media\ImageOptimizerService;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'dp:optimize-images
                            {--path=uploads : Folder under public/ to scan}
                            {--quality=82 : Re-encode quality 50-95}
                            {--limit=100 : Max images per run}';

    protected $description = 'Compress images in place, create WebP copies + responsive WebP variants (originals backed up to storage/app/image-backups)';

    public function handle(ImageOptimizerService $optimizer): int
    {
        $path = (string) $this->option('path');
        $quality = max(50, min(95, (int) $this->option('quality')));
        $limit = max(1, (int) $this->option('limit'));

        $images = $optimizer->scan($path);
        $this->info(count($images) . ' image(s) found under public/' . $path);

        $done = 0;
        $saved = 0.0;
        foreach (array_slice($images, 0, $limit) as $img) {
            if ($img['is_webp']) {
                continue;
            }
            $r = $optimizer->optimize($img['path'], $quality);
            if (($r['ok'] ?? false)) {
                $done++;
                $saved += max(0, $r['orig_kb'] - $r['new_kb']);
                $this->line(sprintf('  ✔ %s  %.1fKB → %.1fKB  (-%.1f%%)', $img['path'], $r['orig_kb'], $r['new_kb'], $r['saved_pct']));
            } else {
                $this->warn('  ✖ ' . $img['path'] . ' — ' . ($r['error'] ?? 'failed'));
            }
        }

        $this->info("Optimized {$done} image(s), saved " . round($saved, 1) . " KB. WebP copies + variants created alongside.");
        $this->line('Backups: storage/app/image-backups — restore anytime.');
        return self::SUCCESS;
    }
}
