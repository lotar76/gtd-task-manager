<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
              <h3 class="text-xl font-semibold text-gray-900">
                {{ task ? 'Редактировать задачу' : 'Новая задача' }}
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Body -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
              <!-- Completed Toggle -->
              <div class="flex items-center space-x-3 pb-4 border-b border-gray-200">
                <label class="flex items-center space-x-2 cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="isCompleted"
                    @change="handleToggleCompleted"
                    class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                  />
                  <span class="text-sm font-medium text-gray-700">
                    {{ isCompleted ? 'Задача выполнена' : 'Отметить как выполненную' }}
                  </span>
                </label>
              </div>

              <!-- Title -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
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
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
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
                  <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Статус
                  </label>
                  <div class="relative">
                    <button
                      type="button"
                      @click="statusDropdownOpen = !statusDropdownOpen"
                      class="input w-full flex items-center justify-between"
                    >
                      <span class="flex items-center space-x-2">
                        <component :is="statusOptions[form.status].icon" class="w-5 h-5 text-gray-500" />
                        <span>{{ statusOptions[form.status].label }}</span>
                      </span>
                      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </button>
                    <div
                      v-if="statusDropdownOpen"
                      class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg"
                    >
                      <button
                        v-for="(option, key) in statusOptions"
                        :key="key"
                        type="button"
                        @click="selectStatus(key)"
                        class="w-full flex items-center space-x-2 px-3 py-2 text-sm hover:bg-gray-50 first:rounded-t-lg last:rounded-b-lg transition-colors"
                        :class="form.status === key ? 'bg-primary-50 text-primary-700' : 'text-gray-700'"
                      >
                        <component :is="option.icon" class="w-5 h-5" />
                        <span>{{ option.label }}</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Priority -->
                <div>
                  <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
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
                  <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
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
                  <label for="estimated_time" class="block text-sm font-medium text-gray-700 mb-2">
                    Время выполнения
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

              <!-- Workspace -->
              <div v-if="workspaces.length > 1">
                <label for="workspace_id" class="block text-sm font-medium text-gray-700 mb-2">
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
                <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                  Проект
                </label>
                <select
                  id="project_id"
                  v-model="form.project_id"
                  class="input"
                >
                  <option :value="null">Без проекта</option>
                  <option
                    v-for="project in activeProjects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.name }}
                  </option>
                </select>
              </div>

              <!-- Error Message -->
              <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">
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
})

const emit = defineEmits(['close', 'submit'])

const route = useRoute()
const workspaceStore = useWorkspaceStore()
const projectsStore = useProjectsStore()
const workspaces = computed(() => workspaceStore.workspaces)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)
const activeProjects = computed(() => projectsStore.activeProjects)

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
    // Для "Сегодня" автоматически ставим сегодняшнюю дату
    const today = new Date()
    return today.toISOString().split('T')[0]
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
  workspace_id: null,
  project_id: null,
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
}

const selectStatus = (status) => {
  form.value.status = status
  statusDropdownOpen.value = false
  
  // Если выбран статус "Сегодня", автоматически ставим сегодняшнюю дату
  if (status === 'today' && !form.value.due_date) {
    const today = new Date()
    form.value.due_date = today.toISOString().split('T')[0]
  }
}

// Computed свойство для определения, выполнена ли задача
const isCompleted = computed(() => form.value.status === 'completed')

// Обработчик переключения статуса выполнения
const handleToggleCompleted = (event) => {
  const checked = event.target.checked
  
  if (checked) {
    // Отмечаем как выполненную - сохраняем текущий статус и меняем на completed
    if (form.value.status !== 'completed') {
      previousStatus.value = form.value.status
      form.value.status = 'completed'
    }
  } else {
    // Восстанавливаем предыдущий статус или ставим inbox по умолчанию
    form.value.status = previousStatus.value || 'inbox'
    previousStatus.value = null
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
      workspace_id: currentWorkspace.value?.id,
      project_id: null,
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
    
    // Если задача выполнена, сохраняем предыдущий статус из completed_at или используем inbox
    if (newTask.status === 'completed') {
      // Пытаемся восстановить предыдущий статус из истории или используем inbox
      previousStatus.value = newTask.previous_status || 'inbox'
    } else {
      // Сохраняем текущий статус как предыдущий на случай если отметят как выполненную
      previousStatus.value = newTask.status || 'inbox'
    }
    
    form.value = {
      title: newTask.title || '',
      description: newTask.description || '',
      status: newTask.status || 'inbox',
      priority: newTask.priority || 'medium',
      due_date: formatDateForInput(newTask.due_date),
      estimated_time: formatTimeForInput(newTask.estimated_time),
      workspace_id: newTask.workspace_id || currentWorkspace.value?.id,
      project_id: newTask.project_id || null,
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
      workspace_id: currentWorkspace.value?.id,
      project_id: null,
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
      form.value.due_date = getDefaultDueDateFromRoute()
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
      workspace_id: currentWorkspace.value?.id,
      project_id: null,
    }
    previousStatus.value = null
    statusDropdownOpen.value = false
    loading.value = false
    error.value = ''
  }
})

// Загружаем проекты при открытии модалки
watch(() => props.show, (newShow) => {
  if (newShow && currentWorkspace.value?.id) {
    projectsStore.fetchProjects()
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

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
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

