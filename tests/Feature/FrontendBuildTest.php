<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendBuildTest extends TestCase
{
    /**
     * Tier 1: Verify all 5 UI components exist under resources/js/Components/UI/.
     * Specification R3.
     */
    public function test_all_ui_components_exist(): void
    {
        $base = resource_path('js/Components/UI/');

        $expectedFiles = [
            'Button.jsx',
            'Badge.jsx',
            'Modal.jsx',
            'Input.jsx',
            'InView.jsx',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists(
                $base . $file,
                "Required UI component '{$file}' is missing at {$base}{$file}"
            );
        }
    }

    /**
     * Tier 1: Verify all 3 Navigation components exist under resources/js/Components/Navigation/.
     * Specification R3.
     */
    public function test_all_navigation_components_exist(): void
    {
        $base = resource_path('js/Components/Navigation/');

        $expectedFiles = [
            'Navbar.jsx',
            'MobileMenu.jsx',
            'Footer.jsx',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists(
                $base . $file,
                "Required Navigation component '{$file}' is missing at {$base}{$file}"
            );
        }
    }

    /**
     * Tier 1: Verify all 3 Portfolio components exist under resources/js/Components/Portfolio/.
     * Specification R3.
     */
    public function test_all_portfolio_components_exist(): void
    {
        $base = resource_path('js/Components/Portfolio/');

        $expectedFiles = [
            'ProjectCard.jsx',
            'FilterBar.jsx',
            'ImageGallery.jsx',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists(
                $base . $file,
                "Required Portfolio component '{$file}' is missing at {$base}{$file}"
            );
        }
    }

    /**
     * Tier 1: Verify PublicLayout.jsx exists under resources/js/Layouts/.
     * Specification R3.
     */
    public function test_public_layout_component_exists(): void
    {
        $path = resource_path('js/Layouts/PublicLayout.jsx');
        $this->assertFileExists($path, "Required layout 'PublicLayout.jsx' is missing at {$path}");
    }

    /**
     * Tier 1: Verify all 6 Page components exist under resources/js/Pages/.
     * Specification R3.
     */
    public function test_all_page_components_exist(): void
    {
        $pages = [
            'Home/Index.jsx',
            'Projects/Index.jsx',
            'Projects/Show.jsx',
            'Studio/Index.jsx',
            'Services/Index.jsx',
            'Contact/Index.jsx',
        ];

        foreach ($pages as $page) {
            $path = resource_path('js/Pages/' . $page);
            $this->assertFileExists($path, "Required page component '{$page}' is missing at {$path}");
        }
    }

    /**
     * Tier 1 / R4 Motion: Verify InView.jsx implements Framer Motion with blur reveal variants and once=true.
     */
    public function test_in_view_component_implements_motion_blur_specification(): void
    {
        $path = resource_path('js/Components/UI/InView.jsx');
        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertStringContainsString('framer-motion', $content);
        $this->assertStringContainsString('useInView', $content);
        $this->assertStringContainsString('blur', $content);
        $this->assertStringContainsString('once', $content);
    }

    /**
     * Tier 1 / R4 Portfolio: Verify FilterBar.jsx supports sorting without categories.
     */
    public function test_filter_bar_implements_sorting_and_omits_categories(): void
    {
        $path = resource_path('js/Components/Portfolio/FilterBar.jsx');
        $this->assertFileExists($path);

        $content = file_get_contents($path);

        // Must support newest, oldest, area_desc / Largest Area
        $this->assertTrue(
            str_contains($content, 'newest') || str_contains($content, 'Newest'),
            'FilterBar must support Newest sort option.'
        );
        $this->assertTrue(
            str_contains($content, 'oldest') || str_contains($content, 'Oldest'),
            'FilterBar must support Oldest sort option.'
        );
        $this->assertTrue(
            str_contains($content, 'area_desc') || str_contains($content, 'Largest Area'),
            'FilterBar must support Largest Area Size in m² sort option.'
        );

        // Strict: No categories
        $this->assertStringNotContainsString('category', strtolower($content), 'FilterBar must not contain category filtering.');
    }

    /**
     * Tier 1 / R4 Lightbox: Verify Modal.jsx implements escape key listener and close functionality.
     */
    public function test_modal_component_implements_lightbox_accessibility(): void
    {
        $path = resource_path('js/Components/UI/Modal.jsx');
        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertStringContainsString('Escape', $content);
        $this->assertStringContainsString('onClose', $content);
    }

    /**
     * Tier 1 / Package Manifest: Verify package.json specifies required modern frontend packages.
     */
    public function test_package_json_includes_required_stack(): void
    {
        $packageJsonPath = base_path('package.json');
        $this->assertFileExists($packageJsonPath, 'package.json must exist at project root.');

        $json = json_decode(file_get_contents($packageJsonPath), true);
        $deps = array_merge(
            $json['dependencies'] ?? [],
            $json['devDependencies'] ?? []
        );

        $this->assertArrayHasKey('@inertiajs/react', $deps);
        $this->assertArrayHasKey('react', $deps);
        $this->assertArrayHasKey('react-dom', $deps);
        $this->assertArrayHasKey('framer-motion', $deps);
        $this->assertArrayHasKey('lucide-react', $deps);
    }
}
