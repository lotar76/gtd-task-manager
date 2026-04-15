<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

      <!-- Header -->
      <DashboardHeader
        :period="store.selectedPeriod"
        :bible-verse="store.aiMessage?.bible_verse"
        @change-period="store.setPeriod"
      />

      <!-- Dashboard content -->
      <WorkspaceDashboard
        :period="store.selectedPeriod"
        :data="dashboardData"
        @analyze-ai="handleAnalyzeAi"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'
import WorkspaceDashboard from '@/components/dashboard/WorkspaceDashboard.vue'

const store = useDashboardStore()

const dashboardData = computed(() => ({
  lifeMirror: store.lifeMirror,
  aiMessage: store.aiMessage,
  loading: store.loading,
  aiLoading: store.aiLoading,
  aiSilentLoading: store.aiSilentLoading,
}))

const handleAnalyzeAi = (force = false) => {
  store.fetchAiMessage(force)
}

const handleVisibilityChange = () => {
  if (!document.hidden) {
    store.fetchAll(true)
  }
}

let refreshInterval = null

onMounted(() => {
  store.fetchAll()
  document.addEventListener('visibilitychange', handleVisibilityChange)
  refreshInterval = setInterval(() => {
    if (!document.hidden) {
      store.fetchAll(true)
    }
  }, 30000)
})

onUnmounted(() => {
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>
