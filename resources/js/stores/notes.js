import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useNotesStore = defineStore('notes', () => {
  const allNotes = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAllNotes = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const response = await api.get('/v1/notes')
      allNotes.value = response.data.data || response.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  const createNote = async (data) => {
    const response = await api.post('/v1/notes', data)
    const note = response.data.data || response.data
    allNotes.value.unshift(note)
    return note
  }

  const updateNote = async (id, data) => {
    const response = await api.put(`/v1/notes/${id}`, data)
    const updated = response.data.data || response.data
    const idx = allNotes.value.findIndex(n => n.id === id)
    if (idx !== -1) allNotes.value[idx] = updated
    return updated
  }

  const deleteNote = async (id) => {
    await api.delete(`/v1/notes/${id}`)
    allNotes.value = allNotes.value.filter(n => n.id !== id)
  }

  const notesByGoalId = (goalId) =>
    allNotes.value.filter(n => n.goal_id === goalId)

  const notesByTaskId = (taskId) =>
    allNotes.value.filter(n => n.task_id === taskId)

  return {
    allNotes,
    loading,
    loaded,
    fetchAllNotes,
    createNote,
    updateNote,
    deleteNote,
    notesByGoalId,
    notesByTaskId,
  }
})
