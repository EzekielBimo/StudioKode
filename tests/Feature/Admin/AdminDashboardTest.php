<?php

namespace Tests\Feature\Admin;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 1: Primary Happy Paths & Interface Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 1: Dashboard root (/admin) requires authentication.
     * Feature 8: Admin Layout & Navigation (R5)
     */
    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**
     * Tier 1: Dashboard renders successfully for authenticated admin.
     * Feature 8 & 10: Admin Dashboard Overview (R5)
     */
    public function test_dashboard_renders_for_authenticated_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
        );
    }

    /**
     * Tier 1: Dashboard exposes KPI metrics in Inertia props.
     * Feature 10: Dashboard KPI Metrics (R5)
     */
    public function test_dashboard_displays_kpi_metrics(): void
    {
        // Seed 3 projects: 2 featured, 2 published
        Project::factory()->create([
            'is_featured' => true,
            'published_at' => now(),
        ]);
        Project::factory()->create([
            'is_featured' => true,
            'published_at' => now(),
        ]);
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => null,
        ]);

        // Seed 2 inquiries: 1 new, 1 contacted
        Inquiry::factory()->create(['status' => 'new']);
        Inquiry::factory()->create(['status' => 'contacted']);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->has('metrics', function (Assert $metrics) {
                $metrics->hasAll([
                    'total_projects',
                    'featured_projects',
                    'published_projects',
                    'total_inquiries',
                    'new_inquiries',
                ])->etc();
            });
        });
    }

    /**
     * Tier 1: Dashboard exposes recent projects and recent inquiries lists.
     * Feature 11: Dashboard Recent Lists (R5)
     */
    public function test_dashboard_displays_recent_projects_and_inquiries(): void
    {
        Project::factory()->count(3)->create();
        Inquiry::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            // Accommodate snake_case or camelCase prop naming
            $hasProjects = $page->toArray()['props']['recent_projects'] ?? $page->toArray()['props']['recentProjects'] ?? null;
            $hasInquiries = $page->toArray()['props']['recent_inquiries'] ?? $page->toArray()['props']['recentInquiries'] ?? null;

            $this->assertNotNull($hasProjects, 'Expected recent projects in dashboard props');
            $this->assertNotNull($hasInquiries, 'Expected recent inquiries in dashboard props');
            $this->assertCount(3, $hasProjects);
            $this->assertCount(2, $hasInquiries);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 2: Boundary Value Analysis & Zero States
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 2: Dashboard handles zero state cleanly without 500 errors.
     * Feature 10: Dashboard KPI Metrics (R5)
     */
    public function test_dashboard_handles_zero_state_gracefully(): void
    {
        // 0 projects and 0 inquiries in DB
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $metrics = $page->toArray()['props']['metrics'] ?? [];
            $this->assertEquals(0, $metrics['total_projects'] ?? 0);
            $this->assertEquals(0, $metrics['total_inquiries'] ?? 0);
            $this->assertEquals(0, $metrics['new_inquiries'] ?? 0);
        });
    }

    /**
     * Tier 2: Dashboard KPI metrics accurately reflect dynamic record counts.
     * Feature 10: Dashboard KPI Metrics (R5)
     */
    public function test_dashboard_metrics_accurately_reflect_dynamic_counts(): void
    {
        // 4 projects total: 2 featured, 3 published, 1 draft
        Project::factory()->create(['is_featured' => true, 'published_at' => now()]);
        Project::factory()->create(['is_featured' => true, 'published_at' => now()]);
        Project::factory()->create(['is_featured' => false, 'published_at' => now()]);
        Project::factory()->create(['is_featured' => false, 'published_at' => null]);

        // 5 inquiries: 3 new, 1 contacted, 1 archived
        Inquiry::factory()->count(3)->create(['status' => 'new']);
        Inquiry::factory()->create(['status' => 'contacted']);
        Inquiry::factory()->create(['status' => 'archived']);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $metrics = $page->toArray()['props']['metrics'];
            $this->assertSame(4, (int) $metrics['total_projects']);
            $this->assertSame(2, (int) $metrics['featured_projects']);
            $this->assertSame(3, (int) $metrics['published_projects']);
            $this->assertSame(5, (int) $metrics['total_inquiries']);
            $this->assertSame(3, (int) $metrics['new_inquiries']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 3: Sorting, Ordering & Capping
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 3: Recent projects on dashboard are ordered newest first and capped (e.g. at 5).
     * Feature 11: Dashboard Recent Lists (R5)
     */
    public function test_dashboard_recent_projects_ordered_by_latest_and_capped(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Project::factory()->create([
                'title' => "Project {$i}",
                'created_at' => now()->subHours(10 - $i),
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $projects = $page->toArray()['props']['recent_projects'] ?? $page->toArray()['props']['recentProjects'];
            $this->assertLessThanOrEqual(5, count($projects));
            // First project in list should be the latest created
            $this->assertEquals('Project 8', $projects[0]['title']);
        });
    }

    /**
     * Tier 3: Recent inquiries on dashboard are ordered newest first and capped.
     * Feature 11: Dashboard Recent Lists (R5)
     */
    public function test_dashboard_recent_inquiries_ordered_by_latest_and_capped(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Inquiry::factory()->create([
                'name' => "Client {$i}",
                'created_at' => now()->subHours(10 - $i),
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $inquiries = $page->toArray()['props']['recent_inquiries'] ?? $page->toArray()['props']['recentInquiries'];
            $this->assertLessThanOrEqual(5, count($inquiries));
            $this->assertEquals('Client 8', $inquiries[0]['name']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 4: Indonesian Localization & Architectural Theme Consistency
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 4: Indonesian localization dictionary file exists and contains mandatory terms.
     * Feature 9: Indonesian Localization Strings (R5)
     */
    public function test_indonesian_localization_dictionary_completeness(): void
    {
        $idFilePath = resource_path('js/lib/id.js');

        $this->assertFileExists($idFilePath, 'Expected Indonesian localization dictionary at resources/js/lib/id.js');

        $content = file_get_contents($idFilePath);

        // Verify key architectural & administrative terms in Indonesian
        $requiredTerms = [
            'Dasbor',           // Dashboard
            'Proyek',           // Projects
            'Pesan Konsultasi', // Inquiries
            'Baru',             // New status
            'Dihubungi',        // Contacted status
            'Diarsipkan',       // Archived status
            'Unggulan',         // Featured
            'Simpan',           // Save
            'Hapus',            // Delete
        ];

        foreach ($requiredTerms as $term) {
            $this->assertStringContainsString(
                $term,
                $content,
                "Expected Indonesian term '{$term}' to be defined in resources/js/lib/id.js"
            );
        }
    }

    /**
     * Tier 4: Dashboard alias route (/admin/dashboard) redirects or renders identically.
     * Feature 8: Admin Layout & Navigation (R5)
     */
    public function test_dashboard_alias_route_resolves_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        // Either direct 200 or 302 to /admin
        $this->assertTrue(
            in_array($response->status(), [200, 302]),
            "Expected 200 OK or 302 redirect for /admin/dashboard, got {$response->status()}"
        );
    }
}
