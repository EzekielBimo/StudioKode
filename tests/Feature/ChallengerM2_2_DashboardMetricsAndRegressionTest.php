<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM2_2_DashboardMetricsAndRegressionTest extends TestCase
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
    | Suite 1: Dashboard Dynamic KPI Metrics Empirical Challenge
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Challenge: Zero state dashboard metrics must return exact integer 0s,
     * not null, not string '0', and empty arrays for recent lists.
     */
    public function test_kpi_metrics_zero_state_integers_and_structure(): void
    {
        // Zero records in DB
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->component('Admin/Dashboard/Index');

            $props = $page->toArray()['props'];
            $metrics = $props['metrics'];

            $this->assertIsArray($metrics);
            $this->assertSame(0, $metrics['total_projects']);
            $this->assertSame(0, $metrics['featured_projects']);
            $this->assertSame(0, $metrics['published_projects']);
            $this->assertSame(0, $metrics['total_inquiries']);
            $this->assertSame(0, $metrics['new_inquiries']);

            $this->assertIsArray($props['recent_projects']);
            $this->assertCount(0, $props['recent_projects']);

            $this->assertIsArray($props['recent_inquiries']);
            $this->assertCount(0, $props['recent_inquiries']);
        });
    }

    /**
     * Empirical Challenge: Publication date matrix (past vs future vs null draft).
     * Future published_at (> now()) and null published_at must NOT count towards published_projects.
     */
    public function test_kpi_metrics_published_at_past_vs_future_vs_null(): void
    {
        // 2 past published projects
        Project::factory()->create(['published_at' => now()->subDays(5)]);
        Project::factory()->create(['published_at' => now()->subMinute()]);

        // 2 scheduled future projects (must NOT be counted as published)
        Project::factory()->create(['published_at' => now()->addHour()]);
        Project::factory()->create(['published_at' => now()->addDays(7)]);

        // 3 draft projects (published_at is null)
        Project::factory()->create(['published_at' => null]);
        Project::factory()->create(['published_at' => null]);
        Project::factory()->create(['published_at' => null]);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $metrics = $page->toArray()['props']['metrics'];

            $this->assertSame(7, (int) $metrics['total_projects']);
            $this->assertSame(2, (int) $metrics['published_projects']);
        });
    }

    /**
     * Empirical Challenge: Featured toggle count accuracy under mixed distribution.
     */
    public function test_kpi_metrics_featured_toggle_accuracy(): void
    {
        // 5 featured, 6 non-featured
        Project::factory()->count(5)->create(['is_featured' => true]);
        Project::factory()->count(6)->create(['is_featured' => false]);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $metrics = $page->toArray()['props']['metrics'];

            $this->assertSame(11, (int) $metrics['total_projects']);
            $this->assertSame(5, (int) $metrics['featured_projects']);
        });
    }

    /**
     * Empirical Challenge: Bilingual inquiries status accuracy.
     * Both 'new' and 'baru' count towards new_inquiries.
     * 'contacted', 'dihubungi', 'archived', 'diarsipkan' count ONLY towards total_inquiries.
     */
    public function test_kpi_metrics_inquiries_bilingual_status_accuracy(): void
    {
        Inquiry::factory()->count(3)->create(['status' => 'new']);
        Inquiry::factory()->count(4)->create(['status' => 'baru']);
        Inquiry::factory()->count(2)->create(['status' => 'contacted']);
        Inquiry::factory()->count(3)->create(['status' => 'dihubungi']);
        Inquiry::factory()->count(1)->create(['status' => 'archived']);
        Inquiry::factory()->count(2)->create(['status' => 'diarsipkan']);

        // Total: 3 + 4 + 2 + 3 + 1 + 2 = 15 inquiries.
        // New: 3 ('new') + 4 ('baru') = 7 new inquiries.

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $metrics = $page->toArray()['props']['metrics'];

            $this->assertSame(15, (int) $metrics['total_inquiries']);
            $this->assertSame(7, (int) $metrics['new_inquiries']);
        });
    }

    /**
     * Empirical Challenge: Dynamic reactivity across mutations.
     * Metrics must dynamically update immediately when records are inserted, modified, or deleted.
     */
    public function test_kpi_metrics_dynamic_reactivity_across_mutations(): void
    {
        // Initial state: 2 projects, 2 inquiries
        $p1 = Project::factory()->create(['is_featured' => true, 'published_at' => now()->subDay()]);
        $p2 = Project::factory()->create(['is_featured' => false, 'published_at' => null]);
        $i1 = Inquiry::factory()->create(['status' => 'new']);
        $i2 = Inquiry::factory()->create(['status' => 'contacted']);

        $res1 = $this->actingAs($this->admin)->get('/admin');
        $m1 = $res1->original->getData()['page']['props']['metrics'];
        $this->assertSame(2, (int) $m1['total_projects']);
        $this->assertSame(1, (int) $m1['featured_projects']);
        $this->assertSame(1, (int) $m1['published_projects']);
        $this->assertSame(2, (int) $m1['total_inquiries']);
        $this->assertSame(1, (int) $m1['new_inquiries']);

        // Mutation 1: Insert 2 projects (1 featured + published) and 2 inquiries ('baru')
        $p3 = Project::factory()->create(['is_featured' => true, 'published_at' => now()->subDay()]);
        $p4 = Project::factory()->create(['is_featured' => false, 'published_at' => null]);
        Inquiry::factory()->count(2)->create(['status' => 'baru']);

        $res2 = $this->actingAs($this->admin)->get('/admin');
        $m2 = $res2->original->getData()['page']['props']['metrics'];
        $this->assertSame(4, (int) $m2['total_projects']);
        $this->assertSame(2, (int) $m2['featured_projects']);
        $this->assertSame(2, (int) $m2['published_projects']);
        $this->assertSame(4, (int) $m2['total_inquiries']);
        $this->assertSame(3, (int) $m2['new_inquiries']);

        // Mutation 2: Delete 1 project ($p1), update inquiry $i1 status to 'archived'
        $p1->delete();
        $i1->update(['status' => 'archived']);

        $res3 = $this->actingAs($this->admin)->get('/admin');
        $m3 = $res3->original->getData()['page']['props']['metrics'];
        $this->assertSame(3, (int) $m3['total_projects']);
        $this->assertSame(1, (int) $m3['featured_projects']);
        $this->assertSame(1, (int) $m3['published_projects']);
        $this->assertSame(4, (int) $m3['total_inquiries']);
        $this->assertSame(2, (int) $m3['new_inquiries']);
    }

    /**
     * Empirical Challenge: Recent projects must be capped at 5, sorted descending by created_at,
     * and expose all required attributes.
     */
    public function test_dashboard_recent_projects_ordering_capping_and_fields(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Project::factory()->create([
                'title' => "Project #{$i}",
                'created_at' => now()->subMinutes(60 - ($i * 5)),
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $projects = $page->toArray()['props']['recent_projects'];

            $this->assertCount(5, $projects);
            $this->assertEquals('Project #10', $projects[0]['title']);
            $this->assertEquals('Project #9', $projects[1]['title']);
            $this->assertEquals('Project #8', $projects[2]['title']);
            $this->assertEquals('Project #7', $projects[3]['title']);
            $this->assertEquals('Project #6', $projects[4]['title']);

            // Required contract fields
            $requiredKeys = ['id', 'title', 'slug', 'location', 'is_featured', 'published_at', 'created_at'];
            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $projects[0]);
            }
        });
    }

    /**
     * Empirical Challenge: Recent inquiries must be capped at 5, sorted descending by created_at,
     * and expose all required attributes.
     */
    public function test_dashboard_recent_inquiries_ordering_capping_and_fields(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Inquiry::factory()->create([
                'name' => "Client #{$i}",
                'created_at' => now()->subMinutes(60 - ($i * 5)),
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $inquiries = $page->toArray()['props']['recent_inquiries'];

            $this->assertCount(5, $inquiries);
            $this->assertEquals('Client #10', $inquiries[0]['name']);
            $this->assertEquals('Client #9', $inquiries[1]['name']);
            $this->assertEquals('Client #8', $inquiries[2]['name']);
            $this->assertEquals('Client #7', $inquiries[3]['name']);
            $this->assertEquals('Client #6', $inquiries[4]['name']);

            // Required contract fields
            $requiredKeys = ['id', 'name', 'email', 'site_location', 'status', 'created_at'];
            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $inquiries[0]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 2: Query Budget Limits Empirical Challenge
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Challenge: Dashboard must strictly execute <= 6 queries,
     * and query count must remain constant O(1) whether 0, 20, or 60 records exist.
     */
    public function test_dashboard_query_budget_strictly_bounded_o1(): void
    {
        // Baseline run with 0 records
        $queryCountZero = 0;
        DB::listen(function () use (&$queryCountZero) {
            $queryCountZero++;
        });

        $this->actingAs($this->admin)->get('/admin')->assertStatus(200);

        $this->assertLessThanOrEqual(
            6,
            $queryCountZero,
            "Dashboard exceeded query budget with 0 records: {$queryCountZero} queries (limit: 6)"
        );

        // Heavy stress run: 50 projects and 50 inquiries
        Project::factory()->count(50)->create();
        Inquiry::factory()->count(50)->create();

        $queryCountFifty = 0;
        DB::listen(function () use (&$queryCountFifty) {
            $queryCountFifty++;
        });

        $this->actingAs($this->admin)->get('/admin')->assertStatus(200);

        $this->assertLessThanOrEqual(
            6,
            $queryCountFifty,
            "Dashboard exceeded query budget under heavy load (50 records): {$queryCountFifty} queries (limit: 6)"
        );

        // Complexity check: query count for 50 records must not exceed query count for 0 records + 1
        $this->assertLessThanOrEqual(
            $queryCountZero + 1,
            $queryCountFifty,
            "Dashboard queries scaled with record count: {$queryCountZero} vs {$queryCountFifty} (N+1 regression)"
        );
    }

    /**
     * Empirical Challenge: Inquiries index query budget must strictly be <= 5 queries
     * across all search, filtering, and pagination combinations.
     */
    public function test_inquiries_index_query_budget_across_all_filter_combinations(): void
    {
        Inquiry::factory()->count(35)->create();

        $testScenarios = [
            'default index' => '/admin/inquiries',
            'filter baru' => '/admin/inquiries?status=baru',
            'filter new' => '/admin/inquiries?status=new',
            'filter dihubungi' => '/admin/inquiries?status=dihubungi',
            'filter diarsipkan' => '/admin/inquiries?status=diarsipkan',
            'filter all' => '/admin/inquiries?status=all',
            'search query' => '/admin/inquiries?search=Garden',
            'combined filter and search' => '/admin/inquiries?status=baru&search=Estate',
            'pagination page 2' => '/admin/inquiries?page=2',
        ];

        DB::enableQueryLog();

        foreach ($testScenarios as $scenarioName => $uri) {
            DB::flushQueryLog();

            $response = $this->actingAs($this->admin)->get($uri);
            $response->assertStatus(200);

            $queries = DB::getQueryLog();
            $queryCount = count($queries);

            $this->assertLessThanOrEqual(
                5,
                $queryCount,
                "Inquiries index exceeded query budget for scenario '{$scenarioName}' ({$uri}): executed {$queryCount} queries (limit: 5)"
            );
        }

        DB::disableQueryLog();
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 3: Regression Suite Across Public Routes & M1 Auth
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Challenge: Public routes render correctly with expected Inertia components and data.
     */
    public function test_regression_public_showcase_routes(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Home
        $this->get('/')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Home/Index')->has('featuredProjects')->has('statistics'));

        // Projects index
        $this->get('/projects')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Index')->has('projects'));

        // Projects show
        $project = Project::firstOrFail();
        $this->get("/projects/{$project->slug}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Show')->has('project')->has('images'));

        // Studio
        $this->get('/studio')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Studio/Index'));

        // Services
        $this->get('/services')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Services/Index'));

        // Contact
        $this->get('/contact')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Contact/Index'));
    }

    /**
     * Empirical Challenge: Public portfolio filtering and sorting.
     */
    public function test_regression_portfolio_filters(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Search by keyword
        $this->get('/projects?search=Moss')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Index')->has('projects', 1));

        // Sort newest
        $this->get('/projects?sort=newest')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Index')->has('projects', 6));

        // Sort area_desc
        $this->get('/projects?sort=area_desc')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page->component('Projects/Index')->has('projects', 6));
    }

    /**
     * Empirical Challenge: Public inquiry submission and validation regression.
     */
    public function test_regression_public_inquiry_submission(): void
    {
        // Valid submission
        $payload = [
            'name' => 'Challenger Regression User',
            'email' => 'challenger@studiokode.com',
            'phone' => '+62 811 0000 1111',
            'site_location' => 'Bali Sanctuary',
            'estimated_area' => '1000 m²',
            'budget_range' => '$100k - $250k',
            'message' => 'Empirical test regression inquiry for StudioKode Landscape.',
        ];

        $response = $this->post('/inquiries', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Challenger Regression User',
            'email' => 'challenger@studiokode.com',
            'status' => 'new',
        ]);

        // Invalid submission (missing name and email)
        $invalidResponse = $this->post('/inquiries', [
            'message' => 'Incomplete inquiry.',
        ]);
        $invalidResponse->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * Empirical Challenge: M1 Admin Auth regression.
     * Route protection, valid/invalid login, logout, shared Inertia state.
     */
    public function test_regression_m1_admin_auth(): void
    {
        // 1. Unauthenticated guest visiting /admin redirected to /admin/login
        $this->get('/admin')->assertRedirect('/admin/login');

        // 2. Guest can view login page
        $this->get('/admin/login')->assertStatus(200)->assertInertia(fn (Assert $p) => $p->component('Admin/Auth/Login'));

        // 3. Invalid credentials rejected
        $failedLogin = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'wrong-password',
        ]);
        $failedLogin->assertSessionHasErrors('email');
        $this->assertGuest();

        // 4. Intended URL redirect test: visiting /admin/inquiries stores intended URL
        $this->get('/admin/inquiries')->assertRedirect('/admin/login');
        $intendedLogin = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);
        $intendedLogin->assertRedirect('/admin/inquiries');
        $this->assertAuthenticatedAs($this->admin);

        // 5. Authenticated user visiting /admin/login redirected to /admin
        $this->actingAs($this->admin)->get('/admin/login')->assertRedirect('/admin');

        // 6. Direct login without intended URL redirects to /admin
        $this->post('/admin/logout');
        $this->assertGuest();
        session()->forget('url.intended');

        $directLogin = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);
        $directLogin->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);

        // 7. Shared auth.user prop exposed for authenticated user
        $dashboardResponse = $this->actingAs($this->admin)->get('/admin');
        $dashboardResponse->assertInertia(fn (Assert $page) => $page
            ->has('auth.user', fn (Assert $user) => $user
                ->where('email', 'admin@studiokode.com')
                ->where('name', $this->admin->name)
                ->missing('password')
                ->etc()
            )
        );

        // 8. Logout terminates session
        $logoutResponse = $this->actingAs($this->admin)->post('/admin/logout');
        $logoutResponse->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /**
     * Empirical Challenge: Indonesian localization for inquiry validation errors and flash notifications.
     */
    public function test_inquiry_actions_indonesian_flash_and_validation(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        // Missing status validation
        $resMissing = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", []);
        $resMissing->assertSessionHasErrors(['status' => 'Status konsultasi wajib dipilih.']);

        // Invalid status validation
        $resInvalid = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", ['status' => 'invalid_status_xyz']);
        $resInvalid->assertSessionHasErrors(['status' => 'Status konsultasi yang dipilih tidak valid.']);

        // Valid update flash message
        $resUpdate = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", ['status' => 'dihubungi']);
        $resUpdate->assertRedirect();
        $resUpdate->assertSessionHas('success', 'Status pesan konsultasi berhasil diperbarui.');
        $this->assertEquals('dihubungi', $inquiry->fresh()->status);

        // Delete flash message
        $resDelete = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");
        $resDelete->assertRedirect('/admin/inquiries');
        $resDelete->assertSessionHas('success', 'Pesan konsultasi berhasil dihapus.');
        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 4: Vite Production Build Artifacts Integrity
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Challenge: Vite production build artifacts exist on disk and
     * manifest maps all required admin and public pages.
     */
    public function test_vite_production_build_artifacts_present(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath, 'Vite manifest.json must exist in public/build/');

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest, 'Vite manifest.json must be valid JSON');

        // Required entrypoints & page chunks
        $expectedEntries = [
            'resources/css/app.css',
            'resources/js/Pages/Admin/Auth/Login.jsx',
            'resources/js/Pages/Admin/Dashboard/Index.jsx',
            'resources/js/Pages/Admin/Inquiries/Index.jsx',
            'resources/js/Pages/Admin/Inquiries/Show.jsx',
            'resources/js/Pages/Home/Index.jsx',
            'resources/js/Pages/Projects/Index.jsx',
            'resources/js/Pages/Projects/Show.jsx',
            'resources/js/Pages/Studio/Index.jsx',
            'resources/js/Pages/Services/Index.jsx',
            'resources/js/Pages/Contact/Index.jsx',
        ];

        foreach ($expectedEntries as $entry) {
            $this->assertArrayHasKey($entry, $manifest, "Manifest missing entry for '{$entry}'");
            $assetFile = public_path('build/' . $manifest[$entry]['file']);
            $this->assertFileExists($assetFile, "Compiled asset for '{$entry}' does not exist at {$assetFile}");
        }
    }
}
