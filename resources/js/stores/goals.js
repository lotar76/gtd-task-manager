import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useGoalsStore = defineStore('goals', () => {
  const allGoals = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const filteredGoals = computed(() => allGoals.value)

  const activeGoals = computed(() => {
    return allGoals.value.filter(g => g.status === 'active' || !g.status)
  })

  const archivedGoals = computed(() => {
    return allGoals.value.filter(g => g.status === 'archived')
  })

  const completedGoals = computed(() => {
    return allGoals.value.filter(g => g.status === 'completed')
  })

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

  const fetchGoal = async (goalId) => {
    const response = await api.get(`/v1/goals/${goalId}`)
    return response.data.data || response.data
  }

  const createGoal = async (goalData) => {
    const { workspace_id, ...data } = goalData

    let response
    if (data.imageFile) {
      const formData = new FormData()
      formData.append('image', data.imageFile)
      Object.keys(data).forEach(key => {
        if (key === 'imageFile') return
        if (data[key] !== null && data[key] !== undefined) {
          formData.append(key, data[key])
        }
      })
      response = await api.post('/v1/goals', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      const { imageFile, ...cleanData } = data
      response = await api.post('/v1/goals', cleanData)
    }

    const newGoal = response.data.data || response.data
    allGoals.value.unshift(newGoal)
    return newGoal
  }

  const updateGoal = async (goalId, goalData) => {
    const { workspace_id, ...data } = goalData

    let response
    if (data.imageFile) {
      const formData = new FormData()
      formData.append('_method', 'PUT')
      formData.append('image', data.imageFile)
      Object.keys(data).forEach(key => {
        if (key === 'imageFile') return
        if (data[key] !== null && data[key] !== undefined) {
          formData.append(key, data[key])
        }
      })
      response = await api.post(`/v1/goals/${goalId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      const { imageFile, ...dataToSend } = data
      response = await api.put(`/v1/goals/${goalId}`, dataToSend)
    }

    const updated = response.data.data || response.data
    const index = allGoals.value.findIndex(g => g.id === goalId)
    if (index !== -1) {
      allGoals.value[index] = updated
    }
    return updated
  }

  const deleteGoal = async (goalId) => {
    await api.delete(`/v1/goals/${goalId}`)
    allGoals.value = allGoals.value.filter(g => g.id !== goalId)
  }

  const deleteGoalImage = async (goalId) => {
    const response = await api.delete(`/v1/goals/${goalId}/image`)
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
