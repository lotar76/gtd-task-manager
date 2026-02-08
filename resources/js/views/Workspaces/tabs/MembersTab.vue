<template>
  <div class="space-y-6">
    <!-- Добавить участника (owner/admin) -->
    <div v-if="canManage" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Добавить участника</h2>

      <form @submit.prevent="handleAdd" class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="addForm.email"
          type="email"
          class="input flex-1"
          placeholder="Email пользователя"
          required
        />
        <select v-model="addForm.role" class="input w-full sm:w-36">
          <option value="viewer">Просмотр</option>
          <option value="member">Участник</option>
          <option value="admin">Админ</option>
        </select>
        <button type="submit" :disabled="addLoading" class="btn btn-primary whitespace-nowrap">
          {{ addLoading ? '...' : 'Добавить' }}
        </button>
      </form>

      <div v-if="addError" class="mt-3 text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
        {{ addError }}
      </div>
      <div v-if="addSuccess" class="mt-3 text-green-600 dark:text-green-400 text-sm bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
        {{ addSuccess }}
      </div>
    </div>

    <!-- Список участников -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          Участники
          <span v-if="members.length > 0" class="text-sm font-normal text-gray-500 dark:text-gray-400">
            ({{ members.length }})
          </span>
        </h2>
      </div>

      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <div
          v-for="member in members"
          :key="member.id"
          class="flex items-center justify-between p-4"
        >
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-medium text-primary-700 dark:text-primary-400">
              {{ getInitials(member.name) }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ member.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ member.email }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span
              class="px-2 py-1 text-xs font-medium rounded-full"
              :class="getRoleBadgeClass(member.pivot?.role || (member.id === workspace?.owner_id ? 'owner' : 'member'))"
            >
              {{ getRoleLabel(member.pivot?.role || (member.id === workspace?.owner_id ? 'owner' : 'member')) }}
            </span>
            <button
              v-if="canManage && member.id !== workspace?.owner_id && member.id !== currentUserId"
              @click="handleRemove(member)"
              class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
              title="Удалить"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  workspace: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
})

const workspaceStore = useWorkspaceStore()
const authStore = useAuthStore()

const members = ref([])
const loading = ref(true)
const addLoading = ref(false)
const addError = ref('')
const addSuccess = ref('')
const addForm = ref({ email: '', role: 'member' })
const currentUserId = computed(() => authStore.user?.id)

const loadMembers = async () => {
  if (!props.workspace?.id) return
  loading.value = true
  try {
    const response = await workspaceStore.fetchMembers(props.workspace.id)
    members.value = response || []
  } catch (e) {
    console.error('Error loading members:', e)
  } finally {
    loading.value = false
  }
}

const handleAdd = async () => {
  addError.value = ''
  addSuccess.value = ''
  addLoading.value = true
  try {
    await workspaceStore.addMember(props.workspace.id, addForm.value)
    addSuccess.value = `${addForm.value.email} добавлен`
    addForm.value.email = ''
    await loadMembers()
  } catch (e) {
    addError.value = e.response?.data?.message || 'Ошибка добавления'
  } finally {
    addLoading.value = false
  }
}

const handleRemove = async (member) => {
  if (!confirm(`Удалить ${member.name} из пространства?`)) return
  try {
    await workspaceStore.removeMember(props.workspace.id, member.id)
    await loadMembers()
  } catch (e) {
    console.error('Error removing member:', e)
  }
}

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
}

const getRoleBadgeClass = (role) => {
  const classes = {
    owner: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    admin: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    member: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    viewer: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
  }
  return classes[role] || classes.member
}

const getRoleLabel = (role) => {
  const labels = { owner: 'Владелец', admin: 'Админ', member: 'Участник', viewer: 'Просмотр' }
  return labels[role] || role
}

onMounted(loadMembers)
</script>
