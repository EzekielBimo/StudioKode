# Scope: Milestone 1 — theme_foundations_layouts

## Architecture
Milestone 1 establishes the foundational design system and structural shells for StudioKode's transformation into the **"Smoked Obsidian / Dark Moss Glass"** aesthetic.
It covers:
1. Root Blade template: `resources/views/app.blade.php` (<meta name="theme-color" content="#0B0E0C">, dark body/html canvas, dark selection).
2. Tailwind & CSS Tokens: `tailwind.config.js` and `resources/css/app.css` (obsidian/moss utility classes and layer base definitions).
3. Public Layout Shell & Navigation: `PublicLayout.jsx`, `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`.
4. Admin Layout Shell: `AdminLayout.jsx` (with strict retention of `#1C2A21`, `#F9F8F6`, and `#E6E2DD` to satisfy `ChallengerM5_2_FrontendLocalizationStressTest`).

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Root Template Dark Obsidian & Meta Theme-Color | `resources/views/app.blade.php` `<meta name="theme-color" content="#0B0E0C">`, `html` & `body` dark background `#0B0E0C`, text `#F4F6F0`, selection `#1C3024` / `#F4F6F0` | M1 | Survey Theme 1 |
| 2 | Tailwind & CSS Tokens | `tailwind.config.js` and `resources/css/app.css` obsidian & dark moss utilities and selection styles | M1 | Survey Theme 1 |
| 3 | Public Layout Shell & Toasts | `resources/js/Layouts/PublicLayout.jsx` dark obsidian background, emerald/red glass flash banners | M1 | Survey Theme 1 |
| 4 | Public Navbar Glass Header | `resources/js/Components/Navigation/Navbar.jsx` translucent dark moss glass header, brand styling, active indicators | M1 | Survey Theme 1 |
| 5 | Mobile Menu Drawer Glass | `resources/js/Components/Navigation/MobileMenu.jsx` translucent dark moss drawer with frosted borders | M1 | Survey Theme 1 |
| 6 | Footer Obsidian Surface | `resources/js/Components/Navigation/Footer.jsx` deep obsidian background, sage typography, emerald CTA button | M1 | Survey Theme 1 |
| 7 | Admin Layout Shell & Sidebar | `resources/js/Layouts/AdminLayout.jsx` obsidian sidebar, mobile drawer, flash toasts (preserving `#1C2A21`, `#F9F8F6`, `#E6E2DD` tokens) | M1 | Survey Theme 1 & 3 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | `theme_foundations_layouts` | Root template, global CSS, Tailwind tokens, PublicLayout, Navbar, MobileMenu, Footer, AdminLayout (Features 1-7) | none | DONE |

## Interface Contracts
### Root Template ↔ Layouts
- `resources/views/app.blade.php` defines root background `#0B0E0C` and text `#F4F6F0`.
- `<meta name="theme-color" content="#0B0E0C">` sets browser chrome immersion.
- Selection classes `selection:bg-[#1C3024] selection:text-[#F4F6F0]` establish consistent brand selection.

### Layouts ↔ Pages
- `PublicLayout.jsx` wraps all public pages (`Home`, `Projects`, `Studio`, `Services`, `Contact`). Child pages inherit canvas background `#0B0E0C` and text `#F4F6F0`.
- `AdminLayout.jsx` wraps all admin pages. Establishes the `#0E1310` desktop sidebar and `#0B0E0C` main scroll container.
- `AdminLayout.jsx` preserves selection classes referencing `#1C2A21`, `#F9F8F6`, and `#E6E2DD` to ensure `ChallengerM5_2_FrontendLocalizationStressTest` passes without disruption.

## Code Layout (Milestone 1 Exclusively Owned Files)
- `resources/views/app.blade.php`
- `tailwind.config.js`
- `resources/css/app.css`
- `resources/js/Layouts/PublicLayout.jsx`
- `resources/js/Components/Navigation/Navbar.jsx`
- `resources/js/Components/Navigation/MobileMenu.jsx`
- `resources/js/Components/Navigation/Footer.jsx`
- `resources/js/Layouts/AdminLayout.jsx`
