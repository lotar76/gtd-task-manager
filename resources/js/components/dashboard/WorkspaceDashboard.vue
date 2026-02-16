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
    <!-- For "day" period: 3-column compact layout -->
    <template v-if="period === 'day'">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- 1. Рекомендации ИИ -->
        <AiHeroBanner
          :message="data.aiMessage"
          :loading="data.aiLoading"
          :silent-loading="data.aiSilentLoading"
          @analyze="handleAnalyze"
          @refresh="handleRefreshAi"
        />

        <!-- 2. Диаграмма с вкладками Сферы/Цели -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-2 sm:p-4">
          <!-- Tabs -->
          <div class="flex gap-2 mb-4 border-b border-gray-200 dark:border-gray-700">
            <button
              @click="activeTab = 'spheres'"
              class="px-4 py-2 text-sm font-medium transition-colors border-b-2"
              :class="activeTab === 'spheres'
                ? 'text-primary-600 dark:text-primary-400 border-primary-600 dark:border-primary-400'
                : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'"
            >
              Сферы
            </button>
            <button
              @click="activeTab = 'goals'"
              class="px-4 py-2 text-sm font-medium transition-colors border-b-2"
              :class="activeTab === 'goals'
                ? 'text-primary-600 dark:text-primary-400 border-primary-600 dark:border-primary-400'
                : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'"
            >
              Цели
            </button>
          </div>

          <!-- Tab content -->
          <div class="flex flex-col items-center justify-center">
            <!-- Spheres tab -->
            <BalanceWheel
              v-if="activeTab === 'spheres'"
              :spheres="wheelSpheres"
              :balance-index="data.lifeMirror?.balance_index || 0"
              :size="wheelSize"
              :hovered-sphere-name="hoveredSphereName"
              @sphere-hover="handleSphereHover"
              @sphere-leave="handleSphereLeave"
              @sphere-click="handleSphereClick"
            />

            <!-- Goals tab -->
            <BalanceWheel
              v-else-if="activeTab === 'goals' && wheelGoals.length"
              :spheres="wheelGoals"
              :balance-index="goalsBalanceIndex"
              :size="wheelSize"
              :hovered-sphere-name="hoveredSphereName"
              @sphere-hover="handleSphereHover"
              @sphere-leave="handleSphereLeave"
              @sphere-click="handleSphereClick"
            />
            <div v-else class="flex items-center justify-center py-16 text-gray-400 dark:text-gray-500 text-sm">
              Нет целей
            </div>
          </div>
        </div>

        <!-- 3. Задачи на сегодня -->
        <PeriodDayView
          :data="data.lifeMirror"
          :workspace-id="workspaceId"
          :hovered-sphere-name="hoveredSphereName"
          @task-hover="handleSphereHover"
          @task-leave="handleSphereLeave"
        />
      </div>

      <!-- Day Timeline (below 3 columns) -->
      <DayTimeline
        :tasks="allDayTasks"
        @task-click="handleTaskClick"
        @changes-saved="handleTimelineChangesSaved"
      />
    </template>

    <!-- For other periods: AI banner at the top -->
    <template v-else>
      <AiHeroBanner
        :message="data.aiMessage"
        :loading="data.aiLoading"
        @analyze="handleAnalyze"
        @refresh="handleRefreshAi"
        class="mb-6"
      />
    </template>

    <!-- Balance Wheel + Sphere Cards (for non-day periods only) -->
    <template v-if="period !== 'day'">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Wheel -->
        <div class="lg:col-span-1 flex items-center justify-center">
          <BalanceWheel
            :spheres="wheelSpheres"
            :balance-index="data.lifeMirror?.balance_index || 0"
            :size="wheelSize"
            :hovered-sphere-name="hoveredSphereName"
            @sphere-hover="handleSphereHover"
            @sphere-leave="handleSphereLeave"
            @sphere-click="handleSphereClick"
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
    </template>

    <!-- For other periods: period-specific view -->
    <template v-if="period !== 'day'">
      <component
        :is="periodComponent"
        :data="data.lifeMirror"
      />
    </template>
  </template>

  <!-- Task View -->
  <TaskView
    :show="showTaskView"
    :task="selectedTask"
    @close="showTaskView = false; selectedTask = null"
    @enter-edit="handleEnterEdit"
    @complete-task="handleCompleteTask"
    @uncomplete-task="handleUncompleteTask"
  />

  <!-- Task Modal -->
  <TaskModal
    v-if="showTaskModal"
    :show="showTaskModal"
    :task="editingTask"
    :server-error="taskError"
    @close="closeTaskModal"
    @submit="handleSaveTask"
  />
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import AiHeroBanner from '@/components/dashboard/AiHeroBanner.vue'
import BalanceWheel from '@/components/dashboard/BalanceWheel.vue'
import SphereCard from '@/components/dashboard/SphereCard.vue'
import MissingSpheres from '@/components/dashboard/MissingSpheres.vue'
import PeriodDayView from '@/components/dashboard/PeriodDayView.vue'
import DayTimeline from '@/components/dashboard/DayTimeline.vue'
import PeriodWeekView from '@/components/dashboard/PeriodWeekView.vue'
import PeriodMonthView from '@/components/dashboard/PeriodMonthView.vue'
import PeriodYearView from '@/components/dashboard/PeriodYearView.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import { useTasksStore } from '@/stores/tasks'
import { useDashboardStore } from '@/stores/dashboard'

const props = defineProps({
  workspaceId: { type: Number, required: true },
  period: { type: String, required: true },
  data: { type: Object, default: null },
})

const tasksStore = useTasksStore()
const dashboardStore = useDashboardStore()

const hoveredSphereName = ref(null)
const activeTab = ref('spheres')
const windowWidth = ref(window.innerWidth)

// TaskModal state
const showTaskModal = ref(false)
const showTaskView = ref(false)
const editingTask = ref(null)
const selectedTask = ref(null)
const taskError = ref('')

const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', updateWindowWidth)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateWindowWidth)
})

// Responsive wheel size: desktop (440px), tablet (360px), mobile (280px)
const wheelSize = computed(() => {
  if (windowWidth.value >= 1024) return 440 // lg breakpoint
  if (windowWidth.value >= 768) return 360  // md breakpoint
  return 280
})

const handleSphereHover = (sphereName) => {
  hoveredSphereName.value = sphereName
}

const handleSphereLeave = () => {
  hoveredSphereName.value = null
}

const emit = defineEmits(['refresh-ai', 'analyze-ai'])

const handleAnalyze = () => {
  emit('analyze-ai', false) // первый запуск, без force
}

const handleRefreshAi = () => {
  emit('analyze-ai', true) // принудительное обновление, с force
}

const handleSphereClick = (sphereIndex) => {
  const sphere = wheelSpheres.value[sphereIndex]

  if (!sphere || sphere.total !== 0) {
    return
  }

  // Открываем форму создания задачи с предустановленными полями
  const today = new Date().toISOString().split('T')[0]
  editingTask.value = {
    workspace_id: props.workspaceId,
    life_sphere_id: sphere.id,
    due_date: props.period === 'day' ? today : null,
    status: props.period === 'day' ? 'today' : 'new',
  }
  showTaskModal.value = true
}

const closeTaskModal = () => {
  showTaskModal.value = false
  editingTask.value = null
  selectedTask.value = null
  taskError.value = ''
}

const handleTaskClick = (task) => {
  // Make a copy to avoid mutations
  selectedTask.value = { ...task }
  showTaskView.value = true
}

const handleEnterEdit = () => {
  // Make sure to copy all task data including id
  editingTask.value = { ...selectedTask.value }
  showTaskView.value = false
  showTaskModal.value = true
}

const handleCompleteTask = async (task) => {
  try {
    await tasksStore.completeTask(task.id)
    // Refresh dashboard data
    await dashboardStore.fetchForWorkspace(props.workspaceId, true)
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleUncompleteTask = async (task) => {
  try {
    await tasksStore.uncompleteTask(task.id)
    // Refresh dashboard data
    await dashboardStore.fetchForWorkspace(props.workspaceId, true)
  } catch (error) {
    console.error('Error uncompleting task:', error)
  }
}

const handleTimelineChangesSaved = async (callback) => {
  // Refresh dashboard data after timeline changes
  await dashboardStore.fetchForWorkspace(props.workspaceId, true)
  // Call the callback after refresh is complete
  if (callback) callback()
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (taskData.id) {
      // Update existing task
      await tasksStore.updateTask(taskData.id, taskData)
    } else {
      // Create new task
      await tasksStore.createTask(taskData)
    }
    closeTaskModal()
    // Refresh dashboard data
    await dashboardStore.fetchForWorkspace(props.workspaceId, true)
  } catch (error) {
    console.error('Error saving task:', error)
    taskError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении задачи'
  }
}

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
    id: s.id,
    name: s.name,
    total: s.tasks_total || 0,
    done: s.tasks_done || 0,
    color: s.color,
  }))

  const missing = (mirror.missing_spheres || []).map(s => ({
    id: s.id,
    name: s.name,
    total: 0,
    done: 0,
    color: s.color,
  }))

  return [...active, ...missing]
})

// Цели для колеса (в формате как сферы)
const wheelGoals = computed(() => {
  const mirror = props.data?.lifeMirror
  if (!mirror?.goals) return []

  return mirror.goals.map(g => ({
    name: g.name,
    total: 100,
    done: g.progress || 0,
    color: g.color || '#9ca3af',
  }))
})

// Средний прогресс целей
const goalsBalanceIndex = computed(() => {
  if (!wheelGoals.value.length) return 0
  const sum = wheelGoals.value.reduce((acc, g) => acc + g.done, 0)
  return Math.round(sum / wheelGoals.value.length)
})

// Все задачи дня для таймлайна
const allDayTasks = computed(() => {
  const mirror = props.data?.lifeMirror
  if (!mirror) return []

  const tasks = []

  // Задачи из сфер
  if (mirror.spheres) {
    mirror.spheres.forEach(sphere => {
      if (sphere.tasks?.length) {
        sphere.tasks.forEach(task => {
          tasks.push({
            ...task,
            sphere_name: sphere.name,
            sphere_color: sphere.color,
          })
        })
      }
    })
  }

  // Задачи без сфер (с дефолтным серым цветом)
  if (mirror.tasks_without_sphere?.length) {
    mirror.tasks_without_sphere.forEach(task => {
      tasks.push({
        ...task,
        sphere_name: 'Без сферы',
        sphere_color: '#6b7280', // серый цвет
      })
    })
  }

  return tasks
})
</script>
