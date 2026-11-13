# Dispatch: Sub-Orchestrator Milestone 1 (`theme_foundations_layouts`)

**Role**: Sub-Orchestrator for Milestone 1
**Working Directory**: `c:\laragon\www\StudioKode`
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m1`

**Mandatory Reading**:
- `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md` (read first!)
- `c:\laragon\www\StudioKode\PROJECT.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_1\analysis.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_1\handoff.md`
- `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\analysis.md`

**Milestone 1 Scope**:
- Root template: `resources/views/app.blade.php` (`<meta name="theme-color" content="#0B0E0C">`, `html` and `body` dark background and selection)
- Styling configuration: `tailwind.config.js`, `resources/css/app.css` (defining obsidian & dark moss utilities, selection styles, and color variables)
- Public Layout & Navigation:
  - `resources/js/Layouts/PublicLayout.jsx` (dark obsidian canvas, emerald/red glass flash banners)
  - `resources/js/Components/Navigation/Navbar.jsx` (translucent dark moss glass header, brand wordmark, active link indicator)
  - `resources/js/Components/Navigation/MobileMenu.jsx` (translucent dark moss drawer, frosted borders)
  - `resources/js/Components/Navigation/Footer.jsx` (deep obsidian background, sage typography, emerald CTA)
- Admin Layout:
  - `resources/js/Layouts/AdminLayout.jsx` (obsidian sidebar `#0E1310`, mobile drawer, flash toasts)
  - **CRITICAL TEST ASSERTION REQUIREMENT**: `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:835-849` explicitly asserts that the literal strings `#1C2A21`, `#F9F8F6`, and `#E6E2DD` appear in `AdminLayout.jsx`. You MUST retain these color codes in `AdminLayout.jsx` (e.g. in selection classes, or comment/fallback palette definition) so this test passes with 100% success!

**Your Workflow**:
1. Create `SCOPE.md`, `BRIEFING.md`, and `progress.md` in your state folder.
2. Execute the iteration loop:
   - Spawn Explorer (if needed, or use survey findings) -> Worker (`teamwork_worker`) -> Reviewers (`teamwork_reviewer`) + Challengers (`teamwork_challenger`) + Forensic Auditor (`teamwork_auditor`).
   - Run gate evaluation in `GATE_STATUS.md`.
   - Pass criteria: Build (`npm run build`) passes, automated tests (`php artisan test`) pass 100%, Reviewers APPROVE, Challengers APPROVE, Forensic Auditor CLEAN.
3. On gate pass, write `handoff.md` and send completion message to parent orchestrator.
