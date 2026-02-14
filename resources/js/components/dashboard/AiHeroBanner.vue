<template>
  <div>
    <!-- Pulse line (outside card) -->
    <div v-if="message" class="h-1 mb-2 relative overflow-hidden rounded-full">
      <svg
        class="absolute inset-0 w-full h-full"
        preserveAspectRatio="none"
        :viewBox="`0 0 ${pulseWidth} 10`"
      >
        <path
          :d="pulsePath"
          fill="none"
          :stroke="moodColor"
          stroke-width="2"
          stroke-linecap="round"
          class="pulse-line"
          :style="{ '--pulse-color': moodColor }"
        />
      </svg>
    </div>

    <!-- Card -->
    <div class="relative overflow-hidden rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
    <div class="p-4 sm:p-6">
      <div class="max-h-[40vh] overflow-y-auto sm:max-h-none sm:overflow-visible scrollbar-thin">
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

          <!-- Bible verse -->
          <div
            v-if="message.bible_verse"
            class="mt-3 sm:mt-4 pt-3 border-t border-gray-200 dark:border-gray-700"
          >
            <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm italic">
              &laquo;{{ message.bible_verse.text }}&raquo;
              <span class="text-gray-400 dark:text-gray-500 not-italic ml-1">— {{ message.bible_verse.ref }}</span>
            </p>
          </div>
        </div>

        <!-- Fallback when no message -->
        <div v-else>
          <p class="text-base font-medium text-gray-900 dark:text-white">Зеркало жизни</p>
          <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Обзор баланса твоих жизненных сфер</p>
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
})

const moodColors = {
  positive: '#10b981',
  concerned: '#6366f1',
  warning: '#f59e0b',
  serious: '#ef4444',
  reflective: '#8b5cf6',
}

const moodColor = computed(() => {
  const mood = props.message?.mood || 'concerned'
  return moodColors[mood] || moodColors.concerned
})

// Amplitude by mood: calm moods = small waves, intense = big spikes
const moodAmplitude = {
  positive: 2,
  reflective: 2.5,
  concerned: 3,
  warning: 3.5,
  serious: 4.5,
}

const pulseWidth = 200

const pulsePath = computed(() => {
  const mood = props.message?.mood || 'concerned'
  const amp = moodAmplitude[mood] || 3
  const w = pulseWidth
  const mid = 5 // vertical center

  // Generate a heartbeat-like pattern repeated across width
  // Each "beat" is ~40 units wide
  const beats = Math.floor(w / 40)
  let d = `M 0 ${mid}`

  for (let i = 0; i < beats; i++) {
    const x = i * 40
    // Flat -> small dip -> big spike up -> big spike down -> flat
    d += ` L ${x + 12} ${mid}`                    // flat lead
    d += ` L ${x + 16} ${mid + amp * 0.4}`        // small dip
    d += ` L ${x + 20} ${mid - amp}`              // spike up
    d += ` L ${x + 24} ${mid + amp * 0.7}`        // spike down
    d += ` L ${x + 28} ${mid}`                    // return
    d += ` L ${x + 40} ${mid}`                    // flat trail
  }

  return d
})
</script>

<style scoped>
.pulse-line {
  animation: pulse-draw 3s ease-in-out infinite;
  stroke-dasharray: 600;
  stroke-dashoffset: 600;
}

@keyframes pulse-draw {
  0% {
    stroke-dashoffset: 600;
    opacity: 0.4;
  }
  50% {
    stroke-dashoffset: 0;
    opacity: 1;
  }
  100% {
    stroke-dashoffset: -600;
    opacity: 0.4;
  }
}
</style>
