<template>
  <div class="min-h-screen bg-gray-50">
    <div class="flex h-screen">
      <!-- Sidebar -->
      <aside
        :class="[
          'fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
      >
        <div class="flex flex-col h-full">
          <!-- Logo (только десктоп) -->
          <div class="hidden lg:flex items-center justify-between p-6 border-b border-gray-200">
            <h1 class="text-xl font-bold text-gray-900">GTD TODO</h1>
          </div>

          <!-- Workspace Section -->
          <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Рабочие пространства
              </h3>
              <button
                @click="showWorkspaceModal = true"
                class="text-gray-400 hover:text-gray-600 transition-colors"
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
                  'flex items-center rounded-lg transition-colors',
                  selectedWorkspaceIds.includes(ws.id)
                    ? 'bg-primary-50'
                    : 'hover:bg-gray-50'
                ]"
              >
                <!-- Checkbox для выбора workspace -->
                <label class="flex-1 flex items-center px-3 py-2 text-sm cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="selectedWorkspaceIds.includes(ws.id)"
                    @change="toggleWorkspace(ws)"
                    class="mr-3 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <span 
                    :class="[
                      'truncate',
                      selectedWorkspaceIds.includes(ws.id)
                        ? 'text-primary-700 font-medium'
                        : 'text-gray-700'
                    ]"
                  >
                    {{ ws.name }}
                  </span>
                </label>
                
                <!-- Three dots menu -->
                <div class="relative" @click.stop>
                  <button
                    @click="toggleWorkspaceMenu(ws.id)"
                    class="p-2 text-gray-400 hover:text-gray-600 rounded transition-colors"
                  >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                  
                  <!-- Dropdown menu -->
                  <Transition name="dropdown">
                    <div
                      v-if="openWorkspaceMenuId === ws.id"
                      class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                    >
                      <button
                        @click="handleViewMembers(ws)"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
            >
                        Участники
                      </button>
                      <button
                        v-if="canManageWorkspace(ws)"
                        @click="handleRenameWorkspace(ws)"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                      >
                        Переименовать
                      </button>
                      <button
                        v-if="canManageWorkspace(ws)"
                        @click="handleAddMember(ws)"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                      >
                        Добавить пользователя
                      </button>
                      <button
                        v-if="!canManageWorkspace(ws)"
                        @click="openWorkspaceMenuId = null"
                        class="w-full px-4 py-2 text-left text-sm text-gray-500 italic"
                        disabled
                      >
                        Нет доступных действий
                      </button>
                    </div>
                  </Transition>
                </div>
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

            <div class="mt-6 pt-6 border-t border-gray-200 space-y-1">
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
            </div>

            <!-- Projects -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <div class="flex items-center justify-between px-3 mb-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Проекты
                </h3>
                <button
                  @click="handleQuickAddProject"
                  class="text-gray-400 hover:text-gray-600"
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
                  class="flex items-center justify-between group px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                >
                  <router-link
                    :to="`/workspaces/${currentWorkspace?.id}/projects/${project.id}`"
                    class="flex items-center space-x-2 flex-1 min-w-0"
                  >
                    <div
                      class="w-3 h-3 rounded-full flex-shrink-0"
                      :style="{ backgroundColor: project.color || '#3B82F6' }"
                    ></div>
                    <span class="text-sm text-gray-700 truncate">{{ project.name }}</span>
                    <span
                      v-if="project.tasks_count > 0"
                      class="text-xs text-gray-500"
                    >
                      ({{ project.tasks_count }})
                    </span>
                  </router-link>
                  <button
                    @click.stop="handleArchiveProject(project)"
                    class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 p-1 transition-opacity"
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

            <div class="mt-6 pt-6 border-t border-gray-200">
              <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                Организация
              </h3>
              <div class="space-y-1">
                <NavLink to="/workspaces/:id/goals" icon="target">
                  Цели
                </NavLink>
              </div>
            </div>
          </nav>

          <!-- User Menu (десктоп) -->
          <div class="hidden lg:block p-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                  {{ userInitials }}
                </div>
                <div class="text-sm">
                  <div class="font-medium text-gray-900">{{ user?.name }}</div>
                  <div class="text-xs text-gray-500">{{ user?.email }}</div>
                </div>
              </div>
              <button @click="handleLogout" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Overlay (mobile) -->
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
      />

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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import { useTaskEvents } from '@/composables/useTaskEvents'
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
const { triggerTaskUpdate } = useTaskEvents()

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

const user = computed(() => {
  console.log('User from store:', authStore.user)
  return authStore.user
})
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

const toggleWorkspace = async (workspace) => {
  workspaceStore.toggleSelectedWorkspace(workspace)
  await tasksStore.fetchCounts()
  
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
    await tasksStore.fetchCounts()
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
    console.log('Dropping task:', taskId, 'to status:', newStatus)
    
    // Для "today" устанавливаем статус и дату на сегодня
    if (newStatus === 'today') {
      const today = new Date().toISOString().split('T')[0]
      await tasksStore.updateTask(taskId, { status: 'today', due_date: today })
    }
    // Для "tomorrow" устанавливаем статус и дату на завтра
    else if (newStatus === 'tomorrow') {
      const tomorrow = new Date()
      tomorrow.setDate(tomorrow.getDate() + 1)
      const tomorrowStr = tomorrow.toISOString().split('T')[0]
      await tasksStore.updateTask(taskId, { status: 'tomorrow', due_date: tomorrowStr })
    } else {
      // Обновляем статус задачи для остальных папок
      await tasksStore.updateTask(taskId, { status: newStatus })
    }
    
    // Обновляем счетчики
    await tasksStore.fetchCounts()
    
    // Триггерим событие обновления для перезагрузки списков
    triggerTaskUpdate()
  } catch (error) {
    console.error('Error updating task status:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error status:', error.response?.status)
    
    // Показываем пользователю понятное сообщение
    const errorMessage = error.response?.data?.message || 'Ошибка при обновлении задачи'
    alert(errorMessage)
  }
}

const handleSearch = (query) => {
  console.log('Search:', query)
  // TODO: Реализовать поиск задач
}

const handleProfile = () => {
  console.log('Profile clicked')
  // TODO: Перейти на страницу профиля
}

const handleSettings = () => {
  console.log('Settings clicked')
  // TODO: Перейти на страницу настроек
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

onMounted(async () => {
  await workspaceStore.fetchWorkspaces()
  
  if (workspaceStore.currentWorkspace) {
    await tasksStore.fetchCounts()
    await projectsStore.fetchProjects()
  }

  // Закрываем меню воркспейсов при клике вне его
  document.addEventListener('click', handleClickOutsideWorkspaceMenu)
})

// Загружаем проекты при смене workspace
watch(() => workspaceStore.currentWorkspace?.id, (newWorkspaceId) => {
  if (newWorkspaceId) {
    projectsStore.fetchProjects()
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutsideWorkspaceMenu)
})

const handleClickOutsideWorkspaceMenu = () => {
  openWorkspaceMenuId.value = null
}

watch(() => workspaceStore.selectedWorkspaces, async () => {
  await tasksStore.fetchCounts()
  
  // Перезагружаем текущий список задач если мы на странице задач
  const currentPath = router.currentRoute.value.path
  if (currentPath.includes('/inbox')) {
    await tasksStore.fetchInbox()
  } else if (currentPath.includes('/next-actions')) {
    await tasksStore.fetchNextActions()
  } else if (currentPath.includes('/waiting')) {
    await tasksStore.fetchWaiting()
  } else if (currentPath.includes('/someday')) {
    await tasksStore.fetchSomeday()
  } else if (currentPath.includes('/today')) {
    await tasksStore.fetchToday()
  } else if (currentPath.includes('/tomorrow')) {
    await tasksStore.fetchTomorrow()
  }
}, { deep: true })
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
</style>

