<?php

namespace App\Support;

/**
 * Resolve upload-column values to public URLs without double-prefixing.
 *
 * Two storage formats coexist in the legacy file columns:
 *   - legacy web uploads: bare filename            ("Screenshot.png")
 *   - UploadGuard rows:   full web-relative path   ("uploads/productsimages/x.png")
 */
class UploadUrl
{
    public static function resolve(?string $value, string $module): string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $v)) {
            return $v;
        }
        $rel = ltrim($v, '/');
        $prefix = 'uploads/' . $module . '/';

        return str_starts_with($rel, 'uploads/')
            ? asset($rel)
            : asset($prefix . $rel);
    }

    /** orderproducts.image / receipt / offerorderproducts.image (nullable columns). */
    public static function product(?string $value): string
    {
        return self::resolve($value, 'productsimages');
    }

    /** order_chats.image */
    public static function chat(?string $value): string
    {
        return self::resolve($value, 'chatimages');
    }
}
