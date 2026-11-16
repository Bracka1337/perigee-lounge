import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/vendor/chart.js',
                'resources/js/vendor/sortable.js',
                'resources/js/vendor/zxing.js',
                'resources/js/room-editor.js',
                'resources/js/lounge-toggle.js',
                'resources/js/studio/index.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // The 3D lounge view bundles three.js (about 580 kB) and is only downloaded when it is opened.
        chunkSizeWarningLimit: 700,
    },
});
