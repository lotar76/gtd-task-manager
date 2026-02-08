<template>
  <div class="max-w-2xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Настройки</h1>

    <!-- Профиль -->
    <div class="card">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Профиль</h2>
      <form @submit.prevent="handleProfileSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Имя</label>
          <input v-model="profileForm.name" type="text" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
          <input :value="authStore.user?.email" type="email" class="input opacity-60" disabled />
        </div>
        <div class="flex items-center space-x-3">
          <button type="submit" class="btn btn-primary" :disabled="profileLoading">
            {{ profileLoading ? 'Сохранение...' : 'Сохранить' }}
          </button>
          <span v-if="profileSuccess" class="text-sm text-green-600 dark:text-green-400">{{ profileSuccess }}</span>
          <span v-if="profileError" class="text-sm text-red-600 dark:text-red-400">{{ profileError }}</span>
        </div>
      </form>
    </div>

    <!-- Пароль -->
    <div class="card">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Изменить пароль</h2>
      <form @submit.prevent="handlePasswordSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текущий пароль</label>
          <input v-model="passwordForm.current_password" type="password" class="input" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Новый пароль</label>
          <input v-model="passwordForm.password" type="password" class="input" required minlength="8" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Подтвердите пароль</label>
          <input v-model="passwordForm.password_confirmation" type="password" class="input" required minlength="8" />
        </div>
        <div class="flex items-center space-x-3">
          <button type="submit" class="btn btn-primary" :disabled="passwordLoading">
            {{ passwordLoading ? 'Изменение...' : 'Изменить пароль' }}
          </button>
          <span v-if="passwordSuccess" class="text-sm text-green-600 dark:text-green-400">{{ passwordSuccess }}</span>
          <span v-if="passwordError" class="text-sm text-red-600 dark:text-red-400">{{ passwordError }}</span>
        </div>
      </form>
    </div>

    <!-- Внешний вид -->
    <div class="card">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Внешний вид</h2>
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-medium text-gray-900 dark:text-white">Тёмная тема</div>
          <div class="text-sm text-gray-500 dark:text-gray-400">Переключить цветовую схему интерфейса</div>
        </div>
        <button
          @click="themeStore.toggleTheme()"
          class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
          :class="themeStore.isDark ? 'bg-primary-600' : 'bg-gray-200'"
          role="switch"
          :aria-checked="themeStore.isDark"
        >
          <span
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            :class="themeStore.isDark ? 'translate-x-5' : 'translate-x-0'"
          />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'

const authStore = useAuthStore()
const themeStore = useThemeStore()

const profileForm = ref({ name: '' })
const profileLoading = ref(false)
const profileSuccess = ref('')
const profileError = ref('')

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const passwordLoading = ref(false)
const passwordSuccess = ref('')
const passwordError = ref('')

onMounted(() => {
  profileForm.value.name = authStore.user?.name || ''
})

const handleProfileSubmit = async () => {
  profileSuccess.value = ''
  profileError.value = ''
  profileLoading.value = true
  try {
    await authStore.updateProfile(profileForm.value.name)
    profileSuccess.value = 'Профиль обновлён'
    setTimeout(() => { profileSuccess.value = '' }, 3000)
  } catch (err) {
    profileError.value = err.response?.data?.message || 'Ошибка обновления профиля'
  } finally {
    profileLoading.value = false
  }
}

const handlePasswordSubmit = async () => {
  passwordSuccess.value = ''
  passwordError.value = ''
  passwordLoading.value = true
  try {
    await authStore.updatePassword(passwordForm.value)
    passwordSuccess.value = 'Пароль изменён'
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
    setTimeout(() => { passwordSuccess.value = '' }, 3000)
  } catch (err) {
    passwordError.value = err.response?.data?.message || 'Ошибка изменения пароля'
  } finally {
    passwordLoading.value = false
  }
}
</script>
