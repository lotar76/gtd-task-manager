<template>
  <div class="flex flex-col items-center">
    <svg
      :width="size"
      :height="size"
      :viewBox="`${-pad} ${-pad} ${size + pad * 2} ${size + pad * 2}`"
    >

      <!-- Grid circles (between inner and outer radius) -->
      <circle
        v-for="pct in gridLevels"
        :key="'grid-' + pct"
        :cx="center"
        :cy="center"
        :r="innerR + (outerR - innerR) * pct / 100"
        fill="none"
        stroke="currentColor"
        class="text-gray-200 dark:text-gray-700"
        stroke-width="0.5"
        stroke-dasharray="3 3"
      />

      <!-- Outer circle -->
      <circle
        :cx="center"
        :cy="center"
        :r="outerR"
        fill="none"
        stroke="currentColor"
        class="text-gray-300 dark:text-gray-600"
        stroke-width="1.5"
      />

      <!-- Sector divider lines (from inner to outer radius) -->
      <line
        v-for="(pts, i) in dividerLines"
        :key="'div-' + i"
        :x1="pts.x1" :y1="pts.y1"
        :x2="pts.x2" :y2="pts.y2"
        stroke="currentColor"
        class="text-gray-300 dark:text-gray-600"
        stroke-width="1"
      />

      <!-- Gray fills: total (planned) tasks -->
      <path
        v-for="(sphere, i) in spheres"
        :key="'total-' + i"
        :d="totalAnnularPath(i, sphere)"
        fill="currentColor"
        class="text-gray-400 dark:text-gray-500"
        fill-opacity="0.25"
      />

      <!-- Colored fills: done tasks -->
      <path
        v-for="(sphere, i) in spheres"
        :key="'done-' + i"
        :d="doneAnnularPath(i, sphere)"
        :fill="sphere.color || '#888'"
        fill-opacity="0.45"
        class="transition-all duration-700 ease-out"
      />

      <!-- Done edge arc (bright colored arc at done level) -->
      <path
        v-for="(sphere, i) in spheres"
        :key="'edge-' + i"
        :d="doneEdgeArc(i, sphere)"
        fill="none"
        :stroke="sphere.color || '#888'"
        stroke-width="2.5"
        stroke-linecap="round"
        class="transition-all duration-700 ease-out"
      />

      <!-- Sector outer arcs (colored border for each sector) -->
      <path
        v-for="(sphere, i) in spheres"
        :key="'arc-' + i"
        :d="outerArcPath(i)"
        fill="none"
        :stroke="sphere.color || '#888'"
        stroke-width="3"
        stroke-opacity="0.25"
        stroke-linecap="round"
      />

      <!-- Sphere labels -->
      <text
        v-for="(sphere, i) in spheres"
        :key="'label-' + i"
        :x="labelPoints[i].x"
        :y="labelPoints[i].y"
        :fill="sphere.color || '#888'"
        font-size="11"
        font-weight="600"
        :text-anchor="getTextAnchor(i)"
        dominant-baseline="central"
      >
        {{ sphere.name || '' }}
      </text>

      <!-- Inner circle (center) -->
      <circle
        :cx="center"
        :cy="center"
        :r="innerR"
        class="fill-white dark:fill-gray-900 stroke-gray-300 dark:stroke-gray-600"
        stroke-width="1"
      />

      <!-- Center balance index -->
      <text
        :x="center"
        :y="center - 8"
        text-anchor="middle"
        dominant-baseline="central"
        font-size="28"
        font-weight="bold"
        class="fill-gray-900 dark:fill-white"
      >
        {{ balanceIndex }}
      </text>
      <text
        :x="center"
        :y="center + 14"
        text-anchor="middle"
        dominant-baseline="central"
        font-size="10"
        class="fill-gray-400 dark:fill-gray-500"
      >
        баланс
      </text>
    </svg>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  spheres: { type: Array, default: () => [] },
  balanceIndex: { type: Number, default: 0 },
  size: { type: Number, default: 280 },
})

const pad = 40 // extra padding for labels
const center = computed(() => props.size / 2)
const outerR = computed(() => props.size / 2 - 40)
const innerR = computed(() => outerR.value * 0.35)
const numSpheres = computed(() => props.spheres.length)
const angleStep = computed(() => numSpheres.value > 0 ? (2 * Math.PI) / numSpheres.value : 0)
const GAP = 0.03
const gridLevels = [25, 50, 75]

const sa = (i) => angleStep.value * i - Math.PI / 2 + GAP / 2
const ea = (i) => angleStep.value * (i + 1) - Math.PI / 2 - GAP / 2
const mid = (i) => (sa(i) + ea(i)) / 2

const px = (angle, r) => center.value + r * Math.cos(angle)
const py = (angle, r) => center.value + r * Math.sin(angle)

// Max total tasks across all spheres (for normalization)
const maxTotal = computed(() => {
  if (!props.spheres.length) return 1
  return Math.max(...props.spheres.map(s => s.total || 0), 1)
})

// Annular sector path (donut slice between r1 and r2)
const annularPath = (i, r1, r2) => {
  if (numSpheres.value === 0 || r2 <= r1) return ''
  const s = sa(i)
  const e = ea(i)
  const large = (e - s) > Math.PI ? 1 : 0
  return [
    `M ${px(s, r1)} ${py(s, r1)}`,
    `L ${px(s, r2)} ${py(s, r2)}`,
    `A ${r2} ${r2} 0 ${large} 1 ${px(e, r2)} ${py(e, r2)}`,
    `L ${px(e, r1)} ${py(e, r1)}`,
    `A ${r1} ${r1} 0 ${large} 0 ${px(s, r1)} ${py(s, r1)}`,
    'Z',
  ].join(' ')
}

// Arc path (just the arc line)
const arcPath = (i, r) => {
  if (numSpheres.value === 0) return ''
  const s = sa(i)
  const e = ea(i)
  const large = (e - s) > Math.PI ? 1 : 0
  return `M ${px(s, r)} ${py(s, r)} A ${r} ${r} 0 ${large} 1 ${px(e, r)} ${py(e, r)}`
}

// Radius for total tasks (gray) — normalized across all spheres
const totalR = (sphere) => {
  if (!sphere.total || sphere.total === 0) return innerR.value
  const pct = sphere.total / maxTotal.value
  const minPct = 0.1
  return innerR.value + (outerR.value - innerR.value) * Math.max(pct, minPct)
}

// Radius for done tasks (colored) — normalized across all spheres
const doneR = (sphere) => {
  if (!sphere.done || sphere.done === 0) return innerR.value
  const pct = sphere.done / maxTotal.value
  const minPct = 0.08
  return innerR.value + (outerR.value - innerR.value) * Math.max(pct, minPct)
}

const outerArcPath = (i) => arcPath(i, outerR.value)

const totalAnnularPath = (i, sphere) => {
  const r = totalR(sphere)
  if (r <= innerR.value) return ''
  return annularPath(i, innerR.value, r)
}

const doneAnnularPath = (i, sphere) => {
  const r = doneR(sphere)
  if (r <= innerR.value) return ''
  return annularPath(i, innerR.value, r)
}

const doneEdgeArc = (i, sphere) => {
  const r = doneR(sphere)
  if (r <= innerR.value) return ''
  return arcPath(i, r)
}

// Divider lines from innerR to outerR at sector boundaries
const dividerLines = computed(() => {
  if (numSpheres.value <= 1) return []
  return props.spheres.map((_, i) => {
    const angle = angleStep.value * i - Math.PI / 2
    return {
      x1: px(angle, innerR.value),
      y1: py(angle, innerR.value),
      x2: px(angle, outerR.value),
      y2: py(angle, outerR.value),
    }
  })
})

// Labels outside the wheel
const labelPoints = computed(() => {
  return props.spheres.map((_, i) => {
    const labelR = outerR.value + 18
    return { x: px(mid(i), labelR), y: py(mid(i), labelR) }
  })
})

const getTextAnchor = (index) => {
  const deg = (mid(index) * 180) / Math.PI
  if (deg > -60 && deg < 60) return 'start'
  if (deg > 120 || deg < -120) return 'end'
  return 'middle'
}
</script>
