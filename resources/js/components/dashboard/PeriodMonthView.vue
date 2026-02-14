<template>
  <div v-if="data" class="space-y-6">
    <!-- Workload -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Нагрузка</h3>
      <div class="flex items-center gap-4 mb-2">
        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
          <div
            class="h-full rounded-full bg-primary-500 transition-all duration-500"
            :style="{ width: (pvd.completion_rate || 0) + '%' }"
          />
        </div>
        <span class="text-sm font-bold text-gray-900 dark:text-white tabular-nums">
          {{ pvd.done }}/{{ pvd.planned }}
        </span>
      </div>
      <p class="text-xs text-gray-500 dark:text-gray-400">
        Осталось {{ pvd.planned - pvd.done }} задач, ~{{ pvd.required_daily_pace }}/день чтобы успеть.
        Прошло {{ pvd.days_passed }} дн., осталось {{ pvd.days_remaining }} дн.
      </p>
    </div>

    <!-- Sphere trends (was → became) -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Было → Стало</h3>
      <div class="space-y-2">
        <div
          v-for="trend in data.sphere_trends"
          :key="trend.sphere_id"
          class="flex items-center gap-3"
        >
          <div class="w-20 text-xs text-gray-600 dark:text-gray-400 truncate">{{ trend.name }}</div>
          <span class="text-xs text-gray-400 dark:text-gray-500 tabular-nums w-8 text-right">{{ trend.prev_pct }}%</span>
          <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden relative">
            <!-- Previous month marker -->
            <div
              class="absolute top-0 h-full w-0.5 bg-gray-400 dark:bg-gray-500 z-10"
              :style="{ left: trend.prev_pct + '%' }"
            />
            <div
              class="h-full rounded-full transition-all duration-500"
              :style="{ width: trend.current_pct + '%', backgroundColor: trend.color }"
            />
          </div>
          <span class="text-xs font-medium tabular-nums w-8 text-right" :style="{ color: trend.color }">
            {{ trend.current_pct }}%
          </span>
          <span class="text-xs tabular-nums w-10 text-right" :class="trendClass(trend.change)">
            {{ trend.change > 0 ? '+' : '' }}{{ trend.change }}%
          </span>
        </div>
      </div>
    </div>

    <!-- Stalled projects -->
    <div v-if="data.stalled_projects?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-amber-200 dark:border-amber-900/50 p-4">
      <h3 class="text-sm font-semibold text-amber-600 dark:text-amber-400 mb-3">Застрявшие потоки</h3>
      <div class="space-y-2">
        <div
          v-for="project in data.stalled_projects"
          :key="project.id"
          class="flex items-center gap-3 p-2 rounded-lg bg-amber-50/50 dark:bg-amber-900/10"
        >
          <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: project.sphere_color || '#F59E0B' }" />
          <span class="text-sm text-gray-700 dark:text-gray-300 flex-1">{{ project.name }}</span>
          <span class="text-xs text-amber-600 dark:text-amber-400">
            {{ project.days_since_activity }} дн. без движения
          </span>
        </div>
      </div>
    </div>

    <!-- Goals at risk -->
    <div v-if="data.goals_at_risk?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-rose-200 dark:border-rose-900/50 p-4">
      <h3 class="text-sm font-semibold text-rose-600 dark:text-rose-400 mb-3">Цели под угрозой</h3>
      <div class="space-y-3">
        <div
          v-for="goal in data.goals_at_risk"
          :key="goal.id"
          class="p-3 rounded-lg bg-rose-50/50 dark:bg-rose-900/10"
        >
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
              <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: goal.sphere_color }" />
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ goal.name }}</span>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ goal.days_left }} дн. до дедлайна</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden relative">
              <div
                class="absolute top-0 h-full w-0.5 bg-rose-400 z-10"
                :style="{ left: goal.expected_progress + '%' }"
                :title="'Ожидаемый: ' + goal.expected_progress + '%'"
              />
              <div
                class="h-full rounded-full bg-rose-500"
                :style="{ width: goal.progress + '%' }"
              />
            </div>
            <span class="text-xs text-rose-600 dark:text-rose-400 tabular-nums">
              {{ goal.progress }}% <span class="text-gray-400">из {{ goal.expected_progress }}%</span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: { type: Object, default: null },
})

const pvd = computed(() => props.data?.planned_vs_done || {
  planned: 0, done: 0, days_passed: 0, days_remaining: 0, required_daily_pace: 0, completion_rate: 0,
})

const trendClass = (change) => {
  if (change > 2) return 'text-emerald-600 dark:text-emerald-400'
  if (change < -2) return 'text-rose-600 dark:text-rose-400'
  return 'text-gray-400 dark:text-gray-500'
}
</script>
