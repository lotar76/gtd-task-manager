import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig(({ mode }) => {
  // КРИТИЧНО: Для production сборки ВСЕГДА используем '/api'
  // Игнорируем VITE_API_URL из env, чтобы избежать localhost:9090 в production
  const isProduction = mode === 'production'
  const apiUrl = isProduction ? '/api' : (process.env.VITE_API_URL || undefined)
  
  return {
    plugins: [vue()],
    root: 'resources',
    resolve: {
      alias: {
        '@': resolve(__dirname, 'resources/js'),
      },
    },
    define: {
      // Переопределяем переменную окружения
      // В production всегда '/api', в dev - из env или undefined
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


