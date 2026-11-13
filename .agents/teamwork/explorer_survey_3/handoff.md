# Spec Miner Survey 3: Requirements & Endpoints Specification Mapping

## 1. Observation

Direct observations from codebase inspection, database migrations, model definitions, controllers, test suites, and build scripts:

1. **User Request & Acceptance Criteria**:
   `ORIGINAL_REQUEST.md` (lines 10–65) defines 6 core requirements:
   - **R1**: Admin Authentication & Route Protection (`/admin` access redirection to login, credentials auth, session management, logout).
   - **R2**: Project Management CRUD (title, slug, excerpt, description, client, location, year, area size, cover image, masterplan image, featured toggle, publication date, search, filter, deletion cascade).
   - **R3**: Project Gallery Management (multi-upload, captions, sort reorder, individual image deletion).
   - **R4**: Consultations & Inquiries Management (detail view, status filter: baru, dihubungi, diarsipkan, status update, delete).
   - **R5**: Dashboard Summary, Navigation, & Indonesian Localization (metrics, recent projects, recent inquiries, full Indonesian UI labels, buttons, badges, validation messages, and flash notifications).
   - **R6**: Performance & Build Optimization (Vite production build, code-splitting, lazy-loading for images and non-critical components, eager loading `with('images')` to eliminate N+1 queries).

2. **Routes & Controller Surface**:
   - `routes/web.php` (lines 19–32) currently defines only public routes:
     - `GET /` -> `HomeController@index`
     - `GET /projects` -> `ProjectController@index`
     - `GET /projects/{slug}` -> `ProjectController@show`
     - `GET /studio` -> `PageController@studio`
     - `GET /services` -> `PageController@services`
     - `GET /contact` -> `PageController@contact`
     - `POST /inquiries` -> `InquiryController@store`
     - `POST /contact` -> `InquiryController@store`
   - There are currently zero admin routes or admin controllers in `routes/web.php` or `app/Http/Controllers`.

3. **Database Schema & Eloquent Models**:
   - `database/migrations/2024_01_01_000001_create_projects_table.php` (lines 14–29):
     Columns: `id`, `title` (string), `slug` (string, unique), `excerpt` (text, nullable), `description` (longText), `client` (string, nullable), `location` (string), `year` (unsignedSmallInteger), `area_size` (string, 50), `cover_image` (string), `masterplan_image` (string, nullable), `is_featured` (boolean, index), `published_at` (timestamp, index, nullable), `timestamps`.
   - `database/migrations/2024_01_01_000002_create_project_images_table.php` (lines 14–21):
     Columns: `id`, `project_id` (foreignId constrained cascadeOnDelete), `image_path` (string), `caption` (string, nullable), `sort_order` (integer, default 0, index), `timestamps`.
   - `database/migrations/2024_01_01_000003_create_inquiries_table.php` (lines 14–25):
     Columns: `id`, `name` (string), `email` (string), `phone` (string, 50, nullable), `site_location` (string, nullable), `estimated_area` (string, 100, nullable), `budget_range` (string, 100, nullable), `message` (text), `status` (string, 50, default 'new', index), `timestamps`.
   - `database/migrations/0001_01_01_000000_create_users_table.php` (lines 14–37):
     Columns: `id`, `name`, `email` (unique), `password`, `remember_token`, `timestamps`. Sessions table also exists with `id`, `user_id`, `payload`, `last_activity`.
   - Model `App\Models\User` exists and extends `Authenticatable`.
   - Database currently contains `0` users (`User::count() == 0`). `DatabaseSeeder.php` seeds 6 projects and 2 inquiries, but no admin user.

4. **Middleware & Inertia Props Sharing**:
   - `app/Http/Middleware/HandleInertiaRequests.php` (lines 36–45) shares `appName` and `flash.success`, `flash.error`. It does NOT yet share `auth.user` (`$request->user()`).
   - `bootstrap/app.php` appends `HandleInertiaRequests::class` to the web middleware stack.

5. **Test Harness & Existing Regression Sensitivity**:
   - `tests/Feature/ChallengerM1_2_RouteContractTest.php` line 407 (`test_404_response_for_non_existent_system_routes`):
     ```php
     $bogusRoutes = ['/admin', '/dashboard', '/api/projects', '/portfolio', '/about-us'];
     foreach ($bogusRoutes as $route) {
         $response = $this->get($route);
         $this->assertSame(404, $response->getStatusCode(), "Expected 404 for route: {$route}");
     }
     ```
     This test in Milestone 1 explicitly asserted that `/admin` and `/dashboard` return 404. When `/admin` is implemented with auth protection, unauthenticated requests will return 302 redirecting to login. This existing test assertion must be updated to avoid false regression flags.
   - All 83 existing test cases pass (`php artisan test` succeeded with 83 passed, 711 assertions in 37.46s).

6. **Frontend Tooling & Asset Build**:
   - `package.json` includes React 18.2, Inertia React 1.2, Tailwind 3.4, Framer Motion 11.0, Lucide React 0.359.
   - `npm run build` succeeds (13 chunks generated in 7.89s).
   - Currently, no manual rollup chunk splitting is configured in `vite.config.js`.

---

## 2. Logic Chain

1. From **Observation 1 & 2**: The client requires a complete admin portal with authentication (R1), project CRUD (R2), gallery management (R3), inquiries management (R4), localized dashboard (R5), and optimized performance (R6). Because no admin endpoints currently exist in `routes/web.php`, a dedicated `/admin` route group with authentication middleware must be defined.
2. From **Observation 3**: The existing database schema already contains the necessary columns for `projects` (including `cover_image`, `masterplan_image`, `is_featured`, `published_at`, `area_size`), `project_images` (including `sort_order`, `caption`, and database-level `cascadeOnDelete`), and `inquiries` (including `site_location`, `estimated_area`, `budget_range`, `status`). Therefore, no destructive schema redesign is needed.
3. From **Observation 3**: `inquiries.status` has a default of `'new'` in the schema. In the original seed data, `'new'` and `'reviewed'` were used. R4 specifies the workflow: `baru` (`new`), `dihubungi` (`contacted`), `diarsipkan` (`archived`). To maintain compatibility while adhering to Indonesian localization, the status field must support both Indonesian keys and English legacy equivalents with seamless display mapping.
4. From **Observation 3**: Because `User::count() == 0`, admin authentication requires an admin user seeder (e.g. `admin@studiokode.com` / `password`) in `DatabaseSeeder.php` so administrators can log in immediately after setup.
5. From **Observation 4**: Admin navigation and conditional UI depend on current user session. `HandleInertiaRequests.php` must share `auth.user` (`id`, `name`, `email`) with Inertia views.
6. From **Observation 5**: In M1, `ChallengerM1_2_RouteContractTest` included `/admin` in a list of bogus 404 routes. Implementing R1 (protecting `/admin` with `auth` middleware) converts `/admin` into a valid route that returns 302 to `/admin/login` for guests. That assertion will need to remove `/admin` to reflect the new system contract.
7. From **Observation 6**: Code splitting in `vite.config.js` via `manualChunks` will split heavy vendor packages (`framer-motion`, `lucide-react`, `react`, `@inertiajs/react`) into separate cacheable chunks, satisfying R6.

---

## 3. Features Discovered

| # | Category | Feature | Description | Inputs | Outputs | Error Behavior | Discovered Via |
|---|----------|---------|-------------|--------|---------|----------------|----------------|
| 1 | Auth (R1) | Admin Login Page View | Displays localized admin login form | `GET /admin/login` | Inertia `Admin/Auth/Login` | Redirects to `/admin` if already authenticated | R1 / Acceptance Criteria |
| 2 | Auth (R1) | Admin Login Submission | Authenticates credentials and starts admin session | `POST /admin/login` (`email`, `password`, `remember`) | 302 redirect to `/admin` + session regeneration + Indonesian flash | 422 with Indonesian message "Kredensial yang diberikan tidak cocok dengan data kami." | R1 / Acceptance Criteria |
| 3 | Auth (R1) | Admin Logout | Terminates session and regenerates CSRF token | `POST /admin/logout` (auth required) | 302 redirect to `/admin/login` + Indonesian flash | 302 to login if unauthenticated | R1 / Acceptance Criteria |
| 4 | Auth (R1) | Admin Route Protection | Intercepts unauthorized access to `/admin/*` | Any unauthenticated request to `/admin/*` | 302 redirect to `/admin/login` | Redirect to login | R1 / Acceptance Criteria |
| 5 | Dashboard (R5) | Admin Dashboard Overview | Displays key metrics, recent projects, and recent inquiries | `GET /admin` or `GET /admin/dashboard` | Inertia `Admin/Dashboard` with `metrics`, `recentProjects`, `recentInquiries` | 302 redirect to login if unauthenticated | R5 / Acceptance Criteria |
| 6 | Projects (R2) | Projects Admin Index | Displays list of projects with search, filter, and pagination | `GET /admin/projects?search=&featured=&status=&sort=` | Inertia `Admin/Projects/Index` with `projects` collection | Defaults gracefully on empty/invalid queries | R2 / Acceptance Criteria |
| 7 | Projects (R2) | Project Create View | Form to create a new project with all attributes | `GET /admin/projects/create` | Inertia `Admin/Projects/Create` | 302 redirect to login if unauthenticated | R2 / Acceptance Criteria |
| 8 | Projects (R2) | Project Store | Stores new project with title, slug, excerpt, desc, client, location, year, area, cover, masterplan, featured, published_at | `POST /admin/projects` (multipart form) | 302 redirect to project list + Indonesian flash "Proyek baru berhasil ditambahkan." | 422 with Indonesian validation errors | R2 / Acceptance Criteria |
| 9 | Projects (R2) | Project Edit View | Form to edit existing project and manage gallery | `GET /admin/projects/{project}/edit` | Inertia `Admin/Projects/Edit` with project and eager-loaded images | 404 if project not found | R2 / Acceptance Criteria |
| 10 | Projects (R2) | Project Update | Updates project attributes, replaces cover/masterplan, toggles featured/published | `PUT/PATCH /admin/projects/{project}` | 302 redirect + Indonesian flash "Data proyek berhasil diperbarui." | 422 on validation failure; unique slug ignores current ID | R2 / Acceptance Criteria |
| 11 | Projects (R2) | Project Deletion | Deletes project and triggers DB foreign key cascade delete on gallery images | `DELETE /admin/projects/{project}` | 302 redirect to project list + Indonesian flash "Proyek berhasil dihapus." | 404 if project not found | R2 / Acceptance Criteria |
| 12 | Gallery (R3) | Gallery Multi-Upload | Uploads multiple images with captions for a project | `POST /admin/projects/{project}/images` (`images[]`, `captions[]`) | 302 redirect back + Indonesian flash "Foto galeri berhasil diunggah." | 422 if files invalid/oversized | R3 / Acceptance Criteria |
| 13 | Gallery (R3) | Gallery Caption & Sort Edit | Updates caption and/or sort order for an image | `PUT/PATCH /admin/images/{image}` (`caption`, `sort_order`) | 302 redirect back + Indonesian flash "Takarir foto berhasil diperbarui." | 422 on invalid input | R3 / Acceptance Criteria |
| 14 | Gallery (R3) | Gallery Batch Reorder | Updates sequential sort order of all images for a project | `POST /admin/projects/{project}/images/reorder` (`orders: [{id, sort_order}]`) | 302 redirect back or JSON + Indonesian flash "Urutan foto galeri berhasil diperbarui." | 422 if payload malformed | R3 / Acceptance Criteria |
| 15 | Gallery (R3) | Gallery Image Deletion | Deletes individual project image record and asset | `DELETE /admin/images/{image}` | 302 redirect back + Indonesian flash "Foto galeri berhasil dihapus." | 404 if image not found | R3 / Acceptance Criteria |
| 16 | Inquiries (R4) | Inquiries Admin Index | Lists consultation inquiries with status filter and search | `GET /admin/inquiries?status=&search=` | Inertia `Admin/Inquiries/Index` with `inquiries`, `filters`, `statusCounts` | Defaults gracefully on empty/invalid filter | R4 / Acceptance Criteria |
| 17 | Inquiries (R4) | Inquiry Detail View | Displays full message and contact details | `GET /admin/inquiries/{inquiry}` or modal | Inquiry details (name, email, phone, location, area, budget, message, status) | 404 if inquiry not found | R4 / Acceptance Criteria |
| 18 | Inquiries (R4) | Inquiry Status Update | Transitions status (baru -> dihubungi -> diarsipkan) | `PUT/PATCH /admin/inquiries/{inquiry}` (`status`) | 302 redirect back + Indonesian flash "Status pesan konsultasi berhasil diperbarui." | 422 if status not in allowed values | R4 / Acceptance Criteria |
| 19 | Inquiries (R4) | Inquiry Deletion | Deletes consultation inquiry record | `DELETE /admin/inquiries/{inquiry}` | 302 redirect to inquiry index + Indonesian flash "Pesan konsultasi berhasil dihapus." | 404 if inquiry not found | R4 / Acceptance Criteria |
| 20 | Localization (R5) | Full Indonesian UI & Flash | Complete Indonesian strings across navigation, forms, buttons, badges, errors, flash | Request handling across all admin routes | Indonesian UI text, labels, badges, validation messages, flash | 422 returns Indonesian validation errors | R5 / Acceptance Criteria |
| 21 | Performance (R6) | Vite Code-Splitting | Chunks vendor libraries (React, Inertia, Lucide, Motion) into separate files | `npm run build` | Optimized chunk bundles in `public/build/assets` | 0 build errors | R6 / Acceptance Criteria |
| 22 | Performance (R6) | Lazy-Loading & Layout Shift | Native `loading="lazy"`, `decoding="async"`, and aspect ratio containers | Public & Admin image elements | Fast initial load, zero Cumulative Layout Shift | Fallback to standard render | R6 / Acceptance Criteria |
| 23 | Performance (R6) | Database Eager Loading | `Project::with('images')` / `withCount('images')` on all queries | Eloquent ORM queries | Clean 1-2 SQL queries, zero N+1 loops | Fallback to lazy load if not specified | R6 / Acceptance Criteria |

---

## 4. Edge Cases

| # | Feature | Input | Observed / Expected Behavior |
|---|---------|-------|------------------------------|
| 1 | Auth: Unauthenticated Access | Unauthenticated `GET /admin` or `GET /admin/projects` | Returns HTTP 302 redirect to `/admin/login` |
| 2 | Auth: Authenticated Login Access | Authenticated user `GET /admin/login` | Returns HTTP 302 redirect to `/admin` (via `guest` middleware) |
| 3 | Auth: Bad Credentials | Invalid email or incorrect password | Returns 422 with Indonesian error message: "Kredensial yang diberikan tidak cocok dengan data kami." |
| 4 | Auth: Session Fixation | Valid credentials login | New session ID regenerated via `$request->session()->regenerate()`, preserving CSRF safety |
| 5 | Projects: Duplicate Slug on Create | Slug identical to an existing project slug | Returns 422 validation error: "Slug sudah digunakan, pilih slug lain." |
| 6 | Projects: Empty Slug on Create | Empty string for slug | System auto-generates slug from `title` via `Str::slug($title)` |
| 7 | Projects: Same Slug on Update | Updating project without changing its slug | Pass validation via `Rule::unique('projects', 'slug')->ignore($project->id)` |
| 8 | Projects: Update Without Image Replace | Updating project text without uploading new cover image | Retains existing `cover_image` and `masterplan_image` values cleanly |
| 9 | Projects: Cascade Delete | Deleting project with associated gallery images | MySQL foreign key `ON DELETE CASCADE` automatically deletes all child `project_images` |
| 10 | Projects: Search with Regex / SQL Chars | Search keyword: `100%`, `_sanctuary`, `'; DROP TABLE` | Safely escaped and parameterized; no SQL syntax error or injection |
| 11 | Gallery: Non-Image File Upload | Uploading `.pdf` or `.php` file in gallery upload | Returns 422 validation error: "Berkas harus berupa gambar (JPG, PNG, WEBP)." |
| 12 | Gallery: Oversized File Upload | Image file exceeding 5MB (5120 KB) | Returns 422 validation error: "Ukuran gambar maksimal 5 MB." |
| 13 | Gallery: Delete Non-Existent Image | `DELETE /admin/images/999999` | Returns HTTP 404 Not Found |
| 14 | Inquiries: Status Update Invalid Value | `PUT /admin/inquiries/1` with `status: 'spam'` | Returns 422 validation error: "Status yang dipilih tidak valid." |
| 15 | Inquiries: Dual Status Compatibility | Existing seeder uses `'new'`; user selects `'dihubungi'` | Both `'new'`/`'baru'`, `'contacted'`/`'dihubungi'`, `'archived'`/`'diarsipkan'` are accepted and normalized |
| 16 | Inquiries: Filter with No Matches | `GET /admin/inquiries?status=diarsipkan` when no archived inquiries exist | Returns empty collection without crash or 500 error; UI displays "Tidak ada pesan masuk." |
| 17 | Dashboard: Zero Projects or Inquiries | Fresh database before seeding or after clearing records | Dashboard displays `0` counts and displays clean empty placeholders |
| 18 | Performance: Rapid Navigation | Consecutive Inertia client-side page transitions | Framer Motion reveals animate smoothly without layout shifts or memory leaks |

---

## 5. Caveats

1. **Test Assertion Conflict on `/admin`**:
   In `tests/Feature/ChallengerM1_2_RouteContractTest.php` line 407, the test checks that `/admin` returns 404. In the upcoming implementation milestone, `/admin` becomes a valid protected route returning 302 (redirect to login) for unauthenticated visitors. The test array in `ChallengerM1_2_RouteContractTest` must have `/admin` removed so tests reflect the new specification.
2. **File Storage vs URL Support**:
   The initial seeder used Unsplash URLs for `cover_image` and `image_path`. When implementing uploads, the application should handle both remote URLs (starting with `http://` or `https://`) and uploaded local storage paths (e.g. `/storage/...` or `/uploads/...`) seamlessly in the UI. Running `php artisan storage:link` is recommended.
3. **No category table**:
   Per the architectural constraint established in M1 and verified by `SeederTest::test_database_seeder_enforces_category_omission`, the `projects` table strictly does NOT have a `category_id` or categories table. All projects are garden/landscape designs.
4. **Indonesian Localization Consistency**:
   The public frontend uses English for high-end international architectural presentation (Kyoto, Como, Comporta projects). The admin dashboard and administrative management interface are required to be completely localized in Bahasa Indonesia per R5.

---

## 6. Conclusion

1. **Specifications Complete**: Requirements R1 through R6 have been thoroughly analyzed and mapped to concrete database schemas, route endpoints, request validation payloads, and response contracts.
2. **Database Schema Readiness**: The existing database schema (`projects`, `project_images`, `inquiries`, `users`, `sessions`) already contains all necessary fields and cascade foreign key constraints. No migration alter operations are required.
3. **Seeder Requirement**: An admin user seed step must be added to `DatabaseSeeder.php` to supply default credentials (`admin@studiokode.com` / `password`).
4. **Endpoint Architecture**: All admin routes are cleanly isolated under `/admin/*` protected by `auth` middleware, preventing any collision with public portfolio routes.
5. **Quality Gate**: The existing test suite (83 tests) is 100% passing. A complementary Admin test suite (covering Auth, Project CRUD, Gallery, Inquiries, Dashboard, Localization, and Performance) will guarantee full coverage for all acceptance criteria.

---

## 7. Verification Method

To verify these findings and execute the verification gates:

1. **Verify Existing Tests**:
   ```powershell
   php artisan test
   ```
   *Expected*: 83 passed, 711 assertions, 0 failures.

2. **Verify Frontend Build**:
   ```powershell
   npm run build
   ```
   *Expected*: Clean Vite build output with manifest and asset bundles, exit code 0.

3. **Verify Database Schema & Models**:
   ```powershell
   php artisan tinker --execute="echo 'Projects: ' . App\Models\Project::count() . ', Images: ' . App\Models\ProjectImage::count() . ', Inquiries: ' . App\Models\Inquiry::count();"
   ```
   *Expected*: Valid Eloquent models responding with counts.

4. **Inspect Specification Artifacts**:
   Inspect `.agents/teamwork/explorer_survey_3/handoff.md` and `ORIGINAL_REQUEST.md`.
