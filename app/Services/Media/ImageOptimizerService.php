<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

/**
 * Smush-style image optimizer (local, no external API).
 *
 *  - re-encodes JPEG/PNG in place at a configurable quality,
 *  - keeps the original in storage/app/image-backups (reversible),
 *  - generates WebP copies + responsive WebP variants (srcset-ready),
 *  - enhanceContent() rewrites rendered post HTML: adds loading="lazy",
 *    decoding=async and srcset pointing at the generated variants so the
 *    browser picks the right size for the viewport (theme-responsive).
 */
class ImageOptimizerService
{
    protected ImageManager $manager;

    /** Responsive widths generated for srcset. */
    protected array $variants = [1280, 768, 480];

    /** Images wider than this are downscaled before re-encode. */
    protected int $maxWidth = 1920;

    public function __construct()
    {
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('GD with WebP support is required (the web PHP build has it; the CLI may not).');
        }
        $this->manager = new ImageManager(new GdDriver());
    }

    /** Scan public/{path} recursively for optimizable images. */
    public function scan(string $path = 'uploads'): array
    {
        $base = public_path($path);
        if (!is_dir($base)) {
            return [];
        }
        $out = [];
        foreach (File::allFiles($base) as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                continue;
            }
            $rel = $path . '/' . str_replace('\\', '/', $file->getRelativePathname());
            $size = $file->getSize();
            $dim = @getimagesize($file->getPathname());
            $out[] = [
                'path'        => $rel,
                'size_kb'     => round($size / 1024, 1),
                'width'       => $dim[0] ?? null,
                'height'      => $dim[1] ?? null,
                'is_webp'     => $ext === 'webp',
                'has_webp'    => file_exists(public_path($this->webpPath($rel))),
                'has_backup'  => File::exists(storage_path('app/image-backups/' . $rel)),
                'url'         => asset($rel),
            ];
        }
        usort($out, fn ($a, $b) => $b['size_kb'] <=> $a['size_kb']);
        return $out;
    }

    /** Optimize one image (relative to public/). Returns stats. */
    public function optimize(string $rel, int $quality = 82, bool $makeWebp = true): array
    {
        $abs = public_path($rel);
        if (!is_file($abs)) {
            return ['ok' => false, 'error' => 'File not found.'];
        }
        $origSize = filesize($abs);
        $origMtime = filemtime($abs);

        // 1. Backup original once.
        $backup = storage_path('app/image-backups/' . $rel);
        if (!File::exists($backup)) {
            File::ensureDirectoryExists(dirname($backup));
            File::copy($abs, $backup);
        }

        $image = $this->manager->read($abs);

        // 2. Downscale oversized images.
        if ($image->width() > $this->maxWidth) {
            $image->scaleDown(width: $this->maxWidth);
        }

        // 3. Re-encode in place (GIF skipped — animation loss).
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        if (!in_array($ext, ['gif'], true)) {
            $image->toJpeg(quality: $ext === 'png' ? 86 : $quality)->save($abs);
        }

        // 4. WebP sibling.
        $webpPath = null;
        if ($makeWebp && $ext !== 'webp') {
            $webpPath = $this->webpPath($rel);
            $this->manager->read($abs)->toWebp(quality: $quality)->save(public_path($webpPath));
        }

        // 5. Responsive WebP variants.
        $created = [];
        if ($makeWebp) {
            $fresh = $this->manager->read($abs);
            foreach ($this->variants as $w) {
                if ($fresh->width() > $w) {
                    $variantRel = $this->variantPath($webpPath ?: $rel, $w);
                    File::ensureDirectoryExists(dirname(public_path($variantRel)));
                    $fresh->scaleDown(width: $w)->toWebp(quality: $quality)->save(public_path($variantRel));
                    $created[] = $variantRel;
                }
            }
        }

        clearstatcache(true, $abs);
        $newSize = filesize($abs);
        $saved = $origSize > 0 ? round(100 - (100 * $newSize / $origSize), 1) : 0;

        return [
            'ok'          => true,
            'path'        => $rel,
            'orig_kb'     => round($origSize / 1024, 1),
            'new_kb'      => round($newSize / 1024, 1),
            'saved_pct'   => max(0, $saved),
            'webp'        => $webpPath,
            'variants'    => $created,
            'backed_up'   => true,
        ];
    }

    /**
     * Theme-responsive content HTML: adds loading="lazy", decoding="async",
     * and srcset from generated WebP variants when available.
     */
    public function enhanceContent(string $html): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) {
            $tag = $m[0];

            if (stripos($tag, 'loading=') === false) {
                $tag = str_ireplace('<img', '<img loading="lazy"', $tag);
            }
            if (stripos($tag, 'decoding=') === false) {
                $tag = str_ireplace('<img', '<img decoding="async"', $tag);
            }

            if (preg_match('/src=["\']([^"\']+)["\']/i', $tag, $s)) {
                $src = $s[1];
                // Repair known-broken content image URLs:
                //  - legacy dev host (e.g. dplive.test) -> current host
                //  - old static blog files under /blog/<uuid>/ -> /blog-old-static/<uuid>/
                $legacyHost = parse_url((string) config('app.url'), PHP_URL_HOST);
                $reqHost = app()->runningInConsole() ? null : request()->getHttpHost();
                if ($legacyHost && $reqHost && strcasecmp($legacyHost, $reqHost) !== 0) {
                    $newSrc = str_ireplace('://' . $legacyHost, '://' . $reqHost, $src);
                    if ($newSrc !== $src) {
                        $tag = str_ireplace($src, $newSrc, $tag);
                        $src = $newSrc;
                    }
                }
                if (preg_match('#/blog/([0-9a-f-]{8,})/#i', $src)) {
                    $newSrc = preg_replace('#/blog/([0-9a-f-]{8,})/#i', '/blog-old-static/$1/', $src);
                    $tag = str_ireplace($src, $newSrc, $tag);
                    $src = $newSrc;
                }
                $rel = ltrim(str_replace(['\\', asset('/')], ['/', '/'], $src), '/');
                $rel = preg_replace('#^https?://[^/]+/#i', '', $rel);
                $webp = $this->webpPath($rel);
                if (file_exists(public_path($webp))) {
                    $host = request()->getHttpHost();
                    $scheme = request()->getScheme();
                    $set = [$scheme . '://' . $host . '/' . $webp . ' ' . $this->variantWidth($webp) . 'w'];
                    foreach ($this->variants as $w) {
                        $v = $this->variantPath($webp, $w);
                        if (file_exists(public_path($v))) {
                            $set[] = $scheme . '://' . $host . '/' . $v . ' ' . $w . 'w';
                        }
                    }
                    $tag = str_ireplace('<img', '<img srcset="' . implode(', ', $set) . '"', $tag);
                }
            }

            return $tag;
        }, $html) ?? $html;
    }

    protected function webpPath(string $rel): string
    {
        $info = pathinfo($rel);
        return ($info['dirname'] === '.' ? '' : $info['dirname'] . '/') . $info['filename'] . '.webp';
    }

    protected function variantPath(string $rel, int $w): string
    {
        $info = pathinfo($rel);
        return ($info['dirname'] === '.' ? '' : $info['dirname'] . '/') . $info['filename'] . '-' . $w . 'w.' . ($info['extension'] ?? 'webp');
    }

    protected function variantWidth(string $rel): int
    {
        if (preg_match('/-(\d+)w\./', $rel, $m)) {
            return (int) $m[1];
        }
        $dim = @getimagesize(public_path($rel));
        return $dim[0] ?? 1920;
    }
}
