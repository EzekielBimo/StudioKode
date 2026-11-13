# StudioKode Test Infrastructure & Comprehensive 4-Tier Test Matrix

**Document Version**: 3.0.0  
**Date**: 2026-11-10  
**Target System**: StudioKode — Architectural Landscape Portfolio & Admin Suite (Smoked Obsidian / Dark Moss Glass Theme Transformation)  
**Stack**: Laravel 11.x, Inertia.js (React 18), MySQL 8.4 (Dev/Prod), SQLite in-memory (Test), Tailwind CSS 3.4, Framer Motion 11, Vite 5  
**Runtime**: PHP 8.3.30 (Win64), Node.js v24.15.0  

---

## 1. Test Philosophy & Quality Strategy

The StudioKode automated test harness integrates rigorous backend contract verification with frontend build, asset integrity auditing, and automated visual token and layout preservation testing. Tailored specifically for the **"Smoked Obsidian / Dark Moss Glass"** theme transformation (adhering to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` principles), the testing strategy employs a **4-tier test case hierarchy**:

1. **Tier 1: Surface Token Coverage & Theme Foundations**
   - Verifies the physical presence of Smoked Obsidian base tokens (`#0B0E0C`, `#0E1310`, `#121814`), translucent dark moss glass (`rgba(20, 32, 24, 0.65)` / `bg-[#142018]/65` with `backdrop-blur-md`), frosted glass borders (`border-white/10`, `border-emerald-500/15`), luminous moss/gold accents (`#34D399`, `#22C55E`, `#1C3024`, `#C2A649`), and high-contrast editorial typography (`#F4F6F0`, `#A7ACA0`).
   - Asserts `<meta name="theme-color" content="#0B0E0C">` in `resources/views/app.blade.php`, global CSS `:selection` styles (`#1C3024` / `#F4F6F0`), and dark canvas body classes (`bg-[#0B0E0C] text-[#F4F6F0]`).

2. **Tier 2: Boundary Value Analysis, Structural Layout Preservation & Test Assertion Contracts**
   - Asserts zero alterations to structural layout grids, flex containers, responsive max-widths, and padding tokens across all 14 page views and shared components (`max-w-7xl`, `min-h-screen`, `grid-cols-*`, `flex flex-col md:flex-row`).
   - Asserts strict preservation of legacy test tokens in `AdminLayout.jsx` (`#1C2A21`, `#F9F8F6`, `#E6E2DD`, and `aria-label=`) and anti-CLS constraints in `ProjectGalleryManager.jsx` (`aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.Group`, `Reorder.Item`) to guarantee 100% backward compatibility with `ChallengerM5_2_FrontendLocalizationStressTest`.
   - Asserts consumption of Indonesian localization dictionary bindings (`id.*`) across all administrative components.

3. **Tier 3: State Transitions, Interactive Surfaces & Contrast Conformance**
   - Audits interactive hover states (`hover:border-emerald-500/30`, `hover:text-emerald-300`), active navigation indicator underlines with emerald glow shadows, focus rings (`focus:ring-emerald-500/50`), and modal dialog backdrop blur.
   - Enforces WCAG AAA/AA color contrast compliance: primary cream `#F4F6F0` on obsidian base `#0B0E0C` (>15:1 contrast ratio, exceeding WCAG AAA 7:1) and secondary sage stone `#A7ACA0` on obsidian (>6:1 contrast ratio, exceeding WCAG AA 4.5:1).

4. **Tier 4: End-to-End Application Workload, Cross-Device Viewports & Asset Compilation**
   - Verifies end-to-end user workflows from public portfolio exploration to authenticated admin CRUD operations and gallery reordering.
   - Validates Vite production asset compilation (`npm run build`) ensuring zero errors, code-split dynamic page chunks, and vendor isolation chunks (`vendor-react`, `vendor-inertia`, `vendor-motion`, `vendor-lucide`).
   - Validates responsive presentation across mobile (320px–480px), tablet (768px), and desktop (1024px–1920px) viewports with zero horizontal overflow.
   - Guarantees 100% clean execution across all 401 tests in `php artisan test`.

---

## 2. Complete Feature Inventory (32 Features)

All 32 features specified in `PROJECT.md` are mapped to their respective milestones, requirements, test suites, and tier coverage:

| # | Feature Name | Milestone | Req | Target Test Class | Tiers Covered | Contract / Assertion Focus |
|---|--------------|-----------|-----|-------------------|---------------|----------------------------|
| 1 | Root Template Dark Obsidian & Meta Theme-Color | M1 | R1, R2 | `ThemeTransformationTest.php` | Tier 1, 4 | `<meta name="theme-color" content="#0B0E0C">`, body `bg-[#0B0E0C] text-[#F4F6F0]`, selection |
| 2 | Tailwind & CSS Design Tokens | M1 | R1 | `ThemeTransformationTest.php` | Tier 1, 3 | Obsidian & moss color palette, Cormorant Garamond & Plus Jakarta Sans fonts |
| 3 | Public Layout Shell & Toasts | M1 | R2 | `ThemeTransformationTest.php` | Tier 1, 2 | Root `bg-[#0B0E0C]`, moss glass toast alerts, structural flex preservation |
| 4 | Public Navbar Glass Header | M1 | R2 | `ThemeTransformationTest.php` | Tier 1, 3 | Translucent dark moss glass header, `backdrop-blur`, frosted border, active indicator |
| 5 | Mobile Menu Drawer Glass | M1 | R2 | `ThemeTransformationTest.php` | Tier 1, 2 | Translucent dark moss drawer `bg-[#0E1310]`, frosted border `border-white/10` |
| 6 | Footer Obsidian Surface | M1 | R2 | `ThemeTransformationTest.php` | Tier 1, 2 | Deep obsidian base `#080B09` / `#0B0E0C`, high-contrast cream typography |
| 7 | Admin Layout Shell & Sidebar | M1 | R1, R2 | `ThemeTransformationTest.php`, `ChallengerM5_2` | Tier 1, 2 | Obsidian sidebar & drawer while strictly preserving `#1C2A21`, `#F9F8F6`, `#E6E2DD` |
| 8 | UI Primitive Button | M2 | R1, R2 | `ThemeTransformationTest.php`, `FrontendBuildTest` | Tier 1, 3 | Moss emerald primary, elevated dark secondary, frosted borders, focus rings |
| 9 | UI Primitive Badge | M2 | R1, R2 | `ThemeTransformationTest.php`, `FrontendBuildTest` | Tier 1, 3 | Foliage, neutral, outline variants with frosted borders `border-white/10` |
| 10 | UI Primitive Input | M2 | R1, R2 | `ThemeTransformationTest.php` | Tier 1, 3 | Elevated dark surface `#121814`, emerald focus ring, cream text |
| 11 | UI Primitive Modal | M2 | R1, R2 | `ThemeTransformationTest.php`, `FrontendBuildTest` | Tier 1, 3 | Dark glass backdrop blur, obsidian panel `bg-[#0E1310]`, frosted border |
| 12 | Portfolio ProjectCard | M2 | R1, R2 | `ThemeTransformationTest.php`, `AdminPerformanceTest` | Tier 1, 2 | Aspect ratio `aspect-[16/10]`, anti-CLS container, cream title, sage meta |
| 13 | Portfolio FilterBar | M2 | R1, R2 | `ThemeTransformationTest.php`, `FrontendBuildTest` | Tier 1, 2 | Dark moss inputs, category omission verified, sorting preserved |
| 14 | Portfolio ImageGallery | M2 | R1, R2 | `ThemeTransformationTest.php`, `AdminPerformanceTest` | Tier 1, 2 | Aspect ratio `aspect-[4/3]`, dark frames `bg-[#121814]`, obsidian lightbox |
| 15 | Public Home Page | M2 | R2, R3 | `ThemeTransformationTest.php`, `RouteTest` | Tier 1, 2, 4 | `max-w-7xl`, `grid-cols-2 md:grid-cols-4`, `grid-cols-1 md:grid-cols-3` |
| 16 | Public Projects Index Page | M2 | R2, R3 | `ThemeTransformationTest.php`, `RouteTest` | Tier 1, 2, 4 | `max-w-7xl`, `grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8` |
| 17 | Public Project Show Page | M2 | R2, R3 | `ThemeTransformationTest.php`, `RouteTest` | Tier 1, 2, 4 | `max-w-7xl`, metadata grid `grid-cols-2 md:grid-cols-4`, cover `aspect-[21/9]` |
| 18 | Public Studio Page | M2 | R2, R3 | `ThemeTransformationTest.php`, `RouteTest` | Tier 1, 2, 4 | `max-w-7xl`, philosophy `grid-cols-1 md:grid-cols-2`, team `aspect-[4/5]` |
| 19 | Public Services Page | M2 | R2, R3 | `ThemeTransformationTest.php`, `RouteTest` | Tier 1, 2, 4 | `max-w-7xl`, 12-col grid `grid-cols-1 lg:grid-cols-12 gap-8` |
| 20 | Public Contact Page | M2 | R2, R3 | `ThemeTransformationTest.php`, `RouteTest` | Tier 1, 2, 4 | `max-w-7xl`, 12-col grid `grid-cols-1 lg:grid-cols-12 gap-16`, form inputs |
| 21 | Admin Auth Login View | M3 | R2 | `ThemeTransformationTest.php`, `AdminAuthTest` | Tier 1, 2, 3 | Obsidian card, high-contrast inputs, `id.auth.*` dictionary consumption |
| 22 | Admin Dashboard Index | M3 | R2 | `ThemeTransformationTest.php`, `AdminDashboardTest` | Tier 1, 2 | `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`, `grid-cols-1 lg:grid-cols-2`, tables |
| 23 | Admin Projects Index | M3 | R2 | `ThemeTransformationTest.php`, `AdminProjectTest` | Tier 1, 2 | Table wrapper `overflow-x-auto`, filter controls, pagination, `id.projects.*` |
| 24 | Admin Projects Create View | M3 | R2 | `ThemeTransformationTest.php`, `AdminProjectTest` | Tier 1, 2 | `grid-cols-1 lg:grid-cols-3 gap-8`, dropzones, form inputs, `id.projects.*` |
| 25 | Admin Projects Edit View | M3 | R2 | `ThemeTransformationTest.php`, `AdminProjectTest` | Tier 1, 2 | `grid-cols-1 lg:grid-cols-3 gap-8`, eager loaded gallery, tab panels |
| 26 | Admin Projects Gallery Page | M3 | R2 | `ThemeTransformationTest.php`, `AdminGalleryTest` | Tier 1, 2 | Header flex alignment, ProjectGalleryManager container wrapper |
| 27 | Admin Project Gallery Manager | M3 | R2 | `ThemeTransformationTest.php`, `ChallengerM5_2` | Tier 1, 2 | Preserves `aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.*` |
| 28 | Admin Inquiries Index | M3 | R2 | `ThemeTransformationTest.php`, `AdminInquiryTest` | Tier 1, 2 | Status filter tabs, inquiries data table `<table`, `overflow-x-auto` |
| 29 | Admin Inquiries Show View | M3 | R2 | `ThemeTransformationTest.php`, `AdminInquiryTest` | Tier 1, 2 | `grid-cols-1 lg:grid-cols-3 gap-8`, client details card, status workflow |
| 30 | Full Automated Test Verification | M4 | R3 | Full Test Suite (`php artisan test`) | Tiers 1-4 | 100% pass across all 401 tests (4,300 assertions) in 26 test classes |
| 31 | Production Asset Build Verification | M4 | R3 | `ChallengerM4_2_AssetBuildStressTest.php` | Tier 1, 4 | `npm run build` succeeds cleanly, 27 code-split chunks, valid manifest |
| 32 | Multi-Viewport Responsive & Visual Quality Verification | M4 | R3 | `ThemeTransformationTest.php`, Build & AST | Tier 2, 3, 4 | Zero horizontal overflow across 320px–1920px, WCAG contrast > 15:1 |

---

## 3. Test Architecture & Runner Infrastructure

### 3.1 Directory Layout
```
c:\laragon\www\StudioKode\
├── tests/
│   ├── TestCase.php                                     # Base PHPUnit test configuration
│   ├── Feature/
│   │   ├── ThemeTransformationTest.php                  # Authoritative 12-Method Theme Transformation Suite
│   │   ├── Admin/                                       # Dedicated Admin Dashboard E2E Test Suite
│   │   │   ├── AdminAuthTest.php                        # R1: Auth, Login, Logout, Route Protection
│   │   │   ├── AdminDashboardTest.php                   # R5: Metrics, Recent Lists, Indonesian UI
│   │   │   ├── AdminProjectTest.php                     # R2: Projects CRUD, Search, Filter, Cascade
│   │   │   ├── AdminGalleryTest.php                     # R3: Gallery Multi-Upload, Sort, Captions
│   │   │   ├── AdminInquiryTest.php                     # R4: Inquiries Filter, Details, Status, Delete
│   │   │   └── AdminPerformanceTest.php                 # R6: Eager Loading, Query Budgets, Build
│   │   ├── RouteTest.php                                # Public route contracts
│   │   ├── InquiryTest.php                              # Public inquiry submission contracts
│   │   ├── SeederTest.php                               # Public database seeder verification
│   │   ├── ProjectFilterTest.php                        # Public portfolio search & sorting
│   │   ├── FrontendBuildTest.php                        # Public frontend asset contracts
│   │   ├── ChallengerM1Test.php                         # M1 Challenger regression checks
│   │   ├── ChallengerM1_2_RouteContractTest.php
│   │   ├── ChallengerM2_1_InquiryValidationStressTest.php
│   │   ├── ChallengerM2_2_InquiryStatusWorkflowStressTest.php
│   │   ├── ChallengerM3_1_ProjectArchitectureStressTest.php
│   │   ├── ChallengerM3_1_ProjectStressTest.php
│   │   ├── ChallengerM3_2_GalleryReorderStressTest.php
│   │   ├── ChallengerM3_2_GalleryStressTest.php
│   │   ├── ChallengerM4_1_QueryBudgetStressTest.php
│   │   ├── ChallengerM4_2_AssetBuildStressTest.php      # Vite manifest & vendor code splitting audit
│   │   ├── ChallengerM5_1_SecurityRegressionStressTest.php
│   │   ├── ChallengerM5_2_FrontendLocalizationStressTest.php # Botanical tokens & anti-CLS contracts
│   │   └── ChallengerMySQLTest.php
│   └── Unit/
│       ├── AreaSizeParserTest.php
│       └── ProjectModelTest.php
├── phpunit.xml                                          # SQLite :memory: configuration
├── TEST_INFRA.md                                        # Complete test matrix & architecture (v3.0.0)
└── TEST_READY.md                                        # E2E Test Suite Readiness Declaration (v3.0.0)
```

### 3.2 Authoritative Test Runner Commands

#### 1. Execute Theme Transformation Test Suite
```powershell
php artisan test tests/Feature/ThemeTransformationTest.php
```
*Expected Result*: 12 passed tests (202 assertions) in ~2s.

#### 2. Execute Full Project Test Suite
```powershell
if (Test-Path storage\framework\testing\disks) { Remove-Item -Recurse -Force storage\framework\testing\disks }
php artisan test
```
*Expected Result*: 401 passed tests (4,300 assertions) in ~28s with zero failures.

#### 3. Execute Production Asset Build Verification
```powershell
npm run build
```
*Expected Result*: Exits with code 0, compiles ~2013 modules into 27 optimized chunks in `public/build/`.

#### 4. Execute Build & Asset Stress Suite
```powershell
php artisan test tests/Feature/ChallengerM4_2_AssetBuildStressTest.php
```
*Expected Result*: 14 passed assertions validating manifest JSON integrity, code splitting, dynamic entries, and vendor chunks.

#### 5. Execute Frontend Localization & Anti-CLS Preservation Suite
```powershell
php artisan test tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php
```
*Expected Result*: 28 passed assertions validating `#1C2A21`, `#F9F8F6`, `#E6E2DD` in `AdminLayout.jsx` and `aspect-[4/3]` in `ProjectGalleryManager.jsx`.

---

## 4. Real-World Application Workload Scenarios (Tier 4)

### Scenario 4.1: Admin Authentication, Protected Navigation & Session Termination
1. **Unauthenticated Probe**: Guest attempts direct `GET /admin`, `GET /admin/projects`, `GET /admin/inquiries`.
2. **Redirection Contract**: Intercepted by `auth` middleware; returned HTTP 302 redirecting to `/admin/login`.
3. **Login Form Display**: Guest visits `GET /admin/login`, received HTTP 200 with Inertia component `Admin/Auth/Login` in Bahasa Indonesia.
4. **Credential Authentication**: Guest submits valid admin credentials (`admin@studiokode.com` / `password`).
5. **Session Regeneration**: Session ID is refreshed (mitigating session fixation), user is authenticated, and redirected to `/admin`.
6. **Protected Access**: Admin accesses `/admin/dashboard` and `/admin/projects` with HTTP 200.
7. **Logout**: Admin submits `POST /admin/logout`, session is invalidated, and user is redirected to `/admin/login`. Subsequent request to `/admin` returns HTTP 302.

### Scenario 4.2: Full Project Creation, Modification, and Cascading Deletion Lifecycle
1. **Project Creation**: Authenticated admin submits `POST /admin/projects` with full payload:
   - Title: *"Taman Bambu Sastra"*, Excerpt: *"Zen bamboo courtyard"*, Location: *"Ubud, Bali"*, Year: 2024, Area: *"750 m²"*, Featured: `true`, Published: `now()`, Cover Image: `UploadedFile::fake()->image('cover.jpg')`, Masterplan: `UploadedFile::fake()->image('masterplan.jpg')`.
2. **Persistence Verification**: Project record is persisted with slug auto-generated as `taman-bambu-sastra`. Cover and masterplan files stored to public disk.
3. **Gallery Association**: Admin uploads 3 gallery images to `POST /admin/projects/{project}/images`. All 3 rows exist in `project_images` with consecutive `sort_order` (1, 2, 3).
4. **Project Modification**: Admin submits `PUT /admin/projects/{project}` modifying title to *"Taman Bambu Sastra Retreat"* and updating area size to *"820 m²"*, while leaving file uploads blank.
5. **Preservation Verification**: Text attributes update, existing cover and masterplan file paths are preserved without data loss.
6. **Cascading Deletion**: Admin submits `DELETE /admin/projects/{project}`.
7. **Integrity Contract**: The project record is removed from `projects`, and database foreign key `ON DELETE CASCADE` automatically purges all 3 child records from `project_images`.

### Scenario 4.3: Project Gallery Multi-Upload, Captioning, and Drag-and-Drop Reordering Lifecycle
1. **Initial Upload**: Admin uploads 4 architectural photograph assets with distinct captions to a project.
2. **Sequential Verification**: Images are assigned ascending `sort_order` values (1, 2, 3, 4).
3. **Reordering Payload**: Admin simulates Framer Motion drag-and-drop reordering, posting new sequence: `[4, 2, 1, 3]`.
4. **Reorder Verification**: Database updates `sort_order` values so image #4 has order 1, image #2 has order 2, etc. Subsequent queries order images by `sort_order ASC`.
5. **Caption Update**: Admin updates caption for image #4 to *"Detail Gerbang Masuk Bambu Hitam"*. Value updates in database.
6. **Single Item Deletion**: Admin deletes image #2. Only image #2 is removed; remaining images remain intact and ordered.

### Scenario 4.4: Client Inquiry Receipt, Admin Triage, Status Progression, and Archival
1. **Client Submission**: Public visitor submits consultation form via `POST /inquiries` with details (Name: *"Budi Pratama"*, Email: *"budi@example.com"*, Status: `'baru'`).
2. **Admin Inquiry Browser**: Admin views `GET /admin/inquiries?status=baru`. Budi's inquiry appears in the list.
3. **Detail Inspection**: Admin accesses `GET /admin/inquiries/{id}` to review site location, estimated area, budget range, and message.
4. **Status Progression**:
   - Admin contacts client and submits `PATCH /admin/inquiries/{id}` setting status to `'dihubungi'`.
   - Admin filters `?status=dihubungi`; inquiry appears. Filter `?status=baru` now omits it.
5. **Archival**: Consultation completes; admin updates status to `'diarsipkan'`.
6. **Deletion**: Admin deletes obsolete inquiry via `DELETE /admin/inquiries/{id}`. Record is cleanly purged.

### Scenario 4.5: High-Efficiency Performance & Zero N+1 Eager Loading Verification
1. **Database Query Logging**: A test initializes 15 projects, each having 5 gallery images (total 75 gallery rows).
2. **Index Query Budget**: Admin requests `GET /admin/projects`.
3. **Eager Loading Enforcement**: Query logger verifies `Project::withCount('images')` executes at most 2 database queries (1 query for projects pagination + 1 query for image counts), completely preventing 15 separate N+1 queries.
4. **Edit Query Budget**: Admin requests `GET /admin/projects/{id}/edit`. Query logger verifies `Project::with('images')` executes exactly 2 queries regardless of image count.
5. **Asset Bundle Check**: `npm run build` compiles Vite assets producing code-split chunks for pages and vendor libraries.

### Scenario 4.6: End-to-End Theme Transformation & Cross-Device Visual Preservation Lifecycle
1. **Browser Chrome & Canvas Immersion**: Visitor accesses `GET /`. Response serves `<meta name="theme-color" content="#0B0E0C">` ensuring mobile browser status bars immerse seamlessly into the deep obsidian canvas. Body classes establish `#0B0E0C` background and `#F4F6F0` editorial typography.
2. **Translucent Glass Navigation**: Sticky navbar activates `backdrop-blur-md` over translucent moss glass `rgba(20, 32, 24, 0.65)` with frosted glass perimeter `border-white/10`. Navigation items display active indicator underline in emerald glow.
3. **Portfolio Exploration & Anti-CLS Verification**: Visitor browses `/projects` and `/projects/{slug}`. Cards maintain rigid `aspect-[16/10]` frames and masterplan views maintain `aspect-[21/9]` preventing cumulative layout shifts during dynamic image loading.
4. **Administrative Immersion**: Administrator logs in via `/admin/login` and views `/admin`. The administrative interface reflects deep obsidian backgrounds `#0B0E0C` and elevated cards `#0E1310` with Indonesian localization dictionary bindings (`id.*`).
5. **Preservation Safeguard**: `AdminLayout.jsx` strictly retains `#1C2A21`, `#F9F8F6`, and `#E6E2DD` token bindings for selection and accessible badges, ensuring legacy test compliance.
6. **Multi-Viewport Invariance**: All views resize seamlessly across 320px (mobile), 768px (tablet), and 1440px (desktop) with zero horizontal overflow, preserving `max-w-7xl` container constraints and responsive CSS grids.

---

## 5. Coverage Thresholds & Quality Gates

To achieve and maintain **TEST_READY** status:
- **Feature Coverage**: 100% of all 32 features in `PROJECT.md` have corresponding test assertions across Tiers 1–4.
- **Requirements Coverage**: 100% of acceptance criteria for R1 through R3 in `ORIGINAL_REQUEST.md` verified.
- **Execution Pass Rate**: 100% passing tests (zero failures, zero errors across all 401 tests) when run via `php artisan test`.
- **Build Pass Rate**: `npm run build` exits with code 0 and verifies valid chunking in `public/build/manifest.json`.
- **Aesthetic Conformance**: Full compliance with `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` design principles (high-contrast typography > 15:1, tactile frosted borders, zero bloated markup).
- **Code Style & Linting**: All PHP files follow PSR-12 and Laravel coding standards without syntax errors.
