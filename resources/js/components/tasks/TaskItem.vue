<template>
  <div
    class="group cursor-pointer transition-colors"
    :class="[
      compact ? 'px-1.5 py-1 rounded text-xs' : 'border rounded-lg px-3 py-2.5',
      compact ? '' : roleRowClass,
    ]"
    @click="$emit('task-click', task)"
  >
    <div class="flex items-start" :class="compact ? '' : 'gap-2.5'">
      <!-- Checkbox (скрыт для наблюдателя и в compact режиме) -->
      <template v-if="!compact">
        <button
          v-if="role !== 'watcher'"
          @click.stop="$emit('toggle-complete', task)"
          class="flex-shrink-0 mt-0.5 w-4 h-4 rounded border flex items-center justify-center transition-colors"
          :class="[
            task.completed_at
              ? 'bg-emerald-500 border-emerald-500'
              : 'border-gray-300 dark:border-gray-600 hover:border-emerald-500'
          ]"
        >
          <svg v-if="task.completed_at" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
        </button>
        <div v-else class="flex-shrink-0 mt-0.5 w-4 h-4 flex items-center justify-center text-gray-300 dark:text-gray-600" title="Только наблюдение">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z" /></svg>
        </div>
      </template>

      <div class="flex-1 min-w-0">
        <!-- Title row -->
        <div class="flex items-baseline justify-between" :class="compact ? 'gap-1' : 'gap-3'">
          <div class="flex items-center gap-1.5 min-w-0">
            <span
              v-if="priorityMeta && task.priority !== 'medium'"
              class="rounded-full flex-shrink-0"
              :class="[priorityMeta.dot, compact ? 'w-1 h-1' : 'w-1.5 h-1.5']"
              :title="priorityMeta.label"
            ></span>
            <h3
              class="truncate"
              :class="[
                compact ? 'text-[11px] font-medium' : 'text-[13.5px]',
                task.completed_at ? 'text-gray-400 line-through' : compact ? '' : 'text-gray-800 dark:text-gray-100'
              ]"
            >
              {{ task.title }}
            </h3>
          </div>
          <span v-if="!compact && task.creator?.name" class="inline-flex items-center gap-1 text-gray-400 flex-shrink-0 truncate text-[11px] max-w-[160px]">
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            {{ task.creator.name }}
          </span>
        </div>
        <!-- Creator under title (compact only) -->
        <div v-if="compact && task.creator?.name" class="truncate text-[9px] text-gray-400 mt-0.5">
          {{ task.creator.name }}
        </div>

        <!-- Meta -->
        <div
          v-if="compact"
          class="flex flex-wrap gap-0.5 mt-0.5"
        >
          <span v-if="task.estimated_time || task.end_time" class="text-[9px] opacity-75">
            <template v-if="task.estimated_time && task.end_time">{{ formatTime(task.estimated_time) }}–{{ formatTime(task.end_time) }}</template>
            <template v-else-if="task.estimated_time">{{ formatTime(task.estimated_time) }}</template>
            <template v-else>до {{ formatTime(task.end_time) }}</template>
          </span>
          <span v-if="task.project" class="inline-block px-0.5 rounded text-[8px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ task.project.name }}</span>
          <span v-for="name in assigneeNames" :key="name" class="inline-block px-0.5 rounded text-[8px] font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ name }}</span>
          <span v-if="task.context" class="inline-block px-0.5 rounded text-[8px] font-medium" :style="{ backgroundColor: task.context.color + '20', color: task.context.color }">{{ task.context.name }}</span>
          <span v-for="tag in task.tags" :key="tag.id" class="inline-block px-0.5 rounded text-[8px] font-medium" :style="{ backgroundColor: tag.color + '20', color: tag.color }">{{ tag.name }}</span>
        </div>
        <div
          v-else
          class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11.5px] text-gray-500 dark:text-gray-400"
        >
          <span v-if="task.due_date" class="inline-flex items-center gap-1" :class="dueDateClass">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            {{ formattedDate }}
          </span>

          <span v-if="task.estimated_time || task.end_time" class="inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <template v-if="task.estimated_time && task.end_time">{{ formatTime(task.estimated_time) }}–{{ formatTime(task.end_time) }}</template>
            <template v-else-if="task.estimated_time">{{ formatTime(task.estimated_time) }}</template>
            <template v-else>до {{ formatTime(task.end_time) }}</template>
          </span>

          <span v-if="task.project" class="inline-flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6v12M9 4v16M14 8v8M19 5v14" /></svg>
            {{ task.project.name }}
          </span>

          <span v-if="assigneeNames.length" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-300" title="Исполнители">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            {{ assigneeNames.join(', ') }}
          </span>

          <span v-if="watcherNames.length" class="inline-flex items-center gap-1" title="Наблюдатели">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z" /></svg>
            {{ watcherNames.join(', ') }}
          </span>

          <span v-if="task.context" class="inline-flex items-center gap-1" :style="{ color: task.context.color }">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            {{ task.context.name }}
          </span>

          <span v-for="tag in task.tags" :key="tag.id" class="inline-flex items-center gap-1" :style="{ color: tag.color }">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
            {{ tag.name }}
          </span>

          <span v-if="task.attachments?.length" class="inline-flex items-center gap-1" title="Вложения">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
            {{ task.attachments.length }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
import relativeTime from 'dayjs/plugin/relativeTime'
import { useAuthStore } from '@/stores/auth'

dayjs.extend(relativeTime)
dayjs.locale('ru')

const props = defineProps({
  task: {
    type: Object,
    required: true,
  },
  compact: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['task-click', 'toggle-complete'])

const authStore = useAuthStore()

const role = computed(() => {
  const uid = authStore.user?.id
  if (!uid) return 'owner'
  const myLink = (props.task.contacts || []).find(c => c.contact_user_id === uid)
  if (myLink) {
    if (myLink.pivot?.role === 'assignee') return 'assignee'
    return 'watcher'
  }
  return 'owner'
})

const roleRowClass = computed(() => {
  const r = role.value
  if (r === 'watcher') return 'bg-gray-100/60 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700 hover:bg-gray-200/50 dark:hover:bg-gray-700/40'
  if (r === 'assignee') return 'bg-indigo-50/40 dark:bg-indigo-900/10 border-indigo-200 dark:border-indigo-900/40 hover:bg-indigo-100/60 dark:hover:bg-indigo-900/20'
  return 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50'
})

const participants = (role) => {
  return (props.task.contacts || [])
    .filter(c => c.pivot?.role === role)
    .map(c => c.name)
}
const assigneeNames = computed(() => participants('assignee'))
const watcherNames = computed(() => participants('watcher'))

const priorityMap = {
  low:    { label: 'Низкий',  dot: 'bg-gray-400' },
  medium: { label: 'Средний', dot: 'bg-blue-400' },
  high:   { label: 'Высокий', dot: 'bg-amber-500' },
  urgent: { label: 'Срочный', dot: 'bg-red-500' },
}
const priorityMeta = computed(() => priorityMap[props.task.priority] || null)

const formatTime = (time) => {
  if (!time) return ''
  if (/^\d{2}:\d{2}$/.test(time)) return time
  return time.substring(0, 5)
}

const formattedDate = computed(() => {
  if (!props.task.due_date) return ''
  const d = dayjs(props.task.due_date)
  const today = dayjs()
  if (d.isSame(today, 'day')) return 'Сегодня'
  if (d.isSame(today.add(1, 'day'), 'day')) return 'Завтра'
  if (d.isBefore(today)) return d.fromNow()
  return d.format('D MMM')
})

const dueDateClass = computed(() => {
  if (props.task.completed_at) return 'text-gray-400'
  const dueDate = dayjs(props.task.due_date)
  const today = dayjs()
  if (dueDate.isBefore(today, 'day')) return 'text-red-500 dark:text-red-400'
  if (dueDate.isSame(today, 'day')) return 'text-amber-600 dark:text-amber-400'
  return 'text-gray-500 dark:text-gray-400'
})
</script>
