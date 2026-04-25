<template>
  <TransitionGroup name="task" tag="div" class="space-y-2">
    <div
      v-for="task in tasks"
      :key="task.id"
      :data-task-id="task.id"
      draggable="true"
      class="touch-drag-item"
      @dragstart="handleDragStart($event, task)"
      @dragend="handleDragEnd"
      @touchstart="handleTouchStart($event, task)"
      @contextmenu.prevent
      :class="{ 'opacity-50': isDragging && draggedTask?.id === task.id }"
    >
      <TaskItem
        :task="task"
        :hide-project="hideProject"
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
import { ref, onUnmounted } from 'vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import TaskItem from '@/components/tasks/TaskItem.vue'

defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
  hideProject: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['task-click', 'toggle-complete', 'task-drag-start', 'task-drag-end', 'touch-drop'])

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

// Touch drag-and-drop for mobile
// IMPORTANT: use plain variables (not ref) to avoid Vue re-renders causing touchcancel
let touchDrag = null
let touchCloneEl = null
let lastHighlighted = null
let touchDragTask = null

const cleanupTouch = () => {
  if (touchCloneEl) {
    touchCloneEl.remove()
    touchCloneEl = null
  }
  if (lastHighlighted) {
    lastHighlighted.classList.remove('ring-2', 'ring-primary-400', 'ring-inset', 'bg-primary-100', 'dark:bg-primary-900/40')
    lastHighlighted = null
  }
  if (touchDrag) {
    const el = document.querySelector(`[data-task-id="${touchDrag.task.id}"]`)
    if (el) el.style.opacity = '1'
    clearTimeout(touchDrag.holdTimer)
  }
  touchDrag = null
  touchDragTask = null
  document.removeEventListener('touchmove', onDocTouchMove)
  document.removeEventListener('touchend', onDocTouchEnd)
}

const onDocTouchMove = (e) => {
  if (!touchDrag) return
  const touch = e.touches[0]

  if (!touchDrag.isDragging) {
    const dx = Math.abs(touch.clientX - touchDrag.startX)
    const dy = Math.abs(touch.clientY - touchDrag.startY)
    if (dx > 10 || dy > 10) {
      cleanupTouch()
      return
    }
    return
  }

  e.preventDefault()

  if (touchCloneEl) {
    touchCloneEl.style.left = `${touch.clientX}px`
    touchCloneEl.style.top = `${touch.clientY}px`
  }

  const el = document.elementFromPoint(touch.clientX, touch.clientY)
  const dropZone = el?.closest('[data-drop-date]')

  if (lastHighlighted && lastHighlighted !== dropZone) {
    lastHighlighted.classList.remove('ring-2', 'ring-primary-400', 'ring-inset', 'bg-primary-100', 'dark:bg-primary-900/40')
  }

  if (dropZone) {
    dropZone.classList.add('ring-2', 'ring-primary-400', 'ring-inset', 'bg-primary-100', 'dark:bg-primary-900/40')
    lastHighlighted = dropZone
  } else {
    lastHighlighted = null
  }
}

const onDocTouchEnd = () => {
  if (!touchDrag) return

  if (touchDrag.isDragging && touchDragTask && lastHighlighted) {
    const date = lastHighlighted.dataset.dropDate
    if (date) {
      emit('touch-drop', { task: touchDragTask, date })
    }
  }

  cleanupTouch()
  emit('task-drag-end')
}

const handleTouchStart = (e, task) => {
  // Cleanup any previous drag state (e.g. from touchcancel)
  if (touchDrag) cleanupTouch()

  const touch = e.touches[0]
  const taskEl = e.target.closest('[data-task-id]')

  touchDrag = {
    task,
    taskEl,
    startX: touch.clientX,
    startY: touch.clientY,
    isDragging: false,
    holdTimer: setTimeout(() => {
      if (!touchDrag) return
      touchDrag.isDragging = true
      touchDragTask = task

      if (taskEl) {
        const clone = taskEl.cloneNode(true)
        clone.style.cssText = `
          position: fixed; z-index: 9999; pointer-events: none;
          width: ${taskEl.offsetWidth}px; opacity: 0.85;
          transform: translate(-50%, -50%);
          left: ${touch.clientX}px; top: ${touch.clientY}px;
          background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `
        document.body.appendChild(clone)
        touchCloneEl = clone
        taskEl.style.opacity = '0.3'
      }

      emit('task-drag-start', task)
    }, 300),
  }

  document.addEventListener('touchmove', onDocTouchMove, { passive: false })
  document.addEventListener('touchend', onDocTouchEnd)
}

onUnmounted(() => {
  cleanupTouch()
})

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

.touch-drag-item {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  user-select: none;
}
</style>
