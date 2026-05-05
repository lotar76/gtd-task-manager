<template>
  <div class="p-4 lg:p-8">
    <div>
      <!-- Breadcrumbs -->
      <nav class="flex items-center text-sm text-gray-400 dark:text-gray-500 mb-4 min-w-0">
        <router-link to="/projects" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex-shrink-0">Потоки</router-link>
        <ChevronRightIcon class="w-3.5 h-3.5 mx-1.5 flex-shrink-0" />
        <span class="text-gray-700 dark:text-gray-200 font-medium truncate">{{ project?.name || '...' }}</span>
      </nav>

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3 min-w-0 flex-1">
          <div class="min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
              <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white truncate">
                {{ project?.name || 'Загрузка...' }}
              </h1>
              <router-link
                v-if="projectGoal"
                :to="`/goals/${projectGoal.id}`"
                class="hidden lg:inline-flex items-center gap-1.5 px-2 py-0.5 text-[12.5px] bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-700"
              >
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-4a4 4 0 100 8 4 4 0 000-8z" /></svg>
                {{ projectGoal.name }}
              </router-link>
              <span v-if="allTasks.length > 0" class="text-sm text-gray-400 dark:text-gray-500 flex-shrink-0 hidden lg:inline">
                {{ allTasks.length }}
              </span>
            </div>
            <!-- Mobile: goal + sphere -->
            <div v-if="projectGoal || projectSphere" class="lg:hidden flex items-center gap-2 mt-1 text-[11px] text-gray-400 dark:text-gray-500 min-w-0">
              <router-link
                v-if="projectGoal"
                :to="`/goals/${projectGoal.id}`"
                class="inline-flex items-center gap-1 min-w-0 hover:text-gray-600 dark:hover:text-gray-300"
              >
                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="1.8" /><circle cx="12" cy="12" r="4" stroke-width="1.8" /></svg>
                <span class="truncate">{{ projectGoal.name }}</span>
              </router-link>
              <template v-if="projectSphere">
                <span v-if="projectGoal" class="flex-shrink-0">·</span>
                <router-link :to="`/spheres/${projectSphere.id}`" class="inline-flex items-center gap-1 flex-shrink-0 hover:text-gray-600 dark:hover:text-gray-300">
                  <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: projectSphere.color }"></span>
                  {{ projectSphere.name }}
                </router-link>
              </template>
            </div>
            <p v-if="project?.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">
              {{ project.description }}
            </p>
            <!-- Progress cubes -->
            <div v-if="allTasks.length > 0" class="hidden lg:flex items-center gap-1 mt-2" :title="`${completedTasks.length} / ${allTasks.length} выполнено`">
              <template v-if="allTasks.length <= 20">
                <div
                  v-for="i in allTasks.length"
                  :key="i"
                  class="w-2.5 h-2.5 rounded-sm"
                  :class="i <= completedTasks.length ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                ></div>
              </template>
              <template v-else>
                <div
                  v-for="i in 20"
                  :key="i"
                  class="w-2.5 h-2.5 rounded-sm"
                  :class="i <= Math.round(completedTasks.length / allTasks.length * 20) ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                ></div>
                <span class="text-xs text-gray-400 ml-1">{{ completedTasks.length }}/{{ allTasks.length }}</span>
              </template>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-1 flex-shrink-0 ml-2">
          <button
            @click="handleEditProject"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Редактировать"
          >
            <PencilIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <!-- Project Tasks -->
      <template v-else>
        <!-- Empty State -->
        <div v-if="allTasks.length === 0" class="py-12 text-center">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6v12M9 4v16M14 8v8M19 5v14" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">В потоке пока нет задач</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
              Поток — это любая цель, требующая более одного действия.
              Разбейте его на конкретные шаги и добавьте первую задачу,
              чтобы начать двигаться к результату.
            </p>
            <button
              @click="handleCreateTask"
              class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium"
            >
              <PlusIcon class="w-5 h-5 mr-1.5" />
              Добавить задачу
            </button>
          </div>
        </div>

        <!-- Two-column layout: tasks + calendar -->
        <div v-else class="flex flex-col lg:flex-row gap-6 items-start">
          <!-- Tasks -->
          <div class="w-full lg:w-1/2 min-w-0 order-1 lg:order-1">
            <!-- Add task button -->
            <button
              v-if="sortedActiveTasks.length > 0"
              @click="handleCreateTask"
              class="flex items-center gap-1.5 mb-3 px-2 py-1.5 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <PlusIcon class="w-4 h-4" />
              Добавить задачу
            </button>

            <!-- Active Tasks -->
            <TaskList
              v-if="sortedActiveTasks.length > 0"
              :tasks="sortedActiveTasks"
              hide-project
              @task-click="handleTaskClick"
              @toggle-complete="handleToggleComplete"
              @touch-drop="handleTouchDrop"
            />

            <!-- No active tasks -->
            <div v-if="activeTasks.length === 0 && completedTasks.length > 0" class="py-10 text-center">
              <div class="max-w-sm mx-auto">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Нет активных задач</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 leading-relaxed">
                  Добавьте следующий шаг, чтобы двигаться дальше.
                </p>
                <button
                  @click="handleCreateTask"
                  class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium"
                >
                  <PlusIcon class="w-5 h-5 mr-1.5" />
                  Добавить задачу
                </button>
              </div>
            </div>

            <!-- Completed Tasks -->
            <div v-if="completedTasks.length > 0" class="mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
              <button
                @click="showCompleted = !showCompleted"
                class="flex items-center gap-2 w-full text-left px-1 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 8-4-4m5.5-7h5.3c1.12 0 1.68 0 2.11.22a2 2 0 01.87.87c.22.43.22.99.22 2.11v9.6c0 1.12 0 1.68-.22 2.11a2 2 0 01-.87.87c-.43.22-.99.22-2.11.22H6.8c-1.12 0-1.68 0-2.11-.22a2 2 0 01-.87-.87C3.6 18.48 3.6 17.92 3.6 16.8V7.2c0-1.12 0-1.68.22-2.11a2 2 0 01.87-.87C5.12 4 5.68 4 6.8 4h2.7" />
                </svg>
                <span class="text-sm text-gray-500 dark:text-gray-400">Выполненные</span>
                <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">{{ completedTasks.length }}</span>
                <svg
                  class="w-3.5 h-3.5 text-gray-400 ml-auto transition-transform"
                  :class="showCompleted ? 'rotate-180' : ''"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div v-if="showCompleted" class="mt-2 opacity-60">
                <TaskList
                  :tasks="completedTasks"
                  hide-project
                  @task-click="handleTaskClick"
                  @toggle-complete="handleToggleComplete"
                  @touch-drop="handleTouchDrop"
                />
              </div>
            </div>
          </div>

          <!-- Calendar -->
          <div class="w-full lg:w-1/2 lg:sticky lg:top-4 order-2 lg:order-2">
            <!-- Calendar filter -->
            <div class="flex items-center justify-end mb-2 gap-3">
              <span class="text-xs text-gray-400 dark:text-gray-500">показывать:</span>
              <button
                @click="filterOnlyProject = true"
                class="text-xs transition-colors"
                :class="filterOnlyProject ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
              >только поток</button>
              <span class="text-xs text-gray-300 dark:text-gray-600">·</span>
              <button
                @click="filterOnlyProject = false"
                class="text-xs transition-colors"
                :class="!filterOnlyProject ? 'text-primary-600 dark:text-primary-400 font-medium' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
              >все задачи</button>
            </div>

            <!-- Month nav -->
            <div class="flex items-center justify-between mb-3">
              <button @click="calendarMonth = calendarMonth.subtract(1, 'month')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
              </button>
              <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 capitalize">{{ calendarMonth.format('MMMM YYYY') }}</span>
              <button @click="calendarMonth = calendarMonth.add(1, 'month')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
              <!-- Days Header -->
              <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
                <div
                  v-for="d in ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']"
                  :key="d"
                  class="text-center py-2 text-xs font-medium text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 last:border-r-0"
                >{{ d }}</div>
              </div>

              <!-- Calendar Days -->
              <div class="grid grid-cols-7">
                <div
                  v-for="(day, idx) in calendarDays"
                  :key="idx"
                  :data-drop-date="day.date"
                  class="group/day relative min-h-[70px] border-b border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-1.5 cursor-pointer touch-manipulation transition-colors"
                  :class="[
                    getDayCellClass(day),
                    dragOverDate === day.date ? 'ring-2 ring-primary-400 ring-inset bg-primary-100 dark:bg-primary-900/40' : '',
                    selectedCalendarDate === day.date ? 'ring-2 ring-primary-500 ring-inset' : '',
                  ]"
                  @click="selectedCalendarDate = (selectedCalendarDate === day.date ? null : day.date)"
                  @dragover.prevent="handleCalendarDragOver($event, day.date)"
                  @dragleave="handleCalendarDragLeave"
                  @drop.prevent="handleCalendarDrop($event, day.date)"
                >
                  <div class="flex flex-col h-full">
                    <div class="flex justify-between items-start mb-0.5">
                      <span
                        class="text-xs font-medium"
                        :class="{
                          'text-gray-400 dark:text-gray-600': !day.currentMonth,
                          'text-primary-700 dark:text-primary-400': day.isToday,
                          'text-gray-900 dark:text-gray-100': day.currentMonth && !day.isToday
                        }"
                      >{{ day.day }}</span>
                      <span
                        v-if="day.taskCount > 0"
                        class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-semibold bg-primary-600 text-white rounded-full"
                      >{{ day.taskCount }}</span>
                    </div>
                  </div>

                  <!-- Tooltip with task names -->
                  <div
                    v-if="day.taskCount > 0"
                    class="hidden lg:block absolute left-1/2 -translate-x-1/2 bottom-full mb-2 z-50 pointer-events-none opacity-0 group-hover/day:opacity-100 transition-opacity duration-150"
                  >
                    <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg shadow-lg px-3 py-2 whitespace-nowrap max-w-[250px]">
                      <div v-for="task in day.tasks.slice(0, 8)" :key="task.id" class="truncate py-0.5">{{ task.title }}</div>
                      <div v-if="day.tasks.length > 8" class="text-gray-400 text-[10px] pt-1">+{{ day.tasks.length - 8 }}</div>
                      <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selected day tasks -->
            <Transition name="slide-down">
              <div v-if="selectedCalendarDate" class="mt-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center justify-between px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                  <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ dayjs(selectedCalendarDate).format('D MMMM YYYY') }}
                  </h4>
                  <div class="flex items-center gap-2">
                    <button
                      @click="handleCreateTaskForDay(selectedCalendarDate)"
                      class="w-6 h-6 flex items-center justify-center rounded-full text-gray-400 hover:text-green-500 hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors"
                      title="Добавить задачу"
                    >
                      <PlusIcon class="w-4 h-4" />
                    </button>
                    <button @click="selectedCalendarDate = null" class="text-gray-300 hover:text-gray-500 dark:text-gray-600 dark:hover:text-gray-400 transition-colors">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                  </div>
                </div>
                <div v-if="selectedDayTasks.length > 0" class="p-2">
                  <TaskList
                    :tasks="selectedDayTasks"
                    @task-click="handleTaskClick"
                    @toggle-complete="handleToggleComplete"
                    @touch-drop="handleTouchDrop"
                  />
                </div>
                <div v-else class="px-3 py-4 text-center text-sm text-gray-400 dark:text-gray-500">
                  Задач нет
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </template>

      <!-- Task View -->
      <TaskView
        :show="showTaskView"
        :task="selectedTask"
        @close="showTaskView = false; selectedTask = null"
        @complete-task="handleCompleteTask"
        @uncomplete-task="handleUncompleteTask"
      />

      <!-- Черновик новой задачи -->
      <TaskView
        :show="showDraft"
        :task="draftTask"
        @close="handleCloseDraft"
      />

      <!-- Project Modal -->
      <ProjectModal
        :show="showProjectModal"
        :project="project"
        :server-error="projectError"
        @close="handleCloseProjectModal"
        @submit="handleSaveProject"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
dayjs.locale('ru')
import { useRoute, useRouter } from 'vue-router'
import { useProjectsStore } from '@/stores/projects'
import { useTasksStore } from '@/stores/tasks'
import { useGoalsStore } from '@/stores/goals'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import { PencilIcon, ArchiveBoxArrowDownIcon, ArrowUturnLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import { PlusIcon } from '@heroicons/vue/24/solid'
import TaskList from '@/components/tasks/TaskList.vue'
import { useTaskDraft } from '@/composables/useTaskDraft'
import TaskView from '@/components/tasks/TaskView.vue'
import ProjectModal from '@/components/projects/ProjectModal.vue'

const route = useRoute()
const router = useRouter()
const projectsStore = useProjectsStore()
const tasksStore = useTasksStore()
const goalsStore = useGoalsStore()
const lifeSpheresStore = useLifeSpheresStore()

const project = ref(null)
const loading = ref(false)
const showTaskView = ref(false)
const showProjectModal = ref(false)
const selectedTask = ref(null)
const projectError = ref('')
const showCompleted = ref(false)
const calendarMonth = ref(dayjs())
const dragOverDate = ref(null)
const filterOnlyProject = ref(true)
const selectedCalendarDate = ref(null)

const allTasks = computed(() => {
  const projectId = parseInt(route.params.projectId)
  if (!projectId) return []
  return tasksStore.allTasks.filter(t => t.project_id === projectId)
})

const activeTasks = computed(() => allTasks.value.filter(t => !t.completed_at))
const completedTasks = computed(() => allTasks.value.filter(t => t.completed_at))

const sortedActiveTasks = computed(() => {
  const withDate = activeTasks.value.filter(t => t.due_date).sort((a, b) => a.due_date.localeCompare(b.due_date))
  const withoutDate = activeTasks.value.filter(t => !t.due_date)
  return [...withDate, ...withoutDate]
})

// Mini calendar
const calendarDays = computed(() => {
  const days = []
  const start = calendarMonth.value.startOf('month')
  const end = calendarMonth.value.endOf('month')
  const startDay = start.day() === 0 ? 6 : start.day() - 1

  for (let i = startDay - 1; i >= 0; i--) {
    const date = start.subtract(i + 1, 'day')
    days.push(makeDayObj(date, false))
  }
  for (let i = 0; i < end.date(); i++) {
    days.push(makeDayObj(start.add(i, 'day'), true))
  }
  const rem = 42 - days.length
  for (let i = 0; i < rem; i++) {
    days.push(makeDayObj(end.add(i + 1, 'day'), false))
  }
  return days
})

const calendarSourceTasks = computed(() => {
  if (filterOnlyProject.value) return activeTasks.value
  return tasksStore.allTasks.filter(t => !t.completed_at && t.due_date)
})

const selectedDayTasks = computed(() => {
  if (!selectedCalendarDate.value) return []
  const source = filterOnlyProject.value
    ? allTasks.value
    : tasksStore.allTasks
  return source.filter(t => t.due_date && dayjs(t.due_date).format('YYYY-MM-DD') === selectedCalendarDate.value)
})

const makeDayObj = (date, currentMonth) => {
  const dateStr = date.format('YYYY-MM-DD')
  const dayTasks = calendarSourceTasks.value.filter(t => t.due_date && dayjs(t.due_date).format('YYYY-MM-DD') === dateStr)
  return {
    day: date.date(),
    date: dateStr,
    currentMonth,
    isToday: date.isSame(dayjs(), 'day'),
    taskCount: dayTasks.length,
    tasks: dayTasks,
  }
}

const getDayCellClass = (day) => {
  const base = []
  if (day.isToday) base.push('ring-1 ring-inset ring-primary-300 dark:ring-primary-600')
  if (!day.currentMonth) {
    base.push('bg-gray-50/50 dark:bg-gray-900/30')
    return base.join(' ')
  }
  if (day.taskCount === 0) {
    base.push('hover:bg-gray-50 dark:hover:bg-gray-700/50')
  } else {
    base.push('bg-green-50/70 dark:bg-green-900/15 hover:bg-green-100/80 dark:hover:bg-green-900/25')
  }
  return base.join(' ')
}

const handleCalendarDragOver = (e, date) => {
  e.dataTransfer.dropEffect = 'move'
  dragOverDate.value = date
}

const handleCalendarDragLeave = () => {
  dragOverDate.value = null
}

const optimisticMoveToDate = (taskId, date) => {
  const index = tasksStore.allTasks.findIndex(t => t.id === taskId)
  if (index === -1) return
  const backup = { ...tasksStore.allTasks[index] }
  tasksStore.allTasks[index] = { ...backup, due_date: date }
  tasksStore.updateTask(taskId, { due_date: date }).catch(() => {
    const i = tasksStore.allTasks.findIndex(t => t.id === taskId)
    if (i !== -1) tasksStore.allTasks[i] = backup
  })
}

const handleCalendarDrop = (e, date) => {
  dragOverDate.value = null
  try {
    const task = JSON.parse(e.dataTransfer.getData('application/json'))
    if (task?.id) optimisticMoveToDate(task.id, date)
  } catch (err) {
    console.error('Drop error:', err)
  }
}

const handleTouchDrop = ({ task, date }) => {
  if (task?.id && date) optimisticMoveToDate(task.id, date)
}

const projectWorkspace = computed(() => null)

const loadProject = async () => {
  const projectId = parseInt(route.params.projectId)
  if (!projectId) return

  loading.value = true
  try {
    const projectData = await projectsStore.fetchProject(projectId)
    if (!projectData) {
      router.push('/projects')
      return
    }
    project.value = projectData
  } catch (error) {
    console.error('Error loading project:', error)
    if (error.response?.status === 404) {
      router.push('/projects')
    }
  } finally {
    loading.value = false
  }
}

const handleEditProject = () => {
  showProjectModal.value = true
}

const handleArchiveProject = async () => {
  if (!confirm(`Архивировать поток "${project.value?.name}"?`)) {
    return
  }

  try {
    await projectsStore.archiveProject(project.value.id)
    router.push('/projects')
  } catch (error) {
    console.error('Error archiving project:', error)
    alert('Ошибка при архивировании потока')
  }
}

const handleUnarchiveProject = async () => {
  try {
    await projectsStore.unarchiveProject(project.value.id)
    await loadProject()
  } catch (error) {
    console.error('Error unarchiving project:', error)
    alert('Ошибка при восстановлении потока')
  }
}

const { draftTask, showDraft, startDraft, closeDraft } = useTaskDraft(() => {
  tasksStore.fetchTasks?.()
})

const handleCreateTask = () => {
  if (!project.value) return
  startDraft({
    project_id: project.value.id,
    goal_id: project.value.goal_id || projectGoal.value?.id || null,
    life_sphere_id: project.value.life_sphere_id || projectGoal.value?.life_sphere_id || null,
  })
}

const handleCreateTaskForDay = (date) => {
  if (!project.value) return
  startDraft({
    project_id: project.value.id,
    goal_id: project.value.goal_id || projectGoal.value?.id || null,
    life_sphere_id: project.value.life_sphere_id || projectGoal.value?.life_sphere_id || null,
    due_date: date,
  })
}

const projectGoal = computed(() => {
  const gid = project.value?.goal_id
  if (!gid) return null
  return goalsStore.allGoals.find(g => g.id === gid) || null
})
const projectSphere = computed(() =>
  projectGoal.value?.life_sphere || (project.value?.life_sphere_id
    ? lifeSpheresStore.allSpheres.find(s => s.id === project.value.life_sphere_id)
    : null)
)
const handleCloseDraft = () => closeDraft()

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
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

const handleSaveProject = async (projectData) => {
  projectError.value = ''
  try {
    await projectsStore.updateProject(project.value.id, projectData)
    showProjectModal.value = false
    await loadProject()
  } catch (error) {
    console.error('Error saving project:', error)
    projectError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении потока'
  }
}

const handleCloseProjectModal = () => {
  showProjectModal.value = false
  projectError.value = ''
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

watch(() => [route.params.projectId, route.params.id], () => {
  loadProject()
}, { immediate: true })
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}
.slide-down-enter-from, .slide-down-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-6px);
}
.slide-down-enter-to, .slide-down-leave-from {
  opacity: 1;
  max-height: 600px;
}
</style>
