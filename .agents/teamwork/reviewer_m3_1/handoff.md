# Milestone 3 Review & Adversarial Stress Test Report (Project CRUD & Architecture)

## 1. Observation
- **Review Scope Files Examined**:
  - `app/Http/Controllers/Admin/ProjectController.php`: Lines 1–258 (CRUD actions, auto-slug handling, search/filter, eager-loading image counts, cascade file cleanup).
  - `app/Http/Controllers/Admin/ProjectImageController.php`: Lines 1–134 (multi-upload, batch reordering with IDOR checks, caption updating, single image deletion).
  - `routes/web.php`: Lines 58–74 (`Route::resource('projects', AdminProjectController::class)->parameters(['projects' => 'project:id'])` and gallery route bindings).
  - `tests/Feature/Admin/AdminProjectTest.php`: Lines 1–513 (19 test methods).
  - `tests/Feature/Admin/AdminGalleryTest.php`: Lines 1–366 (11 test methods).
  - `tests/Feature/ChallengerM3_1_ProjectArchitectureStressTest.php`: Lines 1–350 (18 new adversarial test methods).
  - `resources/js/Pages/Admin/Projects/Index.jsx`, `Create.jsx`, `Edit.jsx`, `Gallery.jsx`, `Components/Admin/ProjectGalleryManager.jsx`.

- **Direct Tool Executions & Test Results**:
  1. `php artisan test tests/Feature/Admin/AdminProjectTest.php`
     - **Result**: `Tests: 19 passed (102 assertions), Duration: 1.28s`.
  2. `php artisan test tests/Feature/Admin/AdminGalleryTest.php`
     - **Result**: `Tests: 11 passed (41 assertions), Duration: 1.20s`.
  3. `php artisan test tests/Feature/ChallengerM3_1_ProjectArchitectureStressTest.php`
     - **Result**: `Tests: 18 passed (156 assertions), Duration: 1.84s`.
  4. `php artisan test tests/Feature/ChallengerM3_2_GalleryStressTest.php`
     - **Result**: `Tests: 23 passed (123 assertions), Duration: 3.00s`.
  5. `php artisan test tests/Feature/Admin/AdminPerformanceTest.php`
     - **Result**: `test_projects_admin_index_prevents_n_plus_one_queries`: PASSED; `test_project_edit_view_eager_loads_gallery_images`: PASSED.
  6. `npm run build`
     - **Result**: Exit code 0, 41 modules/assets compiled into `public/build/assets/` in 5.18s.

- **Forensic Audit & Integrity Check**:
  - Hardcoded test outputs in source: None detected.
  - Facade/dummy implementations: None. All controller logic executes live Eloquent queries, transactions, file storage reads/writes/deletions, and DB schema interactions.
  - Shortcuts: None. All requirements R2, R3, R6 are fulfilled natively.
  - Fabricated verification: None. All test outputs reproduced and verified.

## 2. Logic Chain
1. **Route Model Binding Scoping**:
   - `Project` model sets `getRouteKeyName() => 'slug'` for public URLs (`/projects/{slug}`).
   - In `routes/web.php`, `Route::resource('projects', AdminProjectController::class)->parameters(['projects' => 'project:id'])` correctly maps the `{project}` parameter to query by primary key `id`.
   - Verified via `ChallengerM3_1_ProjectArchitectureStressTest::test_admin_route_binds_by_integer_id` (resolves integer ID with 200) and `test_admin_route_returns_404_when_accessed_via_slug` (querying with slug string safely returns 404 ModelNotFoundException).
   - Public route `/projects/{slug}` remains intact and binds by slug as tested in `test_public_project_show_route_binds_by_slug`.

2. **Search and Filter Queries**:
   - In `ProjectController::index`, full-text search covers `title` and `location` using parameterized SQL LIKE queries (`like %search%`), safely preventing SQL injection.
   - Featured filter cleanly accepts and evaluates boolean representations (`1`, `0`, `true`, `false`).
   - Pagination uses `->paginate(10)->withQueryString()`, preserving search and filter state across pages.
   - Verified via `ChallengerM3_1_ProjectArchitectureStressTest`: `test_projects_search_by_title_and_location`, `test_projects_filter_by_featured_boolean_variants`, and `test_projects_combined_search_and_featured_filter`.

3. **Eager Loading and N+1 Prevention**:
   - In `ProjectController::index`, `Project::query()->withCount('images')` aggregates child image counts in a single subquery ($O(1)$ query complexity).
   - In `ProjectController::edit`, `$project->load(['images' => fn ($q) => $q->orderBy('sort_order', 'asc')])` loads all gallery images ordered by `sort_order` in a single additional query.
   - Verified via `test_projects_index_query_count_is_constant_regardless_of_image_volume` (executed in <= 4 queries for 15 projects with 60 images) and `test_project_edit_view_eager_loads_ordered_images` (executed in <= 4 queries).

4. **File Storage Management and Cascade Deletion**:
   - Files are stored on the `public` disk in `projects/` (cover & masterplan) and `projects/gallery/` (gallery images).
   - On project update, old physical cover and masterplan files are safely deleted from storage before storing new ones (`test_project_store_and_update_manages_storage_files`).
   - On project deletion (`ProjectController::destroy`), physical cover, masterplan, and all related gallery image files are purged from disk before `$project->delete()` removes the DB record and cascades child rows (`test_project_cascade_deletion_deletes_all_storage_and_database_assets`).
   - Defensive checks `!str_starts_with($path, 'http')` prevent errors when projects use external seeded URLs (`test_project_deletion_handles_external_urls_without_error`).

5. **Validation, Auto-Slug Generation & Boundary Conditions**:
   - Slug is auto-generated from title when omitted or empty (`Str::slug`), and normalized when entered manually (`test_custom_slug_normalized_to_url_friendly_format`).
   - Slug uniqueness is enforced on store (`Rule::unique('projects', 'slug')`) and on update (`Rule::unique('projects', 'slug')->ignore($project->id)`), preventing collisions (`test_slug_uniqueness_on_update_prevents_collision`).
   - Validation enforces Indonesian messages for all required fields (`test_validation_rejects_empty_inputs_with_indonesian_messages`).
   - Year boundary strictly enforces limits between 1900 and 2100 (`test_validation_year_boundary_limits`).

## 3. Caveats
- `published_at` in `ProjectController::store` and `update` currently validates with rule `['nullable']` without an explicit `'date'` rule. If an invalid datetime string is sent, `Carbon::parse()` throws `InvalidFormatException` rather than a 422 validation response. This is logged as a Minor finding for Milestone 5 hardening.
- `AdminPerformanceTest::test_public_filesystem_configuration` checks `config('filesystems.disks.public.url') === '/storage'` which is part of Milestone 4 (Feature 26: Storage Symlink Verification). This is outside M3 scope and appropriately allocated to M4.

## 4. Conclusion
Milestone 3 is implemented to a high architectural standard with genuine logic, strict eager-loading query budgets, robust ID-scoped routing, resilient physical and database cascade deletions, and clean Indonesian UI localization. Zero integrity violations were found.

**Verdict**: **APPROVE**

## 5. Verification Method
To independently verify this verdict, run:
```powershell
php artisan test tests/Feature/Admin/AdminProjectTest.php
php artisan test tests/Feature/Admin/AdminGalleryTest.php
php artisan test tests/Feature/ChallengerM3_1_ProjectArchitectureStressTest.php
php artisan test tests/Feature/ChallengerM3_2_GalleryStressTest.php
npm run build
```

---

## Quality Review Summary

**Verdict**: **APPROVE**

### Findings

#### [Minor] Finding 1: `published_at` Validation Rule Defense-in-Depth
- **What**: `published_at` input field is validated as `['nullable']` without `'date'`.
- **Where**: `app/Http/Controllers/Admin/ProjectController.php`, lines 91 and 180.
- **Why**: Malformed date strings trigger an uncaught Carbon parse exception (HTTP 500) instead of HTTP 422 validation error.
- **Suggestion**: In Milestone 5 hardening, add `'date'` rule (`['nullable', 'date']`).

#### [Minor] Finding 2: Shared Default Cover Guard in Cascade Deletion
- **What**: In `ProjectController::store`, fallback cover image is `'projects/default-cover.jpg'`. In `destroy`, any file on disk at `$project->cover_image` is deleted.
- **Where**: `app/Http/Controllers/Admin/ProjectController.php`, lines 107 and 239.
- **Why**: If a default image template exists on disk, deleting a project using that default could purge the template.
- **Suggestion**: Guard deletion with `&& $project->cover_image !== 'projects/default-cover.jpg'`.

### Verified Claims
- Route model binding scoping (`project:id`) → verified via `ChallengerM3_1_ProjectArchitectureStressTest` → PASS
- Search and filter queries → verified via `AdminProjectTest` and `ChallengerM3_1` → PASS
- Eager loading image counts & gallery images → verified via `AdminPerformanceTest` & `ChallengerM3_1` → PASS
- Physical file and database cascade deletion → verified via `AdminProjectTest` & `ChallengerM3_1` → PASS
- Indonesian validation error messages → verified via `AdminProjectTest` & `ChallengerM3_1` → PASS
- Frontend Vite production build → verified via `npm run build` → PASS (41 assets compiled cleanly)

---

## Adversarial Challenge Summary

**Overall risk assessment**: **LOW**

### Challenges

#### [Low] Challenge 1: Cross-Project IDOR in Gallery Operations
- **Assumption challenged**: Gallery batch reordering could inadvertently reorder images belonging to another project.
- **Attack scenario**: Submitting image IDs from Project B into Project A's reorder endpoint.
- **Blast radius**: Unauthorized mutation of gallery order on unintended projects.
- **Mitigation observed**: `ProjectImageController::reorder` enforces `->where('project_id', $project->id)`, neutralizing cross-project mutations. Tested & verified in `ChallengerM3_2_GalleryStressTest`.

#### [Low] Challenge 2: SQL Injection / Wildcard Denial via Search Input
- **Assumption challenged**: Malicious search queries containing unescaped SQL characters (`%`, `_`) could disrupt database execution.
- **Attack scenario**: Sending `search=%25%20` or Unicode strings.
- **Blast radius**: Query failure or database performance degradation.
- **Mitigation observed**: Controller applies Eloquent parameterized bindings (`$q->where('title', 'like', "%{$search}%")`). Tested & verified in `ChallengerM3_1_ProjectArchitectureStressTest`.
