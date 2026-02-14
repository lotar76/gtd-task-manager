<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">

      <!-- Header -->
      <DashboardHeader
        :period="store.selectedPeriod"
        @change-period="store.setPeriod"
      />

      <!-- No workspaces -->
      <div v-if="!selectedWorkspaces.length" class="text-center py-16">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Нет данных</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Выберите workspace для просмотра аналитики</p>
      </div>

      <template v-else>
        <!-- Workspace Tabs (only if more than 1) -->
        <div v-if="selectedWorkspaces.length > 1" class="mb-6">
          <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            <button
              v-for="ws in selectedWorkspaces"
              :key="ws.id"
              @click="activeWorkspaceId = ws.id"
              class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-xl whitespace-nowrap transition-all duration-200"
              :class="[
                activeWorkspaceId === ws.id
                  ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm ring-1 ring-gray-200 dark:ring-gray-700'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800/50'
              ]"
            >
              <span v-if="ws.emoji" class="text-base">{{ ws.emoji }}</span>
              <span>{{ ws.name }}</span>
            </button>
          </div>
        </div>

        <!-- Dashboard content for active workspace -->
        <WorkspaceDashboard
          v-if="activeWorkspaceId"
          :key="activeWorkspaceId"
          :workspace-id="activeWorkspaceId"
          :period="store.selectedPeriod"
          :data="wsData"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { useWorkspaceStore } from '@/stores/workspace'
import DashboardHeader from '@/components/dashboard/DashboardHeader.vue'
import WorkspaceDashboard from '@/components/dashboard/WorkspaceDashboard.vue'

const store = useDashboardStore()
const workspaceStore = useWorkspaceStore()

const selectedWorkspaces = computed(() => workspaceStore.selectedWorkspaces)
const activeWorkspaceId = ref(null)

// Данные активного workspace
const wsData = computed(() => {
  if (!activeWorkspaceId.value) return null
  return store.dataByWorkspace[activeWorkspaceId.value] || null
})

// Установить активный tab при загрузке / смене выбранных workspace
watch(
  () => selectedWorkspaces.value.map(ws => ws.id),
  (ids) => {
    if (!ids.length) {
      activeWorkspaceId.value = null
      return
    }
    // Если текущий активный не в списке, переключить на первый
    if (!activeWorkspaceId.value || !ids.includes(activeWorkspaceId.value)) {
      activeWorkspaceId.value = ids[0]
    }
  },
  { immediate: true }
)

onMounted(() => {
  store.fetchAllWorkspaces()
})
</script>
