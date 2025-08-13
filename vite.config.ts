// @ts-ignore
import vue from '@vitejs/plugin-vue';
// @ts-ignore
import laravel from 'laravel-vite-plugin';
// @ts-ignore
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3'],
                    ui: ['lucide-vue-next'],
                    utils: ['@vueuse/core'],
                },
            },
        },
        chunkSizeWarningLimit: 1000,
        target: 'es2015',
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
            },
        },
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'lucide-vue-next'],
    },
});
