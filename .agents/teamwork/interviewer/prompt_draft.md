Transform the visual styling of the StudioKode website (public portfolio and admin dashboard) into the "Smoked Obsidian / Dark Moss Glass" theme adhering to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` standards (minimal bloat, concise high-impact modifications without over-engineering), while preserving all existing layouts, structural grids, component hierarchies, and responsive behavior across mobile, tablet, and desktop devices.

Working directory: c:\laragon\www\StudioKode
Integrity mode: development

## Requirements

### R1. Theme Transformation (Smoked Obsidian / Dark Moss Glass)
Transform visual surface styling, backgrounds, glass effects, borders, cards, and typography colors across the entire website without altering existing layouts, structural grids, component hierarchy, or page sections. Apply deep smoked obsidian backgrounds (#0B0E0C / #0E1310 / #121814), translucent dark moss glass surfaces (`rgba(20, 32, 24, 0.65)`, backdrop blur), subtle frosted glass borders (`border-white/10` or `border-emerald-500/15`), luminous moss/gold accents, and high-contrast off-white/cream editorial typography adhering to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` standards.

### R2. Complete Full-Stack UI Coverage
Apply the theme across all public and admin areas:
- Root template: `resources/views/app.blade.php` (dark background, dark selection, meta theme-color).
- Layouts & Navigation: `PublicLayout.jsx`, `Navbar.jsx`, `MobileMenu.jsx`, `Footer.jsx`, `AdminLayout.jsx`.
- Components: `ProjectCard.jsx`, `FilterBar.jsx`, `ImageGallery.jsx`, `Button.jsx`, `Badge.jsx`, `Modal.jsx`, `Input.jsx`.
- Public Pages: `Home/Index.jsx`, `Projects/Index.jsx`, `Projects/Show.jsx`, `Studio/Index.jsx`, `Services/Index.jsx`, `Contact/Index.jsx`.
- Admin Pages: `Admin/Auth/Login.jsx`, `Admin/Dashboard/Index.jsx`, `Admin/Projects/Index.jsx`, `Admin/Projects/Create.jsx`, `Admin/Projects/Edit.jsx`, `Admin/Projects/Gallery.jsx`, `Admin/Inquiries/Index.jsx`, `Admin/Inquiries/Show.jsx`.

### R3. Responsive Presentation & Build Verification
Preserve responsive layout and visual presentation across mobile (320px–480px), tablet (768px), and desktop viewports with zero horizontal overflow or clipping. Ensure Vite production assets build cleanly and all existing automated tests pass.

## Acceptance Criteria

### Build & Verification
- [ ] `npm run build` executes successfully with zero errors.
- [ ] `php artisan test` passes with 100% success across all 389 automated tests.

### Layout Preservation & Responsiveness
- [ ] Existing layouts, grids, component hierarchies, and page sections remain structurally unaltered across all modified pages and components.
- [ ] Pages render cleanly across mobile (320px–480px), tablet (768px), and desktop viewports without unintended horizontal scrolling or broken layouts.

### Visual Aesthetic & Scope
- [ ] Smoked Obsidian / Dark Moss Glass styling is applied across all listed public pages, admin pages, layouts, and components.
- [ ] Root template `resources/views/app.blade.php` includes dark background, dark selection, and updated meta theme-color.
- [ ] Visual design and code modifications adhere to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail` standards with tactile glass surfaces, high-contrast editorial typography, and concise, unbloated implementation.
