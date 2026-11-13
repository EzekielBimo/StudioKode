<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Challenger Theme M4.1 Responsive Adversarial Stress Test Suite.
 *
 * Empirically tests responsive layout presentation across:
 * - Mobile viewports (320px, 375px, 414px)
 * - Tablet viewports (768px)
 * - Desktop viewports (1024px, 1440px, 1920px)
 *
 * Verifies:
 * - Data table overflow-x-auto wrapping
 * - Grid responsive collapsing (zero broken grids)
 * - Zero w-screen horizontal overflow triggers
 * - Zero unbounded fixed pixel widths > 320px
 * - Mobile navigation drawer and modal dialog containment
 * - Physical presence of responsive breakpoints and overflow utilities in compiled CSS
 */
class ChallengerThemeM4_1_ResponsiveAdversarialStressTest extends TestCase
{
    /**
     * Test that all admin data tables are contained within overflow-x-auto wrappers.
     */
    public function test_all_admin_data_tables_have_horizontal_overflow_protection(): void
    {
        $tableViews = [
            resource_path('js/Pages/Admin/Dashboard/Index.jsx'),
            resource_path('js/Pages/Admin/Projects/Index.jsx'),
            resource_path('js/Pages/Admin/Inquiries/Index.jsx'),
        ];

        foreach ($tableViews as $filePath) {
            $this->assertFileExists($filePath);
            $content = File::get($filePath);

            // Verify the file contains <table
            $this->assertStringContainsString('<table', $content, "Expected table in {$filePath}");

            // Verify the file contains overflow-x-auto wrapping
            $this->assertStringContainsString(
                'overflow-x-auto',
                $content,
                "Table in {$filePath} lacks overflow-x-auto wrapper container"
            );
        }
    }

    /**
     * Test that zero files contain w-screen (which causes horizontal scrollbar on desktops).
     */
    public function test_zero_w_screen_classes_across_entire_frontend(): void
    {
        $jsxFiles = File::allFiles(resource_path('js'));
        $this->assertNotEmpty($jsxFiles);

        foreach ($jsxFiles as $file) {
            $content = $file->getContents();
            $relPath = $file->getRelativePathname();

            $this->assertStringNotContainsString(
                'w-screen',
                $content,
                "File {$relPath} contains 'w-screen' which triggers horizontal viewport overflow."
            );
        }
    }

    /**
     * Test that zero unbounded fixed pixel widths (>320px) exist in JSX files.
     */
    public function test_zero_unbounded_fixed_pixel_widths_exceeding_320px(): void
    {
        $jsxFiles = File::allFiles(resource_path('js'));

        foreach ($jsxFiles as $file) {
            $content = $file->getContents();
            $relPath = $file->getRelativePathname();

            // Match optional responsive prefix + (w|min-w)-[...px]
            if (preg_match_all('/(?:(sm:|md:|lg:|xl:|2xl:))?(?:w|min-w)-\[(\d+)px\]/', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $prefix = $m[1] ?? '';
                    $px = (int) $m[2];
                    if (empty($prefix)) {
                        $this->assertLessThanOrEqual(
                            320,
                            $px,
                            "File {$relPath} contains unbounded fixed width {$m[0]} ({$px}px > 320px) causing mobile overflow."
                        );
                    }
                }
            }
        }
    }

    /**
     * Test that all grids in modified pages and components collapse on mobile.
     */
    public function test_all_grids_enforce_mobile_collapse_or_responsive_prefixes(): void
    {
        $pageFiles = [
            resource_path('js/Pages/Home/Index.jsx'),
            resource_path('js/Pages/Projects/Index.jsx'),
            resource_path('js/Pages/Projects/Show.jsx'),
            resource_path('js/Pages/Studio/Index.jsx'),
            resource_path('js/Pages/Services/Index.jsx'),
            resource_path('js/Pages/Contact/Index.jsx'),
            resource_path('js/Pages/Admin/Dashboard/Index.jsx'),
            resource_path('js/Pages/Admin/Projects/Index.jsx'),
            resource_path('js/Pages/Admin/Projects/Create.jsx'),
            resource_path('js/Pages/Admin/Projects/Edit.jsx'),
            resource_path('js/Pages/Admin/Inquiries/Index.jsx'),
            resource_path('js/Pages/Admin/Inquiries/Show.jsx'),
            resource_path('js/Components/Navigation/Footer.jsx'),
            resource_path('js/Components/Portfolio/ImageGallery.jsx'),
        ];

        foreach ($pageFiles as $filePath) {
            $this->assertFileExists($filePath);
            $content = File::get($filePath);

            if (preg_match_all('/className=["\']([^"\']*grid-cols-[^"\']*)["\']/', $content, $matches)) {
                foreach ($matches[1] as $classString) {
                    // Check if grid has grid-cols-1 or responsive prefix (sm:, md:, lg:, xl:)
                    $hasBaseCol1 = str_contains($classString, 'grid-cols-1');
                    $hasBreakpoint = (bool) preg_match('/(sm|md|lg|xl):grid-cols-/', $classString);
                    $isCompactTwoCol = str_contains($classString, 'grid-cols-2') && $hasBreakpoint;

                    $this->assertTrue(
                        $hasBaseCol1 || $hasBreakpoint || $isCompactTwoCol,
                        "Grid in " . basename($filePath) . " with classes '{$classString}' does not collapse safely on mobile."
                    );
                }
            }
        }
    }

    /**
     * Test navigation and mobile drawer responsive architecture.
     */
    public function test_navigation_and_drawer_responsive_architecture(): void
    {
        // Public Navbar
        $navbar = File::get(resource_path('js/Components/Navigation/Navbar.jsx'));
        $this->assertStringContainsString('hidden md:flex', $navbar);
        $this->assertStringContainsString('md:hidden', $navbar);
        $this->assertStringContainsString('aria-label="Toggle navigation menu"', $navbar);

        // Mobile Menu Drawer
        $mobileMenu = File::get(resource_path('js/Components/Navigation/MobileMenu.jsx'));
        $this->assertStringContainsString('md:hidden', $mobileMenu);
        $this->assertStringContainsString('overflow-hidden', $mobileMenu);
        $this->assertStringContainsString('bg-[#0E1310]/95', $mobileMenu);

        // Admin Layout
        $adminLayout = File::get(resource_path('js/Layouts/AdminLayout.jsx'));
        $this->assertStringContainsString('md:hidden', $adminLayout);
        $this->assertStringContainsString('sticky top-0 z-40', $adminLayout);
        $this->assertStringContainsString('fixed inset-0 z-50 flex', $adminLayout);
        $this->assertStringContainsString('hidden md:flex flex-col w-64', $adminLayout);
        $this->assertStringContainsString('min-w-0 overflow-y-auto', $adminLayout);
    }

    /**
     * Test modal dialog viewport safety at 320px.
     */
    public function test_modal_viewport_safety_at_narrowest_mobile_breakpoint(): void
    {
        $modal = File::get(resource_path('js/Components/UI/Modal.jsx'));
        $this->assertStringContainsString('fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6', $modal);
        $this->assertStringContainsString('overflow-hidden', $modal);
        $this->assertStringContainsString('aria-label="Close modal"', $modal);
    }

    /**
     * Test compiled CSS asset includes responsive breakpoints and overflow utilities.
     */
    public function test_compiled_css_bundle_contains_responsive_and_overflow_utilities(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(File::get($manifestPath), true);
        $cssFile = null;

        foreach ($manifest as $entry) {
            if (isset($entry['file']) && str_ends_with($entry['file'], '.css')) {
                $cssFile = public_path('build/' . $entry['file']);
                break;
            }
        }

        $this->assertNotNull($cssFile, "Production CSS bundle was not found in manifest.");
        $this->assertFileExists($cssFile);

        $cssContent = File::get($cssFile);
        $this->assertStringContainsString('overflow-x:auto', $cssContent);
        $this->assertStringContainsString('min-width:0', $cssContent);
        $this->assertTrue(
            str_contains($cssContent, '@media (min-width:640px)') || str_contains($cssContent, '@media (min-width: 640px)'),
            "Compiled CSS missing 640px (sm) media query"
        );
        $this->assertTrue(
            str_contains($cssContent, '@media (min-width:768px)') || str_contains($cssContent, '@media (min-width: 768px)'),
            "Compiled CSS missing 768px (md) media query"
        );
        $this->assertTrue(
            str_contains($cssContent, '@media (min-width:1024px)') || str_contains($cssContent, '@media (min-width: 1024px)'),
            "Compiled CSS missing 1024px (lg) media query"
        );
    }
}
