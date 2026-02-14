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
              <!-- Completed Toggle -->
              <div class="flex items-center space-x-3 pb-4 border-b border-gray-200 dark:border-gray-700">
                <label class="flex items-center space-x-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="isCompleted"
                    @change="handleToggleCompleted"
                    class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500 dark:bg-gray-700"
                  />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ isCompleted ? 'Задача выполнена' : 'Отметить как выполненную' }}
                  </span>
                </label>
              </div>

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
                  class="input"
                  :class="{ 'opacity-60': isCompleted }"
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
                  class="input"
                  placeholder="Добавьте описание"
                ></textarea>
              </div>

              <!-- Row: Status & Priority -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Status -->
                <div>
                  <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Статус
                  </label>
                  <div class="relative">
                    <button
                      type="button"
                      @click="statusDropdownOpen = !statusDropdownOpen"
                      class="input w-full flex items-center justify-between"
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
                  <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Приоритет
                  </label>
                  <select id="priority" v-model="form.priority" class="input">
                    <option value="low">Низкий</option>
                    <option value="medium">Средний</option>
                    <option value="high">Высокий</option>
                    <option value="urgent">Срочный</option>
                  </select>
                </div>
              </div>

              <!-- Row: Due Date & Estimated Time -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Due Date -->
              <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Срок выполнения
                </label>
                <input
                  id="due_date"
                  v-model="form.due_date"
                  type="date"
                  class="input"
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
                    class="input"
                    placeholder="12:30"
                  />
                </div>
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
                  class="input"
                  placeholder="13:30"
                />
              </div>

              <!-- Workspace -->
              <div v-if="workspaces.length > 1">
                <label for="workspace_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Рабочее пространство
                </label>
                <select
                  id="workspace_id"
                  v-model="form.workspace_id"
                  class="input"
                >
                  <option v-for="ws in workspaces" :key="ws.id" :value="ws.id">
                    {{ ws.name }}
                  </option>
                </select>
              </div>

              <!-- Project -->
              <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Поток
                </label>
                <select
                  id="project_id"
                  v-model="form.project_id"
                  class="input"
                >
                  <option :value="null">Без потока</option>
                  <option
                    v-for="project in activeProjects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.name }}
                  </option>
                </select>
              </div>

              <!-- Row: Sphere & Goal -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Life Sphere -->
                <div>
                  <label for="life_sphere_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Сфера жизни
                  </label>
                  <select
                    id="life_sphere_id"
                    v-model="form.life_sphere_id"
                    class="input"
                  >
                    <option :value="null">Без сферы</option>
                    <option
                      v-for="sphere in workspaceSpheres"
                      :key="sphere.id"
                      :value="sphere.id"
                    >
                      {{ sphere.emoji || '' }} {{ sphere.name }}
                    </option>
                  </select>
                </div>

                <!-- Goal -->
                <div>
                  <label for="goal_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Цель
                  </label>
                  <select
                    id="goal_id"
                    v-model="form.goal_id"
                    class="input"
                  >
                    <option :value="null">Без цели</option>
                    <option
                      v-for="goal in workspaceGoals"
                      :key="goal.id"
                      :value="goal.id"
                    >
                      {{ goal.name }}
                    </option>
                  </select>
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

// Сферы жизни текущего workspace
const workspaceSpheres = computed(() => {
  const workspaceId = form.value.workspace_id
  if (!workspaceId) return []
  return spheresStore.allSpheres.filter(s => s.workspace_id === workspaceId)
})

// Цели текущего workspace (активные)
const workspaceGoals = computed(() => {
  const workspaceId = form.value.workspace_id
  if (!workspaceId) return []
  return goalsStore.allGoals.filter(g => g.workspace_id === workspaceId && (g.status === 'active' || !g.status))
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
const previousStatus = ref(null) // Храним предыдущий статус для восстановления

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
    console.log('Task received:', newTask)
    console.log('Original due_date:', newTask.due_date)
    console.log('Formatted due_date:', formatDateForInput(newTask.due_date))

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
  if (statusDropdownOpen.value && !event.target.closest('.relative')) {
    statusDropdownOpen.value = false
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

