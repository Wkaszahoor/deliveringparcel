<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Tests\TestCase;

class AdminTestimonialsTest extends TestCase
{
    public function test_home2_redirects_to_home(): void
    {
        $response = $this->get('/home2');
        $response->assertStatus(301);
        $response->assertRedirect('/');
    }

    public function test_home2_subroutes_redirect_to_root(): void
    {
        $this->get('/home2/blog')->assertStatus(301)->assertRedirect('/blog');
        $this->get('/home2/services')->assertStatus(301)->assertRedirect('/services');
        $this->get('/home2/track-order')->assertStatus(301)->assertRedirect('/track-order');
    }

    public function test_public_home_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Delivering Parcel');
    }

    public function test_public_testimonials_page_renders_with_filters(): void
    {
        $response = $this->get('/testimonials');
        $response->assertStatus(200);
        $response->assertSee('What Our Customers Say');
        $response->assertSee('All Sources');
    }

    public function test_admin_can_view_testimonials_index(): void
    {
        $admin = User::where('email', 'superadmin@deliveringparcel.com')->first();
        if (!$admin) {
            $this->markTestSkipped('Superadmin user not found in database.');
        }

        $response = $this->actingAs($admin)->get('/admin/testimonials');
        $response->assertStatus(200);
        $response->assertSee('Review Platforms & Trust Badges');
        $response->assertSee('Trustpilot');
        $response->assertSee('SiteJabber');
        $response->assertSee('Google');
    }

    public function test_admin_can_update_platform_settings(): void
    {
        $admin = User::where('email', 'superadmin@deliveringparcel.com')->first();
        if (!$admin) {
            $this->markTestSkipped('Superadmin user not found in database.');
        }

        $response = $this->actingAs($admin)->post('/admin/testimonials/platforms', [
            'reviews_google_enabled'     => '1',
            'reviews_google_rating'      => '4.8',
            'reviews_google_count'       => '50',
            'reviews_google_url'         => 'https://google.com',
            'reviews_trustpilot_enabled' => '1',
            'reviews_trustpilot_rating'  => '4.5',
            'reviews_trustpilot_count'   => '120',
            'reviews_trustpilot_url'     => 'https://trustpilot.com/review/deliveringparcel.com',
            'reviews_sitejabber_enabled' => '1',
            'reviews_sitejabber_rating'  => '4.0',
            'reviews_sitejabber_count'   => '15',
            'reviews_sitejabber_url'     => 'https://sitejabber.com',
            'reviews_manual_enabled'     => '1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.testimonials.index'));

        $this->assertTrue(Setting::getBool('reviews_google_enabled'));
        $this->assertSame('4.8', Setting::get('reviews_google_rating'));
        $this->assertSame('50', Setting::get('reviews_google_count'));
    }

    public function test_admin_can_toggle_publish_state(): void
    {
        $admin = User::where('email', 'superadmin@deliveringparcel.com')->first();
        if (!$admin) {
            $this->markTestSkipped('Superadmin user not found in database.');
        }

        $testimonial = Testimonial::first();
        if (!$testimonial) {
            $testimonial = Testimonial::create([
                'user_name'    => 'Test User',
                'content'      => 'Great service forwarded swiftly.',
                'rating'       => 5,
                'source'       => 'manual',
                'is_published' => false,
            ]);
        }

        $originalState = (bool) $testimonial->is_published;

        $response = $this->actingAs($admin)->postJson(route('admin.testimonials.toggle-publish', $testimonial->id));
        $response->assertStatus(200);
        $response->assertJson(['ok' => true, 'is_published' => !$originalState]);

        $testimonial->refresh();
        $this->assertSame(!$originalState, (bool) $testimonial->is_published);
    }

    public function test_services_page_renders_with_tailwind_layout(): void
    {
        $response = $this->get('/services');
        $response->assertStatus(200);
        $response->assertSee('Forwarding & Shipping Services');
    }

    public function test_track_order_page_renders_and_handles_post(): void
    {
        $response = $this->get('/track-order');
        $response->assertStatus(200);
        $response->assertSee('Track Your Shipment');

        $postResponse = $this->post('/track-order', [
            'ref'   => 'DP-NONEXISTENT',
            'email' => 'nobody@example.com',
        ]);
        $postResponse->assertStatus(200);
        $postResponse->assertSee('No Shipment Found');
    }

    public function test_blog_page_renders_with_tailwind_layout(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(200);
        $response->assertSee('The Delivering Parcel Blog');
    }
}

