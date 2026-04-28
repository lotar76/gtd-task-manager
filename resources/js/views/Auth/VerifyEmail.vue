<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 dark:from-gray-900 dark:to-gray-800 px-4">
    <div class="max-w-md w-full">
      <div class="card">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Подтверждение email</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            Мы отправили 6-значный код на<br>
            <span class="font-medium text-gray-900 dark:text-white">{{ authStore.user?.email }}</span>
          </p>
        </div>

        <form @submit.prevent="handleVerify" class="space-y-6">
          <div>
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Код подтверждения
            </label>
            <input
              id="code"
              v-model="code"
              type="text"
              inputmode="numeric"
              maxlength="6"
              required
              class="input text-center text-2xl tracking-[0.5em] font-mono"
              placeholder="------"
              autocomplete="one-time-code"
            />
          </div>

          <div v-if="error" class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
            {{ error }}
          </div>

          <div v-if="success" class="text-green-600 dark:text-green-400 text-sm bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
            {{ success }}
          </div>

          <button
            type="submit"
            :disabled="loading || code.length !== 6"
            class="w-full btn btn-primary"
          >
            {{ loading ? 'Проверка...' : 'Подтвердить' }}
          </button>
        </form>

        <div class="mt-6 text-center space-y-3">
          <button
            @click="handleResend"
            :disabled="resendLoading || resendCooldown > 0"
            class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium disabled:opacity-50"
          >
            {{ resendCooldown > 0 ? `Отправить повторно (${resendCooldown}с)` : resendLoading ? 'Отправка...' : 'Отправить код повторно' }}
          </button>

          <div>
            <button
              @click="handleLogout"
              class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            >
              Выйти
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const code = ref('')
const error = ref('')
const success = ref('')
const loading = ref(false)
const resendLoading = ref(false)
const resendCooldown = ref(0)
let cooldownTimer = null

const handleVerify = async () => {
  error.value = ''
  loading.value = true

  try {
    await api.post('/v1/email/verify', { code: code.value })
    await authStore.checkAuth()
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.message || 'Неверный код'
  } finally {
    loading.value = false
  }
}

const handleResend = async () => {
  error.value = ''
  success.value = ''
  resendLoading.value = true

  try {
    const response = await api.post('/v1/email/resend')
    success.value = response.message || 'Код отправлен'
    startCooldown()
  } catch (err) {
    error.value = err.response?.data?.message || 'Ошибка отправки'
  } finally {
    resendLoading.value = false
  }
}

const startCooldown = () => {
  resendCooldown.value = 60
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownTimer)
    }
  }, 1000)
}

const handleLogout = async () => {
  await authStore.logout()
}

onMounted(() => {
  startCooldown()
})

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})
</script>
