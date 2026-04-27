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
              alt="Крепкая Башня"
              class="w-72 h-72 object-contain"
              @error="handleLogoError"
            />
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 flex justify-center mt-4">
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
                alt="Крепкая Башня"
                class="h-10 w-10 object-contain"
              />
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">Крепкая Башня</h1>
            </div>
          </div>

          <!-- Navigation -->
          <nav class="flex-1 overflow-y-auto px-4 py-3">
            <!-- Основа -->
            <div class="flex items-center justify-between px-3 mb-2 select-none -mx-1 px-4 py-1">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
                Основа
              </h3>
            </div>
            <div class="space-y-1 mb-4">
              <NavLink
                to="/principles"
                icon="list-bullet"
                @close-sidebar="sidebarOpen = false"
              >
                Принципы
              </NavLink>
              <NavLink
                to="/spheres"
                icon="globe-alt"
                @close-sidebar="sidebarOpen = false"
              >
                Сферы жизни
              </NavLink>
              <NavLink
                to="/goals"
                icon="target"
                @close-sidebar="sidebarOpen = false"
              >
                Цели
              </NavLink>
              <NavLink
                to="/challenge"
                icon="refresh"
                @close-sidebar="sidebarOpen = false"
              >
                Привычки
              </NavLink>
              <NavLink
                to="/projects"
                icon="streams"
                @close-sidebar="sidebarOpen = false"
              >
                Потоки
              </NavLink>
            </div>

            <!-- Входящие -->
            <div class="mb-4 border-t border-gray-200 dark:border-gray-700 pt-4">
              <DroppableNavLink
                to="/inbox"
                icon="inbox"
                :count="taskCounts.inbox"
                drop-status="inbox"
                @task-dropped="handleTaskDropped"
                @close-sidebar="sidebarOpen = false"
              >
                Входящие
              </DroppableNavLink>
            </div>

            <!-- Оперативный фокус -->
            <div class="flex items-center justify-between px-3 mb-2 select-none -mx-1 px-4 py-1 border-t border-gray-200 dark:border-gray-700 pt-4">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Оперативный фокус
              </h3>
            </div>
            <div class="space-y-1">
              <!-- Сегодня -->
              <DroppableNavLink
                to="/today"
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
                to="/next-actions"
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
                to="/tomorrow"
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
                to="/calendar"
                icon="calendar-days"
                :count="calendarMonthCount"
                @close-sidebar="sidebarOpen = false"
              >
                Календарь
              </NavLink>

              <!-- Когда-нибудь -->
              <DroppableNavLink
                to="/someday"
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
                to="/waiting"
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
                to="/all"
                icon="rectangle-stack"
                :count="totalTaskCount"
                @close-sidebar="sidebarOpen = false"
              >
                Все задачи
              </NavLink>
            </div>


            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="space-y-1">
                <NavLink
                  to="/contacts"
                  icon="smile-plus"
                  @close-sidebar="sidebarOpen = false"
                >
                  Контакты
                </NavLink>
                <NavLink
                  to="/notifications"
                  icon="bell"
                  @close-sidebar="sidebarOpen = false"
                >
                  Уведомления
                </NavLink>
                <NavLink
                  to="/documents"
                  icon="document-text"
                  @close-sidebar="sidebarOpen = false"
                >
                  Документы
                </NavLink>
                <NavLink
                  to="/archive"
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

      <!-- Mobile Bottom Bar -->
      <div v-if="!sidebarOpen" class="fixed bottom-0 left-0 right-0 z-30 lg:hidden bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 safe-area-bottom">
        <div class="flex items-center justify-around h-14">
          <!-- Menu -->
          <button
            @click="sidebarOpen = true"
            class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 dark:text-gray-400 active:bg-gray-100 dark:active:bg-gray-700 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <span class="text-[10px] mt-0.5">Меню</span>
          </button>

          <!-- Потоки -->
          <button
            @click="$router.push('/projects')"
            class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 dark:text-gray-400 active:bg-gray-100 dark:active:bg-gray-700 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6v12M9 4v16M14 8v8M19 5v14" />
            </svg>
            <span class="text-[10px] mt-0.5">Потоки</span>
          </button>

          <!-- Add task (center, accent circle) -->
          <div class="flex items-center justify-center flex-1 h-full relative">
            <button
              @click="showAddMenu = !showAddMenu"
              class="flex items-center justify-center w-10 h-10 bg-green-500 text-white rounded-full active:scale-95 transition-transform"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </button>
            <!-- Add menu popup -->
            <Teleport to="body">
              <div v-if="showAddMenu" class="fixed inset-0 z-40" @click="showAddMenu = false" />
              <Transition name="fade">
                <div v-if="showAddMenu" class="fixed bottom-16 left-1/2 -translate-x-1/2 z-50 bg-white dark:bg-gray-700 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 py-2 w-48">
                  <button
                    @click="showAddMenu = false; handleQuickAddTask()"
                    class="w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2.5"
                  >
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Задача
                  </button>
                  <button
                    @click="showAddMenu = false; showQuickAiInput = true"
                    class="w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center gap-2.5"
                  >
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                    </svg>
                    AI задача
                  </button>
                </div>
              </Transition>
            </Teleport>
          </div>

          <!-- Habits -->
          <button
            @click="$router.push('/challenge')"
            class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 dark:text-gray-400 active:bg-gray-100 dark:active:bg-gray-700 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
            </svg>
            <span class="text-[10px] mt-0.5">Привычки</span>
          </button>

          <!-- Calendar -->
          <button
            @click="handleCalendarClick"
            class="flex flex-col items-center justify-center flex-1 h-full text-gray-500 dark:text-gray-400 active:bg-gray-100 dark:active:bg-gray-700 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-[10px] mt-0.5">Календарь</span>
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <main class="flex-1 flex flex-col overflow-hidden pb-14 lg:pb-0">
        <!-- Toolbar -->
        <Toolbar
          :user="user"
          :inbox-count="taskCounts.inbox"
          @quick-add-task="handleQuickAddTask"
          @quick-ai-task="showQuickAiInput = true"
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

    <!-- Черновик задачи → открывается в полноценном представлении -->
    <TaskView
      :show="showDraftTask"
      :task="draftTask"
      @close="handleCloseDraft"
      @saved="onDraftSaved"
    />

    <!-- Quick AI Task Input -->
    <Teleport to="body">
      <div v-if="showQuickAiInput" class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] p-4">
        <div class="absolute inset-0 bg-black/50" @click="closeQuickAi" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
          <button @click="closeQuickAi" class="absolute top-3 right-3 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors z-10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- State: initial — mic idle -->
          <template v-if="!quickAiText.trim() && !quickAiTextMode && !isRecording && !isTranscribing && !quickAiError && !quickAiLoading">
            <div class="flex flex-col items-center py-6">
              <button
                @click="toggleRecording"
                class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-600 transition-all active:scale-95"
              >
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                </svg>
              </button>
              <p class="text-sm text-gray-400 dark:text-gray-500 mt-4">Нажми и говори</p>
              <button @click="switchToTextMode" class="text-xs text-gray-400 dark:text-gray-600 mt-2 hover:text-gray-500">или введи текстом</button>
            </div>
          </template>

          <!-- State: recording -->
          <template v-else-if="isRecording">
            <div class="relative flex flex-col items-center py-6 overflow-hidden">
              <!-- Equalizer background -->
              <div class="absolute inset-0 flex items-end justify-center gap-[3px] opacity-15 px-4 pb-2">
                <div
                  v-for="i in eqBars"
                  :key="i.id"
                  class="w-1 rounded-full bg-red-400"
                  :style="{
                    height: i.h + 'px',
                    transition: 'height 0.3s ease',
                  }"
                />
              </div>
              <!-- Stop button -->
              <button
                @click="toggleRecording"
                class="relative w-20 h-20 rounded-full bg-red-500 text-white flex items-center justify-center shadow-lg shadow-red-500/30"
              >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
                </svg>
              </button>
              <p class="relative text-sm text-red-400 mt-4">Слушаю...</p>
            </div>
          </template>

          <!-- State: transcribing -->
          <template v-else-if="isTranscribing">
            <div class="relative flex items-center justify-center py-12 overflow-hidden">
              <div class="absolute inset-0 flex items-end justify-center gap-[3px] opacity-10 px-4 pb-2">
                <div
                  v-for="i in eqBars"
                  :key="'t'+i.id"
                  class="w-1 rounded-full bg-primary-400"
                  :style="{ height: i.h + 'px', transition: 'height 0.3s ease' }"
                />
              </div>
              <p class="relative text-sm text-gray-400 dark:text-gray-500">Распознаю речь...</p>
            </div>
          </template>

          <!-- State: error -->
          <template v-else-if="quickAiError && !quickAiText.trim() && !quickAiTextMode">
            <div class="flex flex-col items-center py-6">
              <div class="w-16 h-16 rounded-full bg-red-50 dark:bg-red-900/20 text-red-400 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
              </div>
              <p class="text-sm text-red-400 mt-3">{{ quickAiError }}</p>
              <button @click="quickAiError = ''" class="text-sm text-primary-500 hover:text-primary-600 mt-2">Попробовать снова</button>
            </div>
          </template>

          <!-- State: creating task (loading after submit) -->
          <template v-else-if="quickAiLoading">
            <div class="relative flex items-center justify-center py-12 overflow-hidden">
              <div class="absolute inset-0 flex items-end justify-center gap-[3px] opacity-10 px-4 pb-2">
                <div
                  v-for="i in eqBarsCreate"
                  :key="'c'+i.id"
                  class="w-1 rounded-full bg-emerald-400"
                  :style="{ height: i.h + 'px', transition: 'height 0.3s ease' }"
                />
              </div>
              <p class="relative text-sm text-gray-400 dark:text-gray-500">Создаю задачу...</p>
            </div>
          </template>

          <!-- State: text ready — textarea + buttons -->
          <template v-else>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Быстрая задача</h3>
            <div class="relative">
              <textarea
                ref="quickAiTextarea"
                v-model="quickAiText"
                rows="6"
                placeholder="Опиши задачу..."
                class="w-full px-3 py-2 pr-12 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none"
                @keydown.meta.enter="submitQuickAi"
                @keydown.ctrl.enter="submitQuickAi"
              />
              <button
                @click="toggleRecording"
                class="absolute right-3 bottom-3 w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z" />
                </svg>
              </button>
            </div>
            <div class="mt-3 flex justify-between items-center">
              <span v-if="quickAiError" class="text-xs text-red-500">{{ quickAiError }}</span>
              <span v-else class="text-[10px] text-gray-400">Ctrl+Enter для отправки</span>
              <div class="flex space-x-2">
                <button
                  @click="closeQuickAi"
                  class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                >
                  Отмена
                </button>
                <button
                  @click="submitQuickAi"
                  :disabled="!quickAiText.trim()"
                  class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
                >
                  <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                  </svg>
                  Анализ
                </button>
              </div>
            </div>
          </template>

        </div>
      </div>
    </Teleport>

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

    <GlobalConfirm />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import GlobalConfirm from '@/components/common/GlobalConfirm.vue'
import NavLink from '@/components/common/NavLink.vue'
import DroppableNavLink from '@/components/common/DroppableNavLink.vue'
import Toolbar from '@/components/common/Toolbar.vue'
import loadingLogoImg from '@/assets/images/logo.png'
import sidebarLogoImg from '@/assets/images/logo_small.png'
import ProjectModal from '@/components/projects/ProjectModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import api from '@/services/api'
import { useTaskDraft } from '@/composables/useTaskDraft'
import GoalModal from '@/components/goals/GoalModal.vue'
import { useGoalsStore } from '@/stores/goals'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import { useNotificationsStore } from '@/stores/notifications'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const tasksStore = useTasksStore()
const projectsStore = useProjectsStore()
const goalsStore = useGoalsStore()
const lifeSpheresStore = useLifeSpheresStore()
const notificationsStore = useNotificationsStore()
const loadingLogo = loadingLogoImg
const sidebarLogo = sidebarLogoImg
const appLoading = ref(true)
const loadingText = ref('Загрузка...'.split(''))
const sidebarOpen = ref(false)
const showAddMenu = ref(false)
const showUserMenu = ref(false)
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
const user = computed(() => authStore.user)
const taskCounts = computed(() => tasksStore.counts)
const activeProjects = computed(() => {
  return projectsStore.activeProjects.map(p => {
    const projectTasks = tasksStore.allTasks.filter(t => t.project_id === p.id)
    const total = projectTasks.length
    const completed = projectTasks.filter(t => t.completed_at).length
    return {
      ...p,
      total_tasks_count: total,
      completed_tasks_count: completed,
      tasks_count: total - completed,
    }
  })
})
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
  const scheduledCount = taskCounts.value.scheduled || 0
  const todayCount = taskCounts.value.today || 0
  const tomorrowCount = taskCounts.value.tomorrow || 0
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

const handleCalendarClick = () => {
  router.push({ path: '/calendar', query: { view: 'month' } })
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const { draftTask, showDraft: showDraftTask, startDraft, closeDraft } = useTaskDraft(() => tasksStore.fetchAllTasks?.({ force: true }))

const handleQuickAddTask = () => startDraft()
const onDraftSaved = () => {}
const handleCloseDraft = () => closeDraft()

// Quick AI Task
const showQuickAiInput = ref(false)
const quickAiText = ref('')
const quickAiLoading = ref(false)
const quickAiError = ref('')
const quickAiTextarea = ref(null)

const isRecording = ref(false)
const isTranscribing = ref(false)
const quickAiTextMode = ref(false)
const eqBars = ref(Array.from({ length: 24 }, (_, i) => ({ id: i, h: 4 })))
const eqBarsCreate = ref(Array.from({ length: 24 }, (_, i) => ({ id: i, h: 4 })))
let eqInterval = null
let eqCreateInterval = null

watch(quickAiTextMode, (v) => {
  if (v) nextTick(() => quickAiTextarea.value?.focus())
})
let mediaRecorder = null
let audioChunks = []

function closeQuickAi() {
  showQuickAiInput.value = false
  quickAiText.value = ''
  quickAiError.value = ''
  quickAiTextMode.value = false
  stopRecording()
}

function switchToTextMode() {
  quickAiTextMode.value = true
  nextTick(() => quickAiTextarea.value?.focus())
}

async function toggleRecording() {
  if (isRecording.value) {
    stopRecording()
  } else {
    await startRecording()
  }
}

async function startRecording() {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    audioChunks = []
    mediaRecorder = new MediaRecorder(stream, { mimeType: MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/mp4' })
    mediaRecorder.ondataavailable = (e) => { if (e.data.size > 0) audioChunks.push(e.data) }
    mediaRecorder.onstop = async () => {
      stream.getTracks().forEach(t => t.stop())
      if (audioChunks.length === 0) return
      const blob = new Blob(audioChunks, { type: mediaRecorder.mimeType })
      if (blob.size < 1000) {
        quickAiError.value = 'Слишком короткая запись'
        stopEq()
        return
      }
      await transcribeAudio(blob, mediaRecorder.mimeType)
    }
    mediaRecorder.start()
    isRecording.value = true
    startEq()
  } catch (e) {
    quickAiError.value = 'Нет доступа к микрофону'
  }
}

function stopRecording() {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop()
  }
  isRecording.value = false
}

function startEq() {
  eqInterval = setInterval(() => {
    eqBars.value = eqBars.value.map(b => ({ ...b, h: 4 + Math.random() * 56 }))
  }, 300)
}

function stopEq() {
  clearInterval(eqInterval)
  eqInterval = null
  eqBars.value = eqBars.value.map(b => ({ ...b, h: 4 }))
}

function startCreateEq() {
  eqCreateInterval = setInterval(() => {
    eqBarsCreate.value = eqBarsCreate.value.map(b => ({ ...b, h: 4 + Math.random() * 56 }))
  }, 300)
}

function stopCreateEq() {
  clearInterval(eqCreateInterval)
  eqCreateInterval = null
  eqBarsCreate.value = eqBarsCreate.value.map(b => ({ ...b, h: 4 }))
}

async function transcribeAudio(blob, mimeType) {
  isTranscribing.value = true
  quickAiError.value = ''
  try {
    const reader = new FileReader()
    const base64 = await new Promise((resolve) => {
      reader.onloadend = () => resolve(reader.result.split(',')[1])
      reader.readAsDataURL(blob)
    })
    const res = await api.post('/v1/tasks/transcribe', { audio: base64, mime_type: mimeType })
    const text = res.data?.text
    if (text) {
      quickAiText.value = quickAiText.value ? quickAiText.value + ' ' + text : text
    } else {
      quickAiError.value = 'Не удалось распознать речь'
    }
  } catch (e) {
    quickAiError.value = 'Ошибка распознавания'
  } finally {
    isTranscribing.value = false
    stopEq()
  }
}

async function submitQuickAi() {
  const text = quickAiText.value.trim()
  if (!text) return
  quickAiLoading.value = true
  quickAiError.value = ''
  startCreateEq()
  try {
    const res = await api.post('/v1/tasks/parse', { text })
    const task = res.data
    stopCreateEq()
    closeQuickAi()
    router.push({ name: 'TaskPage', params: { taskId: task.id }, state: { task } })
  } catch (e) {
    quickAiError.value = 'Не удалось создать задачу'
    stopCreateEq()
  } finally {
    quickAiLoading.value = false
  }
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

onMounted(async () => {
  try {
    // Чистим брошенные пустые черновики (например после refresh без close)
    api.post('/v1/tasks/cleanup-empty').catch(() => {})

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
  notificationsStore.startPolling()
})

onUnmounted(() => {
  tasksStore.stopSync()
  projectsStore.stopSync()
  notificationsStore.stopPolling()
})

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

.safe-area-bottom {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>

