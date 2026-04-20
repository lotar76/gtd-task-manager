import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useChallengesStore = defineStore('challenges', () => {
  const challenges = ref([])
  const loading = ref(false)
  const currentMonth = ref('')

  const fetchChallenges = async (month) => {
    loading.value = true
    try {
      const res = await api.get('/v1/challenges', { params: { month } })
      challenges.value = res.data || []
      currentMonth.value = month
      return challenges.value
    } finally {
      loading.value = false
    }
  }

  const create = async (payload) => {
    const res = await api.post('/v1/challenges', payload)
    const item = res.data
    item.entries = []
    challenges.value.push(item)
    return item
  }

  const update = async (id, payload) => {
    const res = await api.put(`/v1/challenges/${id}`, payload)
    const idx = challenges.value.findIndex(c => c.id === id)
    if (idx !== -1) {
      challenges.value[idx] = { ...challenges.value[idx], ...res.data }
    }
    return res.data
  }

  const remove = async (id) => {
    await api.delete(`/v1/challenges/${id}`)
    challenges.value = challenges.value.filter(c => c.id !== id)
  }

  const toggle = async (challengeId, date, extra = {}) => {
    const res = await api.post(`/v1/challenges/${challengeId}/toggle`, { date, ...extra })
    const challenge = challenges.value.find(c => c.id === challengeId)
    if (!challenge) return

    const entryIdx = challenge.entries.findIndex(e => e.date === date || e.date?.startsWith(date))
    if (res.data.completed) {
      if (entryIdx === -1) {
        challenge.entries.push({
          challenge_id: challengeId,
          date: `${date}T00:00:00.000000Z`,
          completed: true,
          subtask_states: res.data.subtask_states || null,
        })
      } else {
        challenge.entries[entryIdx].completed = true
        if (res.data.subtask_states) challenge.entries[entryIdx].subtask_states = res.data.subtask_states
      }
    } else {
      if (res.data.subtask_states) {
        // Composite: not all done yet but entry exists
        if (entryIdx === -1) {
          challenge.entries.push({
            challenge_id: challengeId,
            date: `${date}T00:00:00.000000Z`,
            completed: false,
            subtask_states: res.data.subtask_states,
          })
        } else {
          challenge.entries[entryIdx].completed = false
          challenge.entries[entryIdx].subtask_states = res.data.subtask_states
        }
      } else {
        if (entryIdx !== -1) {
          challenge.entries.splice(entryIdx, 1)
        }
      }
    }

    return res.data
  }

  const reorder = async (ids) => {
    await api.post('/v1/challenges/reorder', { ids })
  }

  return { challenges, loading, currentMonth, fetchChallenges, create, update, remove, toggle, reorder }
})
