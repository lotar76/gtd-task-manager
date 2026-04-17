import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'
import { resolve } from 'path'

export default defineConfig(({ mode }) => {
  return {
    plugins: [
      vue(),
      VitePWA({
        strategies: 'injectManifest',
        srcDir: 'js',
        filename: 'sw.js',
        registerType: 'prompt',
        outDir: '../public',
        scope: '/',
        base: '/',
        manifest: false,
        injectManifest: {
          globPatterns: [],
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


