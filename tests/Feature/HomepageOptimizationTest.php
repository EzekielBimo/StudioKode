<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Test that the homepage returns HTTP 200 and loads Inertia Home/Index component with required props.
     */
    public function test_homepage_returns_http_200_and_renders_home_index_with_props(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects')
            ->has('statistics')
            ->has('philosophyHighlights')
        );
    }

    /**
     * Test that HomeController caches featured projects under 'home.featured_projects'.
     */
    public function test_homepage_caches_featured_projects(): void
    {
        Cache::forget('home.featured_projects');
        $this->assertFalse(Cache::has('home.featured_projects'));

        $response = $this->get('/');
        $response->assertStatus(200);

        $this->assertTrue(Cache::has('home.featured_projects'));
        $cachedProjects = Cache::get('home.featured_projects');
        $this->assertNotEmpty($cachedProjects);
        $this->assertLessThanOrEqual(3, count($cachedProjects));
    }

    /**
     * Test that storing a new project clears the home.featured_projects cache.
     */
    public function test_admin_project_store_invalidates_featured_projects_cache(): void
    {
        $admin = User::factory()->create();
        Cache::put('home.featured_projects', ['dummy'], 3600);
        $this->assertTrue(Cache::has('home.featured_projects'));

        $response = $this->actingAs($admin)->post(route('admin.projects.store'), [
            'title' => 'New Sanctuaried Retreat',
            'location' => 'Sintra, Portugal',
            'year' => 2025,
            'area_size' => '1,450 m²',
            'description' => 'A tranquil retreat embedded within ancient granite monoliths.',
            'is_featured' => true,
            'published_at' => now()->toDateTimeString(),
        ]);

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertFalse(Cache::has('home.featured_projects'));
    }

    /**
     * Test that updating an existing project clears the home.featured_projects cache.
     */
    public function test_admin_project_update_invalidates_featured_projects_cache(): void
    {
        $admin = User::factory()->create();
        $project = Project::first();
        Cache::put('home.featured_projects', ['dummy'], 3600);
        $this->assertTrue(Cache::has('home.featured_projects'));

        $response = $this->actingAs($admin)->put(route('admin.projects.update', $project), [
            'title' => 'Updated Sanctuary Title',
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
            'description' => $project->description,
            'is_featured' => true,
            'published_at' => now()->toDateTimeString(),
        ]);

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertFalse(Cache::has('home.featured_projects'));
    }

    /**
     * Test that deleting a project clears the home.featured_projects cache.
     */
    public function test_admin_project_destroy_invalidates_featured_projects_cache(): void
    {
        $admin = User::factory()->create();
        $project = Project::first();
        Cache::put('home.featured_projects', ['dummy'], 3600);
        $this->assertTrue(Cache::has('home.featured_projects'));

        $response = $this->actingAs($admin)->delete(route('admin.projects.destroy', $project));

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertFalse(Cache::has('home.featured_projects'));
    }

    /**
     * Test that the root Blade template renders the instant branded loading skeleton.
     */
    public function test_root_template_renders_instant_branded_skeleton(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('id="app-skeleton"', $content);
        $this->assertStringContainsString('StudioKode', $content);
        $this->assertStringContainsString('Landscape Architecture', $content);
        $this->assertStringContainsString('id="app"', $content);
        $this->assertStringContainsString('&display=swap', $content);
    }
}
