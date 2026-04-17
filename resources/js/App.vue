<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <router-view />

    <!-- PWA: предложение обновить -->
    <Transition name="slide-up">
      <div v-if="needRefresh" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3 rounded-lg bg-gray-900 px-4 py-3 text-sm text-white shadow-lg">
        <span>Доступна новая версия</span>
        <button @click="updateSW()" class="rounded bg-blue-500 px-3 py-1 text-xs font-medium hover:bg-blue-600">
          Обновить
        </button>
        <button @click="needRefresh = false" class="text-gray-400 hover:text-white">
          ✕
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// PWA: регистрация service worker
const needRefresh = ref(false)
let updateSW = () => {}

onMounted(async () => {
  // Проверка авторизации при загрузке
  if (localStorage.getItem('token')) {
    authStore.checkAuth()
  }

  // Регистрация SW только в production
  if ('serviceWorker' in navigator && import.meta.env.PROD) {
    const { registerSW } = await import('virtual:pwa-register')
    updateSW = registerSW({
      onNeedRefresh() {
        needRefresh.value = true
      },
    })
  }
})
</script>


