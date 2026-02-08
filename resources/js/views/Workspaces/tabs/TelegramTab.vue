<template>
  <div>
    <!-- Loading -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else class="space-y-6">

      <!-- Секция: Настройка бота (только owner) -->
      <div v-if="isOwner" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Настройка бота</h2>

        <div v-if="!settings.is_configured">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Создайте бота через <a href="https://t.me/BotFather" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">@BotFather</a>
            и вставьте токен.
          </p>

          <form @submit.prevent="handleConnectBot" autocomplete="off" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Токен бота
              </label>
              <input
                v-model="botTokenForm"
                type="text"
                name="bot_token_nofill"
                autocomplete="new-password"
                readonly
                @focus="$event.target.removeAttribute('readonly')"
                class="input"
                placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11"
                required
              />
            </div>
            <div v-if="botError" class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
              {{ botError }}
            </div>
            <button type="submit" :disabled="botLoading" class="btn btn-primary">
              {{ botLoading ? 'Подключение...' : 'Подключить бота' }}
            </button>
          </form>
        </div>

        <div v-else>
          <div class="flex items-center justify-between mb-4">
            <div>
              <div class="flex items-center space-x-2">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">Бот подключен</span>
              </div>
              <p v-if="settings.bot_username" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @{{ settings.bot_username }}
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Подписчиков: {{ settings.subscribers_count || 0 }}
              </p>
            </div>
          </div>

          <button
            @click="handleDisconnectBot"
            :disabled="botLoading"
            class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
          >
            Отключить бота
          </button>
        </div>
      </div>

      <!-- Секция: Моя подписка -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Моя подписка</h2>

        <!-- Бот не настроен -->
        <div v-if="!subscription.bot_configured" class="text-center py-6">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <p class="text-gray-500 dark:text-gray-400">
            Telegram бот ещё не настроен для этого пространства.
          </p>
          <p v-if="!isOwner" class="text-sm text-gray-400 dark:text-gray-500 mt-1">
            Обратитесь к владельцу пространства.
          </p>
        </div>

        <!-- Бот настроен, нет подписки -->
        <div v-else-if="!subscription.subscribed" class="text-center py-6">
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            Подключите Telegram для получения уведомлений и управления задачами.
          </p>
          <button @click="handleSubscribe" :disabled="subLoading" class="btn btn-primary">
            {{ subLoading ? 'Подключение...' : 'Подключить Telegram' }}
          </button>
        </div>

        <!-- Подписка есть, но не активирована -->
        <div v-else-if="!subscription.is_active">
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
            <p class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-2">
              Перейдите по ссылке и нажмите Start:
            </p>
            <a
              :href="subscription.connect_url"
              target="_blank"
              class="inline-flex items-center space-x-2 text-primary-600 dark:text-primary-400 hover:underline font-medium"
            >
              <span>Открыть в Telegram</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </a>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            После нажатия Start в Telegram, обновите эту страницу.
          </p>
          <div class="mt-4 flex space-x-3">
            <button @click="loadData" class="btn btn-secondary text-sm">
              Обновить статус
            </button>
            <button @click="handleUnsubscribe" class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
              Отменить
            </button>
          </div>
        </div>

        <!-- Подписка активна -->
        <div v-else>
          <div class="flex items-center space-x-2 mb-6">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            <span class="text-sm text-green-700 dark:text-green-400 font-medium">Telegram подключен</span>
          </div>

          <form @submit.prevent="handleUpdateSettings" class="space-y-4">
            <!-- Утренний дайджест -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-medium text-gray-900 dark:text-white">Утренний дайджест</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Список задач на сегодня</p>
              </div>
              <input
                v-model="notifyForm.notify_morning_digest"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
            </div>
            <div v-if="notifyForm.notify_morning_digest" class="ml-4">
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Время отправки</label>
              <input
                v-model="notifyForm.morning_digest_time"
                type="time"
                class="input w-32"
              />
            </div>

            <!-- Напоминания -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-medium text-gray-900 dark:text-white">Напоминания</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Перед началом задачи</p>
              </div>
              <input
                v-model="notifyForm.notify_reminders"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
            </div>
            <div v-if="notifyForm.notify_reminders" class="ml-4">
              <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">За сколько минут</label>
              <input
                v-model.number="notifyForm.reminder_minutes_before"
                type="number"
                min="5"
                max="120"
                class="input w-32"
              />
            </div>

            <!-- Просроченные -->
            <div class="flex items-center justify-between">
              <div>
                <label class="text-sm font-medium text-gray-900 dark:text-white">Просроченные задачи</label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ежедневно в 09:00</p>
              </div>
              <input
                v-model="notifyForm.notify_overdue"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
            </div>

            <div v-if="subError" class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
              {{ subError }}
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
              <button type="submit" :disabled="subLoading" class="btn btn-primary">
                {{ subLoading ? 'Сохранение...' : 'Сохранить' }}
              </button>
              <button
                type="button"
                @click="handleUnsubscribe"
                class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
              >
                Отключить Telegram
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const props = defineProps({
  workspace: { type: Object, required: true },
})

const authStore = useAuthStore()

const loading = ref(true)
const botLoading = ref(false)
const subLoading = ref(false)
const botError = ref('')
const subError = ref('')
const botTokenForm = ref('')

const settings = ref({
  is_configured: false,
  bot_username: null,
  is_active: false,
  subscribers_count: 0,
})

const subscription = ref({
  bot_configured: false,
  bot_username: null,
  subscribed: false,
  is_active: false,
  connect_url: null,
})

const notifyForm = ref({
  morning_digest_time: '08:00',
  reminder_minutes_before: 30,
  notify_overdue: true,
  notify_morning_digest: true,
  notify_reminders: true,
})

const isOwner = computed(() => {
  return props.workspace?.owner_id === authStore.user?.id
})

const workspaceId = computed(() => props.workspace?.id)

const loadData = async () => {
  if (!workspaceId.value) {
    loading.value = false
    return
  }

  loading.value = true
  try {
    const subResponse = await api.get(`/v1/workspaces/${workspaceId.value}/telegram-subscription`)
    const subData = subResponse.data
    subscription.value = {
      bot_configured: subData.bot_configured,
      bot_username: subData.bot_username || null,
      subscribed: subData.subscribed || false,
      is_active: subData.is_active || false,
      connect_url: subData.connect_url || null,
    }

    if (subData.subscribed && subData.is_active) {
      notifyForm.value = {
        morning_digest_time: subData.morning_digest_time || '08:00',
        reminder_minutes_before: subData.reminder_minutes_before || 30,
        notify_overdue: subData.notify_overdue ?? true,
        notify_morning_digest: subData.notify_morning_digest ?? true,
        notify_reminders: subData.notify_reminders ?? true,
      }
    }

    if (isOwner.value) {
      try {
        const settingsResponse = await api.get(`/v1/workspaces/${workspaceId.value}/telegram-settings`)
        settings.value = settingsResponse.data
      } catch (e) {
        // ignore
      }
    }
  } catch (error) {
    console.error('Error loading telegram data:', error)
  } finally {
    loading.value = false
  }
}

const handleConnectBot = async () => {
  botError.value = ''
  botLoading.value = true
  try {
    await api.post(`/v1/workspaces/${workspaceId.value}/telegram-settings`, {
      bot_token: botTokenForm.value,
    })
    botTokenForm.value = ''
    await loadData()
  } catch (error) {
    botError.value = error.response?.data?.message || 'Ошибка подключения бота'
  } finally {
    botLoading.value = false
  }
}

const handleDisconnectBot = async () => {
  if (!confirm('Отключить Telegram бота? Все подписки будут удалены.')) return

  botLoading.value = true
  try {
    await api.delete(`/v1/workspaces/${workspaceId.value}/telegram-settings`)
    await loadData()
  } catch (error) {
    botError.value = error.response?.data?.message || 'Ошибка отключения'
  } finally {
    botLoading.value = false
  }
}

const handleSubscribe = async () => {
  subError.value = ''
  subLoading.value = true
  try {
    await api.post(`/v1/workspaces/${workspaceId.value}/telegram-subscription`)
    await loadData()
  } catch (error) {
    subError.value = error.response?.data?.message || 'Ошибка подписки'
  } finally {
    subLoading.value = false
  }
}

const handleUpdateSettings = async () => {
  subError.value = ''
  subLoading.value = true
  try {
    await api.put(`/v1/workspaces/${workspaceId.value}/telegram-subscription`, notifyForm.value)
    subError.value = ''
  } catch (error) {
    subError.value = error.response?.data?.message || 'Ошибка сохранения'
  } finally {
    subLoading.value = false
  }
}

const handleUnsubscribe = async () => {
  if (!confirm('Отключить Telegram уведомления?')) return

  subLoading.value = true
  try {
    await api.delete(`/v1/workspaces/${workspaceId.value}/telegram-subscription`)
    await loadData()
  } catch (error) {
    subError.value = error.response?.data?.message || 'Ошибка отключения'
  } finally {
    subLoading.value = false
  }
}

onMounted(loadData)
</script>
