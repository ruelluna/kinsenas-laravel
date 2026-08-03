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
        'process.env.TAMAGUI_HEADLESS': JSON.stringify(''),
        'process.env.TAMAGUI_CSS_VARIABLE_PREFIX': JSON.stringify(''),
        'process.env.TAMAGUI_WARN_ON_MISSING_VARIANT': JSON.stringify(''),
        'process.env.TAMAGUI_POSITION_STATIC': JSON.stringify(''),
        'process.env.IS_STATIC': JSON.stringify(''),
    },
    resolve: {
        alias: {
            'react-native': 'react-native-web',
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
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
            scope: '/',
            buildBase: '/build/',
            includeAssets: [
                'favicon.svg',
                'icons/icon.svg',
                'icons/icon-180.png',
                'icons/icon-192.png',
                'icons/icon-512.png',
                'icons/icon-512-maskable.png',
                'kinsenas-square-logo.png',
            ],
            manifest: {
                id: '/',
                name: 'Kinsenas',
                short_name: 'Kinsenas',
                description:
                    'Sweldo with a plan — payday allocation planner for Filipino households.',
                theme_color: '#0D7377',
                background_color: '#ffffff',
                display: 'standalone',
                start_url: '/launch',
                scope: '/',
                categories: ['finance', 'productivity'],
                icons: [
                    {
                        src: '/kinsenas-square-logo.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/kinsenas-square-logo.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/kinsenas-square-logo.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
            },
            workbox: {
                navigateFallback: null,
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
