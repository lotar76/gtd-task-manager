import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'
import { resolve } from 'path'

export default defineConfig(({ mode }) => {
  return {
    plugins: [
      vue(),
      VitePWA({
        registerType: 'prompt',
        outDir: '../public',
        scope: '/',
        base: '/',
        manifest: false, // используем существующий site.webmanifest
        workbox: {
          globPatterns: [],
          navigateFallback: null,
          runtimeCaching: [
            {
              urlPattern: /\.(?:js|css|woff2?)$/,
              handler: 'CacheFirst',
              options: {
                cacheName: 'static-assets',
                expiration: { maxEntries: 60, maxAgeSeconds: 30 * 24 * 60 * 60 },
              },
            },
            {
              urlPattern: /\.(?:png|jpg|jpeg|svg|ico|webp)$/,
              handler: 'CacheFirst',
              options: {
                cacheName: 'images',
                expiration: { maxEntries: 60, maxAgeSeconds: 30 * 24 * 60 * 60 },
              },
            },
            {
              urlPattern: /\/api\//,
              handler: 'NetworkFirst',
              options: {
                cacheName: 'api-cache',
                expiration: { maxEntries: 100, maxAgeSeconds: 300 },
              },
            },
            {
              urlPattern: ({ request }) => request.mode === 'navigate',
              handler: 'NetworkFirst',
              options: {
                cacheName: 'pages',
              },
            },
          ],
        },
        devOptions: {
          enabled: false,
        },
      }),
    ],
    root: 'resources',
    publicDir: '../public',
    resolve: {
      alias: {
        '@': resolve(__dirname, 'resources/js'),
      },
    },
    build: {
      outDir: '../public',
      emptyOutDir: false,
      manifest: true,
      rollupOptions: {
        input: resolve(__dirname, 'resources/index.html'),
      },
    },
    server: {
      host: '0.0.0.0',
      port: 5173,
      hmr: {
        host: 'localhost',
      },
      watch: {
        usePolling: true,
      },
    },
  }
})


