<?php

namespace App\Services\Cms;

use App\Models\NavMenu;
use Illuminate\Support\Facades\Cache;

class CmsNavService
{
    private int $cacheTtl = 300; // 5 minutes

    // Returns nested items for a menu location
    public function getMenu(string $location): \Illuminate\Support\Collection
    {
        $cacheKey = 'cms_nav_' . $location;
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($location) {
            try {
                $menu = NavMenu::forLocation($location);
                if (!$menu) {
                    return collect([]);
                }
                return $menu->items()
                            ->where('is_active', true)
                            ->get();
            } catch (\Throwable $e) {
                \Log::warning("CmsNavService: {$location} - " . $e->getMessage());
                return collect([]);
            }
        });
    }

    // Returns flat list for footer columns
    public function getFooterMenus(): array
    {
        return [
            'col1'   => $this->getMenu('footer_col1'),
            'col2'   => $this->getMenu('footer_col2'),
            'col3'   => $this->getMenu('footer_col3'),
            'bottom' => $this->getMenu('footer_bottom'),
        ];
    }

    public function bustAll(): void
    {
        foreach (['header_main', 'header_top', 'footer_col1',
                  'footer_col2', 'footer_col3', 'footer_bottom'] as $loc) {
            Cache::forget('cms_nav_' . $loc);
        }
        Cache::forget('cms_nav_menu_all');
    }

    // For admin menu builder: returns all menus with ALL items (flat)
    public function getAllMenusForAdmin(): \Illuminate\Support\Collection
    {
        return Cache::remember('cms_nav_menu_all', $this->cacheTtl, function () {
            try {
                return NavMenu::with(['allItems'])->orderBy('id')->get();
            } catch (\Throwable $e) {
                return collect([]);
            }
        });
    }
}
