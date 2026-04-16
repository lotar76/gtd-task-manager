<template>
  <div>
    <div class="p-4 lg:p-8">
      <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-6">
        <!-- LEFT: основное содержимое -->
        <div class="min-w-0 space-y-6">
          <div v-if="goal?.image_url" class="relative w-full aspect-video max-h-48 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
            <img :src="goal.image_url" class="w-full h-full object-cover" />
          </div>
          <!-- Title + actions -->
          <div class="flex items-start justify-between">
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">
              {{ goal?.name || 'Загрузка...' }}
            </h1>
            <div class="flex items-center gap-1 flex-shrink-0 ml-3">
              <button @click="handleEditGoal" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" title="Редактировать">
                <PencilIcon class="w-4 h-4" />
              </button>
              <button v-if="goal?.status !== 'archived'" @click="handleArchiveGoal" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" title="Архивировать">
                <ArchiveBoxArrowDownIcon class="w-4 h-4" />
              </button>
              <button v-else @click="handleUnarchiveGoal" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" title="Восстановить">
                <ArrowUturnLeftIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Meta chips -->
          <div class="flex flex-wrap items-center gap-1.5">
            <span v-if="goalSphere" class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[12.5px] bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md">
              <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: goalSphere.color }"></span>
              {{ goalSphere.name }}
            </span>
            <span v-if="goal?.deadline" class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[12.5px] bg-gray-100 dark:bg-gray-800 rounded-md" :class="deadlineClass">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              {{ formatDeadline(goal.deadline) }}
            </span>
            <span v-if="goal?.status === 'archived'" class="px-2 py-0.5 text-[12.5px] bg-gray-200 dark:bg-gray-700 text-gray-500 rounded-md">Архив</span>
          </div>

          <!-- Description -->
          <p v-if="goal?.description" class="text-[15px] text-gray-700 dark:text-gray-300 leading-relaxed">
            {{ goal.description }}
          </p>

          <!-- Bible verse -->
          <p v-if="goal?.bible_verse" class="text-sm italic text-gray-500 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-3">
            {{ goal.bible_verse }}
          </p>

          <!-- Progress -->
          <div v-if="allTasks.length > 0" class="flex items-center gap-2">
            <div class="flex-1 h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
              <div class="h-full bg-emerald-500 transition-all" :style="{ width: progressPct + '%' }"></div>
            </div>
            <span class="text-[11px] text-gray-400">{{ allCompletedCount }}/{{ allTasks.length }}</span>
          </div>

          <template v-if="goal">
            <!-- Direct tasks -->
            <div v-if="activeTasks.length > 0 || completedTasks.length > 0">
              <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Задачи
              </div>
              <TaskList v-if="activeTasks.length > 0" :tasks="activeTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
              <div v-if="completedTasks.length > 0" class="mt-3 opacity-60">
                <TaskList :tasks="completedTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
              </div>
            </div>

            <div v-if="goalProjects.length === 0 && allTasks.length === 0" class="py-10 text-center text-sm text-gray-400">
              В цели пока нет задач и потоков. Добавьте поток справа или создайте задачу.
            </div>
          </template>
        </div>

        <!-- RIGHT: sidebar в стиле TaskDetail -->
        <aside class="lg:sticky lg:top-4 self-start space-y-5 bg-gray-50/60 dark:bg-gray-800/30 rounded-lg p-4">
          <!-- Участники -->
          <div>
            <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              Участники
            </div>
            <div class="flex flex-wrap gap-1 mb-2" v-if="participantContacts.length">
              <span
                v-for="c in participantContacts"
                :key="c.id"
                class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded text-[11.5px]"
              >
                {{ c.name }}
                <button @click="removeParticipant(c.id)" class="hover:text-red-500">✕</button>
              </span>
            </div>
            <div class="relative">
              <button
                @click="pickerOpen = !pickerOpen"
                class="text-[12.5px] text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Добавить участника
              </button>
              <div
                v-if="pickerOpen"
                class="absolute left-0 top-full mt-1 z-20 w-full min-w-[220px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-1 max-h-60 overflow-y-auto thin-scroll"
              >
                <button
                  v-for="c in availableContacts"
                  :key="c.id"
                  @click="addParticipant(c.id)"
                  class="w-full text-left px-2.5 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 truncate"
                >
                  {{ c.name }}
                </button>
                <div v-if="!availableContacts.length" class="px-2.5 py-2 text-xs text-gray-400 text-center">
                  Все контакты уже добавлены
                </div>
              </div>
            </div>
          </div>

          <!-- Потоки -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6v12M9 4v16M14 8v8M19 5v14" /></svg>
                Потоки
              </div>
              <button @click="openProjectModal" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Добавить поток">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
              </button>
            </div>
            <div v-if="goalProjects.length === 0" class="text-xs text-gray-400 py-1">Потоков нет</div>
            <div v-else class="space-y-1">
              <div
                v-for="project in goalProjects"
                :key="project.id"
                class="group flex items-center gap-2 px-2 py-1.5 rounded hover:bg-white/60 dark:hover:bg-gray-800/60 cursor-pointer"
                @click="openProject(project)"
              >
                <div class="flex-1 min-w-0">
                  <div class="text-[13px] text-gray-800 dark:text-gray-200 truncate">{{ project.name }}</div>
                  <div v-if="project.tasks?.length" class="flex items-center gap-1.5 mt-0.5">
                    <div class="flex-1 h-0.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                      <div class="h-full bg-emerald-500" :style="{ width: projectProgress(project) + '%' }"></div>
                    </div>
                    <span class="text-[10px] text-gray-400">
                      {{ project.tasks.filter(t => t.completed_at).length }}/{{ project.tasks.length }}
                    </span>
                  </div>
                </div>
                <button
                  @click.stop="detachProject(project)"
                  class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500"
                  title="Отвязать от цели"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
          </div>
        </aside>

        <!-- Task view -->
        <TaskView
          :show="showTaskView"
          :task="selectedTask"
          @close="showTaskView = false; selectedTask = null"
          @complete-task="handleCompleteTask"
          @uncomplete-task="handleUncompleteTask"
        />

        <!-- Goal modal -->
        <GoalModal
          :show="showGoalModal"
          :goal="goal"
          :server-error="goalError"
          @close="handleCloseGoalModal"
          @submit="handleSaveGoal"
        />

        <!-- Project add dialog -->
        <div v-if="showProjectPicker" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" @click.self="showProjectPicker = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-sm p-1">
            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800 text-[11px] uppercase tracking-wider text-gray-400">
              Поток
            </div>
            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
              <div class="flex items-center gap-2">
                <input
                  v-model="newProjectName"
                  @keydown.enter.prevent="createAndAttachProject"
                  placeholder="Новый поток"
                  class="flex-1 px-2.5 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600"
                />
                <button
                  @click="createAndAttachProject"
                  :disabled="!newProjectName.trim() || creatingProject"
                  class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded disabled:opacity-40 hover:opacity-90"
                >
                  Создать
                </button>
              </div>
            </div>
            <div class="max-h-60 overflow-y-auto thin-scroll p-1">
              <div class="px-2 pt-1 pb-0.5 text-[10px] uppercase tracking-wider text-gray-400">Или привязать существующий</div>
              <button
                v-for="p in availableProjects"
                :key="p.id"
                @click="attachProject(p)"
                class="w-full text-left px-2.5 py-1.5 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 truncate"
              >
                {{ p.name }}
              </button>
              <div v-if="!availableProjects.length" class="px-2.5 py-2 text-xs text-gray-400 text-center">
                Свободных потоков нет
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGoalsStore } from '@/stores/goals'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import { useConfirmStore } from '@/stores/confirm'
import { PencilIcon, ArchiveBoxArrowDownIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import GoalModal from '@/components/goals/GoalModal.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const goalsStore = useGoalsStore()
const tasksStore = useTasksStore()
const projectsStore = useProjectsStore()
const confirmStore = useConfirmStore()

const showTaskView = ref(false)
const showGoalModal = ref(false)
const showProjectPicker = ref(false)
const selectedTask = ref(null)
const goalError = ref('')
const participants = ref([])
const allContacts = ref([])
const pickerOpen = ref(false)

const currentGoalId = computed(() => parseInt(route.params.goalId))
const goal = computed(() => goalsStore.allGoals.find(g => g.id === currentGoalId.value) || null)

const goalProjects = computed(() =>
  projectsStore.allProjects
    .filter(p => p.goal_id === currentGoalId.value)
    .map(p => ({ ...p, tasks: tasksStore.allTasks.filter(t => t.project_id === p.id) }))
)
const availableProjects = computed(() =>
  projectsStore.allProjects.filter(p => p.goal_id !== currentGoalId.value && p.status !== 'archived')
)

const goalSphere = computed(() => goal.value?.life_sphere || null)

const directTasks = computed(() => tasksStore.allTasks.filter(t => t.goal_id === currentGoalId.value && !t.project_id))
const activeTasks = computed(() => directTasks.value.filter(t => !t.completed_at))
const completedTasks = computed(() => directTasks.value.filter(t => t.completed_at))

const allTasks = computed(() => tasksStore.allTasks.filter(t =>
  t.goal_id === currentGoalId.value || goalProjects.value.some(p => p.id === t.project_id)
))
const allCompletedCount = computed(() => allTasks.value.filter(t => t.completed_at).length)
const progressPct = computed(() => allTasks.value.length ? Math.round(allCompletedCount.value / allTasks.value.length * 100) : 0)

const participantContacts = computed(() => {
  const set = new Set(participants.value)
  return allContacts.value.filter(c => set.has(c.id))
})
const availableContacts = computed(() => {
  const set = new Set(participants.value)
  return allContacts.value.filter(c => !set.has(c.id))
})

const projectProgress = (p) => {
  if (!p.tasks?.length) return 0
  return Math.round(p.tasks.filter(t => t.completed_at).length / p.tasks.length * 100)
}

const formatDeadline = (s) => {
  if (!s) return ''
  const d = new Date(s); const today = new Date()
  today.setHours(0,0,0,0); d.setHours(0,0,0,0)
  const days = Math.ceil((d - today) / 86400000)
  if (days < 0) return `Просрочено на ${Math.abs(days)} дн.`
  if (days === 0) return 'Сегодня'
  if (days === 1) return 'Завтра'
  if (days <= 30) return `${days} дн.`
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}
const deadlineClass = computed(() => {
  if (!goal.value?.deadline) return 'text-gray-500 dark:text-gray-400'
  const d = new Date(goal.value.deadline); const today = new Date()
  today.setHours(0,0,0,0); d.setHours(0,0,0,0)
  const days = Math.ceil((d - today) / 86400000)
  if (days < 0) return 'text-red-500 dark:text-red-400'
  if (days <= 3) return 'text-orange-500 dark:text-orange-400'
  if (days <= 7) return 'text-yellow-600 dark:text-yellow-400'
  return 'text-gray-500 dark:text-gray-400'
})

const loadGoalData = async () => {
  if (!currentGoalId.value) return
  try {
    const res = await api.get(`/v1/goals/${currentGoalId.value}`)
    participants.value = (res.data?.contacts || []).map(c => c.id)
  } catch (e) { console.error(e) }
}
const loadContacts = async () => {
  try {
    const res = await api.get('/v1/contacts')
    allContacts.value = res.data || []
  } catch (e) { allContacts.value = [] }
}

onMounted(async () => {
  await Promise.all([loadGoalData(), loadContacts()])
})

const saveParticipants = async () => {
  try {
    await api.put(`/v1/goals/${currentGoalId.value}`, { contact_ids: participants.value })
  } catch (e) { console.error(e) }
}
const addParticipant = (id) => {
  if (!participants.value.includes(id)) {
    participants.value.push(id)
    saveParticipants()
  }
  pickerOpen.value = false
}
const removeParticipant = async (id) => {
  const c = allContacts.value.find(x => x.id === id)
  const ok = await confirmStore.ask({ title: 'Убрать участника?', message: c?.name || '', confirmText: 'Убрать' })
  if (!ok) return
  participants.value = participants.value.filter(x => x !== id)
  saveParticipants()
}

const newProjectName = ref('')
const creatingProject = ref(false)

const attachProject = async (project) => {
  await projectsStore.updateProject(project.id, { goal_id: currentGoalId.value })
  showProjectPicker.value = false
}

const createAndAttachProject = async () => {
  const name = newProjectName.value.trim()
  if (!name) return
  creatingProject.value = true
  try {
    await projectsStore.createProject({
      name,
      goal_id: currentGoalId.value,
      life_sphere_id: goal.value?.life_sphere_id || null,
    })
    newProjectName.value = ''
    showProjectPicker.value = false
  } catch (e) {
    console.error('Ошибка создания потока', e)
  } finally {
    creatingProject.value = false
  }
}
const detachProject = async (project) => {
  const ok = await confirmStore.ask({ title: 'Отвязать поток от цели?', message: project.name, confirmText: 'Отвязать' })
  if (!ok) return
  await projectsStore.updateProject(project.id, { goal_id: null })
}
const openProjectModal = () => { showProjectPicker.value = true }
const openProject = (project) => router.push(`/projects/${project.id}`)

const handleEditGoal = () => { showGoalModal.value = true }

const handleArchiveGoal = async () => {
  const ok = await confirmStore.ask({ title: 'Архивировать цель?', message: goal.value?.name || '', confirmText: 'Архивировать' })
  if (!ok) return
  await goalsStore.updateGoal(goal.value.id, { status: 'archived' })
  router.push('/goals')
}
const handleUnarchiveGoal = async () => {
  await goalsStore.updateGoal(goal.value.id, { status: 'active' })
}

const handleTaskClick = (task) => { selectedTask.value = task; showTaskView.value = true }
const handleToggleComplete = async (task) => {
  if (task.completed_at) await tasksStore.uncompleteTask(task.id)
  else await tasksStore.completeTask(task.id)
}
const handleCompleteTask = (task) => tasksStore.completeTask(task.id)
const handleUncompleteTask = (task) => tasksStore.uncompleteTask(task.id)

const handleSaveGoal = async (goalData) => {
  goalError.value = ''
  try {
    await goalsStore.updateGoal(goal.value.id, goalData)
    showGoalModal.value = false
    await goalsStore.fetchAllGoals({ force: true })
  } catch (error) {
    goalError.value = error.response?.data?.message || 'Ошибка сохранения'
  }
}
const handleCloseGoalModal = () => { showGoalModal.value = false; goalError.value = '' }
</script>

<style scoped>
.thin-scroll { scrollbar-width: thin; scrollbar-color: rgba(156,163,175,0.35) transparent; }
.thin-scroll::-webkit-scrollbar { width: 6px; }
.thin-scroll::-webkit-scrollbar-thumb { background-color: rgba(156,163,175,0.25); border-radius: 3px; }
.thin-scroll::-webkit-scrollbar-thumb:hover { background-color: rgba(156,163,175,0.5); }
</style>
