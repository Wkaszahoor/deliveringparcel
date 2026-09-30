<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MenuOrderController extends Controller
{
    public const LABELS = [
        'analytics'      => 'Analytics',
        'carriers'       => 'Carriers',
        'cms'            => 'CMS',
        'communications' => 'Communications',
        'compliance'     => 'Compliance',
        'content'        => 'Site Content',
        'mobile-app'     => 'Mobile App',
        'orders'         => 'Orders & Quotes',
        'payments'       => 'Payments',
        'rates'          => 'Geo, Rates & Units',
        'settings'       => 'Settings',
        'shippers'       => 'Shippers',
        'warehouse'      => 'Warehouse',
    ];

    public const ORDER_KEY = 'admin_menu_order';

    /** All manageable bars. */
    protected function bars(): array
    {
        return [
            ['key' => 'admin_sidebar', 'title' => 'Admin Sidebar', 'hint' => 'Section order in the admin panel sidebar'],
            ['key' => 'top_bar',       'title' => 'Website Top Bar', 'hint' => 'Links in the landing page top bar'],
            ['key' => 'bottom_bar',    'title' => 'Website Bottom Bar', 'hint' => 'Legal links strip in the footer'],
            ['key' => 'shopper_bar',   'title' => 'Client Sidebar — Shopper', 'hint' => 'Shopper section items (client & shipper portal)'],
            ['key' => 'shipper_bar',   'title' => 'Client Sidebar — Shipper', 'hint' => 'Shipper workspace items'],
        ];
    }

    public function index(Request $request)
    {
        $bars = collect($this->bars())->map(function ($bar) {
            $bar['items'] = match ($bar['key']) {
                'admin_sidebar' => $this->adminItems(),
                'top_bar'       => $this->cmsItems('header_top'),
                'bottom_bar'    => $this->cmsItems('footer_bottom'),
                'shopper_bar'   => $this->staticItems('shopper_bar_order', [
                    'orders'        => 'My Orders',
                    'notifications' => 'Notifications',
                    'messages'      => 'Messages',
                    'password'      => 'Change Password',
                    'become-shipper' => 'Become a Shipper',
                ]),
                'shipper_bar'   => $this->staticItems('shipper_bar_order', [
                    'dashboard'   => 'Overview',
                    'requests'    => 'Marketplace',
                    'my-quotes'   => 'My Quotes',
                    'assignments' => 'Assignments',
                    'wallet'      => 'Wallet',
                    'countries'   => 'Service Countries',
                    'profile'     => 'Profile',
                ]),
            };
            $bar['items'] = collect($bar['items'])->values()->map(fn ($e, $i) => $e + ['pos' => $i + 1])->all();
            return $bar;
        })->all();

        return view('admin.menu-order', ['bars' => $bars]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'bar'   => 'required|string|in:admin_sidebar,top_bar,bottom_bar,shopper_bar,shipper_bar',
            'order' => 'required|array',
            'order.*' => 'string|max:100',
        ]);

        match ($data['bar']) {
            'admin_sidebar' => $this->saveAdminOrder($data['order']),
            'shopper_bar'   => $this->saveCsv('shopper_bar_order', $data['order']),
            'shipper_bar'   => $this->saveCsv('shipper_bar_order', $data['order']),
            'top_bar'       => $this->saveCmsOrder('header_top', $data['order']),
            'bottom_bar'    => $this->saveCmsOrder('footer_bottom', $data['order']),
        };

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'bar' => $data['bar']]);
        }

        return back()->with('success', 'Menu order saved.');
    }

    /* ================= item sources ================= */

    protected function adminItems(): array
    {
        $files = collect(File::glob(resource_path('views/admin/menu/*.blade.php')))
            ->map(fn ($f) => basename($f, '.blade.php'))->values();
        $saved = $this->savedCsv(self::ORDER_KEY);

        return collect($saved)->filter(fn ($k) => $files->contains($k))->values()
            ->merge($files->reject(fn ($k) => in_array($k, $saved))->sort()->values())
            ->map(fn ($k) => ['key' => $k, 'label' => self::LABELS[$k] ?? ucfirst(str_replace('-', ' ', $k))])
            ->all();
    }

    protected function cmsItems(string $location): array
    {
        try {
            $menu = NavMenu::forLocation($location);
            if (!$menu) {
                return [];
            }
            return $menu->items()->orderBy('sort_order')->get()
                ->map(fn ($i) => ['key' => (string) $i->id, 'label' => $i->label])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function staticItems(string $settingKey, array $defaults): array
    {
        $saved = $this->savedCsv($settingKey);
        return collect($saved)->filter(fn ($k) => isset($defaults[$k]))->values()
            ->merge(collect(array_keys($defaults))->reject(fn ($k) => in_array($k, $saved))->values())
            ->map(fn ($k) => ['key' => $k, 'label' => $defaults[$k]])
            ->all();
    }

    /* ================= persistence ================= */

    protected function saveAdminOrder(array $order): void
    {
        $valid = collect(File::glob(resource_path('views/admin/menu/*.blade.php')))
            ->map(fn ($f) => basename($f, '.blade.php'));
        $this->saveCsv(self::ORDER_KEY, collect($order)->filter(fn ($k) => $valid->contains($k))->values()->all());
    }

    protected function saveCsv(string $key, array $order): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => implode(',', $order), 'group' => 'admin', 'type' => 'string', 'updated_at' => now()]
        );
    }

    /** CMS bars: order[] is a list of cms_menu_items IDs → write sort_order. */
    protected function saveCmsOrder(string $location, array $order): void
    {
        $menu = NavMenu::forLocation($location);
        if (!$menu) {
            return;
        }
        // Reorder the WHOLE menu: submitted ids first (in the given order),
        // then any items not mentioned keep their relative order after them —
        // this avoids sort_order collisions.
        $all = $menu->items()->orderBy('sort_order')->orderBy('id')->pluck('id')
            ->map(fn ($v) => (string) $v)->all();
        $submitted = array_values(array_intersect($order, $all));
        $rest = array_values(array_diff($all, $submitted));
        foreach (array_merge($submitted, $rest) as $pos => $id) {
            DB::table('cms_menu_items')->where('id', (int) $id)->update(['sort_order' => $pos + 1]);
        }
    }

    protected function savedCsv(string $key): array
    {
        try {
            $row = DB::table('settings')->where('key', $key)->value('value');
            return $row ? array_values(array_filter(explode(',', $row))) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
