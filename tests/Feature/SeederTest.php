<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Seeder populates exactly 6 projects.
     */
    public function test_database_seeder_populates_exactly_six_projects(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('projects', 6);
    }

    /**
     * Tier 1: Seeder populates at least 18 gallery images across projects.
     */
    public function test_database_seeder_populates_at_least_eighteen_project_images(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(18, ProjectImage::count());
    }

    /**
     * Tier 1 / Architectural Constraint: Strict omission of category table and column.
     * Specification R1: "No category table; all projects are garden/landscape designs."
     */
    public function test_database_seeder_enforces_category_omission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertFalse(
            Schema::hasTable('categories'),
            'Failed asserting that categories table does NOT exist.'
        );

        $this->assertFalse(
            Schema::hasColumn('projects', 'category_id'),
            'Failed asserting that projects table does NOT have a category_id column.'
        );
    }

    /**
     * Tier 1: All seeded projects have area_size formatted with "m²".
     */
    public function test_database_seeder_formats_area_size_consistently(): void
    {
        $this->seed(DatabaseSeeder::class);

        $projects = Project::all();

        $this->assertCount(6, $projects);

        foreach ($projects as $project) {
            $this->assertMatchesRegularExpression(
                '/^[0-9,]+\s*m²$/',
                $project->area_size,
                "Project '{$project->title}' area_size '{$project->area_size}' does not match expected format 'XXX m²'."
            );
        }
    }

    /**
     * Tier 1: At least 3 projects are marked as featured for home page showcase.
     */
    public function test_database_seeder_populates_featured_projects(): void
    {
        $this->seed(DatabaseSeeder::class);

        $featuredCount = Project::where('is_featured', true)->count();

        $this->assertGreaterThanOrEqual(3, $featuredCount);
    }

    /**
     * Tier 1: Project hasMany ProjectImage relationship works properly.
     */
    public function test_project_model_has_many_images_relationship(): void
    {
        $this->seed(DatabaseSeeder::class);

        $project = Project::firstOrFail();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $project->images);
        $this->assertGreaterThanOrEqual(1, $project->images->count());

        $firstImage = $project->images->first();
        $this->assertInstanceOf(ProjectImage::class, $firstImage);
        $this->assertEquals($project->id, $firstImage->project_id);
    }

    /**
     * Tier 2 Boundary: All projects have unique slugs, valid year numbers, and non-empty cover images.
     */
    public function test_all_projects_have_required_metadata_and_unique_slugs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $projects = Project::all();
        $slugs = $projects->pluck('slug')->toArray();

        $this->assertCount(6, array_unique($slugs), 'Project slugs must be unique.');

        foreach ($projects as $project) {
            $this->assertNotEmpty($project->title);
            $this->assertNotEmpty($project->excerpt);
            $this->assertNotEmpty($project->description);
            $this->assertNotEmpty($project->location);
            $this->assertGreaterThanOrEqual(2020, $project->year);
            $this->assertNotEmpty($project->cover_image);
        }
    }

    /**
     * Tier 2 Boundary / Cascade: Deleting a project cascades and removes its gallery images.
     */
    public function test_project_deletion_cascades_to_project_images(): void
    {
        $this->seed(DatabaseSeeder::class);

        $project = Project::firstOrFail();
        $imageId = $project->images->first()->id;

        $project->delete();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $imageId]);
    }
}
