# Handoff Report — Challenger M5.1 (White-box Controller, Route & Auth Hardening)

## 1. Observation

Direct white-box source inspections were conducted across:
- `app/Http/Controllers/Admin/AuthController.php`
- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Admin/InquiryController.php`
- `app/Http/Controllers/Admin/ProjectController.php`
- `app/Http/Controllers/Admin/ProjectImageController.php`
- `routes/web.php`
- `app/Models/Project.php`, `app/Models/ProjectImage.php`, `app/Models/Inquiry.php`, `app/Models/User.php`

Key specific code observations:
1. **AuthController session and token handling (`AuthController.php:55-62`)**:
   ```php
   Auth::logout();
   $request->session()->invalidate();
   $request->session()->regenerateToken();
   session()->flash('success', 'Anda telah berhasil keluar dari sesi admin.');
   return redirect('/admin/login');
   ```
   Direct observation: Session invalidation and CSRF token regeneration are explicitly executed upon logout.
2. **Email normalization in AuthController (`AuthController.php:28-35`)**:
   `$credentials = $request->validate(['email' => ['required', 'string', 'email'], ...]);`
   Email input is passed directly to `Auth::attempt($credentials)`. In SQLite testing (binary collation), mixed-case email (`Admin@StudioKode.com`) fails authentication unless matched exactly or lowered.
3. **Dashboard KPI single-query aggregation (`DashboardController.php:19-34`)**:
   Uses `selectRaw` with conditional SUMs. When the database contains zero projects or inquiries, SQL `SUM` yields `NULL`. Lines 36-40 cast with null coalescing: `(int) ($projectStats->total_projects ?? 0)`, preventing `TypeError`.
4. **Project validation boundaries (`ProjectController.php:79-101`)**:
   `'year' => ['required', 'integer', 'min:1900', 'max:2100']`
   `'area_size' => ['required', 'string', 'max:50']`
   `'title' => ['required', 'string', 'max:255']`
   `'location' => ['required', 'string', 'max:255']`
   `'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')]`
5. **Project file cleanup on update & destroy (`ProjectController.php:193-205, 237-251`)**:
   Deleting a project removes `cover_image`, `masterplan_image`, and child `project_images` from `Storage::disk('public')`. If a project uses `'projects/default-cover.jpg'`, `Storage::disk('public')->delete()` is invoked on that path if it exists on disk.
6. **Gallery reordering IDOR protection (`ProjectImageController.php:84-95`)**:
   ```php
   ProjectImage::where('id', $item['id'])
       ->where('project_id', $project->id)
       ->update(['sort_order' => (int) $item['sort_order']]);
   ```
   Scoping to `project_id` guarantees that cross-project image ID tampering fails to mutate images belonging to other projects.
7. **Adversarial Test Suite Execution**:
   - Command: `php artisan test tests/Feature/ChallengerM5_1_WhiteboxHardeningTest.php`
   - Output: `Tests: 43 passed (525 assertions), Duration: 3.56s`
8. **Full Regression Execution**:
   - Command: `php artisan test`
   - Output: `Tests: 361 passed (3651 assertions), Duration: 20.83s`

## 2. Logic Chain

1. **Auth & Session Invalidation (Dimension 1)**:
   - Observation: `destroy()` executes `Auth::logout()`, `session()->invalidate()`, and `session()->regenerateToken()`.
   - Test: `test_auth_logout_invalidates_session_and_regenerates_csrf_token` recorded the active session token before logout, posted logout, and verified that a subsequent request with that session ID was rejected with a 302 redirect to `/admin/login`.
   - Invariant verified: Post-logout session token reuse is impossible.

2. **Dashboard Resilience & Zero-State Aggregation (Dimension 2)**:
   - Observation: `Project::query()->selectRaw(...)` and `Inquiry::query()->selectRaw(...)` evaluate on empty tables.
   - Test: `test_dashboard_renders_cleanly_with_zero_records_in_database` wiped both tables and requested `/admin`. Verified all 5 metrics evaluated to integer `0` without SQL exception or PHP error.
   - Test: `test_dashboard_metrics_with_future_scheduled_and_draft_projects` proved that future scheduled projects (`published_at > now()`) are omitted from `published_projects`, and draft projects (`published_at = null`) are preserved in `total_projects`.

3. **Inquiries Workflow State Machine & Search Resilience (Dimension 3)**:
   - Observation: `InquiryController::index` filters using bilingual aliases (`baru`, `new`, `dihubungi`, `contacted`, `diarsipkan`, `archived`) and builds parameterized `like` conditions for search.
   - Test: `test_inquiries_search_with_regex_metacharacters_sql_wildcards_and_html` submitted `%`, `[Zone 4]`, `<sustainable>`, and `' OR 1=1 --`. Verified no SQL injection or unhandled regex crash occurred; matches were returned safely.
   - Test: `test_inquiry_status_update_complete_workflow_state_transitions` confirmed full cyclical transition (`baru` -> `dihubungi` -> `diarsipkan` -> `baru`) and rejection of invalid enum values (`test_inquiry_status_update_rejects_disallowed_enum_values`).

4. **Project Boundary Limits, Auto-Slug & Injection Handling (Dimension 4)**:
   - Observation: Validation enforces `year` (1900-2100) and `area_size` (max 50).
   - Test: `test_project_year_boundary_values` proved year 1900 and 2100 succeed, while 1899, 2101, 0, and negative values trigger HTTP 422 with validation errors.
   - Test: `test_project_area_size_boundary_lengths_and_formatting` proved 50 chars passes while 51 fails.
   - Test: `test_project_handles_extreme_unicode_emojis_and_html_injection_verbatim` confirmed `<script>alert("xss")</script> 🌿 庭園` persists safely without code execution.
   - Test: `test_project_creation_rejects_duplicate_slug_with_indonesian_message` and `test_project_update_allows_keeping_same_slug_but_rejects_collision_with_other_project` validated `Rule::unique` enforcement and error messaging.

5. **File Uploads, Cascade Purge & IDOR Isolation (Dimensions 5 & 6)**:
   - Observation: Cover image uploads must satisfy `image|max:10240`. Gallery images must satisfy `image|mimes:jpg,jpeg,png,webp|max:5120`.
   - Test: `test_project_file_upload_rejects_non_image_mimes_and_oversized_files` and `test_gallery_multi_upload_rejects_disallowed_mime_types` confirmed non-image and SVG/GIF rejections with Indonesian messages.
   - Test: `test_project_cascade_delete_cleans_up_all_storage_files_and_gallery_records` uploaded physical mock files for cover, masterplan, and 2 gallery assets. Upon project deletion, all DB rows and physical disk files were purged.
   - Test: `test_gallery_batch_reordering_prevents_idor_cross_project_tampering` attempted to reorder Image B belonging to Project B via Project A's endpoint. Verified Image B's `sort_order` was unchanged.

6. **Route Parity & Model Accessor (Dimension 7)**:
   - Observation: `/admin` and `/admin/dashboard` serve identical content; unsupported verbs must return 405.
   - Test: `test_admin_endpoints_enforce_proper_http_methods_and_reject_invalid_verbs` verified HTTP 405 on unsupported verbs.
   - Test: `test_project_area_numeric_accessor_resilience_across_edge_inputs` verified regex extraction on comma, dot, zero, text, null, and negative inputs.

## 3. Caveats

1. **Email Casing in SQLite vs MySQL**:
   `AuthController::store` does not call `strtolower()` on `$credentials['email']`. While MySQL's default collation (`utf8mb4_unicode_ci`) resolves case-insensitively, in SQLite test environments without `COLLATE NOCASE`, exact casing is required. Recommending adding `$credentials['email'] = strtolower(trim($credentials['email']))` as a defense-in-depth enhancement.
2. **Default Cover Deletion on Destroy**:
   In `ProjectController::destroy`, if a project has `cover_image === 'projects/default-cover.jpg'`, `Storage::disk('public')->delete($project->cover_image)` will delete the default cover file if one exists. A check `&& $project->cover_image !== 'projects/default-cover.jpg'` is recommended.
3. No production implementation files were altered, in strict compliance with the review-only constraint.

## 4. Conclusion

The StudioKode administrative backend controllers, routes, and models demonstrate high structural integrity and robust adversarial resilience:
- Auth routes properly invalidate sessions and regenerate tokens.
- Parameterized PDO queries eliminate SQL injection vulnerabilities.
- Route model bindings strictly scope operations and prevent cross-project IDOR in gallery reordering.
- Boundary conditions (year 1900-2100, area size 50 chars, title/location 255 chars) are enforced with clear Indonesian error messages.
- File uploads enforce mime filters and cascade-delete child storage files.
- The new adversarial hardening suite (`tests/Feature/ChallengerM5_1_WhiteboxHardeningTest.php`) contains 43 tests (525 assertions), all passing.
- Total application regression pass rate is 100% (361 tests, 3,651 assertions).

## 5. Verification Method

To independently verify all findings and test suites:

1. **Run the M5.1 Adversarial Hardening Suite**:
   ```powershell
   php artisan test tests/Feature/ChallengerM5_1_WhiteboxHardeningTest.php
   ```
   *Expected outcome*: 43 passed, 0 failures, 525 assertions.

2. **Run Full Application Test Regression**:
   ```powershell
   php artisan test
   ```
   *Expected outcome*: 361 passed, 0 failures, 3,651 assertions.

3. **Inspect the Test Implementation**:
   Examine `tests/Feature/ChallengerM5_1_WhiteboxHardeningTest.php` covering all 7 hardening dimensions.
