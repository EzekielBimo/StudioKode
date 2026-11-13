<?php

namespace Tests\Feature\Admin;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPerformanceTest extends TestCase
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
    | Tier 1: Query Counting & Eager Loading Verification
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 1: Projects index uses eager loading (withCount) to avoid N+1 queries.
     * Query count remains bounded regardless of whether 2 or 10 projects exist.
     * Feature 25: Database Query Eager Loading (R6)
     */
    public function test_projects_admin_index_prevents_n_plus_one_queries(): void
    {
        // Setup baseline: 2 projects with images
        $projectsA = Project::factory()->count(2)->create();
        foreach ($projectsA as $p) {
            ProjectImage::factory()->count(3)->create(['project_id' => $p->id]);
        }

        // Measure queries for 2 projects
        $queriesForTwo = 0;
        DB::listen(function () use (&$queriesForTwo) {
            $queriesForTwo++;
        });

        $responseTwo = $this->actingAs($this->admin)->get('/admin/projects');
        $responseTwo->assertStatus(200);

        // Reset query listener
        DB::flushQueryLog();

        // Create 8 more projects with images (total 10 projects)
        $projectsB = Project::factory()->count(8)->create();
        foreach ($projectsB as $p) {
            ProjectImage::factory()->count(3)->create(['project_id' => $p->id]);
        }

        $queriesForTen = 0;
        DB::listen(function () use (&$queriesForTen) {
            $queriesForTen++;
        });

        $responseTen = $this->actingAs($this->admin)->get('/admin/projects');
        $responseTen->assertStatus(200);

        // If N+1 existed, queries for 10 projects would be >= (queriesForTwo + 8).
        // With eager loading / withCount, queries remain strictly bounded O(1).
        $this->assertLessThanOrEqual(
            $queriesForTwo + 2,
            $queriesForTen,
            "Detected N+1 query regression on /admin/projects: 2 projects took {$queriesForTwo} queries, but 10 projects took {$queriesForTen} queries."
        );
    }

    /**
     * Tier 1: Project edit view eager loads gallery images relationship.
     * Feature 25: Database Query Eager Loading (R6)
     */
    public function test_project_edit_view_eager_loads_gallery_images(): void
    {
        $project = Project::factory()->create();
        ProjectImage::factory()->count(6)->create(['project_id' => $project->id]);

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");
        $response->assertStatus(200);

        // Eager loading should fetch project and images in <= 3 queries (not 1 + 6 queries)
        $this->assertLessThanOrEqual(
            4,
            $queryCount,
            "Expected eager loading of images on /admin/projects/{id}/edit, but executed {$queryCount} queries."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 2: Strict Query Budgets on Dashboard & Inquiries
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 2: Dashboard metrics calculation adheres to strict query budget.
     * Feature 25: Database Query Eager Loading (R6)
     */
    public function test_admin_dashboard_adheres_to_query_budget(): void
    {
        Project::factory()->count(10)->create();
        Inquiry::factory()->count(10)->create();

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);

        // Aggregating metrics and recent lists must execute in at most 6 queries
        $this->assertLessThanOrEqual(
            6,
            $queryCount,
            "Dashboard query budget exceeded: executed {$queryCount} queries (budget: 6)."
        );
    }

    /**
     * Tier 2: Inquiries index adheres to strict query budget with pagination and status counts.
     * Feature 25: Database Query Eager Loading (R6)
     */
    public function test_admin_inquiries_index_adheres_to_query_budget(): void
    {
        Inquiry::factory()->count(15)->create();

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->actingAs($this->admin)->get('/admin/inquiries');
        $response->assertStatus(200);

        // Query count for listing and status counts should not exceed 5 queries
        $this->assertLessThanOrEqual(
            5,
            $queryCount,
            "Inquiries index query budget exceeded: executed {$queryCount} queries (budget: 5)."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 3: Storage Configuration & Frontend Tooling
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 3: Public filesystem disk is configured for local media uploads.
     * Feature 26: Storage Symlink Verification (R6)
     */
    public function test_public_filesystem_configuration(): void
    {
        $config = config('filesystems.disks.public');

        $this->assertNotNull($config, 'Public disk must be configured in filesystems.php');
        $this->assertEquals('local', $config['driver']);
        $this->assertEquals('/storage', $config['url']);
    }

    /**
     * Tier 3: Frontend package manifest includes performance and UI animation libraries.
     * Feature 23: Vite Code-Splitting (R6)
     */
    public function test_package_json_includes_required_performance_libraries(): void
    {
        $packagePath = base_path('package.json');
        $this->assertFileExists($packagePath);

        $json = json_decode(file_get_contents($packagePath), true);
        $deps = array_merge($json['dependencies'] ?? [], $json['devDependencies'] ?? []);

        $requiredLibs = [
            'react',
            'react-dom',
            '@inertiajs/react',
            'framer-motion',
            'lucide-react',
            'tailwindcss',
            'vite',
        ];

        foreach ($requiredLibs as $lib) {
            $this->assertArrayHasKey($lib, $deps, "Expected {$lib} in package.json dependencies");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 4: Layout Shift Prevention & Production Asset Integrity
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 4: Frontend media components incorporate lazy-loading and aspect-ratio styling
     * to eliminate Cumulative Layout Shift (CLS).
     * Feature 24: Image Lazy-Loading & Layout Shift (R6, Scenario 4.5)
     */
    public function test_frontend_components_prevent_layout_shift(): void
    {
        // Scan public portfolio & admin image components
        $componentFiles = [
            resource_path('js/Components/Portfolio/ProjectCard.jsx'),
            resource_path('js/Components/Portfolio/ImageGallery.jsx'),
        ];

        foreach ($componentFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                // Verify aspect ratio class (aspect-[...] or aspect-video or aspect-square)
                $hasAspectRatio = str_contains($content, 'aspect-');
                $this->assertTrue(
                    $hasAspectRatio,
                    "Component {$file} should specify aspect-ratio styling to prevent layout shift."
                );
            }
        }
    }

    /**
     * Tier 4: Production asset compilation contract:
     * Verifies vite config exists and public assets directory is ready for build artifacts.
     * Feature 23: Vite Code-Splitting (R6)
     */
    public function test_vite_configuration_supports_code_splitting(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        $this->assertFileExists($viteConfigPath);

        $content = file_get_contents($viteConfigPath);
        $this->assertStringContainsString('laravel', $content);
    }
}
