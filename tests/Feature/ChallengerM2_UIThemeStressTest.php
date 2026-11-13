<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Challenger M2 UI Theme Stress Test Suite.
 *
 * Adversarial empirical testing of Milestone 2 UI primitives, portfolio domain
 * components, and public pages under the Smoked Obsidian / Dark Moss Glass theme.
 */
class ChallengerM2_UIThemeStressTest extends TestCase
{
    /**
     * Test Button.jsx supports all required variants, sizes, and states.
     */
    public function test_button_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/UI/Button.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Required variants
        $this->assertStringContainsString('primary:', $content);
        $this->assertStringContainsString('secondary:', $content);
        $this->assertStringContainsString('outline:', $content);
        $this->assertStringContainsString('outline-light\':', $content);
        $this->assertStringContainsString('ghost:', $content);

        // Theme colors
        $this->assertStringContainsString('#1C2A21', $content, 'Primary variant must use dark moss #1C2A21');
        $this->assertStringContainsString('rgba(20,32,24,0.75)', $content, 'Secondary variant must use moss glass');
        $this->assertStringContainsString('#88B896', $content, 'Outline variant must use moss accent #88B896');
        $this->assertStringContainsString('#F4F2ED', $content, 'Text must use editorial cream #F4F2ED');

        // Focus ring & offset
        $this->assertStringContainsString('focus:ring-[#88B896]', $content);
        $this->assertStringContainsString('focus:ring-offset-[#0B0E0C]', $content);

        // Sizes
        $this->assertStringContainsString('sm:', $content);
        $this->assertStringContainsString('md:', $content);
        $this->assertStringContainsString('lg:', $content);

        // Dynamic element selection (Link vs button)
        $this->assertStringContainsString('if (href)', $content);
        $this->assertStringContainsString('<Link href={href}', $content);
        $this->assertStringContainsString('<button', $content);
        $this->assertStringContainsString('disabled={disabled}', $content);
    }

    /**
     * Test Badge.jsx variants and typography.
     */
    public function test_badge_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/UI/Badge.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('neutral:', $content);
        $this->assertStringContainsString('foliage:', $content);
        $this->assertStringContainsString('outline:', $content);
        $this->assertStringContainsString('outline-light\':', $content);

        $this->assertStringContainsString('rgba(20,32,24,0.65)', $content);
        $this->assertStringContainsString('#152319', $content);
        $this->assertStringContainsString('#88B896', $content);
        $this->assertStringContainsString('border-white/10', $content);
    }

    /**
     * Test Input.jsx multiline, required indicator, error state, and contrast.
     */
    public function test_input_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/UI/Input.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Elevated dark surface and cream text
        $this->assertStringContainsString('bg-[#121814]/90', $content);
        $this->assertStringContainsString('text-[#F4F2ED]', $content);
        $this->assertStringContainsString('placeholder:text-[#A8B0A6]/40', $content);
        $this->assertStringContainsString('focus:border-[#88B896]', $content);

        // Multiline textarea vs text input
        $this->assertStringContainsString('multiline || type === \'textarea\'', $content);
        $this->assertStringContainsString('<textarea', $content);
        $this->assertStringContainsString('<input', $content);

        // Required and error indicators
        $this->assertStringContainsString('text-rose-400', $content);
        $this->assertStringContainsString('border-rose-500', $content);
    }

    /**
     * Test Modal.jsx backdrop blur, body overflow lock, and keyboard handler.
     */
    public function test_modal_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/UI/Modal.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Backdrop blur & obsidian surface
        $this->assertStringContainsString('bg-[#0B0E0C]/90', $content);
        $this->assertStringContainsString('backdrop-blur-md', $content);
        $this->assertStringContainsString('bg-[#0E1310]/95', $content);
        $this->assertStringContainsString('backdrop-blur-xl', $content);
        $this->assertStringContainsString('border-white/15', $content);

        // Behavior: escape listener & overflow lock
        $this->assertStringContainsString('e.key === \'Escape\'', $content);
        $this->assertStringContainsString('document.body.style.overflow = \'hidden\'', $content);
        $this->assertStringContainsString('document.body.style.overflow = \'unset\'', $content);
        $this->assertStringContainsString('aria-label="Close modal"', $content);
        $this->assertStringContainsString('e.stopPropagation()', $content);
    }

    /**
     * Test ProjectCard.jsx anti-CLS aspect ratio, dark frame, and hover effects.
     */
    public function test_project_card_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/Portfolio/ProjectCard.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Anti-CLS aspect-[16/10]
        $this->assertStringContainsString('aspect-[16/10]', $content);
        $this->assertStringContainsString('bg-[#121814]', $content);
        $this->assertStringContainsString('border-white/10', $content);

        // Hover scale and title transition
        $this->assertStringContainsString('group-hover:scale-105', $content);
        $this->assertStringContainsString('group-hover:text-[#88B896]', $content);
        $this->assertStringContainsString('text-[#F4F2ED]', $content);

        // Excerpt clamping and priority loading
        $this->assertStringContainsString('line-clamp-2', $content);
        $this->assertStringContainsString('loading={priority ? \'eager\' : \'lazy\'}', $content);
        $this->assertStringContainsString('decoding="async"', $content);
    }

    /**
     * Test FilterBar.jsx input, select dropdown, and divider.
     */
    public function test_filter_bar_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/Portfolio/FilterBar.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Frosted divider
        $this->assertStringContainsString('border-b border-white/10', $content);

        // Search input
        $this->assertStringContainsString('bg-[#121814]/90', $content);
        $this->assertStringContainsString('placeholder:text-[#A8B0A6]/40', $content);
        $this->assertStringContainsString('aria-label="Clear search"', $content);

        // Select options dark styling
        $this->assertStringContainsString('bg-[#121814]', $content);
        $this->assertStringContainsString('text-[#F4F2ED]', $content);
        $this->assertStringContainsString('htmlFor="sort-select"', $content);
    }

    /**
     * Test ImageGallery.jsx thumbnail frame, lightbox modal, and keyboard controls.
     */
    public function test_image_gallery_component_interface_and_theme_contracts(): void
    {
        $path = resource_path('js/Components/Portfolio/ImageGallery.jsx');
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Thumbnail frame
        $this->assertStringContainsString('aspect-[4/3]', $content);
        $this->assertStringContainsString('bg-[#121814]', $content);
        $this->assertStringContainsString('border-white/10', $content);
        $this->assertStringContainsString('group-hover:scale-105', $content);

        // Lightbox modal container
        $this->assertStringContainsString('aspect-[16/10]', $content);
        $this->assertStringContainsString('bg-[#0B0E0C]', $content);
        $this->assertStringContainsString('e.key === \'ArrowLeft\'', $content);
        $this->assertStringContainsString('e.key === \'ArrowRight\'', $content);
    }

    /**
     * Run the empirical Node.js SSR stress harness and verify exit code 0.
     */
    public function test_empirical_node_ssr_stress_harness_executes_cleanly(): void
    {
        $harnessPath = base_path('tests/empirical_theme_m2_stress.js');
        $this->assertFileExists($harnessPath);

        $process = Process::run("node {$harnessPath}");
        $this->assertTrue(
            $process->successful(),
            "Node empirical stress harness failed with output:\n" . $process->output() . "\nError output:\n" . $process->errorOutput()
        );

        $this->assertStringContainsString('Total Assertions: 86 | Passed: 86 | Failed: 0', $process->output());
    }
}
