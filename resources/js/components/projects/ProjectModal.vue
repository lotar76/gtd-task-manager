<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ project ? 'Редактировать проект' : 'Новый проект' }}
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Название проекта *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="input"
                  placeholder="Введите название проекта"
                />
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Описание
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="input"
                  placeholder="Добавьте описание проекта"
                ></textarea>
              </div>

              <!-- Color -->
              <div>
                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Цвет
                </label>
                <div class="flex items-center space-x-3">
                  <input
                    id="color"
                    v-model="form.color"
                    type="color"
                    class="w-16 h-10 rounded border border-gray-300 dark:border-gray-600 cursor-pointer"
                  />
                  <input
                    v-model="form.color"
                    type="text"
                    pattern="^#[0-9A-Fa-f]{6}$"
                    class="input flex-1"
                    placeholder="#3B82F6"
                  />
                </div>
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
import { ref, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  project: {
    type: Object,
    default: null,
  },
  serverError: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close', 'submit'])

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) emit('close')
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const form = ref({
  name: '',
  description: '',
  color: '#3B82F6',
})

const loading = ref(false)
const error = ref('')

watch(() => props.project, (newProject) => {
  if (newProject) {
    form.value = {
      name: newProject.name || '',
      description: newProject.description || '',
      color: newProject.color || '#3B82F6',
    }
  } else {
    form.value = {
      name: '',
      description: '',
      color: '#3B82F6',
    }
  }
  error.value = ''
}, { immediate: true })

watch(() => props.show, (newShow) => {
  if (!newShow) {
    loading.value = false
    error.value = ''
  }
})

watch(() => props.serverError, (newError) => {
  error.value = newError
  if (newError) {
    loading.value = false
  }
})

const handleSubmit = () => {
  error.value = ''
  loading.value = true
  emit('submit', form.value)
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


