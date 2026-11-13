# Dispatch: E2E Testing Orchestrator (`theme_e2e_verification`)

**Role**: E2E Testing Orchestrator
**Working Directory**: `c:\laragon\www\StudioKode`
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_e2e`

**Mandatory Reading**:
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
- `c:\laragon\www\StudioKode\PROJECT.md`
- `c:\laragon\www\StudioKode\TEST_INFRA.md` (existing test infra)
- `c:\laragon\www\StudioKode\TEST_READY.md` (existing test readiness)

**Scope & Mission**:
- Design and implement automated E2E and requirement-driven test verification for the "Smoked Obsidian / Dark Moss Glass" theme transformation.
- Target verification areas:
  1. Build & Asset verification: Verify `npm run build` succeeds cleanly, check CSS and JS bundle sizes and manifests.
  2. Test Suite verification: Run and verify all 389 PHPUnit tests in `tests/` pass with 100% success.
  3. Visual & Style verification: Create an automated feature/regression test (e.g. in `tests/Feature/ThemeTransformationTest.php`) verifying:
     - `resources/views/app.blade.php` contains `<meta name="theme-color" content="#0B0E0C">`, dark body classes (`bg-[#0B0E0C]`, `text-[#F4F6F0]`), and dark selection.
     - `PublicLayout.jsx` and `AdminLayout.jsx` have root obsidian canvas classes.
     - Responsive & layout preservation: verify structural grid classes (`grid-cols-*`, `max-w-7xl`, `min-h-screen`) are present across all pages and components.
     - Contrast & token verification: verify obsidian/moss tokens and off-white/cream editorial typography.
- Publish updated `TEST_READY.md` when the theme verification test suite is complete and ready.


## 2026-11-10T12:26:29Z
You are the E2E Testing Orchestrator for the StudioKode theme transformation.

Your working directory: c:\laragon\www\StudioKode
Your state folder: c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_e2e

CRITICAL REQUIREMENT:
Before doing anything, read the Original User Request file at:
c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md

Also read your dispatch task details at:
c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_e2e\DISPATCH.md
and the master project index at:
c:\laragon\www\StudioKode\PROJECT.md

Scope & Mission:
- Design and implement automated E2E and requirement-driven test verification for the "Smoked Obsidian / Dark Moss Glass" theme transformation.
- Target verification areas:
  1. Build & Asset verification: Verify npm run build succeeds cleanly, check CSS and JS bundle sizes and manifests.
  2. Test Suite verification: Run and verify all 389 PHPUnit tests in tests/ pass with 100% success.
  3. Visual & Style verification: Create an automated feature/regression test (e.g. in tests/Feature/ThemeTransformationTest.php) verifying:
     - resources/views/app.blade.php contains <meta name="theme-color" content="#0B0E0C">, dark body classes (bg-[#0B0E0C], text-[#F4F6F0]), and dark selection.
     - PublicLayout.jsx and AdminLayout.jsx have root obsidian canvas classes.
     - Responsive & layout preservation: verify structural grid classes (grid-cols-*, max-w-7xl, min-h-screen) are present across all pages and components.
     - Contrast & token verification: verify obsidian/moss tokens and off-white/cream editorial typography.
- Publish updated TEST_READY.md when the theme verification test suite is complete and ready.
- Send a completion message to the parent orchestrator when complete.
