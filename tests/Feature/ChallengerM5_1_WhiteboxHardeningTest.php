<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM5_1_WhiteboxHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
            'password' => bcrypt('password'),
        ]);

        Storage::fake('public');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 1: Auth, Session Invalidation & CSRF Hardening
    |--------------------------------------------------------------------------
    */

    /**
     * Test admin logout invalidates the session and regenerates CSRF token.
     * Feature 3: Admin Logout Action (R1)
     */
    public function test_auth_logout_invalidates_session_and_regenerates_csrf_token(): void
    {
        // 1. Authenticate admin and record session token
        $this->actingAs($this->admin);
        $sessionIdBefore = session()->getId();
        $tokenBefore = session()->token();

        // 2. Perform logout
        $response = $this->post('/admin/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('success', 'Anda telah berhasil keluar dari sesi admin.');

        // 3. Assert user is unauthenticated
        $this->assertFalse(Auth::check());

        // 4. Session ID should differ and previous session should not grant access
        $sessionIdAfter = session()->getId();
        $this->assertNotEquals($sessionIdBefore, $sessionIdAfter);

        // 5. Subsequent access to admin dashboard must be redirected to login
        $followUp = $this->get('/admin');
        $followUp->assertStatus(302);
        $followUp->assertRedirect('/admin/login');
    }

    /**
     * Test unauthenticated access to logout route redirects to login.
     * Feature 3 & 4: Admin Route Protection (R1)
     */
    public function test_unauthenticated_post_logout_redirects_to_login(): void
    {
        $response = $this->post('/admin/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**
     * Test authenticated admin is blocked from login screen and login store by guest middleware.
     * Feature 4: Admin Route Protection (R1)
     */
    public function test_authenticated_admin_blocked_from_login_screen_and_store_by_guest_middleware(): void
    {
        // GET /admin/login redirects to /admin
        $responseGet = $this->actingAs($this->admin)->get('/admin/login');
        $responseGet->assertStatus(302);
        $responseGet->assertRedirect('/admin');

        // POST /admin/login redirects to /admin
        $responsePost = $this->actingAs($this->admin)->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);
        $responsePost->assertStatus(302);
        $responsePost->assertRedirect('/admin');
    }

    /**
     * Test admin login with mixed-case email across database drivers (SQLite binary vs MySQL CI).
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_case_insensitivity_and_whitespace_handling(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'Admin@StudioKode.com',
            'password' => 'password',
        ]);

        if (DB::getDriverName() === 'sqlite') {
            // SQLite binary collation without LOWER() requires exact match
            $response->assertStatus(302);
            $response->assertSessionHasErrors(['email']);
            $this->assertGuest();

            // Login with whitespace-padded lowercase email (TrimStrings middleware handles trim)
            $resTrim = $this->post('/admin/login', [
                'email' => '  admin@studiokode.com  ',
                'password' => 'password',
            ]);
            $resTrim->assertStatus(302);
            $resTrim->assertRedirect('/admin');
            $this->assertAuthenticatedAs($this->admin);
        } else {
            // MySQL utf8mb4_unicode_ci collation matches case-insensitively
            $response->assertStatus(302);
            $response->assertRedirect('/admin');
            $this->assertAuthenticatedAs($this->admin);
        }
    }

    /**
     * Test admin login with extreme length strings and SQL injection payloads.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_with_extreme_length_password_and_sql_injection(): void
    {
        // 5000 character password attempt
        $oversizedPassword = str_repeat('A!@#$%', 1000);
        $responseOversized = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => $oversizedPassword,
        ]);
        $responseOversized->assertStatus(302);
        $responseOversized->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // SQL injection payload in email
        $responseSqli = $this->post('/admin/login', [
            'email' => "admin@studiokode.com' OR '1'='1",
            'password' => 'password',
        ]);
        $responseSqli->assertStatus(302);
        $responseSqli->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test admin login remember me functionality generates remember token.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_remember_me_functionality(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);

        // Assert remember token was updated on user
        $this->admin->refresh();
        $this->assertNotNull($this->admin->getRememberToken());
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Dashboard Aggregation, KPI Metrics & Zero States
    |--------------------------------------------------------------------------
    */

    /**
     * Test dashboard renders cleanly with zero database records (NULL SQL aggregates).
     * Feature 10: Dashboard KPI Metrics (R5)
     */
    public function test_dashboard_renders_cleanly_with_zero_records_in_database(): void
    {
        Project::query()->delete();
        Inquiry::query()->delete();

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->where('metrics.total_projects', 0)
            ->where('metrics.featured_projects', 0)
            ->where('metrics.published_projects', 0)
            ->where('metrics.total_inquiries', 0)
            ->where('metrics.new_inquiries', 0)
            ->has('recent_projects', 0)
            ->has('recent_inquiries', 0)
        );
    }

    /**
     * Test dashboard KPI calculations with combinations of future published, past published, and drafts.
     * Feature 10: Dashboard KPI Metrics (R5)
     */
    public function test_dashboard_metrics_with_future_scheduled_and_draft_projects(): void
    {
        // 3 past published projects (1 featured, 2 normal)
        Project::factory()->create([
            'is_featured' => true,
            'published_at' => Carbon::now()->subDays(5),
        ]);
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => Carbon::now()->subDays(2),
        ]);
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => Carbon::now()->subMinutes(10),
        ]);

        // 2 scheduled future projects (1 featured, 1 normal)
        Project::factory()->create([
            'is_featured' => true,
            'published_at' => Carbon::now()->addDays(3),
        ]);
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => Carbon::now()->addHours(1),
        ]);

        // 1 draft project (null published_at, not featured)
        Project::factory()->create([
            'is_featured' => false,
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->where('metrics.total_projects', 6)
            ->where('metrics.featured_projects', 2)
            ->where('metrics.published_projects', 3)
        );
    }

    /**
     * Test dashboard KPI calculations across all inquiry workflow status variations.
     * Feature 10: Dashboard KPI Metrics (R5)
     */
    public function test_dashboard_metrics_with_all_inquiry_status_variations(): void
    {
        Inquiry::factory()->create(['status' => 'new']);
        Inquiry::factory()->create(['status' => 'baru']);
        Inquiry::factory()->create(['status' => 'contacted']);
        Inquiry::factory()->create(['status' => 'dihubungi']);
        Inquiry::factory()->create(['status' => 'archived']);
        Inquiry::factory()->create(['status' => 'diarsipkan']);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->where('metrics.total_inquiries', 6)
            ->where('metrics.new_inquiries', 2)
        );
    }

    /**
     * Test dashboard recent lists are strictly capped at 5 and ordered descending by created_at.
     * Feature 11: Dashboard Recent Lists (R5)
     */
    public function test_dashboard_recent_lists_strict_capping_and_ordering(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Project::factory()->create([
                'title' => "Project Alpha {$i}",
                'created_at' => Carbon::now()->subMinutes(100 - ($i * 10)),
            ]);
            Inquiry::factory()->create([
                'name' => "Client Alpha {$i}",
                'created_at' => Carbon::now()->subMinutes(100 - ($i * 10)),
            ]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->has('recent_projects', 5)
            ->has('recent_inquiries', 5)
            ->where('recent_projects.0.title', 'Project Alpha 8')
            ->where('recent_inquiries.0.name', 'Client Alpha 8')
        );
    }

    /**
     * Test both /admin and /admin/dashboard resolve identically.
     * Feature 8: Admin Layout & Navigation (R5)
     */
    public function test_dashboard_alias_routes_parity(): void
    {
        $resRoot = $this->actingAs($this->admin)->get('/admin');
        $resDashboard = $this->actingAs($this->admin)->get('/admin/dashboard');

        $resRoot->assertStatus(200);
        $resDashboard->assertStatus(200);

        $resRoot->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->has('metrics')
            ->has('recent_projects')
            ->has('recent_inquiries')
        );

        $resDashboard->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard/Index')
            ->has('metrics')
            ->has('recent_projects')
            ->has('recent_inquiries')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Inquiries Edge Cases, Full-Text Search & Workflow States
    |--------------------------------------------------------------------------
    */

    /**
     * Test inquiries filtering by status aliases, empty values, and non-existent statuses.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_filter_by_status_all_variants_and_fallbacks(): void
    {
        Inquiry::factory()->create(['name' => 'Inq 1', 'status' => 'new']);
        Inquiry::factory()->create(['name' => 'Inq 2', 'status' => 'baru']);
        Inquiry::factory()->create(['name' => 'Inq 3', 'status' => 'contacted']);
        Inquiry::factory()->create(['name' => 'Inq 4', 'status' => 'dihubungi']);
        Inquiry::factory()->create(['name' => 'Inq 5', 'status' => 'archived']);
        Inquiry::factory()->create(['name' => 'Inq 6', 'status' => 'diarsipkan']);

        // Filter 'baru' and 'new' should both return 2 inquiries
        $resBaru = $this->actingAs($this->admin)->get('/admin/inquiries?status=baru');
        $resBaru->assertStatus(200);
        $resBaru->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 2));

        $resNew = $this->actingAs($this->admin)->get('/admin/inquiries?status=new');
        $resNew->assertStatus(200);
        $resNew->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 2));

        // Filter 'dihubungi' and 'contacted'
        $resDihubungi = $this->actingAs($this->admin)->get('/admin/inquiries?status=dihubungi');
        $resDihubungi->assertStatus(200);
        $resDihubungi->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 2));

        // Filter 'all' and 'semua'
        $resAll = $this->actingAs($this->admin)->get('/admin/inquiries?status=all');
        $resAll->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 6));

        $resSemua = $this->actingAs($this->admin)->get('/admin/inquiries?status=semua');
        $resSemua->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 6));

        // Filter non-existent status
        $resNonExistent = $this->actingAs($this->admin)->get('/admin/inquiries?status=nonexistent_state');
        $resNonExistent->assertStatus(200);
        $resNonExistent->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 0));
    }

    /**
     * Test inquiries search handles regex metacharacters, SQL wildcards, and HTML entities without error.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_search_with_regex_metacharacters_sql_wildcards_and_html(): void
    {
        Inquiry::factory()->create([
            'name' => 'Ir. Bambang & Rekan',
            'email' => 'bambang.rekan@zen-garden.id',
            'phone' => '+62 811-2345-6789',
            'site_location' => 'Jimbaran [Zone 4]',
            'message' => 'Proyek resor 100% ramah lingkungan <sustainable>',
        ]);

        // Search with SQL wildcard '%'
        $resPercent = $this->actingAs($this->admin)->get('/admin/inquiries?search=100%');
        $resPercent->assertStatus(200);
        $resPercent->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 1));

        // Search with brackets and special characters
        $resBracket = $this->actingAs($this->admin)->get('/admin/inquiries?search=[Zone 4]');
        $resBracket->assertStatus(200);
        $resBracket->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 1));

        // Search with HTML tag
        $resHtml = $this->actingAs($this->admin)->get('/admin/inquiries?search=<sustainable>');
        $resHtml->assertStatus(200);
        $resHtml->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 1));

        // Search with single quotes
        $resQuotes = $this->actingAs($this->admin)->get("/admin/inquiries?search=' OR 1=1 --");
        $resQuotes->assertStatus(200);
        $resQuotes->assertInertia(fn (Assert $page) => $page->has('inquiries.data', 0));
    }

    /**
     * Test inquiries status counts single-query aggregation accuracy.
     * Feature 12: Inquiries Index & Status Filter (R4)
     */
    public function test_inquiries_single_query_status_counts_aggregation_accuracy(): void
    {
        Inquiry::factory()->count(3)->create(['status' => 'baru']);
        Inquiry::factory()->count(2)->create(['status' => 'new']);
        Inquiry::factory()->count(4)->create(['status' => 'dihubungi']);
        Inquiry::factory()->count(1)->create(['status' => 'diarsipkan']);

        $response = $this->actingAs($this->admin)->get('/admin/inquiries');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->where('status_counts.all', 10)
            ->where('status_counts.baru', 5)
            ->where('status_counts.dihubungi', 4)
            ->where('status_counts.diarsipkan', 1)
        );
    }

    /**
     * Test inquiry status update across complete workflow lifecycle.
     * Feature 14: Inquiry Status Update (R4)
     */
    public function test_inquiry_status_update_complete_workflow_state_transitions(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        // 1. baru -> dihubungi
        $res1 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'dihubungi',
        ]);
        $res1->assertStatus(302);
        $res1->assertSessionHas('success', 'Status pesan konsultasi berhasil diperbarui.');
        $this->assertEquals('dihubungi', $inquiry->fresh()->status);

        // 2. dihubungi -> diarsipkan
        $res2 = $this->actingAs($this->admin)->put("/admin/inquiries/{$inquiry->id}", [
            'status' => 'diarsipkan',
        ]);
        $res2->assertStatus(302);
        $this->assertEquals('diarsipkan', $inquiry->fresh()->status);

        // 3. diarsipkan -> baru (re-open)
        $res3 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'baru',
        ]);
        $res3->assertStatus(302);
        $this->assertEquals('baru', $inquiry->fresh()->status);

        // 4. English alias support: baru -> contacted
        $res4 = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
            'status' => 'contacted',
        ]);
        $res4->assertStatus(302);
        $this->assertEquals('contacted', $inquiry->fresh()->status);
    }

    /**
     * Test inquiry status update rejects invalid or arbitrary status strings.
     * Feature 14: Inquiry Status Update (R4)
     */
    public function test_inquiry_status_update_rejects_disallowed_enum_values(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'baru']);

        $disallowed = ['completed', 'spam', 'deleted', 'pending', '', '123'];

        foreach ($disallowed as $badStatus) {
            $response = $this->actingAs($this->admin)->patch("/admin/inquiries/{$inquiry->id}", [
                'status' => $badStatus,
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors(['status']);
        }

        // Verify status remains unchanged
        $this->assertEquals('baru', $inquiry->fresh()->status);
    }

    /**
     * Test inquiry show returns full client details and 404 for invalid ID.
     * Feature 13: Inquiry Detail View (R4)
     */
    public function test_inquiry_show_returns_full_attributes_and_404_on_invalid_id(): void
    {
        $inquiry = Inquiry::factory()->create([
            'name' => 'Wayan Sukadana',
            'email' => 'wayan@sukadana.bali',
            'phone' => '081234567890',
            'site_location' => 'Sanur, Denpasar Selatan',
            'estimated_area' => '800 m²',
            'budget_range' => 'Rp 500 jt - 1 M',
            'message' => 'Desain lanskap taman tropis kontemporer.',
            'status' => 'baru',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inquiries/Show')
            ->where('inquiry.id', $inquiry->id)
            ->where('inquiry.name', 'Wayan Sukadana')
            ->where('inquiry.email', 'wayan@sukadana.bali')
            ->where('inquiry.site_location', 'Sanur, Denpasar Selatan')
            ->where('inquiry.estimated_area', '800 m²')
        );

        // 404 on non-existent inquiry
        $res404 = $this->actingAs($this->admin)->get('/admin/inquiries/999999');
        $res404->assertStatus(404);
    }

    /**
     * Test inquiry deletion removes record and flashes Indonesian success notice.
     * Feature 15: Inquiry Deletion (R4)
     */
    public function test_inquiry_destroy_deletes_record_and_redirects_with_indonesian_flash(): void
    {
        $inquiry = Inquiry::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/inquiries/{$inquiry->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/inquiries');
        $response->assertSessionHas('success', 'Pesan konsultasi berhasil dihapus.');

        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Projects CRUD Extreme Strings, Boundaries & Auto-Slug Engine
    |--------------------------------------------------------------------------
    */

    /**
     * Test project year boundary values (min 1900, max 2100).
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_year_boundary_values(): void
    {
        $basePayload = [
            'title' => 'Taman Minimalis Modern',
            'description' => 'Konsep taman zen modern.',
            'location' => 'Bandung, Jawa Barat',
            'area_size' => '350 m²',
        ];

        // Valid boundary: 1900
        $res1900 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-1900',
            'year' => 1900,
        ]));
        $res1900->assertStatus(302);
        $res1900->assertSessionHas('success');

        // Valid boundary: 2100
        $res2100 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-2100',
            'year' => 2100,
        ]));
        $res2100->assertStatus(302);
        $res2100->assertSessionHas('success');

        // Invalid: 1899 (below minimum)
        $res1899 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-1899',
            'year' => 1899,
        ]));
        $res1899->assertStatus(302);
        $res1899->assertSessionHasErrors(['year']);

        // Invalid: 2101 (above maximum)
        $res2101 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-2101',
            'year' => 2101,
        ]));
        $res2101->assertStatus(302);
        $res2101->assertSessionHasErrors(['year']);

        // Invalid: year 0
        $res0 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-0',
            'year' => 0,
        ]));
        $res0->assertStatus(302);
        $res0->assertSessionHasErrors(['year']);

        // Invalid: negative year
        $resNeg = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-neg',
            'year' => -2024,
        ]));
        $resNeg->assertStatus(302);
        $resNeg->assertSessionHasErrors(['year']);
    }

    /**
     * Test project area_size boundary lengths (max 50 chars).
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_area_size_boundary_lengths_and_formatting(): void
    {
        $basePayload = [
            'title' => 'Taman Paviliun Sejuk',
            'description' => 'Konsep lanskap paviliun.',
            'location' => 'Bogor, Jawa Barat',
            'year' => 2024,
        ];

        // Exact 50 chars area_size: valid
        $area50 = str_repeat('A', 47) . ' m²'; // 47 + 3 bytes utf8 m² = 50 chars
        $res50 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-50',
            'area_size' => str_repeat('X', 50),
        ]));
        $res50->assertStatus(302);
        $res50->assertSessionHas('success');

        // 51 chars area_size: invalid
        $res51 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-51',
            'area_size' => str_repeat('X', 51),
        ]));
        $res51->assertStatus(302);
        $res51->assertSessionHasErrors(['area_size']);

        // Empty area_size: invalid
        $resEmpty = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'slug' => 'taman-empty-area',
            'area_size' => '',
        ]));
        $resEmpty->assertStatus(302);
        $resEmpty->assertSessionHasErrors(['area_size']);
    }

    /**
     * Test project title and location length boundaries (max 255 chars).
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_title_and_location_boundary_lengths(): void
    {
        $basePayload = [
            'description' => 'Lanskap botanical tropis.',
            'year' => 2024,
            'area_size' => '500 m²',
        ];

        // Title 255 chars: valid
        $title255 = str_repeat('T', 255);
        $resTitle255 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'title' => $title255,
            'slug' => 'title-255-test',
            'location' => 'Denpasar, Bali',
        ]));
        $resTitle255->assertStatus(302);
        $resTitle255->assertSessionHas('success');

        // Title 256 chars: invalid
        $title256 = str_repeat('T', 256);
        $resTitle256 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'title' => $title256,
            'slug' => 'title-256-test',
            'location' => 'Denpasar, Bali',
        ]));
        $resTitle256->assertStatus(302);
        $resTitle256->assertSessionHasErrors(['title']);

        // Location 255 chars: valid
        $loc255 = str_repeat('L', 255);
        $resLoc255 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'title' => 'Project Valid Location',
            'slug' => 'valid-loc-test',
            'location' => $loc255,
        ]));
        $resLoc255->assertStatus(302);
        $resLoc255->assertSessionHas('success');

        // Location 256 chars: invalid
        $loc256 = str_repeat('L', 256);
        $resLoc256 = $this->actingAs($this->admin)->post('/admin/projects', array_merge($basePayload, [
            'title' => 'Project Invalid Location',
            'slug' => 'invalid-loc-test',
            'location' => $loc256,
        ]));
        $resLoc256->assertStatus(302);
        $resLoc256->assertSessionHasErrors(['location']);
    }

    /**
     * Test project handles extreme unicode characters, emojis, and HTML injection payloads safely.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_handles_extreme_unicode_emojis_and_html_injection_verbatim(): void
    {
        $payload = [
            'title' => '🌿 Taman Zen Kyoto (京都) & Sanctuary 🏯',
            'slug' => 'taman-zen-kyoto-sanctuary',
            'excerpt' => '<b>Kutipan tebal</b> dengan simbol & <special>',
            'description' => '<script>alert("xss")</script><p>Deskripsi aman dengan tag.</p>',
            'client' => 'PT Studio & Klien \' " 100%',
            'location' => 'Ubud, Gianyar (37°N, 122°W)',
            'year' => 2024,
            'area_size' => '1,450 m²',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/projects', $payload);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');

        $project = Project::where('slug', 'taman-zen-kyoto-sanctuary')->first();
        $this->assertNotNull($project);
        $this->assertEquals($payload['title'], $project->title);
        $this->assertEquals($payload['excerpt'], $project->excerpt);
        $this->assertEquals($payload['description'], $project->description);
        $this->assertEquals($payload['client'], $project->client);
    }

    /**
     * Test auto-slug generation when slug is empty vs explicit override.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_auto_slug_generation_and_explicit_slug_override(): void
    {
        // 1. Auto slug from title
        $resAuto = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Lanskap Villa Sanur Paradise',
            'slug' => '',
            'description' => 'Taman villa tropis asri.',
            'location' => 'Sanur, Bali',
            'year' => 2023,
            'area_size' => '650 m²',
        ]);
        $resAuto->assertStatus(302);

        $projectAuto = Project::where('title', 'Lanskap Villa Sanur Paradise')->first();
        $this->assertNotNull($projectAuto);
        $this->assertEquals('lanskap-villa-sanur-paradise', $projectAuto->slug);

        // 2. Explicit slug override
        $resExplicit = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Villa Sanur Suite Dua',
            'slug' => 'custom-sanur-suite-2',
            'description' => 'Taman privat villa.',
            'location' => 'Sanur, Bali',
            'year' => 2023,
            'area_size' => '400 m²',
        ]);
        $resExplicit->assertStatus(302);

        $projectExplicit = Project::where('title', 'Villa Sanur Suite Dua')->first();
        $this->assertNotNull($projectExplicit);
        $this->assertEquals('custom-sanur-suite-2', $projectExplicit->slug);
    }

    /**
     * Test project creation rejects duplicate slug with Indonesian validation message.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_creation_rejects_duplicate_slug_with_indonesian_message(): void
    {
        Project::factory()->create(['slug' => 'halaman-belakang-asri']);

        $response = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Halaman Belakang Asri Duplikat',
            'slug' => 'halaman-belakang-asri',
            'description' => 'Deskripsi proyek baru.',
            'location' => 'Jakarta Selatan',
            'year' => 2024,
            'area_size' => '250 m²',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'slug' => 'Slug proyek sudah digunakan. Silakan gunakan slug lain.',
        ]);
    }

    /**
     * Test project update allows keeping own slug but rejects collision with another project.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_update_allows_keeping_same_slug_but_rejects_collision_with_other_project(): void
    {
        $projectA = Project::factory()->create(['title' => 'Project Alpha', 'slug' => 'project-alpha']);
        $projectB = Project::factory()->create(['title' => 'Project Beta', 'slug' => 'project-beta']);

        // Update Project A keeping its own slug: succeeds
        $resA = $this->actingAs($this->admin)->put("/admin/projects/{$projectA->id}", [
            'title' => 'Project Alpha Updated',
            'slug' => 'project-alpha',
            'description' => 'Deskripsi update.',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '800 m²',
        ]);
        $resA->assertStatus(302);
        $resA->assertSessionHas('success', 'Proyek berhasil diperbarui.');

        // Update Project A using Project B's slug: fails unique validation
        $resCollision = $this->actingAs($this->admin)->put("/admin/projects/{$projectA->id}", [
            'title' => 'Project Alpha Attempted Collision',
            'slug' => 'project-beta',
            'description' => 'Deskripsi update.',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '800 m²',
        ]);
        $resCollision->assertStatus(302);
        $resCollision->assertSessionHasErrors([
            'slug' => 'Slug proyek sudah digunakan oleh proyek lain.',
        ]);
    }

    /**
     * Test project featured filter supports truthy and falsy values.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_project_featured_filter_truthy_and_falsy_values(): void
    {
        Project::factory()->count(2)->create(['is_featured' => true]);
        Project::factory()->count(3)->create(['is_featured' => false]);

        // ?featured=1
        $res1 = $this->actingAs($this->admin)->get('/admin/projects?featured=1');
        $res1->assertStatus(200);
        $res1->assertInertia(fn (Assert $page) => $page->has('projects.data', 2));

        // ?featured=true
        $resTrue = $this->actingAs($this->admin)->get('/admin/projects?featured=true');
        $resTrue->assertStatus(200);
        $resTrue->assertInertia(fn (Assert $page) => $page->has('projects.data', 2));

        // ?featured=0
        $res0 = $this->actingAs($this->admin)->get('/admin/projects?featured=0');
        $res0->assertStatus(200);
        $res0->assertInertia(fn (Assert $page) => $page->has('projects.data', 3));

        // ?featured=false
        $resFalse = $this->actingAs($this->admin)->get('/admin/projects?featured=false');
        $resFalse->assertStatus(200);
        $resFalse->assertInertia(fn (Assert $page) => $page->has('projects.data', 3));

        // ?featured=invalid (returns all 5)
        $resInvalid = $this->actingAs($this->admin)->get('/admin/projects?featured=invalid');
        $resInvalid->assertStatus(200);
        $resInvalid->assertInertia(fn (Assert $page) => $page->has('projects.data', 5));
    }

    /**
     * Test project search handles special characters and wildcards without throwing 500 error.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_project_search_handles_special_characters_without_crashing(): void
    {
        Project::factory()->create([
            'title' => 'Resor Bambu Hitam 100%',
            'location' => 'Seminyak [Area A]',
        ]);

        $searchTerms = ['100%', '[Area A]', "' OR '1'='1", '\\', '🌴', '<script>'];

        foreach ($searchTerms as $term) {
            $response = $this->actingAs($this->admin)->get('/admin/projects?search=' . urlencode($term));
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page->component('Admin/Projects/Index'));
        }
    }

    /**
     * Test project show route redirects to edit route and handles 404 for missing IDs.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_show_redirects_to_edit_view(): void
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}");
        $response->assertStatus(302);
        $response->assertRedirect("/admin/projects/{$project->id}/edit");

        // 404 on missing project
        $res404 = $this->actingAs($this->admin)->get('/admin/projects/999999');
        $res404->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: File Uploads, Boundary Types & Cascade Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Test project file uploads reject non-image file types and oversized files.
     * Feature 17: Project Create View & Action (R2)
     */
    public function test_project_file_upload_rejects_non_image_mimes_and_oversized_files(): void
    {
        $fakePdf = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
        $fakeOversized = UploadedFile::fake()->create('huge.jpg', 12000, 'image/jpeg'); // 12MB > 10240KB

        // Reject PDF cover
        $resPdf = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Taman Asri PDF Test',
            'description' => 'Deskripsi proyek.',
            'location' => 'Denpasar, Bali',
            'year' => 2024,
            'area_size' => '300 m²',
            'cover_image' => $fakePdf,
        ]);
        $resPdf->assertStatus(302);
        $resPdf->assertSessionHasErrors([
            'cover_image' => 'File gambar sampul harus berupa berkas gambar yang valid.',
        ]);

        // Reject oversized masterplan
        $resOversized = $this->actingAs($this->admin)->post('/admin/projects', [
            'title' => 'Taman Asri Huge Test',
            'description' => 'Deskripsi proyek.',
            'location' => 'Denpasar, Bali',
            'year' => 2024,
            'area_size' => '300 m²',
            'masterplan_image' => $fakeOversized,
        ]);
        $resOversized->assertStatus(302);
        $resOversized->assertSessionHasErrors(['masterplan_image']);
    }

    /**
     * Test project update preserves existing cover and masterplan files when omitted.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_update_preserves_existing_files_when_omitted(): void
    {
        $coverFile = UploadedFile::fake()->image('original_cover.jpg');
        $masterFile = UploadedFile::fake()->image('original_master.jpg');

        $coverPath = $coverFile->store('projects', 'public');
        $masterPath = $masterFile->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $coverPath,
            'masterplan_image' => $masterPath,
        ]);

        // Update project with new title but no image uploads
        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => 'Judul Proyek Diperbarui',
            'description' => 'Deskripsi tetap lengkap.',
            'location' => 'Ubud, Bali',
            'year' => 2024,
            'area_size' => '720 m²',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Proyek berhasil diperbarui.');

        $project->refresh();
        $this->assertEquals($coverPath, $project->cover_image);
        $this->assertEquals($masterPath, $project->masterplan_image);
        Storage::disk('public')->assertExists($coverPath);
        Storage::disk('public')->assertExists($masterPath);
    }

    /**
     * Test project update replaces cover image and cleans up old file from storage.
     * Feature 18: Project Edit View & Action (R2)
     */
    public function test_project_update_replaces_cover_and_deletes_old_file_from_storage(): void
    {
        $oldFile = UploadedFile::fake()->image('old_cover.jpg');
        $oldPath = $oldFile->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new_cover.jpg');

        $response = $this->actingAs($this->admin)->put("/admin/projects/{$project->id}", [
            'title' => $project->title,
            'description' => $project->description,
            'location' => $project->location,
            'year' => $project->year,
            'area_size' => $project->area_size,
            'cover_image' => $newFile,
        ]);

        $response->assertStatus(302);

        $project->refresh();
        $this->assertNotEquals($oldPath, $project->cover_image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($project->cover_image);
    }

    /**
     * Test project cascade deletion removes all storage assets and child gallery records.
     * Feature 19: Project Cascade Deletion (R2)
     */
    public function test_project_cascade_delete_cleans_up_all_storage_files_and_gallery_records(): void
    {
        $coverPath = UploadedFile::fake()->image('cover.jpg')->store('projects', 'public');
        $masterPath = UploadedFile::fake()->image('master.jpg')->store('projects', 'public');

        $project = Project::factory()->create([
            'cover_image' => $coverPath,
            'masterplan_image' => $masterPath,
        ]);

        $galleryPath1 = UploadedFile::fake()->image('gal1.jpg')->store('projects/gallery', 'public');
        $galleryPath2 = UploadedFile::fake()->image('gal2.jpg')->store('projects/gallery', 'public');

        $img1 = $project->images()->create(['image_path' => $galleryPath1, 'sort_order' => 1]);
        $img2 = $project->images()->create(['image_path' => $galleryPath2, 'sort_order' => 2]);

        Storage::disk('public')->assertExists($coverPath);
        Storage::disk('public')->assertExists($masterPath);
        Storage::disk('public')->assertExists($galleryPath1);
        Storage::disk('public')->assertExists($galleryPath2);

        // Delete project
        $response = $this->actingAs($this->admin)->delete("/admin/projects/{$project->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/admin/projects');
        $response->assertSessionHas('success', 'Proyek berhasil dihapus.');

        // Project record deleted
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);

        // Child gallery records cascaded
        $this->assertDatabaseMissing('project_images', ['id' => $img1->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $img2->id]);

        // Physical storage files purged
        Storage::disk('public')->assertMissing($coverPath);
        Storage::disk('public')->assertMissing($masterPath);
        Storage::disk('public')->assertMissing($galleryPath1);
        Storage::disk('public')->assertMissing($galleryPath2);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 6: Gallery Multi-Upload, Reordering, IDOR & Concurrency
    |--------------------------------------------------------------------------
    */

    /**
     * Test gallery multi-upload validates image types and stores consecutive sort orders.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_multi_upload_validates_image_types_and_stores_files(): void
    {
        $project = Project::factory()->create();

        $images = [
            UploadedFile::fake()->image('img1.jpg'),
            UploadedFile::fake()->image('img2.png'),
            UploadedFile::fake()->image('img3.webp'),
        ];

        $captions = [
            'Takarir Foto Satu',
            'Takarir Foto Dua',
            'Takarir Foto Tiga',
        ];

        $response = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => $images,
            'captions' => $captions,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Foto galeri berhasil diunggah.');

        $this->assertCount(3, $project->fresh()->images);

        $savedImages = $project->fresh()->images()->orderBy('sort_order', 'asc')->get();
        $this->assertEquals(1, $savedImages[0]->sort_order);
        $this->assertEquals(2, $savedImages[1]->sort_order);
        $this->assertEquals(3, $savedImages[2]->sort_order);
        $this->assertEquals('Takarir Foto Satu', $savedImages[0]->caption);
    }

    /**
     * Test gallery upload rejects disallowed mime types with Indonesian validation message.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_multi_upload_rejects_disallowed_mime_types(): void
    {
        $project = Project::factory()->create();

        $fakeSvg = UploadedFile::fake()->create('vector.svg', 100, 'image/svg+xml');

        $response = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => [$fakeSvg],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'images.0' => 'Format gambar harus berupa JPG, JPEG, PNG, atau WEBP.',
        ]);
    }

    /**
     * Test gallery multi-upload rejects empty images payload.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_multi_upload_rejects_empty_payload(): void
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => [],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'images' => 'Minimal satu berkas gambar galeri harus dipilih.',
        ]);
    }

    /**
     * Test gallery multi-upload handles caption count mismatches and whitespace trimming.
     * Feature 20: Gallery Multi-Upload (R3)
     */
    public function test_gallery_multi_upload_handles_caption_mismatches_and_nulls(): void
    {
        $project = Project::factory()->create();

        $images = [
            UploadedFile::fake()->image('img_a.jpg'),
            UploadedFile::fake()->image('img_b.jpg'),
        ];

        // Only first caption provided, whitespace only for second
        $captions = [
            '   Takarir Pertama Asli   ',
            '    ',
        ];

        $response = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images", [
            'images' => $images,
            'captions' => $captions,
        ]);

        $response->assertStatus(302);

        $saved = $project->fresh()->images()->orderBy('sort_order', 'asc')->get();
        $this->assertEquals('Takarir Pertama Asli', $saved[0]->caption);
        $this->assertNull($saved[1]->caption);
    }

    /**
     * Test gallery batch reordering with orders array and image_ids array payloads.
     * Feature 21: Gallery Sort Reordering (R3)
     */
    public function test_gallery_batch_reordering_with_orders_array_and_image_ids_array(): void
    {
        $project = Project::factory()->create();

        $img1 = $project->images()->create(['image_path' => 'img1.jpg', 'sort_order' => 1]);
        $img2 = $project->images()->create(['image_path' => 'img2.jpg', 'sort_order' => 2]);
        $img3 = $project->images()->create(['image_path' => 'img3.jpg', 'sort_order' => 3]);

        // 1. Reorder using 'orders' payload
        $resOrders = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images/reorder", [
            'orders' => [
                ['id' => $img1->id, 'sort_order' => 3],
                ['id' => $img2->id, 'sort_order' => 1],
                ['id' => $img3->id, 'sort_order' => 2],
            ],
        ]);

        $resOrders->assertStatus(302);
        $resOrders->assertSessionHas('success', 'Urutan foto galeri berhasil diperbarui.');

        $this->assertEquals(3, $img1->fresh()->sort_order);
        $this->assertEquals(1, $img2->fresh()->sort_order);
        $this->assertEquals(2, $img3->fresh()->sort_order);

        // 2. Reorder using 'image_ids' sequential array
        $resImageIds = $this->actingAs($this->admin)->post("/admin/projects/{$project->id}/images/reorder", [
            'image_ids' => [$img3->id, $img1->id, $img2->id],
        ]);

        $resImageIds->assertStatus(302);
        $this->assertEquals(1, $img3->fresh()->sort_order);
        $this->assertEquals(2, $img1->fresh()->sort_order);
        $this->assertEquals(3, $img2->fresh()->sort_order);
    }

    /**
     * Test gallery reordering prevents IDOR tampering across different projects.
     * Feature 21: Gallery Sort Reordering (R3)
     */
    public function test_gallery_batch_reordering_prevents_idor_cross_project_tampering(): void
    {
        $projectA = Project::factory()->create();
        $projectB = Project::factory()->create();

        $imgA = $projectA->images()->create(['image_path' => 'a.jpg', 'sort_order' => 1]);
        $imgB = $projectB->images()->create(['image_path' => 'b.jpg', 'sort_order' => 99]);

        // Attempt to alter Project B's image sort order by sending it to Project A's reorder endpoint
        $response = $this->actingAs($this->admin)->post("/admin/projects/{$projectA->id}/images/reorder", [
            'orders' => [
                ['id' => $imgB->id, 'sort_order' => 1],
            ],
        ]);

        $response->assertStatus(302);

        // Project B's image sort_order MUST NOT be changed
        $this->assertEquals(99, $imgB->fresh()->sort_order);
    }

    /**
     * Test gallery single caption update boundary and trimming.
     * Feature 22: Gallery Caption & Item Delete (R3)
     */
    public function test_gallery_caption_update_and_boundary_length(): void
    {
        $project = Project::factory()->create();
        $image = $project->images()->create(['image_path' => 'test.jpg', 'sort_order' => 1]);

        // Caption at exact 255 characters: valid
        $caption255 = str_repeat('C', 255);
        $res255 = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", [
            'caption' => $caption255,
        ]);
        $res255->assertStatus(302);
        $res255->assertSessionHas('success', 'Takarir foto berhasil diperbarui.');
        $this->assertEquals($caption255, $image->fresh()->caption);

        // Caption at 256 characters: invalid
        $caption256 = str_repeat('C', 256);
        $res256 = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", [
            'caption' => $caption256,
        ]);
        $res256->assertStatus(302);
        $res256->assertSessionHasErrors(['caption']);

        // Blank/whitespace caption trims to null
        $resBlank = $this->actingAs($this->admin)->put("/admin/project-images/{$image->id}", [
            'caption' => '    ',
        ]);
        $resBlank->assertStatus(302);
        $this->assertNull($image->fresh()->caption);
    }

    /**
     * Test gallery single image delete removes DB row and storage asset.
     * Feature 22: Gallery Caption & Item Delete (R3)
     */
    public function test_gallery_image_destroy_removes_file_and_record(): void
    {
        $filePath = UploadedFile::fake()->image('sample.jpg')->store('projects/gallery', 'public');
        $project = Project::factory()->create();
        $image = $project->images()->create(['image_path' => $filePath, 'sort_order' => 1]);

        Storage::disk('public')->assertExists($filePath);

        $response = $this->actingAs($this->admin)->delete("/admin/project-images/{$image->id}");

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Foto galeri berhasil dihapus.');

        $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($filePath);

        // 404 on deleting non-existent image
        $res404 = $this->actingAs($this->admin)->delete('/admin/project-images/999999');
        $res404->assertStatus(404);
    }

    /**
     * Test dual route bindings parity for project-images and images.
     * Feature 22: Gallery Caption & Item Delete (R3)
     */
    public function test_gallery_dual_route_bindings_parity(): void
    {
        $project = Project::factory()->create();
        $image1 = $project->images()->create(['image_path' => 'img1.jpg', 'sort_order' => 1]);
        $image2 = $project->images()->create(['image_path' => 'img2.jpg', 'sort_order' => 2]);

        // Route: /admin/images/{image} for update
        $resUpdate = $this->actingAs($this->admin)->put("/admin/images/{$image1->id}", [
            'caption' => 'Update via images alias',
        ]);
        $resUpdate->assertStatus(302);
        $this->assertEquals('Update via images alias', $image1->fresh()->caption);

        // Route: /admin/images/{image} for delete
        $resDelete = $this->actingAs($this->admin)->delete("/admin/images/{$image2->id}");
        $resDelete->assertStatus(302);
        $this->assertDatabaseMissing('project_images', ['id' => $image2->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 7: Route Model Binding, HTTP Methods & Edge Model Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Test admin endpoints enforce proper HTTP methods and return 405 Method Not Allowed for invalid verbs.
     * Feature 4: Admin Route Protection (R1)
     */
    public function test_admin_endpoints_enforce_proper_http_methods_and_reject_invalid_verbs(): void
    {
        $project = Project::factory()->create();

        // GET on POST-only /admin/projects/{project}/images
        $resMethod1 = $this->actingAs($this->admin)->get("/admin/projects/{$project->id}/images");
        $resMethod1->assertStatus(405);

        // GET on PUT/DELETE-only /admin/project-images/{id}
        $img = $project->images()->create(['image_path' => 'test.jpg', 'sort_order' => 1]);
        $resMethod2 = $this->actingAs($this->admin)->get("/admin/project-images/{$img->id}");
        $resMethod2->assertStatus(405);

        // POST on GET-only /admin/inquiries
        $resMethod3 = $this->actingAs($this->admin)->post('/admin/inquiries', []);
        $resMethod3->assertStatus(405);
    }

    /**
     * Test Project model area_numeric accessor across extreme and malformed inputs.
     * Feature 16: Projects Admin Index (R2)
     */
    public function test_project_area_numeric_accessor_resilience_across_edge_inputs(): void
    {
        $p1 = new Project(['area_size' => '1,250 m²']);
        $this->assertEquals(1250, $p1->area_numeric);

        $p2 = new Project(['area_size' => '750 m²']);
        $this->assertEquals(750, $p2->area_numeric);

        $p3 = new Project(['area_size' => '1.500 m²']);
        $this->assertEquals(1500, $p3->area_numeric);

        $p4 = new Project(['area_size' => '0 m²']);
        $this->assertEquals(0, $p4->area_numeric);

        $p5 = new Project(['area_size' => 'Luas Tidak Diketahui']);
        $this->assertEquals(0, $p5->area_numeric);

        $p6 = new Project(['area_size' => '']);
        $this->assertEquals(0, $p6->area_numeric);

        $p7 = new Project(['area_size' => null]);
        $this->assertEquals(0, $p7->area_numeric);

        $p8 = new Project(['area_size' => '-300 m²']);
        $this->assertEquals(300, $p8->area_numeric);
    }
}
