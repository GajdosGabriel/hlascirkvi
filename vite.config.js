import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
    ],
    resolve: {
        // Many components import siblings without the ".vue" suffix, which
        // webpack resolved for us. Keep that working instead of touching every
        // import statement.
        extensions: ['.mjs', '.js', '.mts', '.ts', '.jsx', '.tsx', '.json', '.vue'],
        alias: {
            // Blade renders the root template inside #app, so the browser build
            // must include Vue's template compiler.
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
