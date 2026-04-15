import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useTasksStore = defineStore('tasks', () => {
  const allTasks = ref([])
  const loading = ref(false)
  const loaded = ref(false)
  const viewMode = ref(localStorage.getItem('taskViewMode') || 'list')

  // Все задачи без completed
  const filteredTasks = computed(() => {
    return allTasks.value.filter(t => !t.completed_at)
  })

  // Computed-фильтры по секциям GTD
  const inboxTasks = computed(() => filteredTasks.value.filter(t => t.status === 'inbox'))
  const nextActionTasks = computed(() => filteredTasks.value.filter(t => t.status === 'next_action'))
  const todayTasks = computed(() => filteredTasks.value.filter(t => t.status === 'today'))
  const tomorrowTasks = computed(() => filteredTasks.value.filter(t => t.status === 'tomorrow'))
  const waitingTasks = computed(() => filteredTasks.value.filter(t => t.status === 'waiting'))
  const somedayTasks = computed(() => filteredTasks.value.filter(t => t.status === 'someday'))
  const scheduledTasks = computed(() => filteredTasks.value.filter(t => t.status === 'scheduled'))

  // Архивные задачи (завершённые) - последние 20
  const archivedTasks = computed(() => {
    return allTasks.value
      .filter(t => t.completed_at)
      .sort((a, b) => new Date(b.completed_at || b.updated_at) - new Date(a.completed_at || a.updated_at))
      .slice(0, 20)
  })

  // Counts
  const counts = computed(() => ({
    inbox: inboxTasks.value.length,
    next_actions: nextActionTasks.value.length,
    today: todayTasks.value.length,
    tomorrow: tomorrowTasks.value.length,
    waiting: waitingTasks.value.length,
    someday: somedayTasks.value.length,
    scheduled: scheduledTasks.value.length,
  }))

  // Calendar — фильтр по диапазону дат
  const calendarTasks = (startDate, endDate) => {
    return filteredTasks.value.filter(t => {
      if (!t.due_date) return false
      const d = t.due_date.substring(0, 10)
      return d >= startDate && d <= endDate
    })
  }

  // === Загрузка ===
  const fetchAllTasks = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/tasks')
      allTasks.value = response.data.data?.tasks || response.data.tasks || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  // === CRUD ===

  const sanitizeData = (data) => {
    const d = { ...data }
    if (d.estimated_time === '') d.estimated_time = null
    if (d.due_date === '') d.due_date = null
    if (d.description === '') d.description = null
    if (d.project_id === '' || d.project_id === null) d.project_id = null
    // Убираем workspace_id — бэкенд сам определит
    delete d.workspace_id
    return d
  }

  const createTask = async (taskData) => {
    const dataToSend = sanitizeData(taskData)
    const response = await api.post('/v1/tasks', dataToSend)
    const newTask = response.data.data || response.data
    allTasks.value.unshift(newTask)
    return newTask
  }

  const updateTask = async (taskId, taskData) => {
    const dataToSend = sanitizeData(taskData)
    const response = await api.put(`/v1/tasks/${taskId}`, dataToSend)
    const updated = response.data.data || response.data

    const index = allTasks.value.findIndex(t => t.id === taskId)
    if (index !== -1) {
      allTasks.value[index] = updated
    }
    return updated
  }

  const completeTask = async (taskId) => {
    const taskIndex = allTasks.value.findIndex(t => t.id === taskId)
    if (taskIndex === -1) return

    const task = allTasks.value[taskIndex]
    const backup = { ...task }
    allTasks.value[taskIndex] = { ...task, completed_at: new Date().toISOString() }

    try {
      const response = await api.post(`/v1/tasks/${taskId}/complete`)
      const updated = response.data.data || response.data
      allTasks.value[taskIndex] = updated
    } catch (error) {
      allTasks.value[taskIndex] = backup
      throw error
    }
  }

  const uncompleteTask = async (taskId) => {
    const taskIndex = allTasks.value.findIndex(t => t.id === taskId)
    if (taskIndex === -1) return

    const task = allTasks.value[taskIndex]
    const backup = { ...task }
    allTasks.value[taskIndex] = { ...task, completed_at: null }

    try {
      const response = await api.post(`/v1/tasks/${taskId}/uncomplete`)
      const updated = response.data.data || response.data
      allTasks.value[taskIndex] = updated
    } catch (error) {
      allTasks.value[taskIndex] = backup
      throw error
    }
  }

  const deleteTask = async (taskId) => {
    await api.delete(`/v1/tasks/${taskId}`)
    allTasks.value = allTasks.value.filter(t => t.id !== taskId)
  }

  const setViewMode = (mode) => {
    viewMode.value = mode
    localStorage.setItem('taskViewMode', mode)
  }

  // Фоновая синхронизация
  let syncInterval = null
  const startSync = () => {
    syncInterval = setInterval(() => fetchAllTasks({ force: true }), 5 * 60 * 1000)
  }
  const stopSync = () => {
    if (syncInterval) clearInterval(syncInterval)
  }

  return {
    allTasks,
    loading,
    loaded,
    filteredTasks,
    inboxTasks,
    nextActionTasks,
    todayTasks,
    tomorrowTasks,
    waitingTasks,
    somedayTasks,
    scheduledTasks,
    archivedTasks,
    counts,
    calendarTasks,
    viewMode,
    fetchAllTasks,
    createTask,
    updateTask,
    completeTask,
    uncompleteTask,
    deleteTask,
    setViewMode,
    startSync,
    stopSync,
  }
})
