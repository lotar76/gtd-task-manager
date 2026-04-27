<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Книги</h2>
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
    <div v-else-if="filtered.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">Нет книг</div>
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
      <div
        v-for="book in filtered" :key="book.id"
        @click="openEdit(book)"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden cursor-pointer hover:shadow-md transition-shadow"
      >
        <div class="aspect-[2/3] bg-gray-100 dark:bg-gray-700 relative">
          <img v-if="book.cover_url" :src="book.cover_url" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
          </div>
          <!-- Rating badge -->
          <div v-if="book.rating" class="absolute top-1.5 right-1.5 bg-yellow-400 text-yellow-900 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">
            {{ book.rating }}
          </div>
        </div>
        <div class="p-2.5">
          <div class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ book.title }}</div>
          <div v-if="book.author" class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ book.author }}</div>
        </div>
      </div>
    </div>

    <BookModal
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
import { useBooksStore } from '@/stores/books'
import BookModal from './BookModal.vue'

const store = useBooksStore()
const showModal = ref(false)
const editItem = ref(null)
const activeStatus = ref('all')

const statusFilters = [
  { value: 'all', label: 'Все' },
  { value: 'want', label: 'Хочу' },
  { value: 'reading', label: 'Читаю' },
  { value: 'read', label: 'Прочитал' },
]

onMounted(() => store.fetchAll())

const filtered = computed(() => {
  if (activeStatus.value === 'all') return store.allBooks
  return store.allBooks.filter(b => b.status === activeStatus.value)
})

const countByStatus = (status) => {
  if (status === 'all') return store.allBooks.length
  return store.allBooks.filter(b => b.status === status).length
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
