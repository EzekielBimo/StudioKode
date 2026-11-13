<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGalleryTest extends TestCase
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
     * Helper to resolve the image update endpoint.
     */
    protected function updateImage(ProjectImage $image, array $payload)
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
     * Helper to resolve the image delete endpoint.
     */
    protected function deleteImage(ProjectImage $image)
    {
        if (Route::has('admin.images.destroy')) {
            return $this->actingAs($this->admin)->delete(route('admin.images.destroy', $image));
        }

        $res = $this->actingAs($this->admin)->delete("/admin/project-images/{$image->id}");
        if ($res->status() === 404) {
            return $this->actingAs($this->admin)->delete("/admin/images/{$image->id}", []);
        }
        return $res;
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 1: Primary Happy Paths & Interface Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 1: Gallery upload requires authentication.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_upload_requires_authentication(): void
    {
        $response = $this->post("/admin/projects/{$this->project->id}/images", []);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**
     * Tier 1: Multi-image upload persists files and captions to project gallery.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_multi_upload_persists_images_and_captions(): void
    {
        $image1 = UploadedFile::fake()->image('photo1.jpg', 1200, 800);
        $image2 = UploadedFile::fake()->image('photo2.jpg', 1200, 800);

        $payload = [
            'images' => [$image1, $image2],
            'captions' => [
                'Pavilion entrance with stone paving',
                'Reflective koi pond at twilight',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('project_images', 2);
        $this->assertDatabaseHas('project_images', [
            'project_id' => $this->project->id,
            'caption' => 'Pavilion entrance with stone paving',
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('project_images', [
            'project_id' => $this->project->id,
            'caption' => 'Reflective koi pond at twilight',
            'sort_order' => 2,
        ]);
    }

    /**
     * Tier 1: Gallery image caption updates successfully.
     * Feature 22: Gallery Caption & Item Delete (R3)
     */
    public function test_gallery_image_caption_updates_successfully(): void
    {
        $image = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'caption' => 'Original caption',
            'sort_order' => 1,
        ]);

        $response = $this->updateImage($image, [
            'caption' => 'Updated botanical terrace perspective',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('project_images', [
            'id' => $image->id,
            'caption' => 'Updated botanical terrace perspective',
        ]);
    }

    /**
     * Tier 1: Gallery images batch reordering updates sort order sequence.
     * Feature 21: Gallery Sort Reordering (R3)
     */
    public function test_gallery_batch_reordering_updates_sort_orders(): void
    {
        $img1 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 1]);
        $img2 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 2]);
        $img3 = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 3]);

        // Reverse the order: img3 (order 1), img1 (order 2), img2 (order 3)
        $payload = [
            'orders' => [
                ['id' => $img3->id, 'sort_order' => 1],
                ['id' => $img1->id, 'sort_order' => 2],
                ['id' => $img2->id, 'sort_order' => 3],
            ],
            // Alternative payload format support: image_ids array
            'image_ids' => [$img3->id, $img1->id, $img2->id],
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertEquals(1, $img3->fresh()->sort_order);
        $this->assertEquals(2, $img1->fresh()->sort_order);
        $this->assertEquals(3, $img2->fresh()->sort_order);
    }

    /**
     * Tier 1: Gallery individual image deletes successfully.
     * Feature 22: Gallery Caption & Item Delete (R3)
     */
    public function test_gallery_image_deletes_successfully(): void
    {
        $image = ProjectImage::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->deleteImage($image);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 2: Boundary Value Analysis, Validation & Edge Cases
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 2: Gallery upload rejects non-image file formats (e.g. PHP scripts or documents).
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_upload_rejects_non_image_files(): void
    {
        $invalidFile = UploadedFile::fake()->create('exploit.php', 10, 'text/x-php');

        $payload = [
            'images' => [$invalidFile],
        ];

        $response = $this->actingAs($this->admin)
            ->from("/admin/projects/{$this->project->id}/edit")
            ->post("/admin/projects/{$this->project->id}/images", $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['images.0']);
        $this->assertDatabaseCount('project_images', 0);
    }

    /**
     * Tier 2: Gallery upload rejects oversized images (greater than 5MB / 5120KB).
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_upload_rejects_oversized_images(): void
    {
        $oversizedImage = UploadedFile::fake()->image('huge.jpg')->size(6000);

        $payload = [
            'images' => [$oversizedImage],
        ];

        $response = $this->actingAs($this->admin)
            ->from("/admin/projects/{$this->project->id}/edit")
            ->post("/admin/projects/{$this->project->id}/images", $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['images.0']);
    }

    /**
     * Tier 2: Deleting a non-existent image ID returns 404.
     * Feature 22: Gallery Caption & Item Delete (R3)
     */
    public function test_gallery_image_delete_returns_404_for_non_existent_image(): void
    {
        $response = $this->actingAs($this->admin)->delete('/admin/project-images/999999');

        if ($response->status() === 404) {
            $this->assertSame(404, $response->status());
        } else {
            $altResponse = $this->actingAs($this->admin)->delete('/admin/images/999999');
            $altResponse->assertStatus(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 3: Ordering Sequence & Progressive Appending
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 3: Gallery images are returned in ascending sort_order.
     * Feature 21: Gallery Sort Reordering (R3)
     */
    public function test_gallery_images_retrieved_in_ascending_sort_order(): void
    {
        $imgC = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 30]);
        $imgA = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 10]);
        $imgB = ProjectImage::factory()->create(['project_id' => $this->project->id, 'sort_order' => 20]);

        $sortedIds = $this->project->fresh()->images->pluck('id')->all();

        $this->assertEquals([$imgA->id, $imgB->id, $imgC->id], $sortedIds);
    }

    /**
     * Tier 3: Subsequent gallery uploads append sort orders after the existing maximum.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_upload_increments_sort_order_from_existing_maximum(): void
    {
        ProjectImage::factory()->create([
            'project_id' => $this->project->id,
            'sort_order' => 5,
        ]);

        $newImage = UploadedFile::fake()->image('appended.jpg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$newImage],
            ]);

        $response->assertStatus(302);

        $latestImage = ProjectImage::where('project_id', $this->project->id)
            ->orderBy('id', 'desc')
            ->firstOrFail();

        $this->assertGreaterThan(5, $latestImage->sort_order);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 4: End-to-End Gallery Management Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 4: Complete gallery lifecycle: Upload 3 images -> Verify order ->
     * Reorder sequence -> Update caption -> Delete single image -> Verify integrity.
     * Scenario 4.3 (R3)
     */
    public function test_gallery_management_end_to_end_lifecycle(): void
    {
        // 1. Upload 3 images
        $f1 = UploadedFile::fake()->image('f1.jpg');
        $f2 = UploadedFile::fake()->image('f2.jpg');
        $f3 = UploadedFile::fake()->image('f3.jpg');

        $uploadResponse = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images", [
                'images' => [$f1, $f2, $f3],
                'captions' => ['Step 1 Image', 'Step 2 Image', 'Step 3 Image'],
            ]);
        $uploadResponse->assertStatus(302);

        $images = $this->project->fresh()->images;
        $this->assertCount(3, $images);
        $i1 = $images[0];
        $i2 = $images[1];
        $i3 = $images[2];

        // 2. Reorder images: put i3 first, i1 second, i2 third
        $reorderResponse = $this->actingAs($this->admin)
            ->post("/admin/projects/{$this->project->id}/images/reorder", [
                'orders' => [
                    ['id' => $i3->id, 'sort_order' => 1],
                    ['id' => $i1->id, 'sort_order' => 2],
                    ['id' => $i2->id, 'sort_order' => 3],
                ],
                'image_ids' => [$i3->id, $i1->id, $i2->id],
            ]);
        $reorderResponse->assertStatus(302);

        $reordered = $this->project->fresh()->images;
        $this->assertEquals($i3->id, $reordered[0]->id);
        $this->assertEquals($i1->id, $reordered[1]->id);
        $this->assertEquals($i2->id, $reordered[2]->id);

        // 3. Update caption on i3
        $updateCaption = $this->updateImage($i3, [
            'caption' => 'Refined architectural perspective',
        ]);
        $updateCaption->assertStatus(302);
        $this->assertEquals('Refined architectural perspective', $i3->fresh()->caption);

        // 4. Delete image i1
        $deleteResponse = $this->deleteImage($i1);
        $deleteResponse->assertStatus(302);

        $remaining = $this->project->fresh()->images;
        $this->assertCount(2, $remaining);
        $this->assertEquals([$i3->id, $i2->id], $remaining->pluck('id')->all());
    }
}
