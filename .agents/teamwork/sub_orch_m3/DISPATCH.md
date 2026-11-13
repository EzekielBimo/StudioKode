# Dispatch: Sub-Orchestrator Milestone 3 (`theme_admin_dashboard_suite`)

**Role**: Sub-Orchestrator for Milestone 3
**Working Directory**: `c:\laragon\www\StudioKode`
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m3`

**Mandatory Reading**:
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
- `c:\laragon\www\StudioKode\PROJECT.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\analysis.md` (read this thoroughly for exact admin styling plan & test warnings)
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\handoff.md`

**Milestone 3 Scope (9 files)**:
1. `resources/js/Pages/Admin/Auth/Login.jsx`
2. `resources/js/Pages/Admin/Dashboard/Index.jsx`
3. `resources/js/Pages/Admin/Projects/Index.jsx`
4. `resources/js/Pages/Admin/Projects/Create.jsx`
5. `resources/js/Pages/Admin/Projects/Edit.jsx`
6. `resources/js/Pages/Admin/Projects/Gallery.jsx`
7. `resources/js/Pages/Admin/Inquiries/Index.jsx`
8. `resources/js/Pages/Admin/Inquiries/Show.jsx`
9. `resources/js/Components/Admin/ProjectGalleryManager.jsx`

**Key Requirements & Preservation Rules**:
- Smoked Obsidian / Dark Moss Glass styling:
  - Deep obsidian backgrounds (`#0B0E0C`, `#0E1310`, `#121814`)
  - Translucent moss glass surfaces (`rgba(20, 32, 24, 0.65)` with backdrop blur) on KPI cards, table containers, and modals
  - Frosted borders: `border-white/10` or `border-emerald-500/15`
  - High-contrast off-white/cream editorial typography (`#F4F6F0` / `#F5F2EB`) for superior administrative clarity
  - Form inputs: dark moss matte backgrounds (`#0E1310` or `#121814`) with emerald focus rings (`focus:ring-emerald-500/50`)
- **CRITICAL TEST ASSERTION REQUIREMENT in `ProjectGalleryManager.jsx`**:
  `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:855-873` explicitly asserts:
  - `aspect-[4/3]`
  - `bg-[#E6E2DD]`
  - `loading="lazy"`
  - `decoding="async"`
  - `Reorder.Group`
  - `Reorder.Item`
  You MUST retain these exact classes and attributes in `ProjectGalleryManager.jsx`!
- **LOCALIZATION REQUIREMENT**:
  Preserve all Indonesian localization strings (`id.*` from `resources/js/lib/id.js`).
- **LAYOUT PRESERVATION**:
  Preserve all tables, KPI grids, forms, tab layouts, and responsive drawers without structural alteration.

**Your Workflow**:
1. Initialize `SCOPE.md`, `BRIEFING.md`, and `progress.md` in your state folder.
2. Run iteration loop:
   - Worker implements the styling across the 9 files.
   - Run `npm run build` and `php artisan test`.
   - Dispatch Reviewers, Challengers, and Forensic Auditor.
   - Evaluate Gate in `GATE_STATUS.md`.
3. When gate passes, write `handoff.md` and report completion back to parent orchestrator.


## 2026-11-10T13:29:13Z
You are the Sub-Orchestrator for Milestone 3: theme_admin_dashboard_suite in the StudioKode theme transformation.

Your working directory: c:\laragon\www\StudioKode
Your state folder: c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m3

CRITICAL REQUIREMENT:
Before doing anything, read the Original User Request file at:
c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md

Also read your dispatch task details at:
c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m3\DISPATCH.md
and the master project index at:
c:\laragon\www\StudioKode\PROJECT.md

Detailed technical analysis and test warnings from Survey:
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\analysis.md (read this thoroughly!)
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\handoff.md

Scope of Milestone 3 (9 files):
1. resources/js/Pages/Admin/Auth/Login.jsx
2. resources/js/Pages/Admin/Dashboard/Index.jsx
3. resources/js/Pages/Admin/Projects/Index.jsx
4. resources/js/Pages/Admin/Projects/Create.jsx
5. resources/js/Pages/Admin/Projects/Edit.jsx
6. resources/js/Pages/Admin/Projects/Gallery.jsx
7. resources/js/Pages/Admin/Inquiries/Index.jsx
8. resources/js/Pages/Admin/Inquiries/Show.jsx
9. resources/js/Components/Admin/ProjectGalleryManager.jsx

CRITICAL TEST ASSERTION REQUIREMENT:
tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:855-873 explicitly asserts that ProjectGalleryManager.jsx contains:
- aspect-[4/3]
- bg-[#E6E2DD]
- loading="lazy"
- decoding="async"
- Reorder.Group
- Reorder.Item
You MUST retain these exact classes/attributes in ProjectGalleryManager.jsx!

LOCALIZATION & LAYOUT PRESERVATION:
- Retain all Indonesian text and id.* localization references from resources/js/lib/id.js.
- Preserve all structural grids, data tables, form layouts, and responsive drawers.

Execute the sub-orchestrator procedure:
1. Initialize SCOPE.md, BRIEFING.md, and progress.md in your state folder.
2. Run iteration loop:
   - Worker implements the styling across the 9 files adhering to /design-taste-frontend, /antislop-ui, and /ponytail.
   - Run npm run build and php artisan test.
   - Reviewers, Challengers, and Forensic Auditor verify the implementation.
   - Evaluate gate in GATE_STATUS.md.
3. When gate passes, write handoff.md and send completion message to parent orchestrator.
