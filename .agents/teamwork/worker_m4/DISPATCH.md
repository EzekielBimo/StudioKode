## 2026-11-09T13:21:28Z
You are Worker M4 (Milestone 4 Implementation Worker).
Your State Folder: c:\laragon\www\StudioKode\.agents\teamwork\worker_m4\
Working Directory: c:\laragon\www\StudioKode

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY FIRST STEP:
Read c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md and c:\laragon\www\StudioKode\PROJECT.md before doing anything else.
Next, read all three Explorer reports:
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_m4_1\handoff.md (Vite code splitting & Rollup manualChunks)
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_m4_2\handoff.md (config/filesystems.php & storage symlink)
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_m4_3\handoff.md (Layout shift prevention, decoding="async", bg-[#E6E2DD])

Scope & File Ownership:
You own exclusively:
- `vite.config.js`
- `config/filesystems.php`
- `resources/js/Components/Portfolio/ProjectCard.jsx`
- `resources/js/Components/Portfolio/ImageGallery.jsx`
- `resources/js/Components/Admin/ProjectGalleryManager.jsx`
- `resources/js/Pages/Admin/Projects/Index.jsx`
- `resources/js/Pages/Admin/Projects/Create.jsx`
- `resources/js/Pages/Admin/Projects/Edit.jsx`

Tasks to Implement:
1. `vite.config.js`:
   Implement Rollup `manualChunks` function with path normalization (`const normalizedId = id.replace(/\\/g, '/')`):
   - `vendor-react`: `react`, `react-dom`, `scheduler`
   - `vendor-inertia`: `@inertiajs/react`, `@inertiajs/core`
   - `vendor-motion`: `framer-motion`, `motion-dom`, `motion-utils`
   - `vendor-lucide`: `lucide-react`
   - Set `chunkSizeWarningLimit: 600` and `sourcemap: false`
2. `config/filesystems.php`:
   Update line 42 from:
   `'url' => env('APP_URL').'/storage',`
   to:
   `'url' => env('FILESYSTEM_PUBLIC_URL', '/storage'),`
   Verify that `public/storage` symlink exists; if missing, run `php artisan storage:link`.
3. Layout Shift & Image Optimization:
   - In `ProjectCard.jsx`: add `decoding="async"` to `<img>`
   - In `ImageGallery.jsx`: add `decoding="async"` to `<img>` (both grid thumbnail and modal lightbox)
   - In `ProjectGalleryManager.jsx`: add `decoding="async"` and `loading="lazy"` to staged queue preview `<img>`, update container background to `bg-[#E6E2DD]`
   - In `Admin/Projects/Index.jsx`, `Create.jsx`, `Edit.jsx`: ensure image container backgrounds use `bg-[#E6E2DD]` and have explicit aspect-ratio wrappers.

Verification Requirements:
Run and report the exact results of:
1. `php artisan test tests/Feature/Admin/AdminPerformanceTest.php` (Must pass ALL 8/8 tests!)
2. `npm run build` (Must build cleanly with vendor chunks: `vendor-react`, `vendor-inertia`, `vendor-motion`, `vendor-lucide`, entry point `app.jsx` ~4 kB!)
3. `php artisan test tests/Feature/Admin/` (All 75 admin tests must pass!)
4. `php artisan test` (Full regression: all 293 tests must pass!)

Output Requirements:
Write your complete handoff report to `c:\laragon\www\StudioKode\.agents\teamwork\worker_m4\handoff.md`.
Then send a message back to the caller (parent orchestrator).
