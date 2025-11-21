import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref([])
  const loading = ref(false)
  const counts = ref({
    inbox: 0,
    next_actions: 0,
    today: 0,
    tomorrow: 0,
    waiting: 0,
    someday: 0,
  })
  const viewMode = ref(localStorage.getItem('taskViewMode') || 'list') // 'list' или 'calendar'
  const filter = ref({
    status: null,
    projectId: null,
    myTasks: false,
  })

  const workspaceStore = useWorkspaceStore()

  const workspaceId = computed(() => workspaceStore.currentWorkspace?.id)

  // GTD виды
  const fetchInbox = async (myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/inbox`, { params })
      tasks.value = response.data
    } finally {
      loading.value = false
    }
  }

  const fetchNextActions = async (myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/next-actions`, { params })
      tasks.value = response.data
    } finally {
      loading.value = false
    }
  }

  const fetchWaiting = async (myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/waiting`, { params })
      tasks.value = response.data
    } finally {
      loading.value = false
    }
  }

  const fetchSomeday = async (myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/someday`, { params })
      tasks.value = response.data
    } finally {
      loading.value = false
    }
  }

  const fetchToday = async (myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/today`, { params })
      tasks.value = response.data.data || response.data || []
    } finally {
      loading.value = false
    }
  }

  const fetchTomorrow = async (myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/tomorrow`, { params })
      tasks.value = response.data.data || response.data || []
    } finally {
      loading.value = false
    }
  }

  const fetchCalendar = async (startDate, endDate, myTasks = false) => {
    if (!workspaceId.value) return
    loading.value = true
    try {
      const params = { start_date: startDate, end_date: endDate }
      if (myTasks) params.my_tasks = true
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/calendar`, { params })
      // API возвращает { success: true, data: [...], message: '...' }
      tasks.value = response.data.data || response.data || []
    } finally {
      loading.value = false
    }
  }

  const fetchCounts = async (myTasks = false) => {
    if (!workspaceId.value) return
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/counts`, { params })
      counts.value = response.data
    } catch (error) {
      console.error('Ошибка при получении счетчиков задач:', error)
    }
  }

  const createTask = async (taskData) => {
    if (!workspaceId.value) return
    
    const dataToSend = { ...taskData }
    
    // Преобразуем пустые строки в null для необязательных полей
    if (dataToSend.estimated_time === '') {
      dataToSend.estimated_time = null
    }
    if (dataToSend.due_date === '') {
      dataToSend.due_date = null
    }
    if (dataToSend.description === '') {
      dataToSend.description = null
    }
    if (dataToSend.project_id === '' || dataToSend.project_id === null) {
      dataToSend.project_id = null
    }
    
    const response = await api.post(`/v1/workspaces/${workspaceId.value}/tasks`, dataToSend)
    tasks.value.unshift(response.data)
    await fetchCounts()
    return response.data
  }

  const updateTask = async (taskId, taskData) => {
    if (!workspaceId.value) return
    
    const dataToSend = { ...taskData }
    
    // Убираем workspace_id из данных обновления (его нельзя менять)
    delete dataToSend.workspace_id
    
    // Преобразуем пустые строки в null для необязательных полей
    if (dataToSend.estimated_time === '') {
      dataToSend.estimated_time = null
    }
    if (dataToSend.due_date === '') {
      dataToSend.due_date = null
    }
    if (dataToSend.project_id === '' || dataToSend.project_id === null) {
      dataToSend.project_id = null
    }
    
    console.log('Updating task with data:', dataToSend)
    
    try {
      const response = await api.put(`/v1/workspaces/${workspaceId.value}/tasks/${taskId}`, dataToSend)
      const index = tasks.value.findIndex(t => t.id === taskId)
      if (index !== -1) {
        tasks.value[index] = response.data
      }
      await fetchCounts()
      return response.data
    } catch (error) {
      console.error('Update task error:', error.response?.data)
      throw error
    }
  }

  const completeTask = async (taskId) => {
    if (!workspaceId.value) return
    const response = await api.post(`/v1/workspaces/${workspaceId.value}/tasks/${taskId}/complete`)
    const index = tasks.value.findIndex(t => t.id === taskId)
    if (index !== -1) {
      tasks.value[index] = response.data
    }
    await fetchCounts()
    return response.data
  }

  const deleteTask = async (taskId) => {
    if (!workspaceId.value) return
    await api.delete(`/v1/workspaces/${workspaceId.value}/tasks/${taskId}`)
    tasks.value = tasks.value.filter(t => t.id !== taskId)
    await fetchCounts()
  }

  const setViewMode = (mode) => {
    viewMode.value = mode
    localStorage.setItem('taskViewMode', mode)
  }

  return {
    tasks,
    loading,
    counts,
    viewMode,
    filter,
    fetchInbox,
    fetchNextActions,
    fetchWaiting,
    fetchSomeday,
    fetchToday,
    fetchTomorrow,
    fetchCalendar,
    fetchCounts,
    createTask,
    updateTask,
    completeTask,
    deleteTask,
    setViewMode,
  }
})

