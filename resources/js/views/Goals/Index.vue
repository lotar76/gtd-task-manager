<template>
  <div class="p-4 lg:p-8 max-w-full overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_240px] gap-6">
      <!-- LEFT: timeline -->
      <div class="min-w-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Цели</h1>
          <div class="flex items-center gap-2">
            <!-- Scale switcher -->
            <div class="hidden sm:flex bg-gray-100 dark:bg-gray-800 rounded-lg p-0.5">
              <button
                v-for="s in scales"
                :key="s.key"
                @click="scale = s.key"
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                :class="scale === s.key
                  ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
              >
                {{ s.label }}
              </button>
            </div>
            <button
              @click="showGoalModal = true"
              class="p-2 sm:px-4 sm:py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span class="hidden sm:inline">Создать</span>
            </button>
          </div>
        </div>

        <!-- Mobile scale switcher -->
        <div class="sm:hidden flex bg-gray-100 dark:bg-gray-800 rounded-lg p-0.5 mb-4">
          <button
            v-for="s in scales"
            :key="s.key"
            @click="scale = s.key"
            class="flex-1 px-2 py-1.5 text-xs font-medium rounded-md transition-colors text-center"
            :class="scale === s.key
              ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
              : 'text-gray-500 dark:text-gray-400'"
          >
            {{ s.label }}
          </button>
        </div>

        <!-- MONTH: Calendar view -->
        <div v-if="scale === 'month' && filteredGoals.length > 0">
          <!-- Month nav -->
          <div class="flex items-center gap-3 mb-3">
            <button @click="calMonth--; if(calMonth<0){calMonth=11;calYear--}" class="p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
              {{ new Date(calYear, calMonth).toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' }) }}
            </span>
            <button @click="calMonth++; if(calMonth>11){calMonth=0;calYear++}" class="p-1 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>
          </div>
          <!-- Calendar grid -->
          <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Day names -->
            <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
              <div v-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']" :key="d" class="px-1 py-1.5 text-center text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ d }}</div>
            </div>
            <!-- Weeks -->
            <div class="grid grid-cols-7">
              <div
                v-for="(cell, idx) in calendarCells"
                :key="idx"
                class="min-h-[80px] border-b border-r border-gray-100 dark:border-gray-800 p-1 transition-all"
                :class="[
                  cell.isCurrentMonth ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/30',
                  dragOver === `cal-${idx}` ? 'ring-2 ring-primary-400 ring-inset rounded' : ''
                ]"
                @dragover.prevent="cell.isCurrentMonth && (dragOver = `cal-${idx}`)"
                @dragleave="dragOver = null"
                @drop.prevent="handleCalDrop(cell)"
              >
                <div class="text-[10px] mb-1" :class="cell.isToday ? 'w-5 h-5 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold' : cell.isCurrentMonth ? 'text-gray-500' : 'text-gray-300 dark:text-gray-600'">
                  {{ cell.day }}
                </div>
                <div class="space-y-0.5">
                  <div
                    v-for="goal in cell.goals"
                    :key="goal.id"
                    draggable="true"
                    @dragstart="handleDragStart($event, goal)"
                    @dragend="dragGoal = null; dragOver = null"
                    @click="openGoal(goal)"
                    class="text-[10px] leading-tight px-1.5 py-1.5 rounded truncate cursor-grab text-white hover:brightness-110 active:cursor-grabbing"
                    :style="{ backgroundColor: goal.life_sphere?.color || '#6366f1' }"
                    :title="goal.name"
                  >
                    {{ goal.name }}
                  </div>
                </div>
                <button
                  v-if="cell.isCurrentMonth"
                  @click.stop="openNewGoalFromCal(cell.day)"
                  class="w-full flex items-center justify-center mt-0.5 rounded text-gray-300 dark:text-gray-600 hover:text-primary-500 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- YEAR/3YEAR/5YEAR: same matrix as stream but different year count -->
        <div v-if="(scale === 'year' || scale === '3year' || scale === '5year')" class="overflow-x-auto rounded-lg sm:rounded-lg border border-gray-200 dark:border-gray-700 stream-matrix -mx-4 sm:mx-0">
          <table class="border-separate border-spacing-0" style="table-layout: fixed;">
            <thead>
              <tr>
                <th class="hidden sm:table-cell sticky left-0 z-20 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 w-8"></th>
                <th class="sticky left-0 sm:left-8 z-20 bg-gray-50 dark:bg-gray-800 px-2 py-2 text-left text-[10px] font-medium text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700 w-14 sm:min-w-[60px]"></th>
                <th
                  v-for="(sphere, si) in matrixSpheres"
                  :key="sphere.id"
                  class="px-2 py-2 text-center border-b border-l border-gray-100 dark:border-gray-800/50 bg-gray-50 dark:bg-gray-800 min-w-[280px] sm:min-w-[140px]"
                  @mouseenter="hoverCol = si"
                  @mouseleave="hoverCol = -1"
                >
                  <router-link :to="`/spheres/${sphere.id}`" class="flex flex-col items-center gap-1 hover:opacity-80 transition-opacity">
                    <div class="w-6 h-6 rounded-full overflow-hidden border flex-shrink-0" :style="{ borderColor: sphere.color }">
                      <img v-if="sphere.cover_image_url" :src="sphere.cover_image_url" class="w-full h-full object-cover" />
                      <div v-else class="w-full h-full flex items-center justify-center text-white text-[7px] font-bold" :style="{ backgroundColor: sphere.color }">{{ sphere.name?.charAt(0) }}</div>
                    </div>
                    <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 truncate max-w-[100px]">{{ sphere.name }}</span>
                  </router-link>
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(yr, yi) in scaleYears" :key="yr">
                <tr
                  v-for="m in 12"
                  :key="yr + '-' + m"
                  @mouseenter="hoverRow = yi * 12 + m - 1"
                  @mouseleave="hoverRow = -1"
                >
                  <td
                    v-if="m === 1"
                    :rowspan="12"
                    class="hidden sm:table-cell sticky left-0 z-10 w-8 text-center align-middle"
                    :class="yi % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800' : 'bg-white dark:bg-gray-900'"
                  >
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 [writing-mode:vertical-lr] rotate-180">{{ yr }}</span>
                  </td>
                  <td
                    class="sticky left-0 sm:left-8 z-10 px-2 py-1.5 border-b border-gray-200 dark:border-gray-700 text-[11px] whitespace-nowrap w-14 sm:min-w-[60px]"
                    :class="[
                      isNowYearMonth(yr, m - 1) ? 'text-primary-600 dark:text-primary-400 font-semibold bg-primary-50 dark:bg-primary-900' :
                      yi % 2 === 0 ? 'text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800' : 'text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900',
                      hoverRow === yi * 12 + m - 1 ? '!bg-primary-50 dark:!bg-primary-800' : ''
                    ]"
                  >
                    <span class="sm:hidden">{{ monthNamesShort[m - 1] }}<template v-if="scaleYears.length > 1 && m === 1"> '{{ String(yr).slice(2) }}</template></span>
                    <span class="hidden sm:inline">{{ monthNamesShort[m - 1] }}</span>
                  </td>
                  <td
                    v-for="(sphere, si) in matrixSpheres"
                    :key="sphere.id"
                    class="px-1 py-1 border-b border-l border-gray-100 dark:border-gray-800/50 align-top transition-colors min-w-[280px] sm:min-w-[140px] max-w-[280px] sm:max-w-none overflow-hidden"
                    :class="[
                      isNowYearMonth(yr, m - 1) ? 'bg-primary-50/20 dark:bg-primary-900/5' :
                      yi % 2 === 0 ? 'bg-gray-50/50 dark:bg-gray-800/20' : 'bg-white dark:bg-gray-900',
                      (hoverRow === yi * 12 + m - 1 || hoverCol === si) ? '!bg-primary-50/10 dark:!bg-primary-800/5' : ''
                    ]"
                    @mouseenter="hoverCol = si"
                    @mouseleave="hoverCol = -1"
                  >
                    <div
                      class="flex items-stretch gap-0.5 min-h-[24px] group/cell"
                      @dragover.prevent="dragOver = `${sphere.id}-${yr}-${m-1}`"
                      @dragleave="dragOver = null"
                      @drop.prevent="handleDrop(sphere.id, yr, m - 1)"
                      :class="dragOver === `${sphere.id}-${yr}-${m-1}` ? 'ring-2 ring-primary-400 ring-inset rounded' : ''"
                    >
                      <div v-if="streamMatrixGoals(sphere.id, yr, m - 1).length > 0" class="flex-1 min-w-0 space-y-0.5">
                        <div
                          v-for="goal in streamMatrixGoals(sphere.id, yr, m - 1)"
                          :key="goal.id"
                          draggable="true"
                          @dragstart="handleDragStart($event, goal)"
                          @dragend="dragGoal = null; dragOver = null"
                          @click="openGoal(goal)"
                          class="text-[10px] leading-tight px-1.5 py-1.5 rounded cursor-grab truncate text-white hover:brightness-110 transition-all active:cursor-grabbing"
                          :style="{ backgroundColor: sphere.color }"
                          :title="goal.name"
                        >
                          <span class="opacity-70">{{ new Date(goal.deadline).getDate() }}</span> {{ goal.name }}
                        </div>
                      </div>
                      <button
                        @click.stop="openNewGoal(sphere.id, yr, m - 1)"
                        class="flex-shrink-0 w-5 h-5 flex items-center justify-center rounded text-gray-300 dark:text-gray-600 hover:text-primary-500 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors self-center"
                        :class="streamMatrixGoals(sphere.id, yr, m - 1).length === 0 ? 'mx-auto' : ''"
                        title="Создать цель"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="allGoals.length === 0" class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21l3.75-3.75M3 21h4.5M3 21v-4.5m8.25-8.25L21 0M12.75 8.25v4.5m0-4.5h4.5" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Карта целей пуста</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
        Создайте цели и привяжите к сферам жизни, чтобы увидеть полную картину.
      </p>
      <button
        @click="showGoalModal = true"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
      >
        Создать цель
      </button>
    </div>

      </div>

      <!-- RIGHT: sidebar with spheres -->
      <aside class="hidden lg:block self-start sticky top-4 space-y-4">
        <!-- Sphere filter -->
        <div>
          <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium mb-2">Сферы жизни</div>
          <div class="space-y-1">
            <button
              @click="hiddenSpheres = new Set()"
              class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs transition-colors text-left"
              :class="hiddenSpheres.size === 0
                ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-medium'
                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50'"
            >
              <span class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-white text-[8px]">*</span>
              Все сферы
              <span class="ml-auto text-[10px] text-gray-400">{{ rangeGoals.length }}</span>
            </button>
            <button
              v-for="sphere in sortedSpheres"
              :key="sphere.id"
              @click="toggleSphereVisibility(sphere.id)"
              class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs transition-colors text-left"
              :class="[
                hiddenSpheres.has(sphere.id)
                  ? 'text-gray-300 dark:text-gray-600'
                  : rangeGoalCountBySphere(sphere.id) > 0
                    ? 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50'
                    : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50'
              ]"
            >
              <div
                class="w-5 h-5 rounded-full overflow-hidden border flex-shrink-0 transition-all"
                :style="{ borderColor: hiddenSpheres.has(sphere.id) ? '#d1d5db' : sphere.color }"
                :class="hiddenSpheres.has(sphere.id) ? 'grayscale opacity-30' : ''"
              >
                <img v-if="sphere.cover_image_url" :src="sphere.cover_image_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-white text-[7px] font-bold" :style="{ backgroundColor: sphere.color }">
                  {{ sphere.name?.charAt(0) }}
                </div>
              </div>
              <span class="flex-1 truncate" :class="hiddenSpheres.has(sphere.id) ? 'line-through' : ''">{{ sphere.name }}</span>
              <span class="ml-auto text-[10px]" :class="hiddenSpheres.has(sphere.id) ? 'text-gray-300 dark:text-gray-600' : 'text-gray-400'">{{ rangeGoalCountBySphere(sphere.id) }}</span>
            </button>
          </div>
        </div>

        <!-- Pie chart -->
        <div v-if="balanceSegments.length > 0">
          <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium mb-3">Баланс</div>
          <div class="flex justify-center mb-3">
            <svg viewBox="0 0 100 100" class="w-32 h-32">
              <circle
                v-for="(seg, idx) in pieSlices"
                :key="seg.sphereId"
                cx="50" cy="50" r="40"
                fill="none"
                :stroke="seg.color"
                stroke-width="20"
                :stroke-dasharray="seg.dash"
                :stroke-dashoffset="seg.offset"
                :transform="'rotate(-90 50 50)'"
              />
            </svg>
          </div>
          <div class="space-y-1">
            <div v-for="seg in balanceSegments" :key="seg.sphereId" class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: seg.color }"></span>
              <span class="text-[11px] text-gray-600 dark:text-gray-400 flex-1 truncate">{{ seg.name }}</span>
              <span class="text-[10px] text-gray-400">{{ seg.count }}</span>
            </div>
          </div>
        </div>
      </aside>
    </div>

    <!-- Goal Modal -->
    <GoalModal
      :show="showGoalModal"
      :goal="selectedGoal"
      :server-error="goalError"
      @close="handleCloseGoalModal"
      @submit="handleSaveGoal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useGoalsStore } from '@/stores/goals'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import GoalModal from '@/components/goals/GoalModal.vue'

const router = useRouter()
const goalsStore = useGoalsStore()
const spheresStore = useLifeSpheresStore()

const showGoalModal = ref(false)
const selectedGoal = ref(null)
const goalError = ref('')
const scale = ref('year')
const hoverRow = ref(-1)
const hoverCol = ref(-1)
const dragGoal = ref(null)
const dragOver = ref(null)
const hiddenSpheres = ref(new Set())

const scales = [
  { key: 'month', label: 'Месяц' },
  { key: 'year', label: 'Год' },
  { key: '3year', label: '3 года' },
  { key: '5year', label: '5 лет' },
]

const allGoals = computed(() => goalsStore.activeGoals)
const spheres = computed(() => spheresStore.visibleSpheres)

const filteredGoals = computed(() => {
  if (hiddenSpheres.value.size === 0) return allGoals.value
  return allGoals.value.filter(g => !hiddenSpheres.value.has(g.life_sphere_id))
})

// Time columns based on scale
const timeColumns = computed(() => {
  const now = new Date()
  const cols = []

  if (scale.value === 'month') {
    const year = now.getFullYear()
    const month = now.getMonth()
    const daysInMonth = new Date(year, month + 1, 0).getDate()
    for (let d = 1; d <= daysInMonth; d++) {
      const date = new Date(year, month, d)
      cols.push({
        key: `${year}-${month}-${d}`,
        label: d.toString(),
        start: new Date(year, month, d),
        end: new Date(year, month, d + 1),
        isCurrent: d === now.getDate(),
        isPast: d < now.getDate(),
      })
    }
  } else if (scale.value === 'year') {
    const year = now.getFullYear()
    const monthNames = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек']
    for (let m = 0; m < 12; m++) {
      cols.push({
        key: `${year}-${m}`,
        label: monthNames[m],
        start: new Date(year, m, 1),
        end: new Date(year, m + 1, 1),
        isCurrent: m === now.getMonth(),
        isPast: m < now.getMonth(),
      })
    }
  } else if (scale.value === '3year') {
    const startYear = now.getFullYear()
    const qLabels = ['Q1', 'Q2', 'Q3', 'Q4']
    for (let y = startYear; y < startYear + 3; y++) {
      for (let q = 0; q < 4; q++) {
        cols.push({
          key: `${y}-Q${q}`,
          label: `${qLabels[q]} ${y}`,
          start: new Date(y, q * 3, 1),
          end: new Date(y, (q + 1) * 3, 1),
          isCurrent: y === now.getFullYear() && Math.floor(now.getMonth() / 3) === q,
          isPast: y < now.getFullYear() || (y === now.getFullYear() && Math.floor(now.getMonth() / 3) > q),
        })
      }
    }
  } else {
    const startYear = now.getFullYear()
    for (let y = startYear; y < startYear + 5; y++) {
      cols.push({
        key: `${y}`,
        label: y.toString(),
        start: new Date(y, 0, 1),
        end: new Date(y + 1, 0, 1),
        isCurrent: y === now.getFullYear(),
        isPast: y < now.getFullYear(),
      })
    }
  }

  return cols
})

const goalCountBySphere = (sphereId) => allGoals.value.filter(g => g.life_sphere_id === sphereId).length

const toggleSphereVisibility = (id) => {
  const next = new Set(hiddenSpheres.value)
  if (next.has(id)) {
    next.delete(id)
  } else {
    next.add(id)
  }
  hiddenSpheres.value = next
}

const multiYears = computed(() => {
  const start = new Date().getFullYear()
  const count = scale.value === '3year' ? 3 : 5
  return Array.from({ length: count }, (_, i) => start + i)
})

const goalsInYear = (y) => {
  return filteredGoals.value
    .filter(g => g.deadline && new Date(g.deadline).getFullYear() === y)
    .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
}

// Goals in current visible range (for sidebar counts)
const rangeGoals = computed(() => {
  return allGoals.value.filter(g => {
    if (!g.deadline) return false
    const d = new Date(g.deadline)
    if (scale.value === 'month') {
      return d.getFullYear() === calYear.value && d.getMonth() === calMonth.value
    }
    if (scale.value === 'year') {
      return d.getFullYear() === viewYear.value
    }
    if (scale.value === '3year') {
      const y = new Date().getFullYear()
      return d.getFullYear() >= y && d.getFullYear() < y + 3
    }
    // 5year
    const y = new Date().getFullYear()
    return d.getFullYear() >= y && d.getFullYear() < y + 5
  })
})

const rangeGoalCountBySphere = (sphereId) => rangeGoals.value.filter(g => g.life_sphere_id === sphereId).length

const viewYear = ref(new Date().getFullYear())
const monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
const monthNamesShort = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек']

const matrixRows = computed(() => {
  const rows = []
  for (const sphere of sortedSpheres.value) {
    if (hiddenSpheres.value.has(sphere.id)) continue
    rows.push({ id: sphere.id, name: sphere.name, color: sphere.color })
  }
  const noSphere = filteredGoals.value.some(g => !g.life_sphere_id)
  if (noSphere) {
    rows.push({ id: null, name: 'Без сферы', color: '#9ca3af' })
  }
  return rows
})

const matrixGoals = (sphereId, month) => {
  return filteredGoals.value
    .filter(g => {
      if (!g.deadline) return false
      const d = new Date(g.deadline)
      const matchSphere = sphereId === null ? !g.life_sphere_id : g.life_sphere_id === sphereId
      return matchSphere && d.getFullYear() === viewYear.value && d.getMonth() === month
    })
    .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
}

const isCurrentYearMonth = (m) => viewYear.value === new Date().getFullYear() && m === new Date().getMonth()

const goalsInMonth = (m) => {
  return filteredGoals.value
    .filter(g => {
      if (!g.deadline) return false
      const d = new Date(g.deadline)
      return d.getFullYear() === viewYear.value && d.getMonth() === m
    })
    .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
}

// Calendar
const now = new Date()
const calYear = ref(now.getFullYear())
const calMonth = ref(now.getMonth())

const calendarCells = computed(() => {
  const firstDay = new Date(calYear.value, calMonth.value, 1)
  let startDow = firstDay.getDay()
  if (startDow === 0) startDow = 7 // Mon=1
  const daysInMonth = new Date(calYear.value, calMonth.value + 1, 0).getDate()
  const today = new Date()
  today.setHours(0,0,0,0)

  const cells = []
  // Previous month padding
  const prevDays = new Date(calYear.value, calMonth.value, 0).getDate()
  for (let i = startDow - 2; i >= 0; i--) {
    cells.push({ day: prevDays - i, isCurrentMonth: false, isToday: false, goals: [] })
  }
  // Current month
  for (let d = 1; d <= daysInMonth; d++) {
    const date = new Date(calYear.value, calMonth.value, d)
    date.setHours(0,0,0,0)
    const dateStr = `${calYear.value}-${String(calMonth.value+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`
    const goals = filteredGoals.value.filter(g => g.deadline && g.deadline.substring(0,10) === dateStr)
    cells.push({ day: d, isCurrentMonth: true, isToday: date.getTime() === today.getTime(), goals })
  }
  // Next month padding
  while (cells.length % 7 !== 0) {
    cells.push({ day: cells.length - (startDow - 1 + daysInMonth) + 1, isCurrentMonth: false, isToday: false, goals: [] })
  }
  return cells
})

const sphereOrder = ref([])
const sortedSpheres = computed(() => {
  if (sphereOrder.value.length > 0) {
    return sphereOrder.value.map(id => spheres.value.find(s => s.id === id)).filter(Boolean)
  }
  return spheres.value
})

// Position goal block on timeline
const goalStyle = (goal) => {
  const cols = timeColumns.value
  if (!cols.length) return { display: 'none' }

  const timelineStart = cols[0].start.getTime()
  const timelineEnd = cols[cols.length - 1].end.getTime()
  const totalWidth = timelineEnd - timelineStart

  const deadline = goal.deadline ? new Date(goal.deadline).getTime() : timelineEnd
  const clampedDeadline = Math.min(Math.max(deadline, timelineStart), timelineEnd)

  // Block width = ~1 column width, positioned at deadline
  const colWidth = 100 / cols.length
  const position = ((clampedDeadline - timelineStart) / totalWidth) * 100
  const left = Math.max(0, position - colWidth)
  const width = Math.min(colWidth * 1.5, 100 - left)

  const color = goal.life_sphere?.color || '#6366f1'

  return {
    left: left + '%',
    width: width + '%',
    backgroundColor: color,
    minWidth: '60px',
  }
}

// Balance segments
const balanceSegments = computed(() => {
  const counts = {}
  const rangeFiltered = rangeGoals.value.filter(g => !hiddenSpheres.value.has(g.life_sphere_id))
  const src = rangeFiltered
  for (const g of src) {
    const sid = g.life_sphere_id || 'none'
    counts[sid] = (counts[sid] || 0) + 1
  }
  const total = src.length || 1
  const segs = []
  for (const sphere of spheres.value) {
    if (counts[sphere.id]) {
      segs.push({ sphereId: sphere.id, name: sphere.name, color: sphere.color, count: counts[sphere.id], pct: (counts[sphere.id] / total) * 100 })
    }
  }
  if (counts['none']) {
    segs.push({ sphereId: 'none', name: 'Без сферы', color: '#9ca3af', count: counts['none'], pct: (counts['none'] / total) * 100 })
  }
  return segs
})

// Pie chart slices
const pieSlices = computed(() => {
  const circumference = 2 * Math.PI * 40 // ~251.3
  let accumulated = 0
  return balanceSegments.value.map(seg => {
    const dash = (seg.pct / 100) * circumference
    const gap = circumference - dash
    const offset = -accumulated
    accumulated += dash
    return { ...seg, dash: `${dash} ${gap}`, offset }
  })
})

// Stream matrix
const matrixSpheres = computed(() => {
  return sortedSpheres.value.filter(s => !hiddenSpheres.value.has(s.id))
})

const streamYears = computed(() => {
  const start = new Date().getFullYear()
  return Array.from({ length: 5 }, (_, i) => start + i)
})

const scaleYears = computed(() => {
  const start = new Date().getFullYear()
  const count = scale.value === 'year' ? 1 : scale.value === '3year' ? 3 : 5
  return Array.from({ length: count }, (_, i) => start + i)
})

const isNowYearMonth = (yr, m) => yr === new Date().getFullYear() && m === new Date().getMonth()

const streamMatrixGoals = (sphereId, yr, m) => {
  return filteredGoals.value
    .filter(g => {
      if (!g.deadline) return false
      const d = new Date(g.deadline)
      return g.life_sphere_id === sphereId && d.getFullYear() === yr && d.getMonth() === m
    })
    .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
}

const getRemainingDays = (s) => {
  if (!s) return null
  const d = new Date(s); const t = new Date()
  d.setHours(0,0,0,0); t.setHours(0,0,0,0)
  return Math.ceil((d - t) / 86400000)
}
const formatDeadline = (s) => {
  const days = getRemainingDays(s)
  if (days === null) return ''
  if (days < 0) return `просрочено ${Math.abs(days)} дн.`
  if (days === 0) return 'Сегодня'
  if (days === 1) return 'Завтра'
  if (days <= 30) return `${days} дн.`
  return new Date(s).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}
const deadlineClass = (s) => {
  const days = getRemainingDays(s)
  if (days === null) return 'text-gray-400'
  if (days < 0) return 'text-red-500'
  if (days <= 7) return 'text-amber-500'
  return 'text-gray-400'
}

const handleDragStart = (e, goal) => {
  dragGoal.value = goal
  e.dataTransfer.effectAllowed = 'move'
}

const handleDrop = async (sphereId, year, month) => {
  dragOver.value = null
  if (!dragGoal.value) return
  const goal = dragGoal.value
  dragGoal.value = null

  const newDeadline = `${year}-${String(month + 1).padStart(2, '0')}-01`
  const updates = {}
  if (goal.deadline?.substring(0, 7) !== `${year}-${String(month + 1).padStart(2, '0')}`) {
    updates.deadline = newDeadline
  }
  if (goal.life_sphere_id !== sphereId) {
    updates.life_sphere_id = sphereId
  }
  if (Object.keys(updates).length === 0) return

  // Optimistic
  if (updates.deadline) goal.deadline = newDeadline + 'T00:00:00.000000Z'
  if (updates.life_sphere_id !== undefined) {
    goal.life_sphere_id = sphereId
    goal.life_sphere = spheres.value.find(s => s.id === sphereId) || null
  }

  try {
    await goalsStore.updateGoal(goal.id, updates)
  } catch {
    await goalsStore.fetchAllGoals({ force: true })
  }
}

const handleCalDrop = async (cell) => {
  dragOver.value = null
  if (!dragGoal.value || !cell.isCurrentMonth) return
  const goal = dragGoal.value
  dragGoal.value = null

  const newDeadline = `${calYear.value}-${String(calMonth.value + 1).padStart(2, '0')}-${String(cell.day).padStart(2, '0')}`
  goal.deadline = newDeadline + 'T00:00:00.000000Z'

  try {
    await goalsStore.updateGoal(goal.id, { deadline: newDeadline })
  } catch {
    await goalsStore.fetchAllGoals({ force: true })
  }
}

const openGoal = (goal) => router.push(`/goals/${goal.id}`)

const openNewGoalFromCal = (day) => {
  const deadline = `${calYear.value}-${String(calMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
  selectedGoal.value = { deadline }
  showGoalModal.value = true
}

const openNewGoal = (sphereId, year, month) => {
  const deadline = `${year}-${String(month + 1).padStart(2, '0')}-01`
  selectedGoal.value = { life_sphere_id: sphereId, deadline }
  showGoalModal.value = true
}

const handleSaveGoal = async (goalData) => {
  goalError.value = ''
  try {
    if (selectedGoal.value) {
      await goalsStore.updateGoal(selectedGoal.value.id, goalData)
    } else {
      await goalsStore.createGoal(goalData)
    }
    showGoalModal.value = false
    selectedGoal.value = null
    await goalsStore.fetchAllGoals({ force: true })
  } catch (err) {
    goalError.value = err.response?.data?.message || err.message || 'Ошибка при сохранении цели'
  }
}

const handleCloseGoalModal = () => {
  showGoalModal.value = false
  selectedGoal.value = null
  goalError.value = ''
}

onMounted(async () => {
  await Promise.all([
    goalsStore.fetchAllGoals(),
    spheresStore.fetchAll(),
  ])
  // Fix sphere order once on load, sorted by goal count desc
  sphereOrder.value = [...spheres.value]
    .sort((a, b) => goalCountBySphere(b.id) - goalCountBySphere(a.id))
    .map(s => s.id)
})
</script>
