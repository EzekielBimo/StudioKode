<?php

namespace Tests\Feature;

use Dotenv\Dotenv;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Env;
use Tests\TestCase;

class EnvironmentLoaderTest extends TestCase
{
    private string $envLocalPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->envLocalPath = base_path('.env.local');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->envLocalPath)) {
            @unlink($this->envLocalPath);
        }
        parent::tearDown();
    }

    /**
     * Test that if .env.local exists with only partial variables (e.g. VERCEL_OIDC_TOKEN),
     * the application backfills base .env variables so APP_KEY remains present.
     */
    public function test_env_local_with_partial_variables_backfills_base_env(): void
    {
        // Write partial .env.local mimicking Vercel CLI token output
        file_put_contents($this->envLocalPath, "VERCEL_OIDC_TOKEN=test_token_regression_guard\n");

        try {
            // Re-bootstrap kernel with .env.local active
            $app = require base_path('bootstrap/app.php');
            $app->loadEnvironmentFrom('.env.local');
            $kernel = $app->make(Kernel::class);
            $kernel->bootstrap();

            $this->assertNotEmpty(env('APP_KEY'), 'APP_KEY should be backfilled from base .env');
            $this->assertSame('test_token_regression_guard', env('VERCEL_OIDC_TOKEN'));
            $this->assertSame('127.0.0.1', env('DB_HOST'));
        } finally {
            if (file_exists($this->envLocalPath)) {
                @unlink($this->envLocalPath);
            }
        }
    }

    /**
     * Test that if .env.local explicitly defines an override, the override takes precedence.
     */
    public function test_env_local_overrides_base_env_keys(): void
    {
        file_put_contents($this->envLocalPath, "APP_NAME=StudioKodeLocalOverride\n");

        try {
            $app = require base_path('bootstrap/app.php');
            $app->loadEnvironmentFrom('.env.local');
            $kernel = $app->make(Kernel::class);
            $kernel->bootstrap();

            $this->assertSame('StudioKodeLocalOverride', env('APP_NAME'));
            $this->assertNotEmpty(env('APP_KEY'));
        } finally {
            if (file_exists($this->envLocalPath)) {
                @unlink($this->envLocalPath);
            }
        }
    }
}
