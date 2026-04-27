<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ item ? 'Редактирование книги' : 'Новая книга' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="Название книги"
              class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
            />

            <input
              v-model="form.author"
              type="text"
              placeholder="Автор"
              class="w-full bg-transparent border-0 outline-none text-sm placeholder-gray-300 dark:placeholder-gray-600 text-gray-700 dark:text-gray-300 px-0"
            />

            <!-- Cover upload -->
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Обложка</label>
              <div v-if="coverPreview" class="relative inline-block mb-2">
                <img :src="coverPreview" class="w-24 h-32 object-cover rounded-lg" />
                <button type="button" @click="removeCover" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <input
                ref="coverInput"
                type="file"
                accept="image/*"
                @change="onCoverChange"
                class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 dark:file:bg-gray-800 file:text-gray-700 dark:file:text-gray-300"
              />
            </div>

            <textarea
              v-model="form.annotation"
              rows="3"
              placeholder="Аннотация"
              class="w-full bg-transparent border-0 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-none"
            ></textarea>

            <!-- Status -->
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Статус</label>
              <div class="flex gap-2">
                <button v-for="s in statuses" :key="s.value" type="button"
                  @click="form.status = s.value"
                  class="px-3 py-1.5 text-xs rounded-lg transition-colors"
                  :class="form.status === s.value ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"
                >{{ s.label }}</button>
              </div>
            </div>

            <!-- Rating -->
            <div v-if="form.status === 'read'">
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Рейтинг</label>
              <div class="flex gap-1">
                <button v-for="n in 10" :key="n" type="button"
                  @click="form.rating = form.rating === n ? null : n"
                  class="w-7 h-7 rounded text-xs font-medium transition-colors"
                  :class="form.rating >= n ? 'bg-yellow-400 text-yellow-900' : 'bg-gray-100 dark:bg-gray-800 text-gray-400'"
                >{{ n }}</button>
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

const props = defineProps({
  show: { type: Boolean, default: false },
  item: { type: Object, default: null },
})

const emit = defineEmits(['close', 'submit', 'delete'])

const statuses = [
  { value: 'want', label: 'Хочу прочитать' },
  { value: 'reading', label: 'Читаю' },
  { value: 'read', label: 'Прочитал' },
]

const form = ref({ title: '', author: '', annotation: '', status: 'want', rating: null, cover: null })
const coverPreview = ref(null)
const coverInput = ref(null)
const saving = ref(false)
const error = ref('')

watch(() => props.show, (val) => {
  if (val) {
    error.value = ''
    if (props.item) {
      form.value = {
        title: props.item.title || '',
        author: props.item.author || '',
        annotation: props.item.annotation || '',
        status: props.item.status || 'want',
        rating: props.item.rating || null,
        cover: null,
      }
      coverPreview.value = props.item.cover_url || null
    } else {
      form.value = { title: '', author: '', annotation: '', status: 'want', rating: null, cover: null }
      coverPreview.value = null
    }
  }
})

const onCoverChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  form.value.cover = file
  coverPreview.value = URL.createObjectURL(file)
}

const removeCover = () => {
  form.value.cover = null
  coverPreview.value = null
  if (coverInput.value) coverInput.value.value = ''
}

const handleSubmit = () => {
  saving.value = true
  error.value = ''
  emit('submit', { ...form.value })
  saving.value = false
}

const handleDelete = () => emit('delete', props.item)
</script>
