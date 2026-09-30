<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;

/**
 * Public testimonials — published quotes from the admin Tools → Testimonials
 * CRUD. Respects admin-enabled review platforms (Google, Trustpilot, SiteJabber, Manual).
 */
class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $enabledSources = Testimonial::enabledSources();

        $query = Testimonial::visible()
            ->orderByDesc('sort')
            ->orderByDesc('created_at');

        $currentSource = trim((string) $request->input('source', ''));
        if ($currentSource !== '' && in_array($currentSource, $enabledSources, true)) {
            $query->where('source', $currentSource);
        }

        $currentCountry = trim((string) $request->input('country', ''));
        if ($currentCountry !== '') {
            $query->where('country', $currentCountry);
        }

        $testimonials = $query->paginate(12)->appends($request->query());

        $countries = Testimonial::visible()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('testimonials', [
            'testimonials'   => $testimonials,
            'countries'      => $countries,
            'currentCountry' => $currentCountry,
            'enabledSources' => $enabledSources,
            'currentSource'  => $currentSource,
        ]);
    }
}
