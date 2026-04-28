<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
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

            <!-- Folder + Author row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Папка</label>
                <select
                  v-model="form.article_folder_id"
                  required
                  class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white"
                >
                  <option :value="null" disabled>Выберите папку</option>
                  <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Автор</label>
                <select
                  v-model="form.article_author_id"
                  class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white"
                >
                  <option :value="null">Без автора</option>
                  <option v-for="a in availableAuthors" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
                <div v-if="form.article_folder_id && availableAuthors.length === 0" class="text-xs text-gray-400 mt-1">
                  Нет авторов в этой папке.
                </div>
              </div>
            </div>

            <!-- Content (Markdown Editor) -->
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Текст статьи</label>
              <MarkdownEditor
                v-model="form.content"
                :preview="showPreview"
                placeholder="Текст статьи в формате Markdown..."
                :rows="14"
                @toggle-preview="showPreview = !showPreview"
              />
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
import { ref, watch, computed } from 'vue'
import MarkdownEditor from './MarkdownEditor.vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  item: { type: Object, default: null },
  folders: { type: Array, default: () => [] },
  authors: { type: Array, default: () => [] },
})

const emit = defineEmits(['close', 'submit', 'delete'])

const form = ref({ title: '', content: '', article_author_id: null, article_folder_id: null })
const saving = ref(false)
const error = ref('')
const showPreview = ref(false)

const availableAuthors = computed(() => {
  if (!form.value.article_folder_id) return []
  return props.authors.filter(a => a.article_folder_id === form.value.article_folder_id)
})

watch(() => form.value.article_folder_id, (newFolderId, oldFolderId) => {
  if (oldFolderId && newFolderId !== oldFolderId) {
    const authorStillValid = availableAuthors.value.some(a => a.id === form.value.article_author_id)
    if (!authorStillValid) {
      form.value.article_author_id = null
    }
  }
})

watch(() => props.show, (val) => {
  if (val) {
    error.value = ''
    showPreview.value = false
    if (props.item) {
      form.value = {
        title: props.item.title || '',
        content: props.item.content || '',
        article_author_id: props.item.article_author_id || null,
        article_folder_id: props.item.article_folder_id || null,
      }
    } else {
      form.value = { title: '', content: '', article_author_id: null, article_folder_id: null }
    }
  }
})

const handleSubmit = () => {
  saving.value = true
  error.value = ''
  emit('submit', { ...form.value })
  saving.value = false
}

const handleDelete = () => emit('delete', props.item)
</script>
