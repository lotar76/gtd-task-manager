<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900">Входящие</h1>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <!-- Task List -->
      <TaskList
        v-else
        :tasks="tasks"
        @task-click="handleTaskClick"
        @toggle-complete="handleToggleComplete"
      />

      <!-- Task Modal -->
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
import { ref, onMounted, watch } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { useWorkspaceStore } from '@/stores/workspace'
import { useTaskEvents } from '@/composables/useTaskEvents'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'

const tasksStore = useTasksStore()
const workspaceStore = useWorkspaceStore()
const { taskUpdatedEvent } = useTaskEvents()

const tasks = ref([])
const loading = ref(false)
const showTaskModal = ref(false)
const selectedTask = ref(null)
const taskError = ref('')

const loadTasks = async () => {
  loading.value = true
  try {
    await tasksStore.fetchInbox(false)
    tasks.value = tasksStore.tasks
  } finally {
    loading.value = false
  }
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskModal.value = true
}

const handleToggleComplete = async (task) => {
  try {
    if (task.status === 'completed') {
      // Возврат в работу
      await tasksStore.updateTask(task.id, { status: 'inbox' })
    } else {
      // Завершить
      await tasksStore.completeTask(task.id)
    }
    loadTasks()
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value) {
      // Обновление существующей задачи
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      // Создание новой задачи
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
    selectedTask.value = null
    await loadTasks()
  } catch (error) {
    console.error('Error saving task:', error)
    console.error('Error details:', error.response?.data)
    
    // Формируем детальное сообщение об ошибке
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

// Watch для загрузки задач при смене workspace
watch(() => workspaceStore.currentWorkspace?.id, (newWorkspaceId) => {
  if (newWorkspaceId) {
    loadTasks()
  }
}, { immediate: true })

// Следим за событиями обновления задач (например, после drag & drop)
watch(taskUpdatedEvent, () => {
  loadTasks()
})
</script>

