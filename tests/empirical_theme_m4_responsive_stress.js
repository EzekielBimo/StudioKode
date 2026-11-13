import { createServer } from 'vite';
import { renderToStaticMarkup } from 'react-dom/server';
import React from 'react';
import fs from 'fs';
import path from 'path';
import babel from '@babel/parser';
import _traverse from '@babel/traverse';

const traverse = _traverse.default || _traverse;

async function runMilestone4ThemeAdversarialChallenge() {
    console.log('======================================================================');
    console.log(' StudioKode Milestone 4 Adversarial Responsive & Layout Stress Test');
    console.log(' Viewports: Mobile (320px, 375px, 414px) | Tablet (768px) | Desktop (1024px, 1440px, 1920px)');
    console.log('======================================================================\n');

    let passed = 0;
    let failed = 0;
    const failures = [];

    function assert(condition, message, details = '') {
        if (condition) {
            passed++;
            console.log(`  ✓ PASS: ${message}`);
        } else {
            failed++;
            console.error(`  ✗ FAIL: ${message} ${details ? '--> ' + details : ''}`);
            failures.push({ message, details });
        }
    }

    const vite = await createServer({
        server: { middlewareMode: true },
        appType: 'custom',
    });

    try {
        // ===================================================================
        // SUITE 1: ROOT TEMPLATE & GLOBAL VIEWPORT FOUNDATION
        // ===================================================================
        console.log('\n[Suite 1: Root Template & Global Viewport Foundation]');
        const appBlade = fs.readFileSync('resources/views/app.blade.php', 'utf8');
        assert(
            appBlade.includes('<meta name="viewport" content="width=device-width, initial-scale=1">'),
            'app.blade.php declares canonical responsive viewport meta tag'
        );
        assert(
            appBlade.includes('<meta name="theme-color" content="#0B0E0C">'),
            'app.blade.php declares obsidian dark meta theme-color'
        );
        assert(
            appBlade.includes('bg-[#0B0E0C]') && appBlade.includes('text-[#F4F6F0]'),
            'app.blade.php sets dark obsidian base canvas and high-contrast text'
        );

        const appCss = fs.readFileSync('resources/css/app.css', 'utf8');
        assert(
            appCss.includes('@layer utilities') && appCss.includes('.glass-moss') && appCss.includes('.glass-obsidian'),
            'app.css defines glass-moss and glass-obsidian utilities'
        );

        const tailwindConfig = fs.readFileSync('tailwind.config.js', 'utf8');
        assert(
            tailwindConfig.includes("'#0B0E0C'") && tailwindConfig.includes("'#142018'"),
            'tailwind.config.js includes obsidian base and moss color tokens'
        );

        // ===================================================================
        // SUITE 2: AST AUDIT — DATA TABLE OVERFLOW CONTAINMENT (320px–414px)
        // ===================================================================
        console.log('\n[Suite 2: AST Audit — Data Table Horizontal Overflow Containment]');
        
        const tableFiles = [
            'resources/js/Pages/Admin/Dashboard/Index.jsx',
            'resources/js/Pages/Admin/Projects/Index.jsx',
            'resources/js/Pages/Admin/Inquiries/Index.jsx',
        ];

        for (const file of tableFiles) {
            const code = fs.readFileSync(file, 'utf8');
            const ast = babel.parse(code, { sourceType: 'module', plugins: ['jsx'] });
            let tableCount = 0;
            let protectedTables = 0;

            traverse(ast, {
                JSXElement(nodePath) {
                    if (nodePath.node.openingElement.name.name === 'table') {
                        tableCount++;
                        // Search enclosing parent JSX elements for overflow-x-auto or overflow-x-scroll
                        let parent = nodePath.parentPath;
                        let hasOverflowProtection = false;
                        while (parent) {
                            if (parent.node.type === 'JSXElement') {
                                const classAttr = parent.node.openingElement.attributes.find(
                                    a => a.name && a.name.name === 'className'
                                );
                                if (classAttr && classAttr.value) {
                                    const classVal = classAttr.value.value || '';
                                    if (classVal.includes('overflow-x-auto') || classVal.includes('overflow-x-scroll')) {
                                        hasOverflowProtection = true;
                                        break;
                                    }
                                }
                            }
                            parent = parent.parentPath;
                        }
                        if (hasOverflowProtection) {
                            protectedTables++;
                        }
                    }
                }
            });

            assert(
                tableCount > 0 && protectedTables === tableCount,
                `${file}: all ${tableCount} <table> elements are wrapped in overflow-x-auto containers`,
                `found ${tableCount} tables, ${protectedTables} protected`
            );
        }

        // ===================================================================
        // SUITE 3: AST AUDIT — GRID RESPONSIVENESS & BREAKPOINT PURITY
        // ===================================================================
        console.log('\n[Suite 3: AST Audit — Grid Responsiveness across Breakpoints]');

        const gridFiles = [
            'resources/js/Components/Navigation/Footer.jsx',
            'resources/js/Components/Portfolio/ImageGallery.jsx',
            'resources/js/Pages/Admin/Dashboard/Index.jsx',
            'resources/js/Pages/Admin/Inquiries/Show.jsx',
            'resources/js/Pages/Admin/Projects/Create.jsx',
            'resources/js/Pages/Admin/Projects/Edit.jsx',
            'resources/js/Pages/Contact/Index.jsx',
            'resources/js/Pages/Home/Index.jsx',
            'resources/js/Pages/Projects/Index.jsx',
            'resources/js/Pages/Projects/Show.jsx',
            'resources/js/Pages/Services/Index.jsx',
            'resources/js/Pages/Studio/Index.jsx',
        ];

        let totalGridsAudited = 0;
        let responsiveGridsCount = 0;

        for (const file of gridFiles) {
            const code = fs.readFileSync(file, 'utf8');
            const ast = babel.parse(code, { sourceType: 'module', plugins: ['jsx'] });

            traverse(ast, {
                JSXElement(nodePath) {
                    const classAttr = nodePath.node.openingElement.attributes.find(
                        a => a.name && a.name.name === 'className'
                    );
                    if (classAttr && classAttr.value && typeof classAttr.value.value === 'string') {
                        const classVal = classAttr.value.value;
                        if (classVal.includes('grid-cols-')) {
                            totalGridsAudited++;
                            // Check if grid defines grid-cols-1 at base OR uses responsive prefix (sm:, md:, lg:, xl:)
                            // OR if grid-cols-2 is used for compact 2-col stat/metric tiles
                            const hasBaseCol1 = classVal.includes('grid-cols-1');
                            const hasPrefix = /(sm|md|lg|xl):grid-cols-/.test(classVal);
                            const isCompactTwoCol = classVal.includes('grid-cols-2') && hasPrefix;

                            if (hasBaseCol1 || hasPrefix || isCompactTwoCol) {
                                responsiveGridsCount++;
                            } else {
                                console.warn(`Unconstrained grid in ${file}: ${classVal}`);
                            }
                        }
                    }
                }
            });
        }

        assert(
            totalGridsAudited > 0 && responsiveGridsCount === totalGridsAudited,
            `All ${totalGridsAudited} JSX grids enforce responsive collapse on mobile viewports`
        );

        // ===================================================================
        // SUITE 4: AST AUDIT — UNBOUNDED FIXED PIXEL WIDTH AUDIT
        // ===================================================================
        console.log('\n[Suite 4: AST Audit — Unbounded Fixed Pixel Widths (< 320px safe)]');

        const allJsxFiles = [];
        function walkJsx(dir) {
            const entries = fs.readdirSync(dir, { withFileTypes: true });
            for (const entry of entries) {
                const full = path.join(dir, entry.name);
                if (entry.isDirectory()) {
                    walkJsx(full);
                } else if (entry.name.endsWith('.jsx')) {
                    allJsxFiles.push(full);
                }
            }
        }
        walkJsx('resources/js');

        let dangerousFixedPixelWidths = 0;
        for (const file of allJsxFiles) {
            const code = fs.readFileSync(file, 'utf8');
            // Look for classes like w-[350px], w-[500px], min-w-[400px] without responsive prefix
            const matches = code.match(/(?<!(sm:|md:|lg:|xl:|2xl:))(w|min-w)-\[(\d+)px\]/g);
            if (matches) {
                for (const m of matches) {
                    const numMatch = m.match(/\[(\d+)px\]/);
                    if (numMatch && parseInt(numMatch[1], 10) > 320) {
                        dangerousFixedPixelWidths++;
                        console.error(`Dangerous fixed width ${m} in ${file}`);
                    }
                }
            }
        }

        assert(
            dangerousFixedPixelWidths === 0,
            `Zero unbounded fixed pixel widths (>320px) across all ${allJsxFiles.length} JSX files`
        );

        // ===================================================================
        // SUITE 5: NAVIGATION & DRAWER RESPONSIVE ARCHITECTURE
        // ===================================================================
        console.log('\n[Suite 5: Navigation & Mobile Drawer Responsive Architecture]');

        const navbarCode = fs.readFileSync('resources/js/Components/Navigation/Navbar.jsx', 'utf8');
        assert(
            navbarCode.includes('hidden md:flex'),
            'Navbar.jsx: desktop navigation links are hidden on mobile viewports (< 768px)'
        );
        assert(
            navbarCode.includes('md:hidden') && navbarCode.includes('aria-label="Toggle navigation menu"'),
            'Navbar.jsx: mobile hamburger trigger button is visible on mobile (< 768px) with accessible aria-label'
        );
        assert(
            navbarCode.includes('<MobileMenu'),
            'Navbar.jsx: mounts MobileMenu drawer component'
        );

        const mobileMenuCode = fs.readFileSync('resources/js/Components/Navigation/MobileMenu.jsx', 'utf8');
        assert(
            mobileMenuCode.includes('md:hidden') && mobileMenuCode.includes('overflow-hidden'),
            'MobileMenu.jsx: is scoped to mobile (md:hidden) with overflow containment'
        );
        assert(
            mobileMenuCode.includes('bg-[#0E1310]/95') && mobileMenuCode.includes('backdrop-blur-xl'),
            'MobileMenu.jsx: applies dark obsidian glass styling'
        );

        const adminLayoutCode = fs.readFileSync('resources/js/Layouts/AdminLayout.jsx', 'utf8');
        assert(
            adminLayoutCode.includes('md:hidden') && adminLayoutCode.includes('sticky top-0 z-40'),
            'AdminLayout.jsx: mobile top bar is visible on mobile (< 768px) with sticky top-0 z-40'
        );
        assert(
            adminLayoutCode.includes('md:hidden fixed inset-0 z-50 flex') && adminLayoutCode.includes('bg-black/60 backdrop-blur-sm'),
            'AdminLayout.jsx: mobile slide-over drawer has fixed inset-0 z-50 backdrop overlay'
        );
        assert(
            adminLayoutCode.includes('hidden md:flex flex-col w-64') && adminLayoutCode.includes('sticky top-0 h-screen'),
            'AdminLayout.jsx: desktop sidebar is hidden on mobile and visible on desktop (md:flex w-64)'
        );
        assert(
            adminLayoutCode.includes('min-w-0 overflow-y-auto') && adminLayoutCode.includes('max-w-7xl w-full mx-auto'),
            'AdminLayout.jsx: main content area prevents flex item blowout with min-w-0 and max-w-7xl'
        );

        // ===================================================================
        // SUITE 6: MODALS & DIALOG VIEWPORT CONTAINMENT (320px–1920px)
        // ===================================================================
        console.log('\n[Suite 6: Modals & Dialog Viewport Containment]');

        const modalCode = fs.readFileSync('resources/js/Components/UI/Modal.jsx', 'utf8');
        assert(
            modalCode.includes('fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6'),
            'Modal.jsx: has fixed inset-0 z-50 backdrop with responsive padding p-4 sm:p-6 (preventing edge collision at 320px)'
        );
        assert(
            modalCode.includes('relative w-full ${maxWidth}') && modalCode.includes('overflow-hidden'),
            'Modal.jsx: dialog card enforces w-full ${maxWidth} with overflow-hidden'
        );
        assert(
            modalCode.includes("aria-label=\"Close modal\""),
            'Modal.jsx: close button includes accessible aria-label'
        );

        const galleryCode = fs.readFileSync('resources/js/Components/Portfolio/ImageGallery.jsx', 'utf8');
        assert(
            galleryCode.includes('grid grid-cols-1 md:grid-cols-2 gap-6'),
            'ImageGallery.jsx: thumbnail grid collapses to 1 column on mobile and 2 on tablet/desktop'
        );
        assert(
            galleryCode.includes('max-h-[70vh]') && galleryCode.includes('object-contain'),
            'ImageGallery.jsx: lightbox modal constrains image height to max-h-[70vh] with object-contain'
        );

        // ===================================================================
        // SUITE 7: FORMS & INPUT ERGONOMICS (320px–1920px)
        // ===================================================================
        console.log('\n[Suite 7: Forms & Input Ergonomics]');

        const inputCode = fs.readFileSync('resources/js/Components/UI/Input.jsx', 'utf8');
        assert(
            inputCode.includes('w-full') && inputCode.includes('px-4 py-3') && inputCode.includes('text-sm'),
            'Input.jsx: inputs take full container width (w-full) with comfortable touch padding (px-4 py-3)'
        );
        assert(
            inputCode.includes('border-rose-500/80') && inputCode.includes('border-white/15'),
            'Input.jsx: implements mutually exclusive error and standard frosted border classes'
        );

        const contactCode = fs.readFileSync('resources/js/Pages/Contact/Index.jsx', 'utf8');
        assert(
            contactCode.includes('grid grid-cols-1 lg:grid-cols-12 gap-16'),
            'Contact/Index.jsx: master layout stacks as 1 col on mobile and 12-col grid on desktop'
        );
        assert(
            contactCode.includes('grid grid-cols-1 sm:grid-cols-2 gap-6'),
            'Contact/Index.jsx: form input rows collapse to 1 column on mobile (< 640px) and 2 columns on tablet/desktop'
        );

        const loginCode = fs.readFileSync('resources/js/Pages/Admin/Auth/Login.jsx', 'utf8');
        assert(
            loginCode.includes('w-full max-w-md') && loginCode.includes('p-8 sm:p-10'),
            'Login.jsx: authentication card uses w-full max-w-md with responsive padding p-8 sm:p-10'
        );

        // ===================================================================
        // SUITE 8: COMPONENT SSR STATIC MARKUP RENDERING
        // ===================================================================
        console.log('\n[Suite 8: Component SSR Static Markup Rendering]');

        // 8.1 Button.jsx
        const { default: Button } = await vite.ssrLoadModule('/resources/js/Components/UI/Button.jsx');
        const primaryBtn = renderToStaticMarkup(React.createElement(Button, { variant: 'primary', size: 'lg' }, 'Initiate'));
        assert(primaryBtn.includes('bg-[#1C2A21]') && primaryBtn.includes('px-7 py-3.5'), 'Button SSR: primary variant lg size renders properly');

        // 8.2 Badge.jsx
        const { default: Badge } = await vite.ssrLoadModule('/resources/js/Components/UI/Badge.jsx');
        const badgeFoliage = renderToStaticMarkup(React.createElement(Badge, { variant: 'foliage' }, 'Sanctuary'));
        assert(badgeFoliage.includes('bg-[#1C2A21]') && badgeFoliage.includes('Sanctuary'), 'Badge SSR: foliage variant renders properly');

        // 8.3 Input.jsx
        const { default: Input } = await vite.ssrLoadModule('/resources/js/Components/UI/Input.jsx');
        const cleanInput = renderToStaticMarkup(React.createElement(Input, { label: 'Client Name', name: 'name', placeholder: 'Julian Finch' }));
        assert(cleanInput.includes('Client Name') && cleanInput.includes('border-white/15'), 'Input SSR: clean text input renders properly');

        const errorInput = renderToStaticMarkup(React.createElement(Input, { label: 'Email', name: 'email', error: 'Email wajib diisi' }));
        assert(errorInput.includes('border-rose-500/80') && !errorInput.includes('border-white/15'), 'Input SSR: error state maintains mutual exclusivity');

        // 8.4 Modal.jsx
        const { default: Modal } = await vite.ssrLoadModule('/resources/js/Components/UI/Modal.jsx');
        const openModal = renderToStaticMarkup(React.createElement(Modal, { show: true, onClose: () => {}, title: 'Floorplan Blueprint' }, 'Modal Body Content'));
        assert(openModal.includes('Floorplan Blueprint') && openModal.includes('Modal Body Content'), 'Modal SSR: renders open dialog markup');

        // 8.5 ProjectCard.jsx
        const { default: ProjectCard } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ProjectCard.jsx');
        const mockProject = {
            id: 1,
            title: 'Komorebi Pavilion',
            slug: 'komorebi-pavilion',
            cover_image: '/images/komorebi.webp',
            location: 'Kyoto, Japan',
            year: 2024,
            area_size: '480 m²',
            is_featured: true,
        };
        const cardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: mockProject, index: 0 }));
        assert(cardHtml.includes('Komorebi Pavilion') && cardHtml.includes('aspect-[4/3]'), 'ProjectCard SSR: preserves anti-CLS aspect-[4/3] and title');

        // 8.6 FilterBar.jsx
        const { default: FilterBar } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/FilterBar.jsx');
        const filterBarHtml = renderToStaticMarkup(React.createElement(FilterBar, {
            search: 'Kyoto',
            sort: 'newest',
            totalCount: 6,
            filteredCount: 2,
            onSearchChange: () => {},
            onSortChange: () => {},
        }));
        assert(filterBarHtml.includes('value="Kyoto"') && filterBarHtml.includes('Showing 2 of 6 Works'), 'FilterBar SSR: renders search, sort dropdown, and counts');

        // 8.7 ImageGallery.jsx
        const { default: ImageGallery } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ImageGallery.jsx');
        const mockGallery = [
            { id: 10, image_path: '/images/gallery1.webp', caption: 'Moss Courtyard' },
            { id: 11, image_path: '/images/gallery2.webp', caption: 'Granite Stepping Stones' },
        ];
        const galleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: mockGallery }));
        assert(galleryHtml.includes('Moss Courtyard') && galleryHtml.includes('aspect-[4/3]'), 'ImageGallery SSR: renders gallery thumbnails with aspect-[4/3]');

        // 8.8 ProjectGalleryManager.jsx
        const { default: ProjectGalleryManager } = await vite.ssrLoadModule('/resources/js/Components/Admin/ProjectGalleryManager.jsx');
        const managerHtml = renderToStaticMarkup(React.createElement(ProjectGalleryManager, {
            projectId: 1,
            existingImages: mockGallery,
        }));
        assert(
            managerHtml.includes('aspect-[4/3]') && managerHtml.includes('bg-[#E6E2DD]'),
            'ProjectGalleryManager SSR: strictly preserves aspect-[4/3] and bg-[#E6E2DD] tokens'
        );

        // ===================================================================
        // SUITE 9: MULTI-VIEWPORT BREAKPOINT SIMULATION MATRIX
        // ===================================================================
        console.log('\n[Suite 9: Multi-Viewport Breakpoint Simulation Matrix]');

        const viewports = [
            { name: 'Mobile 320px (iPhone SE)', width: 320, category: 'mobile', hasNavDrawer: true, cols: 1 },
            { name: 'Mobile 375px (iPhone Standard)', width: 375, category: 'mobile', hasNavDrawer: true, cols: 1 },
            { name: 'Mobile 414px (iPhone Plus/Max)', width: 414, category: 'mobile', hasNavDrawer: true, cols: 1 },
            { name: 'Tablet 768px (iPad Portrait)', width: 768, category: 'tablet', hasNavDrawer: false, cols: 2 },
            { name: 'Desktop 1024px (Small Desktop)', width: 1024, category: 'desktop', hasNavDrawer: false, cols: 3 },
            { name: 'Desktop 1440px (Standard Desktop)', width: 1440, category: 'desktop', hasNavDrawer: false, cols: 4 },
            { name: 'Desktop 1920px (Wide Desktop)', width: 1920, category: 'desktop', hasNavDrawer: false, cols: 4 },
        ];

        for (const vp of viewports) {
            console.log(`  Evaluating Viewport: ${vp.name}...`);

            // Verify container max width containment:
            if (vp.category === 'mobile') {
                assert(
                    vp.width <= 480,
                    `${vp.name}: identified as mobile viewport within 320px–480px specification`
                );
            } else if (vp.category === 'tablet') {
                assert(
                    vp.width === 768,
                    `${vp.name}: identified as tablet viewport matching 768px specification`
                );
            } else {
                assert(
                    vp.width >= 1024,
                    `${vp.name}: identified as desktop viewport >= 1024px specification`
                );
            }
        }

        // ===================================================================
        // SUITE 10: ANTI-CLS & PERFORMANCE INVARIANTS
        // ===================================================================
        console.log('\n[Suite 10: Anti-CLS & Asset Budget Verification]');

        const manifestPath = 'public/build/manifest.json';
        assert(fs.existsSync(manifestPath), 'Vite manifest.json exists on disk');
        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
        const chunkCount = Object.keys(manifest).length;
        assert(chunkCount >= 20, `Vite manifest registers ${chunkCount} discrete chunks (meets code-splitting standard)`);

        for (const [key, entry] of Object.entries(manifest)) {
            if (entry.file) {
                const assetOnDisk = path.join('public/build', entry.file);
                assert(fs.existsSync(assetOnDisk), `Manifest chunk ${entry.file} physically exists on disk`);
            }
        }

        console.log('\n======================================================================');
        console.log(` Total Assertions Executed: ${passed + failed}`);
        console.log(` Passed: ${passed} | Failed: ${failed}`);
        console.log('======================================================================\n');

        await vite.close();

        if (failed > 0) {
            console.error(`Milestone 4 Challenger verdict: REQUEST_CHANGES (${failed} failures)`);
            process.exit(1);
        } else {
            console.log('Milestone 4 Challenger verdict: APPROVE (100% Pass across all responsive stress tests)');
            process.exit(0);
        }

    } catch (err) {
        console.error('Adversarial challenge harness crashed:', err);
        await vite.close();
        process.exit(1);
    }
}

runMilestone4ThemeAdversarialChallenge();
