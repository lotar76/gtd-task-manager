<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Статьи</h2>
      <button @click="openNew" class="w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 active:bg-primary-700 text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
      </button>
    </div>

    <div v-if="store.loading" class="text-center py-8 text-gray-400 text-sm">Загрузка...</div>
    <div v-else-if="store.allArticles.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">Нет статей</div>
    <div v-else class="space-y-4">
      <!-- Articles without folder -->
      <div v-if="unfiled.length > 0" class="space-y-2">
        <div
          v-for="a in unfiled" :key="a.id"
          @click="openEdit(a)"
          class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
        >
          <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
          </svg>
          <div class="min-w-0 flex-1">
            <div class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ a.title }}</div>
            <div v-if="a.author" class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ a.author }}</div>
          </div>
          <a v-if="a.link" :href="a.link" target="_blank" @click.stop class="text-blue-500 hover:text-blue-700 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
          </a>
        </div>
      </div>

      <!-- Folders -->
      <div v-for="folder in foldersWithArticles" :key="folder.id">
        <button
          @click="toggleFolder(folder.id)"
          class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white w-full text-left"
        >
          <svg class="w-4 h-4 transition-transform" :class="openFolders.has(folder.id) ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
          <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
            <path d="M19.5 21a3 3 0 003-3v-4.5a3 3 0 00-3-3h-15a3 3 0 00-3 3V18a3 3 0 003 3h15zM1.5 10.146V6a3 3 0 013-3h5.379a2.25 2.25 0 011.59.659l2.122 2.121c.14.141.331.22.53.22H19.5a3 3 0 013 3v1.146A4.483 4.483 0 0019.5 9h-15a4.483 4.483 0 00-3 1.146z" />
          </svg>
          {{ folder.name }}
          <span class="text-xs text-gray-400 dark:text-gray-500">{{ folder.articles.length }}</span>
          <button @click.stop="editFolder(folder)" class="ml-auto p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
          </button>
        </button>
        <div v-if="openFolders.has(folder.id)" class="space-y-2 ml-6">
          <div
            v-for="a in folder.articles" :key="a.id"
            @click="openEdit(a)"
            class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
          >
            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <div class="min-w-0 flex-1">
              <div class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ a.title }}</div>
              <div v-if="a.author" class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ a.author }}</div>
            </div>
            <a v-if="a.link" :href="a.link" target="_blank" @click.stop class="text-blue-500 hover:text-blue-700 flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>

    <ArticleModal
      :show="showModal"
      :item="editItem"
      :folders="store.allFolders"
      @close="showModal = false; editItem = null"
      @submit="handleSubmit"
      @delete="handleDelete"
    />

    <!-- Folder rename modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showFolderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showFolderModal = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-sm p-5">
            <div class="text-xs text-gray-400 mb-3">Редактирование папки</div>
            <input v-model="folderForm.name" type="text" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white mb-3" @keydown.enter="saveFolderEdit" />
            <div class="flex items-center justify-between">
              <button @click="deleteFolder" class="text-xs text-red-500 hover:text-red-700">Удалить</button>
              <div class="flex gap-2">
                <button @click="showFolderModal = false" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
                <button @click="saveFolderEdit" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md hover:opacity-90">Сохранить</button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, reactive } from 'vue'
import { useArticlesStore } from '@/stores/articles'
import ArticleModal from './ArticleModal.vue'

const store = useArticlesStore()
const showModal = ref(false)
const editItem = ref(null)
const openFolders = reactive(new Set())
const showFolderModal = ref(false)
const folderForm = ref({ id: null, name: '' })

onMounted(() => store.fetchAll())

const unfiled = computed(() => store.allArticles.filter(a => !a.article_folder_id))

const foldersWithArticles = computed(() => {
  return store.allFolders.map(f => ({
    ...f,
    articles: store.allArticles.filter(a => a.article_folder_id === f.id),
  }))
})

const toggleFolder = (id) => {
  if (openFolders.has(id)) openFolders.delete(id)
  else openFolders.add(id)
}

const openNew = () => { editItem.value = null; showModal.value = true }
const openEdit = (item) => { editItem.value = item; showModal.value = true }

const editFolder = (folder) => {
  folderForm.value = { id: folder.id, name: folder.name }
  showFolderModal.value = true
}

const saveFolderEdit = async () => {
  if (!folderForm.value.name.trim()) return
  await store.updateFolder(folderForm.value.id, { name: folderForm.value.name.trim() })
  showFolderModal.value = false
}

const deleteFolder = async () => {
  await store.removeFolder(folderForm.value.id)
  showFolderModal.value = false
}

const handleSubmit = async (data) => {
  try {
    if (editItem.value) {
      await store.updateArticle(editItem.value.id, data)
    } else {
      await store.createArticle(data)
    }
    showModal.value = false
    editItem.value = null
  } catch (e) { console.error(e) }
}

const handleDelete = async (item) => {
  if (!item) return
  await store.removeArticle(item.id)
  showModal.value = false
  editItem.value = null
}
</script>
