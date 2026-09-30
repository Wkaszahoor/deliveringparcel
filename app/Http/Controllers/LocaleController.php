<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Persist the visitor's manually-picked language for a year and send
     * them back where they came from — see App\Http\Middleware\SetLocale
     * for how that cookie is read back on every later request.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        return redirect()->back()
            ->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }
}
