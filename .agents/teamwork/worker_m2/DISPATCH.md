# Dispatch: Worker M2 (Milestone 2 — admin_dashboard_inquiries Implementation)

## Scope & Objective
Implement Milestone 2 (`admin_dashboard_inquiries`) covering Features 8 through 15 in `PROJECT.md`:
1. `app/Http/Controllers/Admin/DashboardController.php`:
   - `index()`:
     - Aggregates KPI metrics:
       - `total_projects`: `Project::count()`
       - `featured_projects`: `Project::featured()->count()`
       - `published_projects`: `Project::published()->count()`
       - `total_inquiries`: `Inquiry::count()`
       - `new_inquiries`: `Inquiry::whereIn('status', ['new', 'baru'])->count()`
     - Recent items:
       - `recent_projects`: `Project::latest()->take(5)->get(['id', 'title', 'slug', 'location', 'is_featured', 'published_at', 'created_at'])`
       - `recent_inquiries`: `Inquiry::latest()->take(5)->get(['id', 'name', 'email', 'site_location', 'status', 'created_at'])`
     - Renders `Admin/Dashboard/Index` with `metrics`, `recent_projects`, and `recent_inquiries` Inertia props.
2. `app/Http/Controllers/Admin/InquiryController.php`:
   - `index(Request $request)`:
     - Filtering by status (`baru`/`new`, `dihubungi`/`contacted`, `diarsipkan`/`archived`). Supports Indonesian and English status aliases seamlessly.
     - Search by sender name, email, or site location.
     - Orders by latest (`latest()`), paginates or lists inquiries.
     - Status counts: counts for `all`, `baru`, `dihubungi`, `diarsipkan`.
     - Renders `Admin/Inquiries/Index` with `inquiries`, `filters`, and `status_counts`.
   - `show(Inquiry $inquiry)`:
     - Renders `Admin/Inquiries/Show` with full attributes (`name`, `email`, `phone`, `site_location`, `estimated_area`, `budget_range`, `message`, `status`, `created_at`).
   - `update(Request $request, Inquiry $inquiry)`:
     - Validates status in `['new', 'baru', 'contacted', 'dihubungi', 'archived', 'diarsipkan']`.
     - Normalizes status and updates inquiry.
     - Sets Indonesian flash message: `session()->flash('success', 'Status pesan konsultasi berhasil diperbarui.');`
     - Returns redirect back or to inquiry show.
   - `destroy(Inquiry $inquiry)`:
     - Deletes inquiry record.
     - Sets Indonesian flash: `session()->flash('success', 'Pesan konsultasi berhasil dihapus.');`
     - Redirects to `/admin/inquiries`.
3. `routes/web.php`:
   - Inside protected `Route::prefix('admin')->name('admin.')->middleware(['auth'])`:
     - Bind `DashboardController@index` to `/admin` (`name('dashboard')`) and `/admin/dashboard`.
     - Bind `InquiryController`:
       - `GET /admin/inquiries` -> `InquiryController@index` (`name('inquiries.index')`)
       - `GET /admin/inquiries/{inquiry}` -> `InquiryController@show` (`name('inquiries.show')`)
       - `match(['put', 'patch'], '/admin/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');`
       - `DELETE /admin/inquiries/{inquiry}` -> `InquiryController@destroy` (`name('inquiries.destroy')`)
4. `resources/js/lib/id.js`:
   - Update and enrich Indonesian localization dictionary:
     - In `nav`: Ensure `inquiries: 'Pesan Konsultasi'`, `dashboard: 'Dasbor'`, `projects: 'Manajemen Proyek'`, `view_site: 'Lihat Website'`, `logout: 'Keluar'`.
     - In `status`: `'baru': 'Baru'`, `'new': 'Baru'`, `'dihubungi': 'Dihubungi'`, `'contacted': 'Dihubungi'`, `'diarsipkan': 'Diarsipkan'`, `'archived': 'Diarsipkan'`, `'unggulan': 'Unggulan'`, `'featured': 'Unggulan'`, `'terbit': 'Terbit'`, `'draf': 'Draf'`.
     - In `dashboard`: Add metric card labels (*Total Proyek*, *Proyek Unggulan*, *Pesan Masuk Baru*, *Total Pesan Konsultasi*), table headers, and empty states.
5. `resources/js/Layouts/AdminLayout.jsx`:
   - Admin Layout container in StudioKode botanical theme (`#F9F8F6` background, `#1C2A21` header/sidebar elements, `#E6E2DD` borders).
   - Sidebar/Topbar navigation with Indonesian links: *Dasbor*, *Manajemen Proyek*, *Pesan Konsultasi*, *Lihat Website*.
   - User profile badge displaying `auth.user.name` and Logout form action submitting `POST /admin/logout`.
   - Global flash alert banner displaying `flash.success` and `flash.error`.
   - Responsive design with mobile navigation toggle.
6. `resources/js/Pages/Admin/Dashboard/Index.jsx`:
   - Wrapped in `AdminLayout`.
   - 4 KPI metric cards (*Total Proyek*, *Proyek Unggulan*, *Pesan Masuk Baru*, *Total Pesan Konsultasi*) styled with Cormorant Garamond numerals.
   - Quick tables for 5 Recent Projects and 5 Recent Inquiries with status badges and quick action links.
7. `resources/js/Pages/Admin/Inquiries/Index.jsx`:
   - Wrapped in `AdminLayout`.
   - Status filter tabs (*Semua*, *Baru*, *Dihubungi*, *Diarsipkan*) with badge counts.
   - Search input for client name, email, or site location.
   - Tabular list of inquiries showing Date, Client Name, Email, Location, Status badge, and Actions (*Lihat Detail*, *Hapus*).
   - Empty placeholder when no inquiries match filter.
8. `resources/js/Pages/Admin/Inquiries/Show.jsx`:
   - Wrapped in `AdminLayout`.
   - Client details card: Name, Email (clickable `mailto:`), Phone (clickable `tel:`), Site Location, Estimated Area Size, Budget Range, Date received.
   - Client Message block.
   - Quick Status update dropdown / buttons (*Tandai Dihubungi*, *Arsipkan*) submitting `PATCH /admin/inquiries/{inquiry}`.
   - Delete button with confirmation modal/prompt.

## Reference Materials
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md`
- `c:\laragon\www\StudioKode\PROJECT.md`
- `c:\laragon\www\StudioKode\TEST_INFRA.md`
- `c:\laragon\www\StudioKode\TEST_READY.md`
- `c:\laragon\www\StudioKode\tests\Feature/Admin/AdminDashboardTest.php`
- `c:\laragon\www\StudioKode\tests\Feature/Admin/AdminInquiryTest.php`

## Verification Requirements
Worker MUST execute and document passing results for:
1. `php artisan test tests/Feature/Admin/AdminDashboardTest.php`
2. `php artisan test tests/Feature/Admin/AdminInquiryTest.php`
3. `php artisan test tests/Feature/Admin/AdminAuthTest.php`
4. `npm run build`

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Write your report to `c:\laragon\www\StudioKode\.agents\teamwork\worker_m2\handoff.md`.


## 2026-11-09T12:19:12Z
You are Worker M2 implementing Milestone 2 (admin_dashboard_inquiries) for StudioKode at c:\laragon\www\StudioKode.

MANDATORY FIRST STEP:
Read c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md, c:\laragon\www\StudioKode\PROJECT.md, and c:\laragon\www\StudioKode\.agents\teamwork\worker_m2\DISPATCH.md before touching any code.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

State folder: c:\laragon\www\StudioKode\.agents\teamwork\worker_m2
Working directory: c:\laragon\www\StudioKode

Your assignment:
1. Implement `app/Http/Controllers/Admin/DashboardController.php` (KPI metrics, recent projects, recent inquiries).
2. Implement `app/Http/Controllers/Admin/InquiryController.php` (filtering, search, pagination, show, update status with Indonesian flash, delete).
3. Bind routes in `routes/web.php` for Dashboard and Inquiries.
4. Update `resources/js/lib/id.js` for complete Indonesian dictionary (including 'Pesan Konsultasi', metric labels).
5. Implement `resources/js/Layouts/AdminLayout.jsx` with botanical theme, Indonesian navigation, user profile, logout action, flash alerts.
6. Implement `resources/js/Pages/Admin/Dashboard/Index.jsx` with KPI cards, recent projects and recent inquiries tables.
7. Implement `resources/js/Pages/Admin/Inquiries/Index.jsx` with status tabs, search, inquiry table, actions.
8. Implement `resources/js/Pages/Admin/Inquiries/Show.jsx` with client details, message, status toggle, deletion.
9. Execute and document test verifications:
   - `php artisan test tests/Feature/Admin/AdminDashboardTest.php`
   - `php artisan test tests/Feature/Admin/AdminInquiryTest.php`
   - `php artisan test tests/Feature/Admin/AdminAuthTest.php`
   - `npm run build`
10. Write your structured handoff report to `c:\laragon\www\StudioKode\.agents\teamwork\worker_m2\handoff.md`.

Send a message when your handoff is ready.
