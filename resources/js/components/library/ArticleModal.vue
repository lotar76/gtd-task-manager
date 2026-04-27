<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ item ? 'Редактирование статьи' : 'Новая статья' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="Название статьи"
              class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
            />

            <input
              v-model="form.author"
              type="text"
              placeholder="Автор"
              class="w-full bg-transparent border-0 outline-none text-sm placeholder-gray-300 dark:placeholder-gray-600 text-gray-700 dark:text-gray-300 px-0"
            />

            <input
              v-model="form.link"
              type="url"
              placeholder="Ссылка (https://...)"
              class="w-full bg-transparent border-0 outline-none text-sm placeholder-gray-300 dark:placeholder-gray-600 text-blue-600 dark:text-blue-400 px-0"
            />

            <!-- Folder -->
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Папка</label>
              <div class="flex gap-2">
                <select
                  v-model="form.article_folder_id"
                  class="flex-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white"
                >
                  <option :value="null">Без папки</option>
                  <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
                </select>
                <button type="button" @click="showNewFolder = !showNewFolder"
                  class="px-2 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                </button>
              </div>
              <div v-if="showNewFolder" class="flex gap-2 mt-2">
                <input
                  v-model="newFolderName"
                  type="text"
                  placeholder="Название папки"
                  class="flex-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white"
                  @keydown.enter.prevent="createFolder"
                />
                <button type="button" @click="createFolder" class="px-3 py-2 bg-primary-600 text-white rounded-lg text-xs">OK</button>
              </div>
            </div>

            <div v-if="error" class="text-red-500 text-xs">{{ error }}</div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
              <button
                v-if="item"
                type="button"
                @click="handleDelete"
                class="text-xs text-red-500 hover:text-red-700"
              >Удалить</button>
              <div v-else></div>
              <div class="flex gap-2">
                <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
                <button type="submit" :disabled="saving" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md disabled:opacity-50 hover:opacity-90">
                  {{ saving ? 'Сохранение...' : 'Сохранить' }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useArticlesStore } from '@/stores/articles'

const props = defineProps({
  show: { type: Boolean, default: false },
  item: { type: Object, default: null },
  folders: { type: Array, default: () => [] },
})

const emit = defineEmits(['close', 'submit', 'delete'])
const articlesStore = useArticlesStore()

const form = ref({ title: '', author: '', link: '', article_folder_id: null })
const saving = ref(false)
const error = ref('')
const showNewFolder = ref(false)
const newFolderName = ref('')

watch(() => props.show, (val) => {
  if (val) {
    error.value = ''
    showNewFolder.value = false
    newFolderName.value = ''
    if (props.item) {
      form.value = {
        title: props.item.title || '',
        author: props.item.author || '',
        link: props.item.link || '',
        article_folder_id: props.item.article_folder_id || null,
      }
    } else {
      form.value = { title: '', author: '', link: '', article_folder_id: null }
    }
  }
})

const createFolder = async () => {
  if (!newFolderName.value.trim()) return
  try {
    const folder = await articlesStore.createFolder({ name: newFolderName.value.trim() })
    form.value.article_folder_id = folder.id
    showNewFolder.value = false
    newFolderName.value = ''
  } catch (e) {
    error.value = e.message
  }
}

const handleSubmit = () => {
  saving.value = true
  error.value = ''
  emit('submit', { ...form.value })
  saving.value = false
}

const handleDelete = () => emit('delete', props.item)
</script>
