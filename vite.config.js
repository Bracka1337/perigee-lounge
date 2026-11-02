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
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
