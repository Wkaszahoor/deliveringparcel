<?php

namespace App\Http\Controllers;

use App\Models\DynamicPage;
use Illuminate\Http\Request;

class DynamicPageController extends Controller
{
    /**
     * Public renderer for admin-managed pages: /page/{slug}.
     * The /page/{slug} route is registered in routes/web.php.
     */
    public function show(Request $request, string $slug)
    {
        $page = DynamicPage::where('slug', $slug)
                           ->where('is_active', true)
                           ->firstOrFail();

        // If the page has a controller_override, forward to it
        if ($page->controller_override) {
            $ctrl = app($page->controller_override);
            if (method_exists($ctrl, 'show')) {
                return $ctrl->show($request, $slug, $page);
            }
        }

        $layout = $page->layout ?: 'home2.layouts.app';

        return view('dynamic-pages.show', compact('page', 'layout'));
    }
}
