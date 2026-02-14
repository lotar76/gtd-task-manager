<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Loading Screen -->
    <Transition name="fade">
      <div v-if="appLoading" class="fixed inset-0 z-[100] bg-white dark:bg-gray-900 flex items-center justify-center">
        <div class="text-center">
          <!-- Logo -->
          <div class="mb-6 flex justify-center">
            <img
              :src="loadingLogo"
              alt="GTD TODO"
              class="w-48 h-48 object-contain"
              @error="handleLogoError"
            />
          </div>
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">GTD TODO</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 flex justify-center">
            <span
              v-for="(letter, index) in loadingText"
              :key="index"
              :style="{ animationDelay: `${index * 0.1}s` }"
              class="loading-letter"
            >
              {{ letter }}
            </span>
          </p>
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
          <div class="hidden lg:flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <img
                :src="sidebarLogo"
                alt="GTD TODO"
                class="h-10 w-10 object-contain"
              />
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">GTD TODO</h1>
            </div>
          </div>

          <!-- Workspace Section -->
          <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Ваши пространства
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
                <button
                  class="flex-1 flex items-center px-3 py-1.5 text-sm cursor-pointer text-left"
                  @click="toggleWorkspace(ws)"
                >
                  <span
                    :class="[
                      'truncate',
                      selectedWorkspaceIds.includes(ws.id)
                        ? 'text-primary-700 dark:text-primary-400 font-medium'
                        : 'text-gray-500 dark:text-gray-400'
                    ]"
                  >
                    <span v-if="ws.emoji" class="mr-1 grayscale">{{ ws.emoji }}</span>{{ ws.name }}
                  </span>
                </button>
                
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
          <nav class="flex-1 overflow-y-auto px-4 py-3">
            <div class="space-y-1">
              <!-- Сегодня -->
              <DroppableNavLink
                to="/workspaces/:id/today"
                icon="calendar"
                :count="taskCounts.today"
                drop-status="today"
                @task-dropped="handleTaskDropped"
                @close-sidebar="sidebarOpen = false"
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
                @close-sidebar="sidebarOpen = false"
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
                @close-sidebar="sidebarOpen = false"
              >
                Завтра
              </DroppableNavLink>

              <!-- Календарь -->
              <NavLink
                to="/workspaces/:id/calendar"
                icon="calendar-days"
                :count="calendarMonthCount"
                @close-sidebar="sidebarOpen = false"
              >
                Календарь
              </NavLink>

              <!-- Когда-нибудь -->
              <DroppableNavLink
                to="/workspaces/:id/someday"
                icon="archive"
                :count="taskCounts.someday"
                drop-status="someday"
                @task-dropped="handleTaskDropped"
                @close-sidebar="sidebarOpen = false"
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
                @close-sidebar="sidebarOpen = false"
              >
                Ожидание
              </DroppableNavLink>

              <!-- Все задачи -->
              <NavLink
                to="/workspaces/:id/all"
                icon="rectangle-stack"
                :count="totalTaskCount"
                @close-sidebar="sidebarOpen = false"
              >
                Все задачи
              </NavLink>
            </div>

            <!-- Projects -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div
                @click="toggleProjectsCollapsed"
                class="flex items-center justify-between px-3 mb-2 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors -mx-1 px-4 py-1"
              >
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6v12M9 4v16M14 8v8M19 5v14" />
                  </svg>
                  Потоки
                </h3>
                <button
                  @click.stop="handleQuickAddProject"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  title="Создать поток"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              <div
                class="overflow-hidden transition-all duration-300 ease-in-out"
                :style="{ maxHeight: projectsCollapsed ? '0px' : projectsMaxHeight }"
              >
              <div class="space-y-1">
                <div
                  v-for="project in activeProjects"
                  :key="project.id"
                  :class="[
                    'flex items-center justify-between group px-3 py-1.5 rounded-lg transition-colors',
                    isProjectActive(project.id)
                      ? 'bg-primary-50 dark:bg-primary-900/30'
                      : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                  ]"
                >
                  <router-link
                    :to="`/workspaces/${project.workspace_id}/projects/${project.id}`"
                    @click="handleProjectLinkClick"
                    class="flex items-center flex-1 min-w-0"
                  >
                    <div class="flex-1 min-w-0">
                      <span
                        :class="[
                          'text-sm line-clamp-2',
                          isProjectActive(project.id)
                            ? 'text-primary-700 dark:text-primary-400 font-medium'
                            : 'text-gray-700 dark:text-gray-300'
                        ]"
                      >{{ project.name }}</span>
                      <div v-if="project.goal?.name" class="text-[10px] text-gray-400 dark:text-gray-500 truncate mt-0.5">
                        {{ project.goal.name }}
                      </div>
                      <div v-if="project.total_tasks_count > 0" class="flex items-center gap-0.5 mt-1" :title="`${project.completed_tasks_count} / ${project.total_tasks_count} выполнено`">
                        <template v-if="project.total_tasks_count <= 12">
                          <div
                            v-for="i in project.total_tasks_count"
                            :key="i"
                            class="w-2 h-2 rounded-sm"
                            :class="i <= project.completed_tasks_count ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                          ></div>
                        </template>
                        <template v-else>
                          <div
                            v-for="i in 12"
                            :key="i"
                            class="w-2 h-2 rounded-sm"
                            :class="i <= Math.round(project.completed_tasks_count / project.total_tasks_count * 12) ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                          ></div>
                          <span class="text-[10px] text-gray-400 ml-0.5">{{ project.completed_tasks_count }}/{{ project.total_tasks_count }}</span>
                        </template>
                      </div>
                    </div>
                  </router-link>
                </div>
                <div v-if="activeProjects.length === 0" class="px-3 py-1.5 text-sm text-gray-500 text-center">
                  Нет потоков
                </div>
              </div>
              </div>
            </div>

            <!-- Goals -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div
                @click="toggleGoalsCollapsed"
                class="flex items-center justify-between px-3 mb-2 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors -mx-1 px-4 py-1"
              >
                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  Цели
                </h3>
                <button
                  @click.stop="handleQuickAddGoal"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  title="Создать цель"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              <div
                class="overflow-hidden transition-all duration-300 ease-in-out"
                :style="{ maxHeight: goalsCollapsed ? '0px' : goalsMaxHeight }"
              >
                <div class="space-y-1">
                  <router-link
                    v-for="goal in activeGoals"
                    :key="goal.id"
                    :to="`/workspaces/${goal.workspace_id}/goals/${goal.id}`"
                    @click="handleGoalLinkClick"
                    :class="[
                      'flex items-center px-3 py-1.5 rounded-lg transition-colors',
                      isActiveGoal(goal.id)
                        ? 'bg-primary-50 dark:bg-primary-900/30'
                        : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                    ]"
                  >
                    <div class="flex-1 min-w-0">
                      <span
                        :class="[
                          'text-sm truncate block',
                          isActiveGoal(goal.id)
                            ? 'text-primary-700 dark:text-primary-400 font-medium'
                            : 'text-gray-700 dark:text-gray-300'
                        ]"
                      >{{ goal.name }}</span>
                      <div v-if="goal.deadline" class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ formatGoalDeadline(goal.deadline) }}
                      </div>
                    </div>
                    <span v-if="goal.progress > 0" class="text-[10px] text-primary-600 dark:text-primary-400 font-medium ml-2">
                      {{ goal.progress }}%
                    </span>
                  </router-link>
                  <div v-if="activeGoals.length === 0" class="px-3 py-1.5 text-sm text-gray-500 text-center">
                    Нет целей
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="space-y-1">
                <NavLink
                  to="/workspaces/:id/archive"
                  icon="archive-box"
                  :count="archivedCount"
                  @close-sidebar="sidebarOpen = false"
                >
                  Архив
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

      <!-- Mobile FABs -->
      <div v-if="!sidebarOpen" class="fixed bottom-5 right-5 z-30 lg:hidden flex flex-col gap-3 items-center">
        <!-- Add task -->
        <button
          @click="showTaskModal = true"
          class="w-10 h-10 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg inline-flex items-center justify-center active:scale-95 transition-transform"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
        </button>

        <!-- Calendar button (with optional day/week buttons) -->
        <div class="relative">
          <!-- Calendar view options (показываются только на странице календаря) -->
          <!-- Кнопки появляются слева от основной кнопки календаря -->
          <Transition name="slide-fade-day">
            <button
              v-if="isCalendarPage"
              @click="changeCalendarView('day')"
              class="absolute right-full mr-2 top-0 w-10 h-10 bg-purple-500 hover:bg-purple-600 text-white rounded-full shadow-lg inline-flex items-center justify-center active:scale-95 transition-all"
              title="Сегодня"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
              </svg>
            </button>
          </Transition>

          <Transition name="slide-fade-week">
            <button
              v-if="isCalendarPage"
              @click="changeCalendarView('week')"
              class="absolute right-full mr-14 top-0 w-10 h-10 bg-purple-500 hover:bg-purple-600 text-white rounded-full shadow-lg inline-flex items-center justify-center active:scale-95 transition-all"
              title="Неделя"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </button>
          </Transition>

          <!-- Calendar main button -->
          <button
            @click="handleCalendarClick"
            class="w-10 h-10 bg-purple-600 hover:bg-purple-700 text-white rounded-full shadow-lg inline-flex items-center justify-center active:scale-95 transition-transform"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </button>
        </div>

        <!-- Open sidebar -->
        <button
          @click="sidebarOpen = true"
          class="w-12 h-12 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-lg inline-flex items-center justify-center active:scale-95 transition-transform"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>
      </div>

      <!-- Main Content -->
      <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Toolbar -->
        <Toolbar
          :user="user"
          :inbox-count="taskCounts.inbox"
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

    <!-- Goal Modal -->
    <GoalModal
      :show="showGoalModal"
      :goal="selectedGoal"
      :server-error="goalError"
      @close="handleCloseGoalModal"
      @submit="handleSaveGoal"
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
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import { useThemeStore } from '@/stores/theme'
import NavLink from '@/components/common/NavLink.vue'
import DroppableNavLink from '@/components/common/DroppableNavLink.vue'
import Toolbar from '@/components/common/Toolbar.vue'
// Логотипы для заставки (старые файлы)
import loadingLogoLight from '@/assets/images/logo.jpg'
import loadingLogoDark from '@/assets/images/logo-bg.png'
// Логотипы для сайдбара (новые SVG)
import sidebarLogoLight from '@/assets/images/logo.svg'
import sidebarLogoDark from '@/assets/images/logo-dark.svg'
import WorkspaceModal from '@/components/workspace/WorkspaceModal.vue'
import AddMemberModal from '@/components/workspace/AddMemberModal.vue'
import RenameWorkspaceModal from '@/components/workspace/RenameWorkspaceModal.vue'
import MembersModal from '@/components/workspace/MembersModal.vue'
import ProjectModal from '@/components/projects/ProjectModal.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import GoalModal from '@/components/goals/GoalModal.vue'
import { useGoalsStore } from '@/stores/goals'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const workspaceStore = useWorkspaceStore()
const tasksStore = useTasksStore()
const projectsStore = useProjectsStore()
const goalsStore = useGoalsStore()
const lifeSpheresStore = useLifeSpheresStore()
const themeStore = useThemeStore()
// Логотип для заставки (старые файлы)
const loadingLogo = computed(() => themeStore.isDark ? loadingLogoDark : loadingLogoLight)
// Логотип для сайдбара (новые SVG)
const sidebarLogo = computed(() => themeStore.isDark ? sidebarLogoDark : sidebarLogoLight)
const appLoading = ref(true)
const loadingText = ref('Загрузка...'.split(''))
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
const showGoalModal = ref(false)
const selectedGoal = ref(null)
const goalError = ref('')
const projectsCollapsed = ref(localStorage.getItem('projectsCollapsed') === 'true')
const toggleProjectsCollapsed = () => {
  projectsCollapsed.value = !projectsCollapsed.value
  localStorage.setItem('projectsCollapsed', projectsCollapsed.value)
}
const goalsCollapsed = ref(localStorage.getItem('goalsCollapsed') === 'true')
const toggleGoalsCollapsed = () => {
  goalsCollapsed.value = !goalsCollapsed.value
  localStorage.setItem('goalsCollapsed', goalsCollapsed.value)
}
const selectedWorkspaceForAction = ref(null)
const showDeleteConfirm = ref(false)
const workspaceToDelete = ref(null)

const user = computed(() => authStore.user)
const workspaces = computed(() => workspaceStore.workspaces)
const taskCounts = computed(() => tasksStore.counts)
const currentWorkspace = computed(() => workspaceStore.currentWorkspace)
const activeProjects = computed(() => {
  return projectsStore.activeProjects.map(p => {
    const projectTasks = tasksStore.allTasks.filter(t => t.project_id === p.id)
    const total = projectTasks.length
    const completed = projectTasks.filter(t => t.completed_at).length
    // Берём актуальные данные workspace из workspaceStore (а не из кеша проектов)
    const ws = workspaceStore.workspaces.find(w => w.id === p.workspace_id)
    return {
      ...p,
      workspace: ws ? { id: ws.id, name: ws.name, emoji: ws.emoji } : p.workspace,
      total_tasks_count: total,
      completed_tasks_count: completed,
      tasks_count: total - completed,
    }
  })
})
const selectedWorkspaceIds = computed(() =>
  workspaceStore.selectedWorkspaces.map(ws => ws.id)
)
const projectsMaxHeight = computed(() => {
  const count = Math.max(activeProjects.value.length, 1)
  return (count * 60 + 20) + 'px'
})

const activeGoals = computed(() => goalsStore.activeGoals)
const goalsMaxHeight = computed(() => {
  const count = Math.max(activeGoals.value.length, 1)
  return (count * 50 + 20) + 'px'
})
const isActiveGoal = (goalId) => {
  return route.path.includes('/goals/' + goalId)
}

const formatGoalDeadline = (deadlineStr) => {
  if (!deadlineStr) return ''
  const deadline = new Date(deadlineStr)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  deadline.setHours(0, 0, 0, 0)
  const days = Math.ceil((deadline - today) / (1000 * 60 * 60 * 24))
  if (days < 0) return `просрочено на ${Math.abs(days)} дн.`
  if (days === 0) return 'сегодня'
  if (days === 1) return 'завтра'
  if (days <= 30) return `осталось ${days} дн.`
  const date = new Date(deadlineStr)
  return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const handleGoalLinkClick = () => {
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
  }
}

// Счетчик задач в текущем месяце для календаря
// Используем scheduled задачи (задачи с датой, не today/tomorrow)
// + today и tomorrow задачи (они тоже отображаются в календаре)
const totalTaskCount = computed(() => tasksStore.filteredTasks.length)

// Счетчик архивных задач
const archivedCount = computed(() => tasksStore.archivedTasks.length)

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

const isCalendarPage = computed(() => {
  return route.path.includes('/calendar')
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

const changeCalendarView = (view) => {
  router.push({ query: { view } })
}

const handleCalendarClick = () => {
  if (isCalendarPage.value) {
    // Если уже на странице календаря - переключаем вид на месяц
    changeCalendarView('month')
  } else {
    // Если не на странице календаря - переходим на календарь
    router.push(`/workspaces/${currentWorkspace.value?.id || 1}/calendar`)
  }
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
    await projectsStore.fetchAllProjects({ force: true })
  } catch (error) {
    console.error('Error saving project:', error)
    projectError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении потока'
  }
}

const handleCloseProjectModal = () => {
  showProjectModal.value = false
  selectedProject.value = null
  projectError.value = ''
}

const handleArchiveProject = async (project) => {
  if (!confirm(`Архивировать поток "${project.name}"?`)) {
    return
  }
  
  try {
    await projectsStore.archiveProject(project.id)
    await projectsStore.fetchAllProjects({ force: true })
  } catch (error) {
    console.error('Error archiving project:', error)
    alert('Ошибка при архивировании потока: ' + (error.response?.data?.message || error.message))
  }
}

const handleQuickAddGoal = () => {
  selectedGoal.value = null
  showGoalModal.value = true
}

const handleSaveGoal = async (goalData) => {
  goalError.value = ''
  try {
    if (selectedGoal.value) {
      await goalsStore.updateGoal(selectedGoal.value.id, goalData)
    } else {
      await goalsStore.createGoal(goalData)
    }
    showGoalModal.value = false
    selectedGoal.value = null
    await goalsStore.fetchAllGoals({ force: true })
  } catch (error) {
    console.error('Error saving goal:', error)
    goalError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении цели'
  }
}

const handleCloseGoalModal = () => {
  showGoalModal.value = false
  selectedGoal.value = null
  goalError.value = ''
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

const handleProjectLinkClick = () => {
  // Закрываем сайдбар только на мобилке (lg breakpoint = 1024px)
  if (window.innerWidth < 1024) {
    sidebarOpen.value = false
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

const handleLogoError = (event) => {
  // Скрываем логотип если он не загружен
  event.target.style.display = 'none'
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
      projectsStore.fetchAllProjects(),
      goalsStore.fetchAllGoals(),
      lifeSpheresStore.fetchAll(),
    ])
  } finally {
    appLoading.value = false
  }
  tasksStore.startSync()
  projectsStore.startSync()

  // Закрываем меню воркспейсов при клике вне его
  document.addEventListener('click', handleClickOutsideWorkspaceMenu)
  document.addEventListener('keydown', handleKeydown)
})

// ПРИМЕЧАНИЕ: watch для перезагрузки проектов при смене workspace больше не нужен,
// т.к. теперь используется локальная фильтрация через computed filteredProjects

onUnmounted(() => {
  tasksStore.stopSync()
  projectsStore.stopSync()
  document.removeEventListener('click', handleClickOutsideWorkspaceMenu)
  document.removeEventListener('keydown', handleKeydown)
})

const handleClickOutsideWorkspaceMenu = () => {
  openWorkspaceMenuId.value = null
}

const getWorkspaceName = (workspaceId) => {
  const workspace = workspaces.value.find(ws => ws.id === workspaceId)
  return workspace?.name || ''
}

const isProjectActive = (projectId) => {
  return route.path.includes(`/projects/${projectId}`)
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

.loading-letter {
  display: inline-block;
  animation: letterFade 2s ease-in-out infinite;
}

@keyframes letterFade {
  0%, 100% {
    opacity: 0.3;
  }
  50% {
    opacity: 1;
  }
}

/* Calendar Speed Dial animations */
.slide-fade-day-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  transition-delay: 0.2s;
}

.slide-fade-day-leave-active {
  transition: all 0.2s ease-in;
  transition-delay: 0.05s;
}

.slide-fade-day-enter-from {
  transform: translateX(60px);
  opacity: 0;
}

.slide-fade-day-leave-to {
  transform: translateX(60px);
  opacity: 0;
}

.slide-fade-week-enter-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  transition-delay: 0.1s;
}

.slide-fade-week-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-week-enter-from {
  transform: translateX(60px);
  opacity: 0;
}

.slide-fade-week-leave-to {
  transform: translateX(60px);
  opacity: 0;
}
</style>

