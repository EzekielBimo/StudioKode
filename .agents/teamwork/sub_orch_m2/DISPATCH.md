# Dispatch: Sub-Orchestrator Milestone 2 (`theme_shared_components_public_pages`)

**Role**: Sub-Orchestrator for Milestone 2
**Working Directory**: `c:\laragon\www\StudioKode`
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m2`

**Mandatory Reading**:
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
- `c:\laragon\www\StudioKode\PROJECT.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_2\analysis.md` (has line-by-line class mappings and diffs for all M2 components and public pages)
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_2\handoff.md`

**Milestone 2 Scope**:
1. Shared UI Primitives:
   - `resources/js/Components/UI/Button.jsx`
   - `resources/js/Components/UI/Badge.jsx`
   - `resources/js/Components/UI/Input.jsx`
   - `resources/js/Components/UI/Modal.jsx`
2. Portfolio Components:
   - `resources/js/Components/Portfolio/ProjectCard.jsx`
   - `resources/js/Components/Portfolio/FilterBar.jsx`
   - `resources/js/Components/Portfolio/ImageGallery.jsx`
3. Public Pages:
   - `resources/js/Pages/Home/Index.jsx`
   - `resources/js/Pages/Projects/Index.jsx`
   - `resources/js/Pages/Projects/Show.jsx`
   - `resources/js/Pages/Studio/Index.jsx`
   - `resources/js/Pages/Services/Index.jsx`
   - `resources/js/Pages/Contact/Index.jsx`

**Key Requirements & Preservation Rules**:
- Smoked Obsidian / Dark Moss Glass styling:
  - Backgrounds: `#0B0E0C` (body/ground), `#0E1310` (cards), `#121814` (elevated)
  - Glass surfaces: `rgba(20, 32, 24, 0.65)` with backdrop blur on interactive cards/panels
  - Frosted borders: `border-white/10` or `border-emerald-500/15`
  - Luminous moss accents (`#34D399` / `#22C55E`) and warm gold accents (`#C2A649` / `#D4AF37`)
  - Typography: `#F4F6F0` (primary off-white/cream), `#A7ACA0` (sage stone), `#7C8375` (muted)
- STRICT PRESERVATION: All structural layouts, grids (`grid-cols-*`), max-width wrappers, padding, flex arrangements, and component hierarchies must remain 100% unaltered.
- Adhere strictly to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` (concise high-impact modifications, no over-engineering).
- Zero horizontal overflow across mobile (320px–480px), tablet (768px), desktop.

**Your Workflow**:
1. Initialize `SCOPE.md`, `BRIEFING.md`, and `progress.md` in your state folder.
2. Run the iteration loop:
   - Worker implements the styling across the 13 files.
   - Run `npm run build` and `php artisan test`.
   - Dispatch Reviewers, Challengers, and Forensic Auditor.
   - Evaluate Gate in `GATE_STATUS.md`.
3. When gate passes, write `handoff.md` and report completion back to parent orchestrator.


## 2026-11-10T12:50:50Z
You are the Sub-Orchestrator for Milestone 2: theme_shared_components_public_pages in the StudioKode theme transformation.

Your working directory: c:\laragon\www\StudioKode
Your state folder: c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m2

CRITICAL REQUIREMENT:
Before doing anything, read the Original User Request file at:
c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md

Also read your dispatch task details at:
c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m2\DISPATCH.md
and the master project index at:
c:\laragon\www\StudioKode\PROJECT.md

Detailed technical analysis and exact class mappings from Survey:
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_2\analysis.md (read this thoroughly!)
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_2\handoff.md

Scope of Milestone 2 (13 files):
1. UI Primitives:
   - resources/js/Components/UI/Button.jsx
   - resources/js/Components/UI/Badge.jsx
   - resources/js/Components/UI/Input.jsx
   - resources/js/Components/UI/Modal.jsx
2. Portfolio Components:
   - resources/js/Components/Portfolio/ProjectCard.jsx
   - resources/js/Components/Portfolio/FilterBar.jsx
   - resources/js/Components/Portfolio/ImageGallery.jsx
3. Public Pages:
   - resources/js/Pages/Home/Index.jsx
   - resources/js/Pages/Projects/Index.jsx
   - resources/js/Pages/Projects/Show.jsx
   - resources/js/Pages/Studio/Index.jsx
   - resources/js/Pages/Services/Index.jsx
   - resources/js/Pages/Contact/Index.jsx

Execute the sub-orchestrator procedure:
1. Initialize SCOPE.md, BRIEFING.md, and progress.md in your state folder.
2. Run iteration loop:
   - Worker implements the changes adhering to /design-taste-frontend, /antislop-ui, and /ponytail.
   - Run npm run build and php artisan test.
   - Reviewers, Challengers, and Forensic Auditor verify the implementation.
   - Evaluate gate in GATE_STATUS.md.
3. When gate passes, write handoff.md and send completion message to parent orchestrator.
