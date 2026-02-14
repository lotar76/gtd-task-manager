<template>
  <div
    class="rounded-xl border bg-white/80 dark:bg-gray-800/80 border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 cursor-pointer group border-l-4"
    :style="{
      borderLeftColor: sphere.color,
      boxShadow: isHovered ? `0 0 20px ${sphere.color}30` : 'none',
    }"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <div class="flex items-center justify-between mb-2">
      <div class="flex items-center gap-2">
        <div
          class="w-3 h-3 rounded-full"
          :style="{ backgroundColor: sphere.color }"
        />
        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ sphere.name }}</span>
      </div>
      <span
        v-if="sphere.days_without_attention !== null && sphere.days_without_attention > 0"
        class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400"
      >
        {{ sphere.days_without_attention }} дн.
      </span>
    </div>

    <!-- Progress -->
    <div class="flex items-center gap-3">
      <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
        <div
          class="h-full rounded-full transition-all duration-500"
          :style="{
            width: progressPct + '%',
            backgroundColor: sphere.color,
          }"
        />
      </div>
      <span class="text-xs font-medium text-gray-600 dark:text-gray-400 tabular-nums whitespace-nowrap">
        {{ sphere.tasks_done }}/{{ sphere.tasks_total }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  sphere: { type: Object, required: true },
})

const isHovered = ref(false)

const progressPct = computed(() => {
  if (!props.sphere.tasks_total) return 0
  return Math.round((props.sphere.tasks_done / props.sphere.tasks_total) * 100)
})
</script>
