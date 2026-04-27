<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Фильмы</h2>
      <button @click="openNew" class="w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 active:bg-primary-700 text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
      </button>
    </div>

    <!-- Status filter -->
    <div class="flex gap-2 mb-4">
      <button v-for="s in statusFilters" :key="s.value"
        @click="activeStatus = s.value"
        class="px-3 py-1.5 text-xs rounded-lg transition-colors"
        :class="activeStatus === s.value ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
      >{{ s.label }} <span v-if="countByStatus(s.value) > 0" class="ml-1 opacity-70">{{ countByStatus(s.value) }}</span></button>
    </div>

    <div v-if="store.loading" class="text-center py-8 text-gray-400 text-sm">Загрузка...</div>
    <div v-else-if="filtered.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">Нет фильмов</div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <div
        v-for="film in filtered" :key="film.id"
        @click="openEdit(film)"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer hover:shadow-md transition-shadow"
      >
        <div class="aspect-[2/3] bg-gray-100 dark:bg-gray-700 relative">
          <img v-if="film.poster_url" :src="film.poster_url" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-2.625 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5" />
            </svg>
          </div>
          <div v-if="film.rating" class="absolute top-1.5 right-1.5 bg-yellow-400 text-yellow-900 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">
            {{ film.rating }}
          </div>
        </div>
        <div class="p-2.5">
          <div class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ film.title }}</div>
          <div v-if="film.director" class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ film.director }}</div>
        </div>
      </div>
    </div>

    <FilmModal
      :show="showModal"
      :item="editItem"
      @close="showModal = false; editItem = null"
      @submit="handleSubmit"
      @delete="handleDelete"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useFilmsStore } from '@/stores/films'
import FilmModal from './FilmModal.vue'

const store = useFilmsStore()
const showModal = ref(false)
const editItem = ref(null)
const activeStatus = ref('all')

const statusFilters = [
  { value: 'all', label: 'Все' },
  { value: 'want', label: 'Хочу' },
  { value: 'watching', label: 'Смотрю' },
  { value: 'watched', label: 'Посмотрел' },
]

onMounted(() => store.fetchAll())

const filtered = computed(() => {
  if (activeStatus.value === 'all') return store.allFilms
  return store.allFilms.filter(f => f.status === activeStatus.value)
})

const countByStatus = (status) => {
  if (status === 'all') return store.allFilms.length
  return store.allFilms.filter(f => f.status === status).length
}

const openNew = () => { editItem.value = null; showModal.value = true }
const openEdit = (item) => { editItem.value = item; showModal.value = true }

const handleSubmit = async (data) => {
  try {
    if (editItem.value) {
      await store.update(editItem.value.id, data)
    } else {
      await store.create(data)
    }
    showModal.value = false
    editItem.value = null
  } catch (e) { console.error(e) }
}

const handleDelete = async (item) => {
  if (!item) return
  await store.remove(item.id)
  showModal.value = false
  editItem.value = null
}
</script>
