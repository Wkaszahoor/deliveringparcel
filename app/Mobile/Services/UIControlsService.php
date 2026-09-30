<?php

namespace App\Mobile\Services;

use App\Mobile\Models\MobileUiAction;
use App\Mobile\Models\MobileUiElement;
use App\Mobile\Models\MobileUiScreen;
use App\Mobile\Models\MobileUiSection;
use Illuminate\Support\Facades\Cache;

/**
 * 4-level UI control hierarchy (screens → sections → elements/actions)
 * with a versioned 10-minute cache. Every save bumps the version so the
 * apps can detect changes cheaply via GET /api/mobile/v1/ui/version.
 */
class UIControlsService
{
    public const CACHE_KEY   = 'mobile_ui_controls';
    public const VERSION_KEY = 'mobile_ui_version';
    public const CACHE_TTL   = 600;

    public function getFullHierarchy(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->buildHierarchy();
        });
    }

    public function buildHierarchy(): array
    {
        $screens = MobileUiScreen::orderBy('screen_order')->get();
        $sections = MobileUiSection::orderBy('section_order')->get();
        $elements = MobileUiElement::orderBy('element_order')->get();
        $actions = MobileUiAction::orderBy('action_order')->get();

        $result = ['screens' => [], 'version' => $this->getVersion(), 'cached_at' => now()->toISOString()];

        foreach ($screens as $screen) {
            $sectionsData = [];
            foreach ($sections->where('screen_key', $screen->screen_key) as $section) {
                $sectionsData[$section->section_key] = [
                    'section_key'   => $section->section_key,
                    'section_name'  => $section->section_name,
                    'global_status' => $section->global_status,
                    'admin_visible' => (bool) $section->admin_visible,
                    'admin_enabled' => (bool) $section->admin_enabled,
                    'client_visible'=> (bool) $section->client_visible,
                    'client_enabled'=> (bool) $section->client_enabled,
                    'elements' => $elements->where('section_key', $section->section_key)
                        ->mapWithKeys(fn ($el) => [$el->element_key => [
                            'element_key'   => $el->element_key,
                            'element_name'  => $el->element_name,
                            'element_type'  => $el->element_type,
                            'global_status' => $el->global_status,
                            'admin_visible' => (bool) $el->admin_visible,
                            'admin_enabled' => (bool) $el->admin_enabled,
                            'client_visible'=> (bool) $el->client_visible,
                            'client_enabled'=> (bool) $el->client_enabled,
                        ]])->toArray(),
                    'actions' => $actions->where('section_key', $section->section_key)
                        ->mapWithKeys(fn ($a) => [$a->action_key => [
                            'action_key'   => $a->action_key,
                            'action_name'  => $a->action_name,
                            'action_label' => $a->action_label,
                            'action_type'  => $a->action_type,
                            'global_status'=> $a->global_status,
                            'admin_visible'  => (bool) $a->admin_visible,
                            'admin_enabled'  => (bool) $a->admin_enabled,
                            'client_visible' => (bool) $a->client_visible,
                            'client_enabled' => (bool) $a->client_enabled,
                            'allowed_order_statuses' => $a->allowed_order_statuses ?? [],
                            'confirmation_required'  => (bool) $a->confirmation_required,
                            'confirmation_message'   => $a->confirmation_message,
                            'icon'  => $a->icon,
                            'color' => $a->color,
                        ]])->toArray(),
                ];
            }

            $result['screens'][$screen->screen_key] = [
                'screen_key'    => $screen->screen_key,
                'screen_name'   => $screen->screen_name,
                'global_status' => $screen->global_status,
                'admin_visible' => (bool) $screen->admin_visible,
                'admin_enabled' => (bool) $screen->admin_enabled,
                'client_visible'=> (bool) $screen->client_visible,
                'client_enabled'=> (bool) $screen->client_enabled,
                'icon'          => $screen->icon,
                'sections'      => $sectionsData,
            ];
        }

        return $result;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->incrementVersion();
    }

    public function getVersion(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    public function incrementVersion(): void
    {
        Cache::forever(self::VERSION_KEY, $this->getVersion() + 1);
    }

    public function getVersionOnly(): array
    {
        return ['version' => $this->getVersion(), 'cached_at' => now()->toISOString()];
    }
}
