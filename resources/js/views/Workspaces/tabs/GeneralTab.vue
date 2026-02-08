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
          <input
            v-model="form.name"
            type="text"
            class="input"
            required
          />
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

    <!-- Опасная зона -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-900/50 p-6">
      <h2 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-2">Опасная зона</h2>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Удаление пространства приведёт к удалению всех задач, проектов и данных.
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

const props = defineProps({
  workspace: { type: Object, required: true },
})

const router = useRouter()
const workspaceStore = useWorkspaceStore()

const form = ref({ name: '', description: '' })
const loading = ref(false)
const deleteLoading = ref(false)
const error = ref('')
const success = ref('')

onMounted(() => {
  if (props.workspace) {
    form.value.name = props.workspace.name || ''
    form.value.description = props.workspace.description || ''
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
