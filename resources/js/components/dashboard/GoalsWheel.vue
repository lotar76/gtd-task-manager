<template>
  <div class="flex flex-col items-center justify-center">
    <!-- SVG Wheel -->
    <svg :width="size" :height="size" viewBox="0 0 200 200" class="transform -rotate-90">
      <!-- Background circle -->
      <circle
        cx="100"
        cy="100"
        r="85"
        fill="none"
        stroke="#e5e7eb"
        stroke-width="30"
        class="dark:stroke-gray-700"
      />

      <!-- Goal segments -->
      <g v-for="(goal, index) in goalsData" :key="goal.id">
        <!-- Background segment -->
        <path
          :d="getSegmentPath(index, goalsData.length, 70, 100)"
          fill="none"
          :stroke="goal.color + '30'"
          stroke-width="30"
          class="transition-all duration-300"
          :class="{ 'opacity-50': hoveredGoalName && hoveredGoalName !== goal.name }"
        />

        <!-- Progress segment -->
        <path
          v-if="goal.progress > 0"
          :d="getProgressPath(index, goalsData.length, goal.progress, 70, 100)"
          fill="none"
          :stroke="goal.color"
          stroke-width="30"
          class="transition-all duration-300"
          :class="{ 'opacity-50': hoveredGoalName && hoveredGoalName !== goal.name }"
        />

        <!-- Interactive invisible segment for hover -->
        <path
          :d="getSegmentPath(index, goalsData.length, 70, 100)"
          fill="transparent"
          stroke="transparent"
          stroke-width="30"
          class="cursor-pointer"
          @mouseenter="$emit('goal-hover', goal.name)"
          @mouseleave="$emit('goal-leave')"
        />
      </g>

      <!-- Center circle with progress -->
      <circle
        cx="100"
        cy="100"
        r="55"
        :fill="getCenterColor()"
        class="transition-colors duration-300"
      />

      <!-- Center text -->
      <text
        x="100"
        y="95"
        text-anchor="middle"
        class="fill-white text-2xl font-bold transform rotate-90"
        style="transform-origin: 100px 100px"
      >
        {{ averageProgress }}%
      </text>
      <text
        x="100"
        y="110"
        text-anchor="middle"
        class="fill-white text-xs transform rotate-90"
        style="transform-origin: 100px 100px"
      >
        Прогресс целей
      </text>
    </svg>

    <!-- Legend -->
    <div class="mt-4 grid grid-cols-2 gap-2 max-w-xs">
      <div
        v-for="goal in goalsData"
        :key="goal.id"
        class="flex items-center gap-2 text-xs transition-opacity duration-200"
        :class="{ 'opacity-40': hoveredGoalName && hoveredGoalName !== goal.name }"
        @mouseenter="$emit('goal-hover', goal.name)"
        @mouseleave="$emit('goal-leave')"
      >
        <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: goal.color }" />
        <span class="text-gray-700 dark:text-gray-300 truncate">{{ goal.name }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  goals: { type: Array, default: () => [] },
  size: { type: Number, default: 320 },
  hoveredGoalName: { type: String, default: null },
})

defineEmits(['goal-hover', 'goal-leave'])

const goalsData = computed(() => {
  return props.goals.map(g => ({
    id: g.id,
    name: g.name,
    progress: g.progress || 0,
    color: g.color || '#9ca3af',
  }))
})

const averageProgress = computed(() => {
  if (!goalsData.value.length) return 0
  const sum = goalsData.value.reduce((acc, g) => acc + g.progress, 0)
  return Math.round(sum / goalsData.value.length)
})

const getCenterColor = () => {
  const avg = averageProgress.value
  if (avg >= 75) return '#10b981' // green
  if (avg >= 50) return '#3b82f6' // blue
  if (avg >= 25) return '#f59e0b' // amber
  return '#ef4444' // red
}

const getSegmentPath = (index, total, innerRadius, outerRadius) => {
  const anglePerSegment = (2 * Math.PI) / total
  const startAngle = index * anglePerSegment
  const endAngle = startAngle + anglePerSegment

  const x1 = 100 + innerRadius * Math.cos(startAngle)
  const y1 = 100 + innerRadius * Math.sin(startAngle)
  const x2 = 100 + outerRadius * Math.cos(startAngle)
  const y2 = 100 + outerRadius * Math.sin(startAngle)
  const x3 = 100 + outerRadius * Math.cos(endAngle)
  const y3 = 100 + outerRadius * Math.sin(endAngle)
  const x4 = 100 + innerRadius * Math.cos(endAngle)
  const y4 = 100 + innerRadius * Math.sin(endAngle)

  const largeArc = anglePerSegment > Math.PI ? 1 : 0

  return `
    M ${x1} ${y1}
    L ${x2} ${y2}
    A ${outerRadius} ${outerRadius} 0 ${largeArc} 1 ${x3} ${y3}
    L ${x4} ${y4}
    A ${innerRadius} ${innerRadius} 0 ${largeArc} 0 ${x1} ${y1}
    Z
  `
}

const getProgressPath = (index, total, progress, innerRadius, outerRadius) => {
  const anglePerSegment = (2 * Math.PI) / total
  const startAngle = index * anglePerSegment
  const progressAngle = startAngle + (anglePerSegment * progress) / 100

  const x1 = 100 + innerRadius * Math.cos(startAngle)
  const y1 = 100 + innerRadius * Math.sin(startAngle)
  const x2 = 100 + outerRadius * Math.cos(startAngle)
  const y2 = 100 + outerRadius * Math.sin(startAngle)
  const x3 = 100 + outerRadius * Math.cos(progressAngle)
  const y3 = 100 + outerRadius * Math.sin(progressAngle)
  const x4 = 100 + innerRadius * Math.cos(progressAngle)
  const y4 = 100 + innerRadius * Math.sin(progressAngle)

  const progressArcAngle = (anglePerSegment * progress) / 100
  const largeArc = progressArcAngle > Math.PI ? 1 : 0

  return `
    M ${x1} ${y1}
    L ${x2} ${y2}
    A ${outerRadius} ${outerRadius} 0 ${largeArc} 1 ${x3} ${y3}
    L ${x4} ${y4}
    A ${innerRadius} ${innerRadius} 0 ${largeArc} 0 ${x1} ${y1}
    Z
  `
}
</script>
