<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Статьи</h2>
      <div class="flex gap-2">
        <button @click="openNewFolder" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 active:bg-gray-300 dark:active:bg-gray-600 text-gray-600 dark:text-gray-300 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
          </svg>
        </button>
        <button @click="openNew" class="w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 active:bg-primary-700 text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        </button>
      </div>
    </div>

    <div v-if="store.loading" class="text-center py-8 text-gray-400 text-sm">Загрузка...</div>
    <div v-else-if="store.allFolders.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">Нет папок. Создайте папку, чтобы добавить статьи.</div>
    <div v-else class="space-y-4">
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
          <span class="text-xs text-gray-400 dark:text-gray-500 ml-1" v-if="folder.authors.length">| {{ folder.authors.length }} авт.</span>
          <div class="ml-auto flex gap-1">
            <button @click.stop="openAuthorsModal(folder)" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Авторы">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
              </svg>
            </button>
            <button @click.stop="editFolder(folder)" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
              </svg>
            </button>
          </div>
        </button>

        <div v-if="openFolders.has(folder.id)" class="ml-6">
          <!-- Sort controls -->
          <div v-if="folder.articles.length > 1" class="flex gap-2 mb-3">
            <button
              @click="setSortForFolder(folder.id, 'date')"
              class="text-xs px-2 py-1 rounded-md transition-colors"
              :class="(folderSorts[folder.id] || 'date') === 'date' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
            >По дате</button>
            <button
              @click="setSortForFolder(folder.id, 'author')"
              class="text-xs px-2 py-1 rounded-md transition-colors"
              :class="(folderSorts[folder.id]) === 'author' ? 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
            >По автору</button>
          </div>

          <div v-if="folder.articles.length === 0" class="text-xs text-gray-400 dark:text-gray-500 py-2">Нет статей в этой папке</div>

          <!-- Cards on desktop, list on mobile -->
          <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div
              v-for="a in sortedArticles(folder)" :key="a.id"
              @click="openEdit(a)"
              class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 cursor-pointer hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600 transition-all flex flex-col"
            >
              <div class="flex items-start justify-between gap-2 mb-2">
                <div class="font-medium text-sm text-gray-900 dark:text-white line-clamp-2">{{ a.title }}</div>
                <button v-if="a.content" @click.stop="openPreview(a)" class="p-1 text-gray-400 hover:text-primary-600 flex-shrink-0 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Читать">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </button>
              </div>
              <div v-if="a.content" class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 mb-3">{{ contentPreview(a.content) }}</div>
              <div class="flex items-center justify-between mt-auto">
                <div v-if="a.author" class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[60%]">{{ a.author.name }}</div>
                <div v-else class="text-xs text-gray-400">--</div>
                <div class="text-[10px] text-gray-400 flex-shrink-0">{{ formatDate(a.created_at) }}</div>
              </div>
            </div>
          </div>

          <!-- List on mobile -->
          <div class="sm:hidden space-y-2">
            <div
              v-for="a in sortedArticles(folder)" :key="a.id"
              @click="openEdit(a)"
              class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer active:bg-gray-50 dark:active:bg-gray-750 transition-colors"
            >
              <div class="min-w-0 flex-1">
                <div class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ a.title }}</div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span v-if="a.author" class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ a.author.name }}</span>
                  <span class="text-[10px] text-gray-400 flex-shrink-0">{{ formatDate(a.created_at) }}</span>
                </div>
              </div>
              <button v-if="a.content" @click.stop="openPreview(a)" class="p-1.5 text-gray-400 hover:text-primary-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ArticleModal
      :show="showModal"
      :item="editItem"
      :folders="store.allFolders"
      :authors="store.allAuthors"
      @close="showModal = false; editItem = null"
      @submit="handleSubmit"
      @delete="handleDelete"
    />

    <!-- Folder edit/create modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showFolderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showFolderModal = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-sm p-5">
            <div class="text-xs text-gray-400 mb-3">{{ folderForm.id ? 'Редактирование папки' : 'Новая папка' }}</div>
            <input v-model="folderForm.name" type="text" placeholder="Название папки" class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white mb-3" @keydown.enter="saveFolderEdit" />
            <div class="flex items-center justify-between">
              <button v-if="folderForm.id" @click="deleteFolder" class="text-xs text-red-500 hover:text-red-700">Удалить</button>
              <div v-else></div>
              <div class="flex gap-2">
                <button @click="showFolderModal = false" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
                <button @click="saveFolderEdit" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md hover:opacity-90">Сохранить</button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Authors management modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showAuthorsManageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showAuthorsManageModal = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md p-5">
            <div class="flex items-center justify-between mb-4">
              <div class="text-sm font-medium text-gray-900 dark:text-white">Авторы: {{ authorsFolder?.name }}</div>
              <button @click="showAuthorsManageModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <!-- Add new author -->
            <div class="flex gap-2 mb-4">
              <input
                v-model="newAuthorName"
                type="text"
                placeholder="Имя автора"
                class="flex-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white"
                @keydown.enter="addAuthor"
              />
              <button @click="addAuthor" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-xs hover:bg-primary-700">Добавить</button>
            </div>

            <!-- Authors list -->
            <div v-if="folderAuthors.length === 0" class="text-center py-4 text-gray-400 text-xs">Нет авторов</div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto">
              <div v-for="author in folderAuthors" :key="author.id" class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div v-if="editingAuthorId !== author.id" class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ author.name }}</div>
                  <div v-if="author.description" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ author.description }}</div>
                </div>
                <div v-else class="flex-1 min-w-0 space-y-2">
                  <input v-model="editAuthorForm.name" type="text" class="w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded px-2 py-1 text-sm text-gray-900 dark:text-white" />
                  <input v-model="editAuthorForm.description" type="text" placeholder="Описание" class="w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded px-2 py-1 text-xs text-gray-700 dark:text-gray-300" />
                  <div class="flex gap-1">
                    <button @click="saveAuthorEdit" class="px-2 py-1 text-xs bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded hover:opacity-90">OK</button>
                    <button @click="editingAuthorId = null" class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Отмена</button>
                  </div>
                </div>
                <div v-if="editingAuthorId !== author.id" class="flex gap-1 flex-shrink-0">
                  <button @click="startEditAuthor(author)" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                  </button>
                  <button @click="deleteAuthor(author.id)" class="p-1 text-red-400 hover:text-red-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
    <!-- Article preview modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="previewItem" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="previewItem = null">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
              <div class="min-w-0 flex-1">
                <div class="font-semibold text-gray-900 dark:text-white truncate">{{ previewItem.title }}</div>
                <div v-if="previewItem.author" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ previewItem.author.name }}</div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                <button @click="openEdit(previewItem); previewItem = null" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" title="Редактировать">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                </button>
                <button @click="previewItem = null" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
            <div class="px-5 py-4 prose prose-sm dark:prose-invert max-w-none overflow-y-auto max-h-[70vh]" v-html="previewHtml"></div>
            <div class="px-5 py-2 border-t border-gray-100 dark:border-gray-800 text-[10px] text-gray-400">
              {{ formatDate(previewItem.created_at) }}
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, reactive } from 'vue'
import { marked } from 'marked'
import { useArticlesStore } from '@/stores/articles'
import ArticleModal from './ArticleModal.vue'

marked.setOptions({ breaks: true, gfm: true })

const store = useArticlesStore()
const showModal = ref(false)
const editItem = ref(null)
const openFolders = reactive(new Set())
const folderSorts = reactive({})

// Folder modal
const previewItem = ref(null)

const previewHtml = computed(() => {
  if (!previewItem.value?.content) return ''
  return marked.parse(previewItem.value.content)
})

const openPreview = (article) => {
  previewItem.value = article
}

const showFolderModal = ref(false)
const folderForm = ref({ id: null, name: '' })

// Authors modal
const showAuthorsManageModal = ref(false)
const authorsFolder = ref(null)
const newAuthorName = ref('')
const editingAuthorId = ref(null)
const editAuthorForm = ref({ name: '', description: '' })

onMounted(() => store.fetchAll())

const foldersWithArticles = computed(() => {
  return store.allFolders.map(f => ({
    ...f,
    articles: store.allArticles.filter(a => a.article_folder_id === f.id),
    authors: store.allAuthors.filter(a => a.article_folder_id === f.id),
  }))
})

const folderAuthors = computed(() => {
  if (!authorsFolder.value) return []
  return store.allAuthors.filter(a => a.article_folder_id === authorsFolder.value.id)
})

const sortedArticles = (folder) => {
  const sort = folderSorts[folder.id] || 'date'
  const articles = [...folder.articles]
  if (sort === 'author') {
    return articles.sort((a, b) => {
      const nameA = a.author?.name || ''
      const nameB = b.author?.name || ''
      return nameA.localeCompare(nameB, 'ru') || new Date(b.created_at) - new Date(a.created_at)
    })
  }
  return articles.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
}

const setSortForFolder = (folderId, sort) => {
  folderSorts[folderId] = sort
}

const contentPreview = (content) => {
  if (!content) return ''
  return content.replace(/[#*`~>\[\]()_\-]/g, '').replace(/\n+/g, ' ').trim()
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}

const toggleFolder = (id) => {
  if (openFolders.has(id)) openFolders.delete(id)
  else openFolders.add(id)
}

const openNew = () => { editItem.value = null; showModal.value = true }
const openEdit = (item) => { editItem.value = item; showModal.value = true }

// Folder management
const openNewFolder = () => {
  folderForm.value = { id: null, name: '' }
  showFolderModal.value = true
}

const editFolder = (folder) => {
  folderForm.value = { id: folder.id, name: folder.name }
  showFolderModal.value = true
}

const saveFolderEdit = async () => {
  if (!folderForm.value.name.trim()) return
  if (folderForm.value.id) {
    await store.updateFolder(folderForm.value.id, { name: folderForm.value.name.trim() })
  } else {
    const folder = await store.createFolder({ name: folderForm.value.name.trim() })
    openFolders.add(folder.id)
  }
  showFolderModal.value = false
}

const deleteFolder = async () => {
  await store.removeFolder(folderForm.value.id)
  showFolderModal.value = false
}

// Authors management
const openAuthorsModal = (folder) => {
  authorsFolder.value = folder
  editingAuthorId.value = null
  newAuthorName.value = ''
  showAuthorsManageModal.value = true
}

const addAuthor = async () => {
  if (!newAuthorName.value.trim() || !authorsFolder.value) return
  await store.createAuthor({
    name: newAuthorName.value.trim(),
    article_folder_id: authorsFolder.value.id,
  })
  newAuthorName.value = ''
}

const startEditAuthor = (author) => {
  editingAuthorId.value = author.id
  editAuthorForm.value = { name: author.name, description: author.description || '' }
}

const saveAuthorEdit = async () => {
  if (!editAuthorForm.value.name.trim()) return
  await store.updateAuthor(editingAuthorId.value, {
    name: editAuthorForm.value.name.trim(),
    description: editAuthorForm.value.description.trim() || null,
  })
  editingAuthorId.value = null
}

const deleteAuthor = async (id) => {
  await store.removeAuthor(id)
}

// Article handlers
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
