<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChallengerM3_2_GalleryStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
        ]);
        $this->project = Project::factory()->create();
        Storage::fake('public');
    }

    /**
     * Helper to perform caption update on an image.
     */
    protected function updateImageCaption(ProjectImage $image, array $payload)
    {
        if (Route::has('admin.images.update')) {
            return $this->actingAs($this->admin)->put(route('admin.images.update', $image), $payload);
        }

        $res = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", $payload);
        if ($res->status() === 404) {
            return $this->actingAs($this->admin)->put("/admin/images/{$image->id}", $payload);
        }
        return $res;
    }

    /**
     * Helper to perform single image deletion.
     */
    protected function deleteImageRecord(ProjectImage $image)
    {
        if (Route::has('admin.images.destroy')) {
            return $this->actingAs($this->admin)->delete(route('admin.images.destroy', $image));
        }

        $res = $this->actingAs($this->admin)->delete("/admin/project-images/{$image->id}");
        if ($res->status() === 404) {
            return $this->actingAs($this->admin)->delete("/admin/images/{$image->id}");
        }
        return $res;
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 1: Multi-Image Upload Stress & Ordering Progression
    |--------------------------------------------------------------------------
    */

    /**
     * Multi-image upload creates sequential sort orders (1, 2, 3...) from initial zero state
     * and correctly stores physical files on public storage.
     */
    public function test_gallery_multi_upload_creates_sequential_sort_orders_from_zero(): void
    {
        $img1 = UploadedFile::fake()->image('pond1.jpg', 800, 600);
        $img2 = UploadedFile::fake()->image('pond2.png', 800, 600);
        $img3 = UploadedFile::fake()->image('pond3.webp', 800, 600);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$img1, $img2, $img3],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $images = $this->project->fresh()->images;
        $this->assertCount(3, $images);

        $this->assertEquals(1, $images[0]->sort_order);
        $this->assertEquals(2, $images[1]->sort_order);
        $this->assertEquals(3, $images[2]->sort_order);

        foreach ($images as $img) {
            $this->assertStringStartsWith('projects/gallery/', $img->image_path);
            Storage::disk('public')->assertExists($img->image_path);
        }
    }

    /**
     * Multi-image upload appends sort orders sequentially from the existing maximum sort order,
     * even when existing sort orders are sparse.
     */
    public function test_gallery_multi_upload_increments_from_existing_highest_sort_order(): void
    {
        ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'sort_order' => 15,
        ]);
        ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'sort_order' => 42,
        ]);

        $imgA = UploadedFile::fake()->image('appendA.jpg');
        $imgB = UploadedFile::fake()->image('appendB.jpg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$imgA, $imgB],
            ]);

        $response->assertStatus(302);

        $newImages = ProjectImage::where('project_id', $this->project->id)
            ->where('sort_order', '>', 42)
            ->orderBy('sort_order', 'asc')
            ->get();

        $this->assertCount(2, $newImages);
        $this->assertEquals(43, $newImages[0]->sort_order);
        $this->assertEquals(44, $newImages[1]->sort_order);
    }

    /**
     * Multi-image upload matches captions positionally with images.
     */
    public function test_gallery_multi_upload_with_matching_captions(): void
    {
        $img1 = UploadedFile::fake()->image('c1.jpg');
        $img2 = UploadedFile::fake()->image('c2.jpg');
        $img3 = UploadedFile::fake()->image('c3.jpg');

        $captions = [
            'Taman Zen dengan bebatuan andesit',
            'Kanopi pohon pule di area selasar',
            'Bunga teratai bermekaran di kolam utama',
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$img1, $img2, $img3],
                'captions' => $captions,
            ]);

        $response->assertStatus(302);

        $images = $this->project->fresh()->images;
        $this->assertCount(3, $images);
        $this->assertEquals('Taman Zen dengan bebatuan andesit', $images[0]->caption);
        $this->assertEquals('Kanopi pohon pule di area selasar', $images[1]->caption);
        $this->assertEquals('Bunga teratai bermekaran di kolam utama', $images[2]->caption);
    }

    /**
     * Multi-image upload handles sparse, empty string, and whitespace captions by converting to null.
     */
    public function test_gallery_multi_upload_with_sparse_and_whitespace_captions(): void
    {
        $img1 = UploadedFile::fake()->image('s1.jpg');
        $img2 = UploadedFile::fake()->image('s2.jpg');
        $img3 = UploadedFile::fake()->image('s3.jpg');
        $img4 = UploadedFile::fake()->image('s4.jpg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$img1, $img2, $img3, $img4],
                'captions' => [
                    'Valid caption 1',
                    '    ',
                    '',
                    null,
                ],
            ]);

        $response->assertStatus(302);

        $images = $this->project->fresh()->images;
        $this->assertCount(4, $images);
        $this->assertEquals('Valid caption 1', $images[0]->caption);
        $this->assertNull($images[1]->caption);
        $this->assertNull($images[2]->caption);
        $this->assertNull($images[3]->caption);
    }

    /**
     * Multi-image upload safely handles fewer captions than uploaded images without index warnings.
     */
    public function test_gallery_multi_upload_with_fewer_captions_than_images(): void
    {
        $img1 = UploadedFile::fake()->image('f1.jpg');
        $img2 = UploadedFile::fake()->image('f2.jpg');
        $img3 = UploadedFile::fake()->image('f3.jpg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$img1, $img2, $img3],
                'captions' => ['Satu-satunya takarir yang diberikan'],
            ]);

        $response->assertStatus(302);

        $images = $this->project->fresh()->images;
        $this->assertCount(3, $images);
        $this->assertEquals('Satu-satunya takarir yang diberikan', $images[0]->caption);
        $this->assertNull($images[1]->caption);
        $this->assertNull($images[2]->caption);
    }

    /**
     * Multi-image upload rejects payload if any file is non-image, ensuring atomic validation.
     */
    public function test_gallery_multi_upload_rejects_non_image_file_in_batch(): void
    {
        $validImage = UploadedFile::fake()->image('valid.jpg');
        $invalidDoc = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$validImage, $invalidDoc],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['images.1']);
        $this->assertDatabaseCount('project_images', 0);
    }

    /**
     * Multi-image upload enforces 5MB (5120KB) boundary limit per image.
     */
    public function test_gallery_multi_upload_file_size_boundary_5mb(): void
    {
        // 5120KB is allowed
        $exact5MB = UploadedFile::fake()->image('exact5mb.jpg')->size(5120);
        $resPass = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$exact5MB],
            ]);
        $resPass->assertStatus(302);
        $resPass->assertSessionHasNoErrors();
        $this->assertDatabaseCount('project_images', 1);

        // 5121KB exceeds max limit
        $oversized = UploadedFile::fake()->image('over5mb.jpg')->size(5121);
        $resFail = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$oversized],
            ]);
        $resFail->assertStatus(302);
        $resFail->assertSessionHasErrors(['images.0']);
        $this->assertDatabaseCount('project_images', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Gallery Reordering Matrix (Orders Array vs Image IDs Array)
    |--------------------------------------------------------------------------
    */

    /**
     * Reordering using the 'orders' array format with explicit IDs and sort orders.
     */
    public function test_gallery_reorder_using_orders_array_format(): void
    {
        $img1 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 1]);
        $img2 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 2]);
        $img3 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 3]);
        $img4 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 4]);

        $payload = [
            'orders' => [
                ['id' => $img4->id, 'sort_order' => 10],
                ['id' => $img2->id, 'sort_order' => 20],
                ['id' => $img1->id, 'sort_order' => 30],
                ['id' => $img3->id, 'sort_order' => 40],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals(10, $img4->fresh()->sort_order);
        $this->assertEquals(20, $img2->fresh()->sort_order);
        $this->assertEquals(30, $img1->fresh()->sort_order);
        $this->assertEquals(40, $img3->fresh()->sort_order);

        $orderedIds = $this->project->fresh()->images->pluck('id')->all();
        $this->assertEquals([$img4->id, $img2->id, $img1->id, $img3->id], $orderedIds);
    }

    /**
     * Reordering using the 'image_ids' array format converts index sequence into 1-based sort orders.
     */
    public function test_gallery_reorder_using_image_ids_array_format(): void
    {
        $imgA = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 10]);
        $imgB = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 20]);
        $imgC = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 30]);

        // Desired order: imgC (1), imgA (2), imgB (3)
        $payload = [
            'image_ids' => [$imgC->id, $imgA->id, $imgB->id],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals(1, $imgC->fresh()->sort_order);
        $this->assertEquals(2, $imgA->fresh()->sort_order);
        $this->assertEquals(3, $imgB->fresh()->sort_order);

        $orderedIds = $this->project->fresh()->images->pluck('id')->all();
        $this->assertEquals([$imgC->id, $imgA->id, $imgB->id], $orderedIds);
    }

    /**
     * Reordering supports sparse and non-consecutive sort orders.
     */
    public function test_gallery_reorder_with_sparse_and_non_consecutive_orders(): void
    {
        $img1 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 1]);
        $img2 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 2]);
        $img3 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 3]);

        $payload = [
            'orders' => [
                ['id' => $img2->id, 'sort_order' => 100],
                ['id' => $img3->id, 'sort_order' => 500],
                ['id' => $img1->id, 'sort_order' => 9999],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);

        $this->assertEquals(100, $img2->fresh()->sort_order);
        $this->assertEquals(500, $img3->fresh()->sort_order);
        $this->assertEquals(9999, $img1->fresh()->sort_order);

        $orderedIds = $this->project->fresh()->images->pluck('id')->all();
        $this->assertEquals([$img2->id, $img3->id, $img1->id], $orderedIds);
    }

    /**
     * Reordering supports negative, zero, and positive sort orders without corruption.
     */
    public function test_gallery_reorder_with_negative_and_zero_sort_orders(): void
    {
        $imgA = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 1]);
        $imgB = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 2]);
        $imgC = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 3]);

        $payload = [
            'orders' => [
                ['id' => $imgC->id, 'sort_order' => -25],
                ['id' => $imgA->id, 'sort_order' => 0],
                ['id' => $imgB->id, 'sort_order' => 50],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);

        $this->assertEquals(-25, $imgC->fresh()->sort_order);
        $this->assertEquals(0, $imgA->fresh()->sort_order);
        $this->assertEquals(50, $imgB->fresh()->sort_order);

        $orderedIds = $this->project->fresh()->images->pluck('id')->all();
        $this->assertEquals([$imgC->id, $imgA->id, $imgB->id], $orderedIds);
    }

    /**
     * Partial reorder: Reordering a subset of images leaves unmentioned images untouched.
     */
    public function test_gallery_reorder_partial_subset_leaves_unspecified_images_unchanged(): void
    {
        $img1 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 1]);
        $img2 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 2]);
        $img3 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 3]);
        $img4 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 4]);

        $payload = [
            'orders' => [
                ['id' => $img2->id, 'sort_order' => 88],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);

        $this->assertEquals(1, $img1->fresh()->sort_order);
        $this->assertEquals(88, $img2->fresh()->sort_order);
        $this->assertEquals(3, $img3->fresh()->sort_order);
        $this->assertEquals(4, $img4->fresh()->sort_order);
    }

    /**
     * Reordering validates against non-integer sort orders.
     */
    public function test_gallery_reorder_rejects_non_integer_sort_orders(): void
    {
        $img = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 1]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", [
                'orders' => [
                    ['id' => $img->id, 'sort_order' => 'bukan-angka'],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['orders.0.sort_order']);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Cross-Project Isolation & Authorization Attack Scenarios
    |--------------------------------------------------------------------------
    */

    /**
     * Cross-Project Attack: Reorder endpoint for Project A MUST NOT modify images
     * belonging to Project B when targeted via 'orders' array payload.
     */
    public function test_cross_project_isolation_reorder_via_orders_cannot_modify_foreign_project_image(): void
    {
        $foreignProject = Project::factory()->create();
        $foreignImage = ProjectImage::factory()->create([
            'project_id' => $foreignProject->id,
            'sort_order' => 5,
        ]);

        $localImage = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'sort_order' => 1,
        ]);

        // Adversarial payload: try to modify foreign image sort_order via this project's endpoint
        $payload = [
            'orders' => [
                ['id' => $foreignImage->id, 'sort_order' => 999],
                ['id' => $localImage->id, 'sort_order' => 2],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);

        // Verification: foreign image must be completely unmodified
        $this->assertEquals(5, $foreignImage->fresh()->sort_order);
        $this->assertEquals($foreignProject->id, $foreignImage->fresh()->project_id);

        // Local image must be updated as expected
        $this->assertEquals(2, $localImage->fresh()->sort_order);
    }

    /**
     * Cross-Project Attack: Reorder endpoint for Project A MUST NOT modify images
     * belonging to Project B when targeted via 'image_ids' array payload.
     */
    public function test_cross_project_isolation_reorder_via_image_ids_cannot_modify_foreign_project_image(): void
    {
        $foreignProject = Project::factory()->create();
        $foreignImage = ProjectImage::factory()->create([
            'project_id' => $foreignProject->id,
            'sort_order' => 77,
        ]);

        $localImage = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'sort_order' => 1,
        ]);

        $payload = [
            'image_ids' => [$foreignImage->id, $localImage->id],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);

        // Foreign image sort order must remain untouched at 77
        $this->assertEquals(77, $foreignImage->fresh()->sort_order);
        $this->assertEquals($foreignProject->id, $foreignImage->fresh()->project_id);

        // Local image gets index 1 + 1 = 2
        $this->assertEquals(2, $localImage->fresh()->sort_order);
    }

    /**
     * Gallery upload to Project A does not cross-contaminate Project B's image collection.
     */
    public function test_cross_project_upload_isolation(): void
    {
        $foreignProject = Project::factory()->create();

        $image = UploadedFile::fake()->image('isolate.jpg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$image],
            ]);

        $response->assertStatus(302);

        $this->assertEquals(1, $this->project->fresh()->images()->count());
        $this->assertEquals(0, $foreignProject->fresh()->images()->count());
    }

    /**
     * Unauthenticated guest requests to all gallery endpoints are strictly redirected to login.
     */
    public function test_unauthenticated_requests_to_gallery_endpoints_redirect_to_login(): void
    {
        $image = ProjectImage::factory()->create(['project_id' => $this->project->id]);

        // Upload
        $this->post("/admin/projects/{$this->project->id}/images", [])
            ->assertStatus(302)->assertRedirect('/admin/login');

        // Reorder
        $this->post("/admin/projects/{$this->project->id}/images/reorder", [])
            ->assertStatus(302)->assertRedirect('/admin/login');

        // Caption Update
        $this->put("/admin/project-images/{$image->id}", ['caption' => 'Test'])
            ->assertStatus(302)->assertRedirect('/admin/login');

        // Delete
        $this->delete("/admin/project-images/{$image->id}")
            ->assertStatus(302)->assertRedirect('/admin/login');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Caption Update Stress & Boundaries
    |--------------------------------------------------------------------------
    */

    /**
     * Caption updates handle empty string and whitespace by converting them to null.
     */
    public function test_gallery_caption_update_normalizes_empty_and_whitespace_to_null(): void
    {
        $image = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'caption' => 'Initial caption',
        ]);

        // Update with empty string
        $res1 = $this->updateImageCaption($image, ['caption' => '']);
        $res1->assertStatus(302);
        $res1->assertSessionHas('success');
        $this->assertNull($image->fresh()->caption);

        // Update with whitespace only
        $res2 = $this->updateImageCaption($image, ['caption' => '     ']);
        $res2->assertStatus(302);
        $this->assertNull($image->fresh()->caption);
    }

    /**
     * Caption updates handle exact 255 character limit and reject 256 characters.
     */
    public function test_gallery_caption_update_boundary_length(): void
    {
        $image = ProjectImage::factory()->create(['project_id' => $this->project->id]);

        $maxCaption = str_repeat('A', 255);
        $resPass = $this->updateImageCaption($image, ['caption' => $maxCaption]);
        $resPass->assertStatus(302);
        $resPass->assertSessionHasNoErrors();
        $this->assertEquals($maxCaption, $image->fresh()->caption);

        $tooLongCaption = str_repeat('B', 256);
        $resFail = $this->updateImageCaption($image, ['caption' => $tooLongCaption]);
        $resFail->assertStatus(302);
        $resFail->assertSessionHasErrors(['caption']);
    }

    /**
     * Caption update correctly stores special botanical characters, quotes, and unicode.
     */
    public function test_gallery_caption_update_supports_unicode_and_botanical_characters(): void
    {
        $image = ProjectImage::factory()->create(['project_id' => $this->project->id]);

        $botanicalText = 'Lanskap "Taman Tropis" & Kolam Reflektif @ 28°C — Flora: Heliconia & Ficus 🌿🪴';

        $res = $this->updateImageCaption($image, ['caption' => $botanicalText]);
        $res->assertStatus(302);
        $this->assertEquals($botanicalText, $image->fresh()->caption);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Single Image Deletion & Storage Cleanup
    |--------------------------------------------------------------------------
    */

    /**
     * Deleting a gallery image removes the database row and deletes the file from public storage.
     */
    public function test_gallery_single_image_deletion_removes_physical_file_and_db_row(): void
    {
        $file = UploadedFile::fake()->image('to_delete.jpg');
        $storedPath = $file->store('projects/gallery', 'public');
        Storage::disk('public')->assertExists($storedPath);

        $image = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'image_path' => $storedPath,
        ]);

        $response = $this->deleteImageRecord($image);
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($storedPath);
    }

    /**
     * Deleting an image whose file is missing from disk or is an external URL completes gracefully.
     */
    public function test_gallery_single_image_deletion_handles_missing_file_or_external_url_gracefully(): void
    {
        // 1. Missing local file
        $missingImage = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'image_path' => 'projects/gallery/non_existent_file.jpg',
        ]);
        $res1 = $this->deleteImageRecord($missingImage);
        $res1->assertStatus(302);
        $this->assertDatabaseMissing('project_images', ['id' => $missingImage->id]);

        // 2. External URL
        $externalImage = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'image_path' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb',
        ]);
        $res2 = $this->deleteImageRecord($externalImage);
        $res2->assertStatus(302);
        $this->assertDatabaseMissing('project_images', ['id' => $externalImage->id]);
    }

    /**
     * Project deletion cascades to delete all associated gallery images from database.
     */
    public function test_project_cascade_deletion_removes_all_associated_gallery_images(): void
    {
        $img1 = ProjectImage::factory()->create(['project_id' => $this->project->id]);
        $img2 = ProjectImage::factory()->create(['project_id' => $this->project->id]);
        $img3 = ProjectImage::factory()->create(['project_id' => $this->project->id]);

        $this->assertDatabaseCount('project_images', 3);

        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$this->project->id}");
        $response->assertStatus(302);

        $this->assertDatabaseMissing('projects', ['id' => $this->project->id]);
        $this->assertDatabaseCount('project_images', 0);
    }
}
