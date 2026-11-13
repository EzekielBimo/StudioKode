import { createServer } from 'vite';
import { renderToStaticMarkup } from 'react-dom/server';
import React from 'react';

async function runEmpiricalStressTests() {
    console.log('=== StudioKode Milestone 2 Empirical UI Component Stress Harness ===\n');

    const vite = await createServer({ server: { middlewareMode: true } });
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

    try {
        // ==========================================
        // 1. Button.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 1: Button.jsx]');
        const { default: Button } = await vite.ssrLoadModule('/resources/js/Components/UI/Button.jsx');

        // Test all 5 variants
        const variants = ['primary', 'secondary', 'outline', 'outline-light', 'ghost'];
        for (const v of variants) {
            const html = renderToStaticMarkup(React.createElement(Button, { variant: v }, `Button ${v}`));
            assert(html.includes(`Button ${v}`), `Button renders variant: ${v}`);
            if (v === 'primary') assert(html.includes('bg-[#1C2A21]'), 'Button primary has moss dark emerald bg');
            if (v === 'secondary') assert(html.includes('bg-[rgba(20,32,24,0.75)]') && html.includes('backdrop-blur-md'), 'Button secondary has dark moss glass');
            if (v === 'outline') assert(html.includes('border-[#88B896]/50'), 'Button outline has moss border');
            if (v === 'outline-light') assert(html.includes('border-white/20'), 'Button outline-light has frosted white border');
            if (v === 'ghost') assert(html.includes('text-[#C4C9C3]'), 'Button ghost has sage text');
        }

        // Test invalid variant fallback
        const invalidVarHtml = renderToStaticMarkup(React.createElement(Button, { variant: 'nonexistent-variant' }, 'Fallback'));
        assert(invalidVarHtml.includes('bg-[#1C2A21]'), 'Button falls back to primary on invalid variant');

        // Test all sizes
        const sizes = ['sm', 'md', 'lg'];
        for (const s of sizes) {
            const html = renderToStaticMarkup(React.createElement(Button, { size: s }, `Size ${s}`));
            if (s === 'sm') assert(html.includes('px-3.5') && html.includes('py-1.5'), 'Button sm size padding');
            if (s === 'md') assert(html.includes('px-5') && html.includes('py-2.5'), 'Button md size padding');
            if (s === 'lg') assert(html.includes('px-7') && html.includes('py-3.5'), 'Button lg size padding');
        }

        // Test invalid size fallback
        const invalidSizeHtml = renderToStaticMarkup(React.createElement(Button, { size: 'extra-gigantic' }, 'Fallback Size'));
        assert(invalidSizeHtml.includes('px-5 py-2.5'), 'Button falls back to md on invalid size');

        // Test href renders Link (a tag) vs button tag
        const linkBtnHtml = renderToStaticMarkup(React.createElement(Button, { href: '/sanctuaries' }, 'Explore'));
        assert(linkBtnHtml.startsWith('<a') && linkBtnHtml.includes('href="/sanctuaries"'), 'Button with href renders <a> link');

        const regularBtnHtml = renderToStaticMarkup(React.createElement(Button, { type: 'submit' }, 'Submit'));
        assert(regularBtnHtml.startsWith('<button') && regularBtnHtml.includes('type="submit"'), 'Button without href renders <button type="submit">');

        // Test disabled state
        const disabledBtnHtml = renderToStaticMarkup(React.createElement(Button, { disabled: true }, 'Disabled'));
        assert(disabledBtnHtml.includes('disabled=""') && disabledBtnHtml.includes('disabled:opacity-50'), 'Button honors disabled state');

        // Test custom className and props propagation
        const customBtnHtml = renderToStaticMarkup(React.createElement(Button, {
            className: 'custom-adversarial-class',
            'data-testid': 'adv-button',
            'aria-label': 'Adversarial button'
        }, 'Props'));
        assert(customBtnHtml.includes('custom-adversarial-class'), 'Button preserves custom className');
        assert(customBtnHtml.includes('data-testid="adv-button"'), 'Button preserves data attributes');
        assert(customBtnHtml.includes('aria-label="Adversarial button"'), 'Button preserves aria-label');


        // ==========================================
        // 2. Badge.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 2: Badge.jsx]');
        const { default: Badge } = await vite.ssrLoadModule('/resources/js/Components/UI/Badge.jsx');

        const badgeVariants = ['neutral', 'foliage', 'outline', 'outline-light'];
        for (const bv of badgeVariants) {
            const html = renderToStaticMarkup(React.createElement(Badge, { variant: bv }, `Badge ${bv}`));
            assert(html.includes(`Badge ${bv}`), `Badge renders variant: ${bv}`);
            if (bv === 'neutral') assert(html.includes('bg-[rgba(20,32,24,0.65)]'), 'Badge neutral dark moss glass');
            if (bv === 'foliage') assert(html.includes('bg-[#152319]') && html.includes('text-[#88B896]'), 'Badge foliage dark moss accent');
            if (bv === 'outline') assert(html.includes('border-white/15'), 'Badge outline frosted border');
            if (bv === 'outline-light') assert(html.includes('border-white/25'), 'Badge outline-light frosted border');
        }

        // Test invalid variant fallback
        const invalidBadgeHtml = renderToStaticMarkup(React.createElement(Badge, { variant: 'mystery' }, 'Fallback Badge'));
        assert(invalidBadgeHtml.includes('bg-[rgba(20,32,24,0.65)]'), 'Badge falls back to neutral on invalid variant');

        // Custom className
        const customBadgeHtml = renderToStaticMarkup(React.createElement(Badge, { className: 'adversarial-badge' }, 'Custom'));
        assert(customBadgeHtml.includes('adversarial-badge'), 'Badge appends custom className');


        // ==========================================
        // 3. Input.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 3: Input.jsx]');
        const { default: Input } = await vite.ssrLoadModule('/resources/js/Components/UI/Input.jsx');

        // Standard text input
        const standardInputHtml = renderToStaticMarkup(React.createElement(Input, {
            label: 'Full Legal Name',
            name: 'full_name',
            placeholder: 'Enter name',
            required: true,
        }));
        assert(standardInputHtml.includes('<input'), 'Input renders <input> for standard text');
        assert(standardInputHtml.includes('id="full_name"'), 'Input uses name as id fallback');
        assert(standardInputHtml.includes('Full Legal Name'), 'Input renders label text');
        assert(standardInputHtml.includes('*'), 'Input renders required asterisk');
        assert(standardInputHtml.includes('bg-[#121814]/90'), 'Input uses elevated dark obsidian surface #121814');
        assert(standardInputHtml.includes('text-[#F4F2ED]'), 'Input uses high-contrast cream text');
        assert(standardInputHtml.includes('placeholder:text-[#A8B0A6]/40'), 'Input uses sage placeholder styling');

        // Multiline textarea
        const multilineHtml = renderToStaticMarkup(React.createElement(Input, {
            label: 'Project Vision',
            name: 'vision',
            multiline: true,
            rows: 6,
        }));
        assert(multilineHtml.includes('<textarea'), 'Input renders <textarea> when multiline=true');
        assert(multilineHtml.includes('rows="6"'), 'Input respects custom rows attribute');

        // Type textarea
        const textareaTypeHtml = renderToStaticMarkup(React.createElement(Input, {
            name: 'notes',
            type: 'textarea',
        }));
        assert(textareaTypeHtml.includes('<textarea'), 'Input renders <textarea> when type="textarea"');

        // Error state
        const errorInputHtml = renderToStaticMarkup(React.createElement(Input, {
            name: 'email',
            error: 'Alamat email wajib diisi',
        }));
        assert(errorInputHtml.includes('Alamat email wajib diisi'), 'Input renders error message');
        assert(errorInputHtml.includes('border-rose-500/80'), 'Input has rose error border classes');
        assert(errorInputHtml.includes('text-rose-400'), 'Input error text has rose color');

        // Label omission
        const noLabelInputHtml = renderToStaticMarkup(React.createElement(Input, { name: 'unlabeled' }));
        assert(!noLabelInputHtml.includes('<label'), 'Input renders cleanly without <label> when label prop omitted');


        // ==========================================
        // 4. Modal.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 4: Modal.jsx]');
        const { default: Modal } = await vite.ssrLoadModule('/resources/js/Components/UI/Modal.jsx');

        // Closed modal
        const closedModalHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: false,
            onClose: () => {},
            title: 'Closed Dialog',
        }, 'Inner Content'));
        assert(closedModalHtml === '', 'Modal renders nothing when isOpen=false');

        // Open modal
        const openModalHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: true,
            onClose: () => {},
            title: 'Sanctuary Masterplan Preview',
            maxWidth: 'max-w-4xl',
        }, React.createElement('div', { id: 'modal-inner' }, 'Secret Blueprints')));
        assert(openModalHtml.includes('Sanctuary Masterplan Preview'), 'Modal renders title');
        assert(openModalHtml.includes('Secret Blueprints'), 'Modal renders children content');
        assert(openModalHtml.includes('bg-[#0B0E0C]/90') && openModalHtml.includes('backdrop-blur-md'), 'Modal overlay has obsidian glass backdrop');
        assert(openModalHtml.includes('bg-[#0E1310]/95') && openModalHtml.includes('backdrop-blur-xl'), 'Modal container has elevated obsidian card body');
        assert(openModalHtml.includes('border-white/15'), 'Modal container has frosted glass border');
        assert(openModalHtml.includes('aria-label="Close modal"'), 'Modal close button has accessible aria-label');
        assert(openModalHtml.includes('max-w-4xl'), 'Modal honors custom maxWidth prop');

        // Open modal without title
        const untitledModalHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: true,
            onClose: () => {},
        }, 'Untitled Modal Body'));
        assert(untitledModalHtml.includes('Untitled Modal Body'), 'Modal renders without crashing when title is omitted');


        // ==========================================
        // 5. ProjectCard.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 5: ProjectCard.jsx]');
        const { default: ProjectCard } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ProjectCard.jsx');

        // Null safety
        const nullCardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: null }));
        assert(nullCardHtml === '', 'ProjectCard safely returns null when project is null');

        // Sample project data
        const sampleProject = {
            id: 1,
            slug: 'sanctuary-ubud-valley',
            title: 'Ubud Valley Living Topography',
            cover_image: 'https://images.unsplash.com/photo-1?w=800',
            location: 'Gianyar, Bali',
            area_size: '4,500 m²',
            year: 2024,
            excerpt: 'Terraced stone walls and endemic bamboo forest preserving river gorge ecology.',
            is_featured: true,
        };

        const cardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: sampleProject }));
        assert(cardHtml.includes('href="/projects/sanctuary-ubud-valley"'), 'ProjectCard links to project slug');
        assert(cardHtml.includes('Ubud Valley Living Topography'), 'ProjectCard renders project title');
        assert(cardHtml.includes('aspect-[16/10]'), 'ProjectCard preserves strict anti-CLS aspect-[16/10]');
        assert(cardHtml.includes('bg-[#121814]'), 'ProjectCard image container has elevated dark obsidian background');
        assert(cardHtml.includes('group-hover:scale-105'), 'ProjectCard has image zoom hover effect');
        assert(cardHtml.includes('group-hover:text-[#88B896]'), 'ProjectCard has title moss hover transition');
        assert(cardHtml.includes('Featured Sanctuary'), 'ProjectCard renders Featured Sanctuary badge when is_featured=true');
        assert(cardHtml.includes('4,500 m²'), 'ProjectCard renders area size');
        assert(cardHtml.includes('Gianyar, Bali'), 'ProjectCard renders location');
        assert(cardHtml.includes('loading="lazy"'), 'ProjectCard defaults to loading="lazy"');

        // Priority card
        const priorityCardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: sampleProject, priority: true }));
        assert(priorityCardHtml.includes('loading="eager"'), 'ProjectCard uses loading="eager" when priority=true');

        // Non-featured card without excerpt
        const basicProject = { ...sampleProject, is_featured: false, excerpt: null };
        const basicCardHtml = renderToStaticMarkup(React.createElement(ProjectCard, { project: basicProject }));
        assert(!basicCardHtml.includes('Featured Sanctuary'), 'ProjectCard omits featured badge when is_featured=false');
        assert(!basicCardHtml.includes('line-clamp-2'), 'ProjectCard cleanly omits excerpt element when excerpt is null');


        // ==========================================
        // 6. FilterBar.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 6: FilterBar.jsx]');
        const { default: FilterBar } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/FilterBar.jsx');

        const filterHtml = renderToStaticMarkup(React.createElement(FilterBar, {
            search: 'bamboo',
            sort: 'newest',
            onSearchChange: () => {},
            onSortChange: () => {},
            totalCount: 12,
            filteredCount: 4,
        }));
        assert(filterHtml.includes('value="bamboo"'), 'FilterBar reflects search value');
        assert(filterHtml.includes('aria-label="Clear search"'), 'FilterBar shows clear button when search is non-empty');
        assert(filterHtml.includes('Showing 4 of 12 Works'), 'FilterBar renders count indicators');
        assert(filterHtml.includes('border-b border-white/10'), 'FilterBar has frosted divider border-b border-white/10');
        assert(filterHtml.includes('bg-[#121814]'), 'FilterBar dropdown has dark elevated obsidian surface');
        assert(filterHtml.includes('text-[#F4F2ED]'), 'FilterBar dropdown text has high contrast cream');
        assert(filterHtml.includes('for="sort-select"'), 'FilterBar includes accessible HTML for attribute on label');

        // Empty search FilterBar
        const emptyFilterHtml = renderToStaticMarkup(React.createElement(FilterBar, {
            search: '',
            sort: 'area_desc',
            onSearchChange: () => {},
            onSortChange: () => {},
            totalCount: 0,
            filteredCount: 0,
        }));
        assert(!emptyFilterHtml.includes('aria-label="Clear search"'), 'FilterBar omits clear button when search is empty');
        assert(!emptyFilterHtml.includes('Showing 0 of 0 Works'), 'FilterBar omits count when totalCount=0');


        // ==========================================
        // 7. ImageGallery.jsx Stress Testing
        // ==========================================
        console.log('\n[Suite 7: ImageGallery.jsx]');
        const { default: ImageGallery } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ImageGallery.jsx');

        // Empty gallery
        const emptyGalleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: [] }));
        assert(emptyGalleryHtml === '', 'ImageGallery safely returns null when images is empty array');

        // Undefined gallery
        const undefinedGalleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, {}));
        assert(undefinedGalleryHtml === '', 'ImageGallery safely returns null when images prop is omitted');

        // Null gallery edge case check
        let nullImagesThrew = false;
        try {
            renderToStaticMarkup(React.createElement(ImageGallery, { images: null }));
        } catch (e) {
            nullImagesThrew = true;
            console.log(`  ℹ OBSERVATION: ImageGallery throws TypeError when images=null is passed (${e.message})`);
        }
        assert(nullImagesThrew, 'ImageGallery dependency array evaluates images.length on null (Vulnerability/Edge case confirmed)');

        // Multi-image gallery
        const sampleImages = [
            { id: 101, image_path: 'https://images.unsplash.com/img1', caption: 'Cantilevered pool deck' },
            { id: 102, image_path: 'https://images.unsplash.com/img2', caption: 'Dry-stone retaining wall' },
            { id: 103, image_path: 'https://images.unsplash.com/img3', caption: null },
        ];

        const galleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: sampleImages }));
        assert(galleryHtml.includes('aspect-[4/3]'), 'ImageGallery preserves thumbnail aspect-[4/3]');
        assert(galleryHtml.includes('bg-[#121814]'), 'ImageGallery thumbnail container has elevated obsidian background');
        assert(galleryHtml.includes('border border-white/10'), 'ImageGallery thumbnail container has frosted border');
        assert(galleryHtml.includes('Cantilevered pool deck'), 'ImageGallery renders caption when available');
        assert(galleryHtml.includes('group-hover:scale-105'), 'ImageGallery thumbnail has hover zoom transition');
        assert(galleryHtml.includes('backdrop-blur-md'), 'ImageGallery zoom button has backdrop blur');

        console.log(`\n======================================================`);
        console.log(`Total Assertions: ${passed + failed} | Passed: ${passed} | Failed: ${failed}`);
        console.log(`======================================================\n`);

        await vite.close();
        if (failed > 0) {
            process.exit(1);
        }
    } catch (err) {
        console.error('Stress harness encountered unhandled error:', err);
        await vite.close();
        process.exit(1);
    }
}

runEmpiricalStressTests();
