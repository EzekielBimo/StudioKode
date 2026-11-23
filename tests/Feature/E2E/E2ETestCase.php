<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class E2ETestCase extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed complete idempotent StudioKode dataset into in-memory SQLite
        $this->seed(DatabaseSeeder::class);

        // 2. Provision or retrieve admin account
        $this->adminUser = User::firstWhere('email', 'admin@studiokode.com') ?? User::factory()->create([
            'name' => 'StudioKode Administrator',
            'email' => 'admin@studiokode.com',
        ]);
    }

    /**
     * Helper to perform an Inertia-aware GET request with standard headers.
     */
    protected function getInertia(string $uri, array $headers = [])
    {
        return $this->withHeaders(array_merge([
            'X-Requested-With' => 'XMLHttpRequest',
        ], $headers))->get($uri);
    }

    /**
     * Helper to assert that a string contains all expected redesign color tokens.
     */
    protected function assertContainsRedesignTokens(string $content, string $messagePrefix = ''): void
    {
        $prefix = $messagePrefix ? "[$messagePrefix] " : '';
        $this->assertStringContainsString('#0D0D0D', $content, "{$prefix}Must include Jet Black (#0D0D0D)");
        $this->assertStringContainsString('#224F10', $content, "{$prefix}Must include Forest Green (#224F10)");
        $this->assertStringContainsString('#B0E454', $content, "{$prefix}Must include Lime Green (#B0E454)");
        $this->assertStringContainsString('#EAFFD0', $content, "{$prefix}Must include Soft Ivory (#EAFFD0)");
    }
}
