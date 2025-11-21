import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useWorkspaceStore = defineStore('workspace', () => {
  const workspaces = ref([])
  const currentWorkspace = ref(null)
  const loading = ref(false)

  const fetchWorkspaces = async () => {
    loading.value = true
    try {
      const response = await api.get('/v1/workspaces')
      workspaces.value = response.data
      
      // Пытаемся восстановить workspace из localStorage
      const savedWorkspaceId = localStorage.getItem('currentWorkspaceId')
      if (savedWorkspaceId && workspaces.value.length > 0) {
        const savedWorkspace = workspaces.value.find(ws => ws.id === parseInt(savedWorkspaceId))
        if (savedWorkspace) {
          currentWorkspace.value = savedWorkspace
        } else {
          // Если сохранённый workspace не найден, берём первый
          currentWorkspace.value = workspaces.value[0]
        }
      } else if (workspaces.value.length > 0 && !currentWorkspace.value) {
        // Если нет сохранённого, автоматически выбираем первый
        currentWorkspace.value = workspaces.value[0]
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
  }

  return {
    workspaces,
    currentWorkspace,
    loading,
    fetchWorkspaces,
    fetchWorkspace,
    createWorkspace,
    updateWorkspace,
    addMember,
    removeMember,
    fetchMembers,
    setCurrentWorkspace,
  }
})

