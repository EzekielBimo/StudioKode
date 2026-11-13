<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Challenger Theme M4.2 Adversarial Verification & Stress Test Suite.
 *
 * Provides empirical verification across:
 * 1. WCAG 2.1 / 2.2 contrast ratios across all theme color pairings.
 * 2. Production asset manifest integrity, chunk size bounds, and absence of bloated dependencies.
 * 3. Anti-CLS aspect ratio contracts across portfolio and admin components.
 * 4. Responsive viewport invariants (zero w-screen leaks, proper container constraints).
 */
class ChallengerThemeM4_2_AdversarialVerificationTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Dimension 1: WCAG AA/AAA Contrast Ratio Verification (Mathematical Oracle)
    |--------------------------------------------------------------------------
    */

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function alphaComposite(array $fgRgb, array $bgRgb, float $alpha): array
    {
        return [
            (int) round($alpha * $fgRgb[0] + (1 - $alpha) * $bgRgb[0]),
            (int) round($alpha * $fgRgb[1] + (1 - $alpha) * $bgRgb[1]),
            (int) round($alpha * $fgRgb[2] + (1 - $alpha) * $bgRgb[2]),
        ];
    }

    private function srgbToLinear(float $val): float
    {
        $val /= 255.0;
        return $val <= 0.03928 ? $val / 12.92 : pow(($val + 0.055) / 1.055, 2.4);
    }

    private function relativeLuminance(array $rgb): float
    {
        $r = $this->srgbToLinear($rgb[0]);
        $g = $this->srgbToLinear($rgb[1]);
        $b = $this->srgbToLinear($rgb[2]);
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    private function calculateContrastRatio(array $rgb1, array $rgb2): float
    {
        $l1 = $this->relativeLuminance($rgb1);
        $l2 = $this->relativeLuminance($rgb2);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);
        return ($lighter + 0.05) / ($darker + 0.05);
    }

    public function test_wcag_contrast_ratios_across_all_theme_pairings(): void
    {
        $obsidianBase = $this->hexToRgb('#0B0E0C');
        $obsidianSurface = $this->hexToRgb('#0E1310');
        $obsidianElevated = $this->hexToRgb('#121814');
        $obsidianFooter = $this->hexToRgb('#080B09');
        $mossGlass065 = $this->alphaComposite([20, 32, 24], $obsidianBase, 0.65);
        $mossGlass075 = $this->alphaComposite([20, 32, 24], $obsidianBase, 0.75);
        $foliageButton = $this->hexToRgb('#1C2A21');
        $foliageBadge = $this->hexToRgb('#152319');
        $selectionBg = $this->hexToRgb('#1C3024');

        $pairings = [
            // Primary text pairs (must meet WCAG AAA >= 7.0:1)
            ['#F4F6F0', $obsidianBase, 7.0, 'Primary text on canvas'],
            ['#F4F2ED', $obsidianSurface, 7.0, 'Primary text on card'],
            ['#F4F2ED', $obsidianElevated, 7.0, 'Primary text on elevated surface'],
            ['#F5F2EB', $obsidianBase, 7.0, 'Admin primary text on canvas'],
            ['#F4F6F0', $obsidianFooter, 7.0, 'Footer primary text'],

            // Secondary text pairs (must meet WCAG AA >= 4.5:1)
            ['#C5C8C0', $obsidianBase, 4.5, 'Secondary sage on canvas'],
            ['#C4C9C3', $obsidianElevated, 4.5, 'Secondary sage on input/elevated'],
            ['#A7ACA0', $obsidianBase, 4.5, 'Secondary sage stone on canvas'],
            ['#A7ACA0', $obsidianSurface, 4.5, 'Secondary sage stone on card'],

            // Button and badge pairs (must meet WCAG AA >= 4.5:1)
            ['#F4F2ED', $foliageButton, 4.5, 'Primary button text'],
            ['#F4F2ED', $mossGlass075, 4.5, 'Secondary button text'],
            ['#88B896', $obsidianBase, 4.5, 'Outline button text'],
            ['#C4C9C3', $mossGlass065, 4.5, 'Neutral badge text'],
            ['#88B896', $foliageBadge, 4.5, 'Foliage badge text'],

            // Navigation and links (must meet WCAG AA >= 4.5:1)
            ['#34D399', $obsidianBase, 4.5, 'Active nav link on canvas'],
            ['#C5C8C0', $obsidianBase, 4.5, 'Inactive nav link on canvas'],
            ['#34D399', $obsidianSurface, 4.5, 'Mobile active link on drawer'],

            // Selection styles (must meet WCAG AA >= 4.5:1)
            ['#F4F6F0', $selectionBg, 4.5, 'Selection highlight text'],
            ['#F9F8F6', $foliageButton, 4.5, 'Admin selection highlight text'],
        ];

        foreach ($pairings as [$fgHex, $bgRgb, $minRatio, $label]) {
            $fgRgb = $this->hexToRgb($fgHex);
            $ratio = $this->calculateContrastRatio($fgRgb, $bgRgb);
            $this->assertGreaterThanOrEqual(
                $minRatio,
                $ratio,
                "WCAG Contrast failure for {$label}: calculated {$ratio}:1, required at least {$minRatio}:1."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 2: Production Bundle Integrity & Dependency Hygiene
    |--------------------------------------------------------------------------
    */

    public function test_production_manifest_bundle_integrity_and_vendor_splitting(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);

        // Required split vendor chunks
        $expectedVendors = ['vendor-react', 'vendor-inertia', 'vendor-motion', 'vendor-lucide'];
        $foundVendors = [];

        foreach ($manifest as $key => $entry) {
            $name = $entry['name'] ?? '';
            $file = $entry['file'] ?? '';

            // Verify file exists on disk and is non-empty
            $physicalPath = public_path('build/' . $file);
            $this->assertFileExists($physicalPath, "Chunk file {$file} missing on disk.");
            $this->assertGreaterThan(0, filesize($physicalPath), "Chunk file {$file} is empty.");

            foreach ($expectedVendors as $v) {
                if ($name === $v || str_contains($key, $v)) {
                    $foundVendors[$v] = true;
                }
            }
        }

        foreach ($expectedVendors as $v) {
            $this->assertTrue(
                isset($foundVendors[$v]),
                "Expected vendor chunk {$v} was not generated in Vite manifest."
            );
        }

        // Verify package.json contains zero bloated or unnecessary packages
        $packageJson = json_decode(file_get_contents(base_path('package.json')), true);
        $deps = array_keys($packageJson['dependencies'] ?? []);
        $allowedDeps = [
            '@inertiajs/react',
            'clsx',
            'framer-motion',
            'lucide-react',
            'react',
            'react-dom',
            'tailwind-merge',
        ];

        foreach ($deps as $dep) {
            $this->assertContains(
                $dep,
                $allowedDeps,
                "Unauthorized or bloated dependency detected in package.json: {$dep}"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 3: Anti-CLS Aspect Ratio Enforcement
    |--------------------------------------------------------------------------
    */

    public function test_anti_cls_aspect_ratios_strictly_enforced_in_components(): void
    {
        // 1. ProjectCard aspect-[16/10]
        $card = file_get_contents(resource_path('js/Components/Portfolio/ProjectCard.jsx'));
        $this->assertStringContainsString('aspect-[16/10]', $card);
        $this->assertStringContainsString('bg-[#121814]', $card);

        // 2. ProjectGalleryManager aspect-[4/3] & bg-[#E6E2DD]
        $manager = file_get_contents(resource_path('js/Components/Admin/ProjectGalleryManager.jsx'));
        $this->assertStringContainsString('aspect-[4/3]', $manager);
        $this->assertStringContainsString('bg-[#E6E2DD]', $manager);

        // 3. ImageGallery aspect-[4/3]
        $gallery = file_get_contents(resource_path('js/Components/Portfolio/ImageGallery.jsx'));
        $this->assertStringContainsString('aspect-[4/3]', $gallery);
        $this->assertStringContainsString('aspect-[16/10]', $gallery);
    }

    /*
    |--------------------------------------------------------------------------
    | Dimension 4: Responsive Viewport Protection & Zero Overflow Leaks
    |--------------------------------------------------------------------------
    */

    public function test_absence_of_horizontal_overflow_triggers_in_all_views(): void
    {
        $jsxFiles = File::allFiles(resource_path('js'));
        $this->assertNotEmpty($jsxFiles);

        foreach ($jsxFiles as $file) {
            $content = $file->getContents();
            $relPath = $file->getRelativePathname();

            // w-screen triggers horizontal scrollbars on desktop viewports with scrollbars
            $this->assertStringNotContainsString(
                'w-screen',
                $content,
                "File {$relPath} contains 'w-screen' which triggers horizontal viewport overflow."
            );
        }
    }
}
