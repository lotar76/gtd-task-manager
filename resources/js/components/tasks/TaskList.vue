<template>
  <TransitionGroup name="task" tag="div" class="space-y-2">
    <div
      v-for="task in tasks"
      :key="task.id"
      draggable="true"
      @dragstart="handleDragStart($event, task)"
      @dragend="handleDragEnd"
      class="group border rounded-lg px-3 py-2.5 cursor-pointer transition-colors"
      :class="[
        { 'opacity-50': isDragging && draggedTask?.id === task.id },
        roleRowClass(task),
      ]"
      @click="$emit('task-click', task)"
    >
      <div class="flex items-start gap-2.5">
        <!-- Checkbox (скрыт для наблюдателя чужой задачи) -->
        <button
          v-if="viewerRole(task) !== 'watcher'"
          @click.stop="handleToggleComplete(task)"
          class="mt-0.5 flex-shrink-0 w-4 h-4 rounded border flex items-center justify-center transition-colors"
          :class="task.completed_at
            ? 'bg-emerald-500 border-emerald-500'
            : 'border-gray-300 dark:border-gray-600 hover:border-emerald-500'"
        >
          <svg v-if="task.completed_at" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
        </button>
        <div v-else class="mt-0.5 flex-shrink-0 w-4 h-4 flex items-center justify-center text-gray-300 dark:text-gray-600" title="Только наблюдение">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z" /></svg>
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-baseline justify-between gap-3">
            <div class="flex items-center gap-1.5 min-w-0">
              <span
                v-if="priorityMeta(task.priority) && task.priority !== 'medium'"
                class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                :class="priorityMeta(task.priority).dot"
                :title="priorityMeta(task.priority).label"
              ></span>
              <h3
                class="text-[13.5px] truncate"
                :class="task.completed_at ? 'text-gray-400 line-through' : 'text-gray-800 dark:text-gray-100'"
              >
                {{ task.title }}
              </h3>
            </div>
            <span v-if="task.creator?.name" class="inline-flex items-center gap-1 text-[11px] text-gray-400 flex-shrink-0 truncate max-w-[160px]">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              {{ task.creator.name }}
            </span>
          </div>

          <!-- Meta -->
          <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11.5px] text-gray-500 dark:text-gray-400">
            <span v-if="task.due_date" class="inline-flex items-center gap-1" :class="getDueDateClass(task)">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              {{ formatDate(task.due_date) }}
            </span>

            <span v-if="task.estimated_time || task.end_time" class="inline-flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <template v-if="task.estimated_time && task.end_time">{{ formatTime(task.estimated_time) }}–{{ formatTime(task.end_time) }}</template>
              <template v-else-if="task.estimated_time">{{ formatTime(task.estimated_time) }}</template>
              <template v-else>до {{ formatTime(task.end_time) }}</template>
            </span>

            <span v-if="task.project" class="inline-flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
              {{ task.project.name }}
            </span>

            <span v-if="assigneeNames(task).length" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-300" title="Исполнители">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              {{ assigneeNames(task).join(', ') }}
            </span>

            <span v-if="watcherNames(task).length" class="inline-flex items-center gap-1" title="Наблюдатели">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z" /></svg>
              {{ watcherNames(task).join(', ') }}
            </span>

            <span v-if="task.context" class="inline-flex items-center gap-1" :style="{ color: task.context.color }">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              {{ task.context.name }}
            </span>

            <span v-for="tag in task.tags" :key="tag.id" class="inline-flex items-center gap-1" :style="{ color: tag.color }">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
              {{ tag.name }}
            </span>
          </div>
        </div>
      </div>
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
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
import relativeTime from 'dayjs/plugin/relativeTime'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'

dayjs.extend(relativeTime)
dayjs.locale('ru')

const authStore = useAuthStore()
const workspaceStore = useWorkspaceStore()

defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
})

const viewerRole = (task) => {
  const uid = authStore.user?.id
  if (!uid) return 'owner'
  // я в числе контактов задачи?
  const myLink = (task.contacts || []).find(c => c.contact_user_id === uid)
  if (myLink) {
    if (myLink.pivot?.role === 'assignee') return 'assignee'
    return 'watcher'
  }
  return 'owner'
}
const roleRowClass = (task) => {
  const r = viewerRole(task)
  if (r === 'watcher') return 'bg-gray-100/60 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700 hover:bg-gray-200/50 dark:hover:bg-gray-700/40'
  if (r === 'assignee') return 'bg-indigo-50/40 dark:bg-indigo-900/10 border-indigo-200 dark:border-indigo-900/40 hover:bg-indigo-100/60 dark:hover:bg-indigo-900/20'
  return 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50'
}
const participants = (task, role) => {
  return (task.contacts || [])
    .filter(c => c.pivot?.role === role)
    .map(c => c.name)
}
const assigneeNames = (task) => participants(task, 'assignee')
const watcherNames = (task) => participants(task, 'watcher')

const priorityMeta = (p) => ({
  low:    { label: 'Низкий',  cls: 'text-gray-600 dark:text-gray-300',     dot: 'bg-gray-400' },
  medium: { label: 'Средний', cls: 'text-blue-700 dark:text-blue-300',     dot: 'bg-blue-400' },
  high:   { label: 'Высокий', cls: 'text-amber-700 dark:text-amber-300',   dot: 'bg-amber-500' },
  urgent: { label: 'Срочный', cls: 'text-red-700 dark:text-red-300',       dot: 'bg-red-500' },
}[p] || null)

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

  // Устанавливаем данные для переноса
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

const formatDate = (date) => {
  const d = dayjs(date)
  const today = dayjs()

  if (d.isSame(today, 'day')) {
    return 'Сегодня'
  } else if (d.isSame(today.add(1, 'day'), 'day')) {
    return 'Завтра'
  } else if (d.isBefore(today)) {
    return d.fromNow()
  }

  return d.format('D MMM')
}

const formatTime = (time) => {
  if (!time) return ''
  // Если время уже в формате HH:mm, возвращаем как есть
  if (/^\d{2}:\d{2}$/.test(time)) return time
  // Иначе извлекаем HH:mm из формата HH:mm:ss
  return time.substring(0, 5)
}

const getDueDateClass = (task) => {
  if (task.completed_at) return 'text-gray-400'
  const dueDate = dayjs(task.due_date)
  const today = dayjs()
  if (dueDate.isBefore(today, 'day')) return 'text-red-500 dark:text-red-400'
  if (dueDate.isSame(today, 'day')) return 'text-amber-600 dark:text-amber-400'
  return 'text-gray-500 dark:text-gray-400'
}

// Вычисление продолжительности задачи в часах
const getTaskDuration = (task) => {
  if (!task.estimated_time || !task.end_time) {
    return 0 // Без времени
  }

  const startTime = formatTime(task.estimated_time)
  const endTime = formatTime(task.end_time)

  if (!startTime || !endTime) {
    return 0
  }

  // Парсим время в формате HH:mm
  const [startHours, startMinutes] = startTime.split(':').map(Number)
  const [endHours, endMinutes] = endTime.split(':').map(Number)

  // Вычисляем разницу в минутах
  const startTotalMinutes = startHours * 60 + startMinutes
  const endTotalMinutes = endHours * 60 + endMinutes

  // Если end_time меньше start_time, значит задача переходит на следующий день
  let diffMinutes = endTotalMinutes - startTotalMinutes
  if (diffMinutes < 0) {
    diffMinutes += 24 * 60 // Добавляем 24 часа
  }

  // Конвертируем в часы (с десятичными долями)
  return diffMinutes / 60
}

// Получение класса градиента в зависимости от продолжительности
const getDurationGradientClass = (task) => {
  if (task.completed_at) {
    return 'bg-white dark:bg-gray-800'
  }

  const duration = getTaskDuration(task)

  if (duration === 0 || duration < 1) {
    return 'bg-gradient-to-l from-white via-green-50 to-green-100 dark:from-gray-800 dark:via-green-900/20 dark:to-green-900/40'
  }

  if (duration >= 1 && duration < 2) {
    return 'bg-gradient-to-l from-white via-orange-50 to-orange-100 dark:from-gray-800 dark:via-orange-900/20 dark:to-orange-900/40'
  }

  if (duration >= 2) {
    return 'bg-gradient-to-l from-white via-red-50 to-red-100 dark:from-gray-800 dark:via-red-900/20 dark:to-red-900/40'
  }

  return 'bg-white dark:bg-gray-800'
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
