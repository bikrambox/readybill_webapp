import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/css/app.css',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // i18n(),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        rollupOptions: {
            output: {
                assetFileNames: '[ext]/[name].[hash][extname]',
            },
        },
    },
    define: {
        // Define Vue feature flags
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(true), // Enable detailed hydration mismatch errors
        __VUE_OPTIONS_API__: JSON.stringify(true), // Enable Options API (set to false if using only Composition API)
        __VUE_PROD_DEVTOOLS__: JSON.stringify(false), // Disable devtools in production
    },
});