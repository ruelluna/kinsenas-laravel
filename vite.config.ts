import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig(({ mode }) => ({
    define: {
        'process.env.NODE_ENV': JSON.stringify(mode),
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('DM Sans', {
                    weights: [400, 500, 600, 700],
                }),
                bunny('Space Grotesk', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        VitePWA({
            registerType: 'prompt',
            injectRegister: null,
            scope: '/',
            buildBase: '/build/',
            includeAssets: [
                'kinsenas-square-logo.png',
                'icons/icon-180.png',
                'icons/icon-192.png',
                'icons/icon-512.png',
                'icons/icon-512-maskable.png',
            ],
            manifest: {
                id: '/',
                name: 'Kinsenas',
                short_name: 'Kinsenas',
                description:
                    'Sweldo with a plan — payday allocation planner for Filipino households.',
                theme_color: '#1E8B75',
                background_color: '#F7FAF9',
                display: 'standalone',
                start_url: '/launch',
                scope: '/',
                categories: ['finance', 'productivity'],
                icons: [
                    {
                        src: '/icons/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512-maskable.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
            workbox: {
                navigateFallback: null,
                globIgnores: ['**/registerSW.js'],
                importScripts: ['/sw-push.js'],
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) =>
                            request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'kinsenas-pages',
                            networkTimeoutSeconds: 10,
                        },
                    },
                    {
                        urlPattern: ({ url }) =>
                            url.pathname.startsWith('/brand/') ||
                            url.pathname.startsWith('/icons/'),
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'kinsenas-static-images',
                            expiration: {
                                maxEntries: 32,
                                maxAgeSeconds: 60 * 60 * 24 * 30,
                            },
                        },
                    },
                ],
            },
            devOptions: {
                enabled: process.env.VITE_PWA_DEV === 'true',
            },
        }),
    ],
}));
