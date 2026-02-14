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
        <!-- Картинка -->
        <div v-if="goal.image_url" class="h-40 overflow-hidden">
          <img :src="goal.image_url" class="w-full h-full object-cover" />
        </div>

        <div class="p-5">
          <!-- Сфера жизни -->
          <span v-if="goal.life_sphere" class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 mb-1">
            <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: goal.life_sphere.color }"></span>
            {{ goal.life_sphere.name }}
          </span>

          <!-- Название -->
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate mb-2">
            {{ goal.name }}
          </h3>

          <!-- Дедлайн: остаток дней -->
          <p v-if="goal.deadline" class="text-sm mb-2 flex items-center" :class="deadlineClass(goal.deadline)">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ formatDeadline(goal.deadline) }}
          </p>

          <!-- Зачем -->
          <p v-if="goal.description" class="text-sm text-gray-600 dark:text-gray-300 mb-2 line-clamp-2">
            {{ goal.description }}
          </p>

          <!-- Стих из библии -->
          <p v-if="goal.bible_verse" class="text-sm italic text-gray-500 dark:text-gray-400 line-clamp-1 mb-3">
            {{ goal.bible_verse }}
          </p>

          <!-- Счётчики -->
          <div class="flex items-center justify-between text-sm">
            <div class="flex items-center space-x-3 text-gray-500 dark:text-gray-400">
              <span v-if="goal.projects_count" class="flex items-center">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                {{ goal.projects_count }}
              </span>
              <span v-if="goal.direct_tasks_count" class="flex items-center">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ goal.direct_tasks_count }}
              </span>
            </div>
            <span v-if="goal.progress > 0" class="text-primary-600 dark:text-primary-400 font-medium">
              {{ goal.progress }}%
            </span>
          </div>

          <!-- Прогресс-бар -->
          <div v-if="goal.progress > 0" class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
            <div
              class="bg-primary-600 h-1.5 rounded-full transition-all"
              :style="{ width: goal.progress + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">У вас пока нет целей</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-2 max-w-md mx-auto leading-relaxed">
        Цель — это ваш маяк. То, к чему стремится сердце.
      </p>
      <p class="text-gray-500 dark:text-gray-400 mb-2 max-w-md mx-auto leading-relaxed">
        Запишите свою мечту, добавьте вдохновляющую картинку и стих из Библии —
        и каждый день напоминайте себе, ради чего вы трудитесь.
      </p>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto leading-relaxed">
        Привязывайте к цели потоки и задачи, чтобы видеть, как шаг за шагом вы приближаетесь к задуманному.
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
import { useWorkspaceStore } from '@/stores/workspace'
import GoalModal from '@/components/goals/GoalModal.vue'

const router = useRouter()
const route = useRoute()
const goalsStore = useGoalsStore()
const workspaceStore = useWorkspaceStore()

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
  router.push(`/workspaces/${route.params.id}/goals/${goal.id}`)
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
