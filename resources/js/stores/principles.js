import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const usePrinciplesStore = defineStore('principles', () => {
  const allPrinciples = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/principles')
      allPrinciples.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  const create = async (data) => {
    const response = await api.post('/v1/principles', data)
    const principle = response.data.data || response.data
    allPrinciples.value.push(principle)
    return principle
  }

  const update = async (principleId, data) => {
    const response = await api.put(`/v1/principles/${principleId}`, data)
    const updated = response.data.data || response.data
    const index = allPrinciples.value.findIndex(p => p.id === principleId)
    if (index !== -1) {
      allPrinciples.value[index] = updated
    }
    return updated
  }

  const remove = async (principleId) => {
    await api.delete(`/v1/principles/${principleId}`)
    allPrinciples.value = allPrinciples.value.filter(p => p.id !== principleId)
  }

  const reorder = async (ids) => {
    await api.post('/v1/principles/reorder', { ids })
    // Update local positions
    ids.forEach((id, index) => {
      const p = allPrinciples.value.find(p => p.id === id)
      if (p) p.position = index
    })
  }

  return {
    allPrinciples,
    loading,
    loaded,
    fetchAll,
    create,
    update,
    remove,
    reorder,
  }
})
