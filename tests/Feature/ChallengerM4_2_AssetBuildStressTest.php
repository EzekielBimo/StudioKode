<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChallengerM4_2_AssetBuildStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected string $manifestPath;
    protected array $manifestData = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
        ]);

        $this->manifestPath = public_path('build/manifest.json');
        if (file_exists($this->manifestPath)) {
            $json = file_get_contents($this->manifestPath);
            $this->manifestData = json_decode($json, true) ?? [];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 1: Vite Manifest JSON Integrity & Entrypoint Resolution
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Check 1: Manifest exists, is readable, and contains valid JSON.
     */
    public function test_manifest_file_exists_and_is_valid_json(): void
    {
        $this->assertFileExists($this->manifestPath, 'Vite build manifest does not exist at public/build/manifest.json.');
        $this->assertIsReadable($this->manifestPath, 'Vite build manifest is not readable.');

        $content = file_get_contents($this->manifestPath);
        $this->assertNotEmpty($content, 'Vite build manifest is empty.');

        $decoded = json_decode($content, true);
        $this->assertSame(
            JSON_ERROR_NONE,
            json_last_error(),
            'Vite manifest.json contains invalid JSON: ' . json_last_error_msg()
        );
        $this->assertIsArray($decoded, 'Decoded manifest must be an associative array.');
        $this->assertGreaterThanOrEqual(15, count($decoded), 'Manifest must contain at least 15 asset entries.');
    }

    /**
     * Empirical Check 2: Manifest maps resources/js/app.jsx to an optimized chunk.
     */
    public function test_manifest_resolves_main_app_entrypoint_with_code_splitting(): void
    {
        $this->assertArrayHasKey(
            'resources/js/app.jsx',
            $this->manifestData,
            'Manifest does not contain resources/js/app.jsx entry.'
        );

        $entry = $this->manifestData['resources/js/app.jsx'];
        $this->assertTrue(
            !empty($entry['isEntry']),
            'resources/js/app.jsx must have isEntry flag set to true.'
        );
        $this->assertArrayHasKey('file', $entry, 'app.jsx entry missing file property.');
        $this->assertStringStartsWith('assets/', $entry['file'], 'app.jsx bundle must reside in assets/.');
        $this->assertStringEndsWith('.js', $entry['file'], 'app.jsx bundle must have .js extension.');

        $physicalPath = public_path('build/' . $entry['file']);
        $this->assertFileExists($physicalPath, "Physical app bundle missing at {$physicalPath}");
        $size = filesize($physicalPath);
        $this->assertGreaterThan(0, $size, 'app.jsx bundle must not be 0 bytes.');

        // Entry point should be lean due to code splitting (under 50 KB raw)
        $this->assertLessThanOrEqual(
            50 * 1024,
            $size,
            "Entry point bundle size ({$size} bytes) exceeds 50 KB limit, indicating vendor code leaking into main entry."
        );

        // Entry point imports vendor-react and vendor-inertia
        $this->assertArrayHasKey('imports', $entry, 'app.jsx entry must define imported vendor chunks.');
        $hasReactVendorImport = false;
        $hasInertiaVendorImport = false;
        foreach ($entry['imports'] as $importKey) {
            if (str_contains($importKey, 'vendor-react')) {
                $hasReactVendorImport = true;
            }
            if (str_contains($importKey, 'vendor-inertia')) {
                $hasInertiaVendorImport = true;
            }
        }
        $this->assertTrue($hasReactVendorImport, 'app.jsx entry chunk must import vendor-react chunk.');
        $this->assertTrue($hasInertiaVendorImport, 'app.jsx entry chunk must import vendor-inertia chunk.');
    }

    /**
     * Empirical Check 3: Manifest maps resources/css/app.css to a compiled stylesheet.
     */
    public function test_manifest_resolves_main_css_entrypoint(): void
    {
        $this->assertArrayHasKey(
            'resources/css/app.css',
            $this->manifestData,
            'Manifest missing resources/css/app.css entry.'
        );

        $entry = $this->manifestData['resources/css/app.css'];
        $this->assertTrue(!empty($entry['isEntry']), 'CSS entry must have isEntry set to true.');
        $this->assertArrayHasKey('file', $entry, 'CSS entry missing file property.');
        $this->assertStringEndsWith('.css', $entry['file'], 'CSS asset must have .css extension.');

        $physicalPath = public_path('build/' . $entry['file']);
        $this->assertFileExists($physicalPath, "Physical CSS bundle missing at {$physicalPath}");
        $this->assertGreaterThan(5000, filesize($physicalPath), 'CSS bundle should be at least 5 KB.');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Physical Disk Existence Across All Manifest Entries
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Check 4: Every single file referenced in manifest.json physically exists on disk.
     */
    public function test_all_manifest_referenced_files_physically_exist_on_disk(): void
    {
        $this->assertNotEmpty($this->manifestData, 'Manifest data is empty.');

        foreach ($this->manifestData as $entryKey => $entry) {
            $this->assertArrayHasKey(
                'file',
                $entry,
                "Manifest entry [{$entryKey}] is missing the required 'file' property."
            );

            $relativeFile = $entry['file'];
            $this->assertNotEmpty($relativeFile, "Manifest entry [{$entryKey}] has empty file property.");

            $absolutePath = public_path('build/' . $relativeFile);
            $this->assertFileExists(
                $absolutePath,
                "File referenced in manifest [{$entryKey} => {$relativeFile}] does not exist on disk at {$absolutePath}."
            );

            $fileSize = filesize($absolutePath);
            $this->assertGreaterThan(
                0,
                $fileSize,
                "File referenced in manifest [{$relativeFile}] is an empty 0-byte file on disk."
            );

            // Check auxiliary CSS arrays if declared
            if (isset($entry['css']) && is_array($entry['css'])) {
                foreach ($entry['css'] as $cssFile) {
                    $cssPath = public_path('build/' . $cssFile);
                    $this->assertFileExists($cssPath, "Auxiliary CSS [{$cssFile}] for entry [{$entryKey}] missing on disk.");
                    $this->assertGreaterThan(0, filesize($cssPath), "Auxiliary CSS [{$cssFile}] is 0 bytes.");
                }
            }

            // Check auxiliary assets arrays if declared
            if (isset($entry['assets']) && is_array($entry['assets'])) {
                foreach ($entry['assets'] as $assetFile) {
                    $assetPath = public_path('build/' . $assetFile);
                    $this->assertFileExists($assetPath, "Auxiliary asset [{$assetFile}] for entry [{$entryKey}] missing on disk.");
                    $this->assertGreaterThan(0, filesize($assetPath), "Auxiliary asset [{$assetFile}] is 0 bytes.");
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Vendor Chunking Architecture & DAG Dependency Stress Test
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Check 5: Vendor chunks (vendor-react, vendor-inertia, vendor-motion, vendor-lucide) exist and are non-empty.
     */
    public function test_vendor_chunks_exist_and_meet_isolation_contracts(): void
    {
        $requiredVendors = [
            'vendor-react' => ['min' => 40 * 1024, 'max' => 300 * 1024],
            'vendor-inertia' => ['min' => 40 * 1024, 'max' => 300 * 1024],
            'vendor-motion' => ['min' => 30 * 1024, 'max' => 300 * 1024],
            'vendor-lucide' => ['min' => 5 * 1024, 'max' => 100 * 1024],
        ];

        $foundVendors = [];

        foreach ($this->manifestData as $key => $entry) {
            $name = $entry['name'] ?? '';
            $file = $entry['file'] ?? '';

            foreach (array_keys($requiredVendors) as $vendorName) {
                if ($name === $vendorName || str_contains($file, $vendorName) || str_contains($key, $vendorName)) {
                    $foundVendors[$vendorName] = $entry;
                }
            }
        }

        foreach ($requiredVendors as $vendorName => $bounds) {
            $this->assertArrayHasKey(
                $vendorName,
                $foundVendors,
                "Required vendor chunk [{$vendorName}] was not produced by Vite Rollup manualChunks."
            );

            $entry = $foundVendors[$vendorName];
            $chunkPath = public_path('build/' . $entry['file']);
            $this->assertFileExists($chunkPath, "Physical chunk for [{$vendorName}] missing on disk.");

            $size = filesize($chunkPath);
            $this->assertGreaterThanOrEqual(
                $bounds['min'],
                $size,
                "Vendor chunk [{$vendorName}] size ({$size} bytes) is below expected minimum ({$bounds['min']} bytes)."
            );
            $this->assertLessThanOrEqual(
                $bounds['max'],
                $size,
                "Vendor chunk [{$vendorName}] size ({$size} bytes) exceeds expected maximum ({$bounds['max']} bytes)."
            );
        }
    }

    /**
     * Empirical Check 6: Vendor dependency graph is strictly hierarchical with zero circular cycles.
     */
    public function test_vendor_chunk_dependency_graph_is_acyclic_and_hierarchical(): void
    {
        $reactChunkEntry = null;
        $inertiaChunkEntry = null;
        $motionChunkEntry = null;
        $lucideChunkEntry = null;

        foreach ($this->manifestData as $key => $entry) {
            $name = $entry['name'] ?? '';
            if ($name === 'vendor-react' || str_contains($key, 'vendor-react')) {
                $reactChunkEntry = $entry;
            } elseif ($name === 'vendor-inertia' || str_contains($key, 'vendor-inertia')) {
                $inertiaChunkEntry = $entry;
            } elseif ($name === 'vendor-motion' || str_contains($key, 'vendor-motion')) {
                $motionChunkEntry = $entry;
            } elseif ($name === 'vendor-lucide' || str_contains($key, 'vendor-lucide')) {
                $lucideChunkEntry = $entry;
            }
        }

        $this->assertNotNull($reactChunkEntry, 'vendor-react entry not found in manifest.');
        $this->assertNotNull($inertiaChunkEntry, 'vendor-inertia entry not found in manifest.');
        $this->assertNotNull($motionChunkEntry, 'vendor-motion entry not found in manifest.');
        $this->assertNotNull($lucideChunkEntry, 'vendor-lucide entry not found in manifest.');

        // vendor-react is DAG root: must NOT import vendor-inertia, vendor-motion, or vendor-lucide
        $reactImports = $reactChunkEntry['imports'] ?? [];
        foreach ($reactImports as $imp) {
            $this->assertStringNotContainsString('vendor-inertia', $imp, 'vendor-react must not import vendor-inertia.');
            $this->assertStringNotContainsString('vendor-motion', $imp, 'vendor-react must not import vendor-motion.');
            $this->assertStringNotContainsString('vendor-lucide', $imp, 'vendor-react must not import vendor-lucide.');
        }

        // Downstream vendor chunks import vendor-react
        $inertiaImports = $inertiaChunkEntry['imports'] ?? [];
        $inertiaImportsReact = false;
        foreach ($inertiaImports as $imp) {
            if (str_contains($imp, 'vendor-react')) {
                $inertiaImportsReact = true;
            }
        }
        $this->assertTrue($inertiaImportsReact, 'vendor-inertia must declare vendor-react as downstream dependency.');

        $motionImports = $motionChunkEntry['imports'] ?? [];
        $motionImportsReact = false;
        foreach ($motionImports as $imp) {
            if (str_contains($imp, 'vendor-react')) {
                $motionImportsReact = true;
            }
        }
        $this->assertTrue($motionImportsReact, 'vendor-motion must declare vendor-react as downstream dependency.');

        $lucideImports = $lucideChunkEntry['imports'] ?? [];
        $lucideImportsReact = false;
        foreach ($lucideImports as $imp) {
            if (str_contains($imp, 'vendor-react')) {
                $lucideImportsReact = true;
            }
        }
        $this->assertTrue($lucideImportsReact, 'vendor-lucide must declare vendor-react as downstream dependency.');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Inertia Dynamic Page Entries Integrity
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Check 7: All Inertia administrative and public page components are mapped as dynamic entries.
     */
    public function test_inertia_admin_and_public_page_chunks_exist_as_dynamic_entries(): void
    {
        $expectedPages = [
            'resources/js/Pages/Admin/Auth/Login.jsx',
            'resources/js/Pages/Admin/Dashboard/Index.jsx',
            'resources/js/Pages/Admin/Inquiries/Index.jsx',
            'resources/js/Pages/Admin/Inquiries/Show.jsx',
            'resources/js/Pages/Admin/Projects/Index.jsx',
            'resources/js/Pages/Admin/Projects/Create.jsx',
            'resources/js/Pages/Admin/Projects/Edit.jsx',
            'resources/js/Pages/Admin/Projects/Gallery.jsx',
            'resources/js/Pages/Home/Index.jsx',
            'resources/js/Pages/Projects/Index.jsx',
            'resources/js/Pages/Projects/Show.jsx',
            'resources/js/Pages/Studio/Index.jsx',
            'resources/js/Pages/Services/Index.jsx',
            'resources/js/Pages/Contact/Index.jsx',
        ];

        foreach ($expectedPages as $pagePath) {
            $this->assertArrayHasKey(
                $pagePath,
                $this->manifestData,
                "Inertia page [{$pagePath}] missing from manifest.json."
            );

            $pageEntry = $this->manifestData[$pagePath];
            $this->assertTrue(
                !empty($pageEntry['isDynamicEntry']),
                "Page [{$pagePath}] must be flagged as isDynamicEntry for code splitting."
            );

            $chunkFile = public_path('build/' . $pageEntry['file']);
            $this->assertFileExists($chunkFile, "Compiled chunk file for page [{$pagePath}] missing on disk.");
            $this->assertGreaterThan(0, filesize($chunkFile), "Compiled chunk file for page [{$pagePath}] is 0 bytes.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Storage Symlink & Public Storage File Mapping Stress Test
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Check 8: public/storage symlink/junction correctly bridges to storage/app/public.
     */
    public function test_public_storage_symlink_directory_mapping_integrity(): void
    {
        $publicStorage = public_path('storage');
        $appPublic = storage_path('app/public');

        $this->assertTrue(file_exists($publicStorage), 'public/storage symlink/junction does not exist.');
        $this->assertTrue(is_dir($publicStorage), 'public/storage is not a directory/junction.');

        // Test bi-directional write/read bridge through junction
        $probeFileName = '__probe_test_' . uniqid() . '.txt';
        $probePayload = 'Probe payload for junction verification: ' . microtime(true);

        $targetFile = $appPublic . DIRECTORY_SEPARATOR . $probeFileName;
        $linkedFile = $publicStorage . DIRECTORY_SEPARATOR . $probeFileName;

        try {
            file_put_contents($targetFile, $probePayload);

            $this->assertFileExists(
                $linkedFile,
                'File written to storage/app/public failed to appear in public/storage.'
            );
            $this->assertSame(
                $probePayload,
                file_get_contents($linkedFile),
                'Content read from public/storage does not match payload written to storage/app/public.'
            );
            $this->assertSame(
                hash_file('sha256', $targetFile),
                hash_file('sha256', $linkedFile),
                'SHA-256 hashes of storage/app/public and public/storage files do not match.'
            );
        } finally {
            if (file_exists($targetFile)) {
                @unlink($targetFile);
            }
            if (file_exists($linkedFile)) {
                @unlink($linkedFile);
            }
        }
    }

    /**
     * Empirical Check 9: Project cover image path storage mapping and URL resolution.
     */
    public function test_storage_mapping_and_serving_for_project_cover_images(): void
    {
        $disk = Storage::disk('public');
        $coverRelativePath = 'projects/covers/stress_test_cover_' . uniqid() . '.jpg';
        $coverBytes = 'BINARY_JPEG_COVER_MOCK_PAYLOAD_' . microtime(true);

        try {
            $saved = $disk->put($coverRelativePath, $coverBytes);
            $this->assertTrue($saved, 'Failed to write project cover image to public disk.');

            // Verify disk exists
            $this->assertTrue($disk->exists($coverRelativePath));

            // Verify physical existence at public/storage
            $physicalPath = public_path('storage/' . $coverRelativePath);
            $this->assertFileExists($physicalPath, "Project cover not accessible via public/storage at {$physicalPath}");
            $this->assertSame($coverBytes, file_get_contents($physicalPath));

            // Verify URL resolution
            $url = $disk->url($coverRelativePath);
            $this->assertStringContainsString('/storage/' . $coverRelativePath, $url);
            $this->assertStringNotContainsString('//storage', $url, 'URL should not have double slashes.');
        } finally {
            $disk->delete($coverRelativePath);
            $this->assertFalse($disk->exists($coverRelativePath));
        }
    }

    /**
     * Empirical Check 10: Project masterplan image path storage mapping and URL resolution.
     */
    public function test_storage_mapping_and_serving_for_project_masterplan_images(): void
    {
        $disk = Storage::disk('public');
        $masterplanRelativePath = 'projects/masterplans/stress_test_masterplan_' . uniqid() . '.png';
        $masterplanBytes = 'BINARY_PNG_MASTERPLAN_MOCK_PAYLOAD_' . microtime(true);

        try {
            $saved = $disk->put($masterplanRelativePath, $masterplanBytes);
            $this->assertTrue($saved, 'Failed to write project masterplan image to public disk.');

            // Verify disk exists
            $this->assertTrue($disk->exists($masterplanRelativePath));

            // Verify physical existence at public/storage
            $physicalPath = public_path('storage/' . $masterplanRelativePath);
            $this->assertFileExists($physicalPath, "Masterplan not accessible via public/storage at {$physicalPath}");
            $this->assertSame($masterplanBytes, file_get_contents($physicalPath));

            // Verify URL resolution
            $url = $disk->url($masterplanRelativePath);
            $this->assertStringContainsString('/storage/' . $masterplanRelativePath, $url);
        } finally {
            $disk->delete($masterplanRelativePath);
            $this->assertFalse($disk->exists($masterplanRelativePath));
        }
    }

    /**
     * Empirical Check 11: Gallery image path storage mapping and URL resolution.
     */
    public function test_storage_mapping_and_serving_for_gallery_images(): void
    {
        $disk = Storage::disk('public');
        $galleryRelativePath = 'projects/gallery/stress_test_gallery_' . uniqid() . '.webp';
        $galleryBytes = 'BINARY_WEBP_GALLERY_MOCK_PAYLOAD_' . microtime(true);

        try {
            $saved = $disk->put($galleryRelativePath, $galleryBytes);
            $this->assertTrue($saved, 'Failed to write gallery image to public disk.');

            // Verify disk exists
            $this->assertTrue($disk->exists($galleryRelativePath));

            // Verify physical existence at public/storage
            $physicalPath = public_path('storage/' . $galleryRelativePath);
            $this->assertFileExists($physicalPath, "Gallery image not accessible via public/storage at {$physicalPath}");
            $this->assertSame($galleryBytes, file_get_contents($physicalPath));

            // Verify URL resolution
            $url = $disk->url($galleryRelativePath);
            $this->assertStringContainsString('/storage/' . $galleryRelativePath, $url);
        } finally {
            $disk->delete($galleryRelativePath);
            $this->assertFalse($disk->exists($galleryRelativePath));
        }
    }

    /**
     * Empirical Check 12: Real HTTP controller upload lifecycle writes to physical public/storage and cleans up cleanly.
     */
    public function test_end_to_end_project_and_gallery_upload_to_real_storage_without_fake(): void
    {
        // We do NOT call Storage::fake('public') here — we test the REAL storage pipeline!
        $coverFile = UploadedFile::fake()->image('real_stress_cover.jpg', 640, 480);
        $masterplanFile = UploadedFile::fake()->image('real_stress_masterplan.png', 800, 600);

        $projectPayload = [
            'title' => 'Empirical Storage Sanctuary ' . uniqid(),
            'slug' => 'empirical-storage-sanctuary-' . uniqid(),
            'description' => 'Real disk upload verification testing physical public/storage accessibility.',
            'location' => 'Bandung, Indonesia',
            'year' => 2026,
            'area_size' => '3,500 m²',
            'cover_image' => $coverFile,
            'masterplan_image' => $masterplanFile,
            'is_featured' => true,
        ];

        $coverCreatedPath = null;
        $masterplanCreatedPath = null;
        $galleryCreatedPath = null;
        $createdProject = null;

        try {
            // 1. Post project creation
            $response = $this->actingAs($this->admin)->post('/admin/projects', $projectPayload);
            $response->assertStatus(302);
            $response->assertSessionHas('success');

            $createdProject = Project::where('slug', $projectPayload['slug'])->firstOrFail();
            $coverCreatedPath = $createdProject->cover_image;
            $masterplanCreatedPath = $createdProject->masterplan_image;

            $this->assertNotNull($coverCreatedPath, 'Project cover image path is null.');
            $this->assertNotNull($masterplanCreatedPath, 'Project masterplan image path is null.');

            // Verify physical existence via public_path('storage/...')
            $physicalCover = public_path('storage/' . $coverCreatedPath);
            $this->assertFileExists($physicalCover, "Real cover image missing at {$physicalCover}");
            $this->assertGreaterThan(0, filesize($physicalCover), 'Cover image is 0 bytes.');

            $physicalMasterplan = public_path('storage/' . $masterplanCreatedPath);
            $this->assertFileExists($physicalMasterplan, "Real masterplan image missing at {$physicalMasterplan}");
            $this->assertGreaterThan(0, filesize($physicalMasterplan), 'Masterplan image is 0 bytes.');

            // 2. Post gallery image upload
            $galleryUploadFile = UploadedFile::fake()->image('real_stress_gallery.jpg', 1024, 768);
            $galleryResponse = $this->actingAs($this->admin)->post("/admin/projects/{$createdProject->id}/images", [
                'images' => [$galleryUploadFile],
                'captions' => ['Empirical gallery physical probe caption'],
            ]);
            $galleryResponse->assertStatus(302);

            $createdImage = ProjectImage::where('project_id', $createdProject->id)->firstOrFail();
            $galleryCreatedPath = $createdImage->image_path;

            $physicalGallery = public_path('storage/' . $galleryCreatedPath);
            $this->assertFileExists($physicalGallery, "Real gallery image missing at {$physicalGallery}");
            $this->assertGreaterThan(0, filesize($physicalGallery), 'Gallery image is 0 bytes.');

            // 3. Delete project via admin endpoint (cascade removes images and files)
            $deleteResponse = $this->actingAs($this->admin)->delete("/admin/projects/{$createdProject->id}");
            $deleteResponse->assertStatus(302);

            // Assert database deletion
            $this->assertDatabaseMissing('projects', ['id' => $createdProject->id]);
            $this->assertDatabaseMissing('project_images', ['id' => $createdImage->id]);

            // Assert physical files deleted from storage
            $this->assertFileDoesNotExist(
                $physicalCover,
                "Cover image file was not deleted upon project deletion: {$physicalCover}"
            );
            $this->assertFileDoesNotExist(
                $physicalMasterplan,
                "Masterplan image file was not deleted upon project deletion: {$physicalMasterplan}"
            );
            $this->assertFileDoesNotExist(
                $physicalGallery,
                "Gallery image file was not deleted upon project deletion: {$physicalGallery}"
            );
        } finally {
            // Safety cleanup if any assertion failed prior to deletion
            $disk = Storage::disk('public');
            if ($coverCreatedPath && $disk->exists($coverCreatedPath)) {
                $disk->delete($coverCreatedPath);
            }
            if ($masterplanCreatedPath && $disk->exists($masterplanCreatedPath)) {
                $disk->delete($masterplanCreatedPath);
            }
            if ($galleryCreatedPath && $disk->exists($galleryCreatedPath)) {
                $disk->delete($galleryCreatedPath);
            }
            if ($createdProject && Project::where('id', $createdProject->id)->exists()) {
                $createdProject->delete();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 6: Edge Cases & Configuration Resiliency
    |--------------------------------------------------------------------------
    */

    /**
     * Empirical Check 13: vite.config.js manualChunks specifies normalized backslash replacements and clean limits.
     */
    public function test_vite_configuration_enforces_manual_vendor_chunks(): void
    {
        $viteConfigPath = base_path('vite.config.js');
        $this->assertFileExists($viteConfigPath);

        $content = file_get_contents($viteConfigPath);

        $this->assertStringContainsString('manualChunks', $content);
        $this->assertStringContainsString('vendor-react', $content);
        $this->assertStringContainsString('vendor-inertia', $content);
        $this->assertStringContainsString('vendor-motion', $content);
        $this->assertStringContainsString('vendor-lucide', $content);
        $this->assertStringContainsString('chunkSizeWarningLimit', $content);

        // Windows path separator normalization
        $this->assertStringContainsString("replace(/\\\\/g, '/')", $content, 'vite.config.js must normalize backslashes for cross-platform compatibility.');
    }

    /**
     * Empirical Check 14: filesystems.php public URL configuration default adheres to relative /storage.
     */
    public function test_storage_url_configuration_adheres_to_project_spec(): void
    {
        $url = config('filesystems.disks.public.url');
        $this->assertSame(
            '/storage',
            $url,
            "Expected filesystems.disks.public.url to be '/storage', got '{$url}'."
        );

        $sampleUrl = Storage::disk('public')->url('sample.jpg');
        $this->assertSame(
            '/storage/sample.jpg',
            $sampleUrl,
            "Expected Storage::disk('public')->url('sample.jpg') to be '/storage/sample.jpg', got '{$sampleUrl}'."
        );
    }
}
