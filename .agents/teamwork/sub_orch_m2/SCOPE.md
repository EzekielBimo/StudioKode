# Scope: Milestone 2 — theme_shared_components_public_pages

## Status: DONE (Gate Result: PASS)

## Architecture
StudioKode landscape architecture portfolio and admin platform. Milestone 2 implements the visual transformation to "Smoked Obsidian / Dark Moss Glass" across all shared UI primitives, portfolio components, and public pages.

### Design Tokens & Palette
- **Canvas Base**: `#0B0E0C` (smoked obsidian base)
- **Card Surface**: `#0E1310` (card obsidian surface)
- **Elevated Panels / Inputs**: `#121814`
- **Dark Moss Glass**: `rgba(20, 32, 24, 0.65)` to `rgba(20, 32, 24, 0.75)` with `backdrop-blur-md`
- **Frosted Borders**: `border-white/10` or `border-emerald-500/15`
- **Luminous Moss Accents**: `#34D399` / `#22C55E` / `#88B896`
- **Warm Gold Accents**: `#C2A649` / `#D4AF37`
- **Typography**: `#F4F6F0` / `#F4F2ED` (cream editorial), `#A7ACA0` / `#C4C9C3` (sage stone body), `#7C8375` / `#A8B0A6` (muted metadata)

## Feature Inventory
| # | Feature | Description | Milestone | Source | Status |
|---|---------|-------------|-----------|--------|--------|
| 8 | UI Primitive Button | `resources/js/Components/UI/Button.jsx` primary, secondary, outline, outline-light, ghost variants with moss/obsidian styling | M2 | Survey Theme 2 | DONE |
| 9 | UI Primitive Badge | `resources/js/Components/UI/Badge.jsx` foliage, neutral, outline, outline-light variants with frosted borders | M2 | Survey Theme 2 | DONE |
| 10 | UI Primitive Input | `resources/js/Components/UI/Input.jsx` dark moss matte input, emerald focus ring, cream text, rose error states | M2 | Survey Theme 2 | DONE |
| 11 | UI Primitive Modal | `resources/js/Components/UI/Modal.jsx` dark glass overlay, obsidian card body, frosted border | M2 | Survey Theme 2 | DONE |
| 12 | Portfolio ProjectCard | `resources/js/Components/Portfolio/ProjectCard.jsx` obsidian surface, aspect frame, hover title transition | M2 | Survey Theme 2 | DONE |
| 13 | Portfolio FilterBar | `resources/js/Components/Portfolio/FilterBar.jsx` dark moss glass inputs and dropdowns, frosted divider | M2 | Survey Theme 2 | DONE |
| 14 | Portfolio ImageGallery | `resources/js/Components/Portfolio/ImageGallery.jsx` dark thumbnail frames, obsidian lightbox modal | M2 | Survey Theme 2 | DONE |
| 15 | Public Home Page | `resources/js/Pages/Home/Index.jsx` hero headline, featured grid, manifesto quote, studio teaser | M2 | Survey Theme 2 | DONE |
| 16 | Public Projects Index Page | `resources/js/Pages/Projects/Index.jsx` header banner, filter bar integration, project grid, empty state | M2 | Survey Theme 2 | DONE |
| 17 | Public Project Show Page | `resources/js/Pages/Projects/Show.jsx` project hero, metadata sidebar, gallery, inquiry CTA | M2 | Survey Theme 2 | DONE |
| 18 | Public Studio Page | `resources/js/Pages/Studio/Index.jsx` narrative hero, philosophy cards, methodology timeline, team, accolades | M2 | Survey Theme 2 | DONE |
| 19 | Public Services Page | `resources/js/Pages/Services/Index.jsx` service cards, deliverables breakdown, process steps | M2 | Survey Theme 2 | DONE |
| 20 | Public Contact Page | `resources/js/Pages/Contact/Index.jsx` contact details, consultation form glass panel, submit button | M2 | Survey Theme 2 | DONE |

## Owned Files (Exclusive Write Ownership for Milestone 2 Worker)
1. `resources/js/Components/UI/Button.jsx`
2. `resources/js/Components/UI/Badge.jsx`
3. `resources/js/Components/UI/Input.jsx`
4. `resources/js/Components/UI/Modal.jsx`
5. `resources/js/Components/Portfolio/ProjectCard.jsx`
6. `resources/js/Components/Portfolio/FilterBar.jsx`
7. `resources/js/Components/Portfolio/ImageGallery.jsx`
8. `resources/js/Pages/Home/Index.jsx`
9. `resources/js/Pages/Projects/Index.jsx`
10. `resources/js/Pages/Projects/Show.jsx`
11. `resources/js/Pages/Studio/Index.jsx`
12. `resources/js/Pages/Services/Index.jsx`
13. `resources/js/Pages/Contact/Index.jsx`

## Strict Preservation Rules
1. **Structural Layout Preservation**:
   - Zero layout shifts. Grid classes (`grid-cols-*`), max-width wrappers (`max-w-*`), padding, flex directions, and hierarchy remain 100% unaltered.
2. **Prop Interface Contracts**:
   - `Button`: `variant`, `size`, `as`, `href`, `className`
   - `Badge`: `variant`, `className`
   - `Input`: `label`, `error`, `multiline`, `required`, `className`
   - `Modal`: `show`, `isOpen`, `onClose`, `title`, `maxWidth`, `children`
   - `ProjectCard`: `project`, `priority`, `delay`
   - `FilterBar`: `filters`, `onFilterChange`
   - `ImageGallery`: `images`, `title`
3. **Responsive Breakpoints**:
   - Zero horizontal scrollbar across 320px–480px (mobile), 768px (tablet), 1024px+ (desktop).
4. **Behavioral Integrity**:
   - Framer Motion animations (`InView`, `AnimatePresence`), hover scaling (1.05 on project images), modal toggle, filter debouncing, and contact form submission remain fully functional.

## Gate Verification Summary
- **Iteration 1**:
  - Reviewer 1: APPROVE
  - Reviewer 2: APPROVE (noted advisory on Modal show/isOpen prop aliasing)
  - Challenger 1: APPROVE (86/86 SSR assertions passed)
  - Challenger 2: REQUEST_CHANGES (Input.jsx border cascade collision between border-white/15 and border-rose-500/80)
  - Auditor: CLEAN
- **Iteration 2 Remediation**:
  - Worker Iteration 2 resolved Input.jsx cascade collision via mutually exclusive class branching and harmonized Modal.jsx show/isOpen props.
  - Reviewer Iteration 2: APPROVE
  - Challenger Iteration 2: APPROVE (38/38 SSR assertions, 409 project tests passed)
  - Forensic Auditor Iteration 2: CLEAN
- **Final Gate Result**: **PASS**
