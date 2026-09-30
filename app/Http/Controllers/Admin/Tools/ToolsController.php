<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\GoogleReviewsSyncService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ToolsController extends Controller
{
    /* ---------------- Global search ---------------- */

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        return view('admin.tools.search', ['q' => $q, 'results' => $q === '' ? [] : $this->searchAll($q)]);
    }

    public function searchJson(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        return response()->json(['q' => $q, 'groups' => $q === '' ? [] : $this->searchAll($q)]);
    }

    private function searchAll(string $q): array
    {
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';
        $groups = [];

        $groups['Users'] = DB::table('users')->where('name', 'like', $like)->orWhere('email', 'like', $like)
            ->limit(10)->get(['id', 'name', 'email'])->map(function ($u) {
                return ['label' => $u->name . ' <' . $u->email . '>', 'url' => route('admin.users.show', $u->id)];
            })->all();

        $groups['Orders'] = DB::table('orders')->where('order_id', 'like', $like)->orWhere('trackingid', 'like', $like)
            ->limit(10)->get(['id', 'order_id', 'order_status'])->map(function ($o) {
                return ['label' => '#' . $o->order_id . ' (' . ($o->order_status ?: 'processing') . ')', 'url' => url('admin-orders')];
            })->all();

        $groups['Quotes'] = DB::table('request_quotes')->where('name', 'like', $like)->orWhere('email', 'like', $like)
            ->limit(10)->get(['id', 'name', 'email'])->map(function ($r) {
                return ['label' => $r->name . ' <' . $r->email . '>', 'url' => route('admin.quotes.show', $r->id)];
            })->all();

        $groups['Messages'] = DB::table('contactuses')->where('name', 'like', $like)->orWhere('email', 'like', $like)
            ->limit(10)->get(['id', 'name', 'email'])->map(function ($c) {
                return ['label' => $c->name . ' <' . $c->email . '>', 'url' => route('admin.contacts.index')];
            })->all();

        foreach ([
            ['blogs', 'Blog posts', ['title', 'slug'], 'admin.blogs.edit'],
            ['shop_products', 'Shop products', ['name', 'slug'], 'admin.shop.products.edit'],
            ['services', 'Services', ['title', 'slug'], 'admin.services.edit'],
        ] as [$table, $label, $cols, $editRoute]) {
            if (Schema::hasTable($table)) {
                $nameCol = $cols[0] === 'name' ? 'name' : 'title';
                $groups[$label] = DB::table($table)->where($nameCol, 'like', $like)
                    ->limit(10)->get(array_merge(['id'], $cols))->map(function ($r) use ($nameCol, $editRoute) {
                        return ['label' => $r->{$nameCol}, 'url' => route($editRoute, $r->id)];
                    })->all();
            }
        }

        return array_filter($groups);
    }

    /* ---------------- System health ---------------- */

    public function health()
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            $dbOk = true;
            $dbLatency = round((microtime(true) - $start) * 1000, 1);
            $dbVersion = DB::select('SELECT VERSION() as v')[0]->v ?? '';
        } catch (\Throwable $e) {
            $dbOk = false;
            $dbLatency = null;
            $dbVersion = '';
        }

        $diskFree = @disk_free_space('C:');
        $diskTotal = @disk_total_space('C:');

        $logErrors = [];
        $log = storage_path('logs/laravel.log');
        if (is_file($log)) {
            $lines = array_slice(file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [], -400);
            foreach (array_reverse($lines) as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.{0,180})/', $line, $m)) {
                    $logErrors[] = ['at' => $m[1], 'level' => $m[3], 'msg' => $m[4]];
                    if (count($logErrors) >= 10) {
                        break;
                    }
                }
            }
        }

        return view('admin.tools.health', [
            'health' => [                'php'          => PHP_VERSION,
                'laravel'      => app()->version(),
                'env'          => config('app.env'),
                'cache'        => config('cache.default'),
                'queue'        => config('queue.default'),
                'session'      => config('session.driver'),
                'db_ok'        => $dbOk,
                'db_version'   => $dbVersion,
                'db_latency'   => $dbLatency,
                'disk_pct'     => $diskTotal ? round(100 - ($diskFree / $diskTotal) * 100, 1) : null,
                'disk_free_gb' => $diskFree ? round($diskFree / 1024 / 1024 / 1024, 1) : null,
                'server_time'  => now()->format('Y-m-d H:i:s'),
            ],
            'logErrors' => $logErrors,
        ]);
    }

    /* ---------------- Read-only DB viewer ---------------- */

    public function database()
    {
        $tables = DB::table('information_schema.TABLES')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->orderBy('TABLE_NAME')
            ->get(['TABLE_NAME as name', 'TABLE_ROWS as rows', 'DATA_LENGTH', 'INDEX_LENGTH'])
            ->map(function ($t) {
                return [
                    'name' => $t->name,
                    'rows' => (int) $t->rows,
                    'size' => $this->formatBytes(($t->DATA_LENGTH ?: 0) + ($t->INDEX_LENGTH ?: 0)),
                ];
            });

        return view('admin.tools.database', ['tables' => $tables]);
    }

    public function browse(Request $request, string $table)
    {
        /* Whitelist strictly against information_schema. */
        $exists = DB::table('information_schema.TABLES')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->where('TABLE_NAME', $table)->exists();
        abort_unless($exists, 404);

        $safe = str_replace('`', '', $table);
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $count = (int) DB::select("SELECT COUNT(*) AS c FROM `{$safe}`")[0]->c;
        $rows = DB::select("SELECT * FROM `{$safe}` LIMIT {$perPage} OFFSET {$offset}");

        /* Mask sensitive columns, truncate longs. */
        $mask = ['password', 'token', 'secret', 'remember_token', 'cvv', 'card'];
        $rows = array_map(function ($row) use ($mask) {
            foreach ((array) $row as $k => $v) {
                foreach ($mask as $needle) {
                    if (stripos($k, $needle) !== false) {
                        $row->{$k} = '***';
                    }
                }
                if (is_string($v) && strlen($v) > 64) {
                    $row->{$k} = substr($v, 0, 64) . '…';
                }
            }

            return $row;
        }, $rows);

        return view('admin.tools.database-browse', [
            'table'   => $table,
            'rows'    => $rows,
            'columns' => $rows ? array_keys((array) $rows[0]) : [],
            'page'    => $page,
            'lastPage' => max(1, (int) ceil($count / $perPage)),
            'total'   => $count,
        ]);
    }

    /* ---------------- PDF tools (print-view fallback) ---------------- */

    public function pdfIndex()
    {
        return view('admin.tools.pdf.index');
    }

    public function pdfInvoice(int $offerOrderId)
    {
        $offer = DB::table('offerorders as of')
            ->join('orders as o', 'o.id', '=', 'of.order_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->where('of.id', $offerOrderId)
            ->select('of.*', 'o.order_id as ref', 'u.name as user_name', 'u.email as user_email')
            ->first();
        abort_if($offer === null, 404);

        $products = DB::table('offerorderproducts')->where('offerorder_id', $offer->id)->get();
        $services = DB::table('offerorderservices')->where('offerorder_id', $offer->id)->get();

        return PdfService::stream('admin.tools.pdf.invoice', compact('offer', 'products', 'services'), 'invoice-' . $offer->id);
    }

    public function pdfOrder(int $orderId)
    {
        $order = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->where('o.id', $orderId)
            ->select('o.*', 'u.name as user_name', 'u.email as user_email')
            ->first();
        abort_if($order === null, 404);

        $items = DB::table('orderproducts')->where('order_id', $order->id)->get();

        return PdfService::stream('admin.tools.pdf.order', compact('order', 'items'), 'order-' . $order->order_id);
    }

    public function pdfFinancial(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $from = $request->input('from') ?: now()->subMonths(12)->format('Y-m-d');
        $to = $request->input('to') ?: now()->format('Y-m-d');

        $months = DB::table('offerorders as of')
            ->whereBetween('of.created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('of.offer_status', 1)
            ->selectRaw("DATE_FORMAT(of.created_at, '%Y-%m') AS ym, COUNT(DISTINCT of.order_id) AS orders_count, SUM(of.total) AS revenue")
            ->groupBy('ym')->orderByDesc('ym')->get();

        return PdfService::stream('admin.tools.pdf.financial', compact('months', 'from', 'to'), 'financial-report');
    }

    /* ---------------- Testimonials ---------------- */

    public function testimonialsIndex()
    {
        $platformSettings = [
            'reviews_google_enabled'     => Setting::getBool('reviews_google_enabled', false),
            'reviews_google_rating'      => Setting::get('reviews_google_rating', ''),
            'reviews_google_count'       => (int) Setting::get('reviews_google_count', 0),
            'reviews_google_url'         => Setting::get('reviews_google_url', ''),
            'reviews_trustpilot_enabled' => Setting::getBool('reviews_trustpilot_enabled', false),
            'reviews_trustpilot_rating'  => Setting::get('reviews_trustpilot_rating', ''),
            'reviews_trustpilot_count'   => (int) Setting::get('reviews_trustpilot_count', 0),
            'reviews_trustpilot_url'     => Setting::get('reviews_trustpilot_url', ''),
            'reviews_sitejabber_enabled' => Setting::getBool('reviews_sitejabber_enabled', false),
            'reviews_sitejabber_rating'  => Setting::get('reviews_sitejabber_rating', ''),
            'reviews_sitejabber_count'   => (int) Setting::get('reviews_sitejabber_count', 0),
            'reviews_sitejabber_url'     => Setting::get('reviews_sitejabber_url', ''),
            'reviews_manual_enabled'     => Setting::getBool('reviews_manual_enabled', true),
        ];

        $countsBySource = Testimonial::select('source', DB::raw('count(*) as total'), DB::raw('sum(is_published) as published'))
            ->groupBy('source')
            ->get()
            ->keyBy('source');

        return view('admin.tools.testimonials.index', [
            'googleSyncAvailable' => app(GoogleReviewsSyncService::class)->configured(),
            'platforms'           => $platformSettings,
            'countsBySource'      => $countsBySource,
        ]);
    }

    public function testimonialsUpdatePlatforms(Request $request)
    {
        $request->validate([
            'reviews_google_rating'      => ['nullable', 'regex:/^[0-5](\.[0-9])?$/'],
            'reviews_google_count'       => 'nullable|integer|min:0',
            'reviews_google_url'         => 'nullable|url|max:500',
            'reviews_trustpilot_rating'  => ['nullable', 'regex:/^[0-5](\.[0-9])?$/'],
            'reviews_trustpilot_count'   => 'nullable|integer|min:0',
            'reviews_trustpilot_url'     => 'nullable|url|max:500',
            'reviews_sitejabber_rating'  => ['nullable', 'regex:/^[0-5](\.[0-9])?$/'],
            'reviews_sitejabber_count'   => 'nullable|integer|min:0',
            'reviews_sitejabber_url'     => 'nullable|url|max:500',
        ]);

        Setting::set('reviews_google_enabled', $request->has('reviews_google_enabled') ? '1' : '0');
        Setting::set('reviews_google_rating', (string) $request->input('reviews_google_rating', ''));
        Setting::set('reviews_google_count', (string) ((int) $request->input('reviews_google_count', 0)));
        Setting::set('reviews_google_url', (string) $request->input('reviews_google_url', ''));

        Setting::set('reviews_trustpilot_enabled', $request->has('reviews_trustpilot_enabled') ? '1' : '0');
        Setting::set('reviews_trustpilot_rating', (string) $request->input('reviews_trustpilot_rating', ''));
        Setting::set('reviews_trustpilot_count', (string) ((int) $request->input('reviews_trustpilot_count', 0)));
        Setting::set('reviews_trustpilot_url', (string) $request->input('reviews_trustpilot_url', ''));

        Setting::set('reviews_sitejabber_enabled', $request->has('reviews_sitejabber_enabled') ? '1' : '0');
        Setting::set('reviews_sitejabber_rating', (string) $request->input('reviews_sitejabber_rating', ''));
        Setting::set('reviews_sitejabber_count', (string) ((int) $request->input('reviews_sitejabber_count', 0)));
        Setting::set('reviews_sitejabber_url', (string) $request->input('reviews_sitejabber_url', ''));

        Setting::set('reviews_manual_enabled', $request->has('reviews_manual_enabled') ? '1' : '0');

        Setting::flushCache();

        return redirect()->route('admin.testimonials.index')->with('success', 'Review platform settings updated.');
    }

    public function testimonialsTogglePublish(Testimonial $testimonial)
    {
        $testimonial->update(['is_published' => ! $testimonial->is_published]);

        if (request()->wantsJson()) {
            return response()->json(['ok' => true, 'is_published' => (bool) $testimonial->is_published]);
        }

        return redirect()->route('admin.testimonials.index')->with(
            'success',
            'Testimonial ' . ($testimonial->is_published ? 'published' : 'hidden') . '.'
        );
    }

    public function testimonialsSyncGoogle(GoogleReviewsSyncService $sync)
    {
        $result = $sync->sync();

        if ($result['error']) {
            return redirect()->route('admin.testimonials.index')->with('error', $result['error']);
        }

        return redirect()->route('admin.testimonials.index')->with(
            'success',
            "Google sync done: {$result['imported']} new review(s) imported as unpublished, {$result['skipped']} already had a copy. Aggregate rating is now {$result['rating']} ({$result['total']} reviews)."
        );
    }

    public function testimonialsData(Request $request)
    {
        $query = Testimonial::query();
        if ($q = trim((string) $request->input('q', ''))) {
            $query->where('user_name', 'like', '%' . $q . '%')->orWhere('content', 'like', '%' . $q . '%');
        }
        if ($src = trim((string) $request->input('source', ''))) {
            $query->where('source', $src);
        }
        if ($request->filled('published')) {
            $query->where('is_published', (int) $request->input('published'));
        }
        $query->orderBy('sort')->orderByDesc('id');

        return response()->json($query->paginate(dp_per_page($request))->through(function (Testimonial $t) {
            return [
                'id'        => $t->id,
                'name'      => $t->user_name,
                'role'      => $t->role_or_company,
                'content'   => \Str::limit(strip_tags($t->content), 90),
                'rating'    => $t->rating ? str_repeat('★', $t->rating) : '—',
                'published' => (bool) $t->is_published,
                'sort'      => $t->sort,
                'avatar'    => $t->user_avatar ? asset($t->user_avatar) : null,
                'source'    => $t->source,
                'urls'      => [
                    'edit'           => route('admin.testimonials.edit', $t->id),
                    'delete'         => route('admin.testimonials.destroy', $t->id),
                    'up'             => route('admin.testimonials.move', ['testimonial' => $t->id, 'direction' => 'up']),
                    'down'           => route('admin.testimonials.move', ['testimonial' => $t->id, 'direction' => 'down']),
                    'toggle_publish' => route('admin.testimonials.toggle-publish', $t->id),
                ],
            ];
        })->toArray());
    }

    public function testimonialsCreate()
    {
        return view('admin.tools.testimonials.form', ['testimonial' => new Testimonial()]);
    }

    public function testimonialsStore(Request $request)
    {
        Testimonial::create($this->testimonialRules($request) + ['user_avatar' => $this->storeAvatar($request)]);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial created.');
    }

    public function testimonialsEdit(Testimonial $testimonial)
    {
        return view('admin.tools.testimonials.form', ['testimonial' => $testimonial]);
    }

    public function testimonialsUpdate(Request $request, Testimonial $testimonial)
    {
        $data = $this->testimonialRules($request);
        if ($path = $this->storeAvatar($request)) {
            $data['user_avatar'] = $path;
        }
        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function testimonialsDestroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    public function testimonialsMove(Testimonial $testimonial, string $direction)
    {
        $delta = $direction === 'up' ? -1 : 1;
        $testimonial->update(['sort' => max(0, $testimonial->sort + $delta)]);

        return redirect()->route('admin.testimonials.index');
    }

    private function testimonialRules(Request $request)
    {
        $data = $request->validate([
            'user_name'       => 'required|string|max:255',
            'role_or_company' => 'nullable|string|max:255',
            'country'         => 'nullable|string|max:100',
            'content'         => 'required|string|max:3000',
            'rating'          => 'nullable|integer|between:1,5',
            'is_published'    => 'nullable|boolean',
            'sort'            => 'nullable|integer|min:0|max:9999',
            'avatar'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
            'source'          => 'nullable|in:manual,google,trustpilot,sitejabber',
            'source_url'      => 'nullable|url|max:500',
        ]);
        $data['is_published'] = $request->has('is_published');
        $data['rating'] = $data['rating'] ?? null;
        $data['sort'] = $data['sort'] ?? 0;
        $data['source'] = $data['source'] ?? 'manual';

        return $data;
    }

    private function storeAvatar(Request $request): ?string
    {
        if (!$request->hasFile('avatar')) {
            return null;
        }

        // UploadGuard: allow-list mimes/ext, size cap, random name (SE-006).
        return \App\Support\UploadGuard::store($request->file('avatar'), 'testimonials');
    }

    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
