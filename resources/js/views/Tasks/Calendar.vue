<template>
  <div class="p-2 sm:p-4 lg:p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 lg:mb-6">
        <div>
          <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900">Календарь</h1>
        </div>
        
        <div class="flex items-center space-x-1 sm:space-x-2 mt-3 lg:mt-0 overflow-x-auto pb-2 lg:pb-0">
          <!-- View Mode Switcher -->
          <button
            @click="viewMode = 'day'"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'day' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 active:bg-gray-200'"
          >
            День
          </button>
          <button
            @click="viewMode = 'week'"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'week' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 active:bg-gray-200'"
          >
            Неделя
          </button>
          <button
            @click="viewMode = 'month'"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'month' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 active:bg-gray-200'"
          >
            Месяц
          </button>
        </div>
      </div>

      <!-- Calendar Navigation -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4 mb-4 lg:mb-6">
        <div class="flex items-center justify-between">
          <button @click="previousPeriod" class="p-2 sm:p-3 rounded-lg bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronLeftIcon class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" />
          </button>
          
          <h2 class="text-base sm:text-lg font-semibold text-gray-900 text-center px-2">
            {{ currentPeriodTitle }}
          </h2>
          
          <button @click="nextPeriod" class="p-2 sm:p-3 rounded-lg bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronRightIcon class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700" />
          </button>
        </div>
      </div>

      <!-- Month View -->
      <div v-if="viewMode === 'month'" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Days Header -->
        <div class="grid grid-cols-7 border-b border-gray-200">
          <div
            v-for="day in weekDays"
            :key="day"
            class="text-center py-2 sm:py-3 text-xs sm:text-sm font-medium text-gray-700 border-r border-gray-200 last:border-r-0"
          >
            {{ day }}
          </div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7">
          <div
            v-for="(day, index) in calendarDays"
            :key="index"
            @click="handleDayClick(day.date)"
            class="min-h-[60px] sm:min-h-[80px] lg:min-h-[100px] border-b border-r border-gray-200 last:border-r-0 p-1 sm:p-2 cursor-pointer touch-manipulation hover:bg-gray-50 active:bg-gray-100 transition-colors"
            :class="{
              'bg-gray-50': !day.currentMonth,
              'bg-primary-50 hover:bg-primary-100': day.isToday
            }"
          >
            <div class="flex flex-col h-full">
              <div class="flex justify-between items-start mb-1">
                <span
                  class="text-xs sm:text-sm font-medium"
                  :class="{
                    'text-gray-400': !day.currentMonth,
                    'text-primary-700': day.isToday,
                    'text-gray-900': day.currentMonth && !day.isToday
                  }"
                >
                  {{ day.day }}
                </span>
                <span
                  v-if="day.taskCount > 0"
                  class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-semibold bg-primary-600 text-white rounded-full"
                >
                  {{ day.taskCount }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Week View -->
      <div v-if="viewMode === 'week'" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Desktop: Grid Layout -->
        <div class="hidden lg:block">
          <!-- Days Header -->
          <div class="grid grid-cols-7 border-b border-gray-200">
            <div
              v-for="day in weekDays"
              :key="day"
              class="text-center py-3 text-sm font-medium text-gray-700 border-r border-gray-200 last:border-r-0"
            >
              {{ day }}
            </div>
          </div>

          <!-- Week Days -->
          <div class="grid grid-cols-7">
            <div
              v-for="(day, index) in weekDaysData"
              :key="index"
              class="min-h-[400px] border-b border-r border-gray-200 last:border-r-0 p-3"
              :class="{
                'bg-primary-50': day.isToday
              }"
            >
              <div class="mb-2">
                <span
                  class="text-sm font-medium"
                  :class="{
                    'text-primary-700': day.isToday,
                    'text-gray-900': !day.isToday
                  }"
                >
                  {{ day.day }} {{ day.monthName }}
                </span>
              </div>

              <!-- Tasks for this day -->
              <div class="space-y-2">
                <div
                  v-for="task in day.tasks"
                  :key="task.id"
                  @click="handleTaskClick(task)"
                  class="p-2 rounded cursor-pointer text-xs touch-manipulation"
                  :class="{
                    'bg-primary-100 text-primary-700 border-l-2 border-primary-500': task.status !== 'completed',
                    'bg-gray-100 text-gray-500 line-through': task.status === 'completed'
                  }"
                >
                  <div class="font-medium truncate">{{ task.title }}</div>
                  <div v-if="task.estimated_time" class="text-[10px] opacity-75 mt-1">
                    {{ formatTime(task.estimated_time) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile: Horizontal Scroll -->
        <div class="lg:hidden overflow-x-auto">
          <div class="flex min-w-max">
            <div
              v-for="(day, index) in weekDaysData"
              :key="index"
              class="w-[280px] min-w-[280px] border-r border-gray-200 last:border-r-0 p-3"
              :class="{
                'bg-primary-50': day.isToday
              }"
            >
              <div class="mb-3 sticky top-0 bg-inherit pb-2 border-b border-gray-200">
                <div class="text-xs text-gray-500 mb-1">{{ weekDays[index] }}</div>
                <span
                  class="text-base font-semibold"
                  :class="{
                    'text-primary-700': day.isToday,
                    'text-gray-900': !day.isToday
                  }"
                >
                  {{ day.day }} {{ day.monthName }}
                </span>
              </div>

              <!-- Tasks for this day -->
              <div class="space-y-2">
                <div
                  v-for="task in day.tasks"
                  :key="task.id"
                  @click="handleTaskClick(task)"
                  class="p-3 rounded-lg cursor-pointer touch-manipulation"
                  :class="{
                    'bg-primary-100 text-primary-700 border-l-4 border-primary-500': task.status !== 'completed',
                    'bg-gray-100 text-gray-500 line-through': task.status === 'completed'
                  }"
                >
                  <div class="font-medium text-sm mb-1">{{ task.title }}</div>
                  <div v-if="task.estimated_time" class="text-xs text-gray-600 mt-1">
                    ⏱ {{ formatTime(task.estimated_time) }}
                  </div>
                </div>
                <div v-if="day.tasks.length === 0" class="text-xs text-gray-400 text-center py-4">
                  Нет задач
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Day View -->
      <div v-if="viewMode === 'day'" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-3 sm:p-4 border-b border-gray-200">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900">
            {{ currentDate.format('D MMMM YYYY') }}
          </h3>
        </div>

        <div class="p-3 sm:p-4">
          <div class="space-y-2 sm:space-y-3">
            <div
              v-for="task in dayTasks"
              :key="task.id"
              @click="handleTaskClick(task)"
              class="p-3 sm:p-4 rounded-lg cursor-pointer border-l-4 touch-manipulation active:scale-[0.98] transition-transform"
              :class="{
                'bg-primary-50 border-primary-500': task.status !== 'completed',
                'bg-gray-50 border-gray-300 line-through': task.status === 'completed'
              }"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-sm sm:text-base text-gray-900 mb-1">{{ task.title }}</div>
                  <div v-if="task.description" class="text-xs sm:text-sm text-gray-600 mt-1 line-clamp-2">{{ task.description }}</div>
                  <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span v-if="task.estimated_time" class="text-xs text-gray-600 flex items-center">
                      <ClockIcon class="w-3 h-3 mr-1" />
                      {{ formatTime(task.estimated_time) }}
                    </span>
                    <span
                      class="text-xs px-2 py-0.5 rounded-full"
                      :class="{
                        'bg-red-100 text-red-700': task.priority === 'urgent',
                        'bg-primary-100 text-primary-700': task.priority === 'high',
                        'bg-yellow-100 text-yellow-700': task.priority === 'medium',
                        'bg-gray-100 text-gray-700': task.priority === 'low'
                      }"
                    >
                      {{ getPriorityLabel(task.priority) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="dayTasks.length === 0" class="text-center py-8 sm:py-12 text-gray-500 text-sm sm:text-base">
              Нет задач на этот день
            </div>
          </div>
        </div>
      </div>

      <!-- Task Modal -->
      <TaskModal
        :show="showTaskModal"
        :task="selectedTask"
        :server-error="taskError"
        @close="handleCloseTaskModal"
        @submit="handleSaveTask"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { useWorkspaceStore } from '@/stores/workspace'
import TaskModal from '@/components/tasks/TaskModal.vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
import { ChevronLeftIcon, ChevronRightIcon, ClockIcon } from '@heroicons/vue/24/outline'

dayjs.locale('ru')

const tasksStore = useTasksStore()
const workspaceStore = useWorkspaceStore()

const currentDate = ref(dayjs())
const tasks = ref([])
const loading = ref(false)
const showMyTasks = ref(false)
// По умолчанию показываем "Месяц" (на всех устройствах)
const viewMode = ref('month')
const showTaskModal = ref(false)
const selectedTask = ref(null)
const taskError = ref('')

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']

const currentMonthName = computed(() => currentDate.value.format('MMMM'))
const currentYear = computed(() => currentDate.value.format('YYYY'))

const currentPeriodTitle = computed(() => {
  if (viewMode.value === 'month') {
    return `${currentMonthName.value} ${currentYear.value}`
  } else if (viewMode.value === 'week') {
    const weekStart = currentDate.value.startOf('week')
    const weekEnd = weekStart.add(6, 'day') // Используем add(6, 'day') для консистентности
    if (weekStart.month() === weekEnd.month()) {
      return `${weekStart.format('D')} - ${weekEnd.format('D MMMM YYYY')}`
    } else {
      return `${weekStart.format('D MMM')} - ${weekEnd.format('D MMM YYYY')}`
    }
  } else {
    return currentDate.value.format('D MMMM YYYY')
  }
})

const weekDaysData = computed(() => {
  const weekStart = currentDate.value.startOf('week')
  const days = []
  
  for (let i = 0; i < 7; i++) {
    const date = weekStart.add(i, 'day')
    const dateString = date.format('YYYY-MM-DD')
    const dayTasks = tasks.value.filter(task => 
      task.due_date && 
      dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
      task.status !== 'completed'
    )
    
    days.push({
      day: date.date(),
      monthName: date.format('MMM'),
      date: dateString,
      isToday: date.isSame(dayjs(), 'day'),
      tasks: dayTasks,
    })
  }
  
  return days
})

const dayTasks = computed(() => {
  const dateString = currentDate.value.format('YYYY-MM-DD')
  return tasks.value.filter(task => 
    task.due_date && 
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed'
  )
})

const calendarDays = computed(() => {
  const days = []
  const startOfMonth = currentDate.value.startOf('month')
  const endOfMonth = currentDate.value.endOf('month')
  const startDay = startOfMonth.day() === 0 ? 6 : startOfMonth.day() - 1 // Понедельник = 0
  
  // Предыдущий месяц
  for (let i = startDay - 1; i >= 0; i--) {
    const date = startOfMonth.subtract(i + 1, 'day')
    days.push(createDayObject(date, false))
  }
  
  // Текущий месяц
  for (let i = 0; i < endOfMonth.date(); i++) {
    const date = startOfMonth.add(i, 'day')
    days.push(createDayObject(date, true))
  }
  
  // Следующий месяц
  const remainingDays = 42 - days.length // 6 недель
  for (let i = 0; i < remainingDays; i++) {
    const date = endOfMonth.add(i + 1, 'day')
    days.push(createDayObject(date, false))
  }
  
  return days
})

const createDayObject = (date, currentMonth) => {
  const dateString = date.format('YYYY-MM-DD')
  const dayTasks = tasks.value.filter(task => 
    task.due_date && 
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed'
  )
  
  return {
    day: date.date(),
    date: dateString,
    currentMonth,
    isToday: date.isSame(dayjs(), 'day'),
    taskCount: dayTasks.length,
    tasks: dayTasks,
  }
}

const loadTasks = async () => {
  loading.value = true
  try {
    let startDate, endDate
    
    if (viewMode.value === 'month') {
      startDate = currentDate.value.startOf('month').subtract(7, 'days').format('YYYY-MM-DD')
      endDate = currentDate.value.endOf('month').add(7, 'days').format('YYYY-MM-DD')
    } else if (viewMode.value === 'week') {
      const weekStart = currentDate.value.startOf('week')
      startDate = weekStart.format('YYYY-MM-DD')
      endDate = weekStart.add(6, 'day').format('YYYY-MM-DD')
    } else {
      startDate = currentDate.value.format('YYYY-MM-DD')
      endDate = currentDate.value.format('YYYY-MM-DD')
    }
    
    await tasksStore.fetchCalendar(startDate, endDate, showMyTasks.value)
    tasks.value = tasksStore.tasks
  } finally {
    loading.value = false
  }
}

// Перезагружаем задачи при смене режима просмотра
watch(() => viewMode.value, () => {
  loadTasks()
})

const previousPeriod = () => {
  if (viewMode.value === 'month') {
    currentDate.value = currentDate.value.subtract(1, 'month')
  } else if (viewMode.value === 'week') {
    currentDate.value = currentDate.value.subtract(1, 'week')
  } else {
    currentDate.value = currentDate.value.subtract(1, 'day')
  }
  loadTasks()
}

const nextPeriod = () => {
  if (viewMode.value === 'month') {
    currentDate.value = currentDate.value.add(1, 'month')
  } else if (viewMode.value === 'week') {
    currentDate.value = currentDate.value.add(1, 'week')
  } else {
    currentDate.value = currentDate.value.add(1, 'day')
  }
  loadTasks()
}

const formatTime = (time) => {
  if (!time) return ''
  // Если время уже в формате HH:mm, возвращаем как есть
  if (/^\d{2}:\d{2}$/.test(time)) return time
  // Иначе извлекаем HH:mm из формата HH:mm:ss
  return time.substring(0, 5)
}

const getPriorityLabel = (priority) => {
  const labels = {
    high: 'Высокий',
    medium: 'Средний',
    low: 'Низкий',
    urgent: 'Срочный'
  }
  return labels[priority] || priority
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskModal.value = true
}

const handleDayClick = (dateString) => {
  // Переключаемся на дневной вид и устанавливаем выбранную дату
  currentDate.value = dayjs(dateString)
  viewMode.value = 'day'
  loadTasks()
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
    selectedTask.value = null
    await loadTasks()
  } catch (error) {
    console.error('Error saving task:', error)
    console.error('Error details:', error.response?.data)
    
    let errorMessage = 'Ошибка при сохранении задачи'
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    
    taskError.value = errorMessage
  }
}

const handleCloseTaskModal = () => {
  showTaskModal.value = false
  selectedTask.value = null
  taskError.value = ''
}

const handleToggleComplete = async (task) => {
  try {
    if (task.status === 'completed') {
      await tasksStore.updateTask(task.id, { status: 'next_action' })
    } else {
      await tasksStore.completeTask(task.id)
    }
    loadTasks()
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

// Watch для загрузки задач при смене workspace
watch(() => workspaceStore.currentWorkspace?.id, (newWorkspaceId) => {
  if (newWorkspaceId) {
    loadTasks()
  }
}, { immediate: true })

// Загружаем задачи при монтировании
onMounted(() => {
  if (workspaceStore.currentWorkspace?.id) {
    loadTasks()
  }
})
</script>

<style scoped>
.touch-manipulation {
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}

/* Улучшение прокрутки на мобильных */
@media (max-width: 1023px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }
  
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
  }
}
</style>

