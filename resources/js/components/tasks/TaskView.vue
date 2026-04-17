<template>
  <TaskDetail
    :show="show"
    :task="fullTask"
    :projects="projectsStore.activeProjects"
    :goals="goalsStore.activeGoals"
    :life-spheres="lifeSpheresStore.allSpheres"
    :contexts="contextsStore.allContexts"
    :contacts="contacts"
    :current-user-id="currentUserId"
    :my-workspace-id="myWorkspaceId"
    @close="$emit('close')"
    @saved="handleSaved"
    @completed="handleCompleted"
    @uncompleted="handleUncompleted"
  />
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import api from '@/services/api'
import TaskDetail from '@/components/tasks/TaskDetail.vue'
import { useProjectsStore } from '@/stores/projects'
import { useGoalsStore } from '@/stores/goals'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import { useContextsStore } from '@/stores/contexts'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'

const authStore = useAuthStore()
const workspaceStore = useWorkspaceStore()

const currentUserId = computed(() => authStore.user?.id || null)
const myWorkspaceId = computed(() => {
  const uid = currentUserId.value
  if (!uid) return null
  const owned = workspaceStore.workspaces.find(w => w.owner_id === uid)
  return owned?.id || workspaceStore.workspaces[0]?.id || null
})

const props = defineProps({
  show: { type: Boolean, default: false },
  task: { type: Object, default: null },
})

const emit = defineEmits(['close', 'enter-edit', 'complete-task', 'uncomplete-task', 'saved'])

const projectsStore = useProjectsStore()
const goalsStore = useGoalsStore()
const lifeSpheresStore = useLifeSpheresStore()
const contextsStore = useContextsStore()

const fullTask = ref(null)
const contacts = ref([])

const ensureStores = async () => {
  const jobs = []
  if (!projectsStore.loaded && !projectsStore.loading && typeof projectsStore.fetchAll === 'function') jobs.push(projectsStore.fetchAll())
  if (!goalsStore.loaded && !goalsStore.loading && typeof goalsStore.fetchAll === 'function') jobs.push(goalsStore.fetchAll())
  if (!lifeSpheresStore.loaded && !lifeSpheresStore.loading && typeof lifeSpheresStore.fetchAll === 'function') jobs.push(lifeSpheresStore.fetchAll())
  if (typeof contextsStore.fetchAll === 'function') jobs.push(contextsStore.fetchAll())
  if (workspaceStore.workspaces.length === 0 && typeof workspaceStore.fetchWorkspaces === 'function') jobs.push(workspaceStore.fetchWorkspaces())
  await Promise.all(jobs)
}

const loadContacts = async () => {
  try {
    const res = await api.get('/v1/contacts')
    contacts.value = res.data || []
  } catch (e) { contacts.value = [] }
}

const loadFullTask = async (id) => {
  if (!id) return
  try {
    const res = await api.get(`/v1/tasks/${id}`)
    fullTask.value = res.data
  } catch (e) {
    fullTask.value = props.task
  }
}

watch(() => [props.show, props.task?.id], async ([s, id]) => {
  if (s && id) {
    fullTask.value = props.task
    await Promise.all([ensureStores(), loadContacts(), loadFullTask(id)])
  } else if (s && props.task) {
    // Черновик без id — ставим как есть
    fullTask.value = { ...props.task }
    await Promise.all([ensureStores(), loadContacts()])
  } else if (!s) {
    fullTask.value = null
  }
}, { immediate: true })

const handleSaved = (task) => emit('saved', task)
const handleCompleted = (task) => emit('complete-task', task)
const handleUncompleted = (task) => emit('uncomplete-task', task)
</script>
