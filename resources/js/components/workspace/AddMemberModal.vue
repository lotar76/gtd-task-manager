<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-auto" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                Добавить пользователя
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                  Workspace: <span class="font-medium">{{ workspace?.name }}</span>
                </p>
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Email пользователя *
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="input"
                  placeholder="user@example.com"
                />
              </div>

              <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Роль
                </label>
                <select id="role" v-model="form.role" class="input">
                  <option value="viewer">Просмотр</option>
                  <option value="member">Участник</option>
                  <option value="admin">Администратор</option>
                </select>
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
                  {{ loading ? 'Добавление...' : 'Добавить' }}
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
  workspace: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'submit'])

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) emit('close')
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const form = ref({
  email: '',
  role: 'member',
})

const loading = ref(false)
const error = ref('')

watch(() => props.show, (newShow) => {
  if (!newShow) {
    form.value = {
      email: '',
      role: 'member',
    }
    loading.value = false
    error.value = ''
  }
})

const handleSubmit = () => {
  error.value = ''
  loading.value = true
  emit('submit', { ...form.value, workspace_id: props.workspace?.id })
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

