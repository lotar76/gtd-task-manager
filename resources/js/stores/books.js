import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useBooksStore = defineStore('books', () => {
  const allBooks = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/books')
      allBooks.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  const create = async (data) => {
    const formData = toFormData(data)
    const response = await api.post('/v1/books', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const item = response.data.data || response.data
    allBooks.value.unshift(item)
    return item
  }

  const update = async (id, data) => {
    const formData = toFormData(data)
    formData.append('_method', 'PUT')
    const response = await api.post(`/v1/books/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const updated = response.data.data || response.data
    const idx = allBooks.value.findIndex(b => b.id === id)
    if (idx !== -1) allBooks.value[idx] = updated
    return updated
  }

  const remove = async (id) => {
    await api.delete(`/v1/books/${id}`)
    allBooks.value = allBooks.value.filter(b => b.id !== id)
  }

  const deleteCover = async (id) => {
    const response = await api.delete(`/v1/books/${id}/cover`)
    const updated = response.data.data || response.data
    const idx = allBooks.value.findIndex(b => b.id === id)
    if (idx !== -1) allBooks.value[idx] = updated
    return updated
  }

  return {
    allBooks,
    loading,
    loaded,
    fetchAll,
    create,
    update,
    remove,
    deleteCover,
  }
})

function toFormData(data) {
  const fd = new FormData()
  for (const [key, value] of Object.entries(data)) {
    if (value === null || value === undefined) continue
    if (key === 'cover' && value instanceof File) {
      fd.append('cover', value)
    } else {
      fd.append(key, String(value))
    }
  }
  return fd
}
