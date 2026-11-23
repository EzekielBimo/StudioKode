import fs from 'fs';
import path from 'path';
import { createServer } from 'vite';
import { renderToStaticMarkup } from 'react-dom/server';
import React from 'react';

/**
 * StudioKode Empirical E2E Asset, Token & SSR Validation Runner
 *
 * Runs under Node 24 ESM to validate:
 * 1. Root Blade Template & Typography Preloads
 * 2. Tailwind Configuration & Global CSS Design Tokens
 * 3. Inertia App Configuration & Progress Bar Color
 * 4. Production Build Manifest & Asset Chunks
 * 5. SSR React Component Markup Rendering & Anti-CLS Invariants
 */
async function runEmpiricalE2ERunner() {
    console.log('=================================================================');
    console.log('  StudioKode Empirical E2E Asset, Token & SSR Validation Runner  ');
    console.log('=================================================================\n');

    let passed = 0;
    let failed = 0;

    function assert(condition, message) {
        if (condition) {
            passed++;
            console.log(`  ✓ PASS: ${message}`);
        } else {
            failed++;
            console.error(`  ✗ FAIL: ${message}`);
        }
    }

    // =========================================================================
    // Suite 1: Root Blade Template & Typography Preloads
    // =========================================================================
    console.log('[Suite 1: Root Blade Template & Typography Preloads]');
    const appBladePath = path.resolve('resources/views/app.blade.php');
    assert(fs.existsSync(appBladePath), 'resources/views/app.blade.php exists');

    const appBlade = fs.readFileSync(appBladePath, 'utf8');
    assert(appBlade.includes('fonts.googleapis.com'), 'app.blade.php connects to Google Fonts');
    assert(appBlade.includes('fonts.gstatic.com'), 'app.blade.php preconnects to Google Fonts gstatic');
    assert(appBlade.includes('Instrument+Serif'), 'app.blade.php preloads Instrument Serif display font');
    assert(appBlade.includes('Plus+Jakarta+Sans'), 'app.blade.php preloads Plus Jakarta Sans geometric font');
    assert(appBlade.includes('meta name="color-scheme" content="dark"'), 'app.blade.php declares dark color-scheme meta');
    assert(appBlade.includes('meta name="theme-color"'), 'app.blade.php declares theme-color meta');
    assert(appBlade.includes('@vite('), 'app.blade.php invokes Vite asset directive');
    assert(appBlade.includes('id="app-skeleton"'), 'app.blade.php contains instant branded loading shell');

    // =========================================================================
    // Suite 2: Tailwind Configuration & Global CSS Design Tokens
    // =========================================================================
    console.log('\n[Suite 2: Tailwind Configuration & Global CSS Design Tokens]');
    const tailwindPath = path.resolve('tailwind.config.js');
    const cssPath = path.resolve('resources/css/app.css');

    assert(fs.existsSync(tailwindPath), 'tailwind.config.js exists');
    assert(fs.existsSync(cssPath), 'resources/css/app.css exists');

    const tailwind = fs.readFileSync(tailwindPath, 'utf8');
    const css = fs.readFileSync(cssPath, 'utf8');

    // Typography
    assert(tailwind.includes('Instrument Serif'), 'tailwind.config.js configures Instrument Serif as primary serif');
    assert(tailwind.includes('Cormorant Garamond'), 'tailwind.config.js preserves Cormorant Garamond fallback');
    assert(css.includes('Instrument Serif'), 'resources/css/app.css applies Instrument Serif to headings');

    // Palette Tokens in Tailwind Config
    assert(tailwind.includes('#0D0D0D'), 'tailwind.config.js defines Jet Black token (#0D0D0D)');
    assert(tailwind.includes('#224F10'), 'tailwind.config.js defines Forest Green token (#224F10)');
    assert(tailwind.includes('#B0E454'), 'tailwind.config.js defines Lime Green token (#B0E454)');
    assert(tailwind.includes('#EAFFD0'), 'tailwind.config.js defines Soft Ivory token (#EAFFD0)');

    // Palette Tokens in CSS Custom Properties
    assert(css.includes('#0D0D0D'), 'resources/css/app.css defines Jet Black token (#0D0D0D)');
    assert(css.includes('#224F10'), 'resources/css/app.css defines Forest Green token (#224F10)');
    assert(css.includes('#B0E454'), 'resources/css/app.css defines Lime Green token (#B0E454)');
    assert(css.includes('#EAFFD0'), 'resources/css/app.css defines Soft Ivory token (#EAFFD0)');

    // CSS Custom Properties & Base Styles
    assert(css.includes('--color-jet-black: #0D0D0D'), 'CSS defines --color-jet-black variable');
    assert(css.includes('--color-forest-green: #224F10'), 'CSS defines --color-forest-green variable');
    assert(css.includes('--color-lime-green: #B0E454'), 'CSS defines --color-lime-green variable');
    assert(css.includes('--color-soft-ivory: #EAFFD0'), 'CSS defines --color-soft-ivory variable');
    assert(css.includes('::selection'), 'CSS defines custom ::selection styling');
    assert(css.includes('.no-scrollbar') || css.includes('.scrollbar-none'), 'CSS exports no-scrollbar utility classes');

    // Backward Compatibility
    assert(tailwind.includes('obsidian'), 'tailwind.config.js preserves obsidian token family');
    assert(tailwind.includes('moss'), 'tailwind.config.js preserves moss token family');

    // =========================================================================
    // Suite 3: Inertia App Configuration & Progress Bar
    // =========================================================================
    console.log('\n[Suite 3: Inertia App Configuration & Progress Bar]');
    const appJsxPath = path.resolve('resources/js/app.jsx');
    assert(fs.existsSync(appJsxPath), 'resources/js/app.jsx exists');

    const appJsx = fs.readFileSync(appJsxPath, 'utf8');
    assert(appJsx.includes('createInertiaApp'), 'app.jsx initializes createInertiaApp');
    assert(appJsx.includes('progress:'), 'app.jsx configures Inertia progress indicator');
    assert(appJsx.includes('#B0E454'), 'Inertia progress bar uses Lime Green accent color (#B0E454)');

    // =========================================================================
    // Suite 4: Production Build Manifest & Asset Chunks
    // =========================================================================
    console.log('\n[Suite 4: Production Build Manifest & Asset Chunks]');
    const manifestPath = path.resolve('public/build/manifest.json');
    assert(fs.existsSync(manifestPath), 'public/build/manifest.json exists');

    if (fs.existsSync(manifestPath)) {
        try {
            const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
            assert(typeof manifest === 'object' && manifest !== null, 'manifest.json is valid JSON object');
            assert(manifest['resources/js/app.jsx'] !== undefined, 'manifest contains resources/js/app.jsx entry');
            assert(manifest['resources/css/app.css'] !== undefined, 'manifest contains resources/css/app.css entry');

            const jsFile = manifest['resources/js/app.jsx']?.file;
            const cssFile = manifest['resources/css/app.css']?.file;

            if (jsFile) {
                assert(fs.existsSync(path.resolve('public/build', jsFile)), `Compiled JS chunk exists: ${jsFile}`);
            }
            if (cssFile) {
                assert(fs.existsSync(path.resolve('public/build', cssFile)), `Compiled CSS chunk exists: ${cssFile}`);
            }
        } catch (e) {
            assert(false, `manifest.json parsing error: ${e.message}`);
        }
    }

    // =========================================================================
    // Suite 5: SSR React Component Rendering Validation
    // =========================================================================
    console.log('\n[Suite 5: SSR React Component Rendering & Anti-CLS Invariants]');
    const vite = await createServer({ server: { middlewareMode: true } });

    try {
        // 1. Button.jsx SSR
        const { default: Button } = await vite.ssrLoadModule('/resources/js/Components/UI/Button.jsx');
        const primaryBtn = renderToStaticMarkup(React.createElement(Button, { variant: 'primary' }, 'Commission Sanctuary'));
        assert(primaryBtn.includes('Commission Sanctuary'), 'Button renders children under SSR');
        assert(primaryBtn.includes('button'), 'Button without href renders <button> element');
        assert(
            primaryBtn.includes('bg-[#1C2A21]') || primaryBtn.includes('bg-forest') || primaryBtn.includes('bg-jet'),
            'Button primary variant renders primary background classes'
        );
        assert(
            primaryBtn.includes('border-[#B0E454]') || primaryBtn.includes('border-lime') || primaryBtn.includes('border-forest') || primaryBtn.includes('border-emerald-500/25') || primaryBtn.includes('border-emerald'),
            'Button primary variant renders primary border classes'
        );

        const linkBtn = renderToStaticMarkup(React.createElement(Button, { href: '/projects', variant: 'secondary' }, 'Explore Portfolio'));
        assert(linkBtn.includes('href="/projects"'), 'Button with href renders <a> Link');
        assert(
            linkBtn.includes('border-white/15') || linkBtn.includes('border-white/20') || linkBtn.includes('backdrop-blur-md'),
            'Button secondary variant renders secondary glass/border classes'
        );

        const disabledBtn = renderToStaticMarkup(React.createElement(Button, { disabled: true }, 'Processing'));
        assert(disabledBtn.includes('disabled'), 'Button honors disabled attribute under SSR');

        // 2. Badge.jsx SSR
        const { default: Badge } = await vite.ssrLoadModule('/resources/js/Components/UI/Badge.jsx');
        const foliageBadge = renderToStaticMarkup(React.createElement(Badge, { variant: 'foliage' }, 'Design Sanctuary'));
        assert(foliageBadge.includes('Design Sanctuary'), 'Badge renders foliage variant children');
        assert(
            foliageBadge.includes('border-[#B0E454]') || foliageBadge.includes('border-white/10') || foliageBadge.includes('border-lime') || foliageBadge.includes('border-emerald-500/30') || foliageBadge.includes('border-emerald'),
            'Badge foliage variant renders foliage border classes'
        );
        assert(
            foliageBadge.includes('text-[#88B896]') || foliageBadge.includes('text-lime') || foliageBadge.includes('bg-[#152319]') || foliageBadge.includes('bg-forest'),
            'Badge foliage variant renders foliage color classes'
        );

        const neutralBadge = renderToStaticMarkup(React.createElement(Badge, { variant: 'neutral' }, 'Construction'));
        assert(neutralBadge.includes('Construction'), 'Badge renders neutral variant children');
        assert(
            neutralBadge.includes('border-white/10') || neutralBadge.includes('border-white/15') || neutralBadge.includes('border-white/20'),
            'Badge neutral variant renders neutral border classes'
        );

        const outlineBadge = renderToStaticMarkup(React.createElement(Badge, { variant: 'outline' }, 'Scale 640 m²'));
        assert(
            outlineBadge.includes('border-white/15') || outlineBadge.includes('border-white/20'),
            'Badge outline variant renders outline border classes'
        );

        // 3. ProjectCard.jsx SSR & Anti-CLS Aspect Ratio
        const { default: ProjectCard } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ProjectCard.jsx');
        const mockProject = {
            id: 1,
            title: 'Komorebi Moss Sanctuary',
            slug: 'komorebi-moss-sanctuary',
            location: 'Kyoto, Japan',
            year: 2024,
            area_size: '640 m²',
            category: 'Project Design',
            cover_image: 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26',
            excerpt: 'A contemplative sanctuary with velvet moss hummocks.',
            is_featured: true,
        };
        const cardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: mockProject }));
        assert(cardHtml.includes('Komorebi Moss Sanctuary'), 'ProjectCard renders title under SSR');
        assert(cardHtml.includes('aspect-[16/10]'), 'ProjectCard enforces aspect-[16/10] anti-CLS container');
        assert(cardHtml.includes('href="/projects/komorebi-moss-sanctuary"'), 'ProjectCard links to canonical slug');

        // 4. FilterBar.jsx SSR
        const { default: FilterBar } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/FilterBar.jsx');
        const filterHtml = renderToStaticMarkup(React.createElement(FilterBar, {
            search: 'Zen',
            sort: 'newest',
            totalCount: 12,
            filteredCount: 3,
            onSearchChange: () => {},
            onSortChange: () => {},
        }));
        assert(filterHtml.includes('value="Zen"'), 'FilterBar renders search input with value');
        assert(filterHtml.includes('newest'), 'FilterBar renders sort select with option');

        // 5. Modal.jsx SSR
        const { default: Modal } = await vite.ssrLoadModule('/resources/js/Components/UI/Modal.jsx');
        const modalHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: true,
            onClose: () => {},
            title: 'Masterplan Schematic'
        }, React.createElement('p', null, 'Modal Content')));
        assert(modalHtml.includes('Masterplan Schematic'), 'Modal renders title under SSR');
        assert(modalHtml.includes('backdrop-blur'), 'Modal renders backdrop-blur overlay');

    } catch (err) {
        assert(false, `SSR component rendering failed: ${err.message}`);
    } finally {
        await vite.close();
    }

    console.log('\n=================================================================');
    console.log(`  Total Checks: ${passed + failed} | Passed: ${passed} | Failed: ${failed}  `);
    console.log('=================================================================\n');

    if (failed > 0) {
        process.exit(1);
    }
}

runEmpiricalE2ERunner();
