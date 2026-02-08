<template>
  <TransitionGroup name="task" tag="div" class="space-y-2">
    <div
      v-for="task in tasks"
      :key="task.id"
      draggable="true"
      @dragstart="handleDragStart($event, task)"
      @dragend="handleDragEnd"
      class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-sm transition-all cursor-move relative overflow-hidden"
      :class="[
        { 'opacity-50': isDragging && draggedTask?.id === task.id },
        getDurationGradientClass(task)
      ]"
      @click="$emit('task-click', task)"
    >
      <div class="flex items-start space-x-3">
        <!-- Checkbox -->
        <button
          @click.stop="$emit('toggle-complete', task)"
          class="mt-1 flex-shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center transition-colors duration-200"
          :class="task.status === 'completed'
            ? 'bg-green-500 border-green-500'
            : 'border-gray-300 hover:border-primary-500'"
        >
          <svg
            v-if="task.status === 'completed'"
            class="w-3 h-3 text-white"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
        </button>

        <!-- Task Content -->
        <div class="flex-1 min-w-0">
          <h3
            class="text-sm font-medium transition-all duration-200"
            :class="task.status === 'completed'
              ? 'text-gray-400 line-through'
              : 'text-gray-900 dark:text-white'"
          >
            {{ task.title }}
          </h3>

          <p v-if="task.description" class="text-sm text-gray-500 mt-1 truncate">
            {{ task.description }}
          </p>

          <!-- Meta информация -->
          <div class="flex flex-wrap items-center gap-2 mt-2">
            <!-- Workspace -->
            <span
              v-if="task.workspace"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
            >
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              {{ task.workspace.name }}
            </span>

            <!-- Проект -->
            <span
              v-if="task.project"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
              :style="{ backgroundColor: task.project.color + '20', color: task.project.color }"
            >
              {{ task.project.name }}
            </span>

            <!-- Дата -->
            <span
              v-if="task.due_date"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
              :class="getDueDateClass(task)"
            >
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ formatDate(task.due_date) }}
            </span>

            <!-- Время начала и окончания -->
            <span
              v-if="task.estimated_time || task.end_time"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700"
            >
              <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span v-if="task.estimated_time && task.end_time">
                {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
              </span>
              <span v-else-if="task.estimated_time">
                {{ formatTime(task.estimated_time) }}
              </span>
              <span v-else-if="task.end_time">
                до {{ formatTime(task.end_time) }}
              </span>
            </span>

            <!-- Назначен -->
            <span v-if="task.assignee" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
              {{ task.assignee.name }}
            </span>

            <!-- Контекст -->
            <span
              v-if="task.context"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
              :style="{ backgroundColor: task.context.color + '20', color: task.context.color }"
            >
              {{ task.context.name }}
            </span>

            <!-- Теги -->
            <span
              v-for="tag in task.tags"
              :key="tag.id"
              class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
              :style="{ backgroundColor: tag.color + '20', color: tag.color }"
            >
              {{ tag.name }}
            </span>
          </div>
        </div>

        <!-- Priority Badge -->
        <div
          v-if="task.priority !== 'medium'"
          class="flex-shrink-0 w-2 h-2 rounded-full mt-2"
          :class="{
            'bg-red-500': task.priority === 'urgent',
            'bg-orange-500': task.priority === 'high',
            'bg-blue-500': task.priority === 'low'
          }"
        />
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
</template>

<script setup>
import { ref } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)
dayjs.locale('ru')

defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['task-click', 'toggle-complete', 'task-drag-start', 'task-drag-end'])

const isDragging = ref(false)
const draggedTask = ref(null)

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
  if (task.status === 'completed') {
    return 'bg-gray-100 text-gray-600'
  }

  const dueDate = dayjs(task.due_date)
  const today = dayjs()

  if (dueDate.isBefore(today, 'day')) {
    return 'bg-red-100 text-red-700'
  } else if (dueDate.isSame(today, 'day')) {
    return 'bg-orange-100 text-orange-700'
  }

  return 'bg-gray-100 text-gray-600'
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
  if (task.status === 'completed') {
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
