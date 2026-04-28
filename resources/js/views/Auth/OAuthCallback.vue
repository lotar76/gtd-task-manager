<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-900 dark:to-gray-800 px-4">
    <div class="max-w-md w-full">
      <div class="card text-center">
        <div v-if="error" class="space-y-4">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">Ошибка авторизации</h2>
          <p class="text-red-600 dark:text-red-400">{{ errorMessage }}</p>
          <router-link to="/login" class="btn btn-primary inline-block">
            Вернуться к входу
          </router-link>
        </div>
        <div v-else class="space-y-4">
          <div class="animate-spin h-8 w-8 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
          <p class="text-gray-600 dark:text-gray-400">Авторизация...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const error = ref(false)
const errorMessage = ref('')

const errorMessages = {
  oauth_failed: 'Не удалось получить данные от провайдера',
  unsupported_provider: 'Неподдерживаемый провайдер авторизации',
  telegram_auth_failed: 'Не удалось проверить данные Telegram',
  telegram_auth_expired: 'Данные авторизации Telegram устарели',
}

onMounted(async () => {
  const token = route.query.token
  const errorCode = route.query.error

  if (errorCode) {
    error.value = true
    errorMessage.value = errorMessages[errorCode] || 'Неизвестная ошибка'
    return
  }

  if (!token) {
    error.value = true
    errorMessage.value = 'Токен не получен'
    return
  }

  // Сохраняем токен и загружаем данные пользователя
  authStore.setToken(token)
  const success = await authStore.checkAuth()

  if (success) {
    router.push('/')
  } else {
    error.value = true
    errorMessage.value = 'Не удалось авторизоваться'
  }
})
</script>
