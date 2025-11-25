import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig(({ mode }) => {
  // Для production сборки используем относительный путь, если VITE_API_URL не установлен
  const apiUrl = process.env.VITE_API_URL || (mode === 'production' ? '/api' : undefined)
  
  return {
    plugins: [vue()],
    root: 'resources',
    resolve: {
      alias: {
        '@': resolve(__dirname, 'resources/js'),
      },
    },
    define: {
      // Переопределяем переменную окружения для production
      'import.meta.env.VITE_API_URL': apiUrl ? JSON.stringify(apiUrl) : 'undefined',
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


