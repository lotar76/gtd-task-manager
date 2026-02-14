<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                {{ project ? 'Редактировать поток' : 'Новый поток' }}
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
                <label for="workspace_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Рабочее пространство *
                </label>
                <select
                  id="workspace_id"
                  v-model="form.workspace_id"
                  required
                  class="input"
                >
                  <option v-for="ws in workspaces" :key="ws.id" :value="ws.id">
                    {{ ws.name }}
                  </option>
                </select>
              </div>

              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Название потока *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="input"
                  placeholder="Введите название потока"
                />
              </div>

              <!-- Goal -->
              <div>
                <label for="goal_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Цель
                </label>
                <select
                  id="goal_id"
                  v-model="form.goal_id"
                  class="input"
                >
                  <option :value="null">Без цели</option>
                  <option v-for="goal in availableGoals" :key="goal.id" :value="goal.id">
                    {{ goal.name }}
                  </option>
                </select>
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
                  placeholder="Добавьте описание потока"
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
import { useGoalsStore } from '@/stores/goals'

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

const workspaceStore = useWorkspaceStore()
const goalsStore = useGoalsStore()
const workspaces = computed(() => workspaceStore.workspaces)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)

const availableGoals = computed(() => {
  const wsId = form.value.workspace_id
  if (!wsId) return goalsStore.activeGoals
  return goalsStore.activeGoals.filter(g => g.workspace_id === wsId)
})

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) emit('close')
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const form = ref({
  workspace_id: null,
  goal_id: null,
  name: '',
  description: '',
})

const loading = ref(false)
const error = ref('')

watch(() => props.project, (newProject) => {
  if (newProject) {
    form.value = {
      workspace_id: newProject.workspace_id || currentWorkspace.value?.id,
      goal_id: newProject.goal_id || null,
      name: newProject.name || '',
      description: newProject.description || '',
    }
  } else {
    form.value = {
      workspace_id: currentWorkspace.value?.id,
      goal_id: null,
      name: '',
      description: '',
    }
  }
  error.value = ''
}, { immediate: true })

// Обновляем workspace_id при изменении currentWorkspace (если форма пустая)
watch(() => currentWorkspace.value?.id, (newWorkspaceId) => {
  if (!form.value.name && newWorkspaceId) {
    form.value.workspace_id = newWorkspaceId
  }
})

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


