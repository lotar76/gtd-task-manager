<template>
  <div class="space-y-6">
    <!-- Название и описание -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Основные</h2>

      <form @submit.prevent="handleSave" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Название
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
              v-model="form.name"
              type="text"
              class="input flex-1"
              required
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Описание
          </label>
          <textarea
            v-model="form.description"
            class="input"
            rows="3"
            placeholder="Описание пространства (необязательно)"
          />
        </div>

        <div v-if="error" class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
          {{ error }}
        </div>
        <div v-if="success" class="text-green-600 dark:text-green-400 text-sm bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
          {{ success }}
        </div>

        <button type="submit" :disabled="loading" class="btn btn-primary">
          {{ loading ? 'Сохранение...' : 'Сохранить' }}
        </button>
      </form>
    </div>

    <!-- Библейские стихи -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Библейские стихи</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Показывать цитаты из Библии в Зеркале жизни
          </p>
        </div>
        <button
          type="button"
          @click="toggleFaith"
          :disabled="faithLoading"
          class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
          :class="form.faith_enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'"
          role="switch"
          :aria-checked="form.faith_enabled"
        >
          <span
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            :class="form.faith_enabled ? 'translate-x-5' : 'translate-x-0'"
          />
        </button>
      </div>
    </div>

    <!-- Опасная зона -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-900/50 p-6">
      <h2 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-2">Опасная зона</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Удаление пространства приведёт к удалению всех задач, потоков и данных.
      </p>
      <button
        @click="handleDelete"
        :disabled="deleteLoading"
        class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
      >
        {{ deleteLoading ? 'Удаление...' : 'Удалить пространство' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import EmojiPicker from 'vue3-emoji-picker'
import 'vue3-emoji-picker/css'

const props = defineProps({
  workspace: { type: Object, required: true },
})

const router = useRouter()
const workspaceStore = useWorkspaceStore()

const form = ref({ name: '', emoji: '', description: '', faith_enabled: true })
const loading = ref(false)
const faithLoading = ref(false)
const deleteLoading = ref(false)
const error = ref('')
const success = ref('')
const showEmojiPicker = ref(false)

const handleEmojiSelect = (emoji) => {
  form.value.emoji = emoji.i
  showEmojiPicker.value = false
}

onMounted(() => {
  if (props.workspace) {
    form.value.name = props.workspace.name || ''
    form.value.emoji = props.workspace.emoji || ''
    form.value.description = props.workspace.description || ''
    form.value.faith_enabled = props.workspace.faith_enabled !== false
  }
})

const handleSave = async () => {
  error.value = ''
  success.value = ''
  loading.value = true
  try {
    await workspaceStore.updateWorkspace(props.workspace.id, form.value)
    success.value = 'Настройки сохранены'
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка сохранения'
  } finally {
    loading.value = false
  }
}

const toggleFaith = async () => {
  faithLoading.value = true
  try {
    form.value.faith_enabled = !form.value.faith_enabled
    await workspaceStore.updateWorkspace(props.workspace.id, {
      faith_enabled: form.value.faith_enabled,
    })
  } catch (e) {
    form.value.faith_enabled = !form.value.faith_enabled
    error.value = e.response?.data?.message || 'Ошибка сохранения'
  } finally {
    faithLoading.value = false
  }
}

const handleDelete = async () => {
  if (!confirm(`Удалить пространство "${props.workspace?.name}"? Это действие необратимо.`)) return

  deleteLoading.value = true
  try {
    await workspaceStore.deleteWorkspace(props.workspace.id)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Ошибка удаления'
  } finally {
    deleteLoading.value = false
  }
}
</script>

<style scoped>
.emoji-btn {
  filter: grayscale(1);
}

.emoji-picker-mono :deep(.v3-emoji-picker) {
  filter: grayscale(1);
}
</style>
