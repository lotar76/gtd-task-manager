<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-auto md:max-h-none max-h-[80vh] flex flex-col" @click.stop>

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Задача</h3>
              <div class="flex items-center space-x-2">
                <!-- Edit button -->
                <button
                  @click="$emit('enter-edit')"
                  class="p-2 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  title="Редактировать"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <!-- Close button -->
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Body (scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">

              <!-- Completed badge -->
              <div v-if="task.completed_at" class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Выполнено</span>
              </div>

              <!-- Task title -->
              <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                  {{ task.title }}
                </h2>
              </div>

              <!-- Properties badges -->
              <div class="flex flex-wrap items-center gap-2">

                <!-- Priority -->
                <span
                  v-if="task.priority && task.priority !== 'medium'"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400': task.priority === 'high',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': task.priority === 'low'
                  }"
                >
                  <span v-if="task.priority === 'high'" class="mr-1">🔥</span>
                  {{ task.priority === 'high' ? 'Срочно' : 'Низкий' }}
                </span>

                <!-- Status -->
                <span
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                  :class="statusBadgeClass(task.status)"
                >
                  <component :is="statusIcon(task.status)" class="w-3.5 h-3.5 mr-1" />
                  {{ statusLabel(task.status) }}
                </span>

                <!-- Due date -->
                <span v-if="task.due_date" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ formatDate(task.due_date) }}
                </span>

                <!-- Time range -->
                <span v-if="task.estimated_time || task.end_time" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                <!-- Assignee -->
                <span v-if="task.assignee" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ task.assignee.name }}
                </span>

                <!-- Context -->
                <span
                  v-if="task.context"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                  :style="{ backgroundColor: task.context.color + '20', color: task.context.color }"
                >
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ task.context.name }}
                </span>

                <!-- Tags -->
                <span
                  v-for="tag in task.tags"
                  :key="tag.id"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                  :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                >
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                  {{ tag.name }}
                </span>
              </div>

              <!-- Description -->
              <div v-if="task.description" class="mt-6">
                <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                  {{ task.description }}
                </p>
              </div>

              <!-- Workspace/Project -->
              <div v-if="task.workspace || task.project" class="flex flex-wrap items-center gap-2 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                <!-- Workspace -->
                <span
                  v-if="task.workspace"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400"
                >
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  {{ task.workspace.name }}
                </span>

                <!-- Project -->
                <span
                  v-if="task.project"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                >
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                  {{ task.project.name }}
                </span>
              </div>

            </div>

            <!-- Footer with button -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
              <!-- Кнопка для активных задач -->
              <button
                v-if="!task.completed_at"
                @click="handleComplete"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 shadow-sm flex items-center justify-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Завершить задачу</span>
              </button>

              <!-- Кнопка для завершённых задач -->
              <button
                v-else
                @click="handleUncomplete"
                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 shadow-sm flex items-center justify-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Вернуть в работу</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import {
  InboxIcon,
  CalendarIcon,
  BoltIcon,
  ClockIcon,
  ArchiveBoxIcon,
  CalendarDaysIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: Boolean,
  task: Object
})

const emit = defineEmits(['close', 'enter-edit', 'complete-task', 'uncomplete-task'])

// Handle complete button
const handleComplete = () => {
  emit('complete-task', props.task)
  emit('close')
}

// Handle uncomplete button
const handleUncomplete = () => {
  emit('uncomplete-task', props.task)
  emit('close')
}

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

// Format time
const formatTime = (time) => {
  if (!time) return ''
  // Если время уже в формате HH:mm, возвращаем как есть
  if (/^\d{2}:\d{2}$/.test(time)) return time
  // Иначе извлекаем HH:mm из формата HH:mm:ss
  return time.substring(0, 5)
}

// Status icon mapping
const statusIcon = (status) => {
  const icons = {
    inbox: InboxIcon,
    today: CalendarIcon,
    next_action: BoltIcon,
    waiting: ClockIcon,
    someday: ArchiveBoxIcon,
    tomorrow: CalendarDaysIcon,
    scheduled: CalendarDaysIcon
  }
  return icons[status] || InboxIcon
}

// Status label mapping
const statusLabel = (status) => {
  const labels = {
    inbox: 'Входящие',
    today: 'Сегодня',
    next_action: 'Следующие',
    waiting: 'Ожидание',
    someday: 'Когда-нибудь',
    tomorrow: 'Завтра',
    scheduled: 'Запланировано',
    completed: 'Выполнено'
  }
  return labels[status] || status
}

// Status badge class mapping
const statusBadgeClass = (status) => {
  const classes = {
    today: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    next_action: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    tomorrow: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
    scheduled: 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-400',
    waiting: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
    someday: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    inbox: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
