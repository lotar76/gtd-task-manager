<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Проекты</h1>
      <button
        @click="showProjectModal = true"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Создать проект</span>
      </button>
    </div>

    <!-- Projects Grid -->
    <div v-if="activeProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="project in activeProjects"
        :key="project.id"
        class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow cursor-pointer"
        @click="$router.push(`/workspaces/${currentWorkspace?.id}/projects/${project.id}`)"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center space-x-3 flex-1 min-w-0">
            <div
              class="w-4 h-4 rounded-full flex-shrink-0"
              :style="{ backgroundColor: project.color || '#3B82F6' }"
            ></div>
            <div class="flex-1 min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                {{ project.name }}
              </h3>
              <p v-if="selectedWorkspaceIds.length > 1" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                {{ getWorkspaceName(project.workspace_id) }}
              </p>
            </div>
          </div>
          <button
            @click.stop="handleArchiveProject(project)"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 transition-colors"
            title="Архивировать"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
          </button>
        </div>

        <p v-if="project.description" class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">
          {{ project.description }}
        </p>

        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500 dark:text-gray-400">
            {{ project.tasks_count || 0 }} задач
          </span>
          <span v-if="project.status === 'active'" class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-full text-xs">
            Активен
          </span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Нет проектов</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-4">Создайте первый проект для организации ваших задач</p>
      <button
        @click="showProjectModal = true"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
      >
        Создать проект
      </button>
    </div>

    <!-- Project Modal -->
    <ProjectModal
      :show="showProjectModal"
      :project="selectedProject"
      :server-error="projectError"
      @close="handleCloseProjectModal"
      @submit="handleSaveProject"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectsStore } from '@/stores/projects'
import { useWorkspaceStore } from '@/stores/workspace'
import ProjectModal from '@/components/projects/ProjectModal.vue'

const router = useRouter()
const projectsStore = useProjectsStore()
const workspaceStore = useWorkspaceStore()

const showProjectModal = ref(false)
const selectedProject = ref(null)
const projectError = ref('')

const activeProjects = computed(() => projectsStore.activeProjects)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)
const workspaces = computed(() => workspaceStore.workspaces)
const selectedWorkspaceIds = computed(() =>
  workspaceStore.selectedWorkspaces.map(ws => ws.id)
)

const getWorkspaceName = (workspaceId) => {
  const workspace = workspaces.value.find(ws => ws.id === workspaceId)
  return workspace?.name || ''
}

const handleSaveProject = async (projectData) => {
  projectError.value = ''
  try {
    if (selectedProject.value) {
      await projectsStore.updateProject(selectedProject.value.id, projectData)
    } else {
      await projectsStore.createProject(projectData)
    }
    showProjectModal.value = false
    selectedProject.value = null
    await projectsStore.fetchProjectsForSelectedWorkspaces()
  } catch (error) {
    console.error('Error saving project:', error)
    projectError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении проекта'
  }
}

const handleCloseProjectModal = () => {
  showProjectModal.value = false
  selectedProject.value = null
  projectError.value = ''
}

const handleArchiveProject = async (project) => {
  if (!confirm(`Архивировать проект "${project.name}"?`)) {
    return
  }

  try {
    await projectsStore.archiveProject(project.id)
    await projectsStore.fetchProjectsForSelectedWorkspaces()
  } catch (error) {
    console.error('Error archiving project:', error)
    alert('Ошибка при архивировании проекта: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(async () => {
  await projectsStore.fetchProjectsForSelectedWorkspaces()
})
</script>
