import { defineStore } from 'pinia'
import { ref, reactive, watch } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useDashboardStore = defineStore('dashboard', () => {
  // Данные по каждому workspace: { [wsId]: { lifeMirror, aiMessage, loading, aiLoading } }
  const dataByWorkspace = reactive({})
  const selectedPeriod = ref(localStorage.getItem('dashboardPeriod') || 'day')

  const workspaceStore = useWorkspaceStore()

  const ensureEntry = (wsId) => {
    if (!dataByWorkspace[wsId]) {
      dataByWorkspace[wsId] = {
        lifeMirror: null,
        aiMessage: null,
        loading: false,
        aiLoading: false,
      }
    }
  }

  const fetchLifeMirror = async (wsId) => {
    if (!wsId) return
    ensureEntry(wsId)
    dataByWorkspace[wsId].loading = true
    try {
      const response = await api.get('/v1/dashboard/life-mirror', {
        params: { workspace_id: wsId, period: selectedPeriod.value },
      })
      dataByWorkspace[wsId].lifeMirror = response.data
    } catch (error) {
      console.error(`Failed to fetch life mirror for ws ${wsId}:`, error)
      dataByWorkspace[wsId].lifeMirror = null
    } finally {
      dataByWorkspace[wsId].loading = false
    }
  }

  const fetchAiMessage = async (wsId) => {
    if (!wsId) return
    ensureEntry(wsId)
    dataByWorkspace[wsId].aiLoading = true
    try {
      const response = await api.get('/v1/dashboard/ai-message', {
        params: { workspace_id: wsId, period: selectedPeriod.value },
      })
      dataByWorkspace[wsId].aiMessage = response.data
    } catch (error) {
      console.error(`Failed to fetch AI message for ws ${wsId}:`, error)
      dataByWorkspace[wsId].aiMessage = null
    } finally {
      dataByWorkspace[wsId].aiLoading = false
    }
  }

  const fetchForWorkspace = async (wsId) => {
    await Promise.all([
      fetchLifeMirror(wsId),
      fetchAiMessage(wsId),
    ])
  }

  const fetchAllWorkspaces = async () => {
    const selected = workspaceStore.selectedWorkspaces
    if (!selected.length) return
    await Promise.all(selected.map(ws => fetchForWorkspace(ws.id)))
  }

  const setPeriod = (period) => {
    selectedPeriod.value = period
    localStorage.setItem('dashboardPeriod', period)
    fetchAllWorkspaces()
  }

  // Следим за сменой списка выбранных workspace
  watch(
    () => workspaceStore.selectedWorkspaces.map(ws => ws.id).join(','),
    () => {
      fetchAllWorkspaces()
    }
  )

  return {
    dataByWorkspace,
    selectedPeriod,
    fetchLifeMirror,
    fetchAiMessage,
    fetchForWorkspace,
    fetchAllWorkspaces,
    setPeriod,
  }
})
