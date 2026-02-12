<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white mb-4">Все задачи</h1>

        <!-- Фильтры -->
        <div v-if="allTasks.length > 0" class="flex flex-wrap gap-2">
          <!-- Фильтр по секции -->
          <button
            v-for="section in availableSections"
            :key="section.value"
            @click="toggleSectionFilter(section.value)"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
            :class="filterSection === section.value
              ? 'border-transparent text-white ' + section.activeBg
              : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
          >
            <component :is="section.icon" class="w-3.5 h-3.5 mr-1.5" />
            {{ section.label }}
            <span class="ml-1.5 opacity-75">{{ section.count }}</span>
          </button>

          <!-- Фильтр просроченных задач -->
          <button
            v-if="overdueCount > 0"
            @click="filterOverdue = !filterOverdue"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
            :class="filterOverdue
              ? 'border-transparent bg-red-500 text-white'
              : 'border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950'"
          >
            <ExclamationTriangleIcon class="w-3.5 h-3.5 mr-1.5" />
            Просроченные
            <span class="ml-1.5 opacity-75">{{ overdueCount }}</span>
          </button>

          <!-- Разделитель -->
          <div class="w-px bg-gray-200 dark:bg-gray-600 mx-1 self-stretch"></div>

          <!-- Фильтр по пространству: "Все" -->
          <button
            @click="filterWorkspaceId = null"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
            :class="filterWorkspaceId === null
              ? 'border-transparent bg-indigo-500 text-white'
              : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
          >
            Все
            <span class="ml-1.5 opacity-75">{{ allTasks.length }}</span>
          </button>

          <!-- Фильтр по пространству: каждое -->
          <button
            v-for="ws in workspacesList"
            :key="ws.id"
            @click="filterWorkspaceId = ws.id"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
            :class="filterWorkspaceId === ws.id
              ? 'border-transparent bg-indigo-500 text-white'
              : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
          >
            {{ ws.name }}
            <span class="ml-1.5 opacity-75">{{ ws.count }}</span>
          </button>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <template v-else>
        <!-- Счётчик результатов -->
        <div v-if="allTasks.length > 0 && (filterSection || filterWorkspaceId || filterOverdue)" class="mb-3 flex items-center justify-between">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            Показано {{ filteredTasks.length }} из {{ allTasks.length }}
          </span>
          <button
            @click="clearFilters"
            class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
          >
            Сбросить фильтры
          </button>
        </div>

        <TaskList
          v-if="filteredTasks.length > 0"
          :tasks="filteredTasks"
          @task-click="handleTaskClick"
          @toggle-complete="handleToggleComplete"
        />

        <!-- Пустое при активных фильтрах -->
        <div v-else-if="allTasks.length > 0" class="text-center py-12">
          <p class="text-sm text-gray-500 dark:text-gray-400">Нет задач по выбранным фильтрам</p>
          <button
            @click="clearFilters"
            class="mt-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
          >
            Сбросить фильтры
          </button>
        </div>

        <!-- Пустое состояние -->
        <div v-else class="text-center py-12">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
              <RectangleStackIcon class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Задач пока нет</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
              Здесь отображаются все ваши активные задачи из всех разделов GTD.
              Начните с добавления задач во «Входящие» — записывайте всё,
              что приходит в голову, а затем распределяйте по нужным спискам.
            </p>
            <button
              @click="handleAddTask"
              class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
            >
              <PlusIcon class="w-5 h-5 mr-1.5" />
              Добавить задачу
            </button>
          </div>
        </div>
      </template>

      <TaskView
        :show="showTaskView"
        :task="selectedTask"
        @close="showTaskView = false; selectedTask = null"
        @enter-edit="handleEnterEdit"
        @complete-task="handleCompleteTask"
      />

      <TaskModal
        :show="showTaskModal"
        :task="selectedTask"
        :server-error="taskError"
        @close="handleCloseModal"
        @submit="handleSaveTask"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { useWorkspaceStore } from '@/stores/workspace'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import {
  RectangleStackIcon,
  PlusIcon,
  InboxIcon,
  CalendarIcon,
  CalendarDaysIcon,
  BoltIcon,
  ClockIcon,
  ArchiveBoxIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const tasksStore = useTasksStore()
const workspaceStore = useWorkspaceStore()

const allTasks = computed(() => tasksStore.filteredTasks)
const loading = computed(() => tasksStore.loading)
const showTaskModal = ref(false)
const showTaskView = ref(false)
const selectedTask = ref(null)
const taskError = ref('')

const filterSection = ref(null)
const filterWorkspaceId = ref(null)
const filterOverdue = ref(false)

// Конфигурация секций
const sectionConfig = {
  inbox: { label: 'Входящие', icon: InboxIcon, activeBg: 'bg-blue-500', order: 0 },
  today: { label: 'Сегодня', icon: CalendarIcon, activeBg: 'bg-amber-500', order: 1 },
  next_action: { label: 'Следующие', icon: BoltIcon, activeBg: 'bg-yellow-500', order: 2 },
  tomorrow: { label: 'Завтра', icon: CalendarDaysIcon, activeBg: 'bg-indigo-500', order: 3 },
  scheduled: { label: 'Запланированные', icon: CalendarDaysIcon, activeBg: 'bg-teal-500', order: 4 },
  waiting: { label: 'Ожидание', icon: ClockIcon, activeBg: 'bg-orange-500', order: 5 },
  someday: { label: 'Когда-нибудь', icon: ArchiveBoxIcon, activeBg: 'bg-purple-500', order: 6 },
}

// Доступные секции (только те, в которых есть задачи)
const availableSections = computed(() => {
  const counts = {}
  for (const task of allTasks.value) {
    counts[task.status] = (counts[task.status] || 0) + 1
  }
  return Object.entries(sectionConfig)
    .filter(([key]) => counts[key] > 0)
    .map(([key, config]) => ({
      value: key,
      label: config.label,
      icon: config.icon,
      activeBg: config.activeBg,
      order: config.order,
      count: counts[key],
    }))
    .sort((a, b) => a.order - b.order)
})

// Количество просроченных задач
const overdueCount = computed(() => {
  const now = new Date()
  return allTasks.value.filter(task => {
    if (!task.due_date || task.status === 'completed') return false
    const dueDate = new Date(task.due_date)
    return dueDate < now
  }).length
})

// Все пространства пользователя с количеством задач
const workspacesList = computed(() => {
  const counts = {}
  for (const task of allTasks.value) {
    counts[task.workspace_id] = (counts[task.workspace_id] || 0) + 1
  }
  return workspaceStore.workspaces.map(ws => ({
    id: ws.id,
    name: ws.name,
    count: counts[ws.id] || 0,
  }))
})

// Отфильтрованные задачи
const filteredTasks = computed(() => {
  let result = allTasks.value
  if (filterSection.value) {
    result = result.filter(t => t.status === filterSection.value)
  }
  if (filterWorkspaceId.value) {
    result = result.filter(t => t.workspace_id === filterWorkspaceId.value)
  }
  if (filterOverdue.value) {
    const now = new Date()
    result = result.filter(t => {
      if (!t.due_date || t.status === 'completed') return false
      const dueDate = new Date(t.due_date)
      return dueDate < now
    })
  }
  return result
})

const toggleSectionFilter = (value) => {
  filterSection.value = filterSection.value === value ? null : value
}

const clearFilters = () => {
  filterSection.value = null
  filterWorkspaceId.value = null
  filterOverdue.value = false
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
}

const handleAddTask = () => {
  selectedTask.value = null
  showTaskModal.value = true
}

const handleEnterEdit = () => {
  showTaskView.value = false
  showTaskModal.value = true
}

const handleCompleteTask = async (task) => {
  try {
    await tasksStore.completeTask(task.id)
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleToggleComplete = async (task) => {
  try {
    if (task.status === 'completed') {
      await tasksStore.updateTask(task.id, { status: 'inbox' })
    } else {
      await tasksStore.completeTask(task.id)
    }
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
    selectedTask.value = null
  } catch (error) {
    console.error('Error saving task:', error)
    let errorMessage = 'Ошибка при сохранении задачи'
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    taskError.value = errorMessage
  }
}

const handleCloseModal = () => {
  showTaskModal.value = false
  selectedTask.value = null
  taskError.value = ''
}
</script>
