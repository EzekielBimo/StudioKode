# Project: StudioKode Smoked Obsidian / Dark Moss Glass Theme Transformation

## Architecture
StudioKode is an architectural landscape portfolio and administration suite built with Laravel 11, Inertia.js (React 18), Tailwind CSS, and Vite.
This project transforms the complete visual styling of the platform into the **"Smoked Obsidian / Dark Moss Glass"** aesthetic adhering to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` design principles, while strictly preserving all existing layouts, structural grids, component hierarchies, and responsive behavior.

### Visual Styling Tokens
- **Canvas Base**: `#0B0E0C` (deep smoked obsidian body with subtle mineral warmth).
- **Surface / Card Base**: `#0E1310` (card obsidian surface).
- **Elevated / Input Surface**: `#121814` (elevated panels, form controls, placeholder frames).
- **Dark Moss Glass**: `rgba(20, 32, 24, 0.65)` to `rgba(14, 22, 17, 0.75)` with `backdrop-blur-md` / `backdrop-blur-lg`.
- **Frosted Borders**: `border-white/10` or `border-emerald-500/15` (tactile glass perimeters).
- **Luminous Moss Accents**: `#34D399` (emerald-400) / `#22C55E` (emerald-500) and `#1C3024` (moss surface).
- **Warm Gold Accents**: `#C2A649` / `#D4AF37` for featured badges and editorial highlights.
- **Editorial Typography**: `#F4F6F0` (primary high-contrast cream), `#A7ACA0` (secondary sage stone), `#7C8375` (muted tertiary), retaining *Cormorant Garamond* (display serif) and *Plus Jakarta Sans* (sans UI).

### Strict Preservation Rules
1. **Zero Layout Shifts**: Structural CSS classes (`max-w-*`, `mx-auto`, `grid-cols-*`, `gap-*`, flex configurations) remain structurally unaltered across all modified files.
2. **Responsive Fidelity**: Zero horizontal scrollbar or clipping across 320px–480px (mobile), 768px (tablet), and 1024px+ (desktop).
3. **Test Assertion Preservation**: In `AdminLayout.jsx`, retain `#1C2A21`, `#F9F8F6`, `#E6E2DD` in selection / color definitions to satisfy `ChallengerM5_2_FrontendLocalizationStressTest`. In `ProjectGalleryManager.jsx`, preserve `aspect-[4/3]` and `bg-[#E6E2DD]` on thumbnail wrappers.
4. **Localization Preservation**: All Indonesian localization tokens (`id.*` from `resources/js/lib/id.js`) remain untouched.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Root Template Dark Obsidian & Meta Theme-Color | `resources/views/app.blade.php` `<meta name="theme-color" content="#0B0E0C">`, `html` & `body` dark background and selection | M1 | Survey Theme 1 |
| 2 | Tailwind & CSS Tokens | `tailwind.config.js` and `resources/css/app.css` obsidian & dark moss utilities and selection styles | M1 | Survey Theme 1 |
| 3 | Public Layout Shell & Toasts | `resources/js/Layouts/PublicLayout.jsx` dark obsidian background, emerald/red glass flash banners | M1 | Survey Theme 1 |
| 4 | Public Navbar Glass Header | `resources/js/Components/Navigation/Navbar.jsx` translucent dark moss glass header, brand styling, active indicators | M1 | Survey Theme 1 |
| 5 | Mobile Menu Drawer Glass | `resources/js/Components/Navigation/MobileMenu.jsx` translucent dark moss drawer with frosted borders | M1 | Survey Theme 1 |
| 6 | Footer Obsidian Surface | `resources/js/Components/Navigation/Footer.jsx` deep obsidian background, sage typography, emerald CTA button | M1 | Survey Theme 1 |
| 7 | Admin Layout Shell & Sidebar | `resources/js/Layouts/AdminLayout.jsx` obsidian sidebar, mobile drawer, flash toasts (preserving `#1C2A21`, `#F9F8F6`, `#E6E2DD` tokens) | M1 | Survey Theme 1 & 3 |
| 8 | UI Primitive Button | `resources/js/Components/UI/Button.jsx` primary (moss emerald), secondary, outline, ghost variants | M2 | Survey Theme 2 |
| 9 | UI Primitive Badge | `resources/js/Components/UI/Badge.jsx` foliage, neutral, outline variants with frosted borders | M2 | Survey Theme 2 |
| 10 | UI Primitive Input | `resources/js/Components/UI/Input.jsx` dark moss matte input, emerald focus ring, cream text | M2 | Survey Theme 2 |
| 11 | UI Primitive Modal | `resources/js/Components/UI/Modal.jsx` dark glass overlay, obsidian card body, frosted border | M2 | Survey Theme 2 |
| 12 | Portfolio ProjectCard | `resources/js/Components/Portfolio/ProjectCard.jsx` obsidian surface, aspect frame, hover title transition | M2 | Survey Theme 2 |
| 13 | Portfolio FilterBar | `resources/js/Components/Portfolio/FilterBar.jsx` dark moss glass inputs and dropdowns, frosted divider | M2 | Survey Theme 2 |
| 14 | Portfolio ImageGallery | `resources/js/Components/Portfolio/ImageGallery.jsx` dark thumbnail frames, obsidian lightbox modal | M2 | Survey Theme 2 |
| 15 | Public Home Page | `resources/js/Pages/Home/Index.jsx` hero headline, featured grid, manifesto quote, studio teaser | M2 | Survey Theme 2 |
| 16 | Public Projects Index Page | `resources/js/Pages/Projects/Index.jsx` header banner, filter bar integration, project grid | M2 | Survey Theme 2 |
| 17 | Public Project Show Page | `resources/js/Pages/Projects/Show.jsx` project hero, metadata sidebar, gallery, inquiry CTA | M2 | Survey Theme 2 |
| 18 | Public Studio Page | `resources/js/Pages/Studio/Index.jsx` narrative hero, philosophy cards, methodology timeline | M2 | Survey Theme 2 |
| 19 | Public Services Page | `resources/js/Pages/Services/Index.jsx` service cards, deliverables breakdown, process steps | M2 | Survey Theme 2 |
| 20 | Public Contact Page | `resources/js/Pages/Contact/Index.jsx` contact details, consultation form glass panel, submit button | M2 | Survey Theme 2 |
| 21 | Admin Auth Login View | `resources/js/Pages/Admin/Auth/Login.jsx` obsidian glass card, moss accents, high-contrast inputs | M3 | Survey Theme 3 |
| 22 | Admin Dashboard Index | `resources/js/Pages/Admin/Dashboard/Index.jsx` 4 KPI metric cards, recent projects & inquiries tables | M3 | Survey Theme 3 |
| 23 | Admin Projects Index | `resources/js/Pages/Admin/Projects/Index.jsx` search/filter bar, data table, status badges, modal triggers | M3 | Survey Theme 3 |
| 24 | Admin Projects Create View | `resources/js/Pages/Admin/Projects/Create.jsx` form inputs, dropzones, featured switch, submit actions | M3 | Survey Theme 3 |
| 25 | Admin Projects Edit View | `resources/js/Pages/Admin/Projects/Edit.jsx` tabbed edit panels, dropzones, metadata fields | M3 | Survey Theme 3 |
| 26 | Admin Projects Gallery Page | `resources/js/Pages/Admin/Projects/Gallery.jsx` breadcrumbs and container wrapper | M3 | Survey Theme 3 |
| 27 | Admin Project Gallery Manager | `resources/js/Components/Admin/ProjectGalleryManager.jsx` drag-and-drop sortable items, caption inputs (preserving `aspect-[4/3]` & `bg-[#E6E2DD]`) | M3 | Survey Theme 3 |
| 28 | Admin Inquiries Index | `resources/js/Pages/Admin/Inquiries/Index.jsx` status filter tabs, search bar, inquiries data table | M3 | Survey Theme 3 |
| 29 | Admin Inquiries Show View | `resources/js/Pages/Admin/Inquiries/Show.jsx` client info card, message quote, workflow status buttons | M3 | Survey Theme 3 |
| 30 | Full Automated Test Verification | `php artisan test` 100% pass across all 389 automated tests (4,098 assertions) | M4 | Survey Theme 1 & 3 |
| 31 | Production Asset Build Verification | `npm run build` cleanly compiled with 0 errors, asset manifest generated | M4 | Survey Theme 1 & 3 |
| 32 | Multi-Viewport Responsive & Visual Quality Verification | Zero horizontal overflow, clean glass borders, high-contrast editorial typography across 320px–1920px | M4 | Acceptance Criteria |
| 33 | Admin Profile & Password Management | `Admin/Profile/Edit.jsx`, `ProfileController.php`, route bindings, validation, and full test suite | M5 | Feature 4 |
| 34 | Inertia Dual-Sync Anti-Blank Screen Protocol | Inline sync script in `app.blade.php` and direct `page` injection in `app.jsx` preventing white screen errors | M5 | Stability |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | `theme_foundations_layouts` | Root template, global CSS, Tailwind tokens, PublicLayout, Navbar, MobileMenu, Footer, AdminLayout (Features 1-7) | none | DONE |
| M2 | `theme_shared_components_public_pages` | Button, Badge, Input, Modal, ProjectCard, FilterBar, ImageGallery, and 6 Public Pages (Features 8-20) | M1 | DONE |
| M3 | `theme_admin_dashboard_suite` | Admin Auth Login, Dashboard, Projects CRUD, Gallery Manager, Inquiries CRUD (Features 21-29) | M1, M2 | DONE |
| M4 | `e2e_verification_and_hardening` | Full 389+ automated tests, Vite production asset build, responsive & visual audit (Features 30-32) | M1, M2, M3 | DONE |

## Milestone Outputs & Gate Results
### Milestone 1: `theme_foundations_layouts` (Gate Result: PASS)
- **Key Files**:
  - `resources/views/app.blade.php`: `<meta name="theme-color" content="#0B0E0C">`, `html` & `body` dark background (`bg-[#0B0E0C] text-[#F4F6F0] selection:bg-[#1C3024] selection:text-[#F4F6F0]`).
  - `tailwind.config.js`: Extended obsidian (`#0B0E0C`, `#0E1310`, `#121814`) and moss (`#1C3024`, `#22C55E`, `#34D399`) palettes.
  - `resources/css/app.css`: Dark base layer, custom scrollbars, `.glass-moss` & `.glass-obsidian` utilities.
  - `resources/js/Layouts/PublicLayout.jsx`: Dark obsidian canvas with emerald/red glass flash banners.
  - `resources/js/Components/Navigation/Navbar.jsx`: Translucent dark moss glass header (`bg-[#0B0E0C]/85 backdrop-blur-md border-b border-white/10`), brand wordmark, active link indicators.
  - `resources/js/Components/Navigation/MobileMenu.jsx`: Translucent dark moss drawer with frosted borders and backdrop blur.
  - `resources/js/Components/Navigation/Footer.jsx`: Deep obsidian background, sage typography, emerald CTA button.
  - `resources/js/Layouts/AdminLayout.jsx`: Dark obsidian sidebar and mobile drawer, strictly preserving `#1C2A21`, `#F9F8F6`, and `#E6E2DD` tokens for localization tests.
- **Verification**:
  - `npm run build`: Clean build in 5.6s, exit code 0, 27 assets within budget.
  - `php artisan test`: 100% pass across 401 tests (4,300 assertions).
  - Reviewers (`reviewer_theme_m1_1`, `reviewer_theme_m1_2`): Unanimous APPROVE.
  - Challengers (`challenger_theme_m1_1`, `challenger_theme_m1_2`): Unanimous APPROVE.
  - Forensic Auditor (`auditor_theme_m1_1`): CLEAN (zero integrity violations).

### Milestone 2: `theme_shared_components_public_pages` (Gate Result: PASS)
- **Key Files**:
  - `resources/js/Components/UI/Button.jsx`: Styled in `primary` (emerald border & shadow), `secondary` (translucent moss glass `rgba(20,32,24,0.75)` with backdrop blur), `outline`, `outline-light`, `ghost`.
  - `resources/js/Components/UI/Badge.jsx`: `foliage`, `neutral`, `outline`, `outline-light` variants with frosted borders.
  - `resources/js/Components/UI/Input.jsx`: Dark moss matte surface (`bg-[#0E1310]`), mutually exclusive border classes (`border-rose-500/80` for error, `border-white/15` for standard), emerald focus rings.
  - `resources/js/Components/UI/Modal.jsx`: Translucent dark moss glass backdrop (`bg-[#0B0E0C]/85 backdrop-blur-md`), obsidian dialog frame (`bg-[#0E1310] border-white/15`), dual prop support (`show` and `isOpen`).
  - `resources/js/Components/Portfolio/ProjectCard.jsx`: Obsidian surface, placeholder `bg-[#121814]`, title hover transition, anti-CLS aspect ratio.
  - `resources/js/Components/Portfolio/FilterBar.jsx`: Dark moss glass inputs, select dropdowns with frosted perimeters, search input.
  - `resources/js/Components/Portfolio/ImageGallery.jsx`: Dark thumbnail frames, obsidian lightbox modal with zoom button.
  - `resources/js/Pages/Home/Index.jsx`: Hero headline, featured project grid, philosophy quote, studio teaser in editorial typography.
  - `resources/js/Pages/Projects/Index.jsx`: Page banner, filter bar integration, project grid, empty state.
  - `resources/js/Pages/Projects/Show.jsx`: Project hero, metadata sidebar with frosted glass cards, gallery integration, inquiry CTA.
  - `resources/js/Pages/Studio/Index.jsx`: Narrative hero, philosophy cards, methodology timeline with moss accents.
  - `resources/js/Pages/Services/Index.jsx`: Service cards, deliverables breakdown, process steps in dark moss styling.
  - `resources/js/Pages/Contact/Index.jsx`: Contact details, consultation form glass panel (`bg-[rgba(20,32,24,0.65)] backdrop-blur-md border border-white/10`), submit action.
- **Verification**:
  - `npm run build`: Clean compilation in 4.77s (exit code 0).
  - `php artisan test`: 100% pass across 409 tests (4,379 assertions).
  - Reviewer Iteration 2: APPROVE.
  - Challenger Iteration 2: APPROVE (38/38 SSR assertions passed).
  - Forensic Auditor Iteration 2: CLEAN (zero integrity violations).

### Milestone 3: `theme_admin_dashboard_suite` (Gate Result: PASS)
- **Key Files**:
  - `resources/js/Pages/Admin/Auth/Login.jsx`: Translucent dark moss glass login card (`bg-[#142018]/65 backdrop-blur-md border-white/10`), SSL indicator, emerald focus.
  - `resources/js/Pages/Admin/Dashboard/Index.jsx`: 4 KPI metric cards in dark moss glass, status badges (`baru`, `dihubungi`, `diarsipkan`), recent project and inquiry tables.
  - `resources/js/Pages/Admin/Projects/Index.jsx`: Search & filter bar in dark moss glass, projects table with status pills, modal deletion triggers.
  - `resources/js/Pages/Admin/Projects/Create.jsx`: Form inputs with dark moss matte backgrounds, image dropzones, featured toggle switch.
  - `resources/js/Pages/Admin/Projects/Edit.jsx`: Tabbed edit panels, cover/masterplan image uploaders, embedded gallery tab.
  - `resources/js/Pages/Admin/Projects/Gallery.jsx`: Breadcrumbs and container wrapper for gallery manager.
  - `resources/js/Components/Admin/ProjectGalleryManager.jsx`: Reorder drag-and-drop sortable items, caption editing, preserving `aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.Group`, `Reorder.Item`.
  - `resources/js/Pages/Admin/Inquiries/Index.jsx`: Status tabs with badge counts, search bar, inquiries data table.
  - `resources/js/Pages/Admin/Inquiries/Show.jsx`: Client details card, message block quote, workflow status buttons.
- **Verification**:
  - `npm run build`: Clean compilation in 4.76s (exit code 0), 27 chunks.
  - `php artisan test`: 100% pass across 409 tests (4,379 assertions).
  - Reviewers (`reviewer_theme_m3_1`, `reviewer_theme_m3_2`): Unanimous APPROVE.
  - Challengers (`challenger_theme_m3_1`, `challenger_theme_m3_2`): Unanimous APPROVE.
  - Forensic Auditor (`auditor_theme_m3_1`): CLEAN (zero integrity violations).

### Milestone 4: `e2e_verification_and_hardening` (Gate Result: PASS)
- **Phase 1: Full E2E Test Suite & Production Build Pass (Tiers 1–4)**:
  - `npm run build`: Executed cleanly with exit code 0; 27 optimized chunks generated in `public/build/manifest.json`.
  - Full PHPUnit automated suite: 100% pass across all 420 tests (4,639 assertions).
  - Dedicated theme suite (`ThemeTransformationTest.php`): 12 passed (202 assertions).
  - Stress suites (`ChallengerM4_2` & `ChallengerM5_2`): 42 passed (719 assertions).
- **Phase 2: Adversarial Coverage Hardening (Tier 5)**:
  - Multi-viewport responsive audit across 320px, 375px, 414px, 768px, 1024px, 1440px, and 1920px: zero horizontal overflow, responsive table scrolling, fluid grid collapse.
  - Relative luminance contrast verification: primary cream (`#F4F6F0`) on obsidian canvas (`#0B0E0C`) achieves 17.82:1 (exceeding WCAG AAA by >2.5x).
  - Anti-CLS aspect ratios verified across 100% of images (15/15 instances).
  - Legacy token contracts (`#1C2A21`, `#F9F8F6`, `#E6E2DD` in `AdminLayout.jsx` and `aspect-[4/3]`, `bg-[#E6E2DD]` in `ProjectGalleryManager.jsx`) and Indonesian localization (`id.*`) strictly preserved.
  - Reviewer 1 & Reviewer 2: Unanimous APPROVE.
  - Challenger 1 & Challenger 2: Unanimous APPROVE.
  - Forensic Auditor: CLEAN (Zero integrity violations).

## Interface Contracts

### Root Template ↔ Layouts
- `resources/views/app.blade.php` defines root background `#0B0E0C` and text `#F4F6F0`.
- `<meta name="theme-color" content="#0B0E0C">` sets browser chrome immersion.
- Selection classes `selection:bg-[#1C3024] selection:text-[#F4F6F0]` establish consistent brand selection.

### Layouts ↔ Pages
- `PublicLayout.jsx` wraps all public pages (`Home`, `Projects`, `Studio`, `Services`, `Contact`). Child pages inherit canvas background `#0B0E0C` and text `#F4F6F0`.
- `AdminLayout.jsx` wraps all admin pages. Establishes the `#0E1310` desktop sidebar and `#0B0E0C` main scroll container.
- `AdminLayout.jsx` preserves selection classes referencing `#1C2A21`, `#F9F8F6`, and `#E6E2DD` to ensure `ChallengerM5_2_FrontendLocalizationStressTest` passes without disruption.

### UI Primitives ↔ Domain Components & Pages
- `Button.jsx` exports `variant` (`primary`, `secondary`, `outline`, `outline-light`, `ghost`), `size` (`sm`, `md`, `lg`), `as`, `href`, `className`.
- `Badge.jsx` exports `variant` (`foliage`, `neutral`, `outline`, `outline-light`), `size` (`sm`, `md`).
- `Input.jsx` exports `label`, `error`, `multiline`, `className`.
- `Modal.jsx` exports `show`, `onClose`, `title`, `maxWidth`, `children`.
- All prop contracts are strictly preserved.

### Admin Suite ↔ Localization Contract
- All user-facing strings in `Admin/**/*.jsx` and `ProjectGalleryManager.jsx` reference `id.*` from `resources/js/lib/id.js`.
- `ProjectGalleryManager.jsx` retains `aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.Group`, and `Reorder.Item`.

## Code Layout

### Milestone 1 Files
- `resources/views/app.blade.php`
- `tailwind.config.js`
- `resources/css/app.css`
- `resources/js/Layouts/PublicLayout.jsx`
- `resources/js/Layouts/AdminLayout.jsx`
- `resources/js/Components/Navigation/Navbar.jsx`
- `resources/js/Components/Navigation/MobileMenu.jsx`
- `resources/js/Components/Navigation/Footer.jsx`

### Milestone 2 Files
- `resources/js/Components/UI/Button.jsx`
- `resources/js/Components/UI/Badge.jsx`
- `resources/js/Components/UI/Input.jsx`
- `resources/js/Components/UI/Modal.jsx`
- `resources/js/Components/Portfolio/ProjectCard.jsx`
- `resources/js/Components/Portfolio/FilterBar.jsx`
- `resources/js/Components/Portfolio/ImageGallery.jsx`
- `resources/js/Pages/Home/Index.jsx`
- `resources/js/Pages/Projects/Index.jsx`
- `resources/js/Pages/Projects/Show.jsx`
- `resources/js/Pages/Studio/Index.jsx`
- `resources/js/Pages/Services/Index.jsx`
- `resources/js/Pages/Contact/Index.jsx`

### Milestone 3 Files
- `resources/js/Pages/Admin/Auth/Login.jsx`
- `resources/js/Pages/Admin/Dashboard/Index.jsx`
- `resources/js/Pages/Admin/Projects/Index.jsx`
- `resources/js/Pages/Admin/Projects/Create.jsx`
- `resources/js/Pages/Admin/Projects/Edit.jsx`
- `resources/js/Pages/Admin/Projects/Gallery.jsx`
- `resources/js/Pages/Admin/Inquiries/Index.jsx`
- `resources/js/Pages/Admin/Inquiries/Show.jsx`
- `resources/js/Components/Admin/ProjectGalleryManager.jsx`

### Milestone 4 & 5 Verification Targets
- `tests/` (all 430 PHPUnit tests, 4,685 assertions passing)
- `npm run build` (Vite production bundle verification, 27 assets clean)
- Responsive viewports: 320px, 375px, 768px, 1024px, 1440px
- Admin Profile & Password Management suite (`AdminProfileTest.php`, 10 tests passing)
