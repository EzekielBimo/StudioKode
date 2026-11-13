import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        cors: true,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    build: {
        chunkSizeWarningLimit: 600,
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        const normalizedId = id.replace(/\\/g, '/');
                        if (
                            normalizedId.includes('/node_modules/react/') ||
                            normalizedId.includes('/node_modules/react-dom/') ||
                            normalizedId.includes('/node_modules/scheduler/')
                        ) {
                            return 'vendor-react';
                        }
                        if (normalizedId.includes('/node_modules/@inertiajs/')) {
                            return 'vendor-inertia';
                        }
                        if (
                            normalizedId.includes('/node_modules/framer-motion/') ||
                            normalizedId.includes('/node_modules/motion-dom/') ||
                            normalizedId.includes('/node_modules/motion-utils/')
                        ) {
                            return 'vendor-motion';
                        }
                        if (normalizedId.includes('/node_modules/lucide-react/')) {
                            return 'vendor-lucide';
                        }
                    }
                },
            },
        },
    },
});
