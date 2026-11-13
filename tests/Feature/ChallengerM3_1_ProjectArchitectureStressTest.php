<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM3_1_ProjectArchitectureStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
        ]);
        Storage::fake('public');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 1: Route Model Binding Scoping ({project:id})
    |--------------------------------------------------------------------------
    */

    public function test_admin_route_binds_by_integer_id(): void
    {
        $project = Project::factory()->create([
            'title' => 'Botanical Pavilion',
            'slug' => 'botanical-pavilion',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Edit')
            ->where('project.id', $project->id)
            ->where('project.slug', 'botanical-pavilion')
        );
    }

    public function test_admin_route_returns_404_when_accessed_via_slug(): void
    {
        $project = Project::factory()->create([
            'title' => 'Slug Binding Test',
            'slug' => 'slug-binding-test-unique',
        ]);

        // Accessing admin edit route with slug instead of ID should 404 because scoping is project:id
        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->slug}/edit");
        $response->assertStatus(404);
    }

    public function test_nested_admin_gallery_route_binds_by_integer_id(): void
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/gallery");
        $response->assertStatus(200);
    }

    public function test_public_project_show_route_binds_by_slug(): void
    {
        $project = Project::factory()->create([
            'slug' => 'public-botanical-showcase',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get("/projects/{$project->slug}");
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show')
            ->where('project.slug', 'public-botanical-showcase')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Search and Filter Queries
    |--------------------------------------------------------------------------
    */

    public function test_projects_search_by_title_and_location(): void
    {
        Project::factory()->create(['title' => 'Taman Hutan Tropis', 'location' => 'Bogor']);
        Project::factory()->create(['title' => 'Resor Pesisir Tebing', 'location' => 'Uluwatu']);
        Project::factory()->create(['title' => 'Pavilion Bambu Minimalis', 'location' => 'Bogor']);

        // Search by location
        $resLocation = $this->actingAs($this->admin)->get('/admin/projects?search=Bogor');
        $resLocation->assertStatus(200);
        $resLocation->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 2)
        );

        // Search by title
        $resTitle = $this->actingAs($this->admin)->get('/admin/projects?search=Tebing');
        $resTitle->assertStatus(200);
        $resTitle->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 1)
        );
    }

    public function test_projects_search_resilient_to_sql_wildcards_and_whitespace(): void
    {
        Project::factory()->create(['title' => 'Taman 100% Organik', 'location' => 'Bandung']);

        // Search with whitespace and percent
        $response = $this->actingAs($this->admin)->get('/admin/projects?search=%20%25%20');
        $response->assertStatus(200);
    }

    public function test_projects_filter_by_featured_boolean_variants(): void
    {
        Project::factory()->create(['title' => 'Project A', 'is_featured' => true]);
        Project::factory()->create(['title' => 'Project B', 'is_featured' => false]);
        Project::factory()->create(['title' => 'Project C', 'is_featured' => false]);

        // Filter 1
        $res1 = $this->actingAs($this->admin)->get('/admin/projects?featured=1');
        $res1->assertInertia(fn (Assert $page) => $page->has('projects.data', 1));

        // Filter 0
        $res0 = $this->actingAs($this->admin)->get('/admin/projects?featured=0');
        $res0->assertInertia(fn (Assert $page) => $page->has('projects.data', 2));

        // Filter 'true' string
        $resTrue = $this->actingAs($this->admin)->get('/admin/projects?featured=true');
        $resTrue->assertInertia(fn (Assert $page) => $page->has('projects.data', 1));

        // Filter 'false' string
        $resFalse = $this->actingAs($this->admin)->get('/admin/projects?featured=false');
        $resFalse->assertInertia(fn (Assert $page) => $page->has('projects.data', 2));
    }

    public function test_projects_combined_search_and_featured_filter(): void
    {
        Project::factory()->create(['title' => 'Botanika Sanctuary', 'is_featured' => true]);
        Project::factory()->create(['title' => 'Botanika Villa', 'is_featured' => false]);
        Project::factory()->create(['title' => 'Zen Haven', 'is_featured' => true]);

        $response = $this->actingAs($this->admin)->get('/admin/projects?search=Botanika&featured=1');
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 1)
            ->where('projects.data.0.title', 'Botanika Sanctuary')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Eager Loading and Query Budgets (N+1 Prevention)
    |--------------------------------------------------------------------------
    */

    public function test_projects_index_query_count_is_constant_regardless_of_image_volume(): void
    {
        // Create 15 projects each with 4 images
        for ($i = 0; $i < 15; $i++) {
            $p = Project::factory()->create();
            ProjectImage::factory()->count(4)->create(['project_id' => $p->id]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->admin)->get('/admin/projects');
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should execute <= 4 queries (session/user + count + select paginated projects with images_count)
        // Strictly prevents N+1
        $this->assertLessThanOrEqual(4, count($queries), 'Projects index executed too many queries: ' . count($queries));
    }

    public function test_project_edit_view_eager_loads_ordered_images(): void
    {
        $project = Project::factory()->create();
        ProjectImage::factory()->create(['project_id' => $project->id, 'sort_order' => 3, 'caption' => 'C']);
        ProjectImage::factory()->create(['project_id' => $project->id, 'sort_order' => 1, 'caption' => 'A']);
        ProjectImage::factory()->create(['project_id' => $project->id, 'sort_order' => 2, 'caption' => 'B']);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Verifies eager loading in <= 4 queries
        $this->assertLessThanOrEqual(4, count($queries));

        // Verifies ordering
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Edit')
            ->where('project.images.0.caption', 'A')
            ->where('project.images.1.caption', 'B')
            ->where('project.images.2.caption', 'C')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: File Storage and Cascade Deletion Cleanup
    |--------------------------------------------------------------------------
    */

    public function test_project_store_and_update_manages_storage_files(): void
    {
        $cover1 = UploadedFile::fake()->image('cover1.jpg');
        $masterplan1 = UploadedFile::fake()->image('plan1.jpg');

        // 1. Create with files
        $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Storage Test Garden',
            'description' => 'Testing file lifecycle',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '500 m²',
            'cover_image' => $cover1,
            'masterplan_image' => $masterplan1,
        ]);

        $project = Project::where('title', 'Storage Test Garden')->firstOrFail();
        $this->assertNotNull($project->cover_image);
        $this->assertNotNull($project->masterplan_image);
        Storage::disk('public')->assertExists($project->cover_image);
        Storage::disk('public')->assertExists($project->masterplan_image);

        $oldCover = $project->cover_image;
        $oldMasterplan = $project->masterplan_image;

        // 2. Update with new files (replacing existing)
        $cover2 = UploadedFile::fake()->image('cover2.jpg');
        $masterplan2 = UploadedFile::fake()->image('plan2.jpg');

        $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => 'Storage Test Garden Updated',
            'description' => 'Updated testing file lifecycle',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '500 m²',
            'cover_image' => $cover2,
            'masterplan_image' => $masterplan2,
        ]);

        $project->refresh();
        $this->assertNotEquals($oldCover, $project->cover_image);
        $this->assertNotEquals($oldMasterplan, $project->masterplan_image);

        // Old files should be deleted from storage
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertMissing($oldMasterplan);

        // New files should exist in storage
        Storage::disk('public')->assertExists($project->cover_image);
        Storage::disk('public')->assertExists($project->masterplan_image);
    }

    public function test_project_cascade_deletion_deletes_all_storage_and_database_assets(): void
    {
        $cover = UploadedFile::fake()->image('project_cover.jpg');
        $masterplan = UploadedFile::fake()->image('project_masterplan.jpg');
        $galleryPhoto1 = UploadedFile::fake()->image('gallery1.jpg');
        $galleryPhoto2 = UploadedFile::fake()->image('gallery2.jpg');

        $coverPath = $cover->store('projects', 'public');
        $masterplanPath = $masterplan->store('projects', 'public');
        $g1Path = $galleryPhoto1->store('projects/gallery', 'public');
        $g2Path = $galleryPhoto2->store('projects/gallery', 'public');

        $project = Project::factory()->create([
            'cover_image' => $coverPath,
            'masterplan_image' => $masterplanPath,
        ]);

        $img1 = ProjectImage::factory()->create(['project_id' => $project->id, 'image_path' => $g1Path]);
        $img2 = ProjectImage::factory()->create(['project_id' => $project->id, 'image_path' => $g2Path]);

        Storage::disk('public')->assertExists($coverPath);
        Storage::disk('public')->assertExists($masterplanPath);
        Storage::disk('public')->assertExists($g1Path);
        Storage::disk('public')->assertExists($g2Path);

        // Delete project
        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");
        $response->assertRedirect('/admin/projects');

        // Assert DB rows missing
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $img1->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $img2->id]);

        // Assert all physical storage files removed
        Storage::disk('public')->assertMissing($coverPath);
        Storage::disk('public')->assertMissing($masterplanPath);
        Storage::disk('public')->assertMissing($g1Path);
        Storage::disk('public')->assertMissing($g2Path);
    }

    public function test_project_deletion_handles_external_urls_without_error(): void
    {
        $project = Project::factory()->create([
            'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26',
            'masterplan_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c',
        ]);
        $galleryImage = ProjectImage::factory()->create([
            'project_id' => $project->id,
            'image_path' => 'https://images.unsplash.com/photo-gallery-12345',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $galleryImage->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Slug Generation, Collision and Custom Slugs
    |--------------------------------------------------------------------------
    */

    public function test_custom_slug_normalized_to_url_friendly_format(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Resor Pesisir Alami',
            'slug' => 'Resor Pesisir Alami & Estetika!',
            'description' => 'Deskripsi proyek landscape arsitektur.',
            'location' => 'Lombok',
            'year' => 2024,
            'area_size' => '750 m²',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', [
            'title' => 'Resor Pesisir Alami',
            'slug' => 'resor-pesisir-alami-estetika',
        ]);
    }

    public function test_slug_uniqueness_on_update_prevents_collision(): void
    {
        $projectA = Project::factory()->create(['slug' => 'slug-proyek-a']);
        $projectB = Project::factory()->create(['slug' => 'slug-proyek-b']);

        // Try updating project B to have slug of project A
        $response = $this->actingAs($this->admin)->put("/admin/projects/{$projectB->id}", [
            'title' => $projectB->title,
            'slug' => 'slug-proyek-a',
            'description' => $projectB->description,
            'location' => $projectB->location,
            'year' => $projectB->year,
            'area_size' => $projectB->area_size,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
        $this->assertEquals('slug-proyek-b', $projectB->fresh()->slug);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 6: Validation, Indonesian Error Messages & Boundaries
    |--------------------------------------------------------------------------
    */

    public function test_validation_rejects_empty_inputs_with_indonesian_messages(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/projects', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'title' => 'Judul proyek wajib diisi.',
            'description' => 'Deskripsi proyek wajib diisi.',
            'location' => 'Lokasi proyek wajib diisi.',
            'year' => 'Tahun proyek wajib diisi.',
            'area_size' => 'Luas area proyek wajib diisi.',
        ]);
    }

    public function test_validation_year_boundary_limits(): void
    {
        $base = [
            'title' => 'Boundary Year Project',
            'description' => 'Description',
            'location' => 'Bali',
            'area_size' => '500 m²',
        ];

        // Below 1900
        $resLow = $this->actingAs($this->admin)->post('/admin/projects', array_merge($base, ['year' => 1899]));
        $resLow->assertSessionHasErrors(['year']);

        // Above 2100
        $resHigh = $this->actingAs($this->admin)->post('/admin/projects', array_merge($base, ['year' => 2101]));
        $resHigh->assertSessionHasErrors(['year']);

        // Exact boundary 1900
        $res1900 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($base, ['year' => 1900, 'slug' => 'year-1900']));
        $res1900->assertSessionDoesntHaveErrors();

        // Exact boundary 2100
        $res2100 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($base, ['year' => 2100, 'slug' => 'year-2100']));
        $res2100->assertSessionDoesntHaveErrors();
    }

    public function test_published_at_can_be_set_and_cleared_to_draft(): void
    {
        $datetime = '2025-05-10 14:30:00';
        $project = Project::factory()->create([
            'published_at' => Carbon::parse($datetime),
        ]);

        $this->assertNotNull($project->published_at);

        // Clear publication date (draft mode)
        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => $project->title,
            'slug' => $project->slug,
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
            'published_at' => '',
        ]);

        $response->assertStatus(302);
        $this->assertNull($project->fresh()->published_at);
    }
}
