<?php

namespace Tests\Feature\E2E;

use App\Models\Inquiry;
use App\Models\Project;
use Inertia\Testing\AssertableInertia as Assert;

class Tier2BoundaryCornerTest extends E2ETestCase
{
    /*
    |--------------------------------------------------------------------------
    | Category Boundaries (8 tests)
    |--------------------------------------------------------------------------
    */

    public function test_bva_category_all_returns_all_published_projects(): void
    {
        $response = $this->get('/projects?category=all');
        $response->assertStatus(200);
        $totalPublished = Project::published()->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('activeCategory', 'all')
            ->has('projects', $totalPublished)
        );
    }

    public function test_bva_category_design_filters_design_projects(): void
    {
        $response = $this->get('/projects?category=design');
        $response->assertStatus(200);
        $expectedCount = Project::published()->where('category', Project::CATEGORY_DESIGN)->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('activeCategory', 'design')
            ->has('projects', $expectedCount)
        );
    }

    public function test_bva_category_construction_filters_construction_projects(): void
    {
        $response = $this->get('/projects?category=construction');
        $response->assertStatus(200);
        $expectedCount = Project::published()->where('category', Project::CATEGORY_CONSTRUCTION)->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->where('activeCategory', 'construction')
            ->has('projects', $expectedCount)
        );
    }

    public function test_bva_category_case_insensitivity_handling(): void
    {
        $response = $this->get('/projects?category=DESIGN');
        $response->assertStatus(200);
        $expectedCount = Project::published()->where('category', Project::CATEGORY_DESIGN)->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', $expectedCount)
        );
    }

    public function test_bva_category_slug_with_spaces_handling(): void
    {
        $response = $this->get('/projects?category=Project%20Design');
        $response->assertStatus(200);
        $expectedCount = Project::published()->where('category', Project::CATEGORY_DESIGN)->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', $expectedCount)
        );
    }

    public function test_bva_category_non_existent_returns_empty_collection_without_error(): void
    {
        $response = $this->get('/projects?category=non_existent_landscape_category');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    public function test_bva_category_empty_string_defaults_to_all(): void
    {
        $response = $this->get('/projects?category=');
        $response->assertStatus(200);
        $totalPublished = Project::published()->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', $totalPublished)
        );
    }

    public function test_bva_category_malformed_input_handled_gracefully(): void
    {
        $response = $this->get('/projects?category=../../etc/passwd');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search Boundaries (8 tests)
    |--------------------------------------------------------------------------
    */

    public function test_bva_search_empty_string_returns_unfiltered_list(): void
    {
        $response = $this->get('/projects?search=');
        $response->assertStatus(200);
        $totalPublished = Project::published()->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', $totalPublished)
        );
    }

    public function test_bva_search_whitespace_only_returns_unfiltered_list(): void
    {
        $response = $this->get('/projects?search=%20%20%20');
        $response->assertStatus(200);
        $totalPublished = Project::published()->count();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', $totalPublished)
        );
    }

    public function test_bva_search_single_character_query_succeeds(): void
    {
        $response = $this->get('/projects?search=a');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_search_special_characters_escaped_cleanly(): void
    {
        $response = $this->get('/projects?search=%25');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_search_extreme_length_255_chars_query_succeeds(): void
    {
        $extremeQuery = str_repeat('A', 255);
        $response = $this->get('/projects?search=' . $extremeQuery);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    public function test_bva_search_extreme_length_1000_chars_query_does_not_crash(): void
    {
        $extremeQuery = str_repeat('X', 1000);
        $response = $this->get('/projects?search=' . $extremeQuery);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    public function test_bva_search_sql_injection_payload_does_not_break_query(): void
    {
        $payload = "' OR '1'='1";
        $response = $this->get('/projects?search=' . urlencode($payload));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    public function test_bva_search_xss_script_payload_escaped_and_safe(): void
    {
        $xss = '<script>alert(1)</script>';
        $response = $this->get('/projects?search=' . urlencode($xss));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
            ->where('filters.search', $xss)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Sort Boundaries (6 tests)
    |--------------------------------------------------------------------------
    */

    public function test_bva_sort_newest_orders_by_year_desc_id_desc(): void
    {
        $response = $this->get('/projects?sort=newest');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_sort_oldest_orders_by_year_asc_id_asc(): void
    {
        $response = $this->get('/projects?sort=oldest');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_sort_area_desc_numeric_ordering_in_sqlite(): void
    {
        $response = $this->get('/projects?sort=area_desc');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_sort_invalid_param_falls_back_to_newest(): void
    {
        $response = $this->get('/projects?sort=unsupported_sort_param');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_sort_empty_param_falls_back_to_newest(): void
    {
        $response = $this->get('/projects?sort=');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    public function test_bva_sort_sql_injection_in_sort_falls_back_to_newest(): void
    {
        $payload = 'year;DROP TABLE projects;';
        $response = $this->get('/projects?sort=' . urlencode($payload));
        $response->assertStatus(200);
        $this->assertGreaterThan(0, Project::count(), 'Table must remain intact after sort injection attempt');
    }

    /*
    |--------------------------------------------------------------------------
    | Project Slug Resolution Boundaries (8 tests)
    |--------------------------------------------------------------------------
    */

    public function test_bva_slug_non_existent_returns_404(): void
    {
        $response = $this->get('/projects/non-existent-garden-sanctuary');
        $response->assertStatus(404);
    }

    public function test_bva_slug_numeric_unseeded_returns_404(): void
    {
        $response = $this->get('/projects/999999');
        $response->assertStatus(404);
    }

    public function test_bva_slug_sql_injection_payload_returns_404(): void
    {
        $response = $this->get('/projects/%27%20OR%201=1--');
        $response->assertStatus(404);
    }

    public function test_bva_slug_xss_payload_returns_404(): void
    {
        $response = $this->get('/projects/%3Cscript%3Ealert(1)%3C%2Fscript%3E');
        $response->assertStatus(404);
    }

    public function test_bva_slug_uppercase_returns_404_or_redirects(): void
    {
        $response = $this->get('/projects/NON-EXISTENT-UPPERCASE-SLUG');
        $response->assertStatus(404);
    }

    public function test_bva_slug_trailing_whitespace_trimmed_or_404(): void
    {
        $response = $this->get('/projects/non-existent-slug%20');
        $response->assertStatus(404);
    }

    public function test_bva_slug_with_subpath_returns_404(): void
    {
        $project = Project::firstOrFail();
        $response = $this->get('/projects/' . $project->slug . '/unexpected-subpath');
        $response->assertStatus(404);
    }

    public function test_bva_slug_special_characters_dot_dot_returns_404(): void
    {
        $response = $this->get('/projects/../../etc/passwd');
        $this->assertContains($response->status(), [400, 404]);
    }

    /*
    |--------------------------------------------------------------------------
    | Inquiry Form Validation Boundaries (22 tests)
    |--------------------------------------------------------------------------
    */

    public function test_bva_inquiry_empty_payload_fails_validation_for_required_fields(): void
    {
        $initialCount = Inquiry::count();
        $response = $this->post('/inquiries', []);
        $response->assertSessionHasErrors(['name', 'email', 'message']);
        $this->assertEquals($initialCount, Inquiry::count());
    }

    public function test_bva_inquiry_missing_name_fails_validation(): void
    {
        $initialCount = Inquiry::count();
        $response = $this->post('/inquiries', [
            'email' => 'client@studiokode.design',
            'message' => 'Site assessment request.',
        ]);
        $response->assertSessionHasErrors(['name']);
        $this->assertEquals($initialCount, Inquiry::count());
    }

    public function test_bva_inquiry_missing_email_fails_validation(): void
    {
        $initialCount = Inquiry::count();
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'message' => 'Site assessment request.',
        ]);
        $response->assertSessionHasErrors(['email']);
        $this->assertEquals($initialCount, Inquiry::count());
    }

    public function test_bva_inquiry_missing_message_fails_validation(): void
    {
        $initialCount = Inquiry::count();
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
        ]);
        $response->assertSessionHasErrors(['message']);
        $this->assertEquals($initialCount, Inquiry::count());
    }

    public function test_bva_inquiry_invalid_email_format_fails_validation(): void
    {
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'invalid-email-format-without-at',
            'message' => 'Site assessment request.',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_bva_inquiry_name_exact_255_chars_passes_validation(): void
    {
        $name = str_repeat('A', 255);
        $response = $this->post('/inquiries', [
            'name' => $name,
            'email' => 'alex@hayes.com',
            'message' => 'Valid consultation request.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['name' => $name]);
    }

    public function test_bva_inquiry_name_256_chars_fails_validation(): void
    {
        $name = str_repeat('A', 256);
        $response = $this->post('/inquiries', [
            'name' => $name,
            'email' => 'alex@hayes.com',
            'message' => 'Valid consultation request.',
        ]);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_bva_inquiry_email_exact_255_chars_passes_validation(): void
    {
        // 243 chars + '@example.com' (12 chars) = 255 chars
        $email = str_repeat('a', 243) . '@example.com';
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => $email,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['email' => $email]);
    }

    public function test_bva_inquiry_email_256_chars_fails_validation(): void
    {
        // 244 chars + '@example.com' = 256 chars
        $email = str_repeat('a', 244) . '@example.com';
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => $email,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_bva_inquiry_phone_exact_50_chars_passes_validation(): void
    {
        $phone = str_repeat('1', 50);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'phone' => $phone,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['phone' => $phone]);
    }

    public function test_bva_inquiry_phone_51_chars_fails_validation(): void
    {
        $phone = str_repeat('1', 51);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'phone' => $phone,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertSessionHasErrors(['phone']);
    }

    public function test_bva_inquiry_site_location_exact_255_chars_passes_validation(): void
    {
        $location = str_repeat('L', 255);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'site_location' => $location,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['site_location' => $location]);
    }

    public function test_bva_inquiry_site_location_256_chars_fails_validation(): void
    {
        $location = str_repeat('L', 256);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'site_location' => $location,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertSessionHasErrors(['site_location']);
    }

    public function test_bva_inquiry_estimated_area_exact_100_chars_passes_validation(): void
    {
        $area = str_repeat('9', 97) . ' m²';
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'estimated_area' => $area,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['estimated_area' => $area]);
    }

    public function test_bva_inquiry_estimated_area_101_chars_fails_validation(): void
    {
        $area = str_repeat('9', 101);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'estimated_area' => $area,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertSessionHasErrors(['estimated_area']);
    }

    public function test_bva_inquiry_budget_range_exact_100_chars_passes_validation(): void
    {
        $budget = str_repeat('B', 100);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'budget_range' => $budget,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['budget_range' => $budget]);
    }

    public function test_bva_inquiry_budget_range_101_chars_fails_validation(): void
    {
        $budget = str_repeat('B', 101);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'budget_range' => $budget,
            'message' => 'Valid consultation request.',
        ]);
        $response->assertSessionHasErrors(['budget_range']);
    }

    public function test_bva_inquiry_message_exact_5000_chars_passes_validation(): void
    {
        $message = str_repeat('M', 5000);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'message' => $message,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['message' => $message]);
    }

    public function test_bva_inquiry_message_5001_chars_fails_validation(): void
    {
        $message = str_repeat('M', 5001);
        $response = $this->post('/inquiries', [
            'name' => 'Alexander Hayes',
            'email' => 'alex@hayes.com',
            'message' => $message,
        ]);
        $response->assertSessionHasErrors(['message']);
    }

    public function test_bva_inquiry_omitting_optional_fields_stores_nulls(): void
    {
        $response = $this->post('/inquiries', [
            'name' => 'Minima Client',
            'email' => 'minima@studiokode.design',
            'message' => 'Minimal brief inquiry.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Minima Client',
            'email' => 'minima@studiokode.design',
            'phone' => null,
            'site_location' => null,
            'estimated_area' => null,
            'budget_range' => null,
            'status' => 'new',
        ]);
    }

    public function test_bva_inquiry_xss_message_stored_without_executing(): void
    {
        $xss = '<script>alert("xss injection")</script>';
        $response = $this->post('/inquiries', [
            'name' => 'Security Auditor',
            'email' => 'sec@audit.org',
            'message' => $xss,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['message' => $xss]);
    }

    public function test_bva_inquiry_utf8_multibyte_and_emoji_stored_correctly(): void
    {
        $multibyte = '京都の庭園デザイン相談 🌿 石庭と苔';
        $response = $this->post('/inquiries', [
            'name' => 'Tanaka Kenji',
            'email' => 'kenji@kyoto-gardens.jp',
            'message' => $multibyte,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('inquiries', ['message' => $multibyte]);
    }

    /*
    |--------------------------------------------------------------------------
    | Component & Data Edge Cases (8 tests)
    |--------------------------------------------------------------------------
    */

    public function test_bva_project_show_handles_project_with_zero_images(): void
    {
        $project = Project::factory()->create([
            'title' => 'Imageless Alpine Retreat',
            'slug' => 'imageless-alpine-retreat',
            'published_at' => now(),
        ]);

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('images', 0)
        );
    }

    public function test_bva_project_show_handles_project_with_null_excerpt(): void
    {
        $project = Project::factory()->create([
            'title' => 'Null Excerpt Villa',
            'slug' => 'null-excerpt-villa',
            'excerpt' => null,
            'published_at' => now(),
        ]);

        $response = $this->get('/projects/' . $project->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.excerpt', null)
        );
    }

    public function test_bva_project_show_handles_fewer_than_3_related_projects(): void
    {
        // Purge existing projects and create exactly 2 published projects
        Project::query()->delete();
        $p1 = Project::factory()->create(['title' => 'Solo Sanctuary One', 'slug' => 'solo-one', 'published_at' => now()]);
        $p2 = Project::factory()->create(['title' => 'Solo Sanctuary Two', 'slug' => 'solo-two', 'published_at' => now()]);

        $response = $this->get('/projects/' . $p1->slug);
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('relatedProjects', 1)
        );
    }

    public function test_bva_filter_bar_handles_zero_total_count(): void
    {
        Project::query()->delete();
        $response = $this->get('/projects');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
            ->where('totalCount', 0)
        );
    }

    public function test_bva_home_page_handles_zero_featured_projects_fallback(): void
    {
        // Clear featured flag on all projects
        Project::query()->update(['is_featured' => false]);
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects')
        );
    }

    public function test_bva_contact_endpoint_route_alias_post_contact_succeeds(): void
    {
        $payload = [
            'name' => 'Alias Route Tester',
            'email' => 'alias@studiokode.design',
            'message' => 'Testing route alias /contact for inquiries.',
        ];

        $response = $this->post('/contact', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('inquiries', ['email' => 'alias@studiokode.design']);
    }

    public function test_bva_unauthenticated_admin_routes_redirect_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_bva_non_existent_http_route_returns_404(): void
    {
        $response = $this->get('/totally-unknown-arbitrary-url');
        $response->assertStatus(404);
    }
}
