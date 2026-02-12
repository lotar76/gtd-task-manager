<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header with Period Selector -->
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ getStatsMessage() }}</p>
          </div>

          <!-- Period Buttons -->
          <div class="inline-flex rounded-lg shadow-sm" role="group">
            <button
              v-for="p in periods"
              :key="p.value"
              @click="selectedPeriod = p.value"
              type="button"
              class="px-4 py-2 text-sm font-medium transition-all duration-200"
              :class="[
                selectedPeriod === p.value
                  ? 'bg-primary-600 text-white shadow-md'
                  : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                p.value === 'day' ? 'rounded-l-lg' : '',
                p.value === 'month' ? 'rounded-r-lg' : '',
                p.value === 'week' ? 'border-x border-gray-200 dark:border-gray-700' : ''
              ]"
            >
              {{ p.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Motivation Banner -->
      <div v-if="stats" class="mb-8 relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 via-pink-500 to-orange-500 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjEiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative z-10">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-white mb-2">{{ getMotivationMessage() }}</h2>
              <p class="text-white/90 text-lg">{{ getFuturePlan() }}</p>
            </div>
            <div class="hidden sm:block text-6xl">🚀</div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Completed Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Выполнено</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.completed_this_period }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <!-- Mini Chart -->
          <div class="flex items-end justify-between h-12 gap-1">
            <div
              v-for="(item, index) in chartData"
              :key="index"
              class="flex-1 bg-green-200 dark:bg-green-700 rounded-t transition-all duration-200 hover:bg-green-300 dark:hover:bg-green-600"
              :style="{ height: getChartHeight(item.count) }"
              :title="`${item.date}: ${item.count}`"
            ></div>
          </div>
        </div>

        <!-- Active Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Активных</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_active }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
          <div class="flex items-center text-sm">
            <span class="text-orange-600 dark:text-orange-400 font-medium">{{ stats.today }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">на сегодня</span>
          </div>
        </div>

        <!-- Week Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">На неделю</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.week }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          <div class="flex items-center text-sm">
            <span class="text-red-600 dark:text-red-400 font-medium">{{ stats.overdue }}</span>
            <span class="text-gray-500 dark:text-gray-400 ml-1">просрочено</span>
          </div>
        </div>

        <!-- Productivity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Продуктивность</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.productivity }}%</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="getProductivityColor()"
              :style="{ width: stats.productivity + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Top Projects -->
      <div v-if="topProjects && topProjects.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
          <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
          Топ проекты за период
        </h3>
        <div class="space-y-3">
          <div
            v-for="(item, index) in topProjects"
            :key="item.project.id"
            class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
          >
            <div class="flex items-center space-x-3">
              <span class="text-2xl">{{ getMedal(index) }}</span>
              <div class="flex items-center">
                <div
                  class="w-3 h-3 rounded-full mr-2"
                  :style="{ backgroundColor: item.project.color }"
                ></div>
                <span class="font-medium text-gray-900 dark:text-white">{{ item.project.name }}</span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ item.count }}</span>
              <span class="text-sm text-gray-500 dark:text-gray-400">задач</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && !stats" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Нет данных</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Выберите workspace для просмотра статистики</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import api from '@/services/api'

const workspaceStore = useWorkspaceStore()

const periods = [
  { value: 'day', label: 'День' },
  { value: 'week', label: 'Неделя' },
  { value: 'month', label: 'Месяц' }
]

const selectedPeriod = ref('week')
const loading = ref(false)
const stats = ref(null)
const chartData = ref([])
const topProjects = ref([])

const fetchStats = async () => {
  if (!workspaceStore.currentWorkspace?.id) {
    stats.value = null
    return
  }

  loading.value = true
  try {
    const response = await api.get('/v1/dashboard/stats', {
      params: {
        workspace_id: workspaceStore.currentWorkspace.id,
        period: selectedPeriod.value
      }
    })

    stats.value = response.stats
    chartData.value = response.chart_data || []
    topProjects.value = response.top_projects || []
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  } finally {
    loading.value = false
  }
}

const getChartHeight = (count) => {
  if (!chartData.value.length) return '0%'
  const max = Math.max(...chartData.value.map(d => d.count), 1)
  return Math.max((count / max) * 100, 10) + '%'
}

const getProductivityColor = () => {
  if (!stats.value) return 'bg-gray-400'
  const p = stats.value.productivity
  if (p >= 80) return 'bg-green-500'
  if (p >= 60) return 'bg-blue-500'
  if (p >= 40) return 'bg-orange-500'
  return 'bg-red-500'
}

const getMotivationMessage = () => {
  if (!stats.value) return 'Загрузка...'

  const completed = stats.value.completed_this_period
  const productivity = stats.value.productivity

  if (productivity >= 80) {
    return '🎯 Невероятно! Ты на пике продуктивности!'
  } else if (productivity >= 60) {
    return '💪 Отличная работа! Продолжай в том же духе!'
  } else if (productivity >= 40) {
    return '👍 Хороший темп! Ещё немного усилий!'
  } else if (completed > 0) {
    return '🌱 Каждая выполненная задача - это шаг вперёд!'
  }
  return '🚀 Время начать что-то новое!'
}

const getStatsMessage = () => {
  const period = periods.find(p => p.value === selectedPeriod.value)?.label.toLowerCase() || 'период'
  if (!stats.value) return `Статистика за ${period}`

  const completed = stats.value.completed_this_period
  if (completed === 0) return `Начни выполнять задачи за ${period}`
  if (completed === 1) return `Выполнена 1 задача за ${period}`
  if (completed < 5) return `Выполнено ${completed} задачи за ${period}`
  return `Выполнено ${completed} задач за ${period}`
}

const getFuturePlan = () => {
  if (!stats.value) return ''

  const active = stats.value.total_active
  const week = stats.value.week

  if (selectedPeriod.value === 'week' && week > 0) {
    return `Предстоит выполнить ${week} ${week === 1 ? 'задачу' : week < 5 ? 'задачи' : 'задач'} на этой неделе`
  }

  if (active > 10) {
    return 'Большие планы требуют больших действий! 🎯'
  } else if (active > 0) {
    return `В работе ${active} ${active === 1 ? 'задача' : active < 5 ? 'задачи' : 'задач'}. Ты справишься!`
  }

  return 'Отличное время для планирования новых задач!'
}

const getMedal = (index) => {
  const medals = ['🥇', '🥈', '🥉']
  return medals[index] || '🏆'
}

// Watch for workspace and period changes
watch(() => workspaceStore.currentWorkspace?.id, () => {
  fetchStats()
}, { immediate: true })

watch(selectedPeriod, () => {
  fetchStats()
})

onMounted(() => {
  fetchStats()
})
</script>
