import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useBirthdaysStore = defineStore('birthdays', () => {
  const allBirthdays = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/birthdays')
      allBirthdays.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  const create = async (data) => {
    const response = await api.post('/v1/birthdays', data)
    const item = response.data.data || response.data
    allBirthdays.value.unshift(item)
    return item
  }

  const update = async (id, data) => {
    const response = await api.put(`/v1/birthdays/${id}`, data)
    const updated = response.data.data || response.data
    const idx = allBirthdays.value.findIndex(b => b.id === id)
    if (idx !== -1) allBirthdays.value[idx] = updated
    return updated
  }

  const remove = async (id) => {
    await api.delete(`/v1/birthdays/${id}`)
    allBirthdays.value = allBirthdays.value.filter(b => b.id !== id)
  }

  return {
    allBirthdays,
    loading,
    loaded,
    fetchAll,
    create,
    update,
    remove,
  }
})
