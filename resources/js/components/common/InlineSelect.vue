<template>
  <div class="relative" ref="rootRef">
    <div
      class="flex items-center gap-2.5 py-1.5 px-1 -mx-1 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors"
      @click.stop="toggle"
    >
      <div v-if="icon" class="text-gray-400 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="iconPath" />
        </svg>
      </div>
      <div v-if="label" class="w-20 text-xs text-gray-400 flex-shrink-0">{{ label }}</div>
      <div class="flex-1 min-w-0 text-sm truncate" :class="current ? 'text-gray-800 dark:text-gray-100 font-medium' : 'text-gray-400'">
        {{ current?.name || placeholder }}
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="open"
        class="fixed z-[60] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-1 max-h-64 overflow-y-auto thin-scroll"
        :style="{ top: pos.top + 'px', left: pos.left + 'px', width: pos.width + 'px' }"
        @click.stop
      >
        <button
          type="button"
          class="w-full text-left px-2.5 py-1 text-sm rounded transition-colors"
          :class="modelValue == null ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
          @click="select(null)"
        >— не выбрано</button>
        <button
          v-for="i in items"
          :key="i.id"
          type="button"
          class="w-full text-left px-2.5 py-1 text-sm rounded transition-colors truncate block"
          :class="modelValue === i.id ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
          @click="select(i.id)"
        >{{ i.name }}</button>
        <div v-if="!items.length" class="px-2.5 py-2 text-sm text-gray-400 text-center">Пусто</div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'

const ICONS = {
  calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  folder: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
  target: 'M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-4a4 4 0 100 8 4 4 0 000-8z',
  sparkles: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.29 5.29L20 11l-5.29 2.29L12 20l-2.29-5.29L4 11l5.29-2.29L12 3z',
}

const props = defineProps({
  modelValue: { type: [Number, String, null], default: null },
  items: { type: Array, default: () => [] },
  placeholder: { type: String, default: '—' },
  label: { type: String, default: '' },
  icon: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

const rootRef = ref(null)
const open = ref(false)
const pos = ref({ top: 0, left: 0, width: 240 })

const iconPath = computed(() => ICONS[props.icon] || '')
const current = computed(() => props.items.find(i => i.id === props.modelValue) || null)

const updatePos = () => {
  if (!rootRef.value) return
  const r = rootRef.value.getBoundingClientRect()
  const width = Math.max(220, r.width)
  const height = 260
  let top = r.bottom + 4
  if (top + height > window.innerHeight - 8) top = Math.max(8, r.top - height - 4)
  let left = r.left
  if (left + width > window.innerWidth - 8) left = window.innerWidth - width - 8
  pos.value = { top, left, width }
}

const toggle = () => {
  open.value = !open.value
  if (open.value) nextTick(updatePos)
}
const select = (id) => { emit('update:modelValue', id); open.value = false }

const onDocClick = (e) => {
  if (open.value && rootRef.value && !rootRef.value.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

watch(() => props.modelValue, () => {})
</script>

<style scoped>
.thin-scroll { scrollbar-width: thin; scrollbar-color: rgba(156,163,175,0.35) transparent; }
.thin-scroll::-webkit-scrollbar { width: 6px; }
.thin-scroll::-webkit-scrollbar-thumb { background-color: rgba(156,163,175,0.25); border-radius: 3px; }
.thin-scroll::-webkit-scrollbar-thumb:hover { background-color: rgba(156,163,175,0.5); }
</style>
