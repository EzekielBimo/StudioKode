# Handoff Report — Explorer M3.1: Projects Controller & Models Architecture

## 1. Observation

### 1.1 Test Suite Contracts (`tests/Feature/Admin/AdminProjectTest.php`)
Inspection of all 19 tests in `tests/Feature/Admin/AdminProjectTest.php` reveals the following explicit interface contracts:

1. **Authentication Gate**:
   - `GET /admin/projects` without authentication redirects with HTTP 302 to `/admin/login` (lines 40-46).
2. **Projects Index Rendering & Contracts**:
   - `GET /admin/projects` with authenticated admin returns HTTP 200, rendering Inertia component `Admin/Projects/Index` with `projects` prop (lines 52-63).
   - Eager-loading count: `Project::query()->withCount('images')` is required by `tests/Feature/Admin/AdminPerformanceTest.php` (lines 38-79) to strictly bound query execution to $O(1)$ regardless of project count (<= queriesForTwo + 2).
   - Filtering by search: `GET /admin/projects?search=Moss` filters matching records across `title` OR `location` (lines 382-396).
   - Filtering by featured status: `GET /admin/projects?featured=1` filters by `is_featured = 1` (lines 402-410).
   - Search without matches: `GET /admin/projects?search=non_existent_keyword_xyz` returns HTTP 200 with an empty collection cleanly (lines 416-424).
3. **Project Creation Form**:
   - `GET /admin/projects/create` returns HTTP 200, rendering Inertia component `Admin/Projects/Create` (lines 69-77).
4. **Project Storage & Validation**:
   - `POST /admin/projects` persists project data and redirects with HTTP 302 to `/admin/projects` with `session('success')` flash notification (lines 83-118).
   - Validation requires mandatory fields: `title`, `description`, `location`, `year`, `area_size` (lines 206-220).
   - Slug handling: Auto-generates slug from `title` via `Str::slug` when `slug` is omitted or empty (lines 226-248).
   - Slug uniqueness: Rejects duplicate slug with validation error on `slug` (lines 253-273).
   - File uploads: Rejects non-image files (e.g. PDF) with validation error on `cover_image` (lines 338-357). Valid image files are uploaded to `public` disk in `projects/` directory (`cover_image`, `masterplan_image`).
5. **Project Edit Form**:
   - `GET /admin/projects/{project:id}/edit` returns HTTP 200, rendering Inertia component `Admin/Projects/Edit` with `project` prop and eager-loaded `images` relation ordered by `sort_order ASC` (lines 124-140).
   - Confirmed by `AdminPerformanceTest.php` (lines 85-104) where query count must be <= 4 queries.
   - Non-existent project ID returns HTTP 404 (lines 363-370).
6. **Project Update Action**:
   - `PUT /admin/projects/{project:id}` updates project attributes, redirects with HTTP 302 to `/admin/projects`, and sets `session('success')` flash notification (lines 146-177).
   - Slug uniqueness on update ignores current project ID (`$project->id`) (lines 279-301).
   - Retains existing `cover_image` and `masterplan_image` when no new image files are uploaded (lines 307-332).
7. **Project Cascade Deletion**:
   - `DELETE /admin/projects/{project:id}` removes project record, cascades to delete associated `project_images` records, and redirects with HTTP 302 to `/admin/projects` with `session('success')` flash message (lines 183-194, lines 434-456).
   - Non-existent project ID returns HTTP 404 (lines 368-370).
   - Associated storage files (`cover_image`, `masterplan_image`, and child `project_images`) must be cleaned up from storage disk.

### 1.2 Model & Route Key Binding Observations
- In `app/Models/Project.php` (lines 92-95):
  ```php
  public function getRouteKeyName(): string
  {
      return 'slug';
  }
  ```
  Because `getRouteKeyName()` returns `'slug'`, implicit route model binding for `{project}` defaults to `slug`.
  However, all admin tests and admin URL routes interact via integer ID:
  - `GET /admin/projects/{project:id}/edit` -> e.g. `/admin/projects/1/edit`
  - `PUT /admin/projects/{project:id}` -> e.g. `/admin/projects/1`
  - `DELETE /admin/projects/{project:id}` -> e.g. `/admin/projects/1`
  Therefore, the admin resource route must be configured with parameter scoping:
  `->parameters(['projects' => 'project:id'])`.
- In `database/migrations/2024_01_01_000001_create_projects_table.php` (lines 14-29):
  Columns: `id`, `title` (string), `slug` (string unique), `excerpt` (text nullable), `description` (longText), `client` (string nullable), `location` (string), `year` (unsignedSmallInteger), `area_size` (string 50), `cover_image` (string), `masterplan_image` (string nullable), `is_featured` (boolean default false), `published_at` (timestamp nullable), `timestamps`.
- In `database/migrations/2024_01_01_000002_create_project_images_table.php` (line 16):
  `$table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();`
  Database-level cascade on delete is already configured and enforced.
- In `routes/web.php` (lines 56-63):
  Currently exists only placeholder stubs returning empty Inertia views for `admin.projects.index` and `admin.projects.create`. `store`, `show`, `edit`, `update`, `destroy` routes are entirely missing, causing HTTP 405 / 404 in tests.

---

## 2. Logic Chain

1. **Route Model Binding Resolution**:
   - Premise: `Project::getRouteKeyName()` is `'slug'` for frontend portfolio routes (`/projects/{slug}`).
   - Requirement: Admin routes require identification by integer ID (`/admin/projects/{id}`, `/admin/projects/{id}/edit`).
   - Inference: Declaring `Route::resource('projects', AdminProjectController::class)->parameters(['projects' => 'project:id'])` in `routes/web.php` instructs Laravel's router to map the `{project}` route parameter to the `id` column (`WHERE id = ?`), seamlessly resolving models by ID while preserving 404 responses for non-existent IDs.

2. **Eager Loading & N+1 Query Budget**:
   - Premise: `AdminPerformanceTest.php` strictly asserts query count for 2 projects vs 10 projects (`$queriesForTen <= $queriesForTwo + 2`), and edit screen queries `<= 4`.
   - Inference: In `ProjectController::index`, using `Project::query()->withCount('images')` ensures gallery counts are computed via subquery without N+1 overhead. In `ProjectController::edit`, calling `$project->load(['images' => fn ($q) => $q->orderBy('sort_order', 'asc')])` loads all child gallery images in a single eager-loading query.

3. **Slug Auto-Generation & Validation Workflow**:
   - Premise: When `slug` is submitted empty, it must auto-generate from `title` via `Str::slug`. When duplicate, it must be rejected with validation error on `slug`. On update, existing project's own slug must be permitted.
   - Inference: Pre-processing the slug before validation (`if (empty(trim((string)$rawSlug)) && $request->filled('title')) $request->merge(['slug' => Str::slug($request->input('title'))])`) allows standard Laravel validation rule `Rule::unique('projects', 'slug')->ignore($project->id ?? null)` to validate both user-entered and auto-generated slugs accurately.

4. **File Upload & Storage Lifecycle**:
   - Premise: Files uploaded must be saved on `'public'` disk under directory `projects/`. When updating without new files, existing cover and masterplan images must be retained. When deleting, uploaded files must be cleaned from disk.
   - Inference:
     - On store: check `$request->hasFile('cover_image')` -> store on `'public'` disk (`projects/`), fallback to default if omitted. Check `$request->hasFile('masterplan_image')` -> store if provided, else null.
     - On update: if new file uploaded, delete old local file (if not URL) and store new path; if no file uploaded, unset `cover_image`/`masterplan_image` from validated array so `$project->update()` retains existing paths.
     - On destroy: delete `cover_image`, `masterplan_image`, and each `$project->images` file from `Storage::disk('public')`, then call `$project->delete()`.

5. **Indonesian Localization Contract**:
   - Premise: In accordance with StudioKode localization guidelines (`resources/js/lib/id.js` and `PROJECT.md`), all feedback messages and validation errors must be in Bahasa Indonesia.
   - Inference: Controller returns flash notifications via `->with('success', 'Proyek berhasil ditambahkan.')`, `->with('success', 'Proyek berhasil diperbarui.')`, and `->with('success', 'Proyek berhasil dihapus.')`. Validation bags include custom Indonesian error strings for mandatory fields and file type constraints.

---

## 3. Caveats

- **Frontend Component Existence**: Inertia components (`Admin/Projects/Index.jsx`, `Admin/Projects/Create.jsx`, `Admin/Projects/Edit.jsx`) are currently stubs or pending implementation in M3.2. Inertia component tests will assert component file presence; frontend builder M3.2 must supply the React views.
- **Gallery Specific Routes**: Gallery multi-upload (`/admin/projects/{project:id}/images`), caption update, and reordering routes belong to Feature 20-22 (`ProjectImageController.php`). However, having `parameters(['projects' => 'project:id'])` on the parent projects resource provides the exact URI and parameter structure needed for nested gallery routes.
- **External Image URLs**: Seeded projects use Unsplash URLs (`https://images.unsplash.com/...`). The storage cleanup logic must check `!str_starts_with($path, 'http')` before attempting `Storage::disk('public')->delete()` to avoid errors.

---

## 4. Conclusion & Implementation Plan

### 4.1 Target File 1: `routes/web.php`
- **Location**: `c:\laragon\www\StudioKode\routes\web.php`
- **Imports**: Add at line 5:
  ```php
  use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
  ```
- **Route Definition**: In the protected admin route group (`Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(...)`), replace lines 56-63 with:
  ```php
  // Projects Management (Features 16-19)
  Route::resource('projects', AdminProjectController::class)->parameters([
      'projects' => 'project:id',
  ]);
  ```

### 4.2 Target File 2: `app/Http/Controllers/Admin/ProjectController.php`
- **Location**: `c:\laragon\www\StudioKode\app\Http\Controllers\Admin\ProjectController.php`
- **Complete Implementation**:
  ```php
  <?php

  namespace App\Http\Controllers\Admin;

  use App\Http\Controllers\Controller;
  use App\Models\Project;
  use Carbon\Carbon;
  use Illuminate\Http\RedirectResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Support\Str;
  use Illuminate\Validation\Rule;
  use Inertia\Inertia;
  use Inertia\Response;

  class ProjectController extends Controller
  {
      /**
       * Display a listing of projects with search, filter, and eager loaded image counts.
       */
      public function index(Request $request): Response
      {
          $query = Project::query()->withCount('images');

          // Filter by featured status toggle (?featured=1 or ?featured=0)
          if ($request->has('featured') && $request->query('featured') !== '' && $request->query('featured') !== null) {
              $featured = $request->query('featured');
              if ($featured === '1' || $featured === 'true' || $featured === 1) {
                  $query->where('is_featured', true);
              } elseif ($featured === '0' || $featured === 'false' || $featured === 0) {
                  $query->where('is_featured', false);
              }
          }

          // Full-text search across project title and location
          if ($request->filled('search')) {
              $search = trim((string) $request->query('search'));
              if ($search !== '') {
                  $query->where(function ($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                  });
              }
          }

          // Paginate results ordered by latest id
          $projects = $query->latest('id')->paginate(10)->withQueryString();

          return Inertia::render('Admin/Projects/Index', [
              'projects' => $projects,
              'filters' => [
                  'search' => $request->query('search', ''),
                  'featured' => $request->query('featured', ''),
              ],
          ]);
      }

      /**
       * Show the form for creating a new project.
       */
      public function create(): Response
      {
          return Inertia::render('Admin/Projects/Create');
      }

      /**
       * Store a newly created project in storage.
       */
      public function store(Request $request): RedirectResponse
      {
          // Auto-generate slug from title if slug is empty or omitted
          $rawSlug = $request->input('slug');
          if (empty(trim((string) $rawSlug)) && $request->filled('title')) {
              $request->merge(['slug' => Str::slug($request->input('title'))]);
          } elseif (!empty(trim((string) $rawSlug))) {
              $request->merge(['slug' => Str::slug($rawSlug)]);
          }

          $validated = $request->validate([
              'title' => ['required', 'string', 'max:255'],
              'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')],
              'excerpt' => ['nullable', 'string'],
              'description' => ['required', 'string'],
              'client' => ['nullable', 'string', 'max:255'],
              'location' => ['required', 'string', 'max:255'],
              'year' => ['required', 'integer', 'min:1900', 'max:2100'],
              'area_size' => ['required', 'string', 'max:50'],
              'cover_image' => ['nullable', 'image', 'max:10240'],
              'masterplan_image' => ['nullable', 'image', 'max:10240'],
              'is_featured' => ['nullable'],
              'published_at' => ['nullable'],
          ], [
              'title.required' => 'Judul proyek wajib diisi.',
              'description.required' => 'Deskripsi proyek wajib diisi.',
              'location.required' => 'Lokasi proyek wajib diisi.',
              'year.required' => 'Tahun proyek wajib diisi.',
              'area_size.required' => 'Luas area proyek wajib diisi.',
              'slug.unique' => 'Slug proyek sudah digunakan. Silakan gunakan slug lain.',
              'cover_image.image' => 'File gambar sampul harus berupa berkas gambar yang valid.',
              'masterplan_image.image' => 'File gambar masterplan harus berupa berkas gambar yang valid.',
          ]);

          // Upload cover_image to public disk (projects/ directory)
          if ($request->hasFile('cover_image')) {
              $validated['cover_image'] = $request->file('cover_image')->store('projects', 'public');
          } else {
              $validated['cover_image'] = 'projects/default-cover.jpg';
          }

          // Upload masterplan_image to public disk (projects/ directory) if provided
          if ($request->hasFile('masterplan_image')) {
              $validated['masterplan_image'] = $request->file('masterplan_image')->store('projects', 'public');
          } else {
              $validated['masterplan_image'] = null;
          }

          // Ensure slug exists if not in validated array
          if (empty($validated['slug'])) {
              $validated['slug'] = Str::slug($validated['title']);
          }

          // Handle booleans and timestamps
          $validated['is_featured'] = $request->boolean('is_featured');
          $validated['published_at'] = $request->filled('published_at')
              ? Carbon::parse($request->input('published_at'))
              : null;

          Project::create($validated);

          return redirect()->route('admin.projects.index')
              ->with('success', 'Proyek berhasil ditambahkan.');
      }

      /**
       * Show the specified project or redirect to edit.
       */
      public function show(Project $project): RedirectResponse
      {
          return redirect()->route('admin.projects.edit', $project);
      }

      /**
       * Show the form for editing the specified project with eager-loaded gallery images.
       */
      public function edit(Project $project): Response
      {
          $project->load(['images' => fn ($q) => $q->orderBy('sort_order', 'asc')]);

          return Inertia::render('Admin/Projects/Edit', [
              'project' => $project,
          ]);
      }

      /**
       * Update the specified project in storage.
       */
      public function update(Request $request, Project $project): RedirectResponse
      {
          // Auto-generate slug from title if empty
          $rawSlug = $request->input('slug');
          if (empty(trim((string) $rawSlug)) && $request->filled('title')) {
              $request->merge(['slug' => Str::slug($request->input('title'))]);
          } elseif (!empty(trim((string) $rawSlug))) {
              $request->merge(['slug' => Str::slug($rawSlug)]);
          }

          $validated = $request->validate([
              'title' => ['required', 'string', 'max:255'],
              'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($project->id)],
              'excerpt' => ['nullable', 'string'],
              'description' => ['required', 'string'],
              'client' => ['nullable', 'string', 'max:255'],
              'location' => ['required', 'string', 'max:255'],
              'year' => ['required', 'integer', 'min:1900', 'max:2100'],
              'area_size' => ['required', 'string', 'max:50'],
              'cover_image' => ['nullable', 'image', 'max:10240'],
              'masterplan_image' => ['nullable', 'image', 'max:10240'],
              'is_featured' => ['nullable'],
              'published_at' => ['nullable'],
          ], [
              'title.required' => 'Judul proyek wajib diisi.',
              'description.required' => 'Deskripsi proyek wajib diisi.',
              'location.required' => 'Lokasi proyek wajib diisi.',
              'year.required' => 'Tahun proyek wajib diisi.',
              'area_size.required' => 'Luas area proyek wajib diisi.',
              'slug.unique' => 'Slug proyek sudah digunakan oleh proyek lain.',
              'cover_image.image' => 'File gambar sampul harus berupa berkas gambar yang valid.',
              'masterplan_image.image' => 'File gambar masterplan harus berupa berkas gambar yang valid.',
          ]);

          // Retain existing cover_image if no new file is uploaded
          if ($request->hasFile('cover_image')) {
              if ($project->cover_image && !str_starts_with($project->cover_image, 'http') && Storage::disk('public')->exists($project->cover_image)) {
                  Storage::disk('public')->delete($project->cover_image);
              }
              $validated['cover_image'] = $request->file('cover_image')->store('projects', 'public');
          } else {
              unset($validated['cover_image']);
          }

          // Retain existing masterplan_image if no new file is uploaded
          if ($request->hasFile('masterplan_image')) {
              if ($project->masterplan_image && !str_starts_with($project->masterplan_image, 'http') && Storage::disk('public')->exists($project->masterplan_image)) {
                  Storage::disk('public')->delete($project->masterplan_image);
              }
              $validated['masterplan_image'] = $request->file('masterplan_image')->store('projects', 'public');
          } else {
              unset($validated['masterplan_image']);
          }

          // Ensure slug if present or update
          if (isset($validated['slug']) && empty($validated['slug'])) {
              $validated['slug'] = Str::slug($validated['title']);
          }

          // Handle booleans and timestamps
          if ($request->has('is_featured')) {
              $validated['is_featured'] = $request->boolean('is_featured');
          }
          if ($request->has('published_at')) {
              $validated['published_at'] = $request->filled('published_at')
                  ? Carbon::parse($request->input('published_at'))
                  : null;
          }

          $project->update($validated);

          return redirect()->route('admin.projects.index')
              ->with('success', 'Proyek berhasil diperbarui.');
      }

      /**
       * Remove the specified project from storage and delete associated files.
       */
      public function destroy(Project $project): RedirectResponse
      {
          // Clean up project cover and masterplan files from storage
          if ($project->cover_image && !str_starts_with($project->cover_image, 'http') && Storage::disk('public')->exists($project->cover_image)) {
              Storage::disk('public')->delete($project->cover_image);
          }
          if ($project->masterplan_image && !str_starts_with($project->masterplan_image, 'http') && Storage::disk('public')->exists($project->masterplan_image)) {
              Storage::disk('public')->delete($project->masterplan_image);
          }

          // Clean up child gallery image files from storage
          foreach ($project->images as $galleryImage) {
              if ($galleryImage->image_path && !str_starts_with($galleryImage->image_path, 'http') && Storage::disk('public')->exists($galleryImage->image_path)) {
                  Storage::disk('public')->delete($galleryImage->image_path);
              }
          }

          // Cascade delete via Eloquent / DB foreign key cascade
          $project->delete();

          return redirect()->route('admin.projects.index')
              ->with('success', 'Proyek berhasil dihapus.');
      }
  }
  ```

---

## 5. Verification Method

Once Builder M3.1 implements the controller and routes, and placeholder or complete views exist:

1. **Route List Verification**:
   ```powershell
   php artisan route:list --path=admin/projects
   ```
   **Expected**: 7 routes under `admin.projects.*` with binding `project:id`.

2. **Feature Test Suite**:
   ```powershell
   php artisan test tests/Feature/Admin/AdminProjectTest.php
   ```
   **Expected**: All 19 tests pass (Tier 1 happy paths, Tier 2 validation & edge cases, Tier 3 search & filter, Tier 4 cascade deletion & lifecycle).

3. **Performance & Query Count Verification**:
   ```powershell
   php artisan test tests/Feature/Admin/AdminPerformanceTest.php --filter=projects
   ```
   **Expected**: `test_projects_admin_index_prevents_n_plus_one_queries` and `test_project_edit_view_eager_loads_gallery_images` pass within query budgets.

4. **Invalidation Conditions**:
   - If `Route::resource` does NOT specify `->parameters(['projects' => 'project:id'])`, route model binding will attempt `WHERE slug = ?`, causing 404 for integer IDs.
   - If `withCount('images')` is omitted from `index()`, `AdminPerformanceTest` N+1 detection will fail.
   - If `$project->load(['images' => ...])` is omitted from `edit()`, the edit view query budget test will fail.
   - If files are not stored on `'public'` disk in `projects/`, storage verification will fail.
