<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Расписание на день</h3>

      <!-- Action buttons -->
      <div v-if="temporaryPositions.size > 0" class="flex items-center gap-2">
        <!-- Reset button -->
        <button
          @click="resetChanges"
          :disabled="isSaving"
          class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-50 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-lg transition-colors"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span>Сбросить</span>
        </button>

        <!-- Save button -->
        <button
          @click="saveAllChanges"
          :disabled="isSaving"
          class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-400 text-white text-xs font-medium rounded-lg transition-colors"
        >
          <svg v-if="!isSaving" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ isSaving ? 'Сохранение...' : `Сохранить (${temporaryPositions.size})` }}</span>
        </button>
      </div>
    </div>

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
      <div
        ref="timelineContainer"
        class="relative mt-2"
        style="min-height: 120px;"
        :class="{ 'pointer-events-none opacity-60': isSaving }"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseUp"
      >
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
          class="absolute h-16 rounded-lg border-2 p-2 transition-all hover:shadow-md hover:z-10 select-none"
          :class="{
            'opacity-60': task.completed_at,
            'cursor-move': !task.completed_at && !isDragging,
            'cursor-grabbing': isDragging && dragState.taskId === task.id,
            'z-30': isDragging && dragState.taskId === task.id
          }"
          :style="{
            left: (isDragging && dragState.taskId === task.id ? dragState.left : getTaskPosition(task)) + '%',
            width: (isDragging && dragState.taskId === task.id ? dragState.width : getTaskWidth(task)) + '%',
            backgroundColor: task.completed_at ? '#e5e7eb' : (task.sphere_color + '20'),
            borderColor: task.completed_at ? '#9ca3af' : task.sphere_color,
            top: '0px'
          }"
          @mousedown="handleTaskMouseDown($event, task)"
          @click="handleTaskClick($event, task)"
        >
          <!-- Left resize handle -->
          <div
            v-if="!task.completed_at"
            class="absolute left-0 top-0 bottom-0 w-2 cursor-ew-resize hover:bg-black/10 rounded-l-lg"
            @mousedown.stop="handleResizeStart($event, task, 'left')"
          />

          <!-- Task content -->
          <div class="pointer-events-none">
            <div class="text-xs font-medium truncate" :style="{ color: task.completed_at ? '#6b7280' : task.sphere_color }">
              {{ task.title }}
            </div>
            <div class="text-[10px] text-gray-500 dark:text-gray-400">
              {{ formatTime(getDisplayTime(task, 'start')) }} -
              {{ formatTime(getDisplayTime(task, 'end')) }}
            </div>
          </div>

          <!-- Right resize handle -->
          <div
            v-if="!task.completed_at"
            class="absolute right-0 top-0 bottom-0 w-2 cursor-ew-resize hover:bg-black/10 rounded-r-lg"
            @mousedown.stop="handleResizeStart($event, task, 'right')"
          />
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
import { computed, ref, reactive, onUnmounted } from 'vue'
import { useTasksStore } from '@/stores/tasks'

const props = defineProps({
  tasks: { type: Array, default: () => [] },
})

const emit = defineEmits(['task-click', 'changes-saved'])

const tasksStore = useTasksStore()

// Full day: 24 hours from 0 to 23
const hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]
const totalHours = 24

const timelineContainer = ref(null)

// Drag state
const isDragging = ref(false)
const preventClick = ref(false) // Prevent click after drag
const dragState = reactive({
  taskId: null,
  type: null, // 'move', 'resize-left', 'resize-right'
  startX: 0,
  startLeft: 0,
  startWidth: 0,
  left: 0,
  width: 0,
  startTime: null,
  endTime: null,
  clickStartX: 0,
  clickStartY: 0,
})

// Manual save state
const isSaving = ref(false)
const temporaryPositions = ref(new Map()) // Store unsaved changes

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

const minutesToTime = (minutes) => {
  const h = Math.floor(minutes / 60) % 24
  const m = Math.floor(minutes % 60)
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}

const getTaskPosition = (task) => {
  // Use temporary position if saving
  const temp = temporaryPositions.value.get(task.id)
  const startTime = temp?.estimated_time || task.estimated_time

  const taskStartMinutes = timeToMinutes(startTime)
  const timelineWidthMinutes = totalHours * 60
  return (taskStartMinutes / timelineWidthMinutes) * 100
}

const getTaskWidth = (task) => {
  // Use temporary position if saving
  const temp = temporaryPositions.value.get(task.id)
  const startTime = temp?.estimated_time || task.estimated_time
  const endTime = temp?.end_time || task.end_time

  const startMinutes = timeToMinutes(startTime)
  const endMinutes = timeToMinutes(endTime)
  let duration = endMinutes - startMinutes
  if (duration < 0) duration += 24 * 60 // Task crosses midnight
  const timelineWidthMinutes = totalHours * 60
  return (duration / timelineWidthMinutes) * 100
}

const percentToMinutes = (percent) => {
  return Math.round((percent / 100) * totalHours * 60)
}

const getDisplayTime = (task, type) => {
  // If currently dragging this task, show drag state time
  if (isDragging.value && dragState.taskId === task.id) {
    return type === 'start' ? dragState.startTime : dragState.endTime
  }

  // If temporary position exists (saving), show temporary time
  const temp = temporaryPositions.value.get(task.id)
  if (temp) {
    return type === 'start' ? temp.estimated_time : temp.end_time
  }

  // Otherwise show original time
  return type === 'start' ? task.estimated_time : task.end_time
}

// Save all changes
const saveAllChanges = async () => {
  if (isSaving.value || temporaryPositions.value.size === 0) return

  isSaving.value = true
  const changes = Array.from(temporaryPositions.value.entries())

  try {
    // Save all changes in parallel
    await Promise.all(
      changes.map(([taskId, times]) =>
        tasksStore.updateTask(taskId, {
          estimated_time: times.estimated_time,
          end_time: times.end_time,
        })
      )
    )

    // Notify parent to refresh dashboard data with callback
    emit('changes-saved', () => {
      // This callback will be called when parent finishes refreshing
      temporaryPositions.value.clear()
      isSaving.value = false
    })
  } catch (error) {
    console.error('Failed to save task times:', error)
    isSaving.value = false
    // Keep temporary positions on error so user can retry
  }
}

// Reset all unsaved changes
const resetChanges = () => {
  temporaryPositions.value.clear()
}

// Start dragging/resizing
const handleResizeStart = (event, task, side) => {
  event.preventDefault()
  event.stopPropagation()

  if (task.completed_at || isSaving.value) return

  isDragging.value = true
  dragState.taskId = task.id
  dragState.type = side === 'left' ? 'resize-left' : 'resize-right'
  dragState.startX = event.clientX
  dragState.startLeft = getTaskPosition(task)
  dragState.startWidth = getTaskWidth(task)
  dragState.left = dragState.startLeft
  dragState.width = dragState.startWidth
  dragState.startTime = task.estimated_time
  dragState.endTime = task.end_time
  dragState.clickStartX = event.clientX
  dragState.clickStartY = event.clientY
}

const handleTaskMouseDown = (event, task) => {
  if (task.completed_at || isSaving.value) return

  // Check if clicking on resize handles
  const rect = event.currentTarget.getBoundingClientRect()
  const x = event.clientX - rect.left
  if (x < 8 || x > rect.width - 8) return

  isDragging.value = true
  dragState.taskId = task.id
  dragState.type = 'move'
  dragState.startX = event.clientX
  dragState.startLeft = getTaskPosition(task)
  dragState.startWidth = getTaskWidth(task)
  dragState.left = dragState.startLeft
  dragState.width = dragState.startWidth
  dragState.startTime = task.estimated_time
  dragState.endTime = task.end_time
  dragState.clickStartX = event.clientX
  dragState.clickStartY = event.clientY
}

const handleMouseMove = (event) => {
  if (!isDragging.value || !timelineContainer.value) return

  const containerRect = timelineContainer.value.getBoundingClientRect()
  const containerWidth = containerRect.width
  const deltaX = event.clientX - dragState.startX
  const deltaPercent = (deltaX / containerWidth) * 100

  if (dragState.type === 'move') {
    // Move the entire task
    let newLeft = dragState.startLeft + deltaPercent

    // Clamp to timeline bounds
    newLeft = Math.max(0, Math.min(100 - dragState.width, newLeft))

    dragState.left = newLeft

    // Calculate new times
    const startMinutes = percentToMinutes(newLeft)
    const endMinutes = startMinutes + percentToMinutes(dragState.width)

    dragState.startTime = minutesToTime(startMinutes)
    dragState.endTime = minutesToTime(endMinutes)

  } else if (dragState.type === 'resize-left') {
    // Resize from left edge
    let newLeft = dragState.startLeft + deltaPercent
    let newWidth = dragState.startWidth - deltaPercent

    // Minimum width: 15 minutes
    const minWidth = (15 / (totalHours * 60)) * 100

    if (newWidth < minWidth) {
      newWidth = minWidth
      newLeft = dragState.startLeft + dragState.startWidth - minWidth
    }

    // Clamp left to timeline bounds
    newLeft = Math.max(0, newLeft)

    dragState.left = newLeft
    dragState.width = newWidth

    // Calculate new times
    const startMinutes = percentToMinutes(newLeft)
    const endMinutes = percentToMinutes(newLeft + newWidth)

    dragState.startTime = minutesToTime(startMinutes)
    dragState.endTime = minutesToTime(endMinutes)

  } else if (dragState.type === 'resize-right') {
    // Resize from right edge
    let newWidth = dragState.startWidth + deltaPercent

    // Minimum width: 15 minutes
    const minWidth = (15 / (totalHours * 60)) * 100
    newWidth = Math.max(minWidth, newWidth)

    // Clamp to timeline bounds
    const maxWidth = 100 - dragState.left
    newWidth = Math.min(maxWidth, newWidth)

    dragState.width = newWidth

    // Calculate new times
    const startMinutes = percentToMinutes(dragState.left)
    const endMinutes = percentToMinutes(dragState.left + newWidth)

    dragState.startTime = minutesToTime(startMinutes)
    dragState.endTime = minutesToTime(endMinutes)
  }
}

const handleMouseUp = (event) => {
  if (!isDragging.value) return

  const task = props.tasks.find(t => t.id === dragState.taskId)
  if (!task) {
    isDragging.value = false
    return
  }

  // Check if this was actually a drag (moved more than 5px)
  const deltaX = Math.abs(event.clientX - dragState.clickStartX)
  const deltaY = Math.abs(event.clientY - dragState.clickStartY)
  const wasDragged = deltaX > 5 || deltaY > 5

  if (wasDragged) {
    // Prevent click event after drag
    preventClick.value = true
    setTimeout(() => {
      preventClick.value = false
    }, 100)

    // Store temporary position (unsaved change)
    temporaryPositions.value.set(task.id, {
      estimated_time: dragState.startTime,
      end_time: dragState.endTime,
    })
  }

  isDragging.value = false
  dragState.taskId = null
  dragState.type = null
}

const handleTaskClick = (_event, task) => {
  // Only emit click if not dragging and not preventing click
  if (!isDragging.value && !preventClick.value) {
    emit('task-click', task)
  }
}

// Cleanup on unmount
onUnmounted(() => {
  temporaryPositions.value.clear()
})
</script>
