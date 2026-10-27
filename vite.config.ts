
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
                globPatterns: ['**/*.{js,css,ico,png,svg,woff2}'],
            },
        }),
    ],
});
