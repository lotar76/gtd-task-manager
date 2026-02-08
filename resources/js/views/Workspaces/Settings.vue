<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
        Настройки — {{ workspace?.name || 'Пространство' }}
      </h1>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="flex space-x-6">
          <button
            v-if="canManage"
            @click="activeTab = 'general'"
            class="pb-3 text-sm font-medium border-b-2 transition-colors"
            :class="activeTab === 'general'
              ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
              : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            Общие
          </button>
          <button
            @click="activeTab = 'members'"
            class="pb-3 text-sm font-medium border-b-2 transition-colors"
            :class="activeTab === 'members'
              ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
              : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            Участники
          </button>
          <button
            @click="activeTab = 'telegram'"
            class="pb-3 text-sm font-medium border-b-2 transition-colors"
            :class="activeTab === 'telegram'
              ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
              : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
          >
            Telegram
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <GeneralTab v-if="activeTab === 'general' && canManage" :key="workspaceId" :workspace="workspace" />
      <MembersTab v-if="activeTab === 'members'" :key="workspaceId" :workspace="workspace" :can-manage="canManage" />
      <TelegramTab v-if="activeTab === 'telegram'" :key="workspaceId" :workspace="workspace" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import { useAuthStore } from '@/stores/auth'
import GeneralTab from './tabs/GeneralTab.vue'
import MembersTab from './tabs/MembersTab.vue'
import TelegramTab from './tabs/TelegramTab.vue'

const route = useRoute()
const router = useRouter()
const workspaceStore = useWorkspaceStore()
const authStore = useAuthStore()

const workspaceId = computed(() => parseInt(route.params.id))
const workspace = computed(() => workspaceStore.workspaces.find(ws => ws.id === workspaceId.value))

const canManage = computed(() => {
  const ws = workspace.value
  const userId = authStore.user?.id
  if (!ws || !userId) return false
  if (ws.owner_id === userId) return true
  const member = ws.members?.find(m => m.id === userId)
  return member?.pivot?.role === 'admin'
})

const defaultTab = computed(() => canManage.value ? 'general' : 'members')

const activeTab = ref(route.query.tab || defaultTab.value)

watch(activeTab, (tab) => {
  router.replace({ query: { ...route.query, tab } })
})
</script>
