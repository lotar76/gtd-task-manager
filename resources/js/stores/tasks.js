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
    scheduled: 0,
  })
  const viewMode = ref(localStorage.getItem('taskViewMode') || 'list') // 'list' или 'calendar'
  const filter = ref({
    status: null,
    projectId: null,
    myTasks: false,
  })

  const workspaceStore = useWorkspaceStore()

  const selectedWorkspaceIds = computed(() => 
    workspaceStore.selectedWorkspaces.map(ws => ws.id)
  )

  const workspaceId = computed(() => workspaceStore.currentWorkspace?.id)

  // Helper для загрузки задач из нескольких workspace
  const fetchFromMultipleWorkspaces = async (endpoint, params = {}) => {
    if (selectedWorkspaceIds.value.length === 0) return []
    
    // Загружаем параллельно из всех выбранных workspace
    const promises = selectedWorkspaceIds.value.map(wsId => 
      api.get(`/v1/workspaces/${wsId}/${endpoint}`, { params })
        .then(res => res.data)
        .catch(err => {
          console.error(`Error fetching from workspace ${wsId}:`, err)
          return []
        })
    )
    
    const results = await Promise.all(promises)
    
    // Объединяем все задачи в один массив
    return results.flat()
  }

  // GTD виды
  const fetchInbox = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      tasks.value = await fetchFromMultipleWorkspaces('inbox', params)
    } finally {
      loading.value = false
    }
  }

  const fetchNextActions = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      tasks.value = await fetchFromMultipleWorkspaces('next-actions', params)
    } finally {
      loading.value = false
    }
  }

  const fetchWaiting = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      tasks.value = await fetchFromMultipleWorkspaces('waiting', params)
    } finally {
      loading.value = false
    }
  }

  const fetchSomeday = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      tasks.value = await fetchFromMultipleWorkspaces('someday', params)
    } finally {
      loading.value = false
    }
  }

  const fetchToday = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const results = await Promise.all(
        selectedWorkspaceIds.value.map(wsId =>
          api.get(`/v1/workspaces/${wsId}/today`, { params })
            .then(res => res.data.data || res.data || [])
            .catch(() => [])
        )
      )
      tasks.value = results.flat()
    } finally {
      loading.value = false
    }
  }

  const fetchTomorrow = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = myTasks ? { my_tasks: true } : {}
      const results = await Promise.all(
        selectedWorkspaceIds.value.map(wsId =>
          api.get(`/v1/workspaces/${wsId}/tomorrow`, { params })
            .then(res => res.data.data || res.data || [])
            .catch(() => [])
        )
      )
      tasks.value = results.flat()
    } finally {
      loading.value = false
    }
  }

  const fetchCalendar = async (startDate, endDate, myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    loading.value = true
    try {
      const params = { start_date: startDate, end_date: endDate }
      if (myTasks) params.my_tasks = true
      
      const results = await Promise.all(
        selectedWorkspaceIds.value.map(wsId =>
          api.get(`/v1/workspaces/${wsId}/calendar`, { params })
            .then(res => res.data.data || res.data || [])
            .catch(() => [])
        )
      )
      tasks.value = results.flat()
    } finally {
      loading.value = false
    }
  }

  const fetchCounts = async (myTasks = false) => {
    if (selectedWorkspaceIds.value.length === 0) return
    try {
      const params = myTasks ? { my_tasks: true } : {}
      
      // Загружаем счетчики из всех выбранных workspace
      const promises = selectedWorkspaceIds.value.map(wsId =>
        api.get(`/v1/workspaces/${wsId}/counts`, { params })
          .then(res => res.data)
          .catch(() => ({
            inbox: 0,
            next_actions: 0,
            today: 0,
            tomorrow: 0,
            waiting: 0,
            someday: 0,
            scheduled: 0,
          }))
      )
      
      const results = await Promise.all(promises)
      
      // Суммируем счетчики
      counts.value = results.reduce((acc, curr) => ({
        inbox: acc.inbox + (curr.inbox || 0),
        next_actions: acc.next_actions + (curr.next_actions || 0),
        today: acc.today + (curr.today || 0),
        tomorrow: acc.tomorrow + (curr.tomorrow || 0),
        waiting: acc.waiting + (curr.waiting || 0),
        someday: acc.someday + (curr.someday || 0),
        scheduled: acc.scheduled + (curr.scheduled || 0),
      }), {
        inbox: 0,
        next_actions: 0,
        today: 0,
        tomorrow: 0,
        waiting: 0,
        someday: 0,
        scheduled: 0,
      })
    } catch (error) {
      console.error('Ошибка при получении счетчиков задач:', error)
    }
  }

  const createTask = async (taskData) => {
    // Используем workspace_id из формы (если пользователь выбрал другой workspace) или currentWorkspace
    const targetWorkspaceId = taskData?.workspace_id || workspaceId.value
    
    if (!targetWorkspaceId) {
      console.error('Cannot create task: workspace_id not found')
      return
    }
    
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
    
    // Убеждаемся, что workspace_id передается в теле запроса (бэкенд поддерживает это при создании)
    dataToSend.workspace_id = targetWorkspaceId
    
    const response = await api.post(`/v1/workspaces/${targetWorkspaceId}/tasks`, dataToSend)
    tasks.value.unshift(response.data)
    await fetchCounts()
    return response.data
  }

  const updateTask = async (taskId, taskData) => {
    // Находим задачу в массиве, чтобы получить её workspace_id
    const existingTask = tasks.value.find(t => t.id === taskId)
    
    // Для URL запроса используем workspace_id из существующей задачи (задача уже принадлежит определенному workspace)
    // Если задача не найдена в массиве, используем workspace_id из формы или currentWorkspace
    let taskWorkspaceId = existingTask?.workspace_id
    
    if (!taskWorkspaceId) {
      // Если задача не найдена, пробуем использовать из формы (может быть пользователь изменил)
      taskWorkspaceId = taskData?.workspace_id || workspaceId.value
    }
    
    if (!taskWorkspaceId) {
      console.error('Cannot update task: workspace_id not found')
      return
    }
    
    const dataToSend = { ...taskData }
    
    // Убираем workspace_id из данных обновления - бэкенд не поддерживает изменение workspace при обновлении
    // Если пользователь изменил workspace в форме, это не будет применено (задача остается в своем workspace)
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
      const response = await api.put(`/v1/workspaces/${taskWorkspaceId}/tasks/${taskId}`, dataToSend)
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
    // Находим задачу в массиве, чтобы получить её workspace_id
    const existingTask = tasks.value.find(t => t.id === taskId)
    const taskWorkspaceId = existingTask?.workspace_id || workspaceId.value
    
    if (!taskWorkspaceId) {
      console.error('Cannot complete task: workspace_id not found')
      return
    }
    
    const response = await api.post(`/v1/workspaces/${taskWorkspaceId}/tasks/${taskId}/complete`)
    const index = tasks.value.findIndex(t => t.id === taskId)
    if (index !== -1) {
      tasks.value[index] = response.data
    }
    await fetchCounts()
    return response.data
  }

  const deleteTask = async (taskId) => {
    // Находим задачу в массиве, чтобы получить её workspace_id
    const existingTask = tasks.value.find(t => t.id === taskId)
    const taskWorkspaceId = existingTask?.workspace_id || workspaceId.value
    
    if (!taskWorkspaceId) {
      console.error('Cannot delete task: workspace_id not found')
      return
    }
    
    await api.delete(`/v1/workspaces/${taskWorkspaceId}/tasks/${taskId}`)
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

