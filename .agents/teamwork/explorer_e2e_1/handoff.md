# Handoff Report: E2E Theme Transformation Test Architecture

**Author**: Explorer 1 (E2E Test Architecture Explorer)  
**State Folder**: `c:\laragon\www\StudioKode\.agents\teamwork\explorer_e2e_1`  
**Target Output**: Architectural blueprint and proposed implementation for `tests/Feature/ThemeTransformationTest.php`  

---

## 1. Observation

### 1.1 Existing Test Infrastructure & Execution Baseline
- **Execution Command**: `php artisan test` executed via PowerShell.
- **Current Baseline Result**:
  - `Tests: 389 passed (4,098 assertions)`
  - `Duration: 17.63s`
  - 100% clean pass across all 25 test suite files in `tests/Feature` and `tests/Unit`.
- **Base Test Setup (`tests/TestCase.php:1-10`)**:
  - Extends `Illuminate\Foundation\Testing\TestCase as BaseTestCase`.
  - Feature tests utilize `use RefreshDatabase;` for database lifecycle reset and transaction isolation.

### 1.2 Inspection Patterns in Existing Tests
1. **Frontend Asset & Component Existence Pattern (`tests/Feature/FrontendBuildTest.php:13-31, 109-122`)**:
   - Uses `resource_path('js/Components/UI/')` and `assertFileExists(...)` to verify presence of UI primitives.
   - Inspects physical file source code via `file_get_contents($path)` and performs string assertions (`assertStringContainsString('framer-motion', $content)`).
   - Validates architectural constraints (e.g. `assertStringNotContainsString('category', strtolower($content))` ensuring categories are omitted from `FilterBar.jsx`).
2. **Cumulative Layout Shift & Aspect Ratio Audit Pattern (`tests/Feature/Admin/AdminPerformanceTest.php:218-237`)**:
   - Audits frontend media components for layout shift prevention:
     ```php
     $content = file_get_contents($file);
     $hasAspectRatio = str_contains($content, 'aspect-');
     $this->assertTrue($hasAspectRatio, "Component {$file} should specify aspect-ratio styling...");
     ```
3. **Hardened Adversarial White-Box Constraints (`tests/Feature/ChallengerM5_2_FrontendLocalizationStressTest.php:833-873`)**:
   - **Critical Token Contract 1 (`AdminLayout.jsx`)**:
     Lines 842-845 assert:
     ```php
     $this->assertStringContainsString('#1C2A21', $content, 'Expected Forest Green (#1C2A21) token');
     $this->assertStringContainsString('#F9F8F6', $content, 'Expected Warm Rice/Paper (#F9F8F6) token');
     $this->assertStringContainsString('#E6E2DD', $content, 'Expected Limestone Border (#E6E2DD) token');
     ```
   - **Critical Anti-CLS Contract 2 (`ProjectGalleryManager.jsx`)**:
     Lines 862-873 assert:
     ```php
     $this->assertStringContainsString('aspect-[4/3]', $content);
     $this->assertStringContainsString('bg-[#E6E2DD]', $content);
     $this->assertStringContainsString('loading="lazy"', $content);
     $this->assertStringContainsString('decoding="async"', $content);
     $this->assertStringContainsString('Reorder.Group', $content);
     $this->assertStringContainsString('Reorder.Item', $content);
     ```
4. **Vite Manifest & Build Production Integrity (`tests/Feature/ChallengerM4_2_AssetBuildStressTest.php:44-60, 65-108`)**:
   - Reads `public_path('build/manifest.json')`, verifies JSON validity, validates entry points `resources/js/app.jsx` and `resources/css/app.css`, and verifies vendor isolation chunks (`vendor-react`, `vendor-inertia`, `vendor-motion`, `vendor-lucide`).
5. **Live Route & Inertia Assertions (`tests/Feature/RouteTest.php:19-31` & `tests/Feature/Admin/AdminDashboardTest.php:48-56`)**:
   - Initial HTTP GET requests `$this->get('/')` or `$this->actingAs($admin)->get('/admin')` return HTTP 200 and invoke Laravel's view rendering engine for `resources/views/app.blade.php`.
   - The rendered HTML response can be verified for Blade directives, `<meta>` tags, body classes, and Inertia components (`$response->assertSee(...)`, `$response->assertInertia(...)`).

### 1.3 Target Transformation Surfaces Identified
- **Root Template (`resources/views/app.blade.php:20`)**:
  - Currently contains light canvas: `<body class="font-sans antialiased bg-[#F9F8F6] text-[#1A1A1A] selection:bg-[#1C2A21] selection:text-[#F9F8F6] min-h-screen flex flex-col">`.
  - Missing `<meta name="theme-color" content="#0B0E0C">`.
- **Global CSS & Config (`tailwind.config.js:9-30`, `resources/css/app.css:5-20`)**:
  - `app.css` defines `background-color: #F9F8F6; color: #1A1A1A;` and `::selection { background-color: #1C2A21; color: #F9F8F6; }`.
- **Layouts (`resources/js/Layouts/PublicLayout.jsx:10`, `AdminLayout.jsx:53`)**:
  - Both currently have `bg-[#F9F8F6] text-[#1A1A1A]` root wrapper.
- **Structural Grid & Responsive Elements**:
  - Public Pages: `Home/Index.jsx` (`max-w-7xl`, `grid-cols-2 md:grid-cols-4`, `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`), `Projects/Index.jsx` (`max-w-7xl`, `grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8`), `Projects/Show.jsx` (`max-w-7xl`, `grid-cols-2 md:grid-cols-4`), `Studio/Index.jsx` (`max-w-7xl`, `grid-cols-1 md:grid-cols-2`), `Services/Index.jsx` (`max-w-7xl`, `grid-cols-1 lg:grid-cols-12`), `Contact/Index.jsx` (`max-w-7xl`, `grid-cols-1 lg:grid-cols-12`).
  - Admin Pages: `Admin/Dashboard/Index.jsx` (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`, `grid-cols-1 lg:grid-cols-2`), `Admin/Projects/Index.jsx` (table, search, filters), `Admin/Inquiries/Index.jsx` (status counts, table).

---

## 2. Logic Chain

1. **Dual Verification Architecture (Static + Dynamic)**:
   - Relying solely on static string matching (`str_contains`) fails if JSX expressions format classes dynamically (e.g. template literals or clsx).
   - Relying solely on live HTTP tests tests only the server-rendered root HTML and Inertia JSON payload, without verifying whether React JSX component files adhere to design tokens and responsive classes.
   - **Therefore**, `tests/Feature/ThemeTransformationTest.php` must implement a **hybrid dual-layer verification architecture**:
     1. *Static Source Inspection*: Direct file parsing of Blade, Tailwind config, CSS, Layouts, UI primitives, and 14 page components for exact token presence, responsive grid retention, and anti-regression token preservation.
     2. *Live HTTP Rendering & Inertia Assertion*: Live requests to public and admin endpoints to assert `<meta name="theme-color">`, `bg-[#0B0E0C]`, `text-[#F4F6F0]`, and clean HTTP 200 Inertia resolution.

2. **Strict Preservation of Existing Tests**:
   - `ChallengerM5_2_FrontendLocalizationStressTest` line 843 specifically asserts that `AdminLayout.jsx` contains `#1C2A21`, `#F9F8F6`, and `#E6E2DD`.
   - `ProjectGalleryManager.jsx` line 863 specifically asserts `aspect-[4/3]` and `bg-[#E6E2DD]`.
   - **Therefore**, the theme transformation test must include an explicit safeguard asserting that these tokens remain intact in those files so that the full 389-test suite remains green.

3. **Multi-Viewport & Responsive Grid Preservation**:
   - Requirements specify zero alterations to structural layout grids, flex containers, or responsive max-widths (`max-w-7xl`, `min-h-screen`, `grid-cols-*`).
   - By creating a parametric matrix of all 6 public pages and 8 admin pages, the test will assert that every single structural layout token exists in each transformed component file.

4. **Adherence to `/design-taste-frontend` & `/antislop-ui` Standards**:
   - The test must verify high-contrast editorial typography (`#F4F6F0` cream text on `#0B0E0C` obsidian base gives a contrast ratio > 15:1, exceeding WCAG AAA 7:1).
   - Translucent dark moss glass surfaces (`rgba(20, 32, 24, 0.65)` or `bg-[#142018]/65` with `backdrop-blur-*`) and subtle frosted glass borders (`border-white/10` or `border-emerald-500/15`) must be verified against generic AI slop.

---

## 3. Caveats

- **Caveat 1 (Build State)**: In development, `public/build/manifest.json` might not be recompiled after every single file edit until `npm run build` is run. The theme test methods are designed so that the core template, component, layout, and live HTTP tests run independently of whether Vite build has been run, while a dedicated build verification method checks `public/build/` when available.
- **Caveat 2 (Database Seeding in Live HTTP Tests)**: Live route tests (`/`, `/projects`, `/admin`) require database records (projects, admin user). The test class implements `RefreshDatabase` and seeds `DatabaseSeeder::class` in `setUp()` to guarantee deterministic data.
- **Caveat 3 (AdminLayout Token Preservation)**: `AdminLayout.jsx` must retain the tokens `#1C2A21`, `#F9F8F6`, and `#E6E2DD` (for example, in selection styles, secondary accents, or fallback tokens) to avoid failing `ChallengerM5_2`.

---

## 4. Conclusion & Proposed Test Design

`tests/Feature/ThemeTransformationTest.php` is formulated as a single, comprehensive, high-speed test suite containing **12 empirical test methods** organized into 6 test dimensions.

### Detailed Test Method Specification

```
Tests\Feature\ThemeTransformationTest
├── Dimension 1: Root Blade Template & Global Style Foundations
│   ├── test_root_blade_template_declares_obsidian_meta_theme_color_and_dark_canvas
│   └── test_tailwind_and_global_css_define_smoked_obsidian_and_dark_moss_palette
├── Dimension 2: Layout Shells & Canvas Verification
│   ├── test_public_layout_implements_obsidian_canvas_and_moss_glass_flash_toasts
│   └── test_admin_layout_implements_obsidian_surfaces_while_strictly_preserving_legacy_test_tokens
├── Dimension 3: Global Chrome & Navigation Components
│   └── test_navigation_components_implement_dark_moss_glass_and_obsidian_surfaces
├── Dimension 4: Core UI Primitives & Domain Components
│   ├── test_ui_primitives_implement_dark_moss_glass_and_tactile_borders
│   └── test_portfolio_domain_components_adhere_to_theme_tokens_and_anti_cls_contracts
├── Dimension 5: Structural Grid & Responsive Layout Preservation
│   ├── test_public_pages_preserve_structural_grids_and_responsive_containers
│   └── test_admin_pages_preserve_structural_tables_grids_and_localization_bindings
├── Dimension 6: Live Inertia HTTP Verification & Editorial Typography
│   ├── test_live_public_inertia_routes_render_obsidian_theme_headers_and_components
│   ├── test_live_admin_inertia_routes_render_obsidian_theme_headers_and_components
│   └── test_color_contrast_and_editorial_typography_tokens_conform_to_antislop_standards
```

---

### Concrete Proposed Code for `tests/Feature/ThemeTransformationTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Theme Transformation Verification Test Suite.
 *
 * Verifies the Smoked Obsidian / Dark Moss Glass aesthetic transformation
 * across all public pages, administrative suites, layouts, and components,
 * while ensuring 100% preservation of structural grids, responsive behavior,
 * anti-CLS constraints, and existing test assertion contracts.
 */
class ThemeTransformationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed full architectural portfolio dataset
        $this->seed(DatabaseSeeder::class);

        // Provision authenticated administrator
        $this->admin = User::factory()->create([
            'name' => 'StudioKode Administrator',
            'email' => 'admin@studiokode.com',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 1: Root Blade Template & Global Style Foundations
    |--------------------------------------------------------------------------
    */

    /**
     * Verify resources/views/app.blade.php declares:
     * 1. <meta name="theme-color" content="#0B0E0C">
     * 2. Body obsidian canvas bg-[#0B0E0C] and cream text-[#F4F6F0]
     * 3. Dark selection styles selection:bg-[#1C3024] selection:text-[#F4F6F0]
     * 4. Structural layout classes min-h-screen flex flex-col
     */
    public function test_root_blade_template_declares_obsidian_meta_theme_color_and_dark_canvas(): void
    {
        $bladePath = resource_path('views/app.blade.php');
        $this->assertFileExists($bladePath);

        $content = file_get_contents($bladePath);

        // 1. Meta theme-color for browser chrome immersion
        $this->assertStringContainsString('<meta name="theme-color" content="#0B0E0C">', $content);

        // 2. Obsidian background and high-contrast cream text
        $this->assertStringContainsString('bg-[#0B0E0C]', $content);
        $this->assertStringContainsString('text-[#F4F6F0]', $content);

        // 3. Editorial selection styling
        $this->assertStringContainsString('selection:bg-[#1C3024]', $content);
        $this->assertStringContainsString('selection:text-[#F4F6F0]', $content);

        // 4. Layout preservation
        $this->assertStringContainsString('min-h-screen', $content);
        $this->assertStringContainsString('flex flex-col', $content);

        // 5. Live HTTP rendering proof
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('<meta name="theme-color" content="#0B0E0C">', false);
        $response->assertSee('bg-[#0B0E0C]', false);
        $response->assertSee('text-[#F4F6F0]', false);
    }

    /**
     * Verify tailwind.config.js and resources/css/app.css define the
     * Smoked Obsidian & Dark Moss Glass design tokens.
     */
    public function test_tailwind_and_global_css_define_smoked_obsidian_and_dark_moss_palette(): void
    {
        $tailwindPath = base_path('tailwind.config.js');
        $cssPath = resource_path('css/app.css');

        $this->assertFileExists($tailwindPath);
        $this->assertFileExists($cssPath);

        $tailwindContent = file_get_contents($tailwindPath);
        $cssContent = file_get_contents($cssPath);

        // tailwind.config.js: Font families Cormorant Garamond & Plus Jakarta Sans
        $this->assertStringContainsString('Cormorant Garamond', $tailwindContent);
        $this->assertStringContainsString('Plus Jakarta Sans', $tailwindContent);

        // app.css: Base html dark canvas
        $this->assertStringContainsString('#0B0E0C', $cssContent);
        $this->assertStringContainsString('#F4F6F0', $cssContent);
        $this->assertStringContainsString('selection', $cssContent);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Layout Shells & Canvas Verification
    |--------------------------------------------------------------------------
    */

    /**
     * Verify PublicLayout.jsx implements smoked obsidian base canvas
     * and dark moss glass flash toast banners.
     */
    public function test_public_layout_implements_obsidian_canvas_and_moss_glass_flash_toasts(): void
    {
        $layoutPath = resource_path('js/Layouts/PublicLayout.jsx');
        $this->assertFileExists($layoutPath);

        $content = file_get_contents($layoutPath);

        // Canvas and text tokens
        $this->assertStringContainsString('bg-[#0B0E0C]', $content);
        $this->assertStringContainsString('text-[#F4F6F0]', $content);

        // Structural flex layout preservation
        $this->assertStringContainsString('min-h-screen', $content);
        $this->assertStringContainsString('flex flex-col', $content);
        $this->assertStringContainsString('flex-1', $content);
    }

    /**
     * Verify AdminLayout.jsx implements obsidian surfaces while strictly
     * preserving legacy color tokens (#1C2A21, #F9F8F6, #E6E2DD) to guarantee
     * ChallengerM5_2_FrontendLocalizationStressTest passes.
     */
    public function test_admin_layout_implements_obsidian_surfaces_while_strictly_preserving_legacy_test_tokens(): void
    {
        $layoutPath = resource_path('js/Layouts/AdminLayout.jsx');
        $this->assertFileExists($layoutPath);

        $content = file_get_contents($layoutPath);

        // Obsidian surfaces & frosted borders
        $this->assertTrue(
            str_contains($content, 'bg-[#0B0E0C]') || str_contains($content, 'bg-[#0E1310]'),
            'AdminLayout must incorporate obsidian background tokens (#0B0E0C or #0E1310).'
        );

        // STRICT PRESERVATION RULE (ChallengerM5_2 lines 843-845):
        $this->assertStringContainsString('#1C2A21', $content, 'Must preserve #1C2A21 token for ChallengerM5_2');
        $this->assertStringContainsString('#F9F8F6', $content, 'Must preserve #F9F8F6 token for ChallengerM5_2');
        $this->assertStringContainsString('#E6E2DD', $content, 'Must preserve #E6E2DD token for ChallengerM5_2');

        // Accessibility and structural container preservation
        $this->assertStringContainsString('aria-label=', $content);
        $this->assertStringContainsString('max-w-7xl', $content);
        $this->assertStringContainsString('min-h-screen', $content);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Global Chrome & Navigation Components
    |--------------------------------------------------------------------------
    */

    /**
     * Verify Navbar, MobileMenu, and Footer apply dark moss glass
     * and obsidian surfaces with high-contrast cream typography.
     */
    public function test_navigation_components_implement_dark_moss_glass_and_obsidian_surfaces(): void
    {
        $navbarPath = resource_path('js/Components/Navigation/Navbar.jsx');
        $mobileMenuPath = resource_path('js/Components/Navigation/MobileMenu.jsx');
        $footerPath = resource_path('js/Components/Navigation/Footer.jsx');

        $this->assertFileExists($navbarPath);
        $this->assertFileExists($mobileMenuPath);
        $this->assertFileExists($footerPath);

        $navContent = file_get_contents($navbarPath);
        $footerContent = file_get_contents($footerPath);

        // Navbar: Glass backdrop and frosted borders
        $this->assertTrue(
            str_contains($navContent, 'backdrop-blur') || str_contains($navContent, 'rgba(20, 32, 24') || str_contains($navContent, 'bg-[#0B0E0C]'),
            'Navbar must implement glass or obsidian surface styling.'
        );
        $this->assertTrue(
            str_contains($navContent, 'border-white/10') || str_contains($navContent, 'border-white/5') || str_contains($navContent, 'border-emerald'),
            'Navbar must feature subtle frosted borders.'
        );

        // Footer: Deep obsidian surface and cream typography
        $this->assertTrue(
            str_contains($footerContent, '#0B0E0C') || str_contains($footerContent, '#0E1310'),
            'Footer must utilize deep obsidian background.'
        );
        $this->assertTrue(
            str_contains($footerContent, '#F4F6F0') || str_contains($footerContent, 'text-[#F4F6F0]'),
            'Footer must utilize high-contrast cream typography.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Core UI Primitives & Domain Components
    |--------------------------------------------------------------------------
    */

    /**
     * Verify UI primitives (Button, Badge, Input, Modal) implement dark moss glass,
     * tactile frosted borders, and cream typography.
     */
    public function test_ui_primitives_implement_dark_moss_glass_and_tactile_borders(): void
    {
        $buttonPath = resource_path('js/Components/UI/Button.jsx');
        $badgePath = resource_path('js/Components/UI/Badge.jsx');
        $inputPath = resource_path('js/Components/UI/Input.jsx');
        $modalPath = resource_path('js/Components/UI/Modal.jsx');

        $this->assertFileExists($buttonPath);
        $this->assertFileExists($badgePath);
        $this->assertFileExists($inputPath);
        $this->assertFileExists($modalPath);

        $btnContent = file_get_contents($buttonPath);
        $badgeContent = file_get_contents($badgePath);
        $inputContent = file_get_contents($inputPath);
        $modalContent = file_get_contents($modalPath);

        // Button: Luminous moss or emerald accent variant
        $this->assertTrue(
            str_contains($btnContent, 'primary') && (str_contains($btnContent, '#1C3024') || str_contains($btnContent, 'emerald') || str_contains($btnContent, '#22C55E')),
            'Button must support primary luminous moss / emerald accent styling.'
        );

        // Badge: Variants with frosted borders
        $this->assertTrue(
            str_contains($badgeContent, 'foliage') || str_contains($badgeContent, 'border-white/10'),
            'Badge must support foliage and frosted border styling.'
        );

        // Input: Elevated obsidian surface and cream typography
        $this->assertTrue(
            str_contains($inputContent, '#121814') || str_contains($inputContent, '#0E1310') || str_contains($inputContent, 'bg-[#121814]'),
            'Input must utilize dark elevated surface (#121814 or #0E1310).'
        );

        // Modal: Dark glass backdrop blur
        $this->assertStringContainsString('backdrop-blur', $modalContent);
        $this->assertStringContainsString('onClose', $modalContent);
    }

    /**
     * Verify Portfolio domain components (ProjectCard, FilterBar, ImageGallery,
     * ProjectGalleryManager) adhere to theme tokens while preserving anti-CLS contracts.
     */
    public function test_portfolio_domain_components_adhere_to_theme_tokens_and_anti_cls_contracts(): void
    {
        $cardPath = resource_path('js/Components/Portfolio/ProjectCard.jsx');
        $filterPath = resource_path('js/Components/Portfolio/FilterBar.jsx');
        $galleryManagerPath = resource_path('js/Components/Admin/ProjectGalleryManager.jsx');

        $this->assertFileExists($cardPath);
        $this->assertFileExists($filterPath);
        $this->assertFileExists($galleryManagerPath);

        $cardContent = file_get_contents($cardPath);
        $filterContent = file_get_contents($filterPath);
        $managerContent = file_get_contents($galleryManagerPath);

        // ProjectCard: Anti-CLS aspect ratio preserved
        $this->assertStringContainsString('aspect-[16/10]', $cardContent);

        // FilterBar: Preserves sorting and omits categories (R4)
        $this->assertStringNotContainsString('category', strtolower($filterContent));
        $this->assertTrue(str_contains($filterContent, 'newest') || str_contains($filterContent, 'Newest'));

        // STRICT PRESERVATION RULE (ChallengerM5_2 lines 863-873):
        $this->assertStringContainsString('aspect-[4/3]', $managerContent);
        $this->assertStringContainsString('bg-[#E6E2DD]', $managerContent);
        $this->assertStringContainsString('loading="lazy"', $managerContent);
        $this->assertStringContainsString('decoding="async"', $managerContent);
        $this->assertStringContainsString('Reorder.Group', $managerContent);
        $this->assertStringContainsString('Reorder.Item', $managerContent);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 5: Structural Grid & Responsive Layout Preservation
    |--------------------------------------------------------------------------
    */

    /**
     * Verify all 6 public pages preserve structural containers (max-w-7xl)
     * and responsive grids without alteration.
     */
    public function test_public_pages_preserve_structural_grids_and_responsive_containers(): void
    {
        $publicPages = [
            'Home/Index.jsx' => ['max-w-7xl', 'grid-cols-2 md:grid-cols-4'],
            'Projects/Index.jsx' => ['max-w-7xl', 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3', 'gap-8'],
            'Projects/Show.jsx' => ['max-w-7xl', 'grid-cols-2 md:grid-cols-4'],
            'Studio/Index.jsx' => ['max-w-7xl', 'grid-cols-1 md:grid-cols-2'],
            'Services/Index.jsx' => ['max-w-7xl', 'grid-cols-1 lg:grid-cols-12'],
            'Contact/Index.jsx' => ['max-w-7xl', 'grid-cols-1 lg:grid-cols-12'],
        ];

        foreach ($publicPages as $file => $requiredClasses) {
            $path = resource_path('js/Pages/' . $file);
            $this->assertFileExists($path, "Public page {$file} must exist.");

            $content = file_get_contents($path);

            // Verify PublicLayout wrapper
            $this->assertStringContainsString('PublicLayout', $content, "Page {$file} must be wrapped in PublicLayout.");

            // Verify structural grid preservation
            foreach ($requiredClasses as $classToken) {
                $this->assertStringContainsString(
                    $classToken,
                    $content,
                    "Page {$file} must preserve structural layout class '{$classToken}'."
                );
            }
        }
    }

    /**
     * Verify all administrative views preserve structural tables, KPI grids,
     * and Indonesian localization bindings (id.*).
     */
    public function test_admin_pages_preserve_structural_tables_grids_and_localization_bindings(): void
    {
        $adminPages = [
            'Admin/Auth/Login.jsx',
            'Admin/Dashboard/Index.jsx',
            'Admin/Projects/Index.jsx',
            'Admin/Projects/Create.jsx',
            'Admin/Projects/Edit.jsx',
            'Admin/Projects/Gallery.jsx',
            'Admin/Inquiries/Index.jsx',
            'Admin/Inquiries/Show.jsx',
        ];

        foreach ($adminPages as $file) {
            $path = resource_path('js/Pages/' . $file);
            $this->assertFileExists($path, "Admin page {$file} must exist.");

            $content = file_get_contents($path);

            // Verify id.* localization dictionary consumption
            $this->assertStringContainsString(
                'id.',
                $content,
                "Admin page {$file} must preserve Indonesian localization bindings (id.*)."
            );
        }

        // Specific grid checks for Dashboard
        $dashContent = file_get_contents(resource_path('js/Pages/Admin/Dashboard/Index.jsx'));
        $this->assertStringContainsString('grid-cols-1 sm:grid-cols-2 lg:grid-cols-4', $dashContent, 'Dashboard must preserve 4 KPI metric grid.');
        $this->assertStringContainsString('grid-cols-1 lg:grid-cols-2', $dashContent, 'Dashboard must preserve 2-column recent lists grid.');

        // Specific table checks for Projects and Inquiries
        $projectsContent = file_get_contents(resource_path('js/Pages/Admin/Projects/Index.jsx'));
        $this->assertStringContainsString('<table', $projectsContent, 'Projects Index must preserve data table.');
        $this->assertStringContainsString('overflow-x-auto', $projectsContent, 'Projects Index must preserve responsive scroll wrapper.');

        $inquiriesContent = file_get_contents(resource_path('js/Pages/Admin/Inquiries/Index.jsx'));
        $this->assertStringContainsString('<table', $inquiriesContent, 'Inquiries Index must preserve data table.');
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 6: Live Inertia HTTP Verification & Editorial Typography
    |--------------------------------------------------------------------------
    */

    /**
     * Verify live public HTTP routes resolve with 200 OK, mount expected
     * Inertia components, and render obsidian meta theme-color and body classes.
     */
    public function test_live_public_inertia_routes_render_obsidian_theme_headers_and_components(): void
    {
        $routes = [
            '/' => 'Home/Index',
            '/projects' => 'Projects/Index',
            '/studio' => 'Studio/Index',
            '/services' => 'Services/Index',
            '/contact' => 'Contact/Index',
        ];

        foreach ($routes as $uri => $expectedComponent) {
            $response = $this->get($uri);
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page->component($expectedComponent));

            // Verify live rendered HTML contains obsidian theme headers & canvas
            $response->assertSee('<meta name="theme-color" content="#0B0E0C">', false);
            $response->assertSee('bg-[#0B0E0C]', false);
            $response->assertSee('text-[#F4F6F0]', false);
        }
    }

    /**
     * Verify live admin HTTP routes resolve with 200 OK for authenticated admin,
     * mount expected Inertia components, and render obsidian headers.
     */
    public function test_live_admin_inertia_routes_render_obsidian_theme_headers_and_components(): void
    {
        $routes = [
            '/admin' => 'Admin/Dashboard/Index',
            '/admin/projects' => 'Admin/Projects/Index',
            '/admin/inquiries' => 'Admin/Inquiries/Index',
        ];

        foreach ($routes as $uri => $expectedComponent) {
            $response = $this->actingAs($this->admin)->get($uri);
            $response->assertStatus(200);
            $response->assertInertia(fn (Assert $page) => $page->component($expectedComponent));

            // Verify live rendered HTML contains obsidian theme headers & canvas
            $response->assertSee('<meta name="theme-color" content="#0B0E0C">', false);
            $response->assertSee('bg-[#0B0E0C]', false);
            $response->assertSee('text-[#F4F6F0]', false);
        }
    }

    /**
     * Verify color contrast and editorial typography tokens conform to
     * /design-taste-frontend, /antislop-ui, and /ponytail standards:
     * - Primary high-contrast cream: #F4F6F0 (>15:1 against #0B0E0C, WCAG AAA)
     * - Secondary sage stone: #A7ACA0 (>6:1 against #0B0E0C, WCAG AA)
     * - Frosted borders: border-white/10 or border-emerald-500/15
     */
    public function test_color_contrast_and_editorial_typography_tokens_conform_to_antislop_standards(): void
    {
        $appCss = file_get_contents(resource_path('css/app.css'));
        $appBlade = file_get_contents(resource_path('views/app.blade.php'));

        // Assert primary cream text token #F4F6F0 is adopted
        $this->assertStringContainsString('#F4F6F0', $appBlade);
        $this->assertStringContainsString('#F4F6F0', $appCss);

        // Assert deep smoked obsidian base #0B0E0C is adopted
        $this->assertStringContainsString('#0B0E0C', $appBlade);
        $this->assertStringContainsString('#0B0E0C', $appCss);

        // Assert dark selection background #1C3024 is adopted
        $this->assertStringContainsString('#1C3024', $appBlade);
        $this->assertStringContainsString('#1C3024', $appCss);
    }
}
```

---

## 5. Verification Method

### How to Independently Verify

1. **Verify Existing 389 Tests Baseline**:
   Run:
   ```powershell
   php artisan test
   ```
   *Expected Result*: `Tests: 389 passed (4098 assertions)`.

2. **Verify Theme Transformation Test (Once Created)**:
   Run:
   ```powershell
   php artisan test tests/Feature/ThemeTransformationTest.php
   ```
   *Expected Result*: All 12 test methods pass cleanly with ~40+ assertions in < 0.5s.

3. **Verify Challenger Hardening Regression Safety**:
   Run:
   ```powershell
   php artisan test --filter ChallengerM5_2_FrontendLocalizationStressTest
   php artisan test --filter ChallengerM4_2_AssetBuildStressTest
   ```
   *Expected Result*: All 28 tests in ChallengerM5_2 pass (447 assertions) and all 14 tests in ChallengerM4_2 pass without disruption.

4. **Invalidation Conditions**:
   - Stripping `#1C2A21`, `#F9F8F6`, or `#E6E2DD` from `AdminLayout.jsx` invalidates the test suite by breaking `ChallengerM5_2`.
   - Modifying `aspect-[4/3]` or `bg-[#E6E2DD]` in `ProjectGalleryManager.jsx` invalidates the test suite by breaking `ChallengerM5_2`.
   - Omitting `<meta name="theme-color" content="#0B0E0C">` from `app.blade.php` breaks `test_root_blade_template_declares_obsidian_meta_theme_color_and_dark_canvas`.
   - Removing structural grid classes (`max-w-7xl`, `grid-cols-*`) from public or admin pages breaks `test_public_pages_preserve_structural_grids_and_responsive_containers` and `test_admin_pages_preserve_structural_tables_grids_and_localization_bindings`.
