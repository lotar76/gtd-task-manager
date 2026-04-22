<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ sphere ? 'Редактирование сферы' : 'Новая сфера' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <!-- Название + Цвет -->
            <div class="flex items-center gap-3">
              <input
                type="color"
                v-model="form.color"
                class="w-9 h-9 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600 p-0.5 flex-shrink-0"
              />
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Название сферы"
                class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
              />
            </div>

            <!-- Описание (видение) -->
            <div>
              <div class="flex items-center gap-1.5 mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Видение
              </div>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Как выглядит идеальное состояние этой сферы? К чему стремлюсь?"
                class="w-full bg-transparent border-0 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-none"
              ></textarea>
            </div>

            <!-- Картинка -->
            <div>
              <div class="flex items-center gap-1.5 mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Картинка
              </div>
              <div v-if="imagePreview || form.image_url" class="relative mb-2">
                <img :src="imagePreview || form.image_url" class="w-full h-32 object-cover rounded-lg" />
                <button type="button" @click="removeImage" class="absolute top-1 right-1 p-1 bg-black/60 text-white rounded hover:bg-red-600">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <input type="file" ref="fileInput" accept="image/*" @change="handleImageSelect" class="hidden" />
              <button type="button" @click="$refs.fileInput.click()"
                class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ imagePreview || form.image_url ? 'Заменить' : 'Выбрать изображение' }}
              </button>
            </div>

            <div v-if="error" class="text-red-500 text-xs">{{ error }}</div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
              <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
              <button type="submit" :disabled="saving" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md disabled:opacity-50 hover:opacity-90">
                {{ saving ? 'Сохранение...' : 'Сохранить' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'

const props = defineProps({
  show: { type: Boolean, default: false },
  sphere: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved'])

const store = useLifeSpheresStore()

const form = ref(getDefaultForm())
const saving = ref(false)
const error = ref('')
const imagePreview = ref(null)
const imageFile = ref(null)
const removeImageFlag = ref(false)
const fileInput = ref(null)

function getDefaultForm() {
  return {
    name: '',
    color: '#3B82F6',
    description: '',
    image_url: null,
  }
}

watch(() => props.show, (val) => {
  if (val) {
    if (props.sphere) {
      form.value = {
        name: props.sphere.name || '',
        color: props.sphere.color || '#3B82F6',
        description: props.sphere.description || '',
        image_url: props.sphere.image_url || null,
      }
    } else {
      form.value = getDefaultForm()
    }
    imagePreview.value = null
    imageFile.value = null
    removeImageFlag.value = false
    error.value = ''
  }
})

const handleImageSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  imageFile.value = file
  removeImageFlag.value = false
  const reader = new FileReader()
  reader.onload = (ev) => { imagePreview.value = ev.target.result }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  imagePreview.value = null
  imageFile.value = null
  form.value.image_url = null
  removeImageFlag.value = true
}

const handleSubmit = async () => {
  if (!form.value.name.trim()) return
  saving.value = true
  error.value = ''
  try {
    const data = {
      name: form.value.name.trim(),
      color: form.value.color,
      description: form.value.description || null,
    }
    if (imageFile.value) {
      data.image = imageFile.value
    }
    if (removeImageFlag.value) {
      data.remove_image = true
    }

    if (props.sphere) {
      await store.update(props.sphere.id, data)
    } else {
      data.position = store.allSpheres.length
      await store.create(data)
    }
    emit('saved')
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка при сохранении'
  } finally {
    saving.value = false
  }
}
</script>
