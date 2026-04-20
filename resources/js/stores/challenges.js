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
    const idx = challenges.value.findIndex(c => c.id === id)
    const snapshot = idx !== -1 ? challenges.value[idx] : null
    challenges.value = challenges.value.filter(c => c.id !== id)
    try {
      await api.delete(`/v1/challenges/${id}`)
    } catch (err) {
      if (snapshot) challenges.value.splice(idx, 0, snapshot)
      throw err
    }
  }

  const toggle = async (challengeId, date, extra = {}) => {
    const challenge = challenges.value.find(c => c.id === challengeId)
    if (!challenge) return

    const entryIdx = challenge.entries.findIndex(e => e.date === date || e.date?.startsWith(date))

    // Snapshot for rollback
    const snapshot = entryIdx !== -1
      ? { ...challenge.entries[entryIdx] }
      : null
    const hadEntry = entryIdx !== -1

    // Optimistic update
    if (extra.subtask_index !== undefined) {
      // Composite: toggle subtask optimistically
      const subtaskCount = challenge.subtasks?.length || 0
      let states = hadEntry && challenge.entries[entryIdx].subtask_states
        ? [...challenge.entries[entryIdx].subtask_states]
        : Array(subtaskCount).fill(false)
      states[extra.subtask_index] = !states[extra.subtask_index]
      const allDone = !states.includes(false)
      if (hadEntry) {
        challenge.entries[entryIdx].subtask_states = states
        challenge.entries[entryIdx].completed = allDone
      } else {
        challenge.entries.push({
          challenge_id: challengeId,
          date: `${date}T00:00:00.000000Z`,
          completed: allDone,
          subtask_states: states,
        })
      }
    } else if (hadEntry && challenge.entries[entryIdx].completed) {
      // Was completed → remove
      challenge.entries.splice(entryIdx, 1)
    } else {
      // Not completed → mark completed
      if (hadEntry) {
        challenge.entries[entryIdx].completed = true
      } else {
        challenge.entries.push({
          challenge_id: challengeId,
          date: `${date}T00:00:00.000000Z`,
          completed: true,
          subtask_states: null,
        })
      }
    }

    // Send to server, rollback on error
    try {
      const res = await api.post(`/v1/challenges/${challengeId}/toggle`, { date, ...extra })
      return res.data
    } catch (err) {
      // Rollback
      const nowIdx = challenge.entries.findIndex(e => e.date === date || e.date?.startsWith(date))
      if (snapshot && hadEntry) {
        if (nowIdx !== -1) {
          challenge.entries[nowIdx] = snapshot
        } else {
          challenge.entries.push(snapshot)
        }
      } else if (!hadEntry && nowIdx !== -1) {
        challenge.entries.splice(nowIdx, 1)
      }
      throw err
    }
  }

  const reorder = async (ids) => {
    await api.post('/v1/challenges/reorder', { ids })
  }

  return { challenges, loading, currentMonth, fetchChallenges, create, update, remove, toggle, reorder }
})
