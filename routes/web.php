<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ProjectImageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Multi-page portfolio routes for StudioKode Landscape & Garden Architecture.
|
*/

// Home Showcase
Route::get('/', [HomeController::class, 'index'])->name('home');

// Project Portfolio
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');

// Studio & Informational Pages
Route::get('/studio', [PageController::class, 'studio'])->name('studio');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Consultations & Inquiries
Route::post('/inquiries', [InquiryController::class, 'store'])->name('inquiries.store');
Route::post('/contact', [InquiryController::class, 'store'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| Administrative Suite Routes (StudioKode Admin)
|--------------------------------------------------------------------------
*/

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'store'])->name('admin.login.store');
});

// Protected Administrative Routes
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Projects Management (Features 16-19)
    Route::resource('projects', AdminProjectController::class)->parameters([
        'projects' => 'project:id',
    ]);
    Route::get('/projects/{project:id}/gallery', [AdminProjectController::class, 'edit'])->name('projects.gallery');

    // Gallery Management (Features 20-22)
    Route::post('/projects/{project:id}/images', [ProjectImageController::class, 'store'])->name('projects.images.store');
    Route::post('/projects/{project:id}/images/reorder', [ProjectImageController::class, 'reorder'])->name('projects.images.reorder');

    // Image Caption Update & Deletion (Supports both /admin/project-images/{image} and /admin/images/{image})
    Route::put('/project-images/{image}', [ProjectImageController::class, 'update'])->name('images.update');
    Route::put('/images/{image}', [ProjectImageController::class, 'update']);

    Route::delete('/project-images/{image}', [ProjectImageController::class, 'destroy'])->name('images.destroy');
    Route::delete('/images/{image}', [ProjectImageController::class, 'destroy']);

    // Inquiries Management (M2)
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
    Route::match(['put', 'patch'], '/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update');
    Route::delete('/inquiries/{inquiry}', [AdminInquiryController::class, 'destroy'])->name('inquiries.destroy');

    // Profile & Password Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::match(['put', 'patch'], '/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Remote Database Migration & Setup Utility for Vercel Serverless
Route::get('/artisan/migrate', function (\Illuminate\Http\Request $request) {
    $expectedKey = env('APP_KEY');
    if (!$expectedKey || $request->query('key') !== $expectedKey) {
        abort(403, 'Unauthorized. Please provide valid ?key=APP_KEY parameter.');
    }

    try {
        config(['cache.default' => 'array']);

        if ($request->boolean('status')) {
            $tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
            $sequences = \Illuminate\Support\Facades\DB::select("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = 'public'");
            $migrations = \Illuminate\Support\Facades\Schema::hasTable('migrations')
                ? \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration')->all()
                : [];
            return response()->json([
                'status' => 'success',
                'tables' => array_column($tables, 'table_name'),
                'sequences' => array_column($sequences, 'sequence_name'),
                'migrations' => $migrations,
            ]);
        }

        if ($request->boolean('fresh', false)) {
            \Illuminate\Support\Facades\DB::unprepared("
                DO $$ DECLARE
                    r RECORD;
                BEGIN
                    FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = 'public') LOOP
                        EXECUTE 'DROP TABLE IF EXISTS public.' || quote_ident(r.tablename) || ' CASCADE';
                    END LOOP;
                    FOR r IN (SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = 'public') LOOP
                        EXECUTE 'DROP SEQUENCE IF EXISTS public.' || quote_ident(r.sequence_name) || ' CASCADE';
                    END LOOP;
                END $$;
            ");
        }

        // Ensure migrations table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
            \Illuminate\Support\Facades\Schema::create('migrations', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
        }

        $migrationFiles = [
            '0001_01_01_000000_create_users_table' => function() {
                if (!\Illuminate\Support\Facades\Schema::hasTable('users')) {
                    $m = require database_path('migrations/0001_01_01_000000_create_users_table.php');
                    $m->up();
                }
            },
            '0001_01_01_000001_create_cache_table' => function() {
                if (!\Illuminate\Support\Facades\Schema::hasTable('cache')) {
                    $m = require database_path('migrations/0001_01_01_000001_create_cache_table.php');
                    $m->up();
                } elseif (!\Illuminate\Support\Facades\Schema::hasTable('cache_locks')) {
                    \Illuminate\Support\Facades\Schema::create('cache_locks', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('key')->primary();
                        $table->string('owner');
                        $table->integer('expiration');
                    });
                }
            },
            '0001_01_01_000002_create_jobs_table' => function() {
                if (!\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
                    $m = require database_path('migrations/0001_01_01_000002_create_jobs_table.php');
                    $m->up();
                }
            },
            '2024_01_01_000001_create_projects_table' => function() {
                if (!\Illuminate\Support\Facades\Schema::hasTable('projects')) {
                    $m = require database_path('migrations/2024_01_01_000001_create_projects_table.php');
                    $m->up();
                }
            },
            '2024_01_01_000002_create_project_images_table' => function() {
                if (!\Illuminate\Support\Facades\Schema::hasTable('project_images')) {
                    $m = require database_path('migrations/2024_01_01_000002_create_project_images_table.php');
                    $m->up();
                }
            },
            '2024_01_01_000003_create_inquiries_table' => function() {
                if (!\Illuminate\Support\Facades\Schema::hasTable('inquiries')) {
                    $m = require database_path('migrations/2024_01_01_000003_create_inquiries_table.php');
                    $m->up();
                }
            },
            '2024_01_02_000001_add_performance_composite_indexes' => function() {
                $m = require database_path('migrations/2024_01_02_000001_add_performance_composite_indexes.php');
                $m->up();
            },
        ];

        $results = [];
        foreach ($migrationFiles as $name => $closure) {
            $exists = \Illuminate\Support\Facades\DB::table('migrations')
                ->where('migration', $name)
                ->exists();
            if (!$exists) {
                $closure();
                \Illuminate\Support\Facades\DB::table('migrations')->insert([
                    'migration' => $name,
                    'batch' => 1,
                ]);
                $results[] = "$name: applied";
            } else {
                $results[] = "$name: already migrated";
            }
        }

        $seedOutput = '';
        if ($request->boolean('seed', true)) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
            $seedOutput = \Illuminate\Support\Facades\Artisan::output();
        }

        $tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

        return response()->json([
            'status' => 'success',
            'message' => 'Database successfully migrated and seeded!',
            'migrations' => $results,
            'seed_output' => $seedOutput,
            'tables' => array_column($tables, 'table_name'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});
