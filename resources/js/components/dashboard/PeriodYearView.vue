<template>
  <div v-if="data" class="space-y-6">
    <!-- Goals summary cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">Всего целей</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ gs.total }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-emerald-200 dark:border-emerald-900/50">
        <p class="text-xs text-emerald-600 dark:text-emerald-400">Достигнуто</p>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ gs.achieved }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-blue-200 dark:border-blue-900/50">
        <p class="text-xs text-blue-600 dark:text-blue-400">По графику</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ gs.on_track }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-amber-200 dark:border-amber-900/50">
        <p class="text-xs text-amber-600 dark:text-amber-400">Отстают</p>
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ gs.behind + gs.at_risk }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-400">Стоят</p>
        <p class="text-2xl font-bold text-gray-400 mt-1">{{ gs.stalled }}</p>
      </div>
    </div>

    <!-- Balance trend chart -->
    <div v-if="data.monthly_balance_trend?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Индекс баланса — тренд</h3>
      <div class="flex items-end gap-2 h-32">
        <div
          v-for="item in data.monthly_balance_trend"
          :key="item.month_key"
          class="flex-1 flex flex-col items-center gap-1"
        >
          <span class="text-xs font-bold text-gray-900 dark:text-white tabular-nums">{{ item.balance_index }}</span>
          <div
            class="w-full rounded-t transition-all duration-300"
            :class="item.is_current
              ? 'bg-primary-500 shadow-lg shadow-primary-500/30'
              : 'bg-gray-300 dark:bg-gray-600'"
            :style="{ height: Math.max(item.balance_index, 5) + '%' }"
          />
          <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate max-w-full">{{ item.month.split(' ')[0] }}</span>
        </div>
      </div>
    </div>

    <!-- Active goals -->
    <div v-if="data.active_goals?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Цели года</h3>
      <div class="space-y-3">
        <div
          v-for="goal in data.active_goals"
          :key="goal.id"
          class="flex items-center gap-3 p-2 rounded-lg"
          :class="goal.status === 'stalled' ? 'opacity-50' : ''"
        >
          <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: goal.sphere_color }" />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-sm text-gray-900 dark:text-white truncate">{{ goal.name }}</span>
              <span
                class="text-[10px] px-1.5 py-0.5 rounded-full"
                :class="statusClasses[goal.status] || statusClasses.on_track"
              >
                {{ statusLabels[goal.status] || goal.status }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :style="{ width: goal.progress + '%', backgroundColor: goal.sphere_color }"
                />
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">{{ goal.progress }}%</span>
            </div>
          </div>
          <span v-if="goal.days_left" class="text-xs text-gray-400 tabular-nums whitespace-nowrap">
            {{ goal.days_left }} дн.
          </span>
        </div>
      </div>
    </div>

    <!-- Achieved goals -->
    <div v-if="data.achieved_goals?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-emerald-200 dark:border-emerald-900/50 p-4">
      <h3 class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-3">Достигнутые цели</h3>
      <div class="space-y-2">
        <div
          v-for="goal in data.achieved_goals"
          :key="goal.id"
          class="flex items-center gap-3 p-2"
        >
          <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm text-gray-900 dark:text-white flex-1">{{ goal.name }}</span>
          <span class="text-xs text-gray-400">{{ goal.completed_at }}</span>
        </div>
      </div>
    </div>

    <!-- Sphere yearly trends -->
    <div v-if="data.sphere_yearly_trends?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
      <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Тренды по сферам</h3>
      <div class="space-y-2">
        <div
          v-for="trend in data.sphere_yearly_trends"
          :key="trend.sphere_id"
          class="flex items-center gap-3"
        >
          <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: trend.color }" />
          <span class="text-xs text-gray-600 dark:text-gray-400 w-20 truncate">{{ trend.name }}</span>
          <span class="text-xs tabular-nums w-12" :class="trendClass(trend.trend)">
            {{ trendArrows[trend.trend] || '—' }} {{ trend.avg_attention }}%
          </span>
          <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ trendLabels[trend.trend] || '' }}</span>
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

const gs = computed(() => props.data?.goals_summary || {
  total: 0, achieved: 0, on_track: 0, behind: 0, at_risk: 0, stalled: 0,
})

const statusClasses = {
  ahead: 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400',
  on_track: 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400',
  behind: 'bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400',
  at_risk: 'bg-rose-100 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400',
  stalled: 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
}

const statusLabels = {
  ahead: 'опережает',
  on_track: 'по графику',
  behind: 'отстаёт',
  at_risk: 'под угрозой',
  stalled: 'стоит',
}

const trendArrows = {
  growing: '↑',
  falling: '↓',
  stable: '→',
  stalled: '·',
}

const trendLabels = {
  growing: 'растёт',
  falling: 'падает',
  stable: 'стабильно',
  stalled: 'стоит',
}

const trendClass = (trend) => {
  if (trend === 'growing') return 'text-emerald-600 dark:text-emerald-400'
  if (trend === 'falling') return 'text-rose-600 dark:text-rose-400'
  return 'text-gray-400 dark:text-gray-500'
}
</script>
