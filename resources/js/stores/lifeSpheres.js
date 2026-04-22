import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useLifeSpheresStore = defineStore('lifeSpheres', () => {
  const allSpheres = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const filteredSpheres = computed(() => allSpheres.value)
  const visibleSpheres = computed(() => allSpheres.value.filter(s => !s.is_hidden))

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

  const fetchOne = async (sphereId) => {
    const response = await api.get(`/v1/life-spheres/${sphereId}`)
    return response.data.data || response.data
  }

  const create = async (data) => {
    const formData = buildFormData(data)
    const response = await api.post('/v1/life-spheres', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const sphere = response.data.data || response.data
    allSpheres.value.push(sphere)
    return sphere
  }

  const update = async (sphereId, data) => {
    const formData = buildFormData(data)
    formData.append('_method', 'PUT')
    const response = await api.post(`/v1/life-spheres/${sphereId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
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

  const reorder = async (ids) => {
    await api.post('/v1/life-spheres/reorder', { ids })
    ids.forEach((id, index) => {
      const s = allSpheres.value.find(s => s.id === id)
      if (s) s.position = index
    })
  }

  // Image management
  const addImage = async (sphereId, file) => {
    const fd = new FormData()
    fd.append('image', file)
    const response = await api.post(`/v1/life-spheres/${sphereId}/images`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data.data || response.data
  }

  const deleteImage = async (sphereId, imageId) => {
    await api.delete(`/v1/life-spheres/${sphereId}/images/${imageId}`)
  }

  const reorderImages = async (sphereId, ids) => {
    await api.post(`/v1/life-spheres/${sphereId}/images/reorder`, { ids })
  }

  const seedDefaults = async () => {
    const response = await api.post('/v1/life-spheres/seed')
    const spheres = response.data.data || response.data || []
    allSpheres.value.push(...spheres)
    return spheres
  }

  function buildFormData(data) {
    const fd = new FormData()
    for (const [key, value] of Object.entries(data)) {
      if (value === null || value === undefined) continue
      if (key === 'image' && value instanceof File) {
        fd.append('image', value)
      } else if (typeof value === 'boolean') {
        fd.append(key, value ? '1' : '0')
      } else {
        fd.append(key, value)
      }
    }
    return fd
  }

  return {
    allSpheres,
    loading,
    loaded,
    filteredSpheres,
    visibleSpheres,
    fetchAll,
    fetchOne,
    create,
    update,
    remove,
    reorder,
    addImage,
    deleteImage,
    reorderImages,
    seedDefaults,
  }
})
