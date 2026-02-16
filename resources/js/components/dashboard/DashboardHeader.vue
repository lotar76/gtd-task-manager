<template>
  <div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
      <!-- Title + Date -->
      <div class="flex flex-col gap-1">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
          Зеркало жизни
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ formattedDate }}</p>
      </div>

      <!-- Bible verse -->
      <div v-if="bibleVerse" class="flex-1 max-w-2xl mx-4">
        <p class="text-gray-600 dark:text-gray-400 text-sm italic leading-relaxed">
          &laquo;{{ bibleVerse.text }}&raquo;
          <span class="text-gray-400 dark:text-gray-500 not-italic ml-1">— {{ bibleVerse.ref }}</span>
        </p>
      </div>

      <!-- Period Selector -->
      <div class="inline-flex rounded-xl bg-gray-100 dark:bg-gray-800 p-1" role="group">
        <button
          v-for="p in periods"
          :key="p.value"
          @click="$emit('change-period', p.value)"
          type="button"
          class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-all duration-200"
          :class="[
            period === p.value
              ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
          ]"
        >
          {{ p.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'

dayjs.locale('ru')

const props = defineProps({
  period: { type: String, required: true },
  bibleVerse: { type: Object, default: null },
})

defineEmits(['change-period'])

const periods = [
  { value: 'day', label: 'День' },
  { value: 'week', label: 'Неделя' },
  { value: 'month', label: 'Месяц' },
  { value: 'year', label: 'Год' },
]

const formattedDate = computed(() => {
  const now = dayjs()
  switch (props.period) {
    case 'day':
      return now.format('D MMMM YYYY, dddd')
    case 'week': {
      const start = now.startOf('week')
      const end = now.endOf('week')
      return `${start.format('D MMM')} — ${end.format('D MMM YYYY')}`
    }
    case 'month':
      return now.format('MMMM YYYY')
    case 'year':
      return now.format('YYYY год')
    default:
      return now.format('D MMMM YYYY')
  }
})
</script>
