<template>
  <div
    @drop="handleDrop"
    @dragover.prevent="handleDragOver"
    @dragleave="handleDragLeave"
    :class="[
      'rounded-lg transition-all',
      isDragOver ? 'bg-primary-100 dark:bg-primary-900/40 ring-2 ring-primary-500' : ''
    ]"
  >
    <router-link
      :to="computedTo"
      @click="handleLinkClick"
      class="flex items-center justify-between px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
      :class="isActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
    >
      <div class="flex items-center space-x-2.5">
        <component :is="iconComponent" class="w-5 h-5" />
        <span><slot /></span>
      </div>
      <span
        v-if="count !== null && count > 0"
        class="px-2 py-0.5 text-xs font-semibold rounded-full"
        :class="isActive ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300'"
      >
        {{ count }}
      </span>
    </router-link>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import {
  InboxIcon,
  BoltIcon,
  CalendarIcon,
  ClockIcon,
  ArchiveBoxIcon,
  FolderIcon,
  CalendarDaysIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  to: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    default: 'folder',
  },
  count: {
    type: Number,
    default: null,
  },
  dropStatus: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['task-dropped', 'close-sidebar'])

const route = useRoute()
const workspaceStore = useWorkspaceStore()
const isDragOver = ref(false)

const handleLinkClick = () => {
  // Закрываем сайдбар только на мобилке (lg breakpoint = 1024px)
  if (window.innerWidth < 1024) {
    emit('close-sidebar')
  }
}

const iconMap = {
  inbox: InboxIcon,
  lightning: BoltIcon,
  calendar: CalendarIcon,
  clock: ClockIcon,
  archive: ArchiveBoxIcon,
  folder: FolderIcon,
  'calendar-days': CalendarDaysIcon,
  target: FolderIcon,
}

const iconComponent = computed(() => iconMap[props.icon] || FolderIcon)

const computedTo = computed(() => {
  const workspaceId = workspaceStore.currentWorkspace?.id
  return props.to.replace(':id', workspaceId || '1')
})

const isActive = computed(() => {
  return route.path === computedTo.value
})

const handleDragOver = (event) => {
  event.preventDefault()
  isDragOver.value = true
  event.dataTransfer.dropEffect = 'move'
}

const handleDragLeave = () => {
  isDragOver.value = false
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  
  try {
    const taskData = JSON.parse(event.dataTransfer.getData('application/json'))
    
    if (props.dropStatus && taskData) {
      emit('task-dropped', { taskId: taskData.id, newStatus: props.dropStatus })
    }
  } catch (error) {
    console.error('Error handling drop:', error)
  }
}
</script>

