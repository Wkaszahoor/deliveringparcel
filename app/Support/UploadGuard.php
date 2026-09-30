<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Shared validated-upload helper (SE-006).
 *
 * Every file upload MUST go through UploadGuard::store(). It enforces:
 *   - extension allow-list (jpg/jpeg/png/webp/gif, +pdf where explicitly allowed)
 *   - REAL MIME sniffing via finfo (not the client-declared Content-Type)
 *   - size cap (2048 KB images / 5120 KB pdf)
 *   - php/phtml/php3-8/phar/pht extensions blocked ALWAYS, regardless of
 *     allow-list arguments, plus other executable/script extensions
 *   - randomized storage names (client filename never reused)
 *   - storage in the WEB DOCROOT uploads tree (uploads/<module>/) which
 *     contains only static assets — never inside any PHP-executable path
 *
 * Returns a web-relative path ("uploads/<module>/<random>.<ext>") suitable
 * for asset() / <img src>, or null when the file is absent/invalid/rejected.
 */
class UploadGuard
{
    /** Extensions that may never be stored, no exceptions. */
    private const BLOCKED_ALWAYS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht', 'phar',
        'phps', 'cgi', 'pl', 'asp', 'aspx', 'jsp', 'jspx', 'exe', 'dll', 'bat',
        'cmd', 'sh', 'com', 'msi', 'ps1', 'vbs', 'hta', 'htaccess', 'htm', 'html',
        'shtml', 'svg', 'swf', 'jar', 'py', 'rb', 'cshtml', 'js',
    ];

    private const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const IMAGE_MIMES = [
        'image/jpeg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif',
    ];

    /** module => [allowed extensions]; modules not listed accept images only. */
    private const MODULE_RULES = [
        'compliance/kyc' => ['pdf'],
        'blog'           => self::IMAGE_EXTS,
        'shop/products'  => self::IMAGE_EXTS,
        'testimonials'   => self::IMAGE_EXTS,
    ];

    public const MAX_IMAGE_KB = 2048;
    public const MAX_PDF_KB   = 5120;

    /**
     * Validate and store an uploaded file.
     *
     * @param  string  $module  uploads sub-folder, e.g. "shop/products"
     * @param  bool    $allowPdf  accept a PDF instead of an image (compliance KYC)
     * @return string|null web-relative path, or null when rejected
     */
    public static function store(?UploadedFile $file, string $module, bool $allowPdf = false): ?string
    {
        if ($file === null || !$file->isValid()) {
            return null;
        }

        $ext = strtolower(preg_replace('/[^a-z0-9]/i', '', $file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: ''));

        if ($ext === '' || in_array($ext, self::BLOCKED_ALWAYS, true)) {
            return null;
        }

        $allowedExts = self::MODULE_RULES[$module] ?? self::IMAGE_EXTS;
        if ($allowPdf) {
            $allowedExts = array_merge($allowedExts, ['pdf']);
        }
        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        $isPdf  = $ext === 'pdf';
        $maxKb  = $isPdf ? self::MAX_PDF_KB : self::MAX_IMAGE_KB;

        // Size checked from the real file, not the client claim.
        $size = @filesize($file->getRealPath());
        if ($size === false || $size > $maxKb * 1024) {
            return null;
        }

        // Real content sniff — catches polyglots and mislabeled uploads.
        $realMime = self::sniffMime($file->getRealPath());
        $okMimes  = $isPdf ? ['application/pdf'] : self::IMAGE_MIMES;
        if (!in_array($realMime, $okMimes, true)) {
            return null;
        }

        // PDF: first bytes must be %PDF- (finfo is loose with polyglots).
        if ($isPdf && strpos((string) @file_get_contents($file->getRealPath(), false, null, 0, 5), '%PDF-') !== 0) {
            return null;
        }

        $dir = self::moduleDir($module);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("UploadGuard: cannot create uploads dir [{$dir}].");
        }

        $name = Str::lower(Str::random(32)) . '.' . $ext;
        $file->move($dir, $name);

        return 'uploads/' . $module . '/' . $name;
    }

    /**
     * Physical directory for a module inside the web docroot uploads tree:
     *   <docroot>/uploads/<module>   (= two levels above Laravel /public,
     *   matching how /uploads is served — see Admin\Blog\BlogController::uploadDir).
     */
    private static function moduleDir(string $module): string
    {
        $module = str_replace(['\\', '..'], ['/', ''], $module);

        return dirname(public_path(), 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $module;
    }

    private static function sniffMime(string $path): ?string
    {
        if (!is_file($path) || !function_exists('finfo_open')) {
            return null;
        }

        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return $mime !== false ? strtolower((string) $mime) : null;
    }
}
