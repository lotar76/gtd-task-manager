<template>
  <div v-if="data" class="space-y-6">
    <!-- Summary cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Всего задач</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ data.summary?.total_tasks || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Выполнено</p>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ data.summary?.completed || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Активных сфер</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
          {{ data.summary?.spheres_active || 0 }}<span class="text-sm text-gray-400">/{{ data.summary?.spheres_total || 0 }}</span>
        </p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Выполнение</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ data.summary?.completion_rate || 0 }}%</p>
      </div>
    </div>

    <!-- Tasks by sphere -->
    <div v-if="data.spheres?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Задачи по сферам</h3>
      <div class="space-y-3">
        <div
          v-for="sphere in data.spheres"
          :key="sphere.id"
          class="border-l-3 rounded-lg p-3 bg-gray-50 dark:bg-gray-700/30"
          :style="{ borderLeftColor: sphere.color, borderLeftWidth: '3px' }"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ sphere.name }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ sphere.tasks_done }}/{{ sphere.tasks_total }}</span>
          </div>
          <div v-if="sphere.tasks?.length" class="space-y-1">
            <div
              v-for="task in sphere.tasks"
              :key="task.id"
              class="flex items-center gap-2 text-sm"
            >
              <div
                class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0"
                :class="task.completed_at
                  ? 'bg-emerald-500 border-emerald-500'
                  : 'border-gray-300 dark:border-gray-600'"
              >
                <svg v-if="task.completed_at" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <span
                :class="task.completed_at ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-700 dark:text-gray-300'"
              >
                {{ task.title }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  data: { type: Object, default: null },
})
</script>
