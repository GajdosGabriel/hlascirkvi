import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';

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
            // Vue 2.7 ships a runtime-only build by default; the app mounts with
            // `new Vue({ el: '#app' })` and relies on in-DOM templates written in
            // Blade, so it needs the build that includes the template compiler.
            vue: 'vue/dist/vue.esm.js',
        },
    },
});
