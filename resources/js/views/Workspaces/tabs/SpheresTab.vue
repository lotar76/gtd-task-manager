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
        :class="{ 'opacity-40': sphere.is_hidden }"
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

        <!-- Кол-во задач -->
        <span
          v-if="sphere.tasks_count > 0"
          class="text-[10px] font-medium text-gray-400 dark:text-gray-500 tabular-nums"
        >
          {{ sphere.tasks_count }}
        </span>

        <!-- Скрыть/Показать -->
        <button
          @click="handleToggleHidden(sphere)"
          class="p-1 text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 opacity-0 group-hover:opacity-100 transition-all"
          :class="{ '!opacity-100': sphere.is_hidden }"
          :title="sphere.is_hidden ? 'Показать сферу' : 'Скрыть сферу'"
        >
          <!-- Eye open -->
          <svg v-if="!sphere.is_hidden" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
          </svg>
          <!-- Eye closed -->
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
          </svg>
        </button>

        <!-- Удалить -->
        <button
          @click="confirmDeleteSphere(sphere)"
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

    <!-- Confirm Dialog -->
    <ConfirmDialog
      :show="showDeleteConfirm"
      title="Удалить сферу"
      :message="deleteConfirmMessage"
      confirm-text="Удалить"
      cancel-text="Отмена"
      variant="danger"
      @confirm="handleDeleteSphere"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import { useDashboardStore } from '@/stores/dashboard'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'

const props = defineProps({
  workspace: { type: Object, required: true },
})

const lifeSpheresStore = useLifeSpheresStore()
const dashboardStore = useDashboardStore()

const loading = ref(false)
const error = ref('')
const editingSphereId = ref(null)
const editingSphereValue = ref('')
const sphereNameInput = ref(null)
const newSphere = ref({ name: '', color: '#3B82F6' })

// Delete confirmation
const showDeleteConfirm = ref(false)
const sphereToDelete = ref(null)
const deleteConfirmMessage = computed(() => {
  if (!sphereToDelete.value) return ''
  return `Удалить сферу «${sphereToDelete.value.name}»? Это действие нельзя отменить.`
})

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
    await lifeSpheresStore.update(sphereId, { name: trimmed })
  } catch (e) {
    error.value = 'Ошибка при переименовании'
  }
  editingSphereId.value = null
}

const handleUpdateSphere = async (sphereId, data) => {
  try {
    await lifeSpheresStore.update(sphereId, data)
  } catch (e) {
    error.value = 'Ошибка при обновлении'
  }
}

const handleToggleHidden = async (sphere) => {
  try {
    await lifeSpheresStore.update(sphere.id, { is_hidden: !sphere.is_hidden })
    dashboardStore.invalidate?.()
  } catch (e) {
    error.value = 'Ошибка при обновлении'
  }
}

const confirmDeleteSphere = (sphere) => {
  if (sphere.tasks_count > 0) {
    error.value = `Нельзя удалить сферу «${sphere.name}» — к ней привязано ${sphere.tasks_count} задач. Открепите задачи или скройте сферу.`
    return
  }
  error.value = ''
  sphereToDelete.value = sphere
  showDeleteConfirm.value = true
}

const handleDeleteSphere = async () => {
  showDeleteConfirm.value = false
  if (!sphereToDelete.value) return
  try {
    await lifeSpheresStore.remove(sphereToDelete.value.id)
  } catch (e) {
    const msg = e.response?.data?.message || 'Ошибка при удалении'
    error.value = msg
  }
  sphereToDelete.value = null
}

const handleAddSphere = async () => {
  const name = newSphere.value.name.trim()
  if (!name) return
  error.value = ''
  loading.value = true
  try {
    await lifeSpheresStore.create({
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
    await lifeSpheresStore.seedDefaults()
  } catch (e) {
    error.value = 'Ошибка при создании сфер по умолчанию'
  } finally {
    loading.value = false
  }
}
</script>
