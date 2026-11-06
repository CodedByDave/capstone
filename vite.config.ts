import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

const isHostingerBuild = process.env.HOSTINGER_BUILD === 'true';

export default defineConfig({
    // Pre-bundle every page's dependencies at dev start. Otherwise Vite discovers them
    // when a page is first opened, re-optimizes, and the in-app navigation hangs
    // until the browser is refreshed.
    optimizeDeps: {
        entries: ['resources/js/app.ts', 'resources/js/pages/**/*.vue'],
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),

        tailwindcss(),

        // Generate Wayfinder types locally, but use committed generated files
        // when Hostinger builds in its Node-only environment.
        ...(!isHostingerBuild
            ? [
                  wayfinder({
                      formVariants: true,
                      // Regenerating deletes and rewrites ~220 files, and for several seconds
                      // Vite answers 404 for them: a page that loads then is a white page. The
                      // default also reruns it on every save of a Form Request, Resource or
                      // Middleware, which cannot change the generated helpers. Only the route
                      // files do; after adding a controller method, run
                      // `php artisan wayfinder:generate --with-form`.
                      patterns: ['routes/**/*.php'],
                  }),
              ]
            : []),

        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

        VitePWA({
            registerType: 'autoUpdate',
            // The worker file lives in /build/, so without this its scope is /build/
            // and it never controls the site root, which makes the app non-installable.
            // Needs the Service-Worker-Allowed header (see public/.htaccess).
            scope: '/',
            includeAssets: ['favicon.ico', 'apple-touch-icon.png'],
            manifest: {
                name: 'LaundryHub',
                short_name: 'LaundryHub',
                description: 'Laundry Shop Management Platform',
                theme_color: '#ffffff',
                background_color: '#ffffff',
                display: 'standalone',
                scope: '/',
                start_url: '/',
                icons: [
                    {
                        src: '/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                navigateFallback: null,
                // Shows driver job alerts as system notifications while the app is closed.
                importScripts: ['/push-sw.js'],
                globPatterns: ['**/*.{js,css,ico,png,svg,woff2}'],
            },
        }),
    ],
});
