# Worker Dispatch: Milestone 3 Theme Admin Suite

## Role & Mission
You are `worker_theme_m3_1`, the implementation worker for Milestone 3: `theme_admin_dashboard_suite` in the StudioKode theme transformation.
Your mission is to transform the visual styling of all 8 administrative views and `ProjectGalleryManager.jsx` into the **"Smoked Obsidian / Dark Moss Glass"** aesthetic.

## Working Directory & State Folder
- Working Directory: `c:\laragon\www\StudioKode`
- State Folder: `c:\laragon\www\StudioKode\.agents\teamwork\worker_theme_m3_1`

## Mandatory Reading Before Touching Code
1. `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
2. `c:\laragon\www\StudioKode\PROJECT.md`
3. `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m3\SCOPE.md`
4. `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\analysis.md` (read thoroughly: has complete Tailwind class mappings for all 9 files)
5. `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\handoff.md`

## Files Owned Exclusively by You (9 files)
1. `resources/js/Pages/Admin/Auth/Login.jsx`
2. `resources/js/Pages/Admin/Dashboard/Index.jsx`
3. `resources/js/Pages/Admin/Projects/Index.jsx`
4. `resources/js/Pages/Admin/Projects/Create.jsx`
5. `resources/js/Pages/Admin/Projects/Edit.jsx`
6. `resources/js/Pages/Admin/Projects/Gallery.jsx`
7. `resources/js/Pages/Admin/Inquiries/Index.jsx`
8. `resources/js/Pages/Admin/Inquiries/Show.jsx`
9. `resources/js/Components/Admin/ProjectGalleryManager.jsx`

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Non-Negotiable Contracts & Constraints
1. **CRITICAL TEST ASSERTION IN `ProjectGalleryManager.jsx`**:
   `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:855-873` explicitly asserts:
   - `aspect-[4/3]`
   - `bg-[#E6E2DD]`
   - `loading="lazy"`
   - `decoding="async"`
   - `Reorder.Group`
   - `Reorder.Item`
   You MUST retain these exact classes and attributes in `ProjectGalleryManager.jsx`! Specifically on thumbnail container wrappers (`aspect-[4/3]`, `bg-[#E6E2DD]`) and images.
2. **LOCALIZATION PRESERVATION**:
   Retain all Indonesian localization tokens (`id.*` from `resources/js/lib/id.js`). Never remove the import or replace Indonesian dictionary references with hardcoded strings.
3. **LAYOUT & STRUCTURAL INTEGRITY**:
   Preserve all structural grids, flex arrangements, table markup, data cells, drawer behaviors, and form fields. Zero layout shifts.
4. **DESIGN SYSTEM & AESTHETIC**:
   - Deep obsidian canvas backgrounds (`#0B0E0C`)
   - Translucent dark moss glass cards (`rgba(20, 32, 24, 0.65)` / `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_24px_rgba(0,0,0,0.35)]`)
   - High-contrast editorial cream typography (`#F5F2EB` / `text-stone-100`) for top administrative legibility
   - Secondary text: muted sage/stone (`text-stone-400` / `text-stone-500`)
   - Form inputs: matte dark moss (`bg-[#0E1310]/80 border border-white/10 text-stone-100 placeholder:text-stone-500 focus:ring-emerald-500/50 focus:border-emerald-500/50`)
   - Action buttons: luminous moss gradient (`bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]`)
   - Status badges: crisp translucent pills with border accents (`bg-emerald-500/20 text-emerald-300 border border-emerald-500/30`, etc.)

## Required Verification Steps After Implementation
1. Run `npm run build` — must succeed cleanly with exit code 0.
2. Clear any Windows testing disk lock:
   `if (Test-Path storage\framework\testing\disks) { Remove-Item -Recurse -Force storage\framework\testing\disks }`
3. Run `php artisan test` — must pass 100% across all tests.
4. Run targeted tests:
   `php artisan test --filter=ChallengerM5_2_FrontendLocalizationStressTest`
   `php artisan test --filter=ChallengerM4_2_AssetBuildStressTest`
   `php artisan test --filter=AdminAuthTest`
   `php artisan test --filter=AdminDashboardTest`
   `php artisan test --filter=AdminProjectTest`
   `php artisan test --filter=AdminGalleryTest`
   `php artisan test --filter=AdminInquiryTest`
5. Write your completion report in `c:\laragon\www\StudioKode\.agents\teamwork\worker_theme_m3_1\handoff.md`.
6. Send a message to your orchestrator reporting completion with exact test and build outputs.


## 2026-11-10T13:31:25Z
Dispatched to worker_theme_m3_1: Theme admin dashboard suite (9 files).
