<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest cannot access admin profile edit page and is redirected to login.
     */
    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get('/admin/profile');

        $response->assertRedirect('/admin/login');
    }

    /**
     * Authenticated admin can view the profile edit screen with user props.
     */
    public function test_admin_can_view_profile_edit_screen(): void
    {
        $admin = User::factory()->create([
            'name' => 'Studio Admin',
            'email' => 'admin@studiokode.com',
        ]);

        $response = $this->actingAs($admin)->get('/admin/profile');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Profile/Edit')
            ->has('user', fn (Assert $user) => $user
                ->where('id', $admin->id)
                ->where('name', 'Studio Admin')
                ->where('email', 'admin@studiokode.com')
            )
        );
    }

    /**
     * Admin can update their name and email successfully.
     */
    public function test_admin_can_update_name_and_email(): void
    {
        $admin = User::factory()->create([
            'name' => 'Original Admin',
            'email' => 'admin@studiokode.com',
        ]);

        $response = $this->actingAs($admin)->patch('/admin/profile', [
            'name' => 'Updated Studio Lead',
            'email' => 'lead@studiokode.com',
        ]);

        $response->assertRedirect('/admin/profile');
        $response->assertSessionHas('success', 'Profil admin berhasil diperbarui.');

        $admin->refresh();
        $this->assertSame('Updated Studio Lead', $admin->name);
        $this->assertSame('lead@studiokode.com', $admin->email);
    }

    /**
     * Updating profile with the same email is allowed for the same user.
     */
    public function test_admin_can_keep_same_email_when_updating_profile(): void
    {
        $admin = User::factory()->create([
            'name' => 'Current Name',
            'email' => 'admin@studiokode.com',
        ]);

        $response = $this->actingAs($admin)->patch('/admin/profile', [
            'name' => 'New Name Only',
            'email' => 'admin@studiokode.com',
        ]);

        $response->assertRedirect('/admin/profile');
        $response->assertSessionHasNoErrors();

        $admin->refresh();
        $this->assertSame('New Name Only', $admin->name);
        $this->assertSame('admin@studiokode.com', $admin->email);
    }

    /**
     * Profile update fails when email is already taken by another user.
     */
    public function test_profile_update_fails_when_email_already_taken(): void
    {
        User::factory()->create(['email' => 'other@studiokode.com']);

        $admin = User::factory()->create([
            'name' => 'Current Admin',
            'email' => 'admin@studiokode.com',
        ]);

        $response = $this->actingAs($admin)->patch('/admin/profile', [
            'name' => 'Current Admin',
            'email' => 'other@studiokode.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $admin->refresh();
        $this->assertSame('admin@studiokode.com', $admin->email);
    }

    /**
     * Profile update requires name and valid email.
     */
    public function test_profile_update_requires_name_and_valid_email(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->patch('/admin/profile', [
            'name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /**
     * Admin can update password with valid current password and confirmation.
     */
    public function test_admin_can_update_password_with_valid_credentials(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'oldpassword123',
            'password' => 'newsecurepassword456',
            'password_confirmation' => 'newsecurepassword456',
        ]);

        $response->assertRedirect('/admin/profile');
        $response->assertSessionHas('success', 'Kata sandi berhasil diperbarui.');

        $admin->refresh();
        $this->assertTrue(Hash::check('newsecurepassword456', $admin->password));
    }

    /**
     * Password update fails if current password is wrong.
     */
    public function test_password_update_fails_when_current_password_is_incorrect(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $admin->refresh();
        $this->assertTrue(Hash::check('correctpassword', $admin->password));
    }

    /**
     * Password update fails when confirmation does not match.
     */
    public function test_password_update_fails_when_confirmation_does_not_match(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('currentpassword123'),
        ]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'currentpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatchedpassword456',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Password update fails when new password is shorter than 8 characters.
     */
    public function test_password_update_fails_when_password_is_too_short(): void
    {
        $admin = User::factory()->create([
            'password' => Hash::make('currentpassword123'),
        ]);

        $response = $this->actingAs($admin)->put('/admin/profile/password', [
            'current_password' => 'currentpassword123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
