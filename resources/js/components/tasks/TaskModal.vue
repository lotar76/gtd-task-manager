<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ task?.id ? 'Редактировать задачу' : 'Новая задача' }}
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
              <!-- Title -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Название задачи *
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                  placeholder="Введите название задачи"
                />
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Описание
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400 resize-none"
                  placeholder="Добавьте описание"
                ></textarea>
              </div>

              <!-- Row: Status & Priority -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Status -->
                <div class="md:col-span-2">
                  <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Статус
                  </label>
                  <div class="relative">
                    <button
                      type="button"
                      @click="statusDropdownOpen = !statusDropdownOpen"
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400 flex items-center justify-between"
                    >
                      <span class="flex items-center space-x-2">
                        <component :is="statusOptions[form.status].icon" class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        <span>{{ statusOptions[form.status].label }}</span>
                      </span>
                      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                    <div
                      v-if="statusDropdownOpen"
                      class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg"
                    >
                      <button
                        v-for="(option, key) in statusOptions"
                        :key="key"
                        type="button"
                        @click="selectStatus(key)"
                        class="w-full flex items-center space-x-2 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-600 first:rounded-t-lg last:rounded-b-lg transition-colors"
                        :class="form.status === key ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'"
                      >
                        <component :is="option.icon" class="w-5 h-5" />
                        <span>{{ option.label }}</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Priority -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Приоритет
                  </label>
                  <div class="flex gap-2 justify-end">
                    <button
                      v-for="(option, key) in priorityOptions"
                      :key="key"
                      type="button"
                      @click="form.priority = key"
                      :title="option.label"
                      :class="[
                        'p-2 rounded-lg transition-all border-2',
                        form.priority === key
                          ? 'bg-white dark:bg-gray-700 border-current shadow-sm'
                          : 'border-transparent hover:bg-gray-100 dark:hover:bg-gray-700',
                        option.color
                      ]"
                    >
                      <component :is="option.icon" class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Row: Due Date, Start Time & End Time -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Due Date -->
                <div>
                  <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Срок выполнения
                  </label>
                  <input
                    id="due_date"
                    v-model="form.due_date"
                    type="date"
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                  />
                </div>

                <!-- Estimated Time -->
                <div>
                  <label for="estimated_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Время начала
                  </label>
                  <input
                    id="estimated_time"
                    v-model="form.estimated_time"
                    type="time"
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                    placeholder="12:30"
                  />
                </div>

                <!-- End Time -->
                <div>
                  <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Время окончания
                  </label>
                  <input
                    id="end_time"
                    v-model="form.end_time"
                    type="time"
                    class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                    placeholder="13:30"
                  />
                </div>
              </div>

              <!-- Workspace -->
              <div v-if="workspaces.length > 1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Рабочее пространство
                </label>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="ws in workspaces"
                    :key="ws.id"
                    type="button"
                    @click="form.workspace_id = ws.id"
                    :class="[
                      'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                      form.workspace_id === ws.id
                        ? 'bg-primary-600 text-white'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                  >
                    <span v-if="ws.emoji" class="mr-1">{{ ws.emoji }}</span>{{ ws.name }}
                  </button>
                </div>
              </div>

              <!-- Row: Project, Sphere & Goal -->
              <div class="grid grid-cols-3 gap-4 relative">
                <!-- Project -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Поток
                  </label>
                  <button
                    type="button"
                    @click="projectDropdownOpen = !projectDropdownOpen"
                    :class="[
                      'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors w-full',
                      selectedProject
                        ? 'bg-primary-600 text-white hover:bg-primary-700'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                  >
                    {{ selectedProject ? selectedProject.name : 'Без потока' }}
                  </button>
                </div>

                <!-- Life Sphere -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Сфера жизни
                  </label>
                  <button
                    type="button"
                    @click="sphereDropdownOpen = !sphereDropdownOpen"
                    :class="[
                      'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors w-full',
                      selectedSphere
                        ? 'bg-primary-600 text-white hover:bg-primary-700'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                  >
                    <span v-if="selectedSphere && selectedSphere.emoji" class="mr-1">{{ selectedSphere.emoji }}</span>
                    {{ selectedSphere ? selectedSphere.name : 'Без сферы' }}
                  </button>
                </div>

                <!-- Goal -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Цель
                  </label>
                  <button
                    type="button"
                    @click="goalDropdownOpen = !goalDropdownOpen"
                    :class="[
                      'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors w-full',
                      selectedGoal
                        ? 'bg-primary-600 text-white hover:bg-primary-700'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                    ]"
                  >
                    {{ selectedGoal ? selectedGoal.name : 'Без цели' }}
                  </button>
                </div>

                <!-- Project Popup (centered) -->
                <div
                  v-if="projectDropdownOpen"
                  class="fixed inset-0 z-50 flex items-center justify-center p-12"
                  @click.self="projectDropdownOpen = false"
                >
                  <!-- Backdrop -->
                  <div class="absolute inset-0 bg-black bg-opacity-30"></div>

                  <!-- Popup content -->
                  <div class="relative bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl p-3 max-h-[70vh] overflow-y-auto w-full max-w-lg">
                    <!-- Close button -->
                    <button
                      type="button"
                      @click="projectDropdownOpen = false"
                      class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>

                    <!-- Quick create input -->
                    <div class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-600 mt-6">
                      <div class="flex gap-2">
                        <input
                          v-model="newProjectName"
                          type="text"
                          placeholder="Создать новый поток..."
                          @keypress.enter="handleCreateProject"
                          class="flex-1 text-sm px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                        />
                        <button
                          type="button"
                          @click="handleCreateProject"
                          :disabled="!newProjectName.trim()"
                          class="p-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                      <!-- "Без потока" chip -->
                      <button
                        type="button"
                        @click="form.project_id = null; projectDropdownOpen = false"
                        :class="[
                          'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                          !form.project_id
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'
                        ]"
                      >
                        Без потока
                      </button>

                      <!-- Project chips -->
                      <button
                        v-for="project in activeProjects"
                        :key="project.id"
                        type="button"
                        @click="form.project_id = project.id; projectDropdownOpen = false"
                        :class="[
                          'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                          form.project_id === project.id
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'
                        ]"
                      >
                        {{ project.name }}
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Sphere Popup (centered) -->
                <div
                  v-if="sphereDropdownOpen"
                  class="fixed inset-0 z-50 flex items-center justify-center p-12"
                  @click.self="sphereDropdownOpen = false"
                >
                  <!-- Backdrop -->
                  <div class="absolute inset-0 bg-black bg-opacity-30"></div>

                  <!-- Popup content -->
                  <div class="relative bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl p-3 max-h-[70vh] overflow-y-auto w-full max-w-lg">
                    <!-- Close button -->
                    <button
                      type="button"
                      @click="sphereDropdownOpen = false"
                      class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>

                    <!-- Quick create input -->
                    <div class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-600 mt-6">
                      <div class="flex gap-2">
                        <input
                          v-model="newSphereName"
                          type="text"
                          placeholder="Создать новую сферу..."
                          @keypress.enter="handleCreateSphere"
                          class="flex-1 text-sm px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                        />
                        <button
                          type="button"
                          @click="handleCreateSphere"
                          :disabled="!newSphereName.trim()"
                          class="p-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                      <!-- "Без сферы" chip -->
                      <button
                        type="button"
                        @click="form.life_sphere_id = null; sphereDropdownOpen = false"
                        :class="[
                          'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                          !form.life_sphere_id
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'
                        ]"
                      >
                        Без сферы
                      </button>

                      <!-- Sphere chips -->
                      <button
                        v-for="sphere in workspaceSpheres"
                        :key="sphere.id"
                        type="button"
                        @click="form.life_sphere_id = sphere.id; sphereDropdownOpen = false"
                        :class="[
                          'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                          form.life_sphere_id === sphere.id
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'
                        ]"
                      >
                        <span v-if="sphere.emoji" class="mr-1">{{ sphere.emoji }}</span>
                        {{ sphere.name }}
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Goal Popup (centered) -->
                <div
                  v-if="goalDropdownOpen"
                  class="fixed inset-0 z-50 flex items-center justify-center p-12"
                  @click.self="goalDropdownOpen = false"
                >
                  <!-- Backdrop -->
                  <div class="absolute inset-0 bg-black bg-opacity-30"></div>

                  <!-- Popup content -->
                  <div class="relative bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-xl p-3 max-h-[70vh] overflow-y-auto w-full max-w-lg">
                    <!-- Close button -->
                    <button
                      type="button"
                      @click="goalDropdownOpen = false"
                      class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>

                    <!-- Quick create input -->
                    <div class="mb-3 pb-3 border-b border-gray-200 dark:border-gray-600 mt-6">
                      <div class="flex gap-2">
                        <input
                          v-model="newGoalName"
                          type="text"
                          placeholder="Создать новую цель..."
                          @keypress.enter="handleCreateGoal"
                          class="flex-1 text-sm px-3 py-2 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:outline-none focus:border-primary-500 dark:focus:border-primary-400"
                        />
                        <button
                          type="button"
                          @click="handleCreateGoal"
                          :disabled="!newGoalName.trim()"
                          class="p-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                          </svg>
                        </button>
                      </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                      <!-- "Без цели" chip -->
                      <button
                        type="button"
                        @click="form.goal_id = null; goalDropdownOpen = false"
                        :class="[
                          'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                          !form.goal_id
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'
                        ]"
                      >
                        Без цели
                      </button>

                      <!-- Goal chips -->
                      <button
                        v-for="goal in workspaceGoals"
                        :key="goal.id"
                        type="button"
                        @click="form.goal_id = goal.id; goalDropdownOpen = false"
                        :class="[
                          'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                          form.goal_id === goal.id
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-500'
                        ]"
                      >
                        {{ goal.name }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Error Message -->
              <div v-if="error" class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                {{ error }}
              </div>

              <!-- Actions -->
              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="$emit('close')"
                  class="btn btn-secondary"
                >
                  Отмена
                </button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="btn btn-primary"
                >
                  {{ loading ? 'Сохранение...' : 'Сохранить' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import {
  InboxIcon,
  BoltIcon,
  ClockIcon,
  ArchiveBoxIcon,
  CalendarIcon,
  ArrowDownIcon,
  MinusIcon,
  ArrowUpIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useWorkspaceStore } from '@/stores/workspace'
import { useProjectsStore } from '@/stores/projects'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import { useGoalsStore } from '@/stores/goals'
import { useRoute } from 'vue-router'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  task: {
    type: Object,
    default: null,
  },
  serverError: {
    type: String,
    default: '',
  },
  defaultDate: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'submit'])

const route = useRoute()
const workspaceStore = useWorkspaceStore()
const projectsStore = useProjectsStore()
const spheresStore = useLifeSpheresStore()
const goalsStore = useGoalsStore()
const workspaces = computed(() => workspaceStore.workspaces)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)

// Фильтруем проекты по workspace_id из формы (не по selectedWorkspaces!)
const activeProjects = computed(() => {
  const workspaceId = form.value.workspace_id
  if (!workspaceId) return []

  return projectsStore.allProjects
    .filter(p => p.workspace_id === workspaceId && (p.status === 'active' || !p.status))
})

const selectedProject = computed(() => {
  if (!form.value.project_id) return null
  return activeProjects.value.find(p => p.id === form.value.project_id)
})

// Сферы жизни текущего workspace
const workspaceSpheres = computed(() => {
  const workspaceId = form.value.workspace_id
  if (!workspaceId) return []
  return spheresStore.allSpheres.filter(s => s.workspace_id === workspaceId)
})

const selectedSphere = computed(() => {
  if (!form.value.life_sphere_id) return null
  return workspaceSpheres.value.find(s => s.id === form.value.life_sphere_id)
})

// Цели текущего workspace (активные)
const workspaceGoals = computed(() => {
  const workspaceId = form.value.workspace_id
  if (!workspaceId) return []
  return goalsStore.allGoals.filter(g => g.workspace_id === workspaceId && (g.status === 'active' || !g.status))
})

const selectedGoal = computed(() => {
  if (!form.value.goal_id) return null
  return workspaceGoals.value.find(g => g.id === form.value.goal_id)
})

// Определяем статус по текущему роуту
const getDefaultStatusFromRoute = () => {
  const path = route.path
  
  if (path.includes('/inbox')) return 'inbox'
  if (path.includes('/today')) return 'today'
  if (path.includes('/next-actions')) return 'next_action'
  if (path.includes('/tomorrow')) return 'tomorrow'
  if (path.includes('/waiting')) return 'waiting'
  if (path.includes('/someday')) return 'someday'
  
  return 'inbox' // По умолчанию
}

// Определяем дату по умолчанию
const getDefaultDueDateFromRoute = () => {
  const path = route.path

  if (path.includes('/today')) {
    return new Date().toISOString().split('T')[0]
  }

  if (path.includes('/tomorrow')) {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    return tomorrow.toISOString().split('T')[0]
  }

  return ''
}

const form = ref({
  title: '',
  description: '',
  status: 'inbox',
  priority: 'medium',
  due_date: '',
  estimated_time: '',
  end_time: '',
  workspace_id: null,
  project_id: null,
  goal_id: null,
  life_sphere_id: null,
  completed_at: null,
})

const loading = ref(false)
const error = ref('')
const statusDropdownOpen = ref(false)
const projectDropdownOpen = ref(false)
const sphereDropdownOpen = ref(false)
const goalDropdownOpen = ref(false)
const previousStatus = ref(null) // Храним предыдущий статус для восстановления

// Quick creation inputs
const newProjectName = ref('')
const newSphereName = ref('')
const newGoalName = ref('')

// Предустановленные цвета для быстрого создания
const PRESET_COLORS = [
  '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
  '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
  '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef',
  '#ec4899', '#f43f5e'
]

// Генерация случайного цвета
const getRandomColor = () => {
  return PRESET_COLORS[Math.floor(Math.random() * PRESET_COLORS.length)]
}

// Обработчики быстрого создания
const handleCreateProject = async () => {
  if (!newProjectName.value.trim()) return

  try {
    const newProject = await projectsStore.createProject({
      name: newProjectName.value.trim(),
      workspace_id: form.value.workspace_id,
      color: getRandomColor(),
      status: 'active',
    })

    // Выбираем созданный проект
    form.value.project_id = newProject.id
    // Закрываем попап
    projectDropdownOpen.value = false
    // Очищаем поле
    newProjectName.value = ''
  } catch (err) {
    console.error('Error creating project:', err)
    error.value = 'Ошибка при создании потока'
  }
}

const handleCreateSphere = async () => {
  if (!newSphereName.value.trim()) return

  try {
    const newSphere = await spheresStore.create(form.value.workspace_id, {
      name: newSphereName.value.trim(),
      color: getRandomColor(),
    })

    // Выбираем созданную сферу
    form.value.life_sphere_id = newSphere.id
    // Закрываем попап
    sphereDropdownOpen.value = false
    // Очищаем поле
    newSphereName.value = ''
  } catch (err) {
    console.error('Error creating sphere:', err)
    error.value = 'Ошибка при создании сферы'
  }
}

const handleCreateGoal = async () => {
  if (!newGoalName.value.trim()) return

  try {
    const newGoal = await goalsStore.createGoal({
      name: newGoalName.value.trim(),
      workspace_id: form.value.workspace_id,
      color: getRandomColor(),
      status: 'active',
    })

    // Выбираем созданную цель
    form.value.goal_id = newGoal.id
    // Закрываем попап
    goalDropdownOpen.value = false
    // Очищаем поле
    newGoalName.value = ''
  } catch (err) {
    console.error('Error creating goal:', err)
    error.value = 'Ошибка при создании цели'
  }
}

// Опции статуса с иконками из сайдбара
const statusOptions = {
  inbox: { label: 'Входящие', icon: InboxIcon },
  next_action: { label: 'Следующие', icon: BoltIcon },
  today: { label: 'Сегодня', icon: CalendarIcon },
  tomorrow: { label: 'Завтра', icon: CalendarIcon },
  waiting: { label: 'Ожидание', icon: ClockIcon },
  someday: { label: 'Когда-нибудь', icon: ArchiveBoxIcon },
  scheduled: { label: 'Запланировано', icon: CalendarIcon },
}

// Опции приоритета с иконками
const priorityOptions = {
  low: { label: 'Низкий', icon: ArrowDownIcon, color: 'text-gray-500' },
  medium: { label: 'Средний', icon: MinusIcon, color: 'text-blue-500' },
  high: { label: 'Высокий', icon: ArrowUpIcon, color: 'text-orange-500' },
  urgent: { label: 'Срочный', icon: ExclamationTriangleIcon, color: 'text-red-500' },
}

const selectStatus = (status) => {
  form.value.status = status
  statusDropdownOpen.value = false
  
  // Если выбран статус "Сегодня", автоматически ставим сегодняшнюю дату
  if (status === 'today') {
    const today = new Date()
    form.value.due_date = today.toISOString().split('T')[0]
  }
  // Если выбран статус "Завтра", автоматически ставим завтрашнюю дату
  else if (status === 'tomorrow') {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    form.value.due_date = tomorrow.toISOString().split('T')[0]
  }
}

// Computed свойство для определения, выполнена ли задача
const isCompleted = computed(() => !!form.value.completed_at)

// Обработчик переключения статуса выполнения
const handleToggleCompleted = (event) => {
  const checked = event.target.checked

  if (checked) {
    // Отмечаем как выполненную - устанавливаем completed_at (не меняем status!)
    form.value.completed_at = new Date().toISOString()
  } else {
    // Возвращаем в работу - очищаем completed_at
    form.value.completed_at = null
  }
}

// Вспомогательная функция для преобразования даты в формат yyyy-MM-dd
const formatDateForInput = (date) => {
  if (!date) return ''
  // Если дата уже в нужном формате (yyyy-MM-dd), возвращаем как есть
  if (/^\d{4}-\d{2}-\d{2}$/.test(date)) return date
  // Иначе конвертируем из ISO формата
  return date.split('T')[0]
}

// Вспомогательная функция для преобразования времени в формат HH:mm
const formatTimeForInput = (time) => {
  if (!time) return ''
  // Если время уже в нужном формате (HH:mm), возвращаем как есть
  if (/^\d{2}:\d{2}$/.test(time)) return time
  // Иначе извлекаем HH:mm из формата HH:mm:ss
  return time.substring(0, 5)
}

// Заполняем форму если редактируем задачу
watch(() => props.task, (newTask, oldTask) => {
  // Если задача была, а теперь стала null - очищаем форму
  if (oldTask && !newTask) {
    form.value = {
      title: '',
      description: '',
      status: 'inbox',
      priority: 'medium',
      due_date: '',
      estimated_time: '',
      end_time: '',
      workspace_id: currentWorkspace.value?.id,
      project_id: null,
      goal_id: null,
      life_sphere_id: null,
      completed_at: null,
    }
    previousStatus.value = null
    error.value = ''
    statusDropdownOpen.value = false
    return
  }

  if (newTask) {
    // Редактируем существующую задачу
    form.value = {
      title: newTask.title || '',
      description: newTask.description || '',
      status: newTask.status || 'inbox',
      priority: newTask.priority || 'medium',
      due_date: formatDateForInput(newTask.due_date),
      estimated_time: formatTimeForInput(newTask.estimated_time),
      end_time: formatTimeForInput(newTask.end_time),
      workspace_id: newTask.workspace_id || currentWorkspace.value?.id,
      project_id: newTask.project_id || null,
      goal_id: newTask.goal_id || null,
      life_sphere_id: newTask.life_sphere_id || null,
      completed_at: newTask.completed_at || null,
    }
  } else {
    // Создаем новую задачу - подставляем текущий workspace, статус и дату из роута
    const defaultStatus = getDefaultStatusFromRoute()
    previousStatus.value = defaultStatus
    form.value = {
      title: '',
      description: '',
      status: defaultStatus,
      priority: 'medium',
      due_date: getDefaultDueDateFromRoute(),
      estimated_time: '',
      end_time: '',
      workspace_id: currentWorkspace.value?.id,
      project_id: null,
      goal_id: null,
      life_sphere_id: null,
      completed_at: null,
    }
  }
  error.value = ''
  statusDropdownOpen.value = false
}, { immediate: true })

// Отслеживаем ошибки с сервера
watch(() => props.serverError, (newError) => {
  error.value = newError
  if (newError) {
    loading.value = false
  }
})

// При открытии/закрытии модалки
watch(() => props.show, (newShow) => {
  if (newShow) {
    // При открытии модалки - форма уже заполнена через watch на props.task
    // Дополнительно обновляем значения из URL если это новая задача
    if (!props.task) {
      form.value.workspace_id = currentWorkspace.value?.id
      form.value.status = getDefaultStatusFromRoute()
      form.value.due_date = props.defaultDate || getDefaultDueDateFromRoute()
    } else if (!form.value.workspace_id) {
      form.value.workspace_id = currentWorkspace.value?.id
    }
  } else {
    // При закрытии модалки всегда полностью очищаем форму
    form.value = {
      title: '',
      description: '',
      status: 'inbox',
      priority: 'medium',
      due_date: '',
      estimated_time: '',
      end_time: '',
      workspace_id: currentWorkspace.value?.id,
      project_id: null,
      goal_id: null,
      life_sphere_id: null,
      completed_at: null,
    }
    previousStatus.value = null
    statusDropdownOpen.value = false
    projectDropdownOpen.value = false
    sphereDropdownOpen.value = false
    goalDropdownOpen.value = false
    loading.value = false
    error.value = ''
  }
})

// Загружаем проекты, сферы и цели при открытии модалки (если еще не загружены)
watch(() => props.show, (newShow) => {
  if (newShow) {
    if (!projectsStore.loaded) projectsStore.fetchAllProjects()
    if (!spheresStore.loaded) spheresStore.fetchAll()
    if (!goalsStore.loaded) goalsStore.fetchAllGoals()
  }
})

// Следим за изменениями URL для обновления статуса и даты (только для новой задачи)
watch(() => route.path, () => {
  if (!props.task) {
    // Обновляем статус и дату по URL, НЕ трогая остальные поля
    form.value.status = getDefaultStatusFromRoute()
    
    // Обновляем дату только если переходим в/из папки "Сегодня"
    const newDate = getDefaultDueDateFromRoute()
    if (newDate || route.path.includes('/today')) {
      form.value.due_date = newDate
    }
  }
})

// Следим за изменениями workspace для обновления workspace_id
watch(() => currentWorkspace.value?.id, (newWorkspaceId) => {
  if (!props.task && newWorkspaceId) {
    form.value.workspace_id = newWorkspaceId
  }
})

// Очищаем поля ввода при закрытии попапов
watch(projectDropdownOpen, (isOpen) => {
  if (!isOpen) newProjectName.value = ''
})

watch(sphereDropdownOpen, (isOpen) => {
  if (!isOpen) newSphereName.value = ''
})

watch(goalDropdownOpen, (isOpen) => {
  if (!isOpen) newGoalName.value = ''
})

// Следим за изменениями workspace_id - обнуляем project_id если проект из другого workspace
watch(() => form.value.workspace_id, (newWorkspaceId, oldWorkspaceId) => {
  // Пропускаем если это первая инициализация или workspace не изменился
  if (!oldWorkspaceId || newWorkspaceId === oldWorkspaceId) return

  // Проверяем: принадлежит ли текущий проект новому workspace
  if (form.value.project_id) {
    const currentProject = projectsStore.allProjects.find(p => p.id === form.value.project_id)

    // Если проект из другого workspace - обнуляем
    if (currentProject && currentProject.workspace_id !== newWorkspaceId) {
      form.value.project_id = null
    }
  }
})

// Следим за изменениями due_date и автоматически меняем статус
watch(() => form.value.due_date, (newDueDate) => {
  if (!newDueDate || form.value.completed_at) return

  const today = new Date().toISOString().split('T')[0]
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  const tomorrowStr = tomorrow.toISOString().split('T')[0]

  // Если дата = сегодня - меняем статус на today
  if (newDueDate === today) {
    form.value.status = 'today'
  }
  // Если дата = завтра - меняем статус на tomorrow
  else if (newDueDate === tomorrowStr) {
    form.value.status = 'tomorrow'
  }
  // Если дата установлена, но не сегодня/завтра - меняем статус на scheduled
  else if (!['today', 'tomorrow', 'completed'].includes(form.value.status)) {
    form.value.status = 'scheduled'
  }
})


const handleSubmit = () => {
  error.value = ''
  loading.value = true
  emit('submit', form.value)
}

// Обработчик клика снаружи dropdown
const handleClickOutside = (event) => {
  // Проверяем клик вне любого dropdown
  const clickedInsideDropdown = event.target.closest('.relative')

  if (!clickedInsideDropdown) {
    statusDropdownOpen.value = false
    projectDropdownOpen.value = false
    sphereDropdownOpen.value = false
    goalDropdownOpen.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

