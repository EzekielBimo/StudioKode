<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM4_1_QueryBudgetStressTest extends TestCase
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
    | Empirical Stress Suite 1: /admin/projects Query Budget & O(1) Scaling
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Stress Test: /admin/projects must execute a strictly constant, O(1)
     * query count whether 2 projects or 60+ projects exist with hundreds of gallery images.
     * Prevents N+1 query regression on images, gallery counts, or metadata.
     */
    public function test_admin_projects_index_strictly_o1_query_scaling_under_high_volume(): void
    {
        // 1. Establish baseline with 2 projects, each having 5 gallery images (10 images total)
        $baselineProjects = Project::factory()->count(2)->create();
        foreach ($baselineProjects as $project) {
            ProjectImage::factory()->count(5)->create(['project_id' => $project->id]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseBaseline = $this->actingAs($this->admin)->get('/admin/projects');
        $responseBaseline->assertStatus(200);

        $baselineQueries = DB::getQueryLog();
        $baselineCount = count($baselineQueries);

        DB::disableQueryLog();

        // 2. Seed heavy volume: 55 more projects, each having 5 gallery images (total 57 projects, 285 images)
        $bulkProjects = Project::factory()->count(55)->create();
        foreach ($bulkProjects as $project) {
            ProjectImage::factory()->count(5)->create(['project_id' => $project->id]);
        }

        $this->assertEquals(57, Project::count(), 'Ground truth project count should be 57.');
        $this->assertEquals(285, ProjectImage::count(), 'Ground truth project image count should be 285.');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseScaled = $this->actingAs($this->admin)->get('/admin/projects');
        $responseScaled->assertStatus(200);

        $scaledQueries = DB::getQueryLog();
        $scaledCount = count($scaledQueries);

        DB::disableQueryLog();

        // 3. Empirical verification: Scaled query count must be strictly EQUAL to baseline
        // If an N+1 query regression occurred (e.g. querying images for each paginated item),
        // scaledCount would exceed baselineCount by 8+ queries.
        $this->assertEquals(
            $baselineCount,
            $scaledCount,
            sprintf(
                "N+1 regression detected! Baseline (2 projects) executed %d queries, but scaled (57 projects, 285 images) executed %d queries.\nExecuted queries: %s",
                $baselineCount,
                $scaledCount,
                json_encode(array_column($scaledQueries, 'query'), JSON_PRETTY_PRINT)
            )
        );

        // Verify total query budget is strictly bounded (at most 4 queries: auth/session, count, select with withCount)
        $this->assertLessThanOrEqual(
            4,
            $scaledCount,
            "Total queries on /admin/projects ({$scaledCount}) exceeded strict ceiling of 4."
        );

        // 4. Assert response payload contains correct eager-loaded images_count
        $responseScaled->assertInertia(function (Assert $page) {
            $page->component('Admin/Projects/Index')
                ->has('projects.data', 10)
                ->where('projects.total', 57)
                ->has('projects.data.0.images_count');

            $firstProject = $page->toArray()['props']['projects']['data'][0];
            $this->assertEquals(5, $firstProject['images_count'], 'Project images_count should accurately reflect 5 gallery images.');
        });
    }

    /**
     * Empirical Stress Test: /admin/projects search and filtering query count remains
     * bounded and constant under large datasets.
     */
    public function test_admin_projects_index_search_and_filter_query_efficiency_under_volume(): void
    {
        // Seed 50 projects with mixed attributes
        Project::factory()->count(25)->create([
            'title' => 'Taman Urban Sanctuary',
            'is_featured' => true,
        ]);
        Project::factory()->count(25)->create([
            'title' => 'Villa Botanical Hill',
            'is_featured' => false,
        ]);

        // 1. Filter by featured=1 and keyword search
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseFiltered = $this->actingAs($this->admin)->get('/admin/projects?featured=1&search=Urban');
        $responseFiltered->assertStatus(200);

        $filterQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            4,
            $filterQueries,
            "Filtered search on /admin/projects executed {$filterQueries} queries (budget: <= 4)."
        );

        $responseFiltered->assertInertia(function (Assert $page) {
            $page->component('Admin/Projects/Index')
                ->where('projects.total', 25)
                ->where('filters.search', 'Urban')
                ->where('filters.featured', '1');
        });

        // 2. Verify page navigation query count (page 2)
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responsePage2 = $this->actingAs($this->admin)->get('/admin/projects?page=2');
        $responsePage2->assertStatus(200);

        $page2Queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            4,
            $page2Queries,
            "Page 2 request on /admin/projects executed {$page2Queries} queries (budget: <= 4)."
        );

        $responsePage2->assertInertia(function (Assert $page) {
            $page->component('Admin/Projects/Index')
                ->where('projects.current_page', 2)
                ->has('projects.data', 10);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Empirical Stress Suite 2: /admin/projects/{id}/edit Eager Loading
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Stress Test: Project edit endpoint with 50+ gallery images must eager-load
     * images in a single relationship query (total <= 3 queries), sorted by sort_order ASC.
     */
    public function test_admin_project_edit_view_eager_loading_with_50_gallery_images(): void
    {
        $project = Project::factory()->create([
            'title' => 'High Density Gallery Project',
        ]);

        // Create 50 gallery images with explicit reverse sort orders to stress-test ordering
        for ($i = 50; $i >= 1; $i--) {
            ProjectImage::factory()->create([
                'project_id' => $project->id,
                'sort_order' => $i,
                'caption' => "Gallery image sequence #{$i}",
            ]);
        }

        $this->assertEquals(50, $project->images()->count());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        DB::disableQueryLog();

        // Must execute in <= 3 queries (find project, eager load images WHERE project_id = ? ORDER BY sort_order ASC)
        $this->assertLessThanOrEqual(
            3,
            $queryCount,
            sprintf(
                "Eager loading violated on edit view! Executed %d queries instead of <= 3.\nQueries: %s",
                $queryCount,
                json_encode(array_column($queries, 'query'), JSON_PRETTY_PRINT)
            )
        );

        // Verify that all 50 images were loaded and returned in ascending order
        $response->assertInertia(function (Assert $page) use ($project) {
            $page->component('Admin/Projects/Edit')
                ->where('project.id', $project->id)
                ->has('project.images', 50);

            $images = $page->toArray()['props']['project']['images'];
            $sortOrders = array_column($images, 'sort_order');
            $expectedSortOrders = range(1, 50);

            $this->assertEquals(
                $expectedSortOrders,
                $sortOrders,
                'Eager-loaded project images must be sorted in strictly ascending sort_order sequence.'
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Empirical Stress Suite 3: /admin Dashboard Query Budget & Aggregations
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Stress Test: /admin dashboard executes bounded queries (single-query
     * aggregates for projects and inquiries, capped take(5) queries for recent items).
     * Query count remains strictly bounded regardless of 70+ projects and 80+ inquiries.
     */
    public function test_admin_dashboard_bounded_query_budget_with_high_data_volume(): void
    {
        // 1. Measure baseline with minimal records (2 projects, 2 inquiries) with explicit attributes
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => null,
        ]);
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => null,
        ]);
        Inquiry::factory()->create(['status' => 'contacted']);
        Inquiry::factory()->create(['status' => 'archived']);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseBase = $this->actingAs($this->admin)->get('/admin');
        $responseBase->assertStatus(200);

        $baseQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 2. Seed heavy volume:
        // - 20 featured & published projects
        // - 15 featured but future published (not yet published)
        // - 15 unfeatured published projects
        // - 15 draft projects (published_at null)
        // Total additional projects: 65 (total 67)
        Project::factory()->count(20)->create([
            'is_featured' => true,
            'published_at' => Carbon::now()->subDays(5),
        ]);
        Project::factory()->count(15)->create([
            'is_featured' => true,
            'published_at' => Carbon::now()->addDays(5),
        ]);
        Project::factory()->count(15)->create([
            'is_featured' => false,
            'published_at' => Carbon::now()->subDays(2),
        ]);
        Project::factory()->count(15)->create([
            'is_featured' => false,
            'published_at' => null,
        ]);

        // Seed 75 inquiries with diverse statuses:
        // - 25 'new' / 'baru'
        // - 25 'contacted' / 'dihubungi'
        // - 25 'archived' / 'diarsipkan'
        Inquiry::factory()->count(15)->create(['status' => 'new']);
        Inquiry::factory()->count(10)->create(['status' => 'baru']);
        Inquiry::factory()->count(15)->create(['status' => 'contacted']);
        Inquiry::factory()->count(10)->create(['status' => 'dihubungi']);
        Inquiry::factory()->count(15)->create(['status' => 'archived']);
        Inquiry::factory()->count(10)->create(['status' => 'diarsipkan']);

        $totalProjects = Project::count();
        $totalInquiries = Inquiry::count();
        $this->assertEquals(67, $totalProjects);
        $this->assertEquals(77, $totalInquiries);

        // 3. Measure dashboard queries under heavy volume
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseScaled = $this->actingAs($this->admin)->get('/admin');
        $responseScaled->assertStatus(200);

        $scaledQueries = DB::getQueryLog();
        $scaledCount = count($scaledQueries);
        DB::disableQueryLog();

        // Assert query count does not increase with volume (budget <= 5 queries)
        $this->assertLessThanOrEqual(
            max(5, $baseQueries),
            $scaledCount,
            sprintf(
                "Dashboard query budget exceeded under volume! Executed %d queries.\nQueries: %s",
                $scaledCount,
                json_encode(array_column($scaledQueries, 'query'), JSON_PRETTY_PRINT)
            )
        );

        // Assert that recent lists are strictly capped at 5 items
        $responseScaled->assertInertia(function (Assert $page) use ($totalProjects, $totalInquiries) {
            $page->component('Admin/Dashboard/Index')
                ->where('metrics.total_projects', $totalProjects)
                ->where('metrics.featured_projects', 35) // 20 + 15
                ->where('metrics.published_projects', 35) // 20 + 15
                ->where('metrics.total_inquiries', $totalInquiries)
                ->where('metrics.new_inquiries', 25) // 15 'new' + 10 'baru'
                ->has('recent_projects', 5)
                ->has('recent_inquiries', 5);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Empirical Stress Suite 4: /admin/inquiries Query Budget & Scaling
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Stress Test: /admin/inquiries query count remains strictly constant
     * O(1) regardless of 100+ records and status aggregations.
     */
    public function test_admin_inquiries_index_query_budget_strictly_constant_under_volume(): void
    {
        // 1. Baseline with 3 inquiries
        Inquiry::factory()->create(['status' => 'baru']);
        Inquiry::factory()->create(['status' => 'dihubungi']);
        Inquiry::factory()->create(['status' => 'diarsipkan']);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseBase = $this->actingAs($this->admin)->get('/admin/inquiries');
        $responseBase->assertStatus(200);

        $baseCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        // 2. Scale up: Seed 100 more inquiries (total 103)
        Inquiry::factory()->count(30)->create(['status' => 'new']);
        Inquiry::factory()->count(10)->create(['status' => 'baru']);
        Inquiry::factory()->count(30)->create(['status' => 'contacted']);
        Inquiry::factory()->count(10)->create(['status' => 'dihubungi']);
        Inquiry::factory()->count(10)->create(['status' => 'archived']);
        Inquiry::factory()->count(10)->create(['status' => 'diarsipkan']);

        $this->assertEquals(103, Inquiry::count());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseScaled = $this->actingAs($this->admin)->get('/admin/inquiries');
        $responseScaled->assertStatus(200);

        $scaledQueries = DB::getQueryLog();
        $scaledCount = count($scaledQueries);
        DB::disableQueryLog();

        // Query count for 103 inquiries must be strictly equal to baseline (O(1))
        $this->assertEquals(
            $baseCount,
            $scaledCount,
            sprintf(
                "Inquiries index query scaling regression! Baseline: %d queries, 103 records: %d queries.\nQueries: %s",
                $baseCount,
                $scaledCount,
                json_encode(array_column($scaledQueries, 'query'), JSON_PRETTY_PRINT)
            )
        );

        $this->assertLessThanOrEqual(
            4,
            $scaledCount,
            "Total queries on /admin/inquiries ({$scaledCount}) exceeded strict ceiling of 4."
        );

        // Verify status counts match exact ground truth
        $responseScaled->assertInertia(function (Assert $page) {
            $page->component('Admin/Inquiries/Index')
                ->where('inquiries.total', 103)
                ->where('inquiries.per_page', 15)
                ->has('inquiries.data', 15)
                ->where('status_counts.all', 103)
                ->where('status_counts.baru', 41) // 1 base + 30 new + 10 baru
                ->where('status_counts.dihubungi', 41) // 1 base + 30 contacted + 10 dihubungi
                ->where('status_counts.diarsipkan', 21); // 1 base + 10 archived + 10 diarsipkan
        });
    }

    /**
     * Empirical Stress Test: Filtered inquiries endpoints execute bounded queries under volume.
     */
    public function test_admin_inquiries_status_filter_and_search_query_budget(): void
    {
        Inquiry::factory()->count(40)->create(['status' => 'baru', 'name' => 'Klien Taman Surya']);
        Inquiry::factory()->count(40)->create(['status' => 'dihubungi', 'name' => 'Klien Villa Bukit']);
        Inquiry::factory()->count(20)->create(['status' => 'diarsipkan', 'name' => 'Klien Urban Oasis']);

        $filterScenarios = [
            '/admin/inquiries?status=baru',
            '/admin/inquiries?status=dihubungi',
            '/admin/inquiries?status=diarsipkan',
            '/admin/inquiries?search=Surya',
        ];

        foreach ($filterScenarios as $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $response = $this->actingAs($this->admin)->get($url);
            $response->assertStatus(200);

            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();

            $this->assertLessThanOrEqual(
                4,
                $queryCount,
                "URL {$url} executed {$queryCount} queries (budget: <= 4)."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Empirical Stress Suite 5: Cascade Deletion & Multi-Upload Stress
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Stress Test: Project cascade deletion cleanly removes project,
     * all associated gallery image records, and all storage files.
     */
    public function test_project_cascade_deletion_cleans_gallery_and_files_under_volume(): void
    {
        Storage::fake('public');

        $coverFile = UploadedFile::fake()->image('cover.jpg', 800, 600);
        $coverPath = $coverFile->store('projects', 'public');

        $masterplanFile = UploadedFile::fake()->image('masterplan.jpg', 1000, 800);
        $masterplanPath = $masterplanFile->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $coverPath,
            'masterplan_image' => $masterplanPath,
        ]);

        // Create 25 gallery images with storage files
        $galleryFilePaths = [];
        for ($i = 1; $i <= 25; $i++) {
            $imageFile = UploadedFile::fake()->image("gallery_{$i}.jpg", 640, 480);
            $storedPath = $imageFile->store("projects/{$project->id}/gallery", 'public');
            $galleryFilePaths[] = $storedPath;

            ProjectImage::factory()->create([
                'project_id' => $project->id,
                'image_path' => $storedPath,
                'sort_order' => $i,
            ]);
        }

        $this->assertEquals(25, $project->images()->count());
        Storage::disk('public')->assertExists($coverPath);
        Storage::disk('public')->assertExists($masterplanPath);
        foreach ($galleryFilePaths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        // Delete project
        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success');

        // Verify database cascade deletion
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertEquals(0, ProjectImage::where('project_id', $project->id)->count());

        // Verify storage file cleanup
        Storage::disk('public')->assertMissing($coverPath);
        Storage::disk('public')->assertMissing($masterplanPath);
        foreach ($galleryFilePaths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    /**
     * Empirical Stress Test: Gallery multi-upload and batch reordering perform efficiently
     * and persist strict sort orders.
     */
    public function test_gallery_multi_upload_and_batch_reordering_performance(): void
    {
        Storage::fake('public');

        $project = Project::factory()->create();

        // 1. Multi-upload 8 images simultaneously
        $files = [];
        $captions = [];
        for ($i = 0; $i < 8; $i++) {
            $files[] = UploadedFile::fake()->image("upload_{$i}.jpg", 500, 500);
            $captions[] = "Takarir foto ke-{$i}";
        }

        $responseUpload = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => $files,
            'captions' => $captions,
        ]);
        $responseUpload->assertRedirect();
        $responseUpload->assertSessionHas('success');

        $this->assertEquals(8, $project->images()->count());

        // Verify sort order increments sequentially (1..8)
        $images = $project->images()->orderBy('sort_order', 'asc')->get();
        for ($i = 0; $i < 8; $i++) {
            $this->assertEquals($i + 1, $images[$i]->sort_order);
            $this->assertEquals("Takarir foto ke-{$i}", $images[$i]->caption);
        }

        // 2. Batch reorder all 8 images in reverse order
        $reversedOrder = [];
        for ($i = 7; $i >= 0; $i--) {
            $reversedOrder[] = [
                'id' => $images[$i]->id,
                'sort_order' => 8 - $i,
            ];
        }

        $responseReorder = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images/reorder", [
            'orders' => $reversedOrder,
        ]);
        $responseReorder->assertRedirect();
        $responseReorder->assertSessionHas('success');

        // Verify updated sort orders in database
        $reorderedImages = $project->images()->orderBy('sort_order', 'asc')->get();
        $this->assertEquals($images[7]->id, $reorderedImages[0]->id);
        $this->assertEquals($images[0]->id, $reorderedImages[7]->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Empirical Stress Suite 6: Edge Cases & Adversarial Inputs Query Bounds
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Stress Test: Zero-state endpoints execute bounded queries without error.
     */
    public function test_zero_state_admin_endpoints_query_budgets(): void
    {
        // 1. Projects index with 0 projects
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseProjects = $this->actingAs($this->admin)->get('/admin/projects');
        $responseProjects->assertStatus(200);

        $projectsZeroQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            3,
            $projectsZeroQueries,
            "Zero state /admin/projects exceeded 3 queries."
        );

        // 2. Dashboard with 0 records
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseDashboard = $this->actingAs($this->admin)->get('/admin');
        $responseDashboard->assertStatus(200);

        $dashboardZeroQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            5,
            $dashboardZeroQueries,
            "Zero state /admin dashboard exceeded 5 queries."
        );

        $responseDashboard->assertInertia(function (Assert $page) {
            $page->component('Admin/Dashboard/Index')
                ->where('metrics.total_projects', 0)
                ->where('metrics.featured_projects', 0)
                ->where('metrics.published_projects', 0)
                ->where('metrics.total_inquiries', 0)
                ->where('metrics.new_inquiries', 0)
                ->has('recent_projects', 0)
                ->has('recent_inquiries', 0);
        });

        // 3. Inquiries index with 0 inquiries
        DB::flushQueryLog();
        DB::enableQueryLog();

        $responseInquiries = $this->actingAs($this->admin)->get('/admin/inquiries');
        $responseInquiries->assertStatus(200);

        $inquiriesZeroQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            3,
            $inquiriesZeroQueries,
            "Zero state /admin/inquiries exceeded 3 queries."
        );
    }

    /**
     * Empirical Stress Test: Project edit with 0 images executes strictly minimal queries.
     */
    public function test_project_edit_view_with_zero_images(): void
    {
        $project = Project::factory()->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");
        $response->assertStatus(200);

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(
            3,
            $queryCount,
            "Edit view with 0 images executed {$queryCount} queries (budget: <= 3)."
        );

        $response->assertInertia(function (Assert $page) use ($project) {
            $page->component('Admin/Projects/Edit')
                ->where('project.id', $project->id)
                ->has('project.images', 0);
        });
    }

    /**
     * Empirical Stress Test: Adversarial SQL injection strings and abnormal query parameters
     * maintain query budget bounds and do not cause syntax or performance failures.
     */
    public function test_adversarial_search_parameters_maintain_bounded_queries(): void
    {
        Project::factory()->count(10)->create();
        Inquiry::factory()->count(10)->create();

        $adversarialProjectSearches = [
            "/admin/projects?search=" . urlencode("' OR '1'='1' -- "),
            "/admin/projects?search=" . urlencode(str_repeat('A', 500)),
            "/admin/projects?featured=invalid_string",
            "/admin/projects?featured=-1",
        ];

        foreach ($adversarialProjectSearches as $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $response = $this->actingAs($this->admin)->get($url);
            $response->assertStatus(200);

            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();

            $this->assertLessThanOrEqual(
                4,
                $queryCount,
                "Adversarial request {$url} executed {$queryCount} queries (budget <= 4)."
            );
        }

        $adversarialInquirySearches = [
            "/admin/inquiries?search=" . urlencode("'; DROP TABLE inquiries; --"),
            "/admin/inquiries?search=" . urlencode(str_repeat('B', 500)),
            "/admin/inquiries?status=unknown_status_slug",
        ];

        foreach ($adversarialInquirySearches as $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();

            $response = $this->actingAs($this->admin)->get($url);
            $response->assertStatus(200);

            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();

            $this->assertLessThanOrEqual(
                4,
                $queryCount,
                "Adversarial request {$url} executed {$queryCount} queries (budget <= 4)."
            );
        }
    }
}
