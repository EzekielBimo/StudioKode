import './bootstrap';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'StudioKode';

// Inertia-laravel v2+ outputs JSON in <script data-page="app">, not on div#app.
// Bridge: read initialPage directly and synchronize div#app[data-page]
const pageScript = typeof document !== 'undefined'
    ? (document.querySelector('script[data-page="app"]') || document.querySelector('script[data-page]'))
    : null;

let initialPage;
if (pageScript && pageScript.textContent) {
    try {
        initialPage = JSON.parse(pageScript.textContent.trim());
    } catch (e) {
        console.error('Failed to parse Inertia page JSON:', e);
    }
}

const el = typeof document !== 'undefined' ? document.getElementById('app') : null;
if (el && pageScript && !el.dataset.page) {
    el.dataset.page = pageScript.textContent.trim();
}

createInertiaApp({
    page: initialPage,
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#88B896',
    },
});
