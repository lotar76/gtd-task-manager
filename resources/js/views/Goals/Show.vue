<template>
  <div>
    <!-- Image Banner (full width) -->
    <div v-if="goal?.image_url" class="w-full overflow-hidden">
      <img :src="goal.image_url" class="w-full h-48 lg:h-64 object-cover" />
    </div>

  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">

      <!-- Title + Actions -->
      <div class="flex items-start justify-between mb-4">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">
          {{ goal?.name || 'Загрузка...' }}
        </h1>
        <div class="flex items-center space-x-1 flex-shrink-0 ml-3">
          <button
            @click="handleEditGoal"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Редактировать"
          >
            <PencilIcon class="w-5 h-5" />
          </button>
          <button
            v-if="goal?.status !== 'archived'"
            @click="handleArchiveGoal"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Архивировать"
          >
            <ArchiveBoxArrowDownIcon class="w-5 h-5" />
          </button>
          <button
            v-else
            @click="handleUnarchiveGoal"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Восстановить"
          >
            <ArrowUturnLeftIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Meta info -->
      <div class="space-y-3 mb-8">
        <!-- Workspace -->
        <div v-if="goalWorkspace" class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
          <span v-if="goalWorkspace.emoji" class="grayscale">{{ goalWorkspace.emoji }}</span>
          <span>{{ goalWorkspace.name }}</span>
        </div>

        <!-- Life Sphere -->
        <div v-if="goalSphere" class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
          <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: goalSphere.color }"></span>
          <span>{{ goalSphere.name }}</span>
        </div>

        <!-- Deadline -->
        <div v-if="goal?.deadline" class="flex items-center text-sm" :class="deadlineClass">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          {{ formatDeadline(goal.deadline) }}
        </div>

        <!-- Description -->
        <p v-if="goal?.description" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
          {{ goal.description }}
        </p>

        <!-- Bible verse -->
        <p v-if="goal?.bible_verse" class="text-sm italic text-gray-500 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-3">
          {{ goal.bible_verse }}
        </p>

        <!-- Progress cubes -->
        <div v-if="allTasks.length > 0" class="flex items-center gap-1" :title="`${allCompletedCount} / ${allTasks.length} выполнено`">
          <template v-if="allTasks.length <= 20">
            <div
              v-for="i in allTasks.length"
              :key="i"
              class="w-2.5 h-2.5 rounded-sm"
              :class="i <= allCompletedCount ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
            ></div>
          </template>
          <template v-else>
            <div
              v-for="i in 20"
              :key="i"
              class="w-2.5 h-2.5 rounded-sm"
              :class="i <= Math.round(allCompletedCount / allTasks.length * 20) ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
            ></div>
            <span class="text-xs text-gray-400 ml-1">{{ allCompletedCount }}/{{ allTasks.length }}</span>
          </template>
        </div>
      </div>

      <template v-if="goal">
        <!-- Projects Section -->
        <div v-if="goalProjects.length > 0" class="mb-8">
          <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-1">
            Потоки
          </h2>
          <div class="space-y-2">
            <div
              v-for="project in goalProjects"
              :key="project.id"
              class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 overflow-hidden"
            >
              <!-- Project header -->
              <div
                class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                @click="toggleProject(project.id)"
              >
                <div class="flex items-center flex-1 min-w-0">
                  <svg
                    class="w-4 h-4 mr-2 text-gray-400 transition-transform flex-shrink-0"
                    :class="{ 'rotate-90': expandedProjects.has(project.id) }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ project.name }}
                    </h3>
                  </div>
                </div>
                <div class="flex items-center ml-3 gap-2">
                  <span v-if="project.tasks" class="text-xs text-gray-400">
                    {{ project.tasks.filter(t => t.completed_at).length }}/{{ project.tasks.length }}
                  </span>
                  <button
                    @click.stop="openProject(project)"
                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded transition-colors"
                    title="Открыть поток"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </button>
                </div>
              </div>
              <!-- Mini progress -->
              <div v-if="project.tasks && project.tasks.length > 0" class="px-4 pb-1">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                  <div
                    class="bg-green-500 h-1 rounded-full transition-all"
                    :style="{ width: Math.round(project.tasks.filter(t => t.completed_at).length / project.tasks.length * 100) + '%' }"
                  ></div>
                </div>
              </div>
              <!-- Expanded tasks -->
              <div v-if="expandedProjects.has(project.id) && project.tasks && project.tasks.length > 0" class="px-4 pb-3 pt-1">
                <TaskList
                  :tasks="project.tasks.filter(t => !t.completed_at)"
                  @task-click="handleTaskClick"
                  @toggle-complete="handleToggleComplete"
                />
                <div v-if="project.tasks.filter(t => t.completed_at).length > 0" class="mt-2 opacity-50">
                  <TaskList
                    :tasks="project.tasks.filter(t => t.completed_at)"
                    @task-click="handleTaskClick"
                    @toggle-complete="handleToggleComplete"
                  />
                </div>
                <div v-if="project.tasks.length === 0" class="text-sm text-gray-400 text-center py-2">
                  Нет задач
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Direct Tasks Section -->
        <div v-if="activeTasks.length > 0 || completedTasks.length > 0">
          <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-1">
            Задачи
          </h2>

          <!-- Active Tasks -->
          <TaskList
            v-if="activeTasks.length > 0"
            :tasks="activeTasks"
            @task-click="handleTaskClick"
            @toggle-complete="handleToggleComplete"
          />

          <!-- Completed Tasks -->
          <div v-if="completedTasks.length > 0" class="mt-4">
            <h3 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3 px-1">
              Выполненные
            </h3>
            <div class="opacity-50">
              <TaskList
                :tasks="completedTasks"
                @task-click="handleTaskClick"
                @toggle-complete="handleToggleComplete"
              />
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="goalProjects.length === 0 && allTasks.length === 0" class="py-12 text-center">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">В цели пока нет задач и потоков</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
              Привяжите к цели потоки или создайте задачи, чтобы начать двигаться к результату.
            </p>
          </div>
        </div>
      </template>

      <!-- Task View -->
      <TaskView
        :show="showTaskView"
        :task="selectedTask"
        @close="showTaskView = false; selectedTask = null"
        @enter-edit="handleEnterEdit"
        @complete-task="handleCompleteTask"
        @uncomplete-task="handleUncompleteTask"
      />

      <!-- Task Modal -->
      <TaskModal
        :show="showTaskModal"
        :task="selectedTask"
        :server-error="taskError"
        @close="handleCloseTaskModal"
        @submit="handleSaveTask"
      />

      <!-- Goal Modal -->
      <GoalModal
        :show="showGoalModal"
        :goal="goal"
        :server-error="goalError"
        @close="handleCloseGoalModal"
        @submit="handleSaveGoal"
      />
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGoalsStore } from '@/stores/goals'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import { useWorkspaceStore } from '@/stores/workspace'
import { PencilIcon, ArchiveBoxArrowDownIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import GoalModal from '@/components/goals/GoalModal.vue'

const route = useRoute()
const router = useRouter()
const goalsStore = useGoalsStore()
const tasksStore = useTasksStore()
const projectsStore = useProjectsStore()
const workspaceStore = useWorkspaceStore()

const expandedProjects = ref(new Set())
const showTaskModal = ref(false)
const showTaskView = ref(false)
const showGoalModal = ref(false)
const selectedTask = ref(null)
const taskError = ref('')
const goalError = ref('')

// Всё из сторов — без лишних API-запросов
const currentGoalId = computed(() => parseInt(route.params.goalId))

const goal = computed(() => {
  if (!currentGoalId.value) return null
  return goalsStore.allGoals.find(g => g.id === currentGoalId.value) || null
})

// Потоки цели с задачами из tasksStore
const goalProjects = computed(() => {
  if (!currentGoalId.value) return []
  return projectsStore.allProjects
    .filter(p => p.goal_id === currentGoalId.value)
    .map(p => ({
      ...p,
      tasks: tasksStore.allTasks.filter(t => t.project_id === p.id)
    }))
})

const goalWorkspace = computed(() => {
  if (!goal.value) return null
  return workspaceStore.workspaces.find(w => w.id === goal.value.workspace_id)
})

const goalSphere = computed(() => {
  return goal.value?.life_sphere || null
})

// Прямые задачи цели (без проекта)
const directTasks = computed(() => {
  if (!currentGoalId.value) return []
  return tasksStore.allTasks.filter(t => t.goal_id === currentGoalId.value && !t.project_id)
})

const activeTasks = computed(() => directTasks.value.filter(t => !t.completed_at))
const completedTasks = computed(() => directTasks.value.filter(t => t.completed_at))

// Все задачи цели (для прогресс-кубиков): прямые + из потоков
const allTasks = computed(() => {
  if (!currentGoalId.value) return []
  return tasksStore.allTasks.filter(t =>
    t.goal_id === currentGoalId.value ||
    goalProjects.value.some(p => p.id === t.project_id)
  )
})

const allCompletedCount = computed(() => allTasks.value.filter(t => t.completed_at).length)

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

const deadlineClass = computed(() => {
  if (!goal.value?.deadline) return 'text-gray-500 dark:text-gray-400'
  const days = getRemainingDays(goal.value.deadline)
  if (days < 0) return 'text-red-500 dark:text-red-400'
  if (days <= 3) return 'text-orange-500 dark:text-orange-400'
  if (days <= 7) return 'text-yellow-600 dark:text-yellow-400'
  return 'text-gray-500 dark:text-gray-400'
})

const toggleProject = (projectId) => {
  if (expandedProjects.value.has(projectId)) {
    expandedProjects.value.delete(projectId)
  } else {
    expandedProjects.value.add(projectId)
  }
  expandedProjects.value = new Set(expandedProjects.value)
}

const openProject = (project) => {
  const workspaceId = route.params.id
  router.push(`/workspaces/${workspaceId}/projects/${project.id}`)
}

const handleEditGoal = () => {
  showGoalModal.value = true
}

const handleArchiveGoal = async () => {
  if (!confirm(`Архивировать цель "${goal.value?.name}"?`)) return

  try {
    await goalsStore.updateGoal(goal.value.id, { status: 'archived' })
    router.push(`/workspaces/${goal.value.workspace_id}/goals`)
  } catch (error) {
    console.error('Error archiving goal:', error)
    alert('Ошибка при архивировании цели')
  }
}

const handleUnarchiveGoal = async () => {
  try {
    await goalsStore.updateGoal(goal.value.id, { status: 'active' })
  } catch (error) {
    console.error('Error unarchiving goal:', error)
    alert('Ошибка при восстановлении цели')
  }
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
}

const handleEnterEdit = () => {
  showTaskView.value = false
  showTaskModal.value = true
}

const handleCompleteTask = async (task) => {
  try {
    await tasksStore.completeTask(task.id)
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleUncompleteTask = async (task) => {
  try {
    await tasksStore.uncompleteTask(task.id)
  } catch (error) {
    console.error('Error uncompleting task:', error)
  }
}

const handleToggleComplete = async (task) => {
  try {
    if (task.completed_at) {
      await tasksStore.uncompleteTask(task.id)
    } else {
      await tasksStore.completeTask(task.id)
    }
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value?.id) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
    selectedTask.value = null
  } catch (error) {
    console.error('Error saving task:', error)
    taskError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении задачи'
  }
}

const handleCloseTaskModal = () => {
  showTaskModal.value = false
  selectedTask.value = null
  taskError.value = ''
}

const handleSaveGoal = async (goalData) => {
  goalError.value = ''
  try {
    await goalsStore.updateGoal(goal.value.id, goalData)
    showGoalModal.value = false
    await goalsStore.fetchAllGoals({ force: true })
  } catch (error) {
    console.error('Error saving goal:', error)
    goalError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении цели'
  }
}

const handleCloseGoalModal = () => {
  showGoalModal.value = false
  goalError.value = ''
}
</script>
