<template>
  <TransitionGroup name="task" tag="div" class="space-y-2">
    <div
      v-for="task in tasks"
      :key="task.id"
      draggable="true"
      @dragstart="handleDragStart($event, task)"
      @dragend="handleDragEnd"
      :class="{ 'opacity-50': isDragging && draggedTask?.id === task.id }"
    >
      <TaskItem
        :task="task"
        @task-click="$emit('task-click', task)"
        @toggle-complete="handleToggleComplete"
      />
    </div>
  </TransitionGroup>

  <!-- Empty State -->
  <div v-if="!tasks.length" class="text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">Нет задач</h3>
    <p class="mt-1 text-sm text-gray-500">Создайте первую задачу чтобы начать</p>
  </div>

  <!-- Confirm Dialog -->
  <ConfirmDialog
    :show="showConfirm"
    :title="confirmTitle"
    :message="confirmMessage"
    confirm-text="Завершить"
    cancel-text="Отмена"
    variant="success"
    @confirm="confirmComplete"
    @cancel="cancelComplete"
  />
</template>

<script setup>
import { ref } from 'vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import TaskItem from '@/components/tasks/TaskItem.vue'

defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['task-click', 'toggle-complete', 'task-drag-start', 'task-drag-end'])

const isDragging = ref(false)
const draggedTask = ref(null)
const showConfirm = ref(false)
const taskToComplete = ref(null)
const confirmTitle = ref('Завершить задачу?')
const confirmMessage = ref('')

const handleDragStart = (event, task) => {
  isDragging.value = true
  draggedTask.value = task

  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('application/json', JSON.stringify(task))

  emit('task-drag-start', task)
}

const handleDragEnd = () => {
  isDragging.value = false
  draggedTask.value = null
  emit('task-drag-end')
}

const handleToggleComplete = (task) => {
  // Если задача уже завершена, сразу отменяем завершение без подтверждения
  if (task.completed_at) {
    emit('toggle-complete', task)
    return
  }

  // Если задача не завершена, показываем подтверждение
  taskToComplete.value = task
  confirmMessage.value = `Вы уверены, что хотите завершить задачу "${task.title}"?`
  showConfirm.value = true
}

const confirmComplete = () => {
  if (taskToComplete.value) {
    emit('toggle-complete', taskToComplete.value)
  }
  showConfirm.value = false
  taskToComplete.value = null
}

const cancelComplete = () => {
  showConfirm.value = false
  taskToComplete.value = null
}
</script>

<style scoped>
.task-leave-active {
  transition: all 0.4s ease;
}
.task-leave-to {
  opacity: 0;
  transform: translateX(30px);
  max-height: 0;
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
  overflow: hidden;
}

.task-move {
  transition: transform 0.3s ease;
}
</style>
