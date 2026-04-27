import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useFilmsStore = defineStore('films', () => {
  const allFilms = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/films')
      allFilms.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  const create = async (data) => {
    const formData = toFormData(data)
    const response = await api.post('/v1/films', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const item = response.data.data || response.data
    allFilms.value.unshift(item)
    return item
  }

  const update = async (id, data) => {
    const formData = toFormData(data)
    formData.append('_method', 'PUT')
    const response = await api.post(`/v1/films/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const updated = response.data.data || response.data
    const idx = allFilms.value.findIndex(f => f.id === id)
    if (idx !== -1) allFilms.value[idx] = updated
    return updated
  }

  const remove = async (id) => {
    await api.delete(`/v1/films/${id}`)
    allFilms.value = allFilms.value.filter(f => f.id !== id)
  }

  const deletePoster = async (id) => {
    const response = await api.delete(`/v1/films/${id}/poster`)
    const updated = response.data.data || response.data
    const idx = allFilms.value.findIndex(f => f.id === id)
    if (idx !== -1) allFilms.value[idx] = updated
    return updated
  }

  return {
    allFilms,
    loading,
    loaded,
    fetchAll,
    create,
    update,
    remove,
    deletePoster,
  }
})

function toFormData(data) {
  const fd = new FormData()
  for (const [key, value] of Object.entries(data)) {
    if (value === null || value === undefined) continue
    if (key === 'poster' && value instanceof File) {
      fd.append('poster', value)
    } else {
      fd.append(key, String(value))
    }
  }
  return fd
}
