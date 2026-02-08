<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Loading Screen -->
    <Transition name="fade">
      <div v-if="appLoading" class="fixed inset-0 z-[100] bg-white dark:bg-gray-900 flex items-center justify-center">
        <div class="text-center">
          <div class="relative w-16 h-16 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full border-4 border-gray-200 dark:border-gray-700"></div>
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-primary-600 animate-spin"></div>
          </div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">GTD TODO</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Загрузка...</p>
        </div>
      </div>
    </Transition>

    <div class="flex h-screen">
      <!-- Sidebar -->
      <aside
        :class="[
          'fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
      >
        <div class="flex flex-col h-full">
          <!-- Logo (только десктоп) -->
          <div class="hidden lg:flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">GTD TODO</h1>
          </div>

          <!-- Workspace Section -->
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Рабочие пространства
              </h3>
              <button
                @click="showWorkspaceModal = true"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                title="Создать workspace"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
            
            <!-- Workspace List -->
            <div class="space-y-1">
              <div
                v-for="ws in workspaces"
                :key="ws.id"
                :class="[
                  'group flex items-center rounded-lg transition-colors overflow-hidden',
                  selectedWorkspaceIds.includes(ws.id)
                    ? 'bg-primary-50 dark:bg-primary-900/30'
                    : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                <!-- Checkbox для выбора workspace -->
                <label class="flex-1 flex items-center px-3 py-2 text-sm cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="selectedWorkspaceIds.includes(ws.id)"
                    @change="toggleWorkspace(ws)"
                    class="mr-3 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700"
                  />
                  <span 
                    :class="[
                      'truncate',
                      selectedWorkspaceIds.includes(ws.id)
                        ? 'text-primary-700 dark:text-primary-400 font-medium'
                        : 'text-gray-700 dark:text-gray-300'
                    ]"
                  >
                    {{ ws.name }}
                  </span>
                </label>
                
                <!-- Settings gear -->
                <router-link
                  :to="`/workspaces/${ws.id}/settings`"
                  class="flex-shrink-0 p-1.5 mr-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded transition-colors opacity-0 group-hover:opacity-100"
                  title="Настройки"
                  @click.stop
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                  </svg>
                </router-link>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 overflow-y-auto p-4">
            <div class="space-y-1">
              <!-- Входящие - отдельно -->
              <DroppableNavLink 
                to="/workspaces/:id/inbox" 
                icon="inbox" 
                :count="taskCounts.inbox"
                drop-status="inbox"
                @task-dropped="handleTaskDropped"
              >
                Входящие
              </DroppableNavLink>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 space-y-1">
              <!-- Сегодня -->
              <DroppableNavLink 
                to="/workspaces/:id/today" 
                icon="calendar" 
                :count="taskCounts.today"
                drop-status="today"
                @task-dropped="handleTaskDropped"
              >
                Сегодня
              </DroppableNavLink>
              
              <!-- Следующие -->
              <DroppableNavLink 
                to="/workspaces/:id/next-actions" 
                icon="lightning" 
                :count="taskCounts.next_actions"
                drop-status="next_action"
                @task-dropped="handleTaskDropped"
              >
                Следующие
              </DroppableNavLink>
              
              <!-- Завтра -->
              <DroppableNavLink 
                to="/workspaces/:id/tomorrow" 
                icon="calendar-days" 
                :count="taskCounts.tomorrow"
                drop-status="tomorrow"
                @task-dropped="handleTaskDropped"
              >
                Завтра
              </DroppableNavLink>
              
              <!-- Календарь -->
              <NavLink to="/workspaces/:id/calendar" icon="calendar-days" :count="calendarMonthCount">
                Календарь
              </NavLink>
              
              <!-- Когда-нибудь -->
              <DroppableNavLink 
                to="/workspaces/:id/someday" 
                icon="archive" 
                :count="taskCounts.someday"
                drop-status="someday"
                @task-dropped="handleTaskDropped"
              >
                Когда-нибудь
              </DroppableNavLink>
              
              <!-- Ожидание -->
              <DroppableNavLink
                to="/workspaces/:id/waiting"
                icon="clock"
                :count="taskCounts.waiting"
                drop-status="waiting"
                @task-dropped="handleTaskDropped"
              >
                Ожидание
              </DroppableNavLink>

              <!-- Все задачи -->
              <NavLink to="/workspaces/:id/all" icon="rectangle-stack" :count="totalTaskCount">
                Все задачи
              </NavLink>
            </div>

            <!-- Projects -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between px-3 mb-3">
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Проекты
                </h3>
                <button
                  @click="handleQuickAddProject"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  title="Создать проект"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              <div class="space-y-1 max-h-64 overflow-y-auto">
                <div
                  v-for="project in activeProjects"
                  :key="project.id"
                  class="flex items-center justify-between group px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <router-link
                    :to="`/workspaces/${currentWorkspace?.id}/projects/${project.id}`"
                    class="flex items-center space-x-2 flex-1 min-w-0"
                  >
                    <div
                      class="w-3 h-3 rounded-full flex-shrink-0"
                      :style="{ backgroundColor: project.color || '#3B82F6' }"
                    ></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ project.name }}</span>
                    <span
                      v-if="project.tasks_count > 0"
                      class="text-xs text-gray-500"
                    >
                      ({{ project.tasks_count }})
                    </span>
                  </router-link>
                  <button
                    @click.stop="handleArchiveProject(project)"
                    class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 transition-opacity"
                    title="Архивировать"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                  </button>
                </div>
                <div v-if="activeProjects.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">
                  Нет проектов
                </div>
              </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
              <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                Организация
              </h3>
              <div class="space-y-1">
                <NavLink to="/workspaces/:id/goals" icon="target">
                  Цели
                </NavLink>
              </div>
            </div>
          </nav>

        </div>
      </aside>

      <!-- Overlay (mobile) -->
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
      />

      <!-- Mobile FAB: open sidebar -->
      <button
        v-if="!sidebarOpen"
        @click="sidebarOpen = true"
        class="fixed bottom-5 right-5 z-30 lg:hidden w-12 h-12 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-lg flex items-center justify-center active:scale-95 transition-transform"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
      </button>

      <!-- Main Content -->
      <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Toolbar -->
        <Toolbar
          :user="user"
          @quick-add-task="handleQuickAddTask"
          @quick-add-project="handleQuickAddProject"
          @quick-add-goal="handleQuickAddGoal"
          @search="handleSearch"
          @logout="handleLogout"
          @profile="handleProfile"
          @settings="handleSettings"
          @toggle-sidebar="sidebarOpen = !sidebarOpen"
        />
        
        <!-- Content -->
        <div class="flex-1 overflow-auto">
          <router-view />
        </div>
      </main>
    </div>

    <!-- Workspace Modal -->
    <WorkspaceModal
      :show="showWorkspaceModal"
      @close="showWorkspaceModal = false"
      @submit="handleCreateWorkspace"
    />

    <!-- Task Modal -->
    <TaskModal
      :show="showTaskModal"
      :server-error="taskError"
      @close="handleCloseTaskModal"
      @submit="handleCreateTask"
    />

    <!-- Add Member Modal -->
    <AddMemberModal
      :show="showAddMemberModal"
      :workspace="selectedWorkspaceForAction"
      @close="handleCloseAddMemberModal"
      @submit="handleSubmitAddMember"
    />

    <!-- Rename Workspace Modal -->
    <RenameWorkspaceModal
      :show="showRenameWorkspaceModal"
      :workspace="selectedWorkspaceForAction"
      @close="handleCloseRenameWorkspaceModal"
      @submit="handleSubmitRenameWorkspace"
    />

    <!-- Members Modal -->
    <MembersModal
      :show="showMembersModal"
      :workspace="selectedWorkspaceForAction"
      @close="handleCloseMembersModal"
      @member-removed="handleMemberRemoved"
    />

    <!-- Project Modal -->
    <ProjectModal
      :show="showProjectModal"
      :project="selectedProject"
      :server-error="projectError"
      @close="handleCloseProjectModal"
      @submit="handleSaveProject"
    />

    <!-- Delete Workspace Confirm -->
    <Transition name="modal">
      <div
        v-if="showDeleteConfirm"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
      >
        <div class="fixed inset-0 bg-black/50" @click="cancelDeleteWorkspace" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Удалить пространство?</h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Пространство «{{ workspaceToDelete?.name }}» будет удалено навсегда. Это действие нельзя отменить.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="cancelDeleteWorkspace"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
            >
              Отмена
            </button>
            <button
              @click="confirmDeleteWorkspace"
              class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors"
            >
              Удалить
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import NavLink from '@/components/common/NavLink.vue'
import DroppableNavLink from '@/components/common/DroppableNavLink.vue'
import Toolbar from '@/components/common/Toolbar.vue'
import WorkspaceModal from '@/components/workspace/WorkspaceModal.vue'
import AddMemberModal from '@/components/workspace/AddMemberModal.vue'
import RenameWorkspaceModal from '@/components/workspace/RenameWorkspaceModal.vue'
import MembersModal from '@/components/workspace/MembersModal.vue'
import ProjectModal from '@/components/projects/ProjectModal.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'

const router = useRouter()
const authStore = useAuthStore()
const workspaceStore = useWorkspaceStore()
const tasksStore = useTasksStore()
const projectsStore = useProjectsStore()
const appLoading = ref(true)
const sidebarOpen = ref(false)
const showUserMenu = ref(false)
const showWorkspaceModal = ref(false)
const showTaskModal = ref(false)
const taskError = ref('')
const openWorkspaceMenuId = ref(null)
const showAddMemberModal = ref(false)
const showRenameWorkspaceModal = ref(false)
const showMembersModal = ref(false)
const showProjectModal = ref(false)
const selectedProject = ref(null)
const projectError = ref('')
const selectedWorkspaceForAction = ref(null)
const showDeleteConfirm = ref(false)
const workspaceToDelete = ref(null)

const user = computed(() => authStore.user)
const workspaces = computed(() => workspaceStore.workspaces)
const taskCounts = computed(() => tasksStore.counts)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)
const activeProjects = computed(() => projectsStore.activeProjects)
const selectedWorkspaceIds = computed(() => 
  workspaceStore.selectedWorkspaces.map(ws => ws.id)
)

// Счетчик задач в текущем месяце для календаря
// Используем scheduled задачи (задачи с датой, не today/tomorrow)
// + today и tomorrow задачи (они тоже отображаются в календаре)
const totalTaskCount = computed(() => tasksStore.filteredTasks.length)

const calendarMonthCount = computed(() => {
  if (selectedWorkspaceIds.value.length === 0) return 0
  
  const scheduledCount = taskCounts.value.scheduled || 0
  const todayCount = taskCounts.value.today || 0
  const tomorrowCount = taskCounts.value.tomorrow || 0
  
  // Суммируем все задачи, которые могут быть в календаре
  return scheduledCount + todayCount + tomorrowCount
})

const userInitials = computed(() => {
  const name = user.value?.name || ''
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const toggleWorkspace = (workspace) => {
  workspaceStore.toggleSelectedWorkspace(workspace)

  // Обновляем роутинг
  const currentPath = router.currentRoute.value.path
  const pathSegments = currentPath.split('/')
  const currentFolder = pathSegments[pathSegments.length - 1]
  const validFolders = ['inbox', 'next-actions', 'today', 'waiting', 'someday', 'calendar', 'projects', 'goals']
  const targetFolder = validFolders.includes(currentFolder) ? currentFolder : 'inbox'
  
  const activeWorkspaceId = workspaceStore.currentWorkspace?.id || workspace.id
  router.push(`/workspaces/${activeWorkspaceId}/${targetFolder}`)
}

const handleCreateWorkspace = async (formData) => {
  try {
    const newWorkspace = await workspaceStore.createWorkspace(formData)
    showWorkspaceModal.value = false
    // При создании нового workspace используем toggleWorkspace,
    // которая сохранит текущую папку
    await toggleWorkspace(newWorkspace)
  } catch (error) {
    console.error('Ошибка создания workspace:', error)
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const handleQuickAddTask = () => {
  showTaskModal.value = true
}

const handleQuickAddProject = () => {
  selectedProject.value = null
  showProjectModal.value = true
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
    await projectsStore.fetchProjects()
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
    await projectsStore.fetchProjects()
  } catch (error) {
    console.error('Error archiving project:', error)
    alert('Ошибка при архивировании проекта: ' + (error.response?.data?.message || error.message))
  }
}

const handleQuickAddGoal = () => {
  console.log('Quick add goal')
  // TODO: Реализовать создание цели
}

const handleCreateTask = async (taskData) => {
  taskError.value = ''
  try {
    await tasksStore.createTask(taskData)
    showTaskModal.value = false
  } catch (error) {
    console.error('Error creating task:', error)
    taskError.value = error.response?.data?.message || error.message || 'Ошибка при создании задачи'
  }
}

const handleCloseTaskModal = () => {
  showTaskModal.value = false
  taskError.value = ''
}

const handleTaskDropped = async ({ taskId, newStatus }) => {
  try {
    const updateData = { status: newStatus }
    if (newStatus === 'today') {
      updateData.due_date = new Date().toISOString().split('T')[0]
    } else if (newStatus === 'tomorrow') {
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      updateData.due_date = tomorrow.toISOString().split('T')[0]
    }
    await tasksStore.updateTask(taskId, updateData)
  } catch (error) {
    console.error('Error updating task status:', error)
    alert(error.response?.data?.message || 'Ошибка при обновлении задачи')
  }
}

const handleSearch = (query) => {
  console.log('Search:', query)
  // TODO: Реализовать поиск задач
}

const handleProfile = () => {
  router.push('/settings')
}

const handleSettings = () => {
  router.push('/settings')
}

const canManageWorkspace = (workspace) => {
  // Проверяем является ли пользователь owner или admin
  const userId = user.value?.id
  if (!userId) return false
  
  // Если пользователь - владелец
  if (workspace.owner_id === userId) return true
  
  // Проверяем роль в members
  const member = workspace.members?.find(m => m.id === userId)
  return member?.pivot?.role === 'admin'
}

const toggleWorkspaceMenu = (workspaceId) => {
  openWorkspaceMenuId.value = openWorkspaceMenuId.value === workspaceId ? null : workspaceId
}

const handleRenameWorkspace = (workspace) => {
  selectedWorkspaceForAction.value = workspace
  showRenameWorkspaceModal.value = true
  openWorkspaceMenuId.value = null
}

const handleViewMembers = (workspace) => {
  selectedWorkspaceForAction.value = workspace
  showMembersModal.value = true
  openWorkspaceMenuId.value = null
}

const handleAddMember = (workspace) => {
  selectedWorkspaceForAction.value = workspace
  showAddMemberModal.value = true
  openWorkspaceMenuId.value = null
}

const getWorkspaceTaskCount = (workspaceId) => {
  return tasksStore.allTasks.filter(t => t.workspace_id === workspaceId).length
}

const handleDeleteWorkspace = (workspace) => {
  workspaceToDelete.value = workspace
  showDeleteConfirm.value = true
  openWorkspaceMenuId.value = null
}

const confirmDeleteWorkspace = async () => {
  if (!workspaceToDelete.value) return
  try {
    await workspaceStore.deleteWorkspace(workspaceToDelete.value.id)
  } catch (error) {
    console.error('Error deleting workspace:', error)
  }
  showDeleteConfirm.value = false
  workspaceToDelete.value = null
}

const cancelDeleteWorkspace = () => {
  showDeleteConfirm.value = false
  workspaceToDelete.value = null
}

const handleCloseAddMemberModal = () => {
  showAddMemberModal.value = false
  selectedWorkspaceForAction.value = null
}

const handleCloseMembersModal = () => {
  showMembersModal.value = false
  selectedWorkspaceForAction.value = null
}

const handleMemberRemoved = async () => {
  // Обновляем список workspaces после удаления участника
  await workspaceStore.fetchWorkspaces()
}

const handleCloseRenameWorkspaceModal = () => {
  showRenameWorkspaceModal.value = false
  selectedWorkspaceForAction.value = null
}

const handleSubmitAddMember = async (memberData) => {
  try {
    console.log('Sending member data:', memberData)
    await workspaceStore.addMember(memberData.workspace_id, {
      email: memberData.email,
      role: memberData.role,
    })
    showAddMemberModal.value = false
    selectedWorkspaceForAction.value = null
    alert('Пользователь успешно добавлен в workspace')
  } catch (error) {
    console.error('Error adding member:', error)
    console.error('Error response:', error.response?.data)
    
    let errorMessage = 'Ошибка при добавлении пользователя'
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    
    alert(errorMessage)
  }
}

const handleSubmitRenameWorkspace = async (newName) => {
  try {
    console.log('Renaming workspace:', selectedWorkspaceForAction.value.id, 'to:', newName)
    await workspaceStore.updateWorkspace(selectedWorkspaceForAction.value.id, { name: newName })
    await workspaceStore.fetchWorkspaces()
    showRenameWorkspaceModal.value = false
    selectedWorkspaceForAction.value = null
    alert('Workspace успешно переименован')
  } catch (error) {
    console.error('Error renaming workspace:', error)
    console.error('Error response:', error.response?.data)
    
    let errorMessage = 'Ошибка при переименовании'
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    
    alert(errorMessage)
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    openWorkspaceMenuId.value = null
  }
}

onMounted(async () => {
  try {
    await workspaceStore.fetchWorkspaces()
    await Promise.all([
      tasksStore.fetchAllTasks(),
      projectsStore.fetchProjects(),
    ])
  } finally {
    appLoading.value = false
  }
  tasksStore.startSync()

  // Закрываем меню воркспейсов при клике вне его
  document.addEventListener('click', handleClickOutsideWorkspaceMenu)
  document.addEventListener('keydown', handleKeydown)
})

// Загружаем проекты при смене workspace
watch(() => workspaceStore.currentWorkspace?.id, (newWorkspaceId) => {
  if (newWorkspaceId) {
    projectsStore.fetchProjects()
  }
})

onUnmounted(() => {
  tasksStore.stopSync()
  document.removeEventListener('click', handleClickOutsideWorkspaceMenu)
  document.removeEventListener('keydown', handleKeydown)
})

const handleClickOutsideWorkspaceMenu = () => {
  openWorkspaceMenuId.value = null
}

</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.fade-leave-active {
  transition: opacity 0.4s ease;
}

.fade-leave-to {
  opacity: 0;
}
</style>

