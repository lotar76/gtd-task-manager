<template>
  <router-link
    :to="to"
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
</template>

<script setup>
import { computed, h } from 'vue'
import { useRoute } from 'vue-router'
import {
  InboxIcon,
  BoltIcon,
  CalendarIcon,
  ClockIcon,
  ArchiveBoxIcon,
  FolderIcon,
  CalendarDaysIcon,
  RectangleStackIcon,
  Cog6ToothIcon,
  ChartBarIcon,
  UserGroupIcon,
  TableCellsIcon,
  DocumentTextIcon,
  GlobeAltIcon,
  ListBulletIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import { SmilePlus } from 'lucide-vue-next'
import { BellIcon } from '@heroicons/vue/24/outline'

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

const emit = defineEmits(['close-sidebar'])

const route = useRoute()

const handleLinkClick = () => {
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
  'rectangle-stack': RectangleStackIcon,
  cog: Cog6ToothIcon,
  'chart-bar': ChartBarIcon,
  'archive-box': ArchiveBoxIcon,
  'document-text': DocumentTextIcon,
  'user-group': UserGroupIcon,
  'table-cells': TableCellsIcon,
  'globe-alt': GlobeAltIcon,
  'list-bullet': ListBulletIcon,
  refresh: ArrowPathIcon,
  'smile-plus': SmilePlus,
  bell: BellIcon,
  streams: {
    render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24', 'stroke-width': '2', class: 'w-5 h-5' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M4 6v12M9 4v16M14 8v8M19 5v14' })
    ])
  },
}

const iconComponent = computed(() => iconMap[props.icon] || FolderIcon)

const isActive = computed(() => {
  return route.path === props.to
})
</script>
