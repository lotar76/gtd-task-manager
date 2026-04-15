import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useProjectsStore = defineStore('projects', () => {
  const allProjects = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  // Все проекты (без фильтрации по workspace)
  const filteredProjects = computed(() => allProjects.value)

  const activeProjects = computed(() => {
    return allProjects.value.filter(p => p.status === 'active' || !p.status)
  })

  const archivedProjects = computed(() => {
    return allProjects.value.filter(p => p.status === 'archived')
  })

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

  const createProject = async (projectData) => {
    const { workspace_id, ...data } = projectData
    const response = await api.post('/v1/projects', data)
    const newProject = response.data.data || response.data
    allProjects.value.unshift(newProject)
    return newProject
  }

  const updateProject = async (projectId, projectData) => {
    const { workspace_id, ...dataToSend } = projectData
    const response = await api.put(`/v1/projects/${projectId}`, dataToSend)
    const updated = response.data.data || response.data

    const index = allProjects.value.findIndex(p => p.id === projectId)
    if (index !== -1) {
      allProjects.value[index] = updated
    }
    return updated
  }

  const archiveProject = async (projectId) => {
    return await updateProject(projectId, { status: 'archived' })
  }

  const unarchiveProject = async (projectId) => {
    return await updateProject(projectId, { status: 'active' })
  }

  const deleteProject = async (projectId) => {
    await api.delete(`/v1/projects/${projectId}`)
    allProjects.value = allProjects.value.filter(p => p.id !== projectId)
  }

  const fetchProject = async (projectId) => {
    const cached = allProjects.value.find(p => p.id === projectId)
    if (cached) return cached

    loading.value = true
    try {
      const response = await api.get(`/v1/projects/${projectId}`)
      const projectData = response.data.data || response.data

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
