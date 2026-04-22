<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
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
                rows="6"
                placeholder="Как выглядит идеальное состояние этой сферы? К чему стремлюсь?"
                class="w-full bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg p-3 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-y focus:border-gray-400 dark:focus:border-gray-500 transition-colors"
              ></textarea>
            </div>

            <!-- Галерея -->
            <div>
              <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Вдохновляющие картинки
              </div>

              <!-- Cropper mode -->
              <div v-if="showCropper" class="space-y-2">
                <div class="relative bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden" style="height: 320px">
                  <img ref="cropperImage" :src="cropperSrc" class="block max-w-full" />
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-[11px] text-gray-400">Выберите область</span>
                  <div class="flex gap-2">
                    <button type="button" @click="cancelCrop" class="px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Отмена</button>
                    <button type="button" @click="applyCrop" class="px-3 py-1 text-xs bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md hover:opacity-90">Применить</button>
                  </div>
                </div>
              </div>

              <!-- Gallery grid -->
              <div v-else>
                <draggable
                  v-if="galleryItems.length > 0"
                  v-model="galleryItems"
                  item-key="key"
                  ghost-class="opacity-30"
                  animation="200"
                  class="flex flex-wrap gap-2 mb-2"
                  @end="handleGalleryReorder"
                >
                  <template #item="{ element, index }">
                    <div class="relative group w-20 h-[106px] rounded-lg overflow-hidden flex-shrink-0">
                      <img :src="element.url || element.preview" class="w-full h-full object-cover" />
                      <!-- Cover badge -->
                      <span v-if="index === 0" class="absolute top-1 left-1 text-[8px] font-bold uppercase bg-white/80 dark:bg-black/60 text-gray-700 dark:text-white px-1 py-0.5 rounded">
                        Обложка
                      </span>
                      <!-- Delete button -->
                      <button
                        type="button"
                        @click="removeGalleryItem(element)"
                        class="absolute top-1 right-1 p-0.5 bg-black/60 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                      >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                      </button>
                    </div>
                  </template>
                </draggable>

                <input type="file" ref="fileInput" accept="image/*" @change="handleImageSelect" class="hidden" />
                <button type="button" @click="$refs.fileInput.click()"
                  class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1"
                  :disabled="uploading"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                  {{ uploading ? 'Загрузка...' : 'Добавить картинку' }}
                </button>
              </div>
            </div>

            <div v-if="error" class="text-red-500 text-xs">{{ error }}</div>

            <!-- Delete confirmation -->
            <div v-if="confirmingDelete" class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-200 dark:border-red-800">
              <p class="text-sm text-red-600 dark:text-red-400 mb-2">Удалить сферу «{{ sphere?.name }}»? Это действие нельзя отменить.</p>
              <div class="flex gap-2">
                <button type="button" @click="confirmingDelete = false" class="px-3 py-1 text-xs text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-800 rounded-md transition-colors">Отмена</button>
                <button type="button" @click="doDelete" :disabled="deleting" class="px-3 py-1 text-xs bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 transition-colors">
                  {{ deleting ? 'Удаление...' : 'Да, удалить' }}
                </button>
              </div>
            </div>

            <div v-else class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
              <div>
                <button
                  v-if="sphere"
                  type="button"
                  @click="handleDelete"
                  :disabled="deleting || !canDelete"
                  class="px-3 py-1.5 text-sm rounded-md transition-colors"
                  :class="canDelete
                    ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20'
                    : 'text-gray-300 dark:text-gray-600 cursor-not-allowed'"
                  :title="canDelete ? 'Удалить сферу' : 'Нельзя удалить сферу с задачами или целями'"
                >
                  Удалить
                </button>
              </div>
              <div class="flex gap-2">
                <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
                <button type="submit" :disabled="saving || showCropper" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md disabled:opacity-50 hover:opacity-90">
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
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import draggable from 'vuedraggable'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'

const CROP_ASPECT = 3 / 4

const props = defineProps({
  show: { type: Boolean, default: false },
  sphere: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved', 'deleted'])
const store = useLifeSpheresStore()

const form = ref(getDefaultForm())
const saving = ref(false)
const deleting = ref(false)
const confirmingDelete = ref(false)
const uploading = ref(false)
const error = ref('')
const fileInput = ref(null)

// Gallery state
const galleryItems = ref([]) // { key, id?, path?, url?, preview?, file? }
const removedImageIds = ref([])
const pendingFiles = ref([]) // files to upload after save (for new spheres)

// Cropper state
const showCropper = ref(false)
const cropperSrc = ref(null)
const cropperImage = ref(null)
let cropperInstance = null

const canDelete = computed(() => {
  if (!props.sphere) return false
  return (props.sphere.tasks_count || 0) === 0 && (props.sphere.goals_count || 0) === 0
})

function getDefaultForm() {
  return { name: '', color: '#3B82F6', description: '' }
}

watch(() => props.show, (val) => {
  if (val) {
    if (props.sphere) {
      form.value = {
        name: props.sphere.name || '',
        color: props.sphere.color || '#3B82F6',
        description: props.sphere.description || '',
      }
      // Load existing images
      galleryItems.value = (props.sphere.images || []).map(img => ({
        key: `existing-${img.id}`,
        id: img.id,
        path: img.path,
        url: img.url,
      }))
    } else {
      form.value = getDefaultForm()
      galleryItems.value = []
    }
    removedImageIds.value = []
    pendingFiles.value = []
    confirmingDelete.value = false
    showCropper.value = false
    error.value = ''
    destroyCropper()
  }
})

function destroyCropper() {
  if (cropperInstance) {
    cropperInstance.destroy()
    cropperInstance = null
  }
}

const handleImageSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => {
    cropperSrc.value = ev.target.result
    showCropper.value = true
    nextTick(() => initCropper())
  }
  reader.readAsDataURL(file)
  e.target.value = ''
}

function initCropper() {
  destroyCropper()
  if (!cropperImage.value) return
  cropperInstance = new Cropper(cropperImage.value, {
    aspectRatio: CROP_ASPECT,
    viewMode: 1,
    dragMode: 'move',
    autoCropArea: 0.9,
    responsive: true,
    background: false,
  })
}

const applyCrop = () => {
  if (!cropperInstance) return
  const canvas = cropperInstance.getCroppedCanvas({
    width: 600,
    height: 800,
    imageSmoothingEnabled: true,
    imageSmoothingQuality: 'high',
  })
  const preview = canvas.toDataURL('image/jpeg', 0.9)
  canvas.toBlob((blob) => {
    const file = new File([blob], 'sphere.jpg', { type: 'image/jpeg' })
    const key = `new-${Date.now()}`

    if (props.sphere) {
      // Existing sphere — upload immediately
      uploadImageNow(file, preview, key)
    } else {
      // New sphere — queue for after save
      galleryItems.value.push({ key, preview, file })
      pendingFiles.value.push(file)
    }
  }, 'image/jpeg', 0.9)
  showCropper.value = false
  destroyCropper()
}

const uploadImageNow = async (file, preview, key) => {
  uploading.value = true
  try {
    const image = await store.addImage(props.sphere.id, file)
    galleryItems.value.push({
      key: `existing-${image.id}`,
      id: image.id,
      path: image.path,
      url: image.url,
    })
  } catch (e) {
    // Show preview even if upload failed, with retry option
    error.value = 'Ошибка загрузки картинки'
  } finally {
    uploading.value = false
  }
}

const cancelCrop = () => {
  showCropper.value = false
  destroyCropper()
}

const removeGalleryItem = async (item) => {
  if (item.id && props.sphere) {
    // Existing image — delete from server
    try {
      await store.deleteImage(props.sphere.id, item.id)
    } catch (e) {
      error.value = 'Ошибка удаления картинки'
      return
    }
  }
  galleryItems.value = galleryItems.value.filter(g => g.key !== item.key)
  if (item.file) {
    pendingFiles.value = pendingFiles.value.filter(f => f !== item.file)
  }
}

const handleGalleryReorder = () => {
  if (!props.sphere) return
  const existingIds = galleryItems.value.filter(g => g.id).map(g => g.id)
  if (existingIds.length > 1) {
    store.reorderImages(props.sphere.id, existingIds)
  }
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

    if (props.sphere) {
      await store.update(props.sphere.id, data)
    } else {
      // Create sphere, then upload pending images
      if (pendingFiles.value.length > 0) {
        data.image = pendingFiles.value[0] // first image via create endpoint
      }
      data.position = store.allSpheres.length
      const created = await store.create(data)
      // Upload remaining images
      for (let i = 1; i < pendingFiles.value.length; i++) {
        await store.addImage(created.id, pendingFiles.value[i])
      }
    }
    emit('saved')
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка при сохранении'
  } finally {
    saving.value = false
  }
}

const handleDelete = () => {
  if (!canDelete.value) return
  confirmingDelete.value = true
}

const doDelete = async () => {
  deleting.value = true
  error.value = ''
  try {
    await store.remove(props.sphere.id)
    confirmingDelete.value = false
    emit('deleted')
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка при удалении'
  } finally {
    deleting.value = false
  }
}

onBeforeUnmount(() => destroyCropper())
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
