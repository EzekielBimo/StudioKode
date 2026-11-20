<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Requirement R1: Database Seeder populates projects in both "Project Design" and "Project Construction".
     */
    public function test_database_seeder_populates_projects_in_both_categories(): void
    {
        $designCount = Project::where('category', Project::CATEGORY_DESIGN)->count();
        $constructionCount = Project::where('category', Project::CATEGORY_CONSTRUCTION)->count();

        $this->assertSame(3, $designCount, 'Expected exactly 3 projects in Project Design category');
        $this->assertSame(3, $constructionCount, 'Expected exactly 3 projects in Project Construction category');
        $this->assertSame(6, Project::count(), 'Expected total 6 projects');
    }

    /**
     * Requirement R2: Projects portfolio filters by category "design".
     */
    public function test_projects_index_filters_by_category_slug_design(): void
    {
        $response = $this->get('/projects?category=design');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 3)
            ->where('projects', function ($projects) {
                foreach ($projects as $project) {
                    if ($project['category'] !== Project::CATEGORY_DESIGN) {
                        return false;
                    }
                }
                return true;
            })
            ->where('activeCategory', 'design')
            ->where('categoryCounts.all', 6)
            ->where('categoryCounts.design', 3)
            ->where('categoryCounts.construction', 3)
        );
    }

    /**
     * Requirement R2: Projects portfolio filters by category "construction".
     */
    public function test_projects_index_filters_by_category_slug_construction(): void
    {
        $response = $this->get('/projects?category=construction');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 3)
            ->where('projects', function ($projects) {
                foreach ($projects as $project) {
                    if ($project['category'] !== Project::CATEGORY_CONSTRUCTION) {
                        return false;
                    }
                }
                return true;
            })
            ->where('activeCategory', 'construction')
        );
    }

    /**
     * Requirement R2: Supports full category names in query parameter.
     */
    public function test_projects_index_supports_full_category_name_in_query(): void
    {
        $response = $this->get('/projects?category=' . urlencode('Project Design'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 3)
            ->where('projects.0.category', Project::CATEGORY_DESIGN)
        );
    }

    /**
     * Requirement R2: Category "all" or omitted returns all published projects.
     */
    public function test_projects_index_category_all_returns_all_projects(): void
    {
        $response = $this->get('/projects?category=all');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('activeCategory', 'all')
        );
    }

    /**
     * Requirement R2: Category filtering combines with keyword search.
     */
    public function test_projects_category_filter_combines_with_search_keyword(): void
    {
        // "Moss" is in Moss & Stone Sanctuary (Project Design)
        $response = $this->get('/projects?category=design&search=Moss');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'moss-stone-sanctuary')
            ->where('projects.0.category', Project::CATEGORY_DESIGN)
        );

        // Searching for "Moss" within "construction" returns 0
        $responseEmpty = $this->get('/projects?category=construction&search=Moss');
        $responseEmpty->assertStatus(200);
        $responseEmpty->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /**
     * Requirement R2: Category filtering combines with sorting.
     */
    public function test_projects_category_filter_combines_with_area_sorting(): void
    {
        $response = $this->get('/projects?category=construction&sort=area_desc');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 3)
            ->where('projects', function ($projects) {
                // Largest construction project should be Coastal Dune (2,100 m²)
                return $projects[0]['slug'] === 'coastal-dune-pine-refuge';
            })
        );
    }

    /**
     * Project Show view includes category in project and related projects.
     */
    public function test_project_show_includes_category_data(): void
    {
        $project = Project::where('slug', 'moss-stone-sanctuary')->firstOrFail();

        $response = $this->get('/projects/' . $project->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.category', Project::CATEGORY_DESIGN)
            ->has('relatedProjects', fn (Assert $rel) => $rel
                ->has('0.category')
                ->etc()
            )
        );
    }

    /**
     * Admin: Filtering projects by category in admin index.
     */
    public function test_admin_can_filter_projects_by_category(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/projects?category=' . urlencode(Project::CATEGORY_CONSTRUCTION));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 3)
            ->where('projects.data.0.category', Project::CATEGORY_CONSTRUCTION)
            ->where('filters.category', Project::CATEGORY_CONSTRUCTION)
        );
    }

    /**
     * Admin: Creating project with category saves successfully.
     */
    public function test_admin_can_create_project_with_explicit_category(): void
    {
        Storage::fake('public');
        $admin = User::first() ?? User::factory()->create();

        $payload = [
            'title' => 'Paviliun Bambu Air',
            'slug' => 'paviliun-bambu-air',
            'category' => Project::CATEGORY_CONSTRUCTION,
            'description' => 'Konstruksi lanskap paviliun bambu terintegrasi.',
            'location' => 'Bandung, Indonesia',
            'year' => 2024,
            'area_size' => '450 m²',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 800, 600),
        ];

        $response = $this->actingAs($admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');

        $this->assertDatabaseHas('projects', [
            'slug' => 'paviliun-bambu-air',
            'category' => Project::CATEGORY_CONSTRUCTION,
        ]);
    }

    /**
     * Admin: Updating project category persists successfully.
     */
    public function test_admin_can_update_project_category(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $project = Project::where('category', Project::CATEGORY_DESIGN)->firstOrFail();

        $payload = [
            'title' => $project->title,
            'slug' => $project->slug,
            'category' => Project::CATEGORY_CONSTRUCTION,
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
        ];

        $response = $this->actingAs($admin)->put("/admin/projects/{$project->id}", $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'category' => Project::CATEGORY_CONSTRUCTION,
        ]);
    }

    /**
     * Admin: Updating project with empty category retains existing category safely without SQL exception.
     */
    public function test_admin_updating_project_with_empty_category(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $project = Project::where('category', Project::CATEGORY_CONSTRUCTION)->firstOrFail();

        $payload = [
            'title' => $project->title,
            'slug' => $project->slug,
            'category' => '',
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
        ];

        $response = $this->actingAs($admin)->put("/admin/projects/{$project->id}", $payload);
        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');

        // Confirm category was safely preserved
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'category' => Project::CATEGORY_CONSTRUCTION,
        ]);
    }

    /**
     * Admin: Creating and updating with invalid category fails validation.
     */
    public function test_admin_cannot_save_project_with_invalid_category(): void
    {
        $admin = User::first() ?? User::factory()->create();
        $project = Project::firstOrFail();

        // 1. Invalid category on store
        $storePayload = [
            'title' => 'Invalid Project Category Test',
            'category' => 'Arbitrary Nonexistent Category',
            'description' => 'Test description',
            'location' => 'Jakarta, Indonesia',
            'year' => 2024,
            'area_size' => '300 m²',
        ];
        $storeResponse = $this->actingAs($admin)->post('/admin/projects', $storePayload);
        $storeResponse->assertSessionHasErrors('category');

        // 2. Invalid category on update
        $updatePayload = [
            'title' => $project->title,
            'category' => 'Arbitrary Nonexistent Category',
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
        ];
        $updateResponse = $this->actingAs($admin)->put("/admin/projects/{$project->id}", $updatePayload);
        $updateResponse->assertSessionHasErrors('category');
    }

    /**
     * Edge case: Visiting unknown category slug returns 0 projects, 200 OK, empty state without 404 or SQL error.
     */
    public function test_projects_index_with_unknown_category_returns_empty_and_200(): void
    {
        $response = $this->get('/projects?category=invalid_random_slug');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
            ->where('activeCategory', 'invalid_random_slug')
        );
    }

    /**
     * Edge case: Visiting category with whitespace returns all projects gracefully.
     */
    public function test_projects_index_with_whitespace_category_returns_all_projects(): void
    {
        $response = $this->get('/projects?category=%20%20');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
        );
    }

    /**
     * Edge case: Category parameter supports uppercase and hyphenated slug formats.
     */
    public function test_projects_index_supports_case_insensitive_and_hyphenated_category_slugs(): void
    {
        // Uppercase
        $responseUpper = $this->get('/projects?category=DESIGN');
        $responseUpper->assertStatus(200);
        $responseUpper->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 3)
            ->where('projects.0.category', Project::CATEGORY_DESIGN)
        );

        // Hyphenated
        $responseHyphen = $this->get('/projects?category=project-construction');
        $responseHyphen->assertStatus(200);
        $responseHyphen->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 3)
            ->where('projects.0.category', Project::CATEGORY_CONSTRUCTION)
        );
    }

    /**
     * Edge case: Array parameter for category or search does not trigger fatal TypeError or 500 error.
     */
    public function test_category_array_query_parameter_is_handled_gracefully(): void
    {
        $admin = User::first() ?? User::factory()->create();

        // 1. Public route with array category and search
        $publicResponse = $this->get('/projects?category[]=design&search[]=test');
        $publicResponse->assertStatus(200);

        // 2. Admin route with array category and search
        $adminResponse = $this->actingAs($admin)->get('/admin/projects?category[]=construction&search[]=test');
        $adminResponse->assertStatus(200);
    }

    /**
     * Adversarial: Triplet filter combination (Category + Search + Sort).
     */
    public function test_projects_category_combines_simultaneously_with_search_and_sort(): void
    {
        // "Kyoto" location in Moss & Stone Sanctuary (Project Design, 380 m²)
        $response = $this->get('/projects?category=design&search=Kyoto&sort=area_desc');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'moss-stone-sanctuary')
            ->where('projects.0.category', Project::CATEGORY_DESIGN)
            ->where('filters.category', 'design')
            ->where('filters.search', 'Kyoto')
            ->where('filters.sort', 'area_desc')
        );
    }

    /**
     * Adversarial: Admin filtering by slugified category parameter works consistently.
     */
    public function test_admin_filtering_by_slugified_category_normalizes(): void
    {
        $admin = User::first() ?? User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/projects?category=design');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 3)
            ->where('projects.data.0.category', Project::CATEGORY_DESIGN)
            ->where('filters.category', 'design')
        );
    }

    /**
     * Adversarial: SQL-like string in category returns 0 projects without SQL errors.
     */
    public function test_projects_category_with_sql_injection_payload(): void
    {
        $response = $this->get('/projects?category=' . urlencode("' OR 1=1 --"));
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /**
     * Adversarial: Empty category parameter (/projects?category=) falls back to all projects.
     */
    public function test_projects_category_empty_parameter_falls_back_to_all(): void
    {
        $response = $this->get('/projects?category=');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('activeCategory', 'all')
        );
    }
}
