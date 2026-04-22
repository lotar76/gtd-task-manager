<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Цели</h1>
      <button
        @click="showGoalModal = true"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Создать цель</span>
      </button>
    </div>

    <!-- Goals Grid -->
    <div v-if="activeGoals.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="goal in activeGoals"
        :key="goal.id"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow cursor-pointer overflow-hidden"
        @click="openGoal(goal)"
      >
        <div class="p-5">
          <!-- Сфера + дата -->
          <div class="flex items-center justify-between mb-2">
            <div v-if="goal.life_sphere" class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full overflow-hidden border-2 flex-shrink-0" :style="{ borderColor: goal.life_sphere.color }">
                <img v-if="goal.life_sphere.cover_image_url" :src="goal.life_sphere.cover_image_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-white text-[10px] font-bold" :style="{ backgroundColor: goal.life_sphere.color }">
                  {{ goal.life_sphere.name?.charAt(0) }}
                </div>
              </div>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ goal.life_sphere.name }}</span>
            </div>
            <span v-if="goal.deadline" class="text-xs flex items-center gap-1" :class="deadlineClass(goal.deadline)">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ formatDeadline(goal.deadline) }}
            </span>
          </div>

          <!-- Название -->
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
            {{ goal.name }}
          </h3>

        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">У вас пока нет целей</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto leading-relaxed">
        Привязывайте к цели потоки и задачи, чтобы видеть прогресс.
      </p>
      <button
        @click="showGoalModal = true"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
      >
        Создать цель
      </button>
    </div>

    <!-- Goal Modal -->
    <GoalModal
      :show="showGoalModal"
      :goal="selectedGoal"
      :server-error="goalError"
      @close="handleCloseGoalModal"
      @submit="handleSaveGoal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useGoalsStore } from '@/stores/goals'
import GoalModal from '@/components/goals/GoalModal.vue'

const router = useRouter()
const route = useRoute()
const goalsStore = useGoalsStore()

const showGoalModal = ref(false)
const selectedGoal = ref(null)
const goalError = ref('')

const activeGoals = computed(() => goalsStore.activeGoals)

const getRemainingDays = (deadlineStr) => {
  if (!deadlineStr) return null
  const deadline = new Date(deadlineStr)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  deadline.setHours(0, 0, 0, 0)
  return Math.ceil((deadline - today) / (1000 * 60 * 60 * 24))
}

const formatDeadline = (deadlineStr) => {
  const days = getRemainingDays(deadlineStr)
  if (days === null) return ''
  if (days < 0) return `Просрочено на ${Math.abs(days)} дн.`
  if (days === 0) return 'Сегодня'
  if (days === 1) return 'Завтра'
  if (days <= 30) return `${days} дн.`
  const date = new Date(deadlineStr)
  return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

const deadlineClass = (deadlineStr) => {
  const days = getRemainingDays(deadlineStr)
  if (days === null) return 'text-gray-500 dark:text-gray-400'
  if (days < 0) return 'text-red-500 dark:text-red-400'
  if (days <= 3) return 'text-orange-500 dark:text-orange-400'
  if (days <= 7) return 'text-yellow-600 dark:text-yellow-400'
  return 'text-gray-500 dark:text-gray-400'
}

const openGoal = (goal) => {
  router.push(`/goals/${goal.id}`)
}

const handleSaveGoal = async (goalData) => {
  goalError.value = ''
  try {
    if (selectedGoal.value) {
      await goalsStore.updateGoal(selectedGoal.value.id, goalData)
    } else {
      await goalsStore.createGoal(goalData)
    }
    showGoalModal.value = false
    selectedGoal.value = null
    await goalsStore.fetchAllGoals({ force: true })
  } catch (error) {
    console.error('Error saving goal:', error)
    goalError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении цели'
  }
}

const handleCloseGoalModal = () => {
  showGoalModal.value = false
  selectedGoal.value = null
  goalError.value = ''
}

onMounted(async () => {
  await goalsStore.fetchAllGoals()
})
</script>
