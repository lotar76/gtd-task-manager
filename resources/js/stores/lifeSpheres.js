import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useLifeSpheresStore = defineStore('lifeSpheres', () => {
  const allSpheres = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const filteredSpheres = computed(() => allSpheres.value)

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

  const create = async (data) => {
    const response = await api.post('/v1/life-spheres', data)
    const sphere = response.data.data || response.data
    allSpheres.value.push(sphere)
    return sphere
  }

  const update = async (sphereId, data) => {
    const response = await api.put(`/v1/life-spheres/${sphereId}`, data)
    const updated = response.data.data || response.data
    const index = allSpheres.value.findIndex(s => s.id === sphereId)
    if (index !== -1) {
      allSpheres.value[index] = updated
    }
    return updated
  }

  const remove = async (sphereId) => {
    await api.delete(`/v1/life-spheres/${sphereId}`)
    allSpheres.value = allSpheres.value.filter(s => s.id !== sphereId)
  }

  const seedDefaults = async () => {
    const response = await api.post('/v1/life-spheres/seed')
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
