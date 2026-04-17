<template>
  <div class="p-4 sm:p-6 max-w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center space-x-4">
        <button
          @click="prevMonth"
          class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize">
          {{ monthLabel }}
        </h1>
        <button
          @click="nextMonth"
          class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
    </div>

    <!-- ========== MOBILE: карточки ========== -->
    <div v-else-if="store.challenges.length > 0" class="block lg:hidden space-y-3">
      <div
        v-for="challenge in store.challenges"
        :key="challenge.id"
        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden"
      >
        <div class="flex items-center justify-between px-4 py-3">
          <div class="flex items-center space-x-3 flex-1 min-w-0">
            <!-- Big toggle button -->
            <button
              @click="toggleToday(challenge)"
              class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-all active:scale-95"
              :class="isTodayCompleted(challenge)
                ? 'bg-emerald-500 text-white shadow-sm'
                : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-2 border-gray-200 dark:border-gray-700'"
            >
              <CheckIcon class="w-7 h-7" :class="isTodayCompleted(challenge) ? 'stroke-[3]' : ''" />
            </button>
            <div class="min-w-0 flex-1">
              <span
                v-if="editingId !== challenge.id"
                @click="startEdit(challenge)"
                class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate block"
              >
                {{ challenge.title }}
              </span>
              <input
                v-else
                ref="editInput"
                v-model="editTitle"
                @keydown.enter="saveEdit(challenge)"
                @keydown.escape="cancelEdit"
                @blur="saveEdit(challenge)"
                class="bg-transparent border-b border-primary-500 outline-none text-sm w-full text-gray-900 dark:text-gray-100"
              />
              <span class="text-xs" :class="percentClass(completionPercent(challenge))">
                {{ completionPercent(challenge) }}% за месяц
              </span>
            </div>
          </div>
          <button
            @click="removeChallenge(challenge)"
            class="ml-2 p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>
        <!-- Mini streak dots -->
        <div class="px-4 pb-3 flex gap-[3px] flex-wrap">
          <div
            v-for="day in daysInMonth"
            :key="day"
            class="w-[7px] h-[7px] rounded-full"
            :class="isDayCompleted(challenge, day)
              ? 'bg-emerald-500'
              : isToday(day)
                ? 'bg-primary-400 ring-1 ring-primary-300'
                : day < todayDay
                  ? 'bg-gray-200 dark:bg-gray-700'
                  : 'bg-gray-100 dark:bg-gray-800'"
          />
        </div>
      </div>

      <!-- Add (mobile) -->
      <div class="flex items-center rounded-xl border border-dashed border-gray-300 dark:border-gray-600 px-4 py-3">
        <PlusIcon class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" />
        <input
          v-model="newTitle"
          @keydown.enter="addChallenge"
          placeholder="Новая привычка..."
          class="bg-transparent text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500 outline-none w-full"
        />
      </div>
    </div>

    <!-- ========== DESKTOP: таблица ========== -->
    <div v-else-if="store.challenges.length > 0" class="hidden lg:block overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
      <table class="min-w-full border-collapse">
        <thead>
          <tr>
            <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-200 dark:border-gray-700 min-w-[180px]">
              Привычка
            </th>
            <th
              v-for="day in daysInMonth"
              :key="day"
              class="px-0 py-1 text-center text-[10px] font-medium border-b border-gray-200 dark:border-gray-700 w-9 min-w-[36px]"
              :class="isToday(day)
                ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400'
                : isWeekend(day)
                  ? 'bg-gray-100 dark:bg-gray-750 text-gray-400 dark:text-gray-500'
                  : 'bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400'"
            >
              <div>{{ dayOfWeekShort(day) }}</div>
              <div class="text-xs font-semibold">{{ day }}</div>
            </th>
            <th class="bg-gray-50 dark:bg-gray-800 px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 border-b border-l border-gray-200 dark:border-gray-700 w-12">
              %
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="challenge in store.challenges"
            :key="challenge.id"
            class="group"
          >
            <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-700 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50">
              <div class="flex items-center justify-between">
                <span
                  v-if="editingId !== challenge.id"
                  @dblclick="startEdit(challenge)"
                  class="cursor-default truncate"
                >
                  {{ challenge.title }}
                </span>
                <input
                  v-else
                  ref="editInput"
                  v-model="editTitle"
                  @keydown.enter="saveEdit(challenge)"
                  @keydown.escape="cancelEdit"
                  @blur="saveEdit(challenge)"
                  class="bg-transparent border-b border-primary-500 outline-none text-sm w-full text-gray-900 dark:text-gray-100"
                />
                <button
                  v-if="editingId !== challenge.id"
                  @click="removeChallenge(challenge)"
                  class="opacity-0 group-hover:opacity-100 ml-2 p-0.5 text-gray-400 hover:text-red-500 transition-all"
                >
                  <XMarkIcon class="w-3.5 h-3.5" />
                </button>
              </div>
            </td>
            <td
              v-for="day in daysInMonth"
              :key="day"
              class="px-0 py-0 text-center border-b border-gray-200 dark:border-gray-700 transition-colors"
              :class="[cellClass(challenge, day), isToday(day) ? 'cursor-pointer' : 'cursor-default']"
              @click="isToday(day) && toggleDay(challenge, day)"
            >
              <div class="w-9 h-8 flex items-center justify-center">
                <CheckIcon v-if="isDayCompleted(challenge, day)" class="w-4 h-4" />
              </div>
            </td>
            <td class="bg-white dark:bg-gray-900 px-2 py-1.5 text-center text-xs font-medium border-b border-l border-gray-200 dark:border-gray-700"
                :class="percentClass(completionPercent(challenge))"
            >
              {{ completionPercent(challenge) }}%
            </td>
          </tr>
          <!-- Add row -->
          <tr>
            <td :colspan="daysInMonth + 2" class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <input
                  v-model="newTitle"
                  @keydown.enter="addChallenge"
                  placeholder="Добавить привычку..."
                  class="bg-transparent text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500 outline-none w-full"
                />
                <button
                  v-if="newTitle.trim()"
                  @click="addChallenge"
                  class="ml-2 text-primary-500 hover:text-primary-600"
                >
                  <PlusIcon class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div
      v-if="!store.loading && store.challenges.length === 0"
      class="text-center py-12 text-gray-500 dark:text-gray-400"
    >
      <TableCellsIcon class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
      <p class="text-sm">Добавь первую привычку для отслеживания</p>
      <div class="mt-4 flex items-center justify-center">
        <input
          v-model="newTitle"
          @keydown.enter="addChallenge"
          placeholder="Например: зарядка, 2л воды..."
          class="bg-transparent border-b border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 outline-none w-64 py-1 text-center"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useChallengesStore } from '@/stores/challenges'
import { useConfirmStore } from '@/stores/confirm'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  CheckIcon,
  XMarkIcon,
  PlusIcon,
  TableCellsIcon,
} from '@heroicons/vue/24/outline'

const store = useChallengesStore()
const confirmStore = useConfirmStore()

const now = new Date()
const year = ref(now.getFullYear())
const month = ref(now.getMonth())

const newTitle = ref('')
const editingId = ref(null)
const editTitle = ref('')
const editInput = ref(null)

const SHORT_DAYS = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']

const monthKey = computed(() => {
  const m = String(month.value + 1).padStart(2, '0')
  return `${year.value}-${m}`
})

const monthLabel = computed(() => {
  const date = new Date(year.value, month.value, 1)
  return date.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
})

const daysInMonth = computed(() => {
  return new Date(year.value, month.value + 1, 0).getDate()
})

const todayStr = computed(() => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
})

const todayDay = computed(() => {
  const d = new Date()
  if (d.getFullYear() === year.value && d.getMonth() === month.value) {
    return d.getDate()
  }
  return 0
})

function dateStr(day) {
  const m = String(month.value + 1).padStart(2, '0')
  const d = String(day).padStart(2, '0')
  return `${year.value}-${m}-${d}`
}

function isToday(day) {
  return dateStr(day) === todayStr.value
}

function isWeekend(day) {
  const d = new Date(year.value, month.value, day)
  return d.getDay() === 0 || d.getDay() === 6
}

function dayOfWeekShort(day) {
  const d = new Date(year.value, month.value, day)
  return SHORT_DAYS[d.getDay()]
}

function isDayCompleted(challenge, day) {
  const ds = dateStr(day)
  return challenge.entries?.some(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === ds && e.completed
  })
}

function isTodayCompleted(challenge) {
  return isDayCompleted(challenge, todayDay.value)
}

function cellClass(challenge, day) {
  const completed = isDayCompleted(challenge, day)
  const today = isToday(day)
  const weekend = isWeekend(day)

  if (completed) {
    return 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400'
  }
  if (today) {
    return 'bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30'
  }
  if (weekend) {
    return 'bg-gray-50 dark:bg-gray-800/50'
  }
  return 'bg-white dark:bg-gray-900'
}

function completionPercent(challenge) {
  const total = daysInMonth.value
  const completed = challenge.entries?.filter(e => e.completed).length || 0
  return Math.round((completed / total) * 100)
}

function percentClass(pct) {
  if (pct >= 80) return 'text-emerald-600 dark:text-emerald-400'
  if (pct >= 50) return 'text-amber-600 dark:text-amber-400'
  if (pct > 0) return 'text-orange-500 dark:text-orange-400'
  return 'text-gray-400 dark:text-gray-500'
}

function prevMonth() {
  if (month.value === 0) {
    month.value = 11
    year.value--
  } else {
    month.value--
  }
  store.fetchChallenges(monthKey.value)
}

function nextMonth() {
  if (month.value === 11) {
    month.value = 0
    year.value++
  } else {
    month.value++
  }
  store.fetchChallenges(monthKey.value)
}

async function toggleDay(challenge, day) {
  if (!isToday(day)) return
  await store.toggle(challenge.id, dateStr(day))
}

async function toggleToday(challenge) {
  if (!todayDay.value) return
  await store.toggle(challenge.id, todayStr.value)
}

async function addChallenge() {
  const title = newTitle.value.trim()
  if (!title) return
  await store.create(title)
  newTitle.value = ''
}

function startEdit(challenge) {
  editingId.value = challenge.id
  editTitle.value = challenge.title
  nextTick(() => {
    editInput.value?.[0]?.focus?.() || editInput.value?.focus?.()
  })
}

async function saveEdit(challenge) {
  if (editingId.value !== challenge.id) return
  const title = editTitle.value.trim()
  editingId.value = null
  if (title && title !== challenge.title) {
    await store.update(challenge.id, { title })
  }
}

function cancelEdit() {
  editingId.value = null
}

async function removeChallenge(challenge) {
  const confirmed = await confirmStore.confirm({
    title: 'Удалить привычку',
    message: `Удалить "${challenge.title}" и все отметки?`,
    confirmText: 'Удалить',
    cancelText: 'Отмена',
    isDanger: true,
  })
  if (confirmed) {
    await store.remove(challenge.id)
  }
}

onMounted(() => {
  store.fetchChallenges(monthKey.value)
})
</script>
