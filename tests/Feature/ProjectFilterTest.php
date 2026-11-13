<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Tier 1: Search query filters projects by matching keyword in title.
     */
    public function test_projects_filter_by_title_keyword(): void
    {
        $response = $this->get('/projects?search=Moss');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.slug', 'moss-stone-sanctuary')
        );
    }

    /**
     * Tier 1: Search query filters projects by location.
     */
    public function test_projects_filter_by_location(): void
    {
        $response = $this->get('/projects?search=Kyoto');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 1)
            ->where('projects.0.location', fn ($loc) => str_contains($loc, 'Kyoto'))
        );
    }

    /**
     * Tier 1: Sort by newest orders projects descending by year.
     */
    public function test_projects_sort_by_newest(): void
    {
        $response = $this->get('/projects?sort=newest');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('projects', function ($projects) {
                $years = collect($projects)->pluck('year')->all();
                $sortedYears = $years;
                rsort($sortedYears);
                return $years === $sortedYears;
            })
        );
    }

    /**
     * Tier 1: Sort by oldest orders projects ascending by year.
     */
    public function test_projects_sort_by_oldest(): void
    {
        $response = $this->get('/projects?sort=oldest');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('projects', function ($projects) {
                $years = collect($projects)->pluck('year')->all();
                $sortedYears = $years;
                sort($sortedYears);
                return $years === $sortedYears;
            })
        );
    }

    /**
     * Tier 1: Sort by largest area size in m² parses formatted string (e.g. "2,100 m²" vs "380 m²")
     * and orders descending by numeric area value.
     */
    public function test_projects_sort_by_largest_area_size(): void
    {
        $response = $this->get('/projects?sort=area_desc');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
            ->where('projects', function ($projects) {
                $numericAreas = collect($projects)->map(function ($p) {
                    return (int) preg_replace('/[^0-9]/', '', $p['area_size']);
                })->all();

                $sortedAreas = $numericAreas;
                rsort($sortedAreas);

                // First should be the largest area (e.g. 2100 m² for Coastal Dune)
                return $numericAreas === $sortedAreas && $numericAreas[0] >= 2000;
            })
        );
    }

    /**
     * Tier 2 Boundary: Search query with zero matching results returns empty collection.
     */
    public function test_projects_search_with_no_matches_returns_empty_collection(): void
    {
        $response = $this->get('/projects?search=nonexistent_botanical_keyword_xyz');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /**
     * Tier 2 Boundary: Search query with regex metacharacters does not cause internal error.
     */
    public function test_projects_search_handles_regex_metacharacters(): void
    {
        $response = $this->get('/projects?search=.*+?^$()[]{}');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 0)
        );
    }

    /**
     * Tier 2 Boundary: Whitespace-only search query returns all projects without filtering.
     */
    public function test_projects_search_with_whitespace_returns_all_projects(): void
    {
        $response = $this->get('/projects?search=%20%20%20');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects', 6)
        );
    }

    /**
     * Tier 3 Pairwise: Combining keyword search and area sorting.
     */
    public function test_projects_pairwise_search_and_sort(): void
    {
        $response = $this->get('/projects?search=Dune&sort=area_desc');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Index')
            ->has('projects')
            ->where('projects', function ($projects) {
                foreach ($projects as $project) {
                    if (!str_contains($project['title'] . $project['description'], 'Dune')) {
                        return false;
                    }
                }
                return count($projects) >= 1;
            })
        );
    }
}
