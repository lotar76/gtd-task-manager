import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useContextsStore = defineStore('contexts', () => {
  const allContexts = ref([])
  const loaded = ref(false)
  const loading = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return allContexts.value
    loading.value = true
    try {
      const res = await api.get('/v1/contexts')
      allContexts.value = res.data || []
      loaded.value = true
      return allContexts.value
    } finally {
      loading.value = false
    }
  }

  const create = async (payload) => {
    const res = await api.post('/v1/contexts', payload)
    const item = res.data
    allContexts.value.push(item)
    return item
  }

  const remove = async (id) => {
    await api.delete(`/v1/contexts/${id}`)
    allContexts.value = allContexts.value.filter(c => c.id !== id)
  }

  return { allContexts, loaded, loading, fetchAll, create, remove }
})
