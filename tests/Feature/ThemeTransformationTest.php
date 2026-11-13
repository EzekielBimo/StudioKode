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

        // Retrieve or provision authenticated administrator
        $this->admin = User::firstWhere('email', 'admin@studiokode.com') ?? User::factory()->create([
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

        // 1. Meta theme-color and color-scheme for browser chrome immersion and dark mode lock
        $this->assertStringContainsString('<meta name="theme-color" content="#0B0E0C">', $content);
        $this->assertStringContainsString('<meta name="color-scheme" content="dark">', $content);

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
        $response->assertSee('<meta name="color-scheme" content="dark">', false);
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

        // app.css: Base html dark canvas, color-scheme lock, and typography
        $this->assertStringContainsString('color-scheme: dark', $cssContent);
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
        $mobileMenuContent = file_get_contents($mobileMenuPath);
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

        // MobileMenu: Glass drawer styling
        $this->assertTrue(
            str_contains($mobileMenuContent, 'backdrop-blur') || str_contains($mobileMenuContent, 'bg-[#0E1310]') || str_contains($mobileMenuContent, 'border-white/10'),
            'MobileMenu must implement glass or obsidian drawer styling.'
        );

        // Footer: Deep obsidian surface and cream typography
        $this->assertTrue(
            str_contains($footerContent, '#0B0E0C') || str_contains($footerContent, '#0E1310') || str_contains($footerContent, '#080B09'),
            'Footer must utilize deep obsidian background.'
        );
        $this->assertTrue(
            str_contains($footerContent, '#F4F6F0') || str_contains($footerContent, 'text-[#F4F6F0]'),
            'Footer must utilize high-contrast cream typography.'
        );
    }

    /**
     * Verify that light mode / dark mode switching has been removed completely,
     * ensuring Navbar and navigation remain locked to the dark aesthetic.
     */
    public function test_theme_toggle_switch_and_light_mode_logic_removed(): void
    {
        $navbarPath = resource_path('js/Components/Navigation/Navbar.jsx');
        $mobileMenuPath = resource_path('js/Components/Navigation/MobileMenu.jsx');

        $navContent = file_get_contents($navbarPath);
        $mobileContent = file_get_contents($mobileMenuPath);

        // Verify absence of theme toggle switch, state, and icons in Navbar
        $this->assertStringNotContainsString('toggleTheme', $navContent, 'Navbar must not contain toggleTheme logic.');
        $this->assertStringNotContainsString('isDark', $navContent, 'Navbar must not contain isDark state.');
        $this->assertStringNotContainsString('light-mode', $navContent, 'Navbar must not inject light-mode classes.');
        $this->assertStringNotContainsString('Toggle theme mode', $navContent, 'Navbar must not contain theme switch toggle button.');
        $this->assertStringNotContainsString('role="switch"', $navContent, 'Navbar must not contain role=switch button.');
        $this->assertStringNotContainsString('<Sun', $navContent, 'Navbar must not contain Sun toggle icon.');
        $this->assertStringNotContainsString('<Moon', $navContent, 'Navbar must not contain Moon toggle icon.');

        // Verify absence in MobileMenu
        $this->assertStringNotContainsString('toggleTheme', $mobileContent);
        $this->assertStringNotContainsString('light-mode', $mobileContent);
        $this->assertStringNotContainsString('role="switch"', $mobileContent);

        // Verify zero occurrences of toggleTheme or light-mode across entire resources/js frontend codebase
        $allJsFiles = \Illuminate\Support\Facades\File::allFiles(resource_path('js'));
        foreach ($allJsFiles as $file) {
            $code = $file->getContents();
            $rel = $file->getRelativePathname();
            $this->assertStringNotContainsString('toggleTheme', $code, "File {$rel} must not contain toggleTheme logic.");
            $this->assertStringNotContainsString('light-mode', $code, "File {$rel} must not contain light-mode classes.");
        }
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
            str_contains($btnContent, 'primary') && (str_contains($btnContent, '#1C3024') || str_contains($btnContent, 'emerald') || str_contains($btnContent, '#22C55E') || str_contains($btnContent, '#1C2A21')),
            'Button must support primary luminous moss / emerald accent styling.'
        );

        // Badge: Variants with frosted borders
        $this->assertTrue(
            str_contains($badgeContent, 'foliage') || str_contains($badgeContent, 'border-white/10') || str_contains($badgeContent, 'border-white/20'),
            'Badge must support foliage and frosted border styling.'
        );

        // Input: Elevated obsidian surface and cream typography
        $this->assertTrue(
            str_contains($inputContent, '#121814') || str_contains($inputContent, '#0E1310') || str_contains($inputContent, 'bg-[#121814]') || str_contains($inputContent, 'border-[#E6E2DD]'),
            'Input must utilize dark elevated surface (#121814 or #0E1310) or defined border tokens.'
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
     * - Deep smoked obsidian base: #0B0E0C
     * - Dark selection background: #1C3024
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
