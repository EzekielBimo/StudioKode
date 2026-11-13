<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Tier 1: Primary Happy Paths & Interface Contracts
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 1: Admin login view renders successfully for unauthenticated guests.
     * Feature 1: Admin Login View (R1)
     */
    public function test_admin_login_screen_renders_successfully(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Auth/Login')
        );
    }

    /**
     * Tier 1: Admin user authenticates with valid credentials, regenerates session,
     * and redirects to admin dashboard.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_authenticates_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Tier 1: Authenticated admin logs out successfully, terminating session.
     * Feature 3: Admin Logout Action (R1)
     */
    public function test_authenticated_admin_can_logout(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /**
     * Tier 1: Unauthenticated guests hitting admin routes are redirected to login.
     * Feature 4: Admin Route Protection (R1)
     */
    public function test_admin_routes_redirect_unauthenticated_guests_to_login(): void
    {
        $protectedRoutes = [
            '/admin',
            '/admin/dashboard',
            '/admin/projects',
            '/admin/projects/create',
            '/admin/inquiries',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/admin/login');
        }
    }

    /**
     * Tier 1: Authenticated admin can access protected admin dashboard route.
     * Feature 4: Admin Route Protection (R1)
     */
    public function test_authenticated_admin_can_access_protected_admin_routes(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 2: Boundary Value Analysis, Validation & Edge Cases
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 2: Login fails when invalid password is provided.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@studiokode.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Tier 2: Login fails when unregistered email is provided.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_fails_with_unregistered_email(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'unknown@studiokode.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Tier 2: Login validation requires both email and password fields.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_requires_email_and_password(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', []);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Tier 2: Login validation rejects invalid email format.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_fails_with_invalid_email_format(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'not-a-valid-email-address',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Tier 2: Authenticated admin visiting login page is redirected to dashboard.
     * Feature 4: Admin Route Protection (R1)
     */
    public function test_authenticated_admin_redirected_away_from_login_screen(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/login');

        $response->assertRedirect('/admin');
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 3: Session Security, Authorization & Inertia Shared State
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 3: Successful login regenerates session ID to prevent session fixation.
     * Feature 2: Admin Login Action (R1)
     */
    public function test_admin_login_regenerates_session_id(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
            'password' => Hash::make('password123'),
        ]);

        $initialSessionId = session()->getId();

        $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'password123',
        ]);

        $newSessionId = session()->getId();

        $this->assertNotEmpty($newSessionId);
        $this->assertNotSame($initialSessionId, $newSessionId);
    }

    /**
     * Tier 3: Inertia shares authenticated user details in 'auth.user' prop.
     * Feature 6: Inertia Shared Auth State (R1)
     */
    public function test_inertia_shares_authenticated_admin_user_data(): void
    {
        $admin = User::factory()->create([
            'name' => 'Studio Admin',
            'email' => 'admin@studiokode.com',
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('auth.user', fn (Assert $page) => $page
                ->where('id', $admin->id)
                ->where('name', 'Studio Admin')
                ->where('email', 'admin@studiokode.com')
                ->etc()
            )
        );
    }

    /**
     * Tier 3: Inertia 'auth.user' shared prop is null for unauthenticated guests.
     * Feature 6: Inertia Shared Auth State (R1)
     */
    public function test_inertia_shares_null_auth_user_for_guests(): void
    {
        $response = $this->get('/admin/login');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', null)
        );
    }

    /**
     * Tier 3: Login form is resilient against SQL injection payloads.
     * Feature 28: Adversarial Coverage Hardening
     */
    public function test_admin_login_resilient_against_sql_injection(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => "' OR '1'='1",
            'password' => "' OR '1'='1",
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Tier 4: End-to-End Authentication & Route Protection Lifecycle
    |--------------------------------------------------------------------------
    */

    /**
     * Tier 4: Complete admin authentication journey:
     * Guest -> Protected Route (302) -> Login Screen (200) -> Authenticate (302)
     * -> Dashboard (200) -> Logout (302) -> Subsequent Protected Probe (302).
     * Scenario 4.1 (R1)
     */
    public function test_admin_authentication_and_route_protection_full_lifecycle(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@studiokode.com',
            'password' => Hash::make('Botanical2024!'),
        ]);

        // Step 1: Guest probe receives 302 redirect
        $probe = $this->get('/admin');
        $probe->assertRedirect('/admin/login');

        // Step 2: Guest loads login screen
        $loginScreen = $this->get('/admin/login');
        $loginScreen->assertStatus(200);

        // Step 3: Guest submits credentials
        $loginAction = $this->post('/admin/login', [
            'email' => 'admin@studiokode.com',
            'password' => 'Botanical2024!',
        ]);
        $loginAction->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);

        // Step 4: Admin accesses dashboard
        $dashboard = $this->get('/admin');
        $dashboard->assertStatus(200);

        // Step 5: Admin logs out
        $logoutAction = $this->post('/admin/logout');
        $logoutAction->assertRedirect('/admin/login');
        $this->assertGuest();

        // Step 6: Subsequent access attempt is blocked
        $finalProbe = $this->get('/admin');
        $finalProbe->assertRedirect('/admin/login');
    }
}
