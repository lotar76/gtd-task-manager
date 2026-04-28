<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center gap-3 mb-6">
      <router-link to="/documents" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
      </router-link>
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Заметки</h1>
      <button @click="openNewNote" class="ml-auto w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 active:bg-primary-700 text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
      </button>
    </div>

    <div v-if="noteFolders.length > 0" class="space-y-6">
      <div v-for="folder in noteFolders" :key="folder.key">
        <button
          @click="toggleNoteFolder(folder.key)"
          class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white"
        >
          <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': openNoteFolders[folder.key] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
          {{ folder.label }}
          <span class="text-[11px] text-gray-400">{{ folder.notes.length }}</span>
        </button>

        <div v-if="openNoteFolders[folder.key]" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 ml-6">
          <div
            v-for="note in folder.notes"
            :key="note.id"
            class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow cursor-pointer"
            @click="openEditNote(note)"
          >
            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ note.title }}</h3>
            <p v-if="note.content" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-3">{{ note.content }}</p>
            <div class="flex items-center gap-2 mt-2 text-[10px] text-gray-400">
              <span v-if="note.task" class="truncate">{{ note.task.title }}</span>
              <span>{{ formatDate(note.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!notesStore.loading" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">
      Нет заметок
    </div>

    <NoteModal
      :show="showNoteModal"
      :note="noteForModal"
      @close="showNoteModal = false; noteForModal = null"
      @submit="handleSaveNote"
      @delete="handleDeleteNote"
    />
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue'
import { useNotesStore } from '@/stores/notes'
import { useGoalsStore } from '@/stores/goals'
import NoteModal from '@/components/notes/NoteModal.vue'

const notesStore = useNotesStore()
const goalsStore = useGoalsStore()

const showNoteModal = ref(false)
const noteForModal = ref(null)
const openNoteFolders = reactive({})

const noteFolders = computed(() => {
  const result = []
  const goalMap = {}

  for (const note of notesStore.allNotes) {
    const goalId = note.goal_id
    if (goalId) {
      if (!goalMap[goalId]) {
        const goalName = note.goal?.name || goalsStore.allGoals.find(g => g.id === goalId)?.name || 'Цель'
        goalMap[goalId] = { key: 'goal-' + goalId, label: goalName, notes: [] }
      }
      goalMap[goalId].notes.push(note)
    }
  }

  const ungrouped = notesStore.allNotes.filter(n => !n.goal_id)

  for (const g of Object.values(goalMap)) result.push(g)
  if (ungrouped.length > 0) result.push({ key: 'no-goal', label: 'Без цели', notes: ungrouped })

  for (const f of result) {
    if (!(f.key in openNoteFolders)) openNoteFolders[f.key] = true
  }

  return result
})

const toggleNoteFolder = (key) => { openNoteFolders[key] = !openNoteFolders[key] }

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const openNewNote = () => { noteForModal.value = null; showNoteModal.value = true }
const openEditNote = (note) => { noteForModal.value = note; showNoteModal.value = true }

const handleSaveNote = async (data) => {
  try {
    if (data.id) await notesStore.updateNote(data.id, data)
    else await notesStore.createNote(data)
    showNoteModal.value = false
    noteForModal.value = null
  } catch (e) { console.error(e) }
}

const handleDeleteNote = async (note) => {
  if (!confirm(`Удалить заметку "${note.title}"?`)) return
  await notesStore.deleteNote(note.id)
  showNoteModal.value = false
  noteForModal.value = null
}

onMounted(async () => {
  await Promise.all([notesStore.fetchAllNotes(), goalsStore.fetchAllGoals()])
})
</script>
