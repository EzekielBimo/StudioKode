# Frontend Architecture, UI Design System, and Build Pipeline Survey Report

**Explorer**: Explorer Survey 2 (Frontend Architecture & Build Pipeline)  
**Date**: 2026-11-09  
**Target Application**: StudioKode Botanical & Landscape Architecture  
**Working Directory**: `c:\laragon\www\StudioKode`  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_2`  

---

## 1. Observation

### 1.1 Frontend Stack & Tooling
Direct inspection of `package.json` (lines 8–25) confirms the exact dependency manifest:
```json
"dependencies": {
    "@inertiajs/react": "^1.2.0",
    "clsx": "^2.1.1",
    "framer-motion": "^11.0.0",
    "lucide-react": "^0.359.0",
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "tailwind-merge": "^2.5.4"
},
"devDependencies": {
    "@vitejs/plugin-react": "^4.2.1",
    "autoprefixer": "^10.4.18",
    "axios": "^1.6.4",
    "laravel-vite-plugin": "^1.0",
    "postcss": "^8.4.35",
    "tailwindcss": "^3.4.1",
    "vite": "^5.0"
}
```
- **Framework**: Inertia.js with React 18 SPA architecture. No Vue, no Alpine.js, no Angular.
- **Styling**: Tailwind CSS v3.4.1, PostCSS v8.4.35, Autoprefixer v10.4.18.
- **Icons**: Lucide React (`lucide-react` 0.359.0).
- **Motion & Transitions**: Framer Motion (`framer-motion` 11.0.0).
- **Client Entry**: `resources/js/app.jsx` initializes Inertia using `createInertiaApp` and dynamically imports pages using:
  ```javascript
  // resources/js/app.jsx:30
  resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
  ```
- **Root Blade Template**: `resources/views/app.blade.php` (lines 10–21) imports Google Fonts (`Cormorant Garamond` and `Plus Jakarta Sans`), `@viteReactRefresh`, `@vite(['resources/css/app.css', 'resources/js/app.jsx'])`, and mounts `@inertia`.

### 1.2 Aesthetic Style & Design Tokens
From `tailwind.config.js` (lines 11–29):
- **Studio Landscape Architecture Palette**:
  - `studio.offwhite`: `#F9F8F6` — Warm architectural chalk/limestone background canvas.
  - `studio.foliage`: `#1C2A21` — Deep botanical forest green for primary CTA, hero backdrops, and active elements.
  - `studio.foliage-light`: `#283c2f` — Hover state for foliage.
  - `studio.stone`: `#E6E2DD` — Warm geological stone borders, neutral cards, and dividers.
  - `studio.stone-dark`: `#d8d3cd` — Hover state for stone elements.
  - `studio.charcoal`: `#1A1A1A` — Deep tactile charcoal for high-contrast typography and dark surfaces.
  - `studio.muted`: `#6E6B65` — Secondary descriptive copy and archival labels.
- **Typography Tokens**:
  - `fontFamily.serif`: `['"Cormorant Garamond"', 'Georgia', 'serif']` for all titles and headings (`h1`–`h4`).
  - `fontFamily.sans`: `['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif']` for interface body, form fields, and data tables.
  - `letterSpacing.widest-plus`: `0.25em` for uppercase architectural badges, categories, and tags.
- **Base CSS**: `resources/css/app.css` defines `html { scroll-behavior: smooth; background-color: #F9F8F6; color: #1A1A1A; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }` and selection styling (`::selection { background-color: #1C2A21; color: #F9F8F6; }`).

### 1.3 Existing Views, Layouts, and Components
Inspection of `resources/js/` reveals:
- **Layouts**: Only `resources/js/Layouts/PublicLayout.jsx` exists (contains `Navbar`, flash message banner, `<main>`, and `Footer`).
- **Components**:
  - `Components/Navigation/`: `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`
  - `Components/Portfolio/`: `ProjectCard.jsx`, `FilterBar.jsx`, `ImageGallery.jsx`
  - `Components/UI/`: `Button.jsx`, `Badge.jsx`, `Input.jsx`, `InView.jsx`, `Modal.jsx`
- **Pages**:
  - `Pages/Home/Index.jsx`
  - `Pages/Projects/Index.jsx`
  - `Pages/Projects/Show.jsx`
  - `Pages/Studio/Index.jsx`
  - `Pages/Services/Index.jsx`
  - `Pages/Contact/Index.jsx`
- **Admin Pages & Layout**: **Zero** admin views currently exist. There is no `Layouts/AdminLayout.jsx`, no `Pages/Admin/`, and no admin auth view (`Pages/Admin/Auth/Login.jsx`).

### 1.4 Production Build Execution & Pipeline Output
Executing `npm run build` completed with code `0`:
```
vite v5.4.21 building for production...
✓ 2005 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json                     3.08 kB │ gzip:  0.51 kB
public/build/assets/app-zi3mP2aB.css          36.96 kB │ gzip:  6.77 kB
public/build/assets/map-pin-DpxE_HXM.js        0.37 kB │ gzip:  0.30 kB
public/build/assets/Button-7ucioZUu.js         1.17 kB │ gzip:  0.57 kB
public/build/assets/ProjectCard-Bk1cX7wM.js    1.61 kB │ gzip:  0.80 kB
public/build/assets/Index-BFqfKzj5.js          2.97 kB │ gzip:  1.21 kB
public/build/assets/Index-B5Xn9Eir.js          4.30 kB │ gzip:  1.13 kB
public/build/assets/Index-CMGH7JvK.js          4.85 kB │ gzip:  1.86 kB
public/build/assets/Index-C66jeWpj.js          5.06 kB │ gzip:  1.85 kB
public/build/assets/Index-BA1tZCJk.js          7.73 kB │ gzip:  2.63 kB
public/build/assets/Show-B58t3C7n.js          10.62 kB │ gzip:  3.14 kB
public/build/assets/InView-CeXI9al_.js       123.68 kB │ gzip: 40.62 kB
public/build/assets/app-Bhmyf7tR.js          288.94 kB │ gzip: 96.97 kB
✓ built in 19.93s
```
- **Code-Splitting**: Code-splitting is natively functioning; each page is isolated into individual chunks (1.1 kB to 3.1 kB gzip).
- **HMR / `public/hot` Artifact**: A `public/hot` file was discovered containing `http://127.0.0.1:5173`. When `public/hot` is present, Laravel's `@vite` directive directs asset requests to the local Vite dev server port rather than reading `public/build/manifest.json`.

### 1.5 Shared Middleware Props
In `app/Http/Middleware/HandleInertiaRequests.php` (lines 36–45):
```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'appName' => config('app.name', 'StudioKode'),
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ]);
}
```
Currently, `'auth.user'` is not shared in Inertia props.

### 1.6 Existing Test Suite Status
Running `php artisan test` succeeded completely:
- **Result**: `83 passed (711 assertions)` in `5.73s`.
- `FrontendBuildTest.php` specifically enforces the presence and structure of the 5 UI components, 3 Navigation components, 3 Portfolio components, `PublicLayout.jsx`, and all 6 public page components.

---

## 2. Logic Chain

1. **Stack Alignment (Inertia + React 18)**:
   - Observation 1.1 demonstrates the application is strictly an Inertia.js React application with Tailwind CSS and Vite.
   - Therefore, any admin interface should be built using Inertia.js React page components (`resources/js/Pages/Admin/**`) and an `AdminLayout.jsx` shell, rather than Blade or external dashboard frameworks. This maintains single-stack cohesion, shared UI tokens, and seamless client-side page transitions without page reloads.

2. **Aesthetic Consistency (StudioKode Botanical Theme)**:
   - Observation 1.2 documents the established luxury landscape architecture palette (`#1C2A21` foliage, `#F9F8F6` offwhite, `#E6E2DD` stone, Cormorant Garamond serif).
   - Generic Tailwind templates or standard bright blue/gray admin designs would break brand consistency. The admin suite must inherit the same tactile stone-and-foliage theme, uppercase wide letter-spacing, and Cormorant Garamond typography for headers and stat metrics.

3. **Admin UI Architecture & Layout Structure**:
   - Observation 1.3 shows that no admin views exist yet, while public views are isolated under `resources/js/Pages/{Home,Projects,Studio,Services,Contact}` and `Layouts/PublicLayout.jsx`.
   - Creating `resources/js/Layouts/AdminLayout.jsx` allows the admin suite to have its own navigation, header, sidebar, session controls, and flash alert containers, completely independent of the public layout, avoiding any regression on the public site or existing tests.

4. **Gallery Drag-and-Drop & Reordering Mechanism**:
   - Observation 1.1 confirms `framer-motion` v11.0.0 is installed in `package.json`.
   - Framer Motion provides built-in `<Reorder.Group>` and `<Reorder.Item>` primitives. This allows implementation of drag-and-drop gallery reordering with zero additional dependencies, full touch/pointer gesture support, and automatic coordinate recalculation.
   - Supplementing this with numeric order inputs and manual "Naik / Turun" (Move Up / Down) buttons guarantees complete accessibility for keyboard and screen reader users.

5. **Indonesian Localization (Bahasa Indonesia)**:
   - Requirement R5 specifies that all admin navigation, forms, buttons, validation messages, badges, and notifications must be in Bahasa Indonesia.
   - Observation 1.5 indicates that flash messages and errors flow through Inertia props. A centralized localization dictionary (`resources/js/lib/i18n.js` or dedicated Indonesian constants) ensures consistent terminology across all admin views:
     - Navigation: *Dasbor*, *Manajemen Proyek*, *Pesan Konsultasi*, *Lihat Website*, *Keluar*.
     - Metric cards: *Total Proyek*, *Proyek Unggulan*, *Pesan Masuk Baru*, *Total Pesan Konsultasi*.
     - Status badges: *Baru*, *Dihubungi*, *Diarsipkan*, *Unggulan*, *Draf*, *Terbit*.
     - Form labels & validation: *Judul Proyek*, *Foto Sampul*, *Denah Masterplan*, *Luas Area*, *Urutan Galeri*, *Pemberitahuan Sukses*.

6. **File Upload Handling & Laravel `_method` Protocol**:
   - Observation 1.4 confirms Vite compiles standard modern ESM. When uploading multi-part files (images) during project updates, standard HTTP `PUT` requests in PHP do not populate `$_FILES`.
   - The React admin forms must transmit multipart updates via `router.post(route, { ...data, _method: 'PUT' }, { forceFormData: true })` to prevent empty file submissions.

7. **Production Performance (R6)**:
   - Observation 1.4 proves Vite builds in ~20s with fine-grained page chunks.
   - To prevent Cumulative Layout Shift (CLS), all image previews in admin tables and gallery grids must use fixed aspect ratio containers (`aspect-[16/10] bg-[#E6E2DD]`) with native `loading="lazy"`.
   - On the backend, admin controllers must apply eager loading (`withCount('images')` for project lists, and `with('images')` for gallery views) to eliminate N+1 queries.

---

## 3. Caveats

1. **`public/hot` Dev Artifact**: The file `public/hot` was present in the repository. If a production test or automated headless browser visits the site without Vite's dev server active at `127.0.0.1:5173`, asset loading will fail. The file should be removed or managed during production testing.
2. **Storage Symlink**: `public/storage` does not yet exist. In development and production, `php artisan storage:link` must be executed to ensure uploaded project and gallery images are publicly accessible via `/storage/`.
3. **Database Seed Integrity**: `tests/Feature/FrontendBuildTest.php` strictly asserts that the 5 existing UI components and 6 public pages exist. Implementing admin components in separate paths (`Pages/Admin/**` and `Layouts/AdminLayout.jsx`) ensures 100% backward compatibility and zero test breakage.

---

## 4. Conclusion

The StudioKode frontend is architectured as an **Inertia.js + React 18 + Tailwind CSS** single-page application with a refined botanical luxury aesthetic. The build pipeline is functioning and ready.

To fulfill requirements R1–R6, the following architectural blueprint must be executed:

### Required Frontend Components to Build:
1. **Layout**:
   - `resources/js/Layouts/AdminLayout.jsx`: Topbar/sidebar with StudioKode aesthetic (`#1C2A21` foliage header/nav, `#F9F8F6` background), admin user profile, Indonesian navigation, flash alert toasts.
2. **Authentication View**:
   - `resources/js/Pages/Admin/Auth/Login.jsx`: Elegant login form in Bahasa Indonesia with CSRF protection, email/password fields, remember-me toggle, and localized error messages.
3. **Dashboard View**:
   - `resources/js/Pages/Admin/Dashboard/Index.jsx`: 4 metric cards (*Total Proyek*, *Proyek Unggulan*, *Pesan Baru*, *Total Pesan*), recent projects table, and recent inquiries table.
4. **Project Management Views**:
   - `resources/js/Pages/Admin/Projects/Index.jsx`: Filterable/searchable project table with status badges (*Unggulan*, *Terbit*), pagination, and action buttons (*Edit*, *Galeri*, *Hapus*).
   - `resources/js/Pages/Admin/Projects/Create.jsx`: Full creation form with auto-slug generation, image preview for cover and masterplan, and Indonesian labels/validation.
   - `resources/js/Pages/Admin/Projects/Edit.jsx`: Pre-populated edit form supporting image replacement and status toggles.
5. **Gallery Management View**:
   - `resources/js/Pages/Admin/Projects/Gallery.jsx`: Multi-image file drag-and-drop uploader, Framer Motion drag-and-drop sortable image list, caption editors, and single-click removal with confirmation.
6. **Inquiry Management Views**:
   - `resources/js/Pages/Admin/Inquiries/Index.jsx`: Status filter tabs (*Semua*, *Baru*, *Dihubungi*, *Diarsipkan*), tabular inquiry browser, and quick status actions.
   - `resources/js/Pages/Admin/Inquiries/Show.jsx`: Full inquiry detail modal/page displaying client parameters, property specifications, direct contact triggers (`mailto:`/`tel:`), and status modifier.
7. **Indonesian Localization Dictionary**:
   - `resources/js/lib/id.js`: Consolidated dictionary of Indonesian labels, form field hints, badge variants, and flash messages.

---

## 5. Verification Method

To independently verify the frontend stack and build health:

1. **Verify Test Suite**:
   ```powershell
   php artisan test
   ```
   *Expected*: All 83 existing tests in `tests/` pass with zero failures.

2. **Verify Production Build**:
   ```powershell
   npm run build
   ```
   *Expected*: Vite completes without warnings/errors, producing `public/build/manifest.json`, `app-[hash].css`, and code-split chunks for all pages under `public/build/assets/`.

3. **Verify Asset Bundle & Code Splitting**:
   Inspect `public/build/manifest.json` to confirm each page is emitted as an independent async chunk without bloated monolithic vendor bundling.

4. **Verify Storage Link**:
   ```powershell
   php artisan storage:link
   Test-Path "public\storage"
   ```
   *Expected*: Returns `True`, linking `public/storage` to `storage/app/public`.

5. **Invalidation Conditions**:
   - Any modification to existing components in `resources/js/Components/` that causes `tests/Feature/FrontendBuildTest.php` to fail.
   - Introducing external drag-and-drop libraries when `framer-motion` already provides `<Reorder>`.
   - Admin routes rendering English text instead of Bahasa Indonesia.
