<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChallengerM3_1_ProjectStressTest extends TestCase
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
    | Dimension 1: Slug Generation Edge Cases & Sanitization
    |--------------------------------------------------------------------------
    */

    /**
     * Test auto-generation of slug from title containing accented characters and diacritics.
     */
    public function test_slug_auto_generation_from_title_with_accented_letters(): void
    {
        $payload = [
            'title' => "Résidence d'Été à Ubud - Château & Forêt",
            'slug' => '',
            'description' => 'Botanical landscape design incorporating tropical microclimates.',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '1,200 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success');

        $project = Project::where('title', "Résidence d'Été à Ubud - Château & Forêt")->first();
        $this->assertNotNull($project);
        $this->assertNotEmpty($project->slug);
        $this->assertDoesNotMatchRegularExpression('/[^a-z0-9\-]/', $project->slug);
        $this->assertStringContainsString('residence', $project->slug);
        $this->assertStringContainsString('ubud', $project->slug);
    }

    /**
     * Test auto-generation of slug from title containing harsh special characters, symbols, and punctuation.
     */
    public function test_slug_auto_generation_with_special_characters_and_symbols(): void
    {
        $payload = [
            'title' => 'StudioKode Villa #42 (Phase & Stage @ Bali) — 100% Organic!',
            'slug' => '',
            'description' => 'Landscape architecture with sustainable water management.',
            'location' => 'Canggu, Bali',
            'year' => 2025,
            'area_size' => '850 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('location', 'Canggu, Bali')->first();
        $this->assertNotNull($project);
        $this->assertNotEmpty($project->slug);
        $this->assertDoesNotMatchRegularExpression('/[^a-z0-9\-]/', $project->slug);
        $this->assertFalse(str_contains($project->slug, '@'));
        $this->assertFalse(str_contains($project->slug, '#'));
        $this->assertFalse(str_contains($project->slug, '%'));
        $this->assertFalse(str_contains($project->slug, '!'));
    }

    /**
     * Test auto-generation of slug from title containing emoji characters.
     */
    public function test_slug_auto_generation_with_emojis_in_title(): void
    {
        $payload = [
            'title' => 'Botanical Garden 🌿🌸 Sanctuary 🍃 Villa',
            'slug' => '',
            'description' => 'A serene garden retreat with exotic flora.',
            'location' => 'Bedugul, Bali',
            'year' => 2024,
            'area_size' => '2,500 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('location', 'Bedugul, Bali')->first();
        $this->assertNotNull($project);
        $this->assertEquals('botanical-garden-sanctuary-villa', $project->slug);
    }

    /**
     * Test manual slug input is sanitized into lowercased URL-friendly format.
     */
    public function test_manual_slug_input_is_sanitized_and_normalized(): void
    {
        $payload = [
            'title' => 'Terrace Cliff Pavilion',
            'slug' => 'MY_CUSTOM--SLUG_2024!!',
            'description' => 'Cliffside terrace landscaping.',
            'location' => 'Uluwatu, Bali',
            'year' => 2024,
            'area_size' => '600 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('title', 'Terrace Cliff Pavilion')->first();
        $this->assertNotNull($project);
        $this->assertEquals('my-custom-slug-2024', $project->slug);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Slug Collision, Duplication & Update Invariance
    |--------------------------------------------------------------------------
    */

    /**
     * Test storing a project with duplicate manual slug is rejected with localized error.
     */
    public function test_store_rejects_duplicate_manual_slug_with_localized_error(): void
    {
        Project::factory()->create([
            'slug' => 'taman-botanika-heritage',
        ]);

        $payload = [
            'title' => 'Different Project Name',
            'slug' => 'taman-botanika-heritage',
            'description' => 'Trying to collide with existing slug.',
            'location' => 'Denpasar, Bali',
            'year' => 2024,
            'area_size' => '400 m²',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
        $errors = session('errors')->get('slug');
        $this->assertStringContainsString('Slug proyek sudah digunakan. Silakan gunakan slug lain.', $errors[0]);
    }

    /**
     * Test storing a project where auto-generated slug collides with existing slug is rejected.
     */
    public function test_store_rejects_duplicate_auto_generated_slug_from_title(): void
    {
        Project::factory()->create([
            'slug' => 'taman-bambu-kuning',
        ]);

        $payload = [
            'title' => 'Taman Bambu Kuning',
            'slug' => '',
            'description' => 'Collision through auto-generation.',
            'location' => 'Gianyar, Bali',
            'year' => 2024,
            'area_size' => '500 m²',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
    }

    /**
     * Test updating a project while keeping its existing slug succeeds (unique ignore).
     */
    public function test_update_allows_keeping_same_slug_without_collision_error(): void
    {
        $project = Project::factory()->create([
            'slug' => 'preserved-original-slug',
            'title' => 'Original Title',
        ]);

        $payload = [
            'title' => 'Updated Title But Same Slug',
            'slug' => 'preserved-original-slug',
            'description' => 'Updated project description.',
            'location' => 'Updated Location',
            'year' => 2025,
            'area_size' => '750 m²',
        ];

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success', 'Proyek berhasil diperbarui.');

        $fresh = $project->fresh();
        $this->assertEquals('Updated Title But Same Slug', $fresh->title);
        $this->assertEquals('preserved-original-slug', $fresh->slug);
    }

    /**
     * Test updating a project to use a slug owned by another project is rejected.
     */
    public function test_update_rejects_colliding_slug_from_another_project(): void
    {
        $projectA = Project::factory()->create(['slug' => 'alpha-sanctuary']);
        $projectB = Project::factory()->create(['slug' => 'beta-sanctuary']);

        $payload = [
            'title' => 'Project B Attempting Takeover',
            'slug' => 'alpha-sanctuary',
            'description' => 'Updated description.',
            'location' => $projectB->location,
            'year' => $projectB->year,
            'area_size' => $projectB->area_size,
        ];

        $response = $this->actingAs($this->admin)
            ->from("/admin/projects/{$projectB->id}/edit")
            ->put("/admin/projects/{$projectB->id}", $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['slug']);
        $errors = session('errors')->get('slug');
        $this->assertStringContainsString('Slug proyek sudah digunakan oleh proyek lain.', $errors[0]);

        $this->assertEquals('beta-sanctuary', $projectB->fresh()->slug);
    }

    /**
     * Test updating project with empty slug regenerates slug from updated title.
     */
    public function test_update_with_empty_slug_regenerates_from_updated_title(): void
    {
        $project = Project::factory()->create([
            'title' => 'Old Estate Garden',
            'slug' => 'old-estate-garden',
        ]);

        $payload = [
            'title' => 'Modern Zen Oasis',
            'slug' => '',
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $fresh = $project->fresh();
        $this->assertEquals('Modern Zen Oasis', $fresh->title);
        $this->assertEquals('modern-zen-oasis', $fresh->slug);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Mandatory Field Omissions & Boundary Inputs
    |--------------------------------------------------------------------------
    */

    /**
     * Test store validation rejects completely empty submission with Indonesian error messages.
     */
    public function test_store_validation_rejects_empty_mandatory_fields_with_localized_errors(): void
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

        $errors = session('errors');
        $this->assertEquals('Judul proyek wajib diisi.', $errors->first('title'));
        $this->assertEquals('Deskripsi proyek wajib diisi.', $errors->first('description'));
        $this->assertEquals('Lokasi proyek wajib diisi.', $errors->first('location'));
        $this->assertEquals('Tahun proyek wajib diisi.', $errors->first('year'));
        $this->assertEquals('Luas area proyek wajib diisi.', $errors->first('area_size'));
    }

    /**
     * Test whitespace-only inputs for mandatory fields fail validation.
     */
    public function test_store_validation_rejects_whitespace_only_strings(): void
    {
        $payload = [
            'title' => '   ',
            'description' => "  \n  \t ",
            'location' => '     ',
            'year' => '',
            'area_size' => '   ',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

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
     * Test title length boundaries: exactly 255 chars succeeds, 256 chars fails.
     */
    public function test_title_length_boundaries_255_and_256_chars(): void
    {
        // 255 characters string
        $validTitle = str_repeat('A', 255);
        $payloadValid = [
            'title' => $validTitle,
            'slug' => 'title-255-chars',
            'description' => 'Valid boundary test description.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '300 m²',
        ];

        $responseValid = $this->actingAs($this->admin)->post('/admin/projects', $payloadValid);
        $responseValid->assertStatus(302);
        $responseValid->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', ['slug' => 'title-255-chars']);

        // 256 characters string
        $invalidTitle = str_repeat('B', 256);
        $payloadInvalid = [
            'title' => $invalidTitle,
            'slug' => 'title-256-chars',
            'description' => 'Invalid boundary test description.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '300 m²',
        ];

        $responseInvalid = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payloadInvalid);
        $responseInvalid->assertStatus(302);
        $responseInvalid->assertSessionHasErrors(['title']);
    }

    /**
     * Test area_size length boundaries: 50 characters succeeds, 51 characters fails.
     */
    public function test_area_size_length_boundaries_50_and_51_chars(): void
    {
        // 50 characters string (e.g. detailed area breakdown)
        $validArea = str_repeat('1', 50);
        $payloadValid = [
            'title' => 'Area Size 50 Test',
            'slug' => 'area-50-valid',
            'description' => 'Valid area size boundary.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => $validArea,
        ];

        $resValid = $this->actingAs($this->admin)->post('/admin/projects', $payloadValid);
        $resValid->assertStatus(302);
        $resValid->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', ['slug' => 'area-50-valid']);

        // 51 characters string
        $invalidArea = str_repeat('2', 51);
        $payloadInvalid = [
            'title' => 'Area Size 51 Test',
            'slug' => 'area-51-invalid',
            'description' => 'Invalid area size boundary.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => $invalidArea,
        ];

        $resInvalid = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payloadInvalid);
        $resInvalid->assertStatus(302);
        $resInvalid->assertSessionHasErrors(['area_size']);
    }

    /**
     * Test client and excerpt optionality and boundary lengths.
     */
    public function test_client_and_excerpt_nullable_and_boundary_lengths(): void
    {
        // Null client and excerpt are allowed
        $payloadNull = [
            'title' => 'Nullable Fields Test',
            'slug' => 'nullable-fields-test',
            'client' => null,
            'excerpt' => null,
            'description' => 'Valid description.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '500 m²',
        ];

        $resNull = $this->actingAs($this->admin)->post('/admin/projects', $payloadNull);
        $resNull->assertStatus(302);
        $resNull->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', [
            'slug' => 'nullable-fields-test',
            'client' => null,
            'excerpt' => null,
        ]);

        // Client boundary: 255 chars succeeds, 256 chars fails
        $payloadClientExceeded = [
            'title' => 'Client Boundary Test',
            'client' => str_repeat('C', 256),
            'description' => 'Valid description.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '500 m²',
        ];

        $resClientFail = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payloadClientExceeded);
        $resClientFail->assertStatus(302);
        $resClientFail->assertSessionHasErrors(['client']);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Extreme Year Values
    |--------------------------------------------------------------------------
    */

    /**
     * Test year minimum boundary at 1900 succeeds.
     */
    public function test_year_minimum_boundary_1900_succeeds(): void
    {
        $payload = [
            'title' => 'Heritage Garden 1900',
            'slug' => 'heritage-garden-1900',
            'description' => 'Historic landscape restoration.',
            'location' => 'Bandung, West Java',
            'year' => 1900,
            'area_size' => '3,000 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', [
            'slug' => 'heritage-garden-1900',
            'year' => 1900,
        ]);
    }

    /**
     * Test year maximum boundary at 2100 succeeds.
     */
    public function test_year_maximum_boundary_2100_succeeds(): void
    {
        $payload = [
            'title' => 'Futuristic Biosphere 2100',
            'slug' => 'futuristic-biosphere-2100',
            'description' => 'Centennial vision landscape project.',
            'location' => 'Nusantara, IKN',
            'year' => 2100,
            'area_size' => '50,000 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', [
            'slug' => 'futuristic-biosphere-2100',
            'year' => 2100,
        ]);
    }

    /**
     * Test year strictly below minimum boundary (1899) is rejected.
     */
    public function test_year_below_minimum_boundary_1899_fails(): void
    {
        $payload = [
            'title' => 'Ancient Garden 1899',
            'slug' => 'ancient-garden-1899',
            'description' => 'Too early year boundary.',
            'location' => 'Surakarta, Central Java',
            'year' => 1899,
            'area_size' => '1,000 m²',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['year']);
        $this->assertDatabaseMissing('projects', ['slug' => 'ancient-garden-1899']);
    }

    /**
     * Test year strictly above maximum boundary (2101) is rejected.
     */
    public function test_year_above_maximum_boundary_2101_fails(): void
    {
        $payload = [
            'title' => 'Distant Future 2101',
            'slug' => 'distant-future-2101',
            'description' => 'Too distant year boundary.',
            'location' => 'Nusantara, IKN',
            'year' => 2101,
            'area_size' => '10,000 m²',
        ];

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['year']);
        $this->assertDatabaseMissing('projects', ['slug' => 'distant-future-2101']);
    }

    /**
     * Test negative, zero, and non-numeric year values are rejected.
     */
    public function test_year_negative_zero_and_non_numeric_values_fail(): void
    {
        // Zero
        $resZero = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', [
                'title' => 'Zero Year Project',
                'description' => 'Testing year 0.',
                'location' => 'Bali',
                'year' => 0,
                'area_size' => '100 m²',
            ]);
        $resZero->assertSessionHasErrors(['year']);

        // Negative year
        $resNeg = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', [
                'title' => 'Negative Year Project',
                'description' => 'Testing negative year.',
                'location' => 'Bali',
                'year' => -2024,
                'area_size' => '100 m²',
            ]);
        $resNeg->assertSessionHasErrors(['year']);

        // Non-numeric string
        $resString = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', [
                'title' => 'String Year Project',
                'description' => 'Testing string year.',
                'location' => 'Bali',
                'year' => 'two-thousand-twenty-four',
                'area_size' => '100 m²',
            ]);
        $resString->assertSessionHasErrors(['year']);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Large Text Ingestion & Payload Stress
    |--------------------------------------------------------------------------
    */

    /**
     * Test ingestion and retrieval of large description payloads (60,000+ characters).
     */
    public function test_large_description_payload_stored_and_retrieved_intact(): void
    {
        // Build 60,000 characters of rich botanical markdown text
        $paragraph = "StudioKode landscape architecture harmonizes endemic botanical diversity with stone masonry.\n\n";
        $largeDescription = str_repeat($paragraph, 650); // > 60KB
        $this->assertGreaterThan(60000, strlen($largeDescription));

        $payload = [
            'title' => 'Monumental Botanical Reserve',
            'slug' => 'monumental-botanical-reserve',
            'description' => $largeDescription,
            'location' => 'Tabanan, Bali',
            'year' => 2024,
            'area_size' => '150,000 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('slug', 'monumental-botanical-reserve')->first();
        $this->assertNotNull($project);
        $expectedDescription = trim($largeDescription);
        $this->assertEquals(strlen($expectedDescription), strlen($project->description));
        $this->assertEquals($expectedDescription, $project->description);
    }

    /**
     * Test description containing complex unicode, HTML tags, and special characters.
     */
    public function test_description_with_html_tags_and_complex_unicode(): void
    {
        $complexContent = <<<'TEXT'
<div class="botanical-suite">
    <h3>Taman Zen & Pavilion — 京都庭園</h3>
    <p>Dihiasi oleh batu alam Andesit, bambu hitam (<i>Phyllostachys nigra</i>), dan lumut hutan (🌿).</p>
    <blockquote>"Keindahan terlahir dari ketenangan alam." &amp; 'Harmoni'</blockquote>
    <code>$waterfall_flow_rate = 15.5; // m³/jam</code>
</div>
TEXT;

        $payload = [
            'title' => 'Zen Master Suite',
            'slug' => 'zen-master-suite',
            'description' => $complexContent,
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '720 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('slug', 'zen-master-suite')->first();
        $this->assertNotNull($project);
        $this->assertEquals($complexContent, $project->description);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 6: File Upload Boundaries & MIME Security
    |--------------------------------------------------------------------------
    */

    /**
     * Test file upload rejects non-image document formats (PDF, TXT, ZIP, EXE).
     */
    public function test_file_upload_rejects_non_image_documents(): void
    {
        // 1. PDF as cover image
        $pdfFile = UploadedFile::fake()->create('blueprint.pdf', 800, 'application/pdf');
        $resPdf = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', [
                'title' => 'PDF Upload Rejection',
                'description' => 'Attempting PDF upload.',
                'location' => 'Bali',
                'year' => 2024,
                'area_size' => '200 m²',
                'cover_image' => $pdfFile,
            ]);
        $resPdf->assertStatus(302);
        $resPdf->assertSessionHasErrors(['cover_image']);
        $this->assertEquals('File gambar sampul harus berupa berkas gambar yang valid.', session('errors')->first('cover_image'));

        // 2. TXT as masterplan image
        $txtFile = UploadedFile::fake()->create('notes.txt', 100, 'text/plain');
        $resTxt = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', [
                'title' => 'TXT Masterplan Rejection',
                'description' => 'Attempting TXT upload.',
                'location' => 'Bali',
                'year' => 2024,
                'area_size' => '200 m²',
                'masterplan_image' => $txtFile,
            ]);
        $resTxt->assertStatus(302);
        $resTxt->assertSessionHasErrors(['masterplan_image']);
        $this->assertEquals('File gambar masterplan harus berupa berkas gambar yang valid.', session('errors')->first('masterplan_image'));
    }

    /**
     * Test file upload rejects oversized images (> 10MB / 10240 KB limit).
     */
    public function test_file_upload_rejects_oversized_images(): void
    {
        // Fake 11MB image file
        $oversizedImage = UploadedFile::fake()->image('huge_photo.jpg')->size(11264); // 11MB in KB

        $response = $this->actingAs($this->admin)
            ->from('/admin/projects/create')
            ->post('/admin/projects', [
                'title' => 'Oversized Cover Test',
                'description' => 'Image exceeds 10MB limit.',
                'location' => 'Bali',
                'year' => 2024,
                'area_size' => '400 m²',
                'cover_image' => $oversizedImage,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['cover_image']);
    }

    /**
     * Test file upload accepts valid image formats (JPEG, PNG, WEBP) and stores under projects/.
     */
    public function test_file_upload_accepts_valid_image_formats(): void
    {
        $jpgCover = UploadedFile::fake()->image('cover.jpg', 1920, 1080);
        $pngMasterplan = UploadedFile::fake()->image('masterplan.png', 2400, 1600);

        $payload = [
            'title' => 'Format Validation Project',
            'slug' => 'format-validation-project',
            'description' => 'Testing JPG cover and PNG masterplan.',
            'location' => 'Seminyak, Bali',
            'year' => 2024,
            'area_size' => '600 m²',
            'cover_image' => $jpgCover,
            'masterplan_image' => $pngMasterplan,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('slug', 'format-validation-project')->first();
        $this->assertNotNull($project);
        $this->assertNotNull($project->cover_image);
        $this->assertNotNull($project->masterplan_image);

        // Verify stored in public disk under projects/ directory
        $this->assertStringStartsWith('projects/', $project->cover_image);
        $this->assertStringStartsWith('projects/', $project->masterplan_image);
        Storage::disk('public')->assertExists($project->cover_image);
        Storage::disk('public')->assertExists($project->masterplan_image);
    }

    /**
     * Test missing optional cover image defaults to projects/default-cover.jpg and masterplan to null.
     */
    public function test_missing_optional_cover_image_uses_default_placeholder(): void
    {
        $payload = [
            'title' => 'No Image Project',
            'slug' => 'no-image-project',
            'description' => 'Project without uploaded media assets.',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '350 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $project = Project::where('slug', 'no-image-project')->first();
        $this->assertNotNull($project);
        $this->assertEquals('projects/default-cover.jpg', $project->cover_image);
        $this->assertNull($project->masterplan_image);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 7: Update Retainment & File Replacement Semantics
    |--------------------------------------------------------------------------
    */

    /**
     * Test updating a project without providing new files retains previous images intact.
     */
    public function test_update_retains_existing_images_when_omitted(): void
    {
        $cover = UploadedFile::fake()->image('initial_cover.jpg');
        $masterplan = UploadedFile::fake()->image('initial_plan.jpg');

        $initialCoverPath = $cover->store('projects', 'public');
        $initialPlanPath = $masterplan->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $initialCoverPath,
            'masterplan_image' => $initialPlanPath,
        ]);

        Storage::disk('public')->assertExists($initialCoverPath);
        Storage::disk('public')->assertExists($initialPlanPath);

        $updatePayload = [
            'title' => 'Updated Name Without Images',
            'slug' => $project->slug,
            'description' => 'Updated description.',
            'location' => 'Sanur, Bali',
            'year' => 2025,
            'area_size' => '800 m²',
        ];

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", $updatePayload);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $fresh = $project->fresh();
        $this->assertEquals($initialCoverPath, $fresh->cover_image);
        $this->assertEquals($initialPlanPath, $fresh->masterplan_image);
        Storage::disk('public')->assertExists($initialCoverPath);
        Storage::disk('public')->assertExists($initialPlanPath);
    }

    /**
     * Test updating project with new cover image replaces file and deletes old file from storage.
     */
    public function test_update_replaces_cover_image_and_deletes_old_file_from_storage(): void
    {
        $oldFile = UploadedFile::fake()->image('old_cover.jpg');
        $oldPath = $oldFile->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('replacement_cover.jpg');

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => $project->title,
            'slug' => $project->slug,
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
            'cover_image' => $newFile,
        ]);

        $response->assertStatus(302);
        $response->assertSessionDoesntHaveErrors();

        $fresh = $project->fresh();
        $this->assertNotEquals($oldPath, $fresh->cover_image);
        Storage::disk('public')->assertExists($fresh->cover_image);
        Storage::disk('public')->assertMissing($oldPath);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 8: Cascade Deletion & Referential Integrity
    |--------------------------------------------------------------------------
    */

    /**
     * Test cascade deletion removes project, child gallery records, and physical files from storage.
     */
    public function test_cascade_deletion_deletes_project_child_images_and_storage_files(): void
    {
        // 1. Create project with stored cover and masterplan files
        $coverFile = UploadedFile::fake()->image('cover_to_delete.jpg');
        $masterplanFile = UploadedFile::fake()->image('plan_to_delete.jpg');
        $coverPath = $coverFile->store('projects', 'public');
        $masterplanPath = $masterplanFile->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $coverPath,
            'masterplan_image' => $masterplanPath,
        ]);

        // 2. Create 3 gallery images with stored files
        $galleryPaths = [];
        $galleryImages = [];
        for ($i = 1; $i <= 3; $i++) {
            $gFile = UploadedFile::fake()->image("gallery_{$i}.jpg");
            $gPath = $gFile->store('projects/gallery', 'public');
            $galleryPaths[] = $gPath;

            $galleryImages[] = ProjectImage::factory()->create([
                'project_id' => $project->id,
                'image_path' => $gPath,
                'caption' => "Gallery image {$i}",
                'sort_order' => $i,
            ]);
        }

        // Verify pre-conditions in DB and Storage
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        Storage::disk('public')->assertExists($coverPath);
        Storage::disk('public')->assertExists($masterplanPath);
        foreach ($galleryImages as $img) {
            $this->assertDatabaseHas('project_images', ['id' => $img->id]);
            Storage::disk('public')->assertExists($img->image_path);
        }

        // 3. Perform DELETE request
        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success', 'Proyek berhasil dihapus.');

        // 4. Verify DB removal
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        foreach ($galleryImages as $img) {
            $this->assertDatabaseMissing('project_images', ['id' => $img->id]);
        }

        // 5. Verify physical storage file clean up
        Storage::disk('public')->assertMissing($coverPath);
        Storage::disk('public')->assertMissing($masterplanPath);
        foreach ($galleryPaths as $gPath) {
            Storage::disk('public')->assertMissing($gPath);
        }
    }

    /**
     * Test cascade deletion on project with zero child gallery images deletes cleanly.
     */
    public function test_cascade_deletion_on_project_with_zero_gallery_images(): void
    {
        $project = Project::factory()->create();
        $this->assertEquals(0, $project->images()->count());

        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success', 'Proyek berhasil dihapus.');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /**
     * Test deleting non-existent project returns 404 response.
     */
    public function test_destroy_returns_404_for_non_existent_project(): void
    {
        $response = $this->actingAs($this->admin)->delete('/admin/projects/9999999');
        $response->assertStatus(404);
    }

    /**
     * Test unauthenticated guest request cannot delete project.
     */
    public function test_unauthenticated_request_cannot_delete_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->delete("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 9: Attributes Casting, Featured & Published Toggles
    |--------------------------------------------------------------------------
    */

    /**
     * Test boolean featured flag and published_at datetime handling.
     */
    public function test_featured_flag_and_published_at_casting(): void
    {
        $payloadFeatured = [
            'title' => 'Featured Published Botanical Haven',
            'slug' => 'featured-published-haven',
            'description' => 'Checking casting.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '1,000 m²',
            'is_featured' => '1',
            'published_at' => '2024-06-15 10:30:00',
        ];

        $resFeatured = $this->actingAs($this->admin)->post('/admin/projects', $payloadFeatured);
        $resFeatured->assertStatus(302);

        $projectFeatured = Project::where('slug', 'featured-published-haven')->first();
        $this->assertTrue($projectFeatured->is_featured);
        $this->assertNotNull($projectFeatured->published_at);
        $this->assertEquals('2024-06-15 10:30:00', $projectFeatured->published_at->format('Y-m-d H:i:s'));

        // Test non-featured and unpublished (draft)
        $payloadDraft = [
            'title' => 'Draft Unfeatured Haven',
            'slug' => 'draft-unfeatured-haven',
            'description' => 'Draft unfeatured project.',
            'location' => 'Bali',
            'year' => 2024,
            'area_size' => '500 m²',
            'is_featured' => 0,
            'published_at' => null,
        ];

        $resDraft = $this->actingAs($this->admin)->post('/admin/projects', $payloadDraft);
        $resDraft->assertStatus(302);

        $projectDraft = Project::where('slug', 'draft-unfeatured-haven')->first();
        $this->assertFalse($projectDraft->is_featured);
        $this->assertNull($projectDraft->published_at);
    }

    /**
     * Test area_numeric accessor extracts numeric integer from diverse area_size formats.
     */
    public function test_area_numeric_accessor_stress(): void
    {
        $project1 = Project::factory()->create(['area_size' => '1,450 m²']);
        $this->assertSame(1450, $project1->area_numeric);

        $project2 = Project::factory()->create(['area_size' => '250.5 m²']);
        $this->assertSame(2505, $project2->area_numeric);

        $project3 = Project::factory()->create(['area_size' => 'No Numbers Here']);
        $this->assertSame(0, $project3->area_numeric);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 10: Advanced Routing, Method Spoofing & Search Hardening
    |--------------------------------------------------------------------------
    */

    /**
     * Test Inertia multipart update pattern using POST with _method = PUT.
     */
    public function test_multipart_update_with_method_spoofing(): void
    {
        $project = Project::factory()->create();
        $replacementCover = UploadedFile::fake()->image('spoofed_cover.jpg');

        $response = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}", [
            '_method' => 'PUT',
            'title' => 'Updated Via Method Spoofing',
            'slug' => $project->slug,
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
            'cover_image' => $replacementCover,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success', 'Proyek berhasil diperbarui.');

        $fresh = $project->fresh();
        $this->assertEquals('Updated Via Method Spoofing', $fresh->title);
        $this->assertStringStartsWith('projects/', $fresh->cover_image);
        Storage::disk('public')->assertExists($fresh->cover_image);
    }

    /**
     * Test search endpoint resilience against SQL wildcards and injection payloads.
     */
    public function test_search_sanitization_against_sql_wildcards_and_injection(): void
    {
        Project::factory()->create(['title' => 'Taman Bambu Sastra', 'location' => 'Ubud']);
        Project::factory()->create(['title' => 'Villa Seribu Batu', 'location' => 'Tabanan']);

        // 1. SQL Injection attempt payload
        $resInjection = $this->actingAs($this->admin)->get('/admin/projects?search=' . urlencode("' OR '1'='1"));
        $resInjection->assertStatus(200);

        // 2. Trailing SQL comment and drop payload
        $resDrop = $this->actingAs($this->admin)->get('/admin/projects?search=' . urlencode("'; DROP TABLE projects; --"));
        $resDrop->assertStatus(200);

        // 3. Raw SQL wildcard characters '%' and '_'
        $resWildcard = $this->actingAs($this->admin)->get('/admin/projects?search=' . urlencode("%_%"));
        $resWildcard->assertStatus(200);

        // Database remains intact
        $this->assertDatabaseCount('projects', 2);
    }

    /**
     * Test show route redirects cleanly to edit view as specified in contract.
     */
    public function test_show_endpoint_redirects_to_edit_view(): void
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect("/admin/projects/{$project->id}/edit");
    }

    /**
     * Test route parameter binds by integer ID, not slug, preserving public slug binding.
     */
    public function test_admin_route_model_binding_uses_numeric_id_over_slug(): void
    {
        $project = Project::factory()->create([
            'id' => 42,
            'slug' => 'custom-text-slug-42',
        ]);

        // Access via integer ID succeeds
        $resId = $this->actingAs($this->admin)->get('/admin/projects/42/edit');
        $resId->assertStatus(200);

        // Access via text slug fails with 404 on integer-bound admin route
        $resSlug = $this->actingAs($this->admin)->get('/admin/projects/custom-text-slug-42/edit');
        $resSlug->assertStatus(404);
    }
}
