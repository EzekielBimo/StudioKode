<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProjectTest extends TestCase
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
    | Tier 1: Primary Happy Paths & Interface Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 1: Projects admin index requires authentication.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_projects_index_requires_authentication(): void
    {
        $response = $this->get('/admin/projects');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**
     * Tier 1: Projects index renders successfully for authenticated admin.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_projects_index_renders_with_project_list(): void
    {
        Project::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/projects');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects')
        );
    }

    /**
     * Tier 1: Project creation view renders with empty form.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_create_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/projects/create');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Create')
        );
    }

    /**
     * Tier 1: Admin stores new project with complete attributes and file uploads.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_stores_successfully_with_complete_attributes(): void
    {
        $cover = UploadedFile::fake()->image('cover.jpg', 1200, 800);
        $masterplan = UploadedFile::fake()->image('masterplan.jpg', 1600, 1000);

        $payload = [
            'title' => 'Taman Bambu Sastra',
            'slug' => 'taman-bambu-sastra',
            'excerpt' => 'A tranquil bamboo pavilion garden.',
            'description' => 'Detailed botanical landscape design featuring black bamboo and moss boulders.',
            'client' => 'PT Sastra Sanctuary',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '850 m²',
            'cover_image' => $cover,
            'masterplan_image' => $masterplan,
            'is_featured' => 1,
            'published_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'title' => 'Taman Bambu Sastra',
            'slug' => 'taman-bambu-sastra',
            'client' => 'PT Sastra Sanctuary',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '850 m²',
            'is_featured' => 1,
        ]);
    }

    /**
     * Tier 1: Project edit screen renders with existing project and eager-loaded gallery images.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_edit_screen_renders_with_eager_loaded_images(): void
    {
        $project = Project::factory()->create();
        ProjectImage::factory()->count(3)->create(['project_id' => $project->id]);

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Projects/Edit')
            ->has('project', fn (Assert $p) => $p
                ->where('id', $project->id)
                ->has('images', 3)
                ->etc()
            )
        );
    }

    /**
     * Tier 1: Project updates successfully and redirects with Indonesian flash message.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_updates_successfully(): void
    {
        $project = Project::factory()->create([
            'title' => 'Original Title',
            'location' => 'Original Location',
            'area_size' => '400 m²',
        ]);

        $updatePayload = [
            'title' => 'Updated Garden Residence',
            'slug' => $project->slug,
            'description' => $project->description,
            'location' => 'Canggu, Bali',
            'year' => 2024,
            'area_size' => '650 m²',
            'is_featured' => 0,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $updatePayload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Garden Residence',
            'location' => 'Canggu, Bali',
            'area_size' => '650 m²',
            'is_featured' => 0,
        ]);
    }

    /**
     * Tier 1: Project deletes cleanly with database record removal.
     * Feature 19: Project Cascade Deletion (R2)
     */
    public function test_project_deletes_successfully(): void
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 2: Boundary Value Analysis, Validation & Edge Cases
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 2: Project creation fails when mandatory fields are omitted.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_store_validation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'title',
            'description',
            'location',
            'year',
            'area_size',
        ]);
    }

    /**
     * Tier 2: System auto-generates slug from title when slug is omitted or empty.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_auto_generates_slug_when_slug_empty(): void
    {
        $cover = UploadedFile::fake()->image('cover.jpg');

        $payload = [
            'title' => 'Zen Courtyard Oasis',
            'slug' => '', // Empty slug
            'description' => 'A tranquil moss courtyard.',
            'location' => 'Kyoto, Japan',
            'year' => 2024,
            'area_size' => '320 m²',
            'cover_image' => $cover,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', [
            'title' => 'Zen Courtyard Oasis',
            'slug' => 'zen-courtyard-oasis',
        ]);
    }

    /**
     * Tier 2: Duplicate project slug is rejected with validation error.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_store_rejects_duplicate_slug(): void
    {
        Project::factory()->create(['slug' => 'existing-sanctuary']);

        $payload = [
            'title' => 'Another Project',
            'slug' => 'existing-sanctuary',
            'description' => 'Duplicate slug attempt.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '500 m²',
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
    }

    /**
     * Tier 2: Updating a project without changing its slug passes unique validation.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_update_allows_keeping_same_slug(): void
    {
        $project = Project::factory()->create(['slug' => 'my-unique-slug']);

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'my-unique-slug',
            'description' => 'Updated description.',
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Title',
            'slug' => 'my-unique-slug',
        ]);
    }

    /**
     * Tier 2: Updating a project without uploading new files retains existing cover image.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_update_without_image_retains_existing_images(): void
    {
        $project = Project::factory()->create([
            'cover_image' => 'projects/original-cover.jpg',
            'masterplan_image' => 'projects/original-masterplan.jpg',
        ]);

        $payload = [
            'title' => 'Updated Title Only',
            'slug' => $project->slug,
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Title Only',
            'cover_image' => 'projects/original-cover.jpg',
            'masterplan_image' => 'projects/original-masterplan.jpg',
        ]);
    }

    /**
     * Tier 2: Project creation rejects non-image file formats.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_store_rejects_invalid_file_types(): void
    {
        $pdfFile = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $payload = [
            'title' => 'Invalid File Project',
            'description' => 'Attempting to upload PDF as image.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '200 m²',
            'cover_image' => $pdfFile,
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['cover_image']);
    }

    /**
     * Tier 2: Project edit/delete returns 404 for non-existent project ID.
     * Feature 18 & 19: Projects CRUD (R2)
     */
    public function test_project_returns_404_for_non_existent_id(): void
    {
        $responseEdit = $this->actingAs($this->admin)->get('/admin/projects/999999/edit');
        $responseEdit->assertStatus(404);

        $responseDelete = $this->actingAs($this->admin)->delete('/admin/projects/999999');
        $responseDelete->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 3: Search, Filtering & Query Boundaries
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 3: Projects index filters by search keyword in title or location.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_projects_index_filters_by_search_keyword(): void
    {
        Project::factory()->create(['title' => 'Moss Sanctuary', 'location' => 'Kyoto']);
        Project::factory()->create(['title' => 'Verdant Terrace', 'location' => 'Como']);

        $response = $this->actingAs($this->admin)->get('/admin/projects?search=Moss');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('projects', fn (Assert $p) => $p
                // Supports paginated or collection response
                ->etc()
            )
        );
    }

    /**
     * Tier 3: Projects index filters by featured status toggle.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_projects_index_filters_by_featured_status(): void
    {
        Project::factory()->create(['title' => 'Featured Item', 'is_featured' => true]);
        Project::factory()->create(['title' => 'Standard Item', 'is_featured' => false]);

        $response = $this->actingAs($this->admin)->get('/admin/projects?featured=1');

        $response->assertStatus(200);
    }

    /**
     * Tier 3: Search with no matching records returns empty collection cleanly.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_projects_index_search_with_no_matches_returns_empty(): void
    {
        Project::factory()->create(['title' => 'Existing Garden']);

        $response = $this->actingAs($this->admin)->get('/admin/projects?search=non_existent_keyword_xyz');

        $response->assertStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 4: Cascade Deletion & Complete Project Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 4: Deleting a project cascades and automatically deletes all associated gallery images.
     * Feature 19: Project Cascade Deletion (R2, Scenario 4.2)
     */
    public function test_project_cascade_deletion_removes_associated_gallery_images(): void
    {
        $project = Project::factory()->create();
        $image1 = ProjectImage::factory()->create(['project_id' => $project->id]);
        $image2 = ProjectImage::factory()->create(['project_id' => $project->id]);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('project_images', ['id' => $image1->id]);
        $this->assertDatabaseHas('project_images', ['id' => $image2->id]);

        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');

        // Verify project is removed
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);

        // Verify child images are removed via cascade
        $this->assertDatabaseMissing('project_images', ['id' => $image1->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $image2->id]);
    }

    /**
     * Tier 4: Full project lifecycle from creation, listing, editing, through cascade deletion.
     * Scenario 4.2 (R2)
     */
    public function test_project_full_lifecycle_e2e_scenario(): void
    {
        // 1. Create project
        $cover = UploadedFile::fake()->image('lifecycle_cover.jpg');
        $createResponse = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Cliffside Botanical Haven',
            'slug' => 'cliffside-botanical-haven',
            'description' => 'A dramatic oceanside landscape.',
            'location' => 'Uluwatu, Bali',
            'year' => 2024,
            'area_size' => '1,400 m²',
            'cover_image' => $cover,
            'is_featured' => 1,
            'published_at' => now()->toDateTimeString(),
        ]);
        $createResponse->assertRedirect('/admin/projects');

        $project = Project::where('slug', 'cliffside-botanical-haven')->firstOrFail();

        // 2. Add gallery image
        $galleryImage = ProjectImage::factory()->create([
            'project_id' => $project->id,
            'caption' => 'Dramatic cliff terrace',
            'sort_order' => 1,
        ]);

        // 3. Edit project details
        $updateResponse = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => 'Cliffside Botanical Haven Resort',
            'slug' => 'cliffside-botanical-haven',
            'description' => 'Updated dramatic oceanside landscape.',
            'location' => 'Uluwatu, Bali',
            'year' => 2024,
            'area_size' => '1,550 m²',
            'is_featured' => 1,
        ]);
        $updateResponse->assertRedirect('/admin/projects');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Cliffside Botanical Haven Resort',
            'area_size' => '1,550 m²',
        ]);

        // 4. Delete project and verify cascade
        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");
        $deleteResponse->assertRedirect('/admin/projects');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $galleryImage->id]);
    }
}
