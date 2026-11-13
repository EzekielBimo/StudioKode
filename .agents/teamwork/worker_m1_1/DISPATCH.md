# Dispatch: Worker M1-1

**Task**: Implement Milestone 1: `theme_foundations_layouts` in the StudioKode theme transformation.
**Your Working Directory**: `c:\laragon\www\StudioKode`
**Your State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\worker_m1_1`

## Mandatory Reading Before Any Modifications
1. `c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md`
2. `c:\laragon\www\StudioKode\PROJECT.md`
3. `c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m1\SCOPE.md`
4. `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_1\analysis.md`
5. `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_1\handoff.md`
6. `c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\analysis.md`

## Relevant Skills
- `/design-taste-frontend`: `c:\laragon\www\StudioKode\.agents\skills\design-taste-frontend\SKILL.md`
- `/antislop-ui`: `C:\Users\Eki\.gemini\config\plugins\antislop\skills\antislop-ui\SKILL.md`
- `/ponytail`: `C:\Users\Eki\.gemini\config\plugins\ponytail\skills\ponytail\SKILL.md`

## Exclusive Write Ownership
You exclusively own and may modify ONLY these 8 files:
1. `resources/views/app.blade.php`
2. `tailwind.config.js`
3. `resources/css/app.css`
4. `resources/js/Layouts/PublicLayout.jsx`
5. `resources/js/Components/Navigation/Navbar.jsx`
6. `resources/js/Components/Navigation/MobileMenu.jsx`
7. `resources/js/Components/Navigation/Footer.jsx`
8. `resources/js/Layouts/AdminLayout.jsx`

DO NOT modify any other files in `resources/js/Pages` or `resources/js/Components/UI` or tests.

## Specific Implementation Requirements
1. **`resources/views/app.blade.php`**:
   - Add `<meta name="theme-color" content="#0B0E0C">` to `<head>`.
   - Update `<html>` tag to include `class="scroll-smooth bg-[#0B0E0C]"`.
   - Update `<body>` class to `font-sans antialiased bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] selection:text-[#F4F6F0] min-h-screen flex flex-col`.
2. **`resources/css/app.css`**:
   - Update `@layer base` `html` to `background-color: #0B0E0C; color: #F4F6F0;`.
   - Update `::selection` to `background-color: #1C3024; color: #F4F6F0;`.
   - Add utility classes under `@layer utilities` if helpful (`.glass-moss`, `.glass-obsidian`).
3. **`tailwind.config.js`**:
   - Ensure color definitions support the obsidian and dark moss aesthetic (e.g. extending colors with obsidian: `#0B0E0C`, `#0E1310`, `#121814`, moss: `#1C3024`, emerald accents, etc.) without breaking any existing tailwind configurations.
4. **`resources/js/Layouts/PublicLayout.jsx`**:
   - Update root wrapper to `bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] selection:text-[#F4F6F0]`.
   - Flash banner success: `bg-[#142018]/90 text-emerald-300 border-b border-emerald-500/20 backdrop-blur-md px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider animate-in fade-in duration-300`.
   - Flash banner error: `bg-rose-950/90 text-rose-200 border-b border-rose-500/30 backdrop-blur-md px-6 py-4 text-xs sm:text-sm text-center font-medium uppercase tracking-wider`.
   - Preserve flex layout and `<main className="flex-1">{children}</main>`.
5. **`resources/js/Components/Navigation/Navbar.jsx`**:
   - Translucent dark moss glass header: `sticky top-0 z-40 bg-[#0B0E0C]/85 backdrop-blur-md border-b border-white/10 transition-all`.
   - Brand wordmark: `font-serif text-2xl tracking-tight text-[#F4F6F0] group-hover:text-emerald-300 transition-colors`.
   - Tagline: `text-[9px] uppercase tracking-[0.25em] text-emerald-400/70 font-medium`.
   - Links: Inactive `text-[#C5C8C0] hover:text-white transition-colors`, Active `text-emerald-400 font-semibold` with underline `h-[1.5px] bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.4)]`.
   - Desktop CTA button: `px-4 py-2 border border-emerald-500/30 text-xs uppercase tracking-widest text-emerald-300 hover:bg-emerald-500/15 hover:border-emerald-400 hover:text-white transition-all font-medium`.
   - Mobile menu toggle icon: `p-2 text-[#F4F6F0] hover:text-emerald-400 transition-colors`.
6. **`resources/js/Components/Navigation/MobileMenu.jsx`**:
   - Container: `md:hidden border-b border-white/10 bg-[#0E1310]/95 backdrop-blur-xl px-6 py-8 overflow-hidden shadow-2xl`.
   - Links: Active `text-emerald-400 font-bold border-l-2 border-emerald-400 pl-3`, Inactive `text-[#C5C8C0] hover:text-white`.
   - Location & Divider: `border-t border-white/10 text-[#A7ACA0]/70`.
   - CTA button: `bg-[#142018] border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/20 hover:text-white`.
7. **`resources/js/Components/Navigation/Footer.jsx`**:
   - Container: `mt-auto bg-[#080B09] text-[#F4F6F0] py-16 border-t border-white/10`.
   - Manifesto text: `text-[#A7ACA0] font-light leading-relaxed`.
   - Headings: `text-xs uppercase tracking-widest text-emerald-400/90 font-semibold mb-4`.
   - Links: `text-[#C5C8C0] hover:text-emerald-300 transition-colors`.
   - CTA button: `border border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/15 hover:border-emerald-400 hover:text-white transition-colors`.
   - Bottom bar: `border-t border-white/5 text-[#7C8375]`.
8. **`resources/js/Layouts/AdminLayout.jsx`**:
   - **CRITICAL AUTOMATED TEST REQUIREMENT**:
     `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:835-849` strictly asserts:
     - string `#1C2A21` appears in `AdminLayout.jsx`
     - string `#F9F8F6` appears in `AdminLayout.jsx`
     - string `#E6E2DD` appears in `AdminLayout.jsx`
     - `aria-label=` appears in `AdminLayout.jsx`
     You MUST retain these exact string tokens in `AdminLayout.jsx`!
     For example:
     - Outer container: `min-h-screen bg-[#0B0E0C] text-[#F5F2EB] font-sans flex flex-col md:flex-row selection:bg-[#1C2A21] selection:text-[#F9F8F6]` (satisfies `#1C2A21` and `#F9F8F6`).
     - Sidebar inactive links: `text-[#E6E2DD]/70 hover:bg-white/5 hover:text-white` (satisfies `#E6E2DD`).
     - Retain all `aria-label=` attributes.
   - Desktop sidebar: `w-64 bg-[#0E1310]/95 backdrop-blur-md text-[#F5F2EB] border-r border-white/10 shrink-0 sticky top-0 h-screen justify-between p-6 z-30`.
   - Active nav link: `bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.15)]`.
   - Mobile topbar: `md:hidden bg-[#0E1310]/95 backdrop-blur-md text-[#F5F2EB] border-b border-white/10 px-4 py-3 flex items-center justify-between sticky top-0 z-40`.
   - Mobile drawer: `bg-[#0E1310] text-[#F5F2EB] p-6 border-r border-white/10`.
   - Flash alerts: Success `bg-[#142018]/90 border-b border-emerald-500/30 text-emerald-200 px-6 py-3.5 backdrop-blur-md`, Error `bg-rose-950/80 border-b border-rose-500/30 text-rose-200 px-6 py-3.5 backdrop-blur-md`.

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Verification
After modifying the files:
1. Run `npm run build` and ensure clean Vite build with 0 errors.
2. Run `php artisan test` and verify 389/389 tests pass with 100% success rate.
3. Write your final report in `c:\laragon\www\StudioKode\.agents\teamwork\worker_m1_1\handoff.md`.
4. Send a message to orchestrator with your completion status and build/test results.


## 2026-11-10T12:28:17Z
[Message] timestamp=2026-11-10T12:28:17Z sender=11b85b4f-a071-4b82-ba51-7f873c1c6eb4 priority=MESSAGE_PRIORITY_HIGH content=You are Worker M1-1 for StudioKode Milestone 1: theme_foundations_layouts.
Read your full dispatch file at:
c:\laragon\www\StudioKode\.agents\teamwork\worker_m1_1\DISPATCH.md

Also read:
- c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md
- c:\laragon\www\StudioKode\PROJECT.md
- c:\laragon\www\StudioKode\.agents\teamwork\sub_orch_m1\SCOPE.md
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_1\analysis.md
- c:\laragon\www\StudioKode\.agents\teamwork\explorer_survey_theme_3\analysis.md

Implement the Smoked Obsidian / Dark Moss Glass styling across the 8 files you exclusively own:
1. resources/views/app.blade.php
2. tailwind.config.js
3. resources/css/app.css
4. resources/js/Layouts/PublicLayout.jsx
5. resources/js/Components/Navigation/Navbar.jsx
6. resources/js/Components/Navigation/MobileMenu.jsx
7. resources/js/Components/Navigation/Footer.jsx
8. resources/js/Layouts/AdminLayout.jsx (CRITICAL: preserve #1C2A21, #F9F8F6, and #E6E2DD strings for ChallengerM5_2 test assertion!)

Adhere to /design-taste-frontend, /antislop-ui, and /ponytail standards.
MANDATORY INTEGRITY WARNING: DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Run `npm run build` and `php artisan test` to verify.
Write your handoff report to `c:\laragon\www\StudioKode\.agents\teamwork\worker_m1_1\handoff.md`.
Update `progress.md` in your folder.
Send completion message with build and test results back to the caller.
