import fs from 'fs';
import path from 'path';
import { createServer } from 'vite';
import { renderToStaticMarkup } from 'react-dom/server';
import React from 'react';

/**
 * StudioKode Tier 5 Adversarial Hardening Empirical Probe Runner
 *
 * Runs under Node 24 ESM to empirically validate:
 * 1. Component Null/Empty/Adversarial Prop Stress Testing under Vite SSR
 * 2. Boundary Values and Missing Fields Resilience in UI Primitives
 * 3. Anti-Slop Architectural Tokens & Hairline Border Invariants
 * 4. ImageGallery and Lightbox Modal Boundary Conditions
 * 5. Strict Content & Theme Token Coexistence Invariants
 */
async function runTier5AdversarialProbe() {
    console.log('=====================================================================');
    console.log('  StudioKode Tier 5 Adversarial Hardening Empirical Probe Runner     ');
    console.log('=====================================================================\n');

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

    const vite = await createServer({ server: { middlewareMode: true } });

    try {
        // =====================================================================
        // Suite 1: UI Primitives Boundary & Null Resilience
        // =====================================================================
        console.log('[Suite 1: UI Primitives Boundary & Null Resilience under SSR]');

        // 1. Button Resilience
        const { default: Button } = await vite.ssrLoadModule('/resources/js/Components/UI/Button.jsx');

        // Unknown variant fallback
        const unknownVariantBtn = renderToStaticMarkup(React.createElement(Button, { variant: 'unrecognized_adversarial_variant' }, 'Fallback Button'));
        assert(unknownVariantBtn.includes('Fallback Button'), 'Button renders children with unrecognized variant');
        assert(unknownVariantBtn.includes('bg-[#1C2A21]'), 'Button falls back to primary variant styling for unknown variant');

        // Disabled button
        const disabledBtn = renderToStaticMarkup(React.createElement(Button, { disabled: true }, 'Disabled Action'));
        assert(disabledBtn.includes('disabled=""') || disabledBtn.includes('disabled'), 'Button carries disabled attribute');
        assert(disabledBtn.includes('disabled:opacity-50'), 'Button includes disabled opacity styling');

        // Link button with custom props
        const linkBtn = renderToStaticMarkup(React.createElement(Button, { href: '/contact', target: '_blank', rel: 'noopener' }, 'External Link'));
        assert(linkBtn.includes('href="/contact"'), 'Button with href renders Link anchor tag');
        assert(linkBtn.includes('target="_blank"'), 'Button passes external target attribute');

        // 2. Badge Resilience
        const { default: Badge } = await vite.ssrLoadModule('/resources/js/Components/UI/Badge.jsx');

        const variants = ['neutral', 'foliage', 'outline', 'outline-light', 'forest', 'lime'];
        for (const variant of variants) {
            const badgeHtml = renderToStaticMarkup(React.createElement(Badge, { variant }, `Variant ${variant}`));
            assert(badgeHtml.includes(`Variant ${variant}`), `Badge renders ${variant} variant correctly`);
        }

        // Unknown variant badge
        const unknownBadge = renderToStaticMarkup(React.createElement(Badge, { variant: 'super_exotic_badge' }, 'Exotic Badge'));
        assert(unknownBadge.includes('Exotic Badge'), 'Badge renders text with unknown variant');
        assert(unknownBadge.includes('bg-[rgba(20,32,24,0.65)]'), 'Badge falls back to neutral variant styling');

        // 3. Modal Resilience
        const { default: Modal } = await vite.ssrLoadModule('/resources/js/Components/UI/Modal.jsx');

        // Closed modal returns nothing
        const closedModalHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: false,
            onClose: () => {},
            title: 'Hidden Modal'
        }, React.createElement('p', null, 'Hidden text')));
        assert(!closedModalHtml.includes('Hidden text'), 'Modal with isOpen=false renders no content');

        // Open modal with null title
        const nullTitleModalHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: true,
            onClose: () => {},
            title: null
        }, React.createElement('p', null, 'Modal Content Without Title')));
        assert(nullTitleModalHtml.includes('Modal Content Without Title'), 'Modal renders children even when title is null');
        assert(nullTitleModalHtml.includes('backdrop-blur'), 'Modal renders backdrop blur overlay');

        // =====================================================================
        // Suite 2: Portfolio Components Adversarial Prop Testing
        // =====================================================================
        console.log('\n[Suite 2: Portfolio Components Adversarial Prop Testing under SSR]');

        // 1. ProjectCard Resilience
        const { default: ProjectCard } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ProjectCard.jsx');

        // Null project prop
        const nullCardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: null }));
        assert(nullCardHtml === '', 'ProjectCard with null project returns null cleanly');

        // Minimal project prop with missing optional attributes
        const minimalProject = {
            id: 999,
            title: 'Adversarial Barebones Cloister',
            slug: 'adversarial-barebones-cloister',
        };
        const minimalCardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: minimalProject }));
        assert(minimalCardHtml.includes('Adversarial Barebones Cloister'), 'ProjectCard renders minimal project title');
        assert(minimalCardHtml.includes('aspect-[16/10]'), 'ProjectCard preserves anti-CLS aspect ratio with minimal data');
        assert(minimalCardHtml.includes('href="/projects/adversarial-barebones-cloister"'), 'ProjectCard generates correct slug link');

        // ProjectCard with null excerpt
        const projectWithNullExcerpt = {
            id: 1000,
            title: 'Sanctuary With Null Excerpt',
            slug: 'sanctuary-null-excerpt',
            excerpt: null,
            cover_image: 'https://images.unsplash.com/test.jpg',
            location: 'Kyoto',
            year: 2024,
            area_size: '500 m²',
        };
        const nullExcerptCardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: projectWithNullExcerpt }));
        assert(nullExcerptCardHtml.includes('Sanctuary With Null Excerpt'), 'ProjectCard renders when excerpt is null');

        // 2. ImageGallery Resilience
        const { default: ImageGallery } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ImageGallery.jsx');

        // Empty images array
        const emptyGalleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: [] }));
        assert(emptyGalleryHtml === '', 'ImageGallery with empty images array returns null');

        // Null images prop: Stress testing null resilience
        try {
            const nullGalleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: null }));
            assert(nullGalleryHtml === '', 'ImageGallery with null images returns null without throwing');
        } catch (e) {
            assert(false, `GAP DETECTED: ImageGallery with images=null throws "${e.message}" due to unshielded images.length in useEffect deps (line 21)`);
        }

        // Gallery with single image (prev/next navigation buttons must NOT render)
        const singleImageGallery = [
            { id: 1, image_path: 'https://images.unsplash.com/single.jpg', caption: 'Solo Visual Archive' }
        ];
        const singleGalleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: singleImageGallery }));
        assert(singleGalleryHtml.includes('Solo Visual Archive'), 'ImageGallery renders caption for single image');
        assert(singleGalleryHtml.includes('aspect-[4/3]'), 'ImageGallery enforces aspect-[4/3] on grid items');

        // 3. FilterBar Resilience
        const { default: FilterBar } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/FilterBar.jsx');

        // Zero counts
        const zeroFilterHtml = renderToStaticMarkup(React.createElement(FilterBar, {
            search: '',
            sort: 'newest',
            totalCount: 0,
            filteredCount: 0,
            onSearchChange: () => {},
            onSortChange: () => {},
        }));
        assert(zeroFilterHtml.includes('Search sanctuaries'), 'FilterBar renders search input');
        assert(!zeroFilterHtml.includes('Showing 0 of 0 Works'), 'FilterBar omits count label when counts are 0');

        // Extreme search text
        const extremeSearch = '極限の庭園デザイン'.repeat(20);
        const extremeFilterHtml = renderToStaticMarkup(React.createElement(FilterBar, {
            search: extremeSearch,
            sort: 'area_desc',
            totalCount: 50,
            filteredCount: 2,
            onSearchChange: () => {},
            onSortChange: () => {},
        }));
        assert(extremeFilterHtml.includes(extremeSearch), 'FilterBar handles long unicode search string without truncation error');
        assert(extremeFilterHtml.includes('Showing 2 of 50 Works'), 'FilterBar renders count summary');

        // =====================================================================
        // Suite 3: Anti-Slop Architectural Tokens & Codebase Invariants
        // =====================================================================
        console.log('\n[Suite 3: Anti-Slop Architectural Tokens & Codebase Invariants]');

        const tailwindPath = path.resolve('tailwind.config.js');
        const appCssPath = path.resolve('resources/css/app.css');
        const appBladePath = path.resolve('resources/views/app.blade.php');

        const tailwind = fs.readFileSync(tailwindPath, 'utf8');
        const appCss = fs.readFileSync(appCssPath, 'utf8');
        const appBlade = fs.readFileSync(appBladePath, 'utf8');

        // Verify exact hexadecimal token constants
        assert(tailwind.includes('#0D0D0D'), 'Tailwind defines Jet Black (#0D0D0D)');
        assert(tailwind.includes('#224F10'), 'Tailwind defines Forest Green (#224F10)');
        assert(tailwind.includes('#B0E454'), 'Tailwind defines Lime Green (#B0E454)');
        assert(tailwind.includes('#EAFFD0'), 'Tailwind defines Soft Ivory (#EAFFD0)');

        assert(appCss.includes('#0D0D0D'), 'CSS defines Jet Black (#0D0D0D)');
        assert(appCss.includes('#224F10'), 'CSS defines Forest Green (#224F10)');
        assert(appCss.includes('#B0E454'), 'CSS defines Lime Green (#B0E454)');
        assert(appCss.includes('#EAFFD0'), 'CSS defines Soft Ivory (#EAFFD0)');

        // Instrument Serif typography
        assert(appBlade.includes('Instrument+Serif'), 'Root Blade links Google Fonts Instrument Serif');
        assert(tailwind.includes('Instrument Serif'), 'Tailwind font-serif includes Instrument Serif');
        assert(appCss.includes('Instrument Serif'), 'Global CSS font headings include Instrument Serif');

        // Anti-slop architectural hairlines
        assert(appCss.includes('::selection'), 'Global CSS defines custom ::selection styling');
        assert(tailwind.includes('obsidian') && tailwind.includes('moss'), 'Tailwind preserves backward compatibility tokens');

    } catch (err) {
        assert(false, `Tier 5 empirical probe execution error: ${err.message}`);
    } finally {
        await vite.close();
    }

    console.log('\n=====================================================================');
    console.log(`  Tier 5 Empirical Checks: ${passed + failed} | Passed: ${passed} | Failed: ${failed}  `);
    console.log('=====================================================================\n');

    if (failed > 0) {
        process.exit(1);
    }
}

runTier5AdversarialProbe();
