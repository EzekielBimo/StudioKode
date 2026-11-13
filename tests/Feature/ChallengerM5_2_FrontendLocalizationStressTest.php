<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Challenger M5.2: Frontend, Localization & UI State Integrity Hardening Test Suite.
 *
 * White-box adversarial coverage hardening targeting:
 * 1. Indonesian localization dictionary coverage and AST/static integrity (resources/js/lib/id.js).
 * 2. Inertia page component bindings and props contract conformance across all admin routes.
 * 3. Validation error message localization in Bahasa Indonesia.
 * 4. Flash notification localization in Bahasa Indonesia across all CRUD lifecycles.
 * 5. Bilingual status workflow cohesion and aggregate count mathematics.
 * 6. UI state consistency, accessibility tokens, and anti-CLS layout constraints.
 */
class ChallengerM5_2_FrontendLocalizationStressTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'StudioKode Administrator',
            'email' => 'admin@studiokode.com',
        ]);

        Storage::fake('public');
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 1: Indonesian Dictionary Coverage & Static Architecture
    |--------------------------------------------------------------------------
    */

    /**
     * Test that resources/js/lib/id.js exists, exports an object with all required
     * system modules and non-empty Indonesian translation strings.
     */
    public function test_indonesian_dictionary_covers_all_system_modules_and_namespaces(): void
    {
        $idFilePath = resource_path('js/lib/id.js');
        $this->assertFileExists($idFilePath, 'Expected Indonesian dictionary at resources/js/lib/id.js');

        $content = file_get_contents($idFilePath);
        $this->assertNotEmpty($content);

        // Required top-level dictionary namespaces
        $requiredNamespaces = [
            'brand:',
            'auth:',
            'nav:',
            'common:',
            'status:',
            'dashboard:',
            'inquiries:',
            'projects:',
            'gallery:',
            'validation:',
            'flash:',
        ];

        foreach ($requiredNamespaces as $ns) {
            $this->assertStringContainsString(
                $ns,
                $content,
                "Expected namespace '{$ns}' to be defined in resources/js/lib/id.js"
            );
        }

        // Verify key Indonesian architectural and administrative terms
        $mandatoryIndonesianTerms = [
            'Panel Administrasi StudioKode',
            'Masuk ke Panel Admin',
            'Alamat Email',
            'Kata Sandi',
            'Dasbor',
            'Manajemen Proyek',
            'Pesan Konsultasi',
            'Tambah Proyek',
            'Ubah Proyek',
            'Galeri Foto',
            'Urutan Tampilan Galeri',
            'Takarir (Caption)',
            'Unggah Foto Terpilih',
            'Tindakan ini tidak dapat dibatalkan',
            'Kredensial yang diberikan tidak cocok dengan data kami.',
            'Anda telah berhasil keluar dari sesi admin.',
        ];

        foreach ($mandatoryIndonesianTerms as $term) {
            $this->assertStringContainsString(
                $term,
                $content,
                "Expected mandatory Indonesian phrase '{$term}' in dictionary."
            );
        }
    }

    /**
     * Test that the dictionary status block provides full bilingual mapping
     * for inquiry and project status badges.
     */
    public function test_indonesian_dictionary_contains_bilingual_status_mappings(): void
    {
        $content = file_get_contents(resource_path('js/lib/id.js'));

        $statusMappings = [
            'baru:' => "'Baru'",
            'new:' => "'Baru'",
            'dihubungi:' => "'Dihubungi'",
            'contacted:' => "'Dihubungi'",
            'diarsipkan:' => "'Diarsipkan'",
            'archived:' => "'Diarsipkan'",
            'unggulan:' => "'Unggulan'",
            'featured:' => "'Unggulan'",
            'standar:' => "'Standar'",
            'standard:' => "'Standar'",
            'terbit:' => "'Terbit'",
            'published:' => "'Terbit'",
            'draf:' => "'Draf'",
            'draft:' => "'Draf'",
        ];

        foreach ($statusMappings as $key => $expectedVal) {
            $this->assertStringContainsString(
                $key,
                $content,
                "Expected status dictionary to contain key {$key}"
            );
            $this->assertStringContainsString(
                $expectedVal,
                $content,
                "Expected status dictionary to contain value {$expectedVal}"
            );
        }
    }

    /**
     * Test that all administrative frontend components import id.js and consume it.
     */
    public function test_frontend_admin_components_import_and_consume_indonesian_dictionary(): void
    {
        $adminComponents = [
            'Layouts/AdminLayout.jsx',
            'Pages/Admin/Auth/Login.jsx',
            'Pages/Admin/Dashboard/Index.jsx',
            'Pages/Admin/Inquiries/Index.jsx',
            'Pages/Admin/Inquiries/Show.jsx',
            'Pages/Admin/Projects/Index.jsx',
            'Pages/Admin/Projects/Create.jsx',
            'Pages/Admin/Projects/Edit.jsx',
            'Pages/Admin/Projects/Gallery.jsx',
            'Components/Admin/ProjectGalleryManager.jsx',
        ];

        foreach ($adminComponents as $relPath) {
            $fullPath = resource_path("js/{$relPath}");
            $this->assertFileExists($fullPath, "Expected admin component at resources/js/{$relPath}");

            $content = file_get_contents($fullPath);

            // Assert component imports id from lib/id
            $this->assertTrue(
                str_contains($content, "from '../lib/id'") ||
                str_contains($content, "from '../../lib/id'") ||
                str_contains($content, "from '../../../lib/id'"),
                "Expected component {$relPath} to import dictionary from lib/id"
            );

            // Assert component actually accesses id namespace
            $this->assertStringContainsString(
                'id.',
                $content,
                "Expected component {$relPath} to consume id dictionary tokens"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 2: Inertia Administrative Route & Prop Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Test GET /admin/login returns Inertia component Admin/Auth/Login with null auth user.
     */
    public function test_admin_login_inertia_contract_and_unauthenticated_state(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->component('Admin/Auth/Login')
                ->has('auth.user', null)
                ->has('appName')
                ->has('flash');
        });
    }

    /**
     * Test GET /admin renders Inertia component Admin/Dashboard/Index with complete
     * metrics and recent activity collections.
     */
    public function test_admin_dashboard_inertia_contract_and_kpi_metrics(): void
    {
        Project::factory()->create([
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);
        Inquiry::factory()->create(['status' => 'baru']);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->component('Admin/Dashboard/Index')
                ->has('metrics', function (Assert $metrics) {
                    $metrics->has('total_projects')
                        ->has('featured_projects')
                        ->has('published_projects')
                        ->has('total_inquiries')
                        ->has('new_inquiries');
                })
                ->has('recent_projects')
                ->has('recent_inquiries')
                ->has('auth.user', function (Assert $user) {
                    $user->where('email', 'admin@studiokode.com')
                        ->where('name', 'StudioKode Administrator')
                        ->has('id');
                });
        });
    }

    /**
     * Test GET /admin/projects renders Inertia component Admin/Projects/Index with
     * paginated records and eager-loaded images_count.
     */
    public function test_admin_projects_index_inertia_contract_and_pagination(): void
    {
        $project = Project::factory()->create();
        ProjectImage::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($this->admin)->get('/admin/projects');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->component('Admin/Projects/Index')
                ->has('projects.data', 1)
                ->has('projects.data.0', function (Assert $item) {
                    $item->has('id')
                        ->has('title')
                        ->has('slug')
                        ->has('location')
                        ->has('year')
                        ->has('area_size')
                        ->has('is_featured')
                        ->has('published_at')
                        ->has('images_count')
                        ->etc();
                })
                ->has('filters', function (Assert $filters) {
                    $filters->has('search')->has('featured');
                });
        });
    }

    /**
     * Test GET /admin/projects/create renders Inertia component Admin/Projects/Create.
     */
    public function test_admin_projects_create_inertia_contract(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/projects/create');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->component('Admin/Projects/Create')
                ->has('auth.user');
        });
    }

    /**
     * Test GET /admin/projects/{id}/edit renders Inertia component Admin/Projects/Edit
     * with eager-loaded gallery images ordered by sort_order.
     */
    public function test_admin_projects_edit_inertia_contract_and_eager_loaded_images(): void
    {
        $project = Project::factory()->create();
        ProjectImage::factory()->create([
            'project_id' => $project->id,
            'sort_order' => 2,
            'caption' => 'Second image',
        ]);
        ProjectImage::factory()->create([
            'project_id' => $project->id,
            'sort_order' => 1,
            'caption' => 'First image',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) use ($project) {
            $page->component('Admin/Projects/Edit')
                ->has('project', function (Assert $p) use ($project) {
                    $p->where('id', $project->id)
                        ->where('title', $project->title)
                        ->has('images', 2)
                        ->where('images.0.sort_order', 1)
                        ->where('images.0.caption', 'First image')
                        ->where('images.1.sort_order', 2)
                        ->where('images.1.caption', 'Second image')
                        ->etc();
                });
        });
    }

    /**
     * Test GET /admin/inquiries renders Inertia component Admin/Inquiries/Index with
     * paginated data, status filter, and all status counts.
     */
    public function test_admin_inquiries_index_inertia_contract_and_status_counts(): void
    {
        Inquiry::factory()->create(['status' => 'baru']);
        Inquiry::factory()->create(['status' => 'dihubungi']);
        Inquiry::factory()->create(['status' => 'diarsipkan']);

        $response = $this->actingAs($this->admin)->get('/admin/inquiries');

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) {
            $page->component('Admin/Inquiries/Index')
                ->has('inquiries.data', 3)
                ->has('filters.status')
                ->has('filters.search')
                ->has('status_counts', function (Assert $counts) {
                    $counts->where('all', 3)
                        ->where('baru', 1)
                        ->where('dihubungi', 1)
                        ->where('diarsipkan', 1);
                });
        });
    }

    /**
     * Test GET /admin/inquiries/{id} renders Inertia component Admin/Inquiries/Show with
     * complete client consultation attributes.
     */
    public function test_admin_inquiries_show_inertia_contract_and_detail_fields(): void
    {
        $inquiry = Inquiry::factory()->create([
            'name' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'phone' => '+62812345678',
            'site_location' => 'Sanur, Bali',
            'estimated_area' => '1,500 m²',
            'budget_range' => 'Rp 500jt - 1M',
            'message' => 'Rancangan lanskap taman botani tropis.',
            'status' => 'baru',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(200);
        $response->assertInertia(function (Assert $page) use ($inquiry) {
            $page->component('Admin/Inquiries/Show')
                ->has('inquiry', function (Assert $item) use ($inquiry) {
                    $item->where('id', $inquiry->id)
                        ->where('name', 'Budi Pratama')
                        ->where('email', 'budi@example.com')
                        ->where('phone', '+62812345678')
                        ->where('site_location', 'Sanur, Bali')
                        ->where('estimated_area', '1,500 m²')
                        ->where('budget_range', 'Rp 500jt - 1M')
                        ->where('message', 'Rancangan lanskap taman botani tropis.')
                        ->where('status', 'baru')
                        ->etc();
                });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 3: Validation Error Messages Localization Stress Test
    |--------------------------------------------------------------------------
    */

    /**
     * Stress-test that login validation failures return clear Indonesian messages.
     */
    public function test_login_validation_errors_are_strictly_in_indonesian(): void
    {
        // 1. Missing credentials
        $resEmpty = $this->post('/admin/login', []);
        $resEmpty->assertSessionHasErrors([
            'email' => 'Kolom alamat email wajib diisi.',
            'password' => 'Kolom kata sandi wajib diisi.',
        ]);

        // 2. Malformed email address
        $resInvalidEmail = $this->post('/admin/login', [
            'email' => 'not-a-valid-email',
            'password' => 'secret123',
        ]);
        $resInvalidEmail->assertSessionHasErrors([
            'email' => 'Format alamat email tidak valid.',
        ]);

        // 3. Unregistered credentials / mismatch
        $resBadCreds = $this->post('/admin/login', [
            'email' => 'nonexistent@studiokode.com',
            'password' => 'wrongpassword',
        ]);
        $resBadCreds->assertSessionHasErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ]);
    }

    /**
     * Stress-test that project store validation failures return clear Indonesian messages.
     */
    public function test_project_store_validation_errors_are_strictly_in_indonesian(): void
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

    /**
     * Stress-test that duplicate project slug returns clear Indonesian error message.
     */
    public function test_project_store_duplicate_slug_validation_error_in_indonesian(): void
    {
        Project::factory()->create(['slug' => 'taman-sastra-bali']);

        $payload = [
            'title' => 'Taman Sastra Bali Kedua',
            'slug' => 'taman-sastra-bali',
            'description' => 'Deskripsi botani taman lanskap.',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '800 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'slug' => 'Slug proyek sudah digunakan. Silakan gunakan slug lain.',
        ]);
    }

    /**
     * Stress-test that invalid file types on project store return clear Indonesian errors.
     */
    public function test_project_store_invalid_image_validation_errors_in_indonesian(): void
    {
        $fakeText = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');

        $payload = [
            'title' => 'Proyek Uji Berkas',
            'description' => 'Deskripsi proyek.',
            'location' => 'Jimbaran, Bali',
            'year' => 2024,
            'area_size' => '500 m²',
            'cover_image' => $fakeText,
            'masterplan_image' => $fakeText,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'cover_image' => 'File gambar sampul harus berupa berkas gambar yang valid.',
            'masterplan_image' => 'File gambar masterplan harus berupa berkas gambar yang valid.',
        ]);
    }

    /**
     * Stress-test that project update validation failures return clear Indonesian messages.
     */
    public function test_project_update_validation_errors_are_strictly_in_indonesian(): void
    {
        $p1 = Project::factory()->create(['slug' => 'proyek-pertama']);
        $p2 = Project::factory()->create(['slug' => 'proyek-kedua']);

        // Missing required fields on update
        $resMissing = $this->actingAs($this->admin)->put("/admin/projects/{$p1->id}", [
            'title' => '',
            'description' => '',
            'location' => '',
            'year' => '',
            'area_size' => '',
        ]);
        $resMissing->assertSessionHasErrors([
            'title' => 'Judul proyek wajib diisi.',
            'description' => 'Deskripsi proyek wajib diisi.',
            'location' => 'Lokasi proyek wajib diisi.',
            'year' => 'Tahun proyek wajib diisi.',
            'area_size' => 'Luas area proyek wajib diisi.',
        ]);

        // Duplicate slug colliding with p2
        $resCol = $this->actingAs($this->admin)->put("/admin/projects/{$p1->id}", [
            'title' => 'Proyek Pertama Diubah',
            'slug' => 'proyek-kedua',
            'description' => 'Deskripsi baru.',
            'location' => 'Denpasar, Bali',
            'year' => 2024,
            'area_size' => '1,000 m²',
        ]);
        $resCol->assertSessionHasErrors([
            'slug' => 'Slug proyek sudah digunakan oleh proyek lain.',
        ]);
    }

    /**
     * Stress-test that inquiry status update validation failures return Indonesian messages.
     */
    public function test_inquiry_status_update_validation_errors_in_indonesian(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        // Missing status
        $resMissing = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", []);
        $resMissing->assertSessionHasErrors([
            'status' => 'Status konsultasi wajib dipilih.',
        ]);

        // Invalid status value
        $resInvalid = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'status_tidak_dikenal',
        ]);
        $resInvalid->assertSessionHasErrors([
            'status' => 'Status konsultasi yang dipilih tidak valid.',
        ]);
    }

    /**
     * Stress-test gallery multi-upload validation errors in Indonesian.
     */
    public function test_gallery_multi_upload_validation_errors_in_indonesian(): void
    {
        $project = Project::factory()->create();

        // 1. Missing images array
        $resEmpty = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", []);
        $resEmpty->assertSessionHasErrors([
            'images' => 'Minimal satu berkas gambar galeri harus dipilih.',
        ]);

        // 2. Non-image file format
        $pdfFile = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');
        $resNonImg = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => [$pdfFile],
        ]);
        $resNonImg->assertSessionHasErrors('images.0');
    }

    /**
     * Stress-test gallery caption update validation errors in Indonesian.
     */
    public function test_gallery_caption_update_validation_errors_in_indonesian(): void
    {
        $project = Project::factory()->create();
        $image = ProjectImage::factory()->create(['project_id' => $project->id]);

        // Caption exceeding 255 chars
        $resOversized = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", [
            'caption' => str_repeat('A', 256),
        ]);
        $resOversized->assertSessionHasErrors([
            'caption' => 'Takarir foto maksimal 255 karakter.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 4: Flash Notification Indonesian Localization Stress Test
    |--------------------------------------------------------------------------
    */

    /**
     * Test authentication lifecycle returns localized Indonesian flash notifications.
     */
    public function test_auth_flash_notifications_are_in_indonesian(): void
    {
        // 1. Login success flash
        $loginRes = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);
        $loginRes->assertRedirect('/admin');
        $loginRes->assertSessionHas('success', 'Selamat datang kembali di Panel Admin StudioKode.');

        // 2. Logout success flash
        $logoutRes = $this->actingAs($this->admin)->post('/admin/logout');
        $logoutRes->assertRedirect('/admin/login');
        $logoutRes->assertSessionHas('success', 'Anda telah berhasil keluar dari sesi admin.');
    }

    /**
     * Test project CRUD lifecycle returns localized Indonesian flash notifications.
     */
    public function test_project_crud_flash_notifications_are_in_indonesian(): void
    {
        // 1. Create project flash
        $createRes = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Villa Botani Canggu',
            'slug' => 'villa-botani-canggu',
            'description' => 'Konsep vegetasi tropis eksotik.',
            'location' => 'Canggu, Bali',
            'year' => 2024,
            'area_size' => '650 m²',
        ]);
        $createRes->assertRedirect(route('admin.projects.index'));
        $createRes->assertSessionHas('success', 'Proyek berhasil ditambahkan.');

        $project = Project::where('slug', 'villa-botani-canggu')->firstOrFail();

        // 2. Update project flash
        $updateRes = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => 'Villa Botani Canggu Diperbarui',
            'slug' => 'villa-botani-canggu',
            'description' => 'Deskripsi diperbarui.',
            'location' => 'Canggu, Bali',
            'year' => 2024,
            'area_size' => '700 m²',
        ]);
        $updateRes->assertRedirect(route('admin.projects.index'));
        $updateRes->assertSessionHas('success', 'Proyek berhasil diperbarui.');

        // 3. Delete project flash
        $deleteRes = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");
        $deleteRes->assertRedirect(route('admin.projects.index'));
        $deleteRes->assertSessionHas('success', 'Proyek berhasil dihapus.');
    }

    /**
     * Test inquiry status update and deletion return localized Indonesian flash notifications.
     */
    public function test_inquiry_status_and_delete_flash_notifications_are_in_indonesian(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        // 1. Status update flash
        $statusRes = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'dihubungi',
        ]);
        $statusRes->assertStatus(302);
        $statusRes->assertSessionHas('success', 'Status pesan konsultasi berhasil diperbarui.');

        // 2. Delete inquiry flash
        $deleteRes = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");
        $deleteRes->assertRedirect(route('admin.inquiries.index'));
        $deleteRes->assertSessionHas('success', 'Pesan konsultasi berhasil dihapus.');
    }

    /**
     * Test gallery CRUD operations return localized Indonesian flash notifications.
     */
    public function test_gallery_crud_flash_notifications_are_in_indonesian(): void
    {
        $project = Project::factory()->create();
        $fakeImage = UploadedFile::fake()->image('photo1.jpg', 800, 600);

        // 1. Gallery upload flash
        $uploadRes = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => [$fakeImage],
            'captions' => ['Foto kanopi taman'],
        ]);
        $uploadRes->assertStatus(302);
        $uploadRes->assertSessionHas('success', 'Foto galeri berhasil diunggah.');

        $image = ProjectImage::where('project_id', $project->id)->firstOrFail();

        // 2. Gallery reorder flash
        $reorderRes = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images/reorder", [
            'orders' => [
                ['id' => $image->id, 'sort_order' => 5],
            ],
        ]);
        $reorderRes->assertStatus(302);
        $reorderRes->assertSessionHas('success', 'Urutan foto galeri berhasil diperbarui.');

        // 3. Caption update flash
        $captionRes = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", [
            'caption' => 'Takarir baru diperbarui',
        ]);
        $captionRes->assertStatus(302);
        $captionRes->assertSessionHas('success', 'Takarir foto berhasil diperbarui.');

        // 4. Delete image flash
        $deleteRes = $this->actingAs($this->admin)->delete("/admin/project-images/{$image->id}");
        $deleteRes->assertStatus(302);
        $deleteRes->assertSessionHas('success', 'Foto galeri berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 5: Bilingual Status Workflow Cohesion & Mathematical Aggregation
    |--------------------------------------------------------------------------
    */

    /**
     * Stress-test that inquiry filtering returns identical result sets whether
     * queried using Indonesian or English aliases ('baru' vs 'new', 'dihubungi' vs 'contacted').
     */
    public function test_inquiry_bilingual_status_filter_cohesion(): void
    {
        Inquiry::factory()->count(3)->create(['status' => 'new']);
        Inquiry::factory()->count(2)->create(['status' => 'baru']);
        Inquiry::factory()->count(4)->create(['status' => 'contacted']);
        Inquiry::factory()->count(1)->create(['status' => 'dihubungi']);
        Inquiry::factory()->count(2)->create(['status' => 'archived']);
        Inquiry::factory()->count(3)->create(['status' => 'diarsipkan']);

        // Total: 5 (new/baru), 5 (contacted/dihubungi), 5 (archived/diarsipkan) = 15

        // Filter by 'baru' vs 'new'
        $resBaru = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru');
        $resNew = $this->actingAs($this->admin)->get('/admin/inquiries?status=new');

        $resBaru->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 5));
        $resNew->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 5));

        // Filter by 'dihubungi' vs 'contacted'
        $resDihubungi = $this->actingAs($this->admin)->get('/admin/inquiries?status=dihubungi');
        $resContacted = $this->actingAs($this->admin)->get('/admin/inquiries?status=contacted');

        $resDihubungi->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 5));
        $resContacted->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 5));

        // Filter by 'diarsipkan' vs 'archived'
        $resDiarsipkan = $this->actingAs($this->admin)->get('/admin/inquiries?status=diarsipkan');
        $resArchived = $this->actingAs($this->admin)->get('/admin/inquiries?status=archived');

        $resDiarsipkan->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 5));
        $resArchived->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 5));
    }

    /**
     * Test that inquiry status counts obey strict mathematical cohesion:
     * count(all) == count(baru) + count(dihubungi) + count(diarsipkan).
     */
    public function test_inquiry_status_counts_aggregation_mathematical_integrity(): void
    {
        Inquiry::factory()->count(4)->create(['status' => 'baru']);
        Inquiry::factory()->count(3)->create(['status' => 'dihubungi']);
        Inquiry::factory()->count(2)->create(['status' => 'diarsipkan']);

        $response = $this->actingAs($this->admin)->get('/admin/inquiries');

        $response->assertInertia(function (Assert $page) {
            $props = $page->toArray()['props'];
            $counts = $props['status_counts'];

            $this->assertSame(9, $counts['all']);
            $this->assertSame(4, $counts['baru']);
            $this->assertSame(3, $counts['dihubungi']);
            $this->assertSame(2, $counts['diarsipkan']);
            $this->assertSame($counts['all'], $counts['baru'] + $counts['dihubungi'] + $counts['diarsipkan']);
        });

        // Also assert Dashboard KPI agrees exactly with status_counts.baru
        $dashResponse = $this->actingAs($this->admin)->get('/admin');
        $dashResponse->assertInertia(function (Assert $page) {
            $props = $page->toArray()['props'];
            $metrics = $props['metrics'];

            $this->assertSame(9, $metrics['total_inquiries']);
            $this->assertSame(4, $metrics['new_inquiries']);
        });
    }

    /**
     * Stress-test inquiry search with adversarial symbols, whitespace, and Unicode.
     */
    public function test_inquiries_search_resilience_with_special_characters(): void
    {
        Inquiry::factory()->create([
            'name' => 'Wayan & Ketut Studio',
            'email' => 'ketut+studio@example.com',
            'site_location' => 'Denpasar (Sector 5)',
        ]);

        $adversarialQueries = [
            'Wayan & Ketut',
            'ketut+studio',
            '(Sector 5)',
            '   Wayan   ',
            '\' OR 1=1 --',
            '<script>alert(1)</script>',
            'NonExistentQuery_XYZ999',
        ];

        foreach ($adversarialQueries as $query) {
            $res = $this->actingAs($this->admin)->get('/admin/inquiries?search=' . urlencode($query));
            $res->assertStatus(200);
            $res->assertInertia(fn (Assert $page) => $page->component('Admin/Inquiries/Index'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Suite 6: Frontend Layout, Anti-CLS Design & Component Audit
    |--------------------------------------------------------------------------
    */

    /**
     * Test that AdminLayout.jsx includes required botanical color tokens and accessible structure.
     */
    public function test_admin_layout_botanical_color_tokens_and_accessibility(): void
    {
        $layoutPath = resource_path('js/Layouts/AdminLayout.jsx');
        $this->assertFileExists($layoutPath);

        $content = file_get_contents($layoutPath);

        // Core botanical palette tokens
        $this->assertStringContainsString('#1C2A21', $content, 'Expected Forest Green (#1C2A21) token');
        $this->assertStringContainsString('#F9F8F6', $content, 'Expected Warm Rice/Paper (#F9F8F6) token');
        $this->assertStringContainsString('#E6E2DD', $content, 'Expected Limestone Border (#E6E2DD) token');

        // Accessibility attributes
        $this->assertStringContainsString('aria-label=', $content, 'Expected aria-label attributes for accessibility');
    }

    /**
     * Test that ProjectGalleryManager incorporates anti-CLS aspect-ratio placeholders
     * and lazy-loading for visual stability.
     */
    public function test_project_gallery_manager_cls_prevention_aspect_ratios(): void
    {
        $componentPath = resource_path('js/Components/Admin/ProjectGalleryManager.jsx');
        $this->assertFileExists($componentPath);

        $content = file_get_contents($componentPath);

        // Anti-CLS aspect ratio placeholder
        $this->assertStringContainsString('aspect-[4/3]', $content);
        $this->assertStringContainsString('bg-[#E6E2DD]', $content);

        // Image optimization attributes
        $this->assertStringContainsString('loading="lazy"', $content);
        $this->assertStringContainsString('decoding="async"', $content);

        // Framer Motion Reorder component
        $this->assertStringContainsString('Reorder.Group', $content);
        $this->assertStringContainsString('Reorder.Item', $content);
    }

    /**
     * Audit component file locations and paths as required by PROJECT.md.
     */
    public function test_admin_component_file_locations_audit(): void
    {
        // Auth login location
        $authLoginPath = resource_path('js/Pages/Admin/Auth/Login.jsx');
        $this->assertFileExists($authLoginPath, 'Expected Login component at resources/js/Pages/Admin/Auth/Login.jsx');

        // All administrative views exist
        $this->assertFileExists(resource_path('js/Pages/Admin/Dashboard/Index.jsx'));
        $this->assertFileExists(resource_path('js/Pages/Admin/Inquiries/Index.jsx'));
        $this->assertFileExists(resource_path('js/Pages/Admin/Inquiries/Show.jsx'));
        $this->assertFileExists(resource_path('js/Pages/Admin/Projects/Index.jsx'));
        $this->assertFileExists(resource_path('js/Pages/Admin/Projects/Create.jsx'));
        $this->assertFileExists(resource_path('js/Pages/Admin/Projects/Edit.jsx'));
        $this->assertFileExists(resource_path('js/Pages/Admin/Projects/Gallery.jsx'));
    }
}
