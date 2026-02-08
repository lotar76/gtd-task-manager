<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-auto" @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                Участники workspace
              </h3>
              <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="p-6">
              <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>

              <div v-else-if="members.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                Нет участников
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="member in members"
                  :key="member.id"
                  class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                >
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                      {{ getInitials(member.name) }}
                    </div>
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white">{{ member.name }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">{{ member.email }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-3">
                    <span
                      class="px-3 py-1 text-xs font-medium rounded-full"
                      :class="getRoleBadgeClass(member.pivot?.role || (workspace?.owner_id === member.id ? 'owner' : 'member'))"
                    >
                      {{ getRoleLabel(member.pivot?.role || (workspace?.owner_id === member.id ? 'owner' : 'member')) }}
                    </span>
                    
                    <button
                      v-if="canRemoveMember(member)"
                      @click="handleRemoveMember(member)"
                      class="text-red-600 hover:text-red-700 p-2 rounded transition-colors"
                      :disabled="removingMemberId === member.id"
                      title="Удалить из workspace"
                    >
                      <svg
                        v-if="removingMemberId !== member.id"
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <div v-else class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-red-600"></div>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="error" class="px-6 pb-4">
              <div class="text-red-600 dark:text-red-400 text-sm bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                {{ error }}
              </div>
            </div>

            <div class="flex justify-end p-6 border-t border-gray-200 dark:border-gray-700">
              <button
                @click="$emit('close')"
                class="btn btn-secondary"
              >
                Закрыть
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import { useAuthStore } from '@/stores/auth'

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

const emit = defineEmits(['close', 'member-removed'])

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.show) emit('close')
}
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const workspaceStore = useWorkspaceStore()
const authStore = useAuthStore()

const members = ref([])
const loading = ref(false)
const error = ref('')
const removingMemberId = ref(null)

const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

const getRoleLabel = (role) => {
  const labels = {
    owner: 'Владелец',
    admin: 'Администратор',
    member: 'Участник',
    viewer: 'Наблюдатель',
  }
  return labels[role] || role
}

const getRoleBadgeClass = (role) => {
  const classes = {
    owner: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    admin: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    member: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    viewer: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
  }
  return classes[role] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400'
}

const canRemoveMember = (member) => {
  const currentUserId = authStore.user?.id
  const isOwner = props.workspace?.owner_id === currentUserId
  const isAdmin = props.workspace?.members?.find(m => m.id === currentUserId)?.pivot?.role === 'admin'
  
  // Нельзя удалить владельца
  if (props.workspace?.owner_id === member.id) return false
  
  // Нельзя удалить самого себя
  if (member.id === currentUserId) return false
  
  // Только owner и admin могут удалять
  return isOwner || isAdmin
}

const loadMembers = async () => {
  if (!props.workspace?.id) return
  
  loading.value = true
  error.value = ''
  try {
    const data = await workspaceStore.fetchMembers(props.workspace.id)
    members.value = data.members || data || []
  } catch (err) {
    console.error('Error loading members:', err)
    error.value = 'Ошибка при загрузке участников'
  } finally {
    loading.value = false
  }
}

const handleRemoveMember = async (member) => {
  if (!confirm(`Вы уверены, что хотите удалить ${member.name} из workspace?`)) {
    return
  }
  
  removingMemberId.value = member.id
  error.value = ''
  
  try {
    await workspaceStore.removeMember(props.workspace.id, member.id)
    await loadMembers()
    emit('member-removed', member)
  } catch (err) {
    console.error('Error removing member:', err)
    error.value = err.response?.data?.message || 'Ошибка при удалении участника'
  } finally {
    removingMemberId.value = null
  }
}

watch(() => props.show, (newShow) => {
  if (newShow) {
    loadMembers()
  } else {
    members.value = []
    error.value = ''
    removingMemberId.value = null
  }
})

watch(() => props.workspace?.id, () => {
  if (props.show) {
    loadMembers()
  }
})
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

