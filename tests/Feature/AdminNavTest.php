<?php

namespace Tests\Feature;

use App\Support\AdminNav;
use Tests\TestCase;

class AdminNavTest extends TestCase
{
    public function test_sections_returns_dashboard_first(): void
    {
        $sections = AdminNav::sections();

        $this->assertSame('dashboard', $sections[0]['key']);
        $this->assertSame('Dashboard', $sections[0]['label']);
    }

    public function test_sections_include_orders_section(): void
    {
        // No sub-links: the section has a single page (freequote-inbox), so the slim
        // sub-sidebar (which hides itself when a section has no links) stays hidden here.
        $sections = AdminNav::sections();
        $orders = collect($sections)->firstWhere('key', 'orders');

        $this->assertNotNull($orders);
        $this->assertSame('Orders & Quotes', $orders['label']);
        $this->assertEmpty($orders['links']);
    }

    public function test_active_section_matches_current_request_path(): void
    {
        $active = AdminNav::activeSectionKey('freequote-inbox');

        $this->assertSame('orders', $active);
    }

    public function test_active_section_defaults_to_dashboard_for_unknown_path(): void
    {
        $active = AdminNav::activeSectionKey('admin/something-not-mapped');

        $this->assertSame('dashboard', $active);
    }

    public function test_active_section_returns_full_section_for_known_path(): void
    {
        $section = AdminNav::activeSection('address');

        $this->assertSame('addresses', $section['key']);
        $this->assertSame('Shipping Addresses', $section['label']);
    }

    public function test_active_section_falls_back_to_dashboard_for_unknown_path(): void
    {
        $section = AdminNav::activeSection('admin/something-not-mapped');

        $this->assertSame('dashboard', $section['key']);
    }

    public function test_every_section_has_its_own_top_nav_route(): void
    {
        // The top nav links each tab via $section['route'], independent of the
        // sidebar's (possibly empty) 'links' list — regression test for a bug where
        // emptying 'links' to hide the sub-sidebar also silently broke the top-nav
        // href, since it used to fall back to $section['links'][0]['route'].
        foreach (AdminNav::sections() as $section) {
            $this->assertArrayHasKey('route', $section, "section '{$section['key']}' is missing a top-nav route");
            if ($section['key'] !== 'dashboard') {
                $this->assertNotSame('admin-dashbord', $section['route'], "section '{$section['key']}' should not silently fall back to the dashboard route");
            }
        }
    }
}
