<template>
  <div class="bg-white/80 dark:bg-gray-800/80 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-4">
    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
      Сферы без внимания
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
      <div
        v-for="sphere in spheres"
        :key="sphere.id"
        class="flex items-center gap-2 rounded-lg border border-dashed p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
        :style="{
          borderColor: sphere.color + '40',
          backgroundColor: sphere.color + '08',
        }"
      >
        <div
          class="w-2.5 h-2.5 rounded-full flex-shrink-0"
          :style="{ backgroundColor: sphere.color }"
        />
        <div class="min-w-0 flex-1">
          <p class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate">{{ sphere.name }}</p>
          <p class="text-[10px] text-gray-400 dark:text-gray-500">
            {{ sphere.days_without_attention !== null ? `${sphere.days_without_attention} ${pluralDays(sphere.days_without_attention)} без внимания` : 'Нет задач' }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  spheres: { type: Array, default: () => [] },
})

const pluralDays = (n) => {
  const abs = Math.abs(n) % 100
  const last = abs % 10
  if (abs > 10 && abs < 20) return 'дней'
  if (last === 1) return 'день'
  if (last >= 2 && last <= 4) return 'дня'
  return 'дней'
}
</script>
