import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useWorkspaceStore } from './workspace'

export const useLifeSpheresStore = defineStore('lifeSpheres', () => {
  const allSpheres = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const workspaceStore = useWorkspaceStore()

  const selectedWorkspaceIds = computed(() =>
    workspaceStore.selectedWorkspaces.map(ws => ws.id)
  )

  // Сферы, отфильтрованные по выбранным workspace
  const filteredSpheres = computed(() => {
    const ids = selectedWorkspaceIds.value
    if (!ids.length) return []
    return allSpheres.value.filter(s => ids.includes(s.workspace_id))
  })

  // Загрузка всех сфер
  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/life-spheres')
      allSpheres.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  // Создание сферы
  const create = async (workspaceId, data) => {
    const response = await api.post(`/v1/workspaces/${workspaceId}/life-spheres`, data)
    const sphere = response.data.data || response.data
    allSpheres.value.push(sphere)
    return sphere
  }

  // Обновление сферы
  const update = async (workspaceId, sphereId, data) => {
    const response = await api.put(`/v1/workspaces/${workspaceId}/life-spheres/${sphereId}`, data)
    const updated = response.data.data || response.data
    const index = allSpheres.value.findIndex(s => s.id === sphereId)
    if (index !== -1) {
      allSpheres.value[index] = updated
    }
    return updated
  }

  // Удаление сферы
  const remove = async (workspaceId, sphereId) => {
    await api.delete(`/v1/workspaces/${workspaceId}/life-spheres/${sphereId}`)
    allSpheres.value = allSpheres.value.filter(s => s.id !== sphereId)
  }

  // Заполнить дефолтными сферами
  const seedDefaults = async (workspaceId) => {
    const response = await api.post(`/v1/workspaces/${workspaceId}/life-spheres/seed`)
    const spheres = response.data.data || response.data || []
    allSpheres.value.push(...spheres)
    return spheres
  }

  return {
    allSpheres,
    loading,
    loaded,
    filteredSpheres,
    fetchAll,
    create,
    update,
    remove,
    seedDefaults,
  }
})
