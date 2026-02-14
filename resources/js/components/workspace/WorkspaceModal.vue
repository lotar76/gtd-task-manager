<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-auto" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                Новое рабочее пространство
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Название *
                </label>
                <div class="flex items-center gap-2">
                  <div class="relative">
                    <button
                      type="button"
                      @click="showEmojiPicker = !showEmojiPicker"
                      class="emoji-btn flex-shrink-0 w-10 h-10 flex items-center justify-center text-xl border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      title="Выбрать иконку"
                    >
                      {{ form.emoji || '◆' }}
                    </button>
                    <div v-if="showEmojiPicker" class="absolute top-12 left-0 z-10 emoji-picker-mono">
                      <EmojiPicker
                        :native="true"
                        :disable-skin-tones="true"
                        :display-recent="true"
                        @select="handleEmojiSelect"
                      />
                    </div>
                  </div>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="input flex-1"
                    placeholder="Например: Команда разработки"
                  />
                </div>
              </div>

              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Описание
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="input"
                  placeholder="Краткое описание рабочего пространства"
                ></textarea>
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
                  {{ loading ? 'Создание...' : 'Создать' }}
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
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
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
  emoji: '',
  description: '',
})

const loading = ref(false)
const error = ref('')
const showEmojiPicker = ref(false)

const handleEmojiSelect = (emoji) => {
  form.value.emoji = emoji.i
  showEmojiPicker.value = false
}

watch(() => props.show, (newShow) => {
  if (!newShow) {
    form.value = {
      name: '',
      emoji: '',
      description: '',
    }
    loading.value = false
    error.value = ''
    showEmojiPicker.value = false
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

.emoji-btn {
  filter: grayscale(1);
}

.emoji-picker-mono :deep(.v3-emoji-picker) {
  filter: grayscale(1);
}
</style>
