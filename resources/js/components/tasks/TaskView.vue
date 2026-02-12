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
              <div v-if="task.status === 'completed'" class="flex items-center space-x-2 text-green-600 dark:text-green-400">
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

                <!-- Estimated time -->
                <span v-if="task.estimated_time" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ task.estimated_time }}
                </span>
              </div>

              <!-- Description -->
              <div v-if="task.description" class="mt-6">
                <p class="text-base text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                  {{ task.description }}
                </p>
              </div>

              <!-- Workspace/Project -->
              <div v-if="task.workspace_name || task.project_name" class="flex items-center gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
                <span v-if="task.workspace_name" class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                  {{ task.workspace_name }}
                </span>
                <span v-if="task.project_name" class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                  {{ task.project_name }}
                </span>
              </div>

            </div>

            <!-- Footer with button (only mobile) -->
            <div v-if="task.status !== 'completed'" class="md:hidden border-t border-gray-200 dark:border-gray-700 p-4">
              <button
                @click="handleComplete"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 shadow-sm flex items-center justify-center space-x-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Выполнено</span>
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

const emit = defineEmits(['close', 'enter-edit', 'complete-task'])

// Handle complete button
const handleComplete = () => {
  emit('complete-task', props.task)
  emit('close')
}

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
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
