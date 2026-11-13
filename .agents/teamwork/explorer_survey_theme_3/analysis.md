# Technical Analysis: Admin Suite Survey & Theme Transformation Plan
**Theme**: Smoked Obsidian / Dark Moss Glass  
**Scope**: Admin Layout, 8 Admin Page Views, Admin Components, Test Suite & Build Verification  
**Author**: Explorer Survey Theme 3  
**Date**: 2026-11-10  

---

## 1. Executive Summary & Design System Translation

This investigation surveys all administrative interfaces and supporting components in StudioKode, maps current styling against the target **"Smoked Obsidian / Dark Moss Glass"** aesthetic, and identifies critical test suite contracts and build requirements.

### 1.1 Core Aesthetic Tokens for Admin

| Element Type | Current Botanical Token | Smoked Obsidian / Dark Moss Glass Target | Tailwind Classes |
| :--- | :--- | :--- | :--- |
| **Page Ground / Base** | Warm Rice `#F9F8F6` | Deep Obsidian `#0B0E0C` | `bg-[#0B0E0C] text-[#F5F2EB]` |
| **Sidebar Ground** | Forest Green `#1C2A21` | Dark Moss Glass `#0E1310`/95 (Backdrop blur) | `bg-[#0E1310]/95 backdrop-blur-md border-r border-white/10` |
| **Surface / Cards** | Crisp White `#FFFFFF` + `#E6E2DD` | Translucent Moss Glass `rgba(20, 32, 24, 0.65)` | `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_24px_rgba(0,0,0,0.35)]` |
| **Elevated Slabs / Sub-cards** | Limestone `#F9F8F6` / `stone-50` | Smoked Charcoal `#0E1310`/80 | `bg-[#0E1310]/80 border border-white/10` |
| **Frosted Borders** | Limestone `#E6E2DD` | Subtle Glass / Moss Border | `border-white/10` or `border-emerald-500/15` |
| **Primary Typography** | Charcoal `#1A1A1A` | High-contrast Off-white / Cream | `text-[#F5F2EB]` / `text-stone-100` |
| **Muted Typography** | Grayish Olive `#6E6B65` | Muted Sage / Stone | `text-stone-400` / `text-stone-500` |
| **Brand / Accent Link** | Forest Green `#1C2A21` | Luminous Moss / Emerald | `text-emerald-400 hover:text-emerald-300` |
| **Primary Action Buttons** | `#1C2A21` hover `#283c2f` | Luminous Moss Gradient | `bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]` |
| **Form Inputs** | White with `#E6E2DD` border | Dark Moss Glass Input | `bg-[#0E1310]/80 border border-white/10 text-stone-100 placeholder:text-stone-500 focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/50` |

### 1.2 Administrative Clarity & Anti-Slop Principles
In adherence to `/design-taste-frontend`, `/antislop-ui`, and `/ponytail`:
1. **Dose-Capped Glassmorphism (R-10)**: Glass blur (`backdrop-blur-md`) is applied to major surfaces (cards, sidebar, floating nav). Inner elements (inputs, table rows) stay solid matte dark moss (`#0E1310`) to avoid blurring blur and maintain crisp data legibility.
2. **Intentional Radii (R-11)**: Avoid turning inputs or cards into pill shapes. Use consistent structural radius (`rounded` or `rounded-md`) matching existing layout geometry.
3. **High Text Contrast for Administrative Work**: Data cells, table values, input text, and KPI counts must use high-contrast cream/off-white (`text-[#F5F2EB]` / `text-stone-100`), ensuring 7:1+ contrast against dark obsidian/moss grounds.
4. **Zero Layout Distortion**: All grid columns (`lg:grid-cols-3`, `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`), padding, margins, flex layouts, and table markup remain strictly unaltered.
5. **No Shared Component Dependencies**: Admin views do not consume public `Components/UI/` components; they use pure Tailwind and `ProjectGalleryManager.jsx`. Therefore, admin modifications will not introduce regression side-effects into public showcase pages.

---

## 2. Critical Test Suite Contracts & Constraints

An exhaustive scan across all 389 automated tests in `tests/` revealed specific, non-negotiable assertions that must be respected during implementation:

### 2.1 Critical Test Assertion 1: `AdminLayout.jsx` Color Tokens
**Test**: `Tests\Feature\ChallengerM5_2_FrontendLocalizationStressTest::test_admin_layout_botanical_color_tokens_and_accessibility`  
**File**: `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:835-849`  
**Assertions**:
```php
$this->assertStringContainsString('#1C2A21', $content, 'Expected Forest Green (#1C2A21) token');
$this->assertStringContainsString('#F9F8F6', $content, 'Expected Warm Rice/Paper (#F9F8F6) token');
$this->assertStringContainsString('#E6E2DD', $content, 'Expected Limestone Border (#E6E2DD) token');
$this->assertStringContainsString('aria-label=', $content, 'Expected aria-label attributes for accessibility');
```
**Technical Solution**:
When styling `AdminLayout.jsx` with the Smoked Obsidian theme, the worker **MUST retain literal strings `#1C2A21`, `#F9F8F6`, and `#E6E2DD`** in `AdminLayout.jsx`.
- Retain `#1C2A21` as selection background or documented botanical token: `selection:bg-[#1C2A21] selection:text-[#F9F8F6]`
- Retain `#F9F8F6` in selection or high-contrast accent/text
- Retain `#E6E2DD` in muted border or secondary text accent: e.g. `text-[#E6E2DD]/70` or `border-[#E6E2DD]/15`
- Ensure `aria-label=` attributes remain on buttons/nav elements.

### 2.2 Critical Test Assertion 2: `ProjectGalleryManager.jsx` Anti-CLS & Reorder Hooks
**Test**: `Tests\Feature\ChallengerM5_2_FrontendLocalizationStressTest::test_project_gallery_manager_cls_prevention_aspect_ratios`  
**File**: `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:855-873`  
**Assertions**:
```php
$this->assertStringContainsString('aspect-[4/3]', $content);
$this->assertStringContainsString('bg-[#E6E2DD]', $content);
$this->assertStringContainsString('loading="lazy"', $content);
$this->assertStringContainsString('decoding="async"', $content);
$this->assertStringContainsString('Reorder.Group', $content);
$this->assertStringContainsString('Reorder.Item', $content);
```
**Technical Solution**:
The worker **MUST retain `aspect-[4/3]` and `bg-[#E6E2DD]`** on the thumbnail container wrappers (e.g. `<div className="w-20 h-15 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] ...">`), and must keep `loading="lazy"`, `decoding="async"`, `Reorder.Group`, and `Reorder.Item`.

### 2.3 Critical Test Assertion 3: Indonesian Localization Contract
**Test**: `Tests\Feature\ChallengerM5_2_FrontendLocalizationStressTest::test_frontend_admin_components_import_and_consume_indonesian_dictionary`  
**File**: `tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:156-192`  
**Assertions**:
Every single one of the following 10 admin components must continue to import from `lib/id` and access `id.`:
1. `resources/js/Layouts/AdminLayout.jsx`
2. `resources/js/Pages/Admin/Auth/Login.jsx`
3. `resources/js/Pages/Admin/Dashboard/Index.jsx`
4. `resources/js/Pages/Admin/Inquiries/Index.jsx`
5. `resources/js/Pages/Admin/Inquiries/Show.jsx`
6. `resources/js/Pages/Admin/Projects/Index.jsx`
7. `resources/js/Pages/Admin/Projects/Create.jsx`
8. `resources/js/Pages/Admin/Projects/Edit.jsx`
9. `resources/js/Pages/Admin/Projects/Gallery.jsx`
10. `resources/js/Components/Admin/ProjectGalleryManager.jsx`

All Indonesian text labels, tooltips, validation messages, and button labels from `id.js` must remain strictly untouched.

### 2.4 Critical Build Contracts: Manifest & Bundle Size
**Tests**: `ChallengerM4_2_AssetBuildStressTest.php` & `ChallengerM2_2_DashboardMetricsAndRegressionTest.php`  
- Manifest entries must number $\ge 15$.
- Entry bundle `public/build/assets/app-*.js` must remain $\le 50\text{ KB}$ raw.
- Vendor chunks (`vendor-react`, `vendor-inertia`, `vendor-motion`, `vendor-lucide`) must be generated and non-empty.
- All 11 pages (6 public, 5 admin) + CSS must exist in `manifest.json`.
- Windows storage lock note: During PHPUnit test execution on Windows, stale locks in `storage/framework/testing/disks/` can cause `FilesystemIterator` exceptions in `ChallengerM3_1` or `ChallengerM3_2`. Running `Remove-Item -Recurse -Force storage\framework\testing\disks` clears the lock immediately.

---

## 3. Detailed Page-by-Page Survey & Concrete Modifications

### 3.1 `resources/js/Layouts/AdminLayout.jsx`
- **Current Styling**:
  - Ground: `bg-[#F9F8F6] text-[#1A1A1A] selection:bg-[#1C2A21] selection:text-[#F9F8F6]`
  - Sidebar: `bg-[#1C2A21] text-[#F9F8F6] border-r border-[#283c2f]`
  - Nav Active: `bg-white/15 text-white shadow-sm`
  - Nav Inactive: `text-[#E6E2DD]/70 hover:bg-white/10 hover:text-white`
  - Logout: `border border-white/20 text-white hover:bg-red-900/40 hover:border-red-400`
  - Mobile Topbar: `bg-[#1C2A21] text-[#F9F8F6] border-b border-[#283c2f]`
  - Flash Alerts: `bg-[#1C2A21]` (success), `bg-red-800` (error)
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Outer Wrapper:
    `min-h-screen bg-[#0B0E0C] text-[#F5F2EB] font-sans flex flex-col md:flex-row selection:bg-[#1C2A21] selection:text-[#F9F8F6]`
    *(Note: Keeps `#1C2A21` and `#F9F8F6` to satisfy ChallengerM5_2 test)*
  - Desktop Sidebar:
    `hidden md:flex flex-col w-64 bg-[#0E1310]/95 backdrop-blur-md text-[#F5F2EB] border-r border-white/10 shrink-0 sticky top-0 h-screen justify-between p-6 z-30`
  - Sidebar Nav Active Item:
    `bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.15)]`
  - Sidebar Nav Inactive Item:
    `text-[#E6E2DD]/70 hover:bg-white/5 hover:text-white hover:border-white/10`
    *(Note: Keeps `#E6E2DD` to satisfy ChallengerM5_2 test)*
  - Mobile Top Bar:
    `md:hidden bg-[#0E1310]/95 backdrop-blur-md text-[#F5F2EB] border-b border-white/10 px-4 py-3 flex items-center justify-between sticky top-0 z-40`
  - Mobile Drawer:
    `bg-[#0E1310] text-[#F5F2EB] p-6 border-r border-white/10`
  - Flash Alerts:
    - Success: `bg-[#142018]/90 border-b border-emerald-500/30 text-emerald-200 px-6 py-3.5 backdrop-blur-md`
    - Error: `bg-rose-950/80 border-b border-rose-500/30 text-rose-200 px-6 py-3.5 backdrop-blur-md`
  - Accessibility: Retain `aria-label="Toggle Navigation"` and other existing `aria-` labels.

---

### 3.2 `resources/js/Pages/Admin/Auth/Login.jsx`
- **Current Styling**:
  - Background: `bg-[#F9F8F6] text-[#1A1A1A]`
  - Back Button: `text-[#1A1A1A]/70 hover:text-[#1C2A21]`
  - Card: `bg-white border border-[#E6E2DD] shadow-sm p-8 sm:p-10`
  - Brand Heading: `text-[#1A1A1A]`
  - Form Inputs: `bg-white border text-sm text-[#1A1A1A] placeholder:text-[#1A1A1A]/35 border-[#E6E2DD] focus:border-[#1C2A21] focus:ring-[#1C2A21]`
  - Checkbox: `border-[#E6E2DD] text-[#1C2A21] accent-[#1C2A21]`
  - Submit Button: `bg-[#1C2A21] text-[#F9F8F6] hover:bg-[#283c2f]`
  - Notice text: `text-[#1A1A1A]/50 border-t border-[#E6E2DD]/80`
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Ground: `bg-[#0B0E0C] text-[#F5F2EB]`
  - Back Button: `text-stone-400 hover:text-emerald-400 font-medium transition-colors`
  - Card: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] p-8 sm:p-10 relative rounded`
  - Divider Line: `w-12 h-px bg-emerald-500/30 mx-auto my-3`
  - Headings: `text-stone-100 font-serif`, Subtitle: `text-stone-400 font-light`
  - Form Inputs:
    `w-full pl-10 pr-4 py-2.5 bg-[#0E1310]/80 border border-white/10 text-sm text-stone-100 placeholder:text-stone-500 transition-colors focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50 rounded`
  - Password Visibility Toggle: `text-stone-400 hover:text-emerald-400`
  - Remember Me Checkbox: `border-white/20 bg-[#0E1310] text-emerald-500 focus:ring-emerald-500 accent-emerald-500`
  - Submit Button: `w-full inline-flex items-center justify-center bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 py-3 text-xs uppercase tracking-widest font-semibold border border-emerald-400/30 shadow-[0_0_20px_rgba(16,185,129,0.25)] transition-all rounded`
  - Security Notice: `border-t border-white/10 text-stone-400 text-[11px]`

---

### 3.3 `resources/js/Pages/Admin/Dashboard/Index.jsx`
- **Current Styling**:
  - Heading: `text-[#1A1A1A]`, Subtitle: `text-[#6E6B65]`
  - KPI Cards: `bg-white border border-[#E6E2DD] shadow-sm`
  - Recent Inquiries & Projects Cards: `bg-white border border-[#E6E2DD] shadow-sm`
  - Tables: Header `bg-[#F9F8F6] border-b border-[#E6E2DD] text-[#6E6B65]`, Row `divide-y divide-[#E6E2DD] hover:bg-[#F9F8F6]/60`
  - Badges: Light pastel badges (`bg-emerald-100`, `bg-amber-100`, `bg-stone-100`)
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Header: `text-stone-100 font-serif text-3xl sm:text-4xl`, Subtitle: `text-stone-400`
  - KPI Cards:
    `bg-[#142018]/65 backdrop-blur-md border border-white/10 p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3)] hover:border-emerald-500/30 transition-all rounded`
    - Values: `font-serif text-3xl sm:text-4xl text-stone-100 font-semibold tracking-tight`
    - Labels: `text-[11px] uppercase tracking-wider font-semibold text-stone-400`
    - Accents:
      - Total Projects: `bg-emerald-500/15 text-emerald-300 border border-emerald-500/30`
      - Featured Projects: `bg-amber-500/15 text-amber-300 border border-amber-500/30`
      - New Inquiries: `bg-teal-500/15 text-teal-300 border border-teal-500/30`
      - Total Inquiries: `bg-white/5 text-stone-300 border border-white/10`
    - Links: `text-emerald-400 hover:text-emerald-300`
  - Activity Cards (Inquiries & Projects):
    `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded overflow-hidden`
    - Header: `p-5 sm:p-6 border-b border-white/10`, Title: `text-stone-100 font-serif text-xl`, Subtitle: `text-stone-400`
    - Table Header: `bg-[#0E1310]/70 border-b border-white/10 text-stone-400 uppercase text-[10px]`
    - Table Rows: `divide-y divide-white/5 hover:bg-white/[0.03] transition-colors`
    - Data Cells: Primary text `text-stone-100 font-medium`, Secondary text `text-stone-400`
    - Status Badges:
      - baru: `bg-emerald-500/20 text-emerald-300 border border-emerald-500/30`
      - dihubungi: `bg-amber-500/20 text-amber-300 border border-amber-500/30`
      - diarsipkan: `bg-stone-800/60 text-stone-400 border border-white/10`
      - terbit: `bg-emerald-500/20 text-emerald-300 border border-emerald-500/30`
      - draf: `bg-stone-800/60 text-stone-400 border border-white/10`
      - unggulan: `text-amber-300`
    - Detail Links: `text-emerald-400 hover:text-emerald-300`

---

### 3.4 `resources/js/Pages/Admin/Projects/Index.jsx`
- **Current Styling**:
  - Create CTA: `bg-[#1C2A21] text-white hover:bg-[#283c2f]`
  - Toolbar: `bg-white border border-[#E6E2DD]`
  - Search: `bg-[#F9F8F6] border border-[#E6E2DD]`
  - Select: `bg-white border border-[#E6E2DD] text-[#1A1A1A]`
  - Table: `bg-white border border-[#E6E2DD]`, header `bg-[#F9F8F6]/80`, rows `hover:bg-[#F9F8F6]/60`
  - Thumbnail: `w-16 h-12 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-[#E6E2DD]`
  - Actions: Edit/Gallery `text-[#1C2A21] hover:bg-stone-100 border border-[#E6E2DD]`, Delete `text-rose-700 hover:bg-rose-50 border border-rose-200`
  - Pagination: Active `bg-[#1C2A21] text-white`, Inactive `text-[#6E6B65] border-[#E6E2DD]`
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Header Divider: `border-b border-white/10`
  - Create CTA: `bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)] rounded`
  - Toolbar Card: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded p-4 sm:p-5 mb-6`
  - Search Input: `bg-[#0E1310]/80 border border-white/10 rounded text-stone-100 placeholder:text-stone-500 focus:bg-[#0E1310] focus:ring-emerald-500/50 focus:border-emerald-500/50`
  - Filter Select: `bg-[#0E1310]/80 border border-white/10 rounded text-stone-200 focus:ring-emerald-500/50`
  - Table Card: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded overflow-hidden`
  - Table Header: `border-b border-white/10 bg-[#0E1310]/70 text-[11px] uppercase tracking-wider text-stone-400 font-semibold`
  - Table Rows: `divide-y divide-white/5 hover:bg-white/[0.03] transition-colors`
  - Thumbnail Container: `w-16 h-12 aspect-[4/3] rounded overflow-hidden bg-[#0E1310] border border-white/10 shrink-0`
  - Title Link: `text-stone-100 hover:text-emerald-400 font-semibold transition-colors`
  - Gallery Count Link: `bg-white/5 text-emerald-300 border border-white/10 hover:bg-white/10 rounded`
  - Action Buttons:
    - Edit: `text-emerald-400 hover:bg-emerald-500/10 rounded border border-white/10 hover:border-emerald-500/30`
    - Gallery: `text-emerald-400 hover:bg-emerald-500/10 rounded border border-white/10 hover:border-emerald-500/30`
    - Delete: `text-rose-400 hover:bg-rose-500/10 rounded border border-rose-500/30`
  - Pagination Controls:
    - Active: `bg-emerald-600 text-stone-50 border border-emerald-400/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]`
    - Inactive: `text-stone-400 border border-white/10 hover:bg-white/5 hover:text-stone-200`

---

### 3.5 `resources/js/Pages/Admin/Projects/Create.jsx` & `Edit.jsx`
- **Current Styling**:
  - Cards: `bg-white border border-[#E6E2DD] p-6 shadow-sm`
  - Labels: `text-[#1A1A1A]`, Sub-headers: `text-[#1A1A1A]`
  - Inputs & Textareas: `bg-white border border-[#E6E2DD] text-[#1A1A1A] focus:ring-[#1C2A21]`
  - Auto-slug Toggle: `bg-stone-50 text-[#1A1A1A] border-[#E6E2DD]`
  - File Dropzones: `border-2 border-dashed border-[#E6E2DD] hover:border-[#1C2A21]`
  - Previews: `bg-[#E6E2DD] border border-[#E6E2DD]`
  - Primary CTA: `bg-[#1C2A21] text-white hover:bg-[#283c2f]`
  - Cancel Button: `border border-[#E6E2DD] bg-white text-[#6E6B65]`
  - Edit Tabs: Active `border-[#1C2A21] text-[#1C2A21]`, Inactive `text-[#6E6B65]`
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Cards: `bg-[#142018]/65 backdrop-blur-md border border-white/10 p-6 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded`
  - Labels: `text-stone-300 text-xs font-semibold uppercase tracking-wider`
  - Sub-headers: `text-stone-300 text-xs font-semibold uppercase tracking-wider pb-2 border-b border-white/10`
  - Inputs & Textareas:
    `bg-[#0E1310]/80 border border-white/10 rounded text-stone-100 placeholder:text-stone-500 focus:outline-none focus:ring-1 focus:ring-emerald-500/50 focus:border-emerald-500/50`
  - Slug Input: `bg-[#0B0E0C]/90 border border-white/10 font-mono text-stone-300 rounded`
  - File Dropzones: `border-2 border-dashed border-white/15 hover:border-emerald-500/50 bg-[#0E1310]/40 rounded-lg p-5 text-center transition-colors cursor-pointer`
  - Previews: `bg-[#0E1310] border border-white/10 rounded overflow-hidden`
  - Primary CTA: `w-full py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 text-xs font-semibold rounded border border-emerald-400/30 shadow-[0_0_20px_rgba(16,185,129,0.25)] transition-all`
  - Cancel Button: `border border-white/10 bg-white/5 text-stone-300 hover:bg-white/10 hover:text-white rounded`
  - Edit Tabs:
    - Active: `border-emerald-400 text-emerald-300`
    - Inactive: `border-transparent text-stone-400 hover:text-stone-200`
    - Gallery Count Pill: `bg-white/10 text-emerald-300 border border-white/10`

---

### 3.6 `resources/js/Pages/Admin/Projects/Gallery.jsx`
- **Current Styling**:
  - Page wrapper around `ProjectGalleryManager`. Header has `border-b border-[#E6E2DD]`. Back button `text-[#6E6B65] hover:text-[#1C2A21]`. Details link `border border-[#E6E2DD] bg-white text-[#1C2A21]`.
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Header: `border-b border-white/10 pb-6 mb-6`
  - Title: `text-stone-100 font-serif text-2xl sm:text-3xl`, Subtitle: `text-stone-400`
  - Back Button: `text-stone-400 hover:text-emerald-400 hover:bg-white/5 rounded transition-colors`
  - Details Link: `border border-white/10 bg-white/5 text-emerald-300 hover:bg-white/10 rounded transition-colors`

---

### 3.7 `resources/js/Components/Admin/ProjectGalleryManager.jsx`
- **Current Styling**:
  - Cards: `bg-white border border-[#E6E2DD]`
  - Dropzone: `border-2 border-dashed border-[#E6E2DD] hover:border-[#1C2A21] bg-[#F9F8F6]/50`
  - Staged Queue Items: `bg-stone-50 border border-[#E6E2DD]`
  - Reorder Items: `bg-white border border-[#E6E2DD] p-3 sm:p-4 rounded shadow-sm hover:shadow-md`
  - Drag handle: `text-[#6E6B65] hover:text-[#1C2A21]`
  - Actions: Edit `text-[#1C2A21] hover:bg-stone-100`, Delete `text-rose-700 hover:bg-rose-50 border-rose-200`
  - Save Order: `bg-[#1C2A21] text-white hover:bg-[#283c2f]`
- **Target Styling ("Smoked Obsidian / Dark Moss Glass") & Contract Preservation**:
  - Container Cards: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded p-6`
  - Dropzone:
    `border-2 border-dashed border-white/15 hover:border-emerald-500/50 bg-[#0E1310]/40 rounded-lg p-6 sm:p-8 text-center transition-colors`
    When dragging: `border-emerald-400 bg-emerald-500/15`
    Browse Button: `bg-emerald-600 hover:bg-emerald-500 text-stone-50 border border-emerald-400/30 rounded`
  - Staged Queue Items:
    `bg-[#0E1310]/80 border border-white/10 text-stone-100 rounded p-3`
    Thumbnail: `w-16 h-12 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-white/10 shrink-0`
    *(MUST keep `aspect-[4/3]` and `bg-[#E6E2DD]` for ChallengerM5_2)*
    Caption Input: `bg-[#0B0E0C] border border-white/10 text-stone-100 placeholder:text-stone-500 focus:border-emerald-500/50 rounded`
    Upload Button: `bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.2)] rounded`
  - Reorder List Item (`GalleryItemRow`):
    `bg-[#0E1310]/80 border border-white/10 hover:border-emerald-500/30 p-3 sm:p-4 rounded shadow-sm hover:shadow-md transition-all`
    Drag Handle: `text-stone-400 hover:text-emerald-400 hover:bg-white/5 rounded`
    Thumbnail: `w-20 h-15 aspect-[4/3] rounded overflow-hidden bg-[#E6E2DD] border border-white/10 shrink-0 relative`
    *(MUST keep `aspect-[4/3]`, `bg-[#E6E2DD]`, `loading="lazy"`, `decoding="async"`, `Reorder.Group`, `Reorder.Item` for ChallengerM5_2)*
    Caption: `text-stone-100 text-xs sm:text-sm`
    Inline Edit Input: `bg-[#0B0E0C] border border-emerald-500/50 text-stone-100 rounded`
    Save Caption Button: `bg-emerald-600 hover:bg-emerald-500 text-white`
    Cancel Edit Button: `bg-stone-800 text-stone-300 hover:bg-stone-700`
    Edit Action Button: `text-emerald-400 hover:bg-emerald-500/10 rounded border border-white/10 hover:border-emerald-500/30`
    Delete Action Button: `text-rose-400 hover:bg-rose-500/10 rounded border border-rose-500/30`
  - Order Changed Banner: `text-amber-300 bg-amber-500/15 border border-amber-500/30 rounded px-2.5 py-1 text-xs`
  - Save Order Button: `bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-stone-50 shadow-[0_0_15px_rgba(16,185,129,0.2)] border border-emerald-400/30 rounded`

---

### 3.8 `resources/js/Pages/Admin/Inquiries/Index.jsx`
- **Current Styling**:
  - Filter Toolbar: `bg-white border border-[#E6E2DD] shadow-sm`
  - Tabs: Active `bg-[#1C2A21] text-white`, Inactive `text-[#6E6B65] hover:text-[#1A1A1A] hover:bg-[#F9F8F6]`
  - Search: `bg-[#F9F8F6] border border-[#E6E2DD] text-[#1A1A1A]`
  - Table: `bg-white border border-[#E6E2DD]`, header `bg-[#F9F8F6] border-b border-[#E6E2DD]`, rows `hover:bg-[#F9F8F6]/60`
  - Badges: `bg-emerald-100`, `bg-amber-100`, `bg-stone-100`
  - Action: Detail `border border-[#E6E2DD] text-[#1C2A21]`, Delete `border border-[#E6E2DD] text-red-600`
  - Pagination: Active `bg-[#1C2A21] text-white`
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Toolbar Card: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded p-4 sm:p-5 mb-6`
  - Tabs:
    - Active: `bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.15)]`
    - Inactive: `text-stone-400 hover:text-stone-200 hover:bg-white/5`
    - Count Pill (Active): `bg-white/10 text-emerald-300`
    - Count Pill (Inactive): `bg-white/5 text-stone-400`
  - Search Input: `w-full pl-9 pr-8 py-2 text-xs bg-[#0E1310]/80 border border-white/10 rounded focus:border-emerald-500/50 focus:ring-emerald-500/50 text-stone-100 placeholder:text-stone-500 transition-colors`
  - Table Card: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_20px_rgba(0,0,0,0.3)] rounded overflow-hidden`
  - Table Header: `bg-[#0E1310]/70 border-b border-white/10 text-stone-400 uppercase text-[10px]`
  - Table Rows: `divide-y divide-white/5 hover:bg-white/[0.03] transition-colors`
  - Client Name: `font-medium text-stone-100 text-sm`
  - Contact Links (Email/Phone): `hover:text-emerald-400 text-stone-400`
  - Location / Area / Budget: `text-stone-200`, `text-stone-400`
  - Status Badges:
    - baru: `bg-emerald-500/20 text-emerald-300 border border-emerald-500/30`
    - dihubungi: `bg-amber-500/20 text-amber-300 border border-amber-500/30`
    - diarsipkan: `bg-stone-800/60 text-stone-400 border border-white/10`
  - Action Buttons:
    - Detail: `inline-flex items-center px-2.5 py-1.5 rounded border border-white/10 text-xs font-medium text-emerald-400 hover:bg-emerald-500/10 hover:border-emerald-500/30 transition-colors`
    - Delete: `p-1.5 rounded border border-rose-500/30 text-rose-400 hover:bg-rose-500/10 transition-colors`
  - Pagination Controls:
    - Active: `bg-emerald-600 text-stone-50 border border-emerald-400/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]`
    - Inactive: `text-stone-400 border border-white/10 hover:bg-white/5 hover:text-stone-200`

---

### 3.9 `resources/js/Pages/Admin/Inquiries/Show.jsx`
- **Current Styling**:
  - Back Link: `text-[#6E6B65] hover:text-[#1C2A21]`
  - Status Toggle Buttons:
    - Mark new: `border border-[#E6E2DD] bg-white text-[#1A1A1A] hover:bg-emerald-50`
    - Mark contacted: `border border-amber-300 bg-amber-50 text-amber-900`
    - Mark archived: `border border-[#E6E2DD] bg-white text-[#6E6B65]`
    - Delete: `border border-red-200 bg-red-50 text-red-700`
  - Main Detail Card: `bg-white border border-[#E6E2DD] shadow-sm p-6 sm:p-8`
  - Client Name: `text-[#1A1A1A] font-serif`
  - Section Headers: `text-[#1C2A21] font-semibold uppercase tracking-wider`
  - Field Labels: `text-[#6E6B65] text-[11px]`
  - Message Container: `bg-[#F9F8F6] border border-[#E6E2DD] text-[#1A1A1A]`
- **Target Styling ("Smoked Obsidian / Dark Moss Glass")**:
  - Back Link: `text-stone-400 hover:text-emerald-400 uppercase tracking-wider text-xs font-medium transition-colors`
  - Status Action Buttons:
    - Mark new: `border border-white/10 bg-white/5 text-stone-200 hover:bg-emerald-500/15 hover:text-emerald-300 hover:border-emerald-500/30 transition-colors`
    - Mark contacted: `border border-amber-500/30 bg-amber-500/15 text-amber-300 hover:bg-amber-500/25 transition-colors`
    - Mark archived: `border border-white/10 bg-white/5 text-stone-400 hover:bg-white/10 hover:text-stone-200 transition-colors`
    - Delete: `border border-rose-500/30 bg-rose-950/40 text-rose-300 hover:bg-rose-900/40 transition-colors`
  - Main Detail Card: `bg-[#142018]/65 backdrop-blur-md border border-white/10 shadow-[0_4px_24px_rgba(0,0,0,0.35)] rounded p-6 sm:p-8 mb-6`
  - Client Name: `text-stone-100 font-serif text-3xl sm:text-4xl tracking-tight`
  - Timestamp: `text-stone-400 text-xs`
  - Section Headers: `text-emerald-300 text-xs uppercase tracking-wider font-semibold mb-4 flex items-center`
  - Field Labels: `text-stone-400 text-[11px] uppercase tracking-wider font-medium`
  - Field Values: `text-stone-100 font-medium`
  - Contact Links: `text-emerald-400 hover:underline font-medium`
  - Message Container: `bg-[#0E1310]/80 border border-white/10 p-5 sm:p-6 text-sm text-stone-200 leading-relaxed whitespace-pre-wrap rounded`

---

## 4. Verification & Testing Playbook for Workers

### 4.1 Asset Build Verification
1. Command:
   ```powershell
   npm run build
   ```
2. Acceptance Criteria:
   - Exit code: 0
   - Manifest generated at `public/build/manifest.json` with $\ge 15$ entries.
   - Entry bundle `public/build/assets/app-*.js` size $\le 50\text{ KB}$ raw.
   - Vendor chunks (`vendor-react`, `vendor-inertia`, `vendor-motion`, `vendor-lucide`) present and within bounds:
     - `vendor-react`: 40 KB – 300 KB
     - `vendor-inertia`: 40 KB – 300 KB
     - `vendor-motion`: 30 KB – 300 KB
     - `vendor-lucide`: 5 KB – 100 KB
   - All 5 Admin pages present in manifest.

### 4.2 Automated Test Suite Verification
1. Command:
   ```powershell
   php artisan test
   ```
2. Windows Storage Lock Cleanup (Run if any disk iterator lock occurs):
   ```powershell
   if (Test-Path storage\framework\testing\disks) { Remove-Item -Recurse -Force storage\framework\testing\disks }
   ```
3. Targeted Tests for Admin & Frontend:
   ```powershell
   # 1. Frontend build structure test
   php artisan test --filter=FrontendBuildTest

   # 2. Asset build & code splitting stress test
   php artisan test --filter=ChallengerM4_2_AssetBuildStressTest

   # 3. Frontend localization & admin token contracts
   php artisan test --filter=ChallengerM5_2_FrontendLocalizationStressTest

   # 4. Admin Feature tests
   php artisan test --filter=AdminAuthTest
   php artisan test --filter=AdminDashboardTest
   php artisan test --filter=AdminProjectTest
   php artisan test --filter=AdminGalleryTest
   php artisan test --filter=AdminInquiryTest
   ```
4. Acceptance Criteria:
   - 100% pass across all 389 tests.
   - Zero regressions in Inertia contracts or response codes.

---
## 5. Summary Recommendation for Phase 3 Execution
The Admin suite transformation can be executed cleanly and rapidly with zero component spillover into public layouts. By retaining the exact string tokens in `AdminLayout.jsx` (`#1C2A21`, `#F9F8F6`, `#E6E2DD`) and `ProjectGalleryManager.jsx` (`aspect-[4/3]`, `bg-[#E6E2DD]`), the implementation worker will guarantee 100% test suite passing while bringing the admin dashboard into a gorgeous, high-contrast Dark Moss Glass aesthetic.
