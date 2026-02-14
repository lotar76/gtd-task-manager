<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Сферы жизни</h2>
      <button
        v-if="spheres.length === 0"
        @click="handleSeedSpheres"
        :disabled="loading"
        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
      >
        Заполнить по умолчанию
      </button>
    </div>

    <!-- Список сфер -->
    <div v-if="spheres.length > 0" class="space-y-2 mb-4">
      <div
        v-for="sphere in spheres"
        :key="sphere.id"
        class="flex items-center gap-3 group"
      >
        <!-- Цвет -->
        <input
          type="color"
          :value="sphere.color"
          @change="handleUpdateSphere(sphere.id, { color: $event.target.value })"
          class="w-7 h-7 rounded cursor-pointer border border-gray-300 dark:border-gray-600 p-0.5"
        />
        <!-- Название (редактируемое) -->
        <input
          v-if="editingSphereId === sphere.id"
          v-model="editingSphereValue"
          @blur="saveSphereName(sphere.id)"
          @keydown.enter="saveSphereName(sphere.id)"
          @keydown.escape="editingSphereId = null"
          ref="sphereNameInput"
          class="input flex-1 text-sm py-1.5"
          autofocus
        />
        <span
          v-else
          @click="startEditSphere(sphere)"
          class="flex-1 text-sm text-gray-900 dark:text-white cursor-pointer hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
          {{ sphere.name }}
        </span>
        <!-- Удалить -->
        <button
          @click="handleDeleteSphere(sphere)"
          class="p-1 text-gray-300 dark:text-gray-600 hover:text-red-500 dark:hover:text-red-400 opacity-0 group-hover:opacity-100 transition-all"
          title="Удалить сферу"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <div v-else-if="!loading" class="text-sm text-gray-500 dark:text-gray-400 mb-4">
      Сферы не созданы. Нажмите «Заполнить по умолчанию» или добавьте вручную.
    </div>

    <!-- Добавить сферу -->
    <form @submit.prevent="handleAddSphere" class="flex items-center gap-2">
      <input
        type="color"
        v-model="newSphere.color"
        class="w-7 h-7 rounded cursor-pointer border border-gray-300 dark:border-gray-600 p-0.5"
      />
      <input
        v-model="newSphere.name"
        type="text"
        class="input flex-1 text-sm py-1.5"
        placeholder="Новая сфера..."
      />
      <button
        type="submit"
        :disabled="!newSphere.name.trim() || loading"
        class="p-1.5 text-primary-600 hover:text-primary-700 dark:text-primary-400 disabled:opacity-30 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
      </button>
    </form>

    <div v-if="error" class="text-red-600 dark:text-red-400 text-sm mt-3">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'

const props = defineProps({
  workspace: { type: Object, required: true },
})

const lifeSpheresStore = useLifeSpheresStore()

const loading = ref(false)
const error = ref('')
const editingSphereId = ref(null)
const editingSphereValue = ref('')
const sphereNameInput = ref(null)
const newSphere = ref({ name: '', color: '#3B82F6' })

const spheres = computed(() => {
  return lifeSpheresStore.allSpheres
    .filter(s => s.workspace_id === props.workspace.id)
    .sort((a, b) => a.position - b.position)
})

const startEditSphere = (sphere) => {
  editingSphereId.value = sphere.id
  editingSphereValue.value = sphere.name
  nextTick(() => {
    if (sphereNameInput.value) {
      const input = Array.isArray(sphereNameInput.value) ? sphereNameInput.value[0] : sphereNameInput.value
      input?.focus()
    }
  })
}

const saveSphereName = async (sphereId) => {
  const trimmed = editingSphereValue.value.trim()
  if (!trimmed) {
    editingSphereId.value = null
    return
  }
  try {
    await lifeSpheresStore.update(props.workspace.id, sphereId, { name: trimmed })
  } catch (e) {
    error.value = 'Ошибка при переименовании'
  }
  editingSphereId.value = null
}

const handleUpdateSphere = async (sphereId, data) => {
  try {
    await lifeSpheresStore.update(props.workspace.id, sphereId, data)
  } catch (e) {
    error.value = 'Ошибка при обновлении'
  }
}

const handleDeleteSphere = async (sphere) => {
  if (!confirm(`Удалить сферу «${sphere.name}»? Цели останутся без сферы.`)) return
  try {
    await lifeSpheresStore.remove(props.workspace.id, sphere.id)
  } catch (e) {
    error.value = 'Ошибка при удалении'
  }
}

const handleAddSphere = async () => {
  const name = newSphere.value.name.trim()
  if (!name) return
  error.value = ''
  loading.value = true
  try {
    await lifeSpheresStore.create(props.workspace.id, {
      name,
      color: newSphere.value.color,
      position: spheres.value.length,
    })
    newSphere.value.name = ''
    newSphere.value.color = '#3B82F6'
  } catch (e) {
    error.value = 'Ошибка при создании'
  } finally {
    loading.value = false
  }
}

const handleSeedSpheres = async () => {
  error.value = ''
  loading.value = true
  try {
    await lifeSpheresStore.seedDefaults(props.workspace.id)
  } catch (e) {
    error.value = 'Ошибка при создании сфер по умолчанию'
  } finally {
    loading.value = false
  }
}
</script>
