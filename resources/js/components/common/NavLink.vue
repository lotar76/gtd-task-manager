<template>
  <router-link
    :to="computedTo"
    class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-medium transition-colors"
    :class="isActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
  >
    <div class="flex items-center space-x-3">
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
</template>

<script setup>
import { computed } from 'vue'
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
  RectangleStackIcon,
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
})

const route = useRoute()
const workspaceStore = useWorkspaceStore()

const iconMap = {
  inbox: InboxIcon,
  lightning: BoltIcon,
  calendar: CalendarIcon,
  clock: ClockIcon,
  archive: ArchiveBoxIcon,
  folder: FolderIcon,
  'calendar-days': CalendarDaysIcon,
  target: FolderIcon, // Заглушка, можно добавить кастомную иконку
  'rectangle-stack': RectangleStackIcon,
}

const iconComponent = computed(() => iconMap[props.icon] || FolderIcon)

const computedTo = computed(() => {
  const workspaceId = workspaceStore.currentWorkspace?.id
  return props.to.replace(':id', workspaceId || '1')
})

const isActive = computed(() => {
  return route.path === computedTo.value
})
</script>

