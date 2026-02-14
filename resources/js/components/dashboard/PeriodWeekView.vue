<template>
  <div v-if="data" class="space-y-6">
    <!-- Week summary -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Всего задач</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ data.summary?.total_tasks || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Выполнено</p>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ data.summary?.completed || 0 }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Выполнение</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ data.summary?.completion_rate || 0 }}%</p>
      </div>
    </div>

    <!-- Week X-ray (daily breakdown) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Рентген недели</h3>
      <div class="flex items-end gap-1 h-32">
        <div
          v-for="day in data.daily_breakdown"
          :key="day.date"
          class="flex-1 flex flex-col items-center gap-1"
        >
          <!-- Stacked bar -->
          <div class="w-full flex flex-col-reverse gap-px" style="height: 100px;">
            <template v-for="sphereId in Object.keys(day.by_sphere)" :key="sphereId">
              <div
                class="w-full rounded-sm transition-all duration-300"
                :style="{
                  height: getBarHeight(day.by_sphere[sphereId]) + 'px',
                  backgroundColor: getSphereColor(parseInt(sphereId)),
                  opacity: 0.8,
                }"
                :title="`${getSphereName(parseInt(sphereId))}: ${day.by_sphere[sphereId]}`"
              />
            </template>
            <!-- Empty day indicator -->
            <div
              v-if="!Object.keys(day.by_sphere).length"
              class="w-full h-full flex items-center justify-center"
            >
              <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
            </div>
          </div>
          <!-- Day label -->
          <span
            class="text-xs mt-1"
            :class="isToday(day.date) ? 'font-bold text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400'"
          >
            {{ day.day }}
          </span>
        </div>
      </div>
    </div>

    <!-- Attention distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Распределение внимания</h3>
      <div class="space-y-2">
        <div
          v-for="item in data.attention_distribution"
          :key="item.sphere_id"
          class="flex items-center gap-3"
        >
          <div class="w-20 text-xs text-gray-600 dark:text-gray-400 truncate">{{ item.name }}</div>
          <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500"
              :style="{
                width: item.percentage + '%',
                backgroundColor: item.color,
                opacity: item.percentage > 0 ? 1 : 0.3,
              }"
            />
          </div>
          <span class="text-xs font-medium text-gray-500 dark:text-gray-400 tabular-nums w-12 text-right">
            {{ item.percentage }}%
          </span>
        </div>
      </div>
    </div>

    <!-- Projects/Flows -->
    <div v-if="data.projects?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Потоки</h3>
      <div class="space-y-2">
        <div
          v-for="project in data.projects"
          :key="project.id"
          class="flex items-center gap-3 p-2 rounded-lg"
          :class="project.is_stalled ? 'opacity-50' : ''"
        >
          <div
            class="w-2 h-2 rounded-full flex-shrink-0"
            :style="{ backgroundColor: project.sphere_color || project.color }"
          />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-900 dark:text-white truncate">{{ project.name }}</span>
              <span v-if="project.is_stalled" class="text-[10px] text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-1.5 py-0.5 rounded">стоит</span>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
              <div
                class="h-full rounded-full"
                :style="{
                  width: (project.total > 0 ? (project.done / project.total * 100) : 0) + '%',
                  backgroundColor: project.sphere_color || project.color,
                }"
              />
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">{{ project.done }}/{{ project.total }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import dayjs from 'dayjs'

const props = defineProps({
  data: { type: Object, default: null },
})

const maxTasksPerDay = computed(() => {
  if (!props.data?.daily_breakdown) return 1
  let max = 0
  for (const day of props.data.daily_breakdown) {
    const total = Object.values(day.by_sphere).reduce((s, v) => s + v, 0)
    if (total > max) max = total
  }
  return Math.max(max, 1)
})

const getBarHeight = (count) => {
  return Math.max((count / maxTasksPerDay.value) * 80, 4)
}

const sphereMap = computed(() => {
  const map = {}
  if (props.data?.attention_distribution) {
    for (const item of props.data.attention_distribution) {
      map[item.sphere_id] = item
    }
  }
  return map
})

const getSphereColor = (id) => sphereMap.value[id]?.color || '#888'
const getSphereName = (id) => sphereMap.value[id]?.name || ''

const isToday = (dateStr) => dayjs().format('YYYY-MM-DD') === dateStr
</script>
