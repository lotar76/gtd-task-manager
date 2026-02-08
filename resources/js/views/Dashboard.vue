<template>
  <div class="p-4 lg:p-8">
    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-6">
      Добро пожаловать!
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="card cursor-pointer hover:shadow-md transition-shadow" @click="navigateTo('inbox')">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Входящие</h3>
            <p class="text-3xl font-bold text-primary-600 mt-2">{{ stats.inbox }}</p>
          </div>
          <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card cursor-pointer hover:shadow-md transition-shadow" @click="navigateTo('today')">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Сегодня</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.today }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="card cursor-pointer hover:shadow-md transition-shadow" @click="navigateTo('next-actions')">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Следующие действия</h3>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ stats.nextActions }}</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'

const router = useRouter()
const workspaceStore = useWorkspaceStore()

const stats = ref({
  inbox: 0,
  today: 0,
  nextActions: 0,
})

const navigateTo = (view) => {
  const workspaceId = workspaceStore.currentWorkspace?.id
  if (workspaceId) {
    router.push(`/workspaces/${workspaceId}/${view}`)
  }
}

onMounted(() => {
  // Можно загрузить статистику через API
  stats.value = {
    inbox: 5,
    today: 3,
    nextActions: 12,
  }
})
</script>


