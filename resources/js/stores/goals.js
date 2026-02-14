import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useGoalsStore = defineStore('goals', () => {
  const allGoals = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const workspaceStore = useWorkspaceStore()

  const selectedWorkspaceIds = computed(() =>
    workspaceStore.selectedWorkspaces.map(ws => ws.id)
  )

  // Все цели, отфильтрованные по выбранным workspace
  const filteredGoals = computed(() => {
    const ids = selectedWorkspaceIds.value
    if (!ids.length) return []
    return allGoals.value.filter(g => ids.includes(g.workspace_id))
  })

  // Активные цели (отфильтрованные)
  const activeGoals = computed(() => {
    return filteredGoals.value.filter(g => g.status === 'active' || !g.status)
  })

  const archivedGoals = computed(() => {
    return filteredGoals.value.filter(g => g.status === 'archived')
  })

  const completedGoals = computed(() => {
    return filteredGoals.value.filter(g => g.status === 'completed')
  })

  // === Загрузка ===
  const fetchAllGoals = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/goals')
      allGoals.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  const fetchGoal = async (workspaceId, goalId) => {
    const response = await api.get(`/v1/workspaces/${workspaceId}/goals/${goalId}`)
    return response.data.data || response.data
  }

  // === CRUD ===

  const createGoal = async (goalData) => {
    const workspaceId = goalData?.workspace_id || workspaceStore.currentWorkspace?.id
    if (!workspaceId) {
      console.error('Cannot create goal: workspace_id not found')
      return
    }

    goalData.workspace_id = workspaceId

    let response
    if (goalData.imageFile) {
      const formData = new FormData()
      formData.append('image', goalData.imageFile)
      Object.keys(goalData).forEach(key => {
        if (key === 'imageFile') return
        if (goalData[key] !== null && goalData[key] !== undefined) {
          formData.append(key, goalData[key])
        }
      })
      response = await api.post(`/v1/workspaces/${workspaceId}/goals`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      const { imageFile, ...data } = goalData
      response = await api.post(`/v1/workspaces/${workspaceId}/goals`, data)
    }

    const newGoal = response.data.data || response.data
    allGoals.value.unshift(newGoal)
    return newGoal
  }

  const updateGoal = async (goalId, goalData) => {
    const existingGoal = allGoals.value.find(g => g.id === goalId)
    const wsId = existingGoal?.workspace_id || goalData?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot update goal: workspace_id not found')
      return
    }

    let response
    if (goalData.imageFile) {
      const formData = new FormData()
      formData.append('_method', 'PUT')
      formData.append('image', goalData.imageFile)
      Object.keys(goalData).forEach(key => {
        if (key === 'imageFile' || key === 'workspace_id') return
        if (goalData[key] !== null && goalData[key] !== undefined) {
          formData.append(key, goalData[key])
        }
      })
      response = await api.post(`/v1/workspaces/${wsId}/goals/${goalId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      const { imageFile, workspace_id, ...dataToSend } = goalData
      response = await api.put(`/v1/workspaces/${wsId}/goals/${goalId}`, dataToSend)
    }

    const updated = response.data.data || response.data
    const index = allGoals.value.findIndex(g => g.id === goalId)
    if (index !== -1) {
      allGoals.value[index] = updated
    }
    return updated
  }

  const deleteGoal = async (goalId) => {
    const goal = allGoals.value.find(g => g.id === goalId)
    const wsId = goal?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot delete goal: workspace_id not found')
      return
    }

    await api.delete(`/v1/workspaces/${wsId}/goals/${goalId}`)
    allGoals.value = allGoals.value.filter(g => g.id !== goalId)
  }

  const deleteGoalImage = async (goalId) => {
    const goal = allGoals.value.find(g => g.id === goalId)
    const wsId = goal?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot delete goal image: workspace_id not found')
      return
    }

    const response = await api.delete(`/v1/workspaces/${wsId}/goals/${goalId}/image`)
    const updated = response.data.data || response.data
    const index = allGoals.value.findIndex(g => g.id === goalId)
    if (index !== -1) {
      allGoals.value[index] = updated
    }
    return updated
  }

  return {
    allGoals,
    loading,
    loaded,
    filteredGoals,
    activeGoals,
    archivedGoals,
    completedGoals,
    fetchAllGoals,
    fetchGoal,
    createGoal,
    updateGoal,
    deleteGoal,
    deleteGoalImage,
  }
})
