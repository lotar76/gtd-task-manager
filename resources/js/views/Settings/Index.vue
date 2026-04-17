<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Настройки</h1>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex space-x-6 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="pb-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
            :class="activeTab === tab.key
              ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
              : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Профиль -->
      <div v-if="activeTab === 'profile'" class="space-y-6">
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

        <div class="card" v-if="workspace">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Библейские стихи</h2>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Показывать цитаты из Библии в Зеркале жизни
              </p>
            </div>
            <button
              type="button"
              @click="toggleFaith"
              :disabled="faithLoading"
              class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
              :class="faithEnabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'"
              role="switch"
              :aria-checked="faithEnabled"
            >
              <span
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                :class="faithEnabled ? 'translate-x-5' : 'translate-x-0'"
              />
            </button>
          </div>
        </div>

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

        <!-- Push-уведомления -->
        <div class="card">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Push-уведомления</h2>

          <template v-if="!pushSupported">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Ваш браузер не поддерживает push-уведомления
            </p>
          </template>

          <template v-else-if="pushPermission === 'denied'">
            <p class="text-sm text-red-500 dark:text-red-400">
              Уведомления заблокированы в настройках браузера. Разрешите их вручную.
            </p>
          </template>

          <template v-else>
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">Получать уведомления</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ pushSubscribed ? 'Вы подписаны на push-уведомления' : 'Напоминания о задачах и обновления' }}
                </div>
              </div>
              <button
                @click="togglePush"
                :disabled="pushLoading"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                :class="pushSubscribed ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'"
                role="switch"
                :aria-checked="pushSubscribed"
              >
                <span
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                  :class="pushSubscribed ? 'translate-x-5' : 'translate-x-0'"
                />
              </button>
            </div>

            <div v-if="pushSubscribed" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button
                @click="sendTestPush"
                :disabled="testPushLoading"
                class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
              >
                {{ testPushLoading ? 'Отправка...' : 'Отправить тестовое уведомление' }}
              </button>
              <span v-if="testPushSuccess" class="ml-2 text-sm text-green-600 dark:text-green-400">Отправлено!</span>
            </div>
          </template>
        </div>
      </div>


      <!-- Сферы жизни -->
      <SpheresTab v-if="activeTab === 'spheres' && workspace" :workspace="workspace" />

      <ContextsTab v-if="activeTab === 'contexts'" />

      <!-- Telegram -->
      <TelegramTab v-if="activeTab === 'telegram' && workspace" :workspace="workspace" />

      <div v-if="['spheres','telegram'].includes(activeTab) && !workspace" class="text-center text-gray-500 py-10">
        Загрузка пространства...
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useWorkspaceStore } from '@/stores/workspace'
import { usePushNotifications } from '@/composables/usePushNotifications'
import api from '@/services/api'
import SpheresTab from '@/views/Workspaces/tabs/SpheresTab.vue'
import TelegramTab from '@/views/Workspaces/tabs/TelegramTab.vue'
import ContextsTab from '@/views/Settings/tabs/ContextsTab.vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const workspaceStore = useWorkspaceStore()
const { permission: pushPermission, isSubscribed: pushSubscribed, subscribe, unsubscribe, checkSubscription } = usePushNotifications()

const pushSupported = 'serviceWorker' in navigator && 'PushManager' in window
const pushLoading = ref(false)
const testPushLoading = ref(false)
const testPushSuccess = ref(false)

const togglePush = async () => {
  pushLoading.value = true
  try {
    if (pushSubscribed.value) {
      await unsubscribe()
    } else {
      await subscribe()
    }
  } finally {
    pushLoading.value = false
  }
}

const sendTestPush = async () => {
  testPushLoading.value = true
  testPushSuccess.value = false
  try {
    await api.post('/v1/push/test')
    testPushSuccess.value = true
    setTimeout(() => { testPushSuccess.value = false }, 3000)
  } finally {
    testPushLoading.value = false
  }
}

const tabs = [
  { key: 'profile', label: 'Профиль' },
  { key: 'spheres', label: 'Сферы жизни' },
  { key: 'contexts', label: 'Контексты' },
  { key: 'telegram', label: 'Telegram' },
]

const activeTab = ref('profile')

const workspace = computed(() => {
  const userId = authStore.user?.id
  return workspaceStore.workspaces.find(ws => ws.owner_id === userId)
    || workspaceStore.currentWorkspace
    || workspaceStore.workspaces[0]
    || null
})

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

const faithLoading = ref(false)
const faithEnabled = ref(true)

const toggleFaith = async () => {
  if (!workspace.value) return
  faithLoading.value = true
  const next = !faithEnabled.value
  try {
    await workspaceStore.updateWorkspace(workspace.value.id, { faith_enabled: next })
    faithEnabled.value = next
  } finally {
    faithLoading.value = false
  }
}

onMounted(async () => {
  profileForm.value.name = authStore.user?.name || ''
  if (workspaceStore.workspaces.length === 0) {
    await workspaceStore.fetchWorkspaces()
  }
  if (workspace.value) {
    faithEnabled.value = workspace.value.faith_enabled !== false
  }
  if (pushSupported) {
    checkSubscription()
  }
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
