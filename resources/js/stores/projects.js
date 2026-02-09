import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useProjectsStore = defineStore('projects', () => {
  const allProjects = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const workspaceStore = useWorkspaceStore()

  const selectedWorkspaceIds = computed(() =>
    workspaceStore.selectedWorkspaces.map(ws => ws.id)
  )

  // Все проекты, отфильтрованные по выбранным workspace
  const filteredProjects = computed(() => {
    const ids = selectedWorkspaceIds.value
    if (!ids.length) return []
    return allProjects.value.filter(p => ids.includes(p.workspace_id))
  })

  // Активные и архивные проекты (отфильтрованные)
  const activeProjects = computed(() => {
    return filteredProjects.value.filter(p => p.status === 'active' || !p.status)
  })

  const archivedProjects = computed(() => {
    return filteredProjects.value.filter(p => p.status === 'archived')
  })

  // === Единственный метод загрузки ===
  const fetchAllProjects = async ({ force = false, includeArchived = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const params = includeArchived ? { include_archived: true } : {}
      const response = await api.get('/v1/projects', { params })
      allProjects.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  // === CRUD с локальным обновлением ===

  const createProject = async (projectData) => {
    const workspaceId = projectData?.workspace_id || workspaceStore.currentWorkspace?.id
    if (!workspaceId) {
      console.error('Cannot create project: workspace_id not found')
      return
    }

    projectData.workspace_id = workspaceId

    const response = await api.post(`/v1/workspaces/${workspaceId}/projects`, projectData)
    const newProject = response.data.data || response.data

    // Добавляем в начало массива
    allProjects.value.unshift(newProject)
    return newProject
  }

  const updateProject = async (projectId, projectData) => {
    const existingProject = allProjects.value.find(p => p.id === projectId)
    const wsId = existingProject?.workspace_id || projectData?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot update project: workspace_id not found')
      return
    }

    const dataToSend = { ...projectData }
    delete dataToSend.workspace_id

    const response = await api.put(`/v1/workspaces/${wsId}/projects/${projectId}`, dataToSend)
    const updated = response.data.data || response.data

    const index = allProjects.value.findIndex(p => p.id === projectId)
    if (index !== -1) {
      allProjects.value[index] = updated
    }
    return updated
  }

  const archiveProject = async (projectId) => {
    const project = allProjects.value.find(p => p.id === projectId)
    return await updateProject(projectId, {
      status: 'archived'
    })
  }

  const unarchiveProject = async (projectId) => {
    const project = allProjects.value.find(p => p.id === projectId)
    return await updateProject(projectId, {
      status: 'active'
    })
  }

  const deleteProject = async (projectId) => {
    const project = allProjects.value.find(p => p.id === projectId)
    const wsId = project?.workspace_id || workspaceStore.currentWorkspace?.id

    if (!wsId) {
      console.error('Cannot delete project: workspace_id not found')
      return
    }

    await api.delete(`/v1/workspaces/${wsId}/projects/${projectId}`)
    allProjects.value = allProjects.value.filter(p => p.id !== projectId)
  }

  const fetchProject = async (projectId) => {
    // Сначала проверяем локальный кеш
    const cached = allProjects.value.find(p => p.id === projectId)
    if (cached) return cached

    // Если нет в кеше, загружаем с сервера
    const project = allProjects.value.find(p => p.id === projectId)
    const wsId = project?.workspace_id || workspaceStore.currentWorkspace?.id
    if (!wsId) return null

    loading.value = true
    try {
      const response = await api.get(`/v1/workspaces/${wsId}/projects/${projectId}`)
      const projectData = response.data.data || response.data

      // Добавляем в кеш если его там нет
      const index = allProjects.value.findIndex(p => p.id === projectId)
      if (index === -1) {
        allProjects.value.push(projectData)
      } else {
        allProjects.value[index] = projectData
      }

      return projectData
    } finally {
      loading.value = false
    }
  }

  // Фоновая синхронизация
  let syncInterval = null
  const startSync = () => {
    syncInterval = setInterval(() => fetchAllProjects({ force: true }), 5 * 60 * 1000)
  }
  const stopSync = () => {
    if (syncInterval) clearInterval(syncInterval)
  }

  return {
    allProjects,
    loading,
    loaded,
    filteredProjects,
    activeProjects,
    archivedProjects,
    fetchAllProjects,
    fetchProject,
    createProject,
    updateProject,
    archiveProject,
    unarchiveProject,
    deleteProject,
    startSync,
    stopSync,
  }
})
