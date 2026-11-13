# StudioKode Backend Architecture Investigation Report

**Document**: `handoff.md`  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_1`  
**Working Directory**: `c:\laragon\www\StudioKode`  
**Date**: 2026-11-09T11:51:00Z  
**Target Scope**: Backend Architecture, Database Layer, Authentication, Routing, Controllers, and Requirements R1 through R6  

---

## 1. Observation

### 1.1 Runtime Environment & Core Dependencies
- **PHP Version**: `PHP 8.3.30 (cli) (built: Jan 13 2026 22:50:40) (ZTS Visual C++ 2019 x64)` (verified via `php -v`).
- **Laravel Framework**: `Laravel Framework 11.57.1` (verified via `php artisan --version` and `composer.json:10`).
- **Composer Dependencies (`composer.json`)**:
  - `php`: `^8.2` (`composer.json:8`)
  - `inertiajs/inertia-laravel`: `^3.7` (`composer.json:9`)
  - `laravel/framework`: `^11.0` (`composer.json:10`)
  - `laravel/tinker`: `^2.9` (`composer.json:11`)
  - `phpunit/phpunit`: `^10.5` (`composer.json:19`)
- **Frontend Stack (`package.json`)**:
  - `@inertiajs/react`: `^1.2.0` (`package.json:9`)
  - `react`: `^18.2.0` (`package.json:13`)
  - `react-dom`: `^18.2.0` (`package.json:14`)
  - `tailwindcss`: `^3.4.1` (`package.json:23`)
  - `framer-motion`: `^11.0.0` (`package.json:11`)
  - `lucide-react`: `^0.359.0` (`package.json:12`)
  - `vite`: `^5.0` (`package.json:24`)
  - `laravel-vite-plugin`: `^1.0` (`package.json:21`)

### 1.2 Database & Environment Configuration
- **Application Environment (`.env`)**:
  - `APP_NAME=StudioKode` (`.env:1`)
  - `APP_ENV=local`, `APP_DEBUG=true` (`.env:2-4`)
  - `DB_CONNECTION=mysql` (`.env:22`)
  - `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=studiokode`, `DB_USERNAME=root`, `DB_PASSWORD=` (`.env:23-27`)
  - `SESSION_DRIVER=database` (`.env:29`)
  - `FILESYSTEM_DISK=local` (`.env:36`)
- **Testing Configuration (`phpunit.xml`)**:
  - `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` (`phpunit.xml:25-26`).
  - `SESSION_DRIVER=array` (`phpunit.xml:30`).
  - *Observation Note*: All SQL logic, query builders, and raw expressions must be compatible with both MySQL 8.4 and SQLite 3 in memory.

### 1.3 Database Migrations & Existing Schema
Executed `php artisan migrate:status`. All 6 migrations have run (`Batch [1] Ran`):
1. `0001_01_01_000000_create_users_table.php`:
   - `users`: `id`, `name`, `email` (unique), `email_verified_at`, `password`, `remember_token`, `timestamps`.
   - `password_reset_tokens`: `email` (primary), `token`, `created_at`.
   - `sessions`: `id` (primary), `user_id` (nullable, index), `ip_address`, `user_agent`, `payload`, `last_activity` (index).
2. `0001_01_01_000001_create_cache_table.php`:
   - `cache`, `cache_locks`.
3. `0001_01_01_000002_create_jobs_table.php`:
   - `jobs`, `job_batches`, `failed_jobs`.
4. `2024_01_01_000001_create_projects_table.php`:
   - `projects`: `id`, `title`, `slug` (unique), `excerpt` (nullable), `description` (longText), `client` (nullable), `location`, `year` (unsignedSmallInteger), `area_size` (VARCHAR 50, e.g. "240 m²"), `cover_image`, `masterplan_image` (nullable), `is_featured` (boolean, index), `published_at` (timestamp nullable, index), `timestamps`.
5. `2024_01_01_000002_create_project_images_table.php`:
   - `project_images`: `id`, `project_id` (foreignId -> `projects.id` with `cascadeOnDelete()`), `image_path`, `caption` (nullable), `sort_order` (integer, default 0, index), `timestamps`.
6. `2024_01_01_000003_create_inquiries_table.php`:
   - `inquiries`: `id`, `name`, `email`, `phone` (VARCHAR 50 nullable), `site_location` (nullable), `estimated_area` (VARCHAR 100 nullable), `budget_range` (VARCHAR 100 nullable), `message` (text), `status` (VARCHAR 50, default 'new', index), `timestamps`.

### 1.4 Models & Relationships
- `App\Models\User`: Standard authenticatable model (`app/Models/User.php:10-46`).
- `App\Models\Project` (`app/Models/Project.php:10-110`):
  - Relationship `images()`: `return $this->hasMany(ProjectImage::class)->orderBy('sort_order', 'asc');` (`Project.php:67-70`).
  - Scopes: `scopeFeatured`, `scopePublished` (`Project.php:75-87`).
  - Route Key: `getRouteKeyName() => 'slug'` (`Project.php:92-95`).
  - Accessor: `getAreaNumericAttribute()` strips non-numeric characters from `area_size` (`Project.php:100-109`).
  - *Note*: Because `getRouteKeyName()` is `'slug'`, public routing uses `/projects/{slug}`. For admin CRUD, binding by `id` (e.g. `{project:id}`) is recommended to avoid collisions or breakages when updating a project's slug.
- `App\Models\ProjectImage` (`app/Models/ProjectImage.php:9-51`):
  - Relationship `project()`: `return $this->belongsTo(Project::class);` (`ProjectImage.php:47-50`).
- `App\Models\Inquiry` (`app/Models/Inquiry.php:8-46`):
  - Fillable contains all consultation fields.

### 1.5 Database Seeding & Current State
- `database/seeders/DatabaseSeeder.php:1-270`:
  - Seeds 6 projects with masterplans and 24 gallery images (4 per project) using remote Unsplash URLs (`DatabaseSeeder.php:26-245`).
  - Seeds 2 consultation inquiries (`Julian Finch` with status `'new'`, `Seraphina Vance` with status `'reviewed'`) (`DatabaseSeeder.php:248-268`).
  - **Zero Users Seeded**: `App\Models\User::count()` returns `0`. There is currently no administrative user seeded in the database.

### 1.6 Routing & Controllers
- **Routes (`routes/web.php:1-32`)**:
  - `GET /` -> `HomeController@index` (`name('home')`)
  - `GET /projects` -> `ProjectController@index` (`name('projects.index')`)
  - `GET /projects/{slug}` -> `ProjectController@show` (`name('projects.show')`)
  - `GET /studio` -> `PageController@studio` (`name('studio')`)
  - `GET /services` -> `PageController@services` (`name('services')`)
  - `GET /contact` -> `PageController@contact` (`name('contact')`)
  - `POST /inquiries` -> `InquiryController@store` (`name('inquiries.store')`)
  - `POST /contact` -> `InquiryController@store` (`name('contact.store')`)
  - **No Admin Routes Exist**: No `/admin`, `/admin/login`, or admin CRUD routes exist.
- **Middleware & Bootstrap Configuration (`bootstrap/app.php:1-21`)**:
  - Laravel 11 structure. Web middleware appends `HandleInertiaRequests::class` (`bootstrap/app.php:15-17`).
  - No guest/auth redirects (`redirectGuestsTo` / `redirectUsersTo`) are currently configured.
- **Inertia Shared Data (`app/Http/Middleware/HandleInertiaRequests.php:36-45`)**:
  ```php
  return array_merge(parent::share($request), [
      'appName' => config('app.name', 'StudioKode'),
      'flash' => [
          'success' => fn () => $request->session()->get('success'),
          'error' => fn () => $request->session()->get('error'),
      ],
  ]);
  ```
  - **Missing**: `'auth.user'` is not shared. Frontend admin layouts cannot inspect the current authenticated user without adding `'auth'` to this share array.

### 1.7 Filesystem & Storage Configuration
- `config/filesystems.php:39-45`: Disk `'public'` maps to `storage/app/public` with URL `/storage`.
- Symlink Check: `Test-Path public\storage` returns `False`.
  - The symbolic link has not been created yet via `php artisan storage:link`.
  - Image paths currently in the database are full HTTP URLs (Unsplash). Uploaded images will be local file paths in `storage/app/public/projects`. Application logic must support both formats seamlessly.

### 1.8 Existing Test Suite & Contract Conflict Discovery
- Executed `php artisan test`: All **83 tests passed (711 assertions)**.
- **CRITICAL DISCOVERY in `tests/Feature/ChallengerM1_2_RouteContractTest.php:407-415`**:
  ```php
  /**
   * 404 test on completely non-existent top-level routes.
   */
  public function test_404_response_for_non_existent_system_routes(): void
  {
      $bogusRoutes = ['/admin', '/dashboard', '/api/projects', '/portfolio', '/about-us'];

      foreach ($bogusRoutes as $route) {
          $response = $this->get($route);
          $this->assertSame(404, $response->getStatusCode(), "Expected 404 for route: {$route}");
      }
  }
  ```
  - The public portfolio test suite explicitly asserts that `/admin` and `/dashboard` return HTTP 404!
  - When admin route protection is implemented per Requirement R1, unauthenticated requests to `/admin` will return HTTP 302 (redirect to `/admin/login`), causing this legacy test assertion to fail.
  - When implementing the admin suite, this test must be updated to test authentic bogus routes (e.g. `'/bogus-admin'`, `'/unknown-dashboard'`).

---

## 2. Logic Chain

### 2.1 Requirement R1: Admin Authentication & Route Protection
1. **Observation**: `User` model and `users` table exist, but `User::count()` is `0`. `config/auth.php` uses session driver with `User` provider. No login route exists.
2. **Logic Step**: To satisfy R1, an admin account must be seeded (e.g., `admin@studiokode.com` / `password`), and authentication endpoints must be created (`GET /admin/login`, `POST /admin/login`, `POST /admin/logout`).
3. **Route Protection**: All administrative endpoints under `/admin/*` must be wrapped in `middleware(['auth'])`.
4. **Redirection Logic**: In Laravel 11, unauthenticated guests hitting an `auth` route redirect to the route named `login`. Naming the GET `/admin/login` route as both `login` and `admin.login` (or defining `$middleware->redirectGuestsTo(fn () => route('login'))` in `bootstrap/app.php`) ensures automated redirection. Authenticated users hitting `/admin/login` must redirect to `/admin` via `guest` middleware.
5. **Session & Shared Props**: Adding `'auth' => ['user' => ...]` to `HandleInertiaRequests::share` delivers authenticated user state to React without impacting existing test contracts.

### 2.2 Requirement R2: Projects CRUD Management
1. **Observation**: All 12 required fields (`title`, `slug`, `excerpt`, `description`, `client`, `location`, `year`, `area_size`, `cover_image`, `masterplan_image`, `is_featured`, `published_at`) already exist in `projects` table and `$fillable` on `Project` model.
2. **Logic Step**: No database migrations are necessary. The backend requires `App\Http\Controllers\Admin\ProjectController` providing:
   - `index`: Filtering (search keyword across title/location/client, status: published/draft, featured: yes/no), sorting (newest, oldest, title, area size), pagination, and eager loading image counts.
   - `create`: Renders `Admin/Projects/Create`.
   - `store`: Validation with Bahasa Indonesia error messages, automatic slug generation (`Str::slug`), cover & masterplan file upload to `storage/app/public/projects`, persistence, and flash message.
   - `edit`: Eager load gallery images (`with(['images' => fn($q) => $q->orderBy('sort_order', 'asc')])`), renders `Admin/Projects/Edit`.
   - `update`: Handles optional replacement of cover/masterplan images, slug uniqueness validation ignoring `$project->id`, and updates record.
   - `destroy`: Cleans up local disk images and deletes project record (foreign key cascade removes related `project_images`).
3. **Route Binding Strategy**: Because `Project::getRouteKeyName()` returns `'slug'`, admin routes should use `{project:id}` parameter scoping (e.g. `Route::resource('projects', AdminProjectController::class)->parameters(['projects' => 'project:id'])`) so admin edits use primary keys rather than fragile slugs.

### 2.3 Requirement R3: Project Gallery Images Management
1. **Observation**: `project_images` table contains `project_id`, `image_path`, `caption`, `sort_order`, with foreign key `cascadeOnDelete()`.
2. **Logic Step**: An administrative controller `App\Http\Controllers\Admin\ProjectImageController` handles:
   - `store`: Multi-file upload (`images.*` file upload), computing next `sort_order`, saving to `public` disk, and creating records.
   - `update`: Updating `caption` or individual `sort_order`.
   - `reorder`: Accepts array of `image_ids` in desired order and batch-updates `sort_order = $index + 1`.
   - `destroy`: Removes local storage file and deletes row.

### 2.4 Requirement R4: Inquiries Management
1. **Observation**: `inquiries` table contains all contact fields and `status`. Seed data uses `'new'` and `'reviewed'`. Requirement R4 specifies statuses: `'new'`, `'contacted'`, `'archived'`.
2. **Logic Step**: `App\Http\Controllers\Admin\InquiryController` handles:
   - `index`: Status filtering (`all`, `new`, `contacted`, `archived`), text search across sender details, latest ordering, pagination, and status badge counts.
   - `show`: Renders full inquiry details in `Admin/Inquiries/Show`.
   - `update`: Validates and updates status field (`new`, `contacted`, `archived`).
   - `destroy`: Deletes inquiry.

### 2.5 Requirement R5: Dashboard, Navigation, & Bahasa Indonesia Localization
1. **Observation**: No admin dashboard exists. Tailwind palette is defined with `studio.foliage` (`#1C2A21`), `studio.offwhite` (`#F9F8F6`), and serif/sans typography.
2. **Logic Step**: `App\Http\Controllers\Admin\DashboardController` aggregates:
   - KPI metrics: `total_projects`, `featured_projects`, `published_projects`, `total_inquiries`, `new_inquiries`, `contacted_inquiries`.
   - Recent items: 5 latest projects, 5 latest inquiries.
   - Renders `Admin/Dashboard`.
3. **Localization**: All controller flash messages (`success`, `error`), validation errors, and status labels must be delivered in Bahasa Indonesia (e.g., "Proyek berhasil disimpan", "Status pesan diperbarui", "Baru", "Dihubungi", "Diarsipkan").

### 2.6 Requirement R6: Query Optimization & Performance
1. **Observation**: Public `ProjectController::index` already eager-loads `with('images')`.
2. **Logic Step**:
   - For Admin project listings: Use `Project::withCount('images')` instead of loading 24+ heavy image models per page.
   - For Admin project edit view: Use eager loading `$project->load(['images' => fn($q) => $q->orderBy('sort_order', 'asc')])`.
   - For Assets: Configure Rollup manual chunks in `vite.config.js` for vendor libraries (`react`, `@inertiajs/react`, `framer-motion`, `lucide-react`).
   - Ensure `php artisan storage:link` is established for fast static asset serving.

---

## 3. Caveats

1. **Read-Only Investigation**: No source code, database tables, or test files were modified during this investigation.
2. **Storage Symlink**: `public/storage` does not exist on disk yet. It must be created via `php artisan storage:link` during the build/setup phase.
3. **Dual Image Paths**: Seed data contains external URLs (`https://images.unsplash.com/...`), whereas uploaded files will have relative storage paths (`projects/...` or `/storage/projects/...`). Frontend and backend image display helpers must accommodate both:
   ```php
   str_starts_with($path, 'http') ? $path : asset('storage/' . $path)
   ```
4. **Test Fixture Conflict in `ChallengerM1_2_RouteContractTest.php:409`**: Once `/admin` routes are registered, the test asserting that `/admin` and `/dashboard` return 404 will fail. This test must be adapted to non-admin test routes.

---

## 4. Conclusion & Recommended Architecture

The StudioKode backend architecture is exceptionally clean and well-prepared for the admin dashboard implementation. No schema migrations are needed.

### 4.1 Recommended Route Architecture (`routes/web.php`)
```php
// Admin Guest Authentication
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [Admin\AuthController::class, 'create'])->name('login');
    Route::post('/admin/login', [Admin\AuthController::class, 'store'])->name('admin.login.store');
});

// Admin Protected Suite
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::post('/logout', [Admin\AuthController::class, 'destroy'])->name('logout');

    // R5: Dashboard
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [Admin\DashboardController::class, 'index']);

    // R2: Projects CRUD
    Route::get('/projects', [Admin\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [Admin\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [Admin\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project:id}/edit', [Admin\ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project:id}', [Admin\ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project:id}', [Admin\ProjectController::class, 'destroy'])->name('projects.destroy');

    // R3: Project Gallery Images Management
    Route::post('/projects/{project:id}/images', [Admin\ProjectImageController::class, 'store'])->name('projects.images.store');
    Route::post('/projects/{project:id}/images/reorder', [Admin\ProjectImageController::class, 'reorder'])->name('projects.images.reorder');
    Route::put('/project-images/{image}', [Admin\ProjectImageController::class, 'update'])->name('images.update');
    Route::delete('/project-images/{image}', [Admin\ProjectImageController::class, 'destroy'])->name('images.destroy');

    // R4: Inquiries Management
    Route::get('/inquiries', [Admin\InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [Admin\InquiryController::class, 'show'])->name('inquiries.show');
    Route::patch('/inquiries/{inquiry}', [Admin\InquiryController::class, 'update'])->name('inquiries.update');
    Route::delete('/inquiries/{inquiry}', [Admin\InquiryController::class, 'destroy'])->name('inquiries.destroy');
});
```

### 4.2 Recommended Controller Architecture
1. `app/Http/Controllers/Admin/AuthController.php`:
   - Methods: `create`, `store`, `destroy`.
   - Localized error: `'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.'`
2. `app/Http/Controllers/Admin/DashboardController.php`:
   - Method: `index` (returns metrics & latest projects and inquiries to `Admin/Dashboard`).
3. `app/Http/Controllers/Admin/ProjectController.php`:
   - Methods: `index`, `create`, `store`, `edit`, `update`, `destroy`.
   - Query optimizations: `withCount('images')` on list, eager-loaded ordered images on edit.
4. `app/Http/Controllers/Admin/ProjectImageController.php`:
   - Methods: `store` (multi-upload), `update` (caption), `reorder` (sequence update), `destroy`.
5. `app/Http/Controllers/Admin/InquiryController.php`:
   - Methods: `index` (filtered by status), `show`, `update` (status), `destroy`.

### 4.3 Seeding Requirement
In `database/seeders/DatabaseSeeder.php`, append admin user creation:
```php
User::firstOrCreate(
    ['email' => 'admin@studiokode.com'],
    [
        'name' => 'Admin StudioKode',
        'password' => Hash::make('password'),
    ]
);
```

### 4.4 Middleware Enhancement
In `app/Http/Middleware/HandleInertiaRequests.php`, expose `auth.user`:
```php
'auth' => [
    'user' => $request->user() ? [
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
    ] : null,
],
```

---

## 5. Verification Method

To independently verify all findings and test subsequent implementation:

1. **Verify Existing Tests**:
   ```powershell
   php artisan test
   ```
   *Expected Result*: All 83 existing tests pass.

2. **Verify Database Connection and Table Status**:
   ```powershell
   php artisan migrate:status
   php artisan tinker --execute="echo 'Projects: ' . App\Models\Project::count() . ', Users: ' . App\Models\User::count();"
   ```
   *Expected Result*: All 6 migrations status `Ran`. Projects: 6, Users: 0 (prior to seeding admin).

3. **Verify Storage Link**:
   ```powershell
   php artisan storage:link
   Test-Path public\storage
   ```
   *Expected Result*: Returns `True`.

4. **Verify Vite Production Build & Asset Splitting**:
   ```powershell
   npm run build
   ```
   *Expected Result*: Production build completes with exit code 0 and valid manifest.

5. **Invalidation Conditions**:
   - If `test_404_response_for_non_existent_system_routes` in `ChallengerM1_2_RouteContractTest.php` fails upon adding `/admin`, verify that `/admin` and `/dashboard` were updated to authentic bogus paths in the test.
   - If SQLite memory database fails on any raw SQL ordering, verify that `REPLACE` functions adhere to ANSI SQL syntax supported by both SQLite and MySQL.
