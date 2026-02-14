<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ goal ? 'Редактировать цель' : 'Новая цель' }}
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
              <!-- Workspace -->
              <div v-if="workspaces.length > 1">
                <label for="goal_workspace_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Рабочее пространство *
                </label>
                <select
                  id="goal_workspace_id"
                  v-model="form.workspace_id"
                  required
                  class="input"
                >
                  <option v-for="ws in workspaces" :key="ws.id" :value="ws.id">
                    {{ ws.name }}
                  </option>
                </select>
              </div>

              <!-- Сфера жизни -->
              <div>
                <label for="goal_life_sphere_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Сфера жизни
                </label>
                <select
                  id="goal_life_sphere_id"
                  v-model="form.life_sphere_id"
                  class="input"
                >
                  <option :value="null">Без сферы</option>
                  <option v-for="sphere in availableSpheres" :key="sphere.id" :value="sphere.id">
                    {{ sphere.name }}
                  </option>
                </select>
              </div>

              <!-- Что (Name) -->
              <div>
                <label for="goal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Что *
                </label>
                <input
                  id="goal_name"
                  v-model="form.name"
                  type="text"
                  required
                  class="input"
                  placeholder="Что хочу достичь?"
                />
              </div>

              <!-- Когда (Deadline) -->
              <div>
                <label for="goal_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Когда
                </label>
                <input
                  id="goal_deadline"
                  v-model="form.deadline"
                  type="date"
                  class="input"
                />
              </div>

              <!-- Зачем (Description) -->
              <div>
                <label for="goal_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Зачем
                </label>
                <textarea
                  id="goal_description"
                  v-model="form.description"
                  rows="2"
                  class="input"
                  placeholder="Зачем мне это нужно?"
                ></textarea>
              </div>

              <!-- Стих из библии -->
              <div>
                <label for="goal_bible_verse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Стих из библии
                </label>
                <textarea
                  id="goal_bible_verse"
                  v-model="form.bible_verse"
                  rows="2"
                  class="input"
                  placeholder="Стих для вдохновения"
                ></textarea>
              </div>

              <!-- Картинка -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Картинка
                </label>
                <div v-if="imagePreview || form.image_url" class="relative mb-2">
                  <img :src="imagePreview || form.image_url" class="w-full h-48 object-cover rounded-lg" />
                  <button
                    type="button"
                    @click="removeImage"
                    class="absolute top-2 right-2 bg-black bg-opacity-50 text-white rounded-full p-1 hover:bg-opacity-70 transition-opacity"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <input
                  type="file"
                  ref="fileInput"
                  accept="image/*"
                  @change="handleImageSelect"
                  class="hidden"
                />
                <button
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="w-full px-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-500 dark:text-gray-400 hover:border-primary-500 hover:text-primary-500 transition-colors text-sm"
                >
                  {{ imagePreview || form.image_url ? 'Заменить изображение' : 'Выбрать изображение' }}
                </button>
              </div>

              <div v-if="error" class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                {{ error }}
              </div>

              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="$emit('close')"
                  class="btn btn-secondary"
                >
                  Отмена
                </button>
                <button
                  type="submit"
                  :disabled="loading"
                  class="btn btn-primary"
                >
                  {{ loading ? 'Сохранение...' : 'Сохранить' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  goal: {
    type: Object,
    default: null,
  },
  serverError: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'submit'])

const workspaceStore = useWorkspaceStore()
const lifeSpheresStore = useLifeSpheresStore()
const workspaces = computed(() => workspaceStore.workspaces)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)

const availableSpheres = computed(() => {
  const wsId = form.value.workspace_id
  if (!wsId) return lifeSpheresStore.allSpheres
  return lifeSpheresStore.allSpheres.filter(s => s.workspace_id === wsId)
})

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) emit('close')
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const form = ref({
  workspace_id: null,
  life_sphere_id: null,
  name: '',
  deadline: '',
  description: '',
  bible_verse: '',
  image_url: null,
  imageFile: null,
})

const loading = ref(false)
const error = ref('')
const fileInput = ref(null)
const imagePreview = ref(null)

watch(() => props.goal, (newGoal) => {
  if (newGoal) {
    form.value = {
      workspace_id: newGoal.workspace_id || currentWorkspace.value?.id,
      life_sphere_id: newGoal.life_sphere_id || null,
      name: newGoal.name || '',
      deadline: newGoal.deadline ? newGoal.deadline.substring(0, 10) : '',
      description: newGoal.description || '',
      bible_verse: newGoal.bible_verse || '',
      image_url: newGoal.image_url || null,
      imageFile: null,
    }
  } else {
    form.value = {
      workspace_id: currentWorkspace.value?.id,
      life_sphere_id: null,
      name: '',
      deadline: '',
      description: '',
      bible_verse: '',
      image_url: null,
      imageFile: null,
    }
  }
  imagePreview.value = null
  error.value = ''
}, { immediate: true })

watch(() => currentWorkspace.value?.id, (newWorkspaceId) => {
  if (!form.value.name && newWorkspaceId) {
    form.value.workspace_id = newWorkspaceId
  }
})

watch(() => props.show, (newShow) => {
  if (!newShow) {
    loading.value = false
    error.value = ''
    imagePreview.value = null
  }
})

watch(() => props.serverError, (newError) => {
  error.value = newError
  if (newError) {
    loading.value = false
  }
})

const handleImageSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (file.size > 5 * 1024 * 1024) {
    error.value = 'Максимальный размер файла 5MB'
    return
  }

  if (!file.type.startsWith('image/')) {
    error.value = 'Выберите изображение'
    return
  }

  form.value.imageFile = file
  imagePreview.value = URL.createObjectURL(file)
  error.value = ''
}

const removeImage = () => {
  form.value.imageFile = null
  form.value.image_url = null
  imagePreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleSubmit = () => {
  error.value = ''
  loading.value = true
  emit('submit', { ...form.value })
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
