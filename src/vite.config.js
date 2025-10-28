import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/src.css', 'resources/js/src.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
