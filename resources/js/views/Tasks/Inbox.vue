<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">Входящие</h1>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <template v-else>
        <TaskList
          v-if="tasks.length > 0"
          :tasks="tasks"
          @task-click="handleTaskClick"
          @toggle-complete="handleToggleComplete"
        />

        <!-- GTD Empty State -->
        <div v-else class="text-center py-12">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
              <InboxIcon class="w-8 h-8 text-blue-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Входящие пусты</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
              Входящие — это ваша корзина для сбора. Записывайте сюда все задачи, идеи и мысли,
              которые приходят в голову. Не оценивайте и не сортируйте — просто фиксируйте.
              Обработаете и распределите по нужным спискам позже.
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
import TaskList from '@/components/tasks/TaskList.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import { InboxIcon, PlusIcon } from '@heroicons/vue/24/outline'

const tasksStore = useTasksStore()

const tasks = computed(() => tasksStore.inboxTasks)
const loading = computed(() => tasksStore.loading)
const showTaskModal = ref(false)
const showTaskView = ref(false)
const selectedTask = ref(null)
const taskError = ref('')

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
