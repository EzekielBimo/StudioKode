<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChallengerM1_1_AuthStressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 1: Credential Boundaries & Malformed Payloads
    |--------------------------------------------------------------------------
    */

    /**
     * Test login with missing fields, empty strings, and whitespace-only.
     */
    public function test_login_rejects_missing_and_whitespace_credentials(): void
    {
        // 1. Missing both
        $res1 = $this->from('/admin/login')->post('/admin/login', []);
        $res1->assertRedirect('/admin/login');
        $res1->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();

        // 2. Empty strings
        $res2 = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => '',
        ]);
        $res2->assertRedirect('/admin/login');
        $res2->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();

        // 3. Whitespace only (Laravel's TrimStrings turns this to null/empty)
        $res3 = $this->from('/admin/login')->post('/admin/login', [
            'email' => '   ',
            'password' => '',
        ]);
        $res3->assertRedirect('/admin/login');
        $res3->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Test login rejects non-string array inputs (type confusion attempts).
     */
    public function test_login_rejects_array_payload_type_confusion(): void
    {
        // Email as array
        $res1 = $this->from('/admin/login')->post('/admin/login', [
            'email' => ['admin@studiokode.com'],
            'password' => 'password',
        ]);
        $res1->assertRedirect('/admin/login');
        $res1->assertSessionHasErrors(['email']);
        $this->assertGuest();

        // Password as array
        $res2 = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => ['password'],
        ]);
        $res2->assertRedirect('/admin/login');
        $res2->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * Test login handling of extremely large payloads (DoS prevention / boundary check).
     */
    public function test_login_with_extremely_large_payload(): void
    {
        // Very large password (> 4096 chars)
        $largePassword = str_repeat('A', 8192);
        $res = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => $largePassword,
        ]);

        $res->assertRedirect('/admin/login');
        $res->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test login email trimming: leading/trailing whitespace around valid email.
     * TrimStrings middleware normalizes input, so ' admin@studiokode.com ' should authenticate successfully.
     */
    public function test_login_with_trimmed_email_succeeds(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '  admin@studiokode.com  ',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->assertSame('admin@studiokode.com', Auth::user()->email);
    }

    /**
     * Test password case sensitivity: passwords MUST be case-sensitive.
     */
    public function test_login_password_is_strictly_case_sensitive(): void
    {
        $resLower = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'PASSWORD', // actual password is lowercase 'password'
        ]);

        $resLower->assertRedirect('/admin/login');
        $resLower->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test email case handling across database drivers (SQLite BINARY vs MySQL CI collation).
     */
    public function test_login_email_case_handling(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'ADMIN@studiokode.com',
            'password' => 'password',
        ]);

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            // SQLite defaults to binary collation on VARCHAR, requiring exact casing or strtolower
            $response->assertRedirect('/admin/login');
            $response->assertSessionHasErrors(['email']);
            $this->assertGuest();
        } else {
            // MySQL utf8mb4_unicode_ci collation matches case-insensitively
            $response->assertRedirect('/admin');
            $this->assertAuthenticated();
        }
    }

    /**
     * Test Indonesian localized validation messages on failed authentication and invalid input.
     */
    public function test_login_validation_messages_are_localized_in_indonesian(): void
    {
        // Empty validation errors
        $res1 = $this->from('/admin/login')->post('/admin/login', []);
        $res1->assertSessionHasErrors([
            'email' => 'Kolom alamat email wajib diisi.',
            'password' => 'Kolom kata sandi wajib diisi.',
        ]);

        // Invalid format error
        $res2 = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'not-an-email',
            'password' => 'test',
        ]);
        $res2->assertSessionHasErrors([
            'email' => 'Format alamat email tidak valid.',
        ]);

        // Bad credentials error
        $res3 = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'incorrect-pass',
        ]);
        $res3->assertSessionHasErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: SQL Injection & Adversarial Probes
    |--------------------------------------------------------------------------
    */

    /**
     * Test SQL injection payloads across email and password fields.
     */
    public function test_sql_injection_vectors_fail_cleanly(): void
    {
        $payloads = [
            "' OR '1'='1",
            "admin@studiokode.com'--",
            "admin@studiokode.com' /*",
            "admin@studiokode.com' OR 1=1--",
            "' UNION SELECT 1, 'hacked', 'hacked'--",
            "admin@studiokode.com'; DROP TABLE users; --",
            "\" OR \"\"=\"",
            "1' or '1' = '1",
        ];

        foreach ($payloads as $sqli) {
            $response = $this->from('/admin/login')->post('/admin/login', [
                'email' => $sqli,
                'password' => $sqli,
            ]);

            $response->assertRedirect('/admin/login');
            $this->assertGuest();
        }

        // Verify users table was not tampered with
        $this->assertDatabaseHas('users', ['email' => 'admin@studiokode.com']);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Session Security, Invalidation & Fixation
    |--------------------------------------------------------------------------
    */

    /**
     * Test session fixation defense: session ID MUST rotate upon successful login.
     */
    public function test_session_id_rotates_and_does_not_persist_pre_auth_id(): void
    {
        // 1. Visit login page to establish initial session
        $this->get('/admin/login');
        $preSessionId = session()->getId();
        $this->assertNotEmpty($preSessionId);

        // 2. Perform authentication
        $loginRes = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);
        $loginRes->assertRedirect('/admin');

        $postSessionId = session()->getId();
        $this->assertNotEmpty($postSessionId);
        $this->assertNotEquals($preSessionId, $postSessionId);
    }

    /**
     * Test session invalidation and CSRF token regeneration upon logout.
     */
    public function test_logout_invalidates_session_and_clears_authenticated_state(): void
    {
        $admin = User::where('email', 'admin@studiokode.com')->first();
        $this->actingAs($admin);

        // Record active session ID and CSRF token
        $activeSessionId = session()->getId();
        $activeCsrf = session()->token();

        // Perform logout
        $logoutRes = $this->post('/admin/logout');
        $logoutRes->assertRedirect('/admin/login');
        $logoutRes->assertSessionHas('success', 'Anda telah berhasil keluar dari sesi admin.');

        $this->assertGuest();

        // Verify session was regenerated/invalidated
        $afterSessionId = session()->getId();
        $afterCsrf = session()->token();

        $this->assertNotEquals($activeCsrf, $afterCsrf);

        // Ensure accessing protected route now fails
        $probe = $this->get('/admin');
        $probe->assertRedirect('/admin/login');
    }

    /**
     * Test Remember Me cookie persistence option.
     */
    public function test_login_remember_me_functionality(): void
    {
        // 1. Without remember me
        $resNoRemember = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
            'remember' => false,
        ]);
        $resNoRemember->assertRedirect('/admin');
        $resNoRemember->assertCookieMissing(Auth::getRecallerName());

        Auth::logout();

        // 2. With remember me
        $resRemember = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
            'remember' => true,
        ]);
        $resRemember->assertRedirect('/admin');
        $resRemember->assertCookieNotExpired(Auth::getRecallerName());
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Route Protection, Redirects & Guest Boundaries
    |--------------------------------------------------------------------------
    */

    /**
     * Test unauthenticated guests are redirected to /admin/login from all administrative paths.
     */
    public function test_all_admin_endpoints_redirect_unauthenticated_guests(): void
    {
        $endpoints = [
            '/admin',
            '/admin/dashboard',
            '/admin/projects',
            '/admin/projects/create',
            '/admin/inquiries',
        ];

        foreach ($endpoints as $url) {
            $res = $this->get($url);
            $res->assertStatus(302);
            $res->assertRedirect('/admin/login');
        }

        // POST /admin/logout also requires auth
        $logoutRes = $this->post('/admin/logout');
        $logoutRes->assertStatus(302);
        $logoutRes->assertRedirect('/admin/login');
    }

    /**
     * Test authenticated users visiting /admin/login are redirected to /admin.
     */
    public function test_authenticated_user_redirected_away_from_login_page(): void
    {
        $admin = User::where('email', 'admin@studiokode.com')->first();

        // GET /admin/login
        $getRes = $this->actingAs($admin)->get('/admin/login');
        $getRes->assertStatus(302);
        $getRes->assertRedirect('/admin');

        // POST /admin/login while authenticated
        $postRes = $this->actingAs($admin)->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);
        $postRes->assertStatus(302);
        $postRes->assertRedirect('/admin');
    }

    /**
     * Test intended URL redirection: guest trying to access a deep route is redirected back after login.
     */
    public function test_intended_url_redirect_after_successful_login(): void
    {
        // 1. Guest accesses deep route
        $targetUrl = '/admin/projects/create';
        $probe = $this->get($targetUrl);
        $probe->assertRedirect('/admin/login');

        // 2. Guest completes login
        $loginRes = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);

        // Intended URL should be /admin/projects/create
        $loginRes->assertRedirect($targetUrl);
    }

    /**
     * Test HTTP method restrictions on authentication routes (405 Method Not Allowed).
     */
    public function test_http_method_restrictions_on_auth_routes(): void
    {
        // GET /admin/logout is not allowed (must be POST)
        $admin = User::where('email', 'admin@studiokode.com')->first();
        $res1 = $this->actingAs($admin)->get('/admin/logout');
        $res1->assertStatus(405);

        // DELETE /admin/login is not allowed
        $res2 = $this->delete('/admin/login');
        $res2->assertStatus(405);

        // PUT /admin/login is not allowed
        $res3 = $this->put('/admin/login');
        $res3->assertStatus(405);
    }

    /**
     * Test 404 response for non-existent administrative subroutes when authenticated.
     */
    public function test_authenticated_admin_receives_404_for_unknown_admin_routes(): void
    {
        $admin = User::where('email', 'admin@studiokode.com')->first();

        $bogusAdminRoutes = [
            '/admin/non-existent-panel',
            '/admin/settings/security/audit',
            '/admin/users/export-all',
        ];

        foreach ($bogusAdminRoutes as $route) {
            $res = $this->actingAs($admin)->get($route);
            $res->assertStatus(404);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Inertia Shared State & Sensitive Data Exposure
    |--------------------------------------------------------------------------
    */

    /**
     * Test Inertia shared auth.user strictly exposes only safe whitelist fields (id, name, email).
     * Sensitive fields like password, remember_token, created_at, updated_at must NOT be exposed.
     */
    public function test_inertia_shared_auth_user_does_not_leak_sensitive_attributes(): void
    {
        $admin = User::where('email', 'admin@studiokode.com')->first();

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        // Strict assertion: omitting ->etc() ensures no unexpected properties exist in auth.user
        $response->assertInertia(fn (Assert $page) => $page
            ->has('auth.user', fn (Assert $page) => $page
                ->where('id', $admin->id)
                ->where('name', 'Admin StudioKode')
                ->where('email', 'admin@studiokode.com')
            )
        );

        // Direct inspection of response Inertia page props
        $pageData = $response->original->getData()['page'];
        $userProps = $pageData['props']['auth']['user'];
        $keys = array_keys($userProps);
        sort($keys);

        \PHPUnit\Framework\Assert::assertSame(['email', 'id', 'name'], $keys);
        \PHPUnit\Framework\Assert::assertArrayNotHasKey('password', $userProps);
        \PHPUnit\Framework\Assert::assertArrayNotHasKey('remember_token', $userProps);
    }

    /**
     * Test Inertia shared auth.user is strictly null for unauthenticated guests across public pages.
     */
    public function test_inertia_shared_auth_user_is_null_on_public_pages_for_guests(): void
    {
        $publicRoutes = [
            '/',
            '/projects',
            '/studio',
            '/services',
            '/contact',
            '/admin/login',
        ];

        foreach ($publicRoutes as $route) {
            $res = $this->get($route);
            $res->assertStatus(200);
            $res->assertInertia(fn (Assert $page) => $page
                ->where('auth.user', null)
            );
        }
    }

    /**
     * Test intended URL redirection preserves complex query strings across authentication.
     */
    public function test_intended_url_preserves_query_parameters(): void
    {
        // 1. Unauthenticated guest attempts to access projects with filters
        $deepUrl = '/admin/projects?status=published&search=sanctuary';
        $probe = $this->get($deepUrl);
        $probe->assertRedirect('/admin/login');

        // 2. Guest submits valid credentials
        $loginRes = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);

        // Intended URL should preserve query parameters and path
        $redirectUrl = $loginRes->headers->get('Location');
        $this->assertNotNull($redirectUrl);

        $parsed = parse_url($redirectUrl);
        $this->assertSame('/admin/projects', $parsed['path']);

        parse_str($parsed['query'] ?? '', $queryParams);
        $this->assertSame('published', $queryParams['status'] ?? null);
        $this->assertSame('sanctuary', $queryParams['search'] ?? null);
    }

    /**
     * Test repeated logout calls are handled gracefully without exceptions.
     */
    public function test_consecutive_logout_calls_handled_safely(): void
    {
        $admin = User::where('email', 'admin@studiokode.com')->first();
        $this->actingAs($admin);

        // First logout succeeds
        $logout1 = $this->post('/admin/logout');
        $logout1->assertRedirect('/admin/login');
        $this->assertGuest();

        // Second logout as guest redirects to /admin/login
        $logout2 = $this->post('/admin/logout');
        $logout2->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /**
     * Test invalid or forged session cookie is rejected safely.
     */
    public function test_forged_session_cookie_is_rejected(): void
    {
        $response = $this->withCookie(config('session.cookie'), 'forged_session_value_that_does_not_exist')
            ->get('/admin');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /**
     * Test flash notification lifecycle: available on redirect, consumed afterwards.
     */
    public function test_flash_notification_lifecycle_on_login(): void
    {
        $loginRes = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password',
        ]);

        $loginRes->assertSessionHas('success', 'Selamat datang kembali di Panel Admin StudioKode.');

        // Follow redirect to dashboard
        $dashboardRes = $this->get('/admin');
        $dashboardRes->assertStatus(200);
        $dashboardRes->assertInertia(fn (Assert $page) => $page
            ->where('flash.success', 'Selamat datang kembali di Panel Admin StudioKode.')
        );
    }

    /**
     * Test Seeder Idempotency: multiple seed executions do not duplicate or corrupt admin user.
     */
    public function test_database_seeder_is_idempotent_for_admin_user(): void
    {
        // Seeder ran in setUp(), run it again
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $count = User::where('email', 'admin@studiokode.com')->count();
        $this->assertSame(1, $count);

        $admin = User::where('email', 'admin@studiokode.com')->first();
        $this->assertTrue(Hash::check('password', $admin->password));
    }
}
