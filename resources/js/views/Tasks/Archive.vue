<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white mb-4">Архив</h1>

        <!-- Фильтры -->
        <div v-if="allArchivedTasks.length > 0" class="flex flex-wrap gap-2">
          <!-- Фильтр по пространству: "Все" -->
          <button
            @click="filterWorkspaceId = null"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
            :class="filterWorkspaceId === null
              ? 'border-transparent bg-green-500 text-white'
              : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
          >
            Все
            <span class="ml-1.5 opacity-75">{{ allArchivedTasks.length }}</span>
          </button>

          <!-- Фильтр по пространству: каждое -->
          <button
            v-for="ws in workspacesList"
            :key="ws.id"
            @click="filterWorkspaceId = ws.id"
            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
            :class="filterWorkspaceId === ws.id
              ? 'border-transparent bg-green-500 text-white'
              : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
          >
            {{ ws.name }}
            <span class="ml-1.5 opacity-75">{{ ws.count }}</span>
          </button>
        </div>

        <p v-if="allArchivedTasks.length > 0" class="text-sm text-gray-500 dark:text-gray-400 mt-3">
          Показаны последние 20 завершённых задач
        </p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <template v-else>
        <!-- Счётчик результатов -->
        <div v-if="allArchivedTasks.length > 0 && filterWorkspaceId" class="mb-3 flex items-center justify-between">
          <span class="text-sm text-gray-500 dark:text-gray-400">
            Показано {{ filteredTasks.length }} из {{ allArchivedTasks.length }}
          </span>
          <button
            @click="filterWorkspaceId = null"
            class="text-xs text-green-600 hover:text-green-700 dark:text-green-400"
          >
            Сбросить фильтр
          </button>
        </div>

        <TaskList
          v-if="filteredTasks.length > 0"
          :tasks="filteredTasks"
          @task-click="handleTaskClick"
          @toggle-complete="handleToggleComplete"
        />

        <!-- Пустое при активных фильтрах -->
        <div v-else-if="allArchivedTasks.length > 0" class="text-center py-12">
          <p class="text-sm text-gray-500 dark:text-gray-400">Нет завершённых задач в этом пространстве</p>
          <button
            @click="filterWorkspaceId = null"
            class="mt-2 text-sm text-green-600 hover:text-green-700 dark:text-green-400"
          >
            Показать все
          </button>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
              <ArchiveBoxIcon class="w-8 h-8 text-green-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Архив пуст</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
              Здесь будут отображаться последние 20 завершённых задач.
              Завершите любую задачу, и она появится здесь.
            </p>
          </div>
        </div>
      </template>

      <TaskView
        :show="showTaskView"
        :task="selectedTask"
        @close="showTaskView = false; selectedTask = null"
        @enter-edit="handleEnterEdit"
        @complete-task="handleCompleteTask"
        @uncomplete-task="handleUncompleteTask"
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
import { ArchiveBoxIcon } from '@heroicons/vue/24/outline'

const tasksStore = useTasksStore()
const workspaceStore = useWorkspaceStore()

const allArchivedTasks = computed(() => tasksStore.archivedTasks)
const loading = computed(() => tasksStore.loading)
const showTaskModal = ref(false)
const showTaskView = ref(false)
const selectedTask = ref(null)
const taskError = ref('')
const filterWorkspaceId = ref(null)

// Все пространства пользователя с количеством архивных задач
const workspacesList = computed(() => {
  const counts = {}
  for (const task of allArchivedTasks.value) {
    counts[task.workspace_id] = (counts[task.workspace_id] || 0) + 1
  }
  return workspaceStore.workspaces
    .map(ws => ({
      id: ws.id,
      name: ws.name,
      count: counts[ws.id] || 0,
    }))
    .filter(ws => ws.count > 0) // Показываем только те пространства, где есть архивные задачи
})

// Отфильтрованные задачи
const filteredTasks = computed(() => {
  if (!filterWorkspaceId.value) {
    return allArchivedTasks.value
  }
  return allArchivedTasks.value.filter(t => t.workspace_id === filterWorkspaceId.value)
})

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
}

const handleEnterEdit = () => {
  showTaskView.value = false
  showTaskModal.value = true
}

const handleCompleteTask = async (task) => {
  // Эта функция не должна вызываться в архиве, но оставляем для совместимости
  try {
    await tasksStore.completeTask(task.id)
    showTaskView.value = false
    selectedTask.value = null
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleUncompleteTask = async (task) => {
  // Возврат задачи в работу
  try {
    await tasksStore.uncompleteTask(task.id)
    showTaskView.value = false
    selectedTask.value = null
  } catch (error) {
    console.error('Error uncompleting task:', error)
  }
}

const handleToggleComplete = async (task) => {
  // Возврат задачи из архива в работу (чекбокс)
  try {
    await tasksStore.uncompleteTask(task.id)
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
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
