<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div class="flex items-center space-x-3 mb-4 lg:mb-0">
          <div
            v-if="project"
            class="w-4 h-4 rounded-full"
            :style="{ backgroundColor: project.color || '#3B82F6' }"
          ></div>
          <div>
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">
              {{ project?.name || 'Загрузка...' }}
            </h1>
            <p v-if="project?.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              {{ project.description }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center space-x-1">
          <button
            @click="handleEditProject"
            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Редактировать"
          >
            <PencilIcon class="w-5 h-5" />
          </button>
          <button
            v-if="project?.status !== 'archived'"
            @click="handleArchiveProject"
            class="hidden lg:block p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Архивировать"
          >
            <ArchiveBoxArrowDownIcon class="w-5 h-5" />
          </button>
          <button
            v-else
            @click="handleUnarchiveProject"
            class="hidden lg:block p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            title="Восстановить"
          >
            <ArrowUturnLeftIcon class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <!-- Project Tasks -->
      <div v-else>
        <!-- Empty State -->
        <div v-if="tasks.length === 0" class="py-12 text-center">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
              <RectangleStackIcon class="w-8 h-8 text-blue-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">В проекте пока нет задач</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
              Проект — это любая цель, требующая более одного действия.
              Разбейте его на конкретные шаги и добавьте первую задачу,
              чтобы начать двигаться к результату.
            </p>
            <button
              @click="handleCreateTask"
              class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors text-sm font-medium"
            >
              <PlusIcon class="w-5 h-5 mr-1.5" />
              Добавить задачу
            </button>
          </div>
        </div>

        <!-- Task List -->
        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              Задачи проекта
              <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                ({{ tasks.length }})
              </span>
            </h2>
          </div>
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <TaskList
              :tasks="tasks"
              @task-click="handleTaskClick"
              @toggle-complete="handleToggleComplete"
            />
          </div>
        </div>
      </div>

      <!-- Task Modal -->
      <TaskModal
        :show="showTaskModal"
        :task="selectedTask"
        :server-error="taskError"
        @close="handleCloseTaskModal"
        @submit="handleSaveTask"
      />

      <!-- Project Modal -->
      <ProjectModal
        :show="showProjectModal"
        :project="project"
        :server-error="projectError"
        @close="handleCloseProjectModal"
        @submit="handleSaveProject"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectsStore } from '@/stores/projects'
import { useTasksStore } from '@/stores/tasks'
import { useWorkspaceStore } from '@/stores/workspace'
import { RectangleStackIcon, PencilIcon, ArchiveBoxArrowDownIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'
import { PlusIcon } from '@heroicons/vue/24/solid'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import ProjectModal from '@/components/projects/ProjectModal.vue'

const route = useRoute()
const router = useRouter()
const projectsStore = useProjectsStore()
const tasksStore = useTasksStore()
const workspaceStore = useWorkspaceStore()

const project = ref(null)
const tasks = ref([])
const loading = ref(false)
const showTaskModal = ref(false)
const showProjectModal = ref(false)
const selectedTask = ref(null)
const taskError = ref('')
const projectError = ref('')

const loadProject = async () => {
  const projectId = parseInt(route.params.projectId)
  const workspaceId = parseInt(route.params.id) // Берем из URL, а не из currentWorkspace

  if (!workspaceId || !projectId) {
    console.error('Missing workspaceId or projectId', { workspaceId, projectId })
    return
  }

  loading.value = true
  try {
    const projectData = await projectsStore.fetchProject(projectId)
    project.value = projectData

    // Проверяем, что проект принадлежит workspace из URL
    if (project.value.workspace_id !== workspaceId) {
      console.error('Project does not belong to workspace from URL')
      router.push(`/workspaces/${workspaceId}/projects`)
      return
    }

    loadTasks()
  } catch (error) {
    console.error('Error loading project:', error)
    console.error('Error details:', error.response?.data)

    // Если проект не найден, перенаправляем на список проектов
    if (error.response?.status === 404) {
      router.push(`/workspaces/${workspaceId}/projects`)
    }
  } finally {
    loading.value = false
  }
}

const loadTasks = () => {
  const projectId = parseInt(route.params.projectId)

  if (!projectId) {
    tasks.value = []
    return
  }

  // Фильтруем задачи из ALL tasks по project_id (не учитываем выбранные workspace)
  tasks.value = tasksStore.allTasks.filter(t => t.project_id === projectId)
}

const handleEditProject = () => {
  showProjectModal.value = true
}

const handleArchiveProject = async () => {
  if (!confirm(`Архивировать проект "${project.value?.name}"?`)) {
    return
  }

  try {
    await projectsStore.archiveProject(project.value.id)
    // Редиректим на список проектов workspace'а, которому принадлежит проект
    router.push(`/workspaces/${project.value.workspace_id}/projects`)
  } catch (error) {
    console.error('Error archiving project:', error)
    alert('Ошибка при архивировании проекта')
  }
}

const handleUnarchiveProject = async () => {
  try {
    await projectsStore.unarchiveProject(project.value.id)
    await loadProject()
  } catch (error) {
    console.error('Error unarchiving project:', error)
    alert('Ошибка при восстановлении проекта')
  }
}

const handleCreateTask = () => {
  selectedTask.value = { project_id: project.value.id }
  showTaskModal.value = true
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskModal.value = true
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value?.id) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      // Новая задача — привязываем к проекту
      taskData.project_id = project.value.id
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
    selectedTask.value = null
    loadTasks()
  } catch (error) {
    console.error('Error saving task:', error)
    taskError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении задачи'
  }
}

const handleCloseTaskModal = () => {
  showTaskModal.value = false
  selectedTask.value = null
  taskError.value = ''
}

const handleSaveProject = async (projectData) => {
  projectError.value = ''
  try {
    await projectsStore.updateProject(project.value.id, projectData)
    showProjectModal.value = false
    await loadProject()
  } catch (error) {
    console.error('Error saving project:', error)
    projectError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении проекта'
  }
}

const handleCloseProjectModal = () => {
  showProjectModal.value = false
  projectError.value = ''
}

const handleToggleComplete = async (task) => {
  try {
    if (task.status === 'completed') {
      await tasksStore.updateTask(task.id, { status: 'inbox' })
    } else {
      await tasksStore.completeTask(task.id)
    }
    loadTasks()
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

// Загружаем проект при монтировании и смене параметров роута
watch(() => [route.params.projectId, route.params.id], () => {
  loadProject()
}, { immediate: true })

// Автоматически обновляем задачи при изменении store
watch(() => tasksStore.allTasks, () => {
  loadTasks()
}, { deep: true })
</script>

