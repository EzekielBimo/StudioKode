<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Home page returns 200 and renders Home/Index with featured projects and statistics.
     */
    public function test_home_page_returns_200_and_renders_inertia(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home/Index')
            ->has('featuredProjects')
            ->has('statistics')
        );
    }

    /**
     * Tier 1: Projects index returns 200 and renders Projects/Index with projects collection.
     */
    public function test_projects_index_returns_200_and_renders_inertia(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/projects');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
        );
    }

    /**
     * Tier 1: Projects show returns 200 with project details, images, and related projects.
     */
    public function test_projects_show_returns_200_with_project_details(): void
    {
        $this->seed(DatabaseSeeder::class);

        $project = Project::firstOrFail();

        $response = $this->get('/projects/' . $project->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->has('project', fn (Assert $p) => $p
                ->where('slug', $project->slug)
                ->where('title', $project->title)
                ->has('area_size')
                ->etc()
            )
            ->has('images')
        );
    }

    /**
     * Tier 1: Studio page returns 200 and renders Studio/Index.
     */
    public function test_studio_page_returns_200_and_renders_inertia(): void
    {
        $response = $this->get('/studio');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Studio/Index')
        );
    }

    /**
     * Tier 1: Services page returns 200 and renders Services/Index.
     */
    public function test_services_page_returns_200_and_renders_inertia(): void
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Services/Index')
        );
    }

    /**
     * Tier 1: Contact page returns 200 and renders Contact/Index.
     */
    public function test_contact_page_returns_200_and_renders_inertia(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Contact/Index')
        );
    }

    /**
     * Tier 2 Boundary: Project show returns 404 for a non-existent slug.
     */
    public function test_projects_show_returns_404_for_non_existent_slug(): void
    {
        $response = $this->get('/projects/non-existent-garden-sanctuary');

        $response->assertStatus(404);
    }

    /**
     * Tier 2 Boundary: Project show returns 404 for malicious or special character slug.
     */
    public function test_projects_show_handles_special_character_slug(): void
    {
        $response = $this->get('/projects/%3Cscript%3Ealert(1)%3C%2Fscript%3E');

        $response->assertStatus(404);
    }

    /**
     * Tier 2 Boundary: Project show returns 404 for empty-like slug.
     */
    public function test_projects_show_handles_numeric_unseeded_slug(): void
    {
        $response = $this->get('/projects/999999');

        $response->assertStatus(404);
    }
}
