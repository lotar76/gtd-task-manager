<template>
  <div v-if="data" class="h-full">
    <!-- Tasks for today block -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 h-full flex flex-col">
      <!-- Header with button -->
      <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Задачи на сегодня</h3>
        <button
          @click="openCreateModal"
          class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors duration-200"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span class="hidden sm:inline">Добавить</span>
        </button>
      </div>

      <!-- Tasks list -->
      <div v-if="allTasks.length" class="space-y-1.5 flex-1 overflow-y-auto">
        <div
          v-for="task in allTasks"
          :key="task.id"
          @click="handleTaskClick(task)"
          @mouseenter="handleTaskHover(task.sphere_name)"
          @mouseleave="handleTaskLeave"
          class="group flex items-start gap-2 p-2 rounded-lg border transition-all duration-200 cursor-pointer"
          :class="
            task.completed_at
              ? 'bg-gray-50/50 dark:bg-gray-700/30 border-gray-200 dark:border-gray-600'
              : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
          "
          :style="isTaskHighlighted(task) ? { borderLeftColor: task.sphere_color } : {}"
        >
          <!-- Sphere indicator -->
          <div
            class="w-0.5 h-full rounded-full flex-shrink-0 mt-0.5 transition-all duration-200"
            :style="{ backgroundColor: task.sphere_color }"
          />

          <!-- Task content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
              <!-- Checkbox -->
              <div
                class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition-colors"
                :class="task.completed_at
                  ? 'bg-emerald-500 border-emerald-500'
                  : 'border-gray-300 dark:border-gray-600 group-hover:border-gray-400 dark:group-hover:border-gray-500'"
              >
                <svg v-if="task.completed_at" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
              </div>

              <!-- Title -->
              <span
                class="text-xs font-medium truncate flex-1"
                :class="task.completed_at
                  ? 'line-through text-gray-400 dark:text-gray-500'
                  : 'text-gray-900 dark:text-white'"
              >
                {{ task.title }}
              </span>

              <!-- Time badge -->
              <span
                v-if="task.estimated_time || task.end_time"
                class="px-1.5 py-0.5 text-[10px] font-medium rounded-full flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400"
              >
                <span v-if="task.estimated_time && task.end_time">
                  {{ formatTime(task.estimated_time) }}-{{ formatTime(task.end_time) }}
                </span>
                <span v-else-if="task.estimated_time">
                  {{ formatTime(task.estimated_time) }}
                </span>
                <span v-else-if="task.end_time">
                  до {{ formatTime(task.end_time) }}
                </span>
              </span>

              <!-- Sphere badge -->
              <span
                class="px-1.5 py-0.5 text-[10px] font-medium rounded-full flex-shrink-0 transition-all duration-200"
                :style="{
                  backgroundColor: task.sphere_color + '20',
                  color: task.sphere_color
                }"
              >
                {{ task.sphere_name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="flex-1 flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Нет задач на сегодня</p>
      </div>

      <!-- Summary stats -->
      <div v-if="allTasks.length" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-xs">
          <span class="text-gray-600 dark:text-gray-400">
            <span class="font-semibold text-gray-900 dark:text-white">{{ data.summary?.completed || 0 }}</span>/{{ data.summary?.total_tasks || 0 }}
          </span>
          <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ data.summary?.completion_rate || 0 }}%</span>
        </div>
      </div>
    </div>

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
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import { useTasksStore } from '@/stores/tasks'
import { useDashboardStore } from '@/stores/dashboard'

const tasksStore = useTasksStore()
const dashboardStore = useDashboardStore()

const props = defineProps({
  data: { type: Object, default: null },
  hoveredSphereName: { type: String, default: null },
  workspaceId: { type: Number, required: true },
})

const emit = defineEmits(['task-hover', 'task-leave'])

const showTaskModal = ref(false)
const showTaskView = ref(false)
const editingTask = ref(null)
const selectedTask = ref(null)
const taskError = ref('')

// Flatten all tasks from all spheres into a single list
const allTasks = computed(() => {
  if (!props.data) return []

  const tasks = []

  // Задачи из сфер
  if (props.data.spheres) {
    props.data.spheres.forEach(sphere => {
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

  // Задачи без сфер
  if (props.data.tasks_without_sphere?.length) {
    props.data.tasks_without_sphere.forEach(task => {
      tasks.push({
        ...task,
        sphere_name: 'Без сферы',
        sphere_color: '#6b7280', // серый цвет
      })
    })
  }

  // Sort: incomplete first, then by sphere
  return tasks.sort((a, b) => {
    if (a.completed_at && !b.completed_at) return 1
    if (!a.completed_at && b.completed_at) return -1
    return a.sphere_name.localeCompare(b.sphere_name)
  })
})

const isTaskHighlighted = (task) => {
  return props.hoveredSphereName && task.sphere_name === props.hoveredSphereName
}

const handleTaskHover = (sphereName) => {
  emit('task-hover', sphereName)
}

const handleTaskLeave = () => {
  emit('task-leave')
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
}

const openCreateModal = () => {
  // Create a new task with today's date, workspace, and "Сегодня" status
  const today = new Date().toISOString().split('T')[0]
  editingTask.value = {
    workspace_id: props.workspaceId,
    due_date: today,
    status: 'today', // Папка "Сегодня"
    estimated_time: null,
    end_time: null,
  }
  showTaskModal.value = true
}

const closeTaskModal = () => {
  showTaskModal.value = false
  editingTask.value = null
  taskError.value = ''
}

const handleEnterEdit = () => {
  editingTask.value = selectedTask.value
  showTaskView.value = false
  showTaskModal.value = true
}

const handleCompleteTask = async (task) => {
  try {
    await tasksStore.completeTask(task.id)
    // Refresh dashboard data
    await dashboardStore.fetchLifeMirror(props.workspaceId)
    await dashboardStore.fetchAiMessage(props.workspaceId, false)
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleUncompleteTask = async (task) => {
  try {
    await tasksStore.uncompleteTask(task.id)
    // Refresh dashboard data
    await dashboardStore.fetchLifeMirror(props.workspaceId)
    await dashboardStore.fetchAiMessage(props.workspaceId, false)
  } catch (error) {
    console.error('Error uncompleting task:', error)
  }
}

const formatTime = (time) => {
  if (!time) return ''
  // Если время уже в формате HH:mm, возвращаем как есть
  if (/^\d{2}:\d{2}$/.test(time)) return time
  // Иначе извлекаем HH:mm из формата HH:mm:ss
  return time.substring(0, 5)
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (editingTask.value?.id) {
      await tasksStore.updateTask(editingTask.value.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    closeTaskModal()
    // Refresh dashboard data to show the new/updated task
    await dashboardStore.fetchLifeMirror(props.workspaceId)
    await dashboardStore.fetchAiMessage(props.workspaceId, false)
  } catch (error) {
    console.error('Error saving task:', error)
    taskError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении задачи'
  }
}
</script>
