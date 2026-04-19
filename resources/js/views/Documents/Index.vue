<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Документы</h1>
      <button
        @click="openNewNote"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Создать заметку</span>
      </button>
    </div>

    <div v-if="folders.length > 0" class="space-y-6">
      <div v-for="folder in folders" :key="folder.key">
        <!-- Folder header -->
        <button
          @click="toggleFolder(folder.key)"
          class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white"
        >
          <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': openFolders[folder.key] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
          </svg>
          {{ folder.label }}
          <span class="text-[11px] text-gray-400">{{ folder.notes.length }}</span>
        </button>

        <!-- Notes grid -->
        <div v-if="openFolders[folder.key]" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 ml-6">
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

    <!-- Empty state -->
    <div v-else-if="!notesStore.loading" class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Нет заметок</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">Создайте первую заметку</p>
      <button
        @click="openNewNote"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
      >
        Создать заметку
      </button>
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
const openFolders = reactive({})

const folders = computed(() => {
  const result = []
  const goalMap = {}

  // Group by goal
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

  // Notes without goal
  const ungrouped = notesStore.allNotes.filter(n => !n.goal_id)

  // Goals first, then ungrouped
  for (const g of Object.values(goalMap)) {
    result.push(g)
  }
  if (ungrouped.length > 0) {
    result.push({ key: 'no-goal', label: 'Без цели', notes: ungrouped })
  }

  // Auto-open all folders on first load
  for (const f of result) {
    if (!(f.key in openFolders)) openFolders[f.key] = true
  }

  return result
})

const toggleFolder = (key) => {
  openFolders[key] = !openFolders[key]
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const openNewNote = () => { noteForModal.value = null; showNoteModal.value = true }
const openEditNote = (note) => { noteForModal.value = note; showNoteModal.value = true }

const handleSaveNote = async (data) => {
  try {
    if (data.id) {
      await notesStore.updateNote(data.id, data)
    } else {
      await notesStore.createNote(data)
    }
    showNoteModal.value = false
    noteForModal.value = null
  } catch (e) {
    console.error('Error saving note:', e)
  }
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
