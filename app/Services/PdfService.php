<?php

namespace App\Services;

/**
 * PDF service — print-view fallback implementation (no external packages).
 * Streams an A4 print-optimized HTML view that triggers the browser print
 * dialog (Save as PDF). API mirrors a native PDF renderer so a driver such
 * as barryvdh/laravel-dompdf can be swapped in later without call-site changes.
 */
class PdfService
{
    /**
     * Stream a printable view. $view renders full HTML (A4 print CSS + auto print JS).
     */
    public static function stream(string $view, array $data, string $filename)
    {
        return response()
            ->view($view, $data, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '.html"');
    }

    public static function download(string $view, array $data, string $filename)
    {
        return self::stream($view, $data, $filename);
    }
}
