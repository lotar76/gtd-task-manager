<template>
  <!-- Loading -->
  <div v-if="data?.loading && !data?.lifeMirror" class="flex items-center justify-center py-16">
    <div class="animate-spin rounded-full h-10 w-10 border-2 border-primary-600 border-t-transparent"></div>
  </div>

  <!-- Empty state -->
  <div v-else-if="!data?.loading && !data?.lifeMirror" class="text-center py-16">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Нет данных</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Создайте сферы жизни и задачи для аналитики</p>
  </div>

  <!-- Content -->
  <template v-else>
    <!-- AI Hero Banner -->
    <AiHeroBanner
      :message="data.aiMessage"
      :loading="data.aiLoading"
      class="mb-6"
    />

    <!-- Balance Wheel + Sphere Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
      <!-- Wheel -->
      <div class="lg:col-span-1 flex items-center justify-center">
        <BalanceWheel
          :spheres="wheelSpheres"
          :balance-index="data.lifeMirror?.balance_index || 0"
          :size="280"
        />
      </div>

      <!-- Sphere Cards -->
      <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <SphereCard
          v-for="sphere in data.lifeMirror?.spheres"
          :key="sphere.id"
          :sphere="sphere"
        />
      </div>
    </div>

    <!-- Missing Spheres -->
    <MissingSpheres
      v-if="data.lifeMirror?.missing_spheres?.length"
      :spheres="data.lifeMirror.missing_spheres"
      class="mb-6"
    />

    <!-- Period-specific view -->
    <component
      :is="periodComponent"
      :data="data.lifeMirror?.period_data"
    />
  </template>
</template>

<script setup>
import { computed } from 'vue'
import AiHeroBanner from '@/components/dashboard/AiHeroBanner.vue'
import BalanceWheel from '@/components/dashboard/BalanceWheel.vue'
import SphereCard from '@/components/dashboard/SphereCard.vue'
import MissingSpheres from '@/components/dashboard/MissingSpheres.vue'
import PeriodDayView from '@/components/dashboard/PeriodDayView.vue'
import PeriodWeekView from '@/components/dashboard/PeriodWeekView.vue'
import PeriodMonthView from '@/components/dashboard/PeriodMonthView.vue'
import PeriodYearView from '@/components/dashboard/PeriodYearView.vue'

const props = defineProps({
  workspaceId: { type: Number, required: true },
  period: { type: String, required: true },
  data: { type: Object, default: null },
})

const periodComponent = computed(() => {
  const map = {
    day: PeriodDayView,
    week: PeriodWeekView,
    month: PeriodMonthView,
    year: PeriodYearView,
  }
  return map[props.period] || PeriodDayView
})

// Все сферы для колеса: активные + пустые (missing)
const wheelSpheres = computed(() => {
  const mirror = props.data?.lifeMirror
  if (!mirror) return []

  const active = (mirror.spheres || []).map(s => ({
    name: s.name,
    total: s.tasks_total || 0,
    done: s.tasks_done || 0,
    color: s.color,
  }))

  const missing = (mirror.missing_spheres || []).map(s => ({
    name: s.name,
    total: 0,
    done: 0,
    color: s.color,
  }))

  return [...active, ...missing]
})
</script>
