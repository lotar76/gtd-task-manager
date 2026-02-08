import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useTasksStore = defineStore('tasks', () => {
  const allTasks = ref([])
  const loading = ref(false)
  const loaded = ref(false)
  const viewMode = ref(localStorage.getItem('taskViewMode') || 'list')

  const workspaceStore = useWorkspaceStore()

  const selectedWorkspaceIds = computed(() =>
    workspaceStore.selectedWorkspaces.map(ws => ws.id)
  )

  // Все задачи, отфильтрованные по выбранным workspace
  const filteredTasks = computed(() => {
    const ids = selectedWorkspaceIds.value
    if (!ids.length) return []
    return allTasks.value.filter(t => ids.includes(t.workspace_id))
  })

  // Computed-фильтры по секциям GTD
  const inboxTasks = computed(() => filteredTasks.value.filter(t => t.status === 'inbox'))
  const nextActionTasks = computed(() => filteredTasks.value.filter(t => t.status === 'next_action'))
  const todayTasks = computed(() => filteredTasks.value.filter(t => t.status === 'today'))
  const tomorrowTasks = computed(() => filteredTasks.value.filter(t => t.status === 'tomorrow'))
  const waitingTasks = computed(() => filteredTasks.value.filter(t => t.status === 'waiting'))
  const somedayTasks = computed(() => filteredTasks.value.filter(t => t.status === 'someday'))
  const scheduledTasks = computed(() => filteredTasks.value.filter(t => t.status === 'scheduled'))

  // Counts — вычисляются автоматически
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
      const d = t.due_date.substring(0, 10) // YYYY-MM-DD без времени
      return d >= startDate && d <= endDate
    })
  }

  // === Единственный метод загрузки ===
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

  // === CRUD с локальным обновлением ===

  const sanitizeData = (data) => {
    const d = { ...data }
    if (d.estimated_time === '') d.estimated_time = null
    if (d.due_date === '') d.due_date = null
    if (d.description === '') d.description = null
    if (d.project_id === '' || d.project_id === null) d.project_id = null
    return d
  }

  const createTask = async (taskData) => {
    const workspaceId = taskData?.workspace_id || workspaceStore.currentWorkspace?.id
    if (!workspaceId) {
      console.error('Cannot create task: workspace_id not found')
      return
    }

    const dataToSend = sanitizeData(taskData)
    dataToSend.workspace_id = workspaceId

    const response = await api.post(`/v1/workspaces/${workspaceId}/tasks`, dataToSend)
    const newTask = response.data.data || response.data
    // Добавляем в начало массива (если не completed)
    if (newTask.status !== 'completed') {
      allTasks.value.unshift(newTask)
    }
    return newTask
  }

  const updateTask = async (taskId, taskData) => {
    const existingTask = allTasks.value.find(t => t.id === taskId)
    const wsId = existingTask?.workspace_id || taskData?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot update task: workspace_id not found')
      return
    }

    const dataToSend = sanitizeData(taskData)
    delete dataToSend.workspace_id

    const response = await api.put(`/v1/workspaces/${wsId}/tasks/${taskId}`, dataToSend)
    const updated = response.data.data || response.data

    const index = allTasks.value.findIndex(t => t.id === taskId)
    if (index !== -1) {
      if (updated.status === 'completed') {
        // Completed задачи убираем из массива
        allTasks.value.splice(index, 1)
      } else {
        allTasks.value[index] = updated
      }
    }
    return updated
  }

  const completeTask = async (taskId) => {
    const taskIndex = allTasks.value.findIndex(t => t.id === taskId)
    if (taskIndex === -1) return

    const task = allTasks.value[taskIndex]
    const wsId = task.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot complete task: workspace_id not found')
      return
    }

    // Optimistic: сразу убираем из списка
    const backup = { ...task }
    allTasks.value.splice(taskIndex, 1)

    try {
      await api.post(`/v1/workspaces/${wsId}/tasks/${taskId}/complete`)
    } catch (error) {
      // Ошибка — возвращаем задачу на место
      allTasks.value.splice(taskIndex, 0, backup)
      throw error
    }
  }

  const deleteTask = async (taskId) => {
    const task = allTasks.value.find(t => t.id === taskId)
    const wsId = task?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot delete task: workspace_id not found')
      return
    }

    await api.delete(`/v1/workspaces/${wsId}/tasks/${taskId}`)
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
    counts,
    calendarTasks,
    viewMode,
    fetchAllTasks,
    createTask,
    updateTask,
    completeTask,
    deleteTask,
    setViewMode,
    startSync,
    stopSync,
  }
})
