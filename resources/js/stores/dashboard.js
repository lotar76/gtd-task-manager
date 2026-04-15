import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useDashboardStore = defineStore('dashboard', () => {
  const lifeMirror = ref(null)
  const aiMessage = ref(null)
  const loading = ref(false)
  const aiLoading = ref(false)
  const aiSilentLoading = ref(false)
  const selectedPeriod = ref(localStorage.getItem('dashboardPeriod') || 'day')

  const fetchLifeMirror = async () => {
    loading.value = true
    try {
      const response = await api.get('/v1/dashboard/life-mirror', {
        params: { period: selectedPeriod.value },
      })
      lifeMirror.value = response.data
    } catch (error) {
      console.error('Failed to fetch life mirror:', error)
      lifeMirror.value = null
    } finally {
      loading.value = false
    }
  }

  const fetchAiMessage = async (force = false, silent = false) => {
    if (silent) {
      aiSilentLoading.value = true
    } else {
      aiLoading.value = true
    }
    try {
      const response = await api.get('/v1/dashboard/ai-message', {
        params: {
          period: selectedPeriod.value,
          force: force ? 1 : 0,
        },
      })
      aiMessage.value = response.data
    } catch (error) {
      console.error('Failed to fetch AI message:', error)
      aiMessage.value = null
    } finally {
      if (silent) {
        aiSilentLoading.value = false
      } else {
        aiLoading.value = false
      }
    }
  }

  const fetchAll = async (silent = false) => {
    await fetchLifeMirror()
    await fetchAiMessage(false, silent)
  }

  const setPeriod = (period) => {
    selectedPeriod.value = period
    localStorage.setItem('dashboardPeriod', period)
    fetchAll()
  }

  const invalidate = () => {
    lifeMirror.value = null
  }

  return {
    lifeMirror,
    aiMessage,
    loading,
    aiLoading,
    aiSilentLoading,
    selectedPeriod,
    fetchLifeMirror,
    fetchAiMessage,
    fetchAll,
    setPeriod,
    invalidate,
  }
})
