import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useWorkspaceStore = defineStore('workspace', () => {
  const workspaces = ref([])
  const currentWorkspace = ref(null)
  const selectedWorkspaces = ref([])
  const loading = ref(false)

  // Восстановление выбранных workspace из localStorage
  const loadSelectedWorkspaces = () => {
    const saved = localStorage.getItem('selectedWorkspaceIds')
    if (saved) {
      try {
        const ids = JSON.parse(saved)
        selectedWorkspaces.value = workspaces.value.filter(ws => ids.includes(ws.id))
      } catch (e) {
        console.error('Error loading selected workspaces:', e)
      }
    }
  }

  // Сохранение выбранных workspace в localStorage
  const saveSelectedWorkspaces = () => {
    const ids = selectedWorkspaces.value.map(ws => ws.id)
    localStorage.setItem('selectedWorkspaceIds', JSON.stringify(ids))
  }

  const fetchWorkspaces = async () => {
    loading.value = true
    try {
      const response = await api.get('/v1/workspaces')
      workspaces.value = response.data
      
      // Загружаем выбранные workspace
      loadSelectedWorkspaces()
      
      // Пытаемся восстановить workspace из localStorage
      const savedWorkspaceId = localStorage.getItem('currentWorkspaceId')
      if (savedWorkspaceId && workspaces.value.length > 0) {
        const savedWorkspace = workspaces.value.find(ws => ws.id === parseInt(savedWorkspaceId))
        if (savedWorkspace) {
          currentWorkspace.value = savedWorkspace
          // Добавляем в выбранные если еще не там
          if (!selectedWorkspaces.value.find(ws => ws.id === savedWorkspace.id)) {
            selectedWorkspaces.value.push(savedWorkspace)
            saveSelectedWorkspaces()
          }
        } else {
          // Если сохранённый workspace не найден, берём первый
          currentWorkspace.value = workspaces.value[0]
        }
      } else if (workspaces.value.length > 0 && !currentWorkspace.value) {
        // Если нет сохранённого, автоматически выбираем первый
        currentWorkspace.value = workspaces.value[0]
      }
      
      // Если нет выбранных, выбираем первый
      if (selectedWorkspaces.value.length === 0 && workspaces.value.length > 0) {
        selectedWorkspaces.value.push(workspaces.value[0])
        if (!currentWorkspace.value) {
          currentWorkspace.value = workspaces.value[0]
        }
        saveSelectedWorkspaces()
      }
    } finally {
      loading.value = false
    }
  }

  const fetchWorkspace = async (id) => {
    loading.value = true
    try {
      const response = await api.get(`/v1/workspaces/${id}`)
      currentWorkspace.value = response.data
      return response.data
    } finally {
      loading.value = false
    }
  }

  const createWorkspace = async (data) => {
    const response = await api.post('/v1/workspaces', data)
    workspaces.value.push(response.data)
    return response.data
  }

  const updateWorkspace = async (id, data) => {
    const response = await api.put(`/v1/workspaces/${id}`, data)
    
    // Обновляем workspace в списке
    const index = workspaces.value.findIndex(ws => ws.id === id)
    if (index !== -1) {
      workspaces.value[index] = response.data
    }
    
    // Если это текущий workspace, обновляем и его
    if (currentWorkspace.value?.id === id) {
      currentWorkspace.value = response.data
    }
    
    return response.data
  }

  const deleteWorkspace = async (id) => {
    await api.delete(`/v1/workspaces/${id}`)
    workspaces.value = workspaces.value.filter(ws => ws.id !== id)
    selectedWorkspaces.value = selectedWorkspaces.value.filter(ws => ws.id !== id)

    if (currentWorkspace.value?.id === id) {
      currentWorkspace.value = workspaces.value[0] || null
      if (currentWorkspace.value) {
        localStorage.setItem('currentWorkspaceId', currentWorkspace.value.id)
      }
    }

    if (selectedWorkspaces.value.length === 0 && workspaces.value.length > 0) {
      selectedWorkspaces.value.push(workspaces.value[0])
    }
    saveSelectedWorkspaces()
  }

  const addMember = async (workspaceId, memberData) => {
    const response = await api.post(`/v1/workspaces/${workspaceId}/members`, memberData)
    return response.data
  }

  const removeMember = async (workspaceId, userId) => {
    const response = await api.delete(`/v1/workspaces/${workspaceId}/members/${userId}`)
    return response.data
  }

  const fetchMembers = async (workspaceId) => {
    const response = await api.get(`/v1/workspaces/${workspaceId}/members`)
    return response.data
  }

  const setCurrentWorkspace = (workspace) => {
    currentWorkspace.value = workspace
    localStorage.setItem('currentWorkspaceId', workspace.id)
    
    // Добавляем в выбранные если еще не там
    if (!selectedWorkspaces.value.find(ws => ws.id === workspace.id)) {
      selectedWorkspaces.value.push(workspace)
      saveSelectedWorkspaces()
    }
  }

  // Переключение workspace (добавить/убрать из выбранных)
  const toggleSelectedWorkspace = (workspace) => {
    const index = selectedWorkspaces.value.findIndex(ws => ws.id === workspace.id)
    
    if (index > -1) {
      // Убираем из выбранных
      selectedWorkspaces.value.splice(index, 1)
      
      // Если это был последний, выбираем первый доступный
      if (selectedWorkspaces.value.length === 0 && workspaces.value.length > 0) {
        selectedWorkspaces.value.push(workspaces.value[0])
        currentWorkspace.value = workspaces.value[0]
      } else if (currentWorkspace.value?.id === workspace.id) {
        // Если убрали текущий, переключаемся на первый из выбранных
        if (selectedWorkspaces.value.length > 0) {
          currentWorkspace.value = selectedWorkspaces.value[0]
          localStorage.setItem('currentWorkspaceId', currentWorkspace.value.id)
        }
      }
    } else {
      // Добавляем в выбранные
      selectedWorkspaces.value.push(workspace)
      currentWorkspace.value = workspace
      localStorage.setItem('currentWorkspaceId', workspace.id)
    }
    
    saveSelectedWorkspaces()
  }

  // Установить выбранные workspace
  const setSelectedWorkspaces = (workspaceIds) => {
    selectedWorkspaces.value = workspaces.value.filter(ws => workspaceIds.includes(ws.id))
    if (selectedWorkspaces.value.length > 0 && !currentWorkspace.value) {
      currentWorkspace.value = selectedWorkspaces.value[0]
      localStorage.setItem('currentWorkspaceId', currentWorkspace.value.id)
    }
    saveSelectedWorkspaces()
  }

  return {
    workspaces,
    currentWorkspace,
    selectedWorkspaces,
    loading,
    fetchWorkspaces,
    fetchWorkspace,
    createWorkspace,
    updateWorkspace,
    deleteWorkspace,
    addMember,
    removeMember,
    fetchMembers,
    setCurrentWorkspace,
    toggleSelectedWorkspace,
    setSelectedWorkspaces,
  }
})

