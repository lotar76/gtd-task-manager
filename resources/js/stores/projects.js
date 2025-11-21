import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useProjectsStore = defineStore('projects', () => {
  const projects = ref([])
  const loading = ref(false)

  const workspaceStore = useWorkspaceStore()
  const workspaceId = computed(() => workspaceStore.currentWorkspace?.id)

  const fetchProjects = async (includeArchived = false) => {
    if (!workspaceId.value) return
    
    loading.value = true
    try {
      const params = includeArchived ? { include_archived: true } : {}
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/projects`, { params })
      // API возвращает { success: true, data: [...], message: '...' }
      projects.value = response.data.data || response.data || []
    } finally {
      loading.value = false
    }
  }

  const fetchProject = async (projectId) => {
    if (!workspaceId.value) return
    
    loading.value = true
    try {
      const response = await api.get(`/v1/workspaces/${workspaceId.value}/projects/${projectId}`)
      // API возвращает { success: true, data: {...}, message: '...' }
      return response.data.data || response.data
    } finally {
      loading.value = false
    }
  }

  const createProject = async (projectData) => {
    if (!workspaceId.value) return
    
    const response = await api.post(`/v1/workspaces/${workspaceId.value}/projects`, projectData)
    const project = response.data.data || response.data
    projects.value.push(project)
    return project
  }

  const updateProject = async (projectId, projectData) => {
    if (!workspaceId.value) return
    
    const response = await api.put(`/v1/workspaces/${workspaceId.value}/projects/${projectId}`, projectData)
    const project = response.data.data || response.data
    const index = projects.value.findIndex(p => p.id === projectId)
    if (index !== -1) {
      projects.value[index] = project
    }
    return project
  }

  const archiveProject = async (projectId) => {
    return await updateProject(projectId, { status: 'archived' })
  }

  const unarchiveProject = async (projectId) => {
    return await updateProject(projectId, { status: 'active' })
  }

  const deleteProject = async (projectId) => {
    if (!workspaceId.value) return
    
    await api.delete(`/v1/workspaces/${workspaceId.value}/projects/${projectId}`)
    projects.value = projects.value.filter(p => p.id !== projectId)
  }

  const activeProjects = computed(() => {
    return projects.value.filter(p => p.status === 'active' || !p.status)
  })

  const archivedProjects = computed(() => {
    return projects.value.filter(p => p.status === 'archived')
  })

  return {
    projects,
    activeProjects,
    archivedProjects,
    loading,
    fetchProjects,
    fetchProject,
    createProject,
    updateProject,
    archiveProject,
    unarchiveProject,
    deleteProject,
  }
})

