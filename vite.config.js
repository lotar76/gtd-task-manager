import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [vue()],
  root: 'resources',
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
    },
  },
  build: {
    outDir: '../public',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'resources/js/app.js',
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
})


