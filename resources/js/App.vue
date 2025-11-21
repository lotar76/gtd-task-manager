<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  // Проверка авторизации при загрузке
  if (localStorage.getItem('token')) {
    authStore.checkAuth()
  }
})
</script>


