<template>
  <div class="flex-1 overflow-y-auto">
    <div class="max-w-2xl mx-auto px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Уведомления</h1>
        <button
          v-if="notificationsStore.unreadCount > 0"
          @click="notificationsStore.markAllRead()"
          class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
        >
          Прочитать все
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
        <button
          @click="tab = 'active'"
          class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
          :class="tab === 'active'
            ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
        >
          Активные
          <span v-if="unreadList.length" class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">{{ unreadList.length }}</span>
        </button>
        <button
          @click="tab = 'read'"
          class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors"
          :class="tab === 'read'
            ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
        >
          Прочитанные
        </button>
      </div>

      <!-- List -->
      <div v-if="loading" class="py-12 text-center text-gray-400">Загрузка...</div>

      <div v-else-if="currentList.length === 0" class="py-12 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ tab === 'active' ? 'Нет новых уведомлений' : 'Нет прочитанных уведомлений' }}
        </p>
      </div>

      <div v-else class="space-y-1">
        <div
          v-for="n in currentList"
          :key="n.id"
          @click="handleClick(n)"
          class="flex items-start gap-3 px-4 py-3 rounded-lg cursor-pointer transition-colors"
          :class="!n.read_at ? 'bg-primary-50/60 dark:bg-primary-900/15 hover:bg-primary-50 dark:hover:bg-primary-900/25' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
        >
          <!-- Icon -->
          <div class="flex-shrink-0 mt-0.5">
            <div v-if="n.data?.type === 'task_changed'" class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
              </svg>
            </div>
            <div v-else-if="n.data?.type === 'contact_invite'" class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
              </svg>
            </div>
            <div v-else class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
              </svg>
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <template v-if="n.data?.type === 'task_changed'">
              <div class="text-sm text-gray-900 dark:text-gray-100">
                <span class="font-medium">{{ n.data.changer_name }}</span>
                <span class="text-gray-500 dark:text-gray-400"> изменил: </span>
                <span>{{ n.data.changes?.join(', ') }}</span>
              </div>
              <div class="mt-1 flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                <span class="truncate">{{ n.data.task_title }}</span>
              </div>
            </template>
            <template v-else-if="n.data?.type === 'contact_invite'">
              <div class="text-sm text-gray-900 dark:text-gray-100">
                <span class="font-medium">{{ n.data.sender_name }}</span>
                <span class="text-gray-500 dark:text-gray-400"> хочет добавить вас в контакты</span>
              </div>
            </template>
            <template v-else>
              <div class="text-sm text-gray-900 dark:text-gray-100">{{ JSON.stringify(n.data) }}</div>
            </template>
            <div class="text-[11px] text-gray-400 mt-1">{{ formatTimeAgo(n.created_at) }}</div>
          </div>

          <!-- Unread dot -->
          <div v-if="!n.read_at" class="flex-shrink-0 mt-2">
            <div class="w-2 h-2 rounded-full bg-primary-500"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '@/stores/notifications'

const router = useRouter()
const notificationsStore = useNotificationsStore()

const tab = ref('active')
const loading = ref(false)

const unreadList = computed(() => notificationsStore.notifications.filter(n => !n.read_at))
const readList = computed(() => notificationsStore.notifications.filter(n => n.read_at))
const currentList = computed(() => tab.value === 'active' ? unreadList.value : readList.value)

const handleClick = (n) => {
  if (!n.read_at) notificationsStore.markRead(n.id)
  if (n.data?.type === 'contact_invite') {
    router.push('/contacts')
  } else if (n.data?.task_id) {
    router.push(`/tasks/${n.data.task_id}`)
  }
}

const formatTimeAgo = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'только что'
  if (diff < 3600) return `${Math.floor(diff / 60)} мин назад`
  if (diff < 86400) return `${Math.floor(diff / 3600)} ч назад`
  const days = Math.floor(diff / 86400)
  if (days === 1) return 'вчера'
  if (days < 7) return `${days} дн назад`
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: days > 365 ? 'numeric' : undefined })
}

onMounted(async () => {
  loading.value = true
  await notificationsStore.fetchNotifications()
  loading.value = false
})
</script>
