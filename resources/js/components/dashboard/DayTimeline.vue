<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Расписание на день</h3>

    <!-- Timeline container -->
    <div class="relative">
      <!-- Hour markers -->
      <div class="flex border-b border-gray-200 dark:border-gray-700">
        <div
          v-for="hour in hours"
          :key="hour"
          class="flex-1 text-center pb-2 text-xs text-gray-500 dark:text-gray-400"
        >
          {{ formatHour(hour) }}
        </div>
      </div>

      <!-- Tasks timeline -->
      <div class="relative mt-2" style="min-height: 120px;">
        <!-- Grid lines -->
        <div class="absolute inset-0 flex">
          <div
            v-for="hour in hours"
            :key="'grid-' + hour"
            class="flex-1 border-r border-gray-100 dark:border-gray-700"
          />
        </div>

        <!-- Current time line -->
        <div
          class="absolute top-0 bottom-0 w-px bg-red-500 z-20"
          :style="{ left: currentTimePosition + '%' }"
        />

        <!-- Tasks -->
        <div
          v-for="task in tasksWithTime"
          :key="task.id"
          class="absolute h-16 rounded-lg border-2 p-2 cursor-pointer transition-all hover:shadow-md hover:z-10"
          :class="{ 'opacity-60': task.completed_at }"
          :style="{
            left: getTaskPosition(task) + '%',
            width: getTaskWidth(task) + '%',
            backgroundColor: task.completed_at ? '#e5e7eb' : (task.sphere_color + '20'),
            borderColor: task.completed_at ? '#9ca3af' : task.sphere_color,
            top: '0px'
          }"
          @click="$emit('task-click', task)"
        >
          <div class="text-xs font-medium truncate" :style="{ color: task.completed_at ? '#6b7280' : task.sphere_color }">
            {{ task.title }}
          </div>
          <div class="text-[10px] text-gray-500 dark:text-gray-400">
            {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
          </div>
        </div>
      </div>

      <!-- Tasks without time -->
      <div v-if="tasksWithoutTime.length" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Без времени:</div>
        <div class="flex flex-wrap gap-2">
          <div
            v-for="task in tasksWithoutTime"
            :key="task.id"
            class="px-3 py-1.5 rounded-lg text-xs font-medium cursor-pointer hover:shadow-sm transition-all"
            :class="{ 'opacity-60': task.completed_at }"
            :style="{
              backgroundColor: task.completed_at ? '#e5e7eb' : (task.sphere_color + '20'),
              color: task.completed_at ? '#6b7280' : task.sphere_color
            }"
            @click="$emit('task-click', task)"
          >
            {{ task.title }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tasks: { type: Array, default: () => [] },
})

defineEmits(['task-click'])

// Full day: 24 hours from 0 to 23
const hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]
const startHour = 0
const endHour = 24
const totalHours = 24

const tasksWithTime = computed(() => {
  return props.tasks.filter(t => t.estimated_time && t.end_time)
})

const tasksWithoutTime = computed(() => {
  return props.tasks.filter(t => !t.estimated_time || !t.end_time)
})

// Current time position
const currentTimePosition = computed(() => {
  const now = new Date()
  const currentMinutes = now.getHours() * 60 + now.getMinutes()
  const timelineWidthMinutes = totalHours * 60
  return (currentMinutes / timelineWidthMinutes) * 100
})

const formatHour = (hour) => {
  return `${hour}:00`
}

const formatTime = (time) => {
  if (!time) return ''
  if (/^\d{2}:\d{2}$/.test(time)) return time
  return time.substring(0, 5)
}

const timeToMinutes = (time) => {
  const [hours, minutes] = formatTime(time).split(':').map(Number)
  return hours * 60 + minutes
}

const getTaskPosition = (task) => {
  const taskStartMinutes = timeToMinutes(task.estimated_time)
  const timelineWidthMinutes = totalHours * 60

  return (taskStartMinutes / timelineWidthMinutes) * 100
}

const getTaskWidth = (task) => {
  const startMinutes = timeToMinutes(task.estimated_time)
  const endMinutes = timeToMinutes(task.end_time)

  let duration = endMinutes - startMinutes
  if (duration < 0) duration += 24 * 60 // Task crosses midnight

  const timelineWidthMinutes = totalHours * 60
  return (duration / timelineWidthMinutes) * 100
}
</script>
