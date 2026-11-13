# Dispatch for Worker M2

Scope: Milestone 2 — Frontend Architecture, Design System, Multi-Page Portfolio & Motion.
Working directory: c:\laragon\www\StudioKode
State folder: c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m2\

Read:
- c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md
- c:\laragon\www\StudioKode\.agents\teamwork\orchestrator\PROJECT.md
- c:\laragon\www\StudioKode\TEST_READY.md
- c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m2\handoff.md

## MANDATORY INTEGRITY WARNING
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Tasks
1. Update `package.json` to include `clsx` and `tailwind-merge`.
2. Configure `vite.config.js` with `@vitejs/plugin-react` and ensure entry point matches `resources/js/app.jsx`.
3. Create `postcss.config.js`.
4. Configure `tailwind.config.js` with organic architectural color palette:
   - Off-white (`#F9F8F6`)
   - Deep foliage green (`#1C2A21`)
   - Warm stone (`#E6E2DD`)
   - Charcoal (`#1A1A1A`)
   - Editorial typography (Cormorant Garamond display + Plus Jakarta Sans body)
5. Update `resources/css/app.css` with `@tailwind base; @tailwind components; @tailwind utilities;` and custom luxury scrollbar/selection styling.
6. Create `resources/js/app.jsx` with `createInertiaApp` resolving pages dynamically.
7. Implement all UI components under `resources/js/Components/UI/`:
   - `Button.jsx`
   - `Badge.jsx`
   - `Modal.jsx` (lightbox modal with keyboard navigation)
   - `Input.jsx` (text inputs & textareas with error states)
   - `InView.jsx` (framer-motion blur reveal: `opacity: 0, y: 24, filter: 'blur(4px)'` to `opacity: 1, y: 0, filter: 'blur(0px)'`, `once: true`)
8. Implement all navigation components under `resources/js/Components/Navigation/`:
   - `Navbar.jsx` (wordmark, navigation links, mobile menu trigger)
   - `MobileMenu.jsx` (drawer overlay)
   - `Footer.jsx` (architectural manifesto, links, newsletter, copyright)
9. Implement layout under `resources/js/Layouts/`:
   - `PublicLayout.jsx` (Head, Navbar, flash notifications, Footer)
10. Implement portfolio components under `resources/js/Components/Portfolio/`:
    - `ProjectCard.jsx` (wrapped in InView, hover effects, specs)
    - `FilterBar.jsx` (keyword/location search, sort by Newest, Oldest, Largest Area in m², no category filters)
    - `ImageGallery.jsx` (staggered InView items, click to open Modal lightbox)
11. Implement all 6 pages under `resources/js/Pages/`:
    - `Home/Index.jsx` (hero presentation, featured projects, studio philosophy, CTA)
    - `Projects/Index.jsx` (full project listing with FilterBar)
    - `Projects/Show.jsx` (detailed view, narrative, masterplan, image gallery with lightbox)
    - `Studio/Index.jsx` (background, philosophy, team, process)
    - `Services/Index.jsx` (landscape & garden architectural services)
    - `Contact/Index.jsx` (consultation form with `useForm`, validation, flash messages)
12. Run `npm install` and `npm run build` (or `npx vite build`) — verify build succeeds with 0 errors.
13. Run `php artisan test` (with PHP 8.3) — verify all 50+ tests pass with 100% success.
14. Write detailed handoff report to `c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m2\handoff.md` and message orchestrator.


## 2026-11-09T02:11:46Z
You are Worker M2 for Milestone 2 (Frontend Architecture, Design System, Multi-Page Portfolio & Motion) of StudioKode.
State folder: c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m2
Working directory: c:\laragon\www\StudioKode

Read:
- c:\laragon\www\StudioKode\.agents\teamwork\ORIGINAL_REQUEST.md
- c:\laragon\www\StudioKode\.agents\teamwork\orchestrator\PROJECT.md
- c:\laragon\www\StudioKode\TEST_READY.md
- c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m2\DISPATCH.md
- c:\laragon\www\StudioKode\.agents\teamwork\teamwork_explorer_m2\handoff.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

File Ownership:
You own:
- package.json, vite.config.js, tailwind.config.js, postcss.config.js
- resources/css/app.css
- resources/js/app.jsx
- resources/js/Components/UI/* (Button.jsx, Badge.jsx, Modal.jsx, Input.jsx, InView.jsx)
- resources/js/Components/Navigation/* (Navbar.jsx, MobileMenu.jsx, Footer.jsx)
- resources/js/Components/Portfolio/* (ProjectCard.jsx, FilterBar.jsx, ImageGallery.jsx)
- resources/js/Layouts/PublicLayout.jsx
- resources/js/Pages/Home/Index.jsx, Projects/Index.jsx, Projects/Show.jsx, Studio/Index.jsx, Services/Index.jsx, Contact/Index.jsx

Follow the blueprints in Explorer M2's handoff.
Execute npm install and npm run build.
Run php artisan test with PHP 8.3 (prepend C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64 to $env:PATH).
Document all commands and verification results in c:\laragon\www\StudioKode\.agents\teamwork\teamwork_worker_m2\handoff.md, and send a completion message with the path.
