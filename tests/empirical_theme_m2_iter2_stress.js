import { createServer } from 'vite';
import { renderToStaticMarkup } from 'react-dom/server';
import React from 'react';
import fs from 'fs';
import path from 'path';

async function runAdversarialVerification() {
    console.log('=== StudioKode Milestone 2 Iteration 2 Empirical Verification Harness ===\n');

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
        // 1. Input.jsx CSS Cascade and Error State Verification
        // ==========================================
        console.log('\n[Suite 1: Input.jsx CSS Cascade & Error Exclusivity]');
        const { default: Input } = await vite.ssrLoadModule('/resources/js/Components/UI/Input.jsx');

        // Case 1A: Unfocused error state on text input
        const errorInputHtml = renderToStaticMarkup(React.createElement(Input, {
            name: 'client_email',
            error: 'Alamat email wajib diisi',
            placeholder: 'studio@domain.com',
        }));
        
        assert(errorInputHtml.includes('border-rose-500/80'), 'Input error state contains border-rose-500/80');
        assert(!errorInputHtml.includes('border-white/15'), 'Input error state strictly DOES NOT contain border-white/15');
        assert(errorInputHtml.includes('focus:border-rose-500'), 'Input error state contains focus:border-rose-500');
        assert(errorInputHtml.includes('focus:ring-rose-500/20'), 'Input error state contains focus:ring-rose-500/20');
        assert(!errorInputHtml.includes('focus:border-[#88B896]'), 'Input error state strictly DOES NOT contain focus:border-[#88B896]');
        assert(errorInputHtml.includes('text-rose-400 font-medium'), 'Input error text renders with rose styling');
        assert(errorInputHtml.includes('Alamat email wajib diisi'), 'Input error text message matches');

        // Case 1B: Clean (non-error) text input state
        const cleanInputHtml = renderToStaticMarkup(React.createElement(Input, {
            name: 'clean_field',
            placeholder: 'No errors here',
        }));

        assert(cleanInputHtml.includes('border-white/15'), 'Clean input state contains border-white/15');
        assert(!cleanInputHtml.includes('border-rose-500/80'), 'Clean input state strictly DOES NOT contain border-rose-500/80');
        assert(cleanInputHtml.includes('focus:border-[#88B896]'), 'Clean input state contains focus:border-[#88B896]');
        assert(cleanInputHtml.includes('focus:ring-[#88B896]/30'), 'Clean input state contains focus:ring-[#88B896]/30');
        assert(!cleanInputHtml.includes('focus:border-rose-500'), 'Clean input state strictly DOES NOT contain focus:border-rose-500');
        assert(!cleanInputHtml.includes('text-rose-400'), 'Clean input state does not render error text');

        // Case 1C: Falsy error edge cases (empty string, null, undefined)
        const emptyErrorHtml = renderToStaticMarkup(React.createElement(Input, { name: 'falsy1', error: '' }));
        assert(emptyErrorHtml.includes('border-white/15') && !emptyErrorHtml.includes('border-rose-500/80'), 'Empty string error treats input as non-error');

        const nullErrorHtml = renderToStaticMarkup(React.createElement(Input, { name: 'falsy2', error: null }));
        assert(nullErrorHtml.includes('border-white/15') && !nullErrorHtml.includes('border-rose-500/80'), 'Null error treats input as non-error');

        // Case 1D: Multiline textarea error state
        const errorTextareaHtml = renderToStaticMarkup(React.createElement(Input, {
            name: 'project_brief',
            multiline: true,
            error: 'Deskripsi proyek terlalu pendek',
        }));
        assert(errorTextareaHtml.startsWith('<div') && errorTextareaHtml.includes('<textarea'), 'Multiline renders <textarea>');
        assert(errorTextareaHtml.includes('border-rose-500/80'), 'Textarea error state contains border-rose-500/80');
        assert(!errorTextareaHtml.includes('border-white/15'), 'Textarea error state strictly DOES NOT contain border-white/15');
        assert(errorTextareaHtml.includes('Deskripsi proyek terlalu pendek'), 'Textarea renders error message');

        // Case 1E: Custom className preserved without breaking mutual exclusivity
        const customErrorHtml = renderToStaticMarkup(React.createElement(Input, {
            name: 'custom_input',
            className: 'my-custom-layout-class',
            error: 'Invalid input',
        }));
        assert(customErrorHtml.includes('my-custom-layout-class'), 'Custom className preserved on error input');
        assert(customErrorHtml.includes('border-rose-500/80') && !customErrorHtml.includes('border-white/15'), 'Custom class does not disrupt border mutual exclusivity');


        // ==========================================
        // 2. Modal.jsx show vs isOpen Prop Compatibility
        // ==========================================
        console.log('\n[Suite 2: Modal.jsx show & isOpen Prop Matrix]');
        const { default: Modal } = await vite.ssrLoadModule('/resources/js/Components/UI/Modal.jsx');

        const dummyClose = () => {};

        // Case 2A: Using 'show=true' (canonical PROJECT.md contract)
        const showTrueHtml = renderToStaticMarkup(React.createElement(Modal, {
            show: true,
            onClose: dummyClose,
            title: 'Canonical Show Dialog',
        }, 'Inner Content Show True'));
        assert(showTrueHtml !== '', 'Modal opens with show=true');
        assert(showTrueHtml.includes('Canonical Show Dialog'), 'Modal renders title with show=true');
        assert(showTrueHtml.includes('Inner Content Show True'), 'Modal renders children with show=true');
        assert(showTrueHtml.includes('bg-[#0B0E0C]/90') && showTrueHtml.includes('backdrop-blur-md'), 'Modal has glass backdrop');
        assert(showTrueHtml.includes('border-white/15'), 'Modal has frosted border');

        // Case 2B: Using 'show=false'
        const showFalseHtml = renderToStaticMarkup(React.createElement(Modal, {
            show: false,
            onClose: dummyClose,
            title: 'Hidden Dialog',
        }, 'Should Not Render'));
        assert(showFalseHtml === '', 'Modal renders empty markup when show=false');

        // Case 2C: Using 'isOpen=true' (existing ImageGallery.jsx contract)
        const isOpenTrueHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: true,
            onClose: dummyClose,
            title: 'Backwards Compatible Dialog',
        }, 'Inner Content isOpen True'));
        assert(isOpenTrueHtml !== '', 'Modal opens with isOpen=true');
        assert(isOpenTrueHtml.includes('Backwards Compatible Dialog'), 'Modal renders title with isOpen=true');
        assert(isOpenTrueHtml.includes('Inner Content isOpen True'), 'Modal renders children with isOpen=true');

        // Case 2D: Using 'isOpen=false'
        const isOpenFalseHtml = renderToStaticMarkup(React.createElement(Modal, {
            isOpen: false,
            onClose: dummyClose,
            title: 'Hidden Dialog',
        }, 'Should Not Render'));
        assert(isOpenFalseHtml === '', 'Modal renders empty markup when isOpen=false');

        // Case 2E: Precedence stress: show takes precedence when both provided
        const showTrueIsOpenFalseHtml = renderToStaticMarkup(React.createElement(Modal, {
            show: true,
            isOpen: false,
            onClose: dummyClose,
        }, 'Show Precedence'));
        assert(showTrueIsOpenFalseHtml.includes('Show Precedence'), 'show=true takes precedence over isOpen=false');

        const showFalseIsOpenTrueHtml = renderToStaticMarkup(React.createElement(Modal, {
            show: false,
            isOpen: true,
            onClose: dummyClose,
        }, 'Should Be Suppressed'));
        assert(showFalseIsOpenTrueHtml === '', 'show=false takes precedence over isOpen=true');

        // Case 2F: Both undefined
        const bothUndefinedHtml = renderToStaticMarkup(React.createElement(Modal, {
            onClose: dummyClose,
        }, 'Should Be Suppressed'));
        assert(bothUndefinedHtml === '', 'Modal renders empty markup when both show and isOpen are undefined');

        // Case 2G: Custom maxWidth and aria-label
        const customMaxWidthHtml = renderToStaticMarkup(React.createElement(Modal, {
            show: true,
            onClose: dummyClose,
            maxWidth: 'max-w-3xl',
        }, 'Max width check'));
        assert(customMaxWidthHtml.includes('max-w-3xl'), 'Modal honors custom maxWidth');
        assert(customMaxWidthHtml.includes('aria-label="Close modal"'), 'Modal contains accessible close button aria-label');


        // ==========================================
        // 3. ImageGallery.jsx integration check with Modal
        // ==========================================
        console.log('\n[Suite 3: ImageGallery Integration with Remediated Modal]');
        const { default: ImageGallery } = await vite.ssrLoadModule('/resources/js/Components/Portfolio/ImageGallery.jsx');

        const galleryImages = [
            { id: 1, image_path: '/images/garden1.webp', caption: 'Moss Terrace' },
            { id: 2, image_path: '/images/garden2.webp', caption: 'Stone Basin' },
        ];
        const galleryHtml = renderToStaticMarkup(React.createElement(ImageGallery, { images: galleryImages }));
        assert(galleryHtml.includes('Moss Terrace'), 'ImageGallery renders thumbnails correctly');
        assert(galleryHtml.includes('aspect-[4/3]'), 'ImageGallery preserves thumbnail aspect-[4/3]');


        console.log(`\n======================================================`);
        console.log(`Total Empirical Assertions: ${passed + failed} | Passed: ${passed} | Failed: ${failed}`);
        console.log(`======================================================\n`);

        await vite.close();
        if (failed > 0) {
            process.exit(1);
        }
    } catch (err) {
        console.error('Empirical harness crashed:', err);
        await vite.close();
        process.exit(1);
    }
}

runAdversarialVerification();
