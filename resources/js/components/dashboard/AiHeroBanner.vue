<template>
  <div>
    <!-- Card -->
    <div class="relative overflow-hidden rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 h-full flex flex-col">
    <!-- Silent loading indicator -->
    <div v-if="silentLoading" class="absolute top-2 right-2 z-10">
      <svg class="animate-spin h-4 w-4 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    <div class="p-4 sm:p-6 flex-1 flex flex-col">
      <div class="max-h-[40vh] overflow-y-auto sm:max-h-none sm:overflow-visible scrollbar-thin flex-1 flex flex-col justify-center">
        <!-- Loading skeleton -->
        <div v-if="loading" class="animate-pulse space-y-3">
          <div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full"></div>
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-4/5"></div>
        </div>

        <!-- AI Message -->
        <div v-else-if="message">
          <!-- Main message -->
          <p class="text-sm sm:text-base text-gray-900 dark:text-gray-100 leading-relaxed">
            {{ message.main_message }}
          </p>

          <!-- Follow-up message -->
          <div
            v-if="message.followup_message"
            class="mt-2 sm:mt-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 sm:p-4"
          >
            <p class="text-gray-700 dark:text-gray-300 text-xs sm:text-sm leading-relaxed">{{ message.followup_message }}</p>
          </div>

          <!-- Recommendations -->
          <div v-if="message.recommendations?.length" class="mt-3 sm:mt-4 flex flex-wrap gap-1.5 sm:gap-2">
            <div
              v-for="(rec, i) in message.recommendations"
              :key="i"
              class="bg-gray-100 dark:bg-gray-700 rounded-lg px-2.5 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 flex items-center gap-1.5 sm:gap-2"
            >
              <div
                class="w-2 h-2 rounded-full flex-shrink-0"
                :style="{ backgroundColor: rec.sphere_color || '#9ca3af' }"
              />
              <span>{{ rec.action }}</span>
            </div>
          </div>

          <!-- Generated at timestamp with stale indicator -->
          <div v-if="message.generated_at" class="mt-3 flex items-center gap-2">
            <p class="text-xs text-gray-400 dark:text-gray-500">
              Анализ от {{ formatTimestamp(message.generated_at) }}
            </p>
            <button
              v-if="message.is_stale"
              @click="$emit('refresh')"
              class="text-xs font-medium text-green-600 dark:text-green-500 hover:text-green-700 dark:hover:text-green-400 transition-colors cursor-pointer"
            >
              • нужно проанализировать состояние
            </button>
          </div>
        </div>

        <!-- Fallback when no message - показываем кнопку "Проанализировать" -->
        <div v-else class="flex flex-col items-center justify-center flex-1 text-center">
          <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm mb-4">Получи AI анализ баланса жизненных сфер</p>
          <button
            @click="$emit('analyze')"
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-medium transition-colors mx-auto"
          >
            Проанализировать
          </button>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: { type: Object, default: null },
  loading: { type: Boolean, default: false },
  silentLoading: { type: Boolean, default: false },
})

defineEmits(['refresh', 'analyze'])

const formatTimestamp = (isoString) => {
  const date = new Date(isoString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)

  // Если меньше часа - показываем минуты
  if (diffMins < 60) {
    return `${diffMins} мин. назад`
  }

  // Если меньше суток - показываем часы
  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) {
    return `${diffHours} ч. назад`
  }

  // Иначе - дата и время
  const day = date.getDate().toString().padStart(2, '0')
  const month = (date.getMonth() + 1).toString().padStart(2, '0')
  const hours = date.getHours().toString().padStart(2, '0')
  const minutes = date.getMinutes().toString().padStart(2, '0')
  return `${day}.${month} в ${hours}:${minutes}`
}
</script>

