<template>
  <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20">
    <div class="flex items-center justify-between px-4 lg:px-8 py-3">
      <!-- Mobile: Nav Icons + Date -->
      <div class="lg:hidden flex items-center gap-2">
        <router-link
          to="/"
          class="p-1.5 rounded-lg transition-colors"
          :class="isDashboardActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </router-link>
        <router-link
          :to="inboxRoute"
          class="relative p-1.5 rounded-lg transition-colors"
          :class="isInboxActive ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <span
            v-if="inboxCount > 0"
            class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center"
          >
            {{ inboxCount }}
          </span>
        </router-link>
        <router-link
          to="/contacts"
          class="p-1.5 rounded-lg transition-colors"
          :class="$route.path === '/contacts' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </router-link>
        <router-link
          to="/challenge"
          class="p-1.5 rounded-lg transition-colors"
          :class="$route.path === '/challenge' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
        >
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
        </router-link>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">
          {{ currentDate }}
        </span>
      </div>

      <!-- Nav Links (desktop) -->
      <nav class="hidden lg:flex items-center space-x-1 ml-4">
        <router-link
          to="/"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
          :class="isDashboardActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span>Дашборд</span>
        </router-link>
        <router-link
          :to="inboxRoute"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
          :class="isInboxActive ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <span>Входящие</span>
          <span
            v-if="inboxCount > 0"
            class="px-1.5 py-0.5 text-xs font-semibold rounded-full"
            :class="isInboxActive ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300'"
          >
            {{ inboxCount }}
          </span>
        </router-link>
        <router-link
          to="/contacts"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
          :class="$route.path === '/contacts' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span>Контакты</span>
        </router-link>
        <router-link
          to="/challenge"
          class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors"
          :class="$route.path === '/challenge' ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <span>Трекер</span>
        </router-link>
      </nav>

      <!-- Spacer -->
      <div class="flex-1"></div>

      <!-- Right: Actions -->
      <div class="flex items-center space-x-2 lg:space-x-4 ml-4">
        <!-- Quick Add Menu -->
        <div class="relative hidden lg:block" ref="quickAddMenuContainer">
        <button
            @click.stop="toggleQuickAddMenu"
          class="inline-flex items-center px-3 lg:px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-primary-500 transition-colors"
        >
          <svg class="w-5 h-5 lg:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
            <span class="hidden lg:inline">Создать</span>
        </button>

          <!-- Quick Add Dropdown -->
          <Transition name="dropdown">
            <div
              v-if="showQuickAddMenu"
              class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-2 z-50"
            >
              <button
                @click="handleQuickAddTask"
                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
              >
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <span>Задачу</span>
                </div>
              </button>
              <button
                @click="handleQuickAddProject"
                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
              >
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                  <span>Поток</span>
                </div>
              </button>
              <button
                @click="handleQuickAddGoal"
                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
              >
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                  </svg>
                  <span>Цель</span>
                </div>
              </button>
            </div>
          </Transition>
        </div>

        <!-- Theme Toggle (desktop only) -->
        <button
          @click="themeStore.toggleTheme()"
          class="hidden lg:block p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          :title="themeStore.isDark ? 'Светлая тема' : 'Тёмная тема'"
        >
          <svg v-if="themeStore.isDark" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
        </button>

        <!-- Notifications Bell -->
        <div class="relative">
          <button
            @click.stop="toggleNotifications"
            class="relative p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span
              v-if="notificationsStore.unreadCount > 0"
              class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full flex items-center justify-center"
            >
              {{ notificationsStore.unreadCount > 9 ? '9+' : notificationsStore.unreadCount }}
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <div
            v-if="showNotifications"
            v-click-outside="() => showNotifications = false"
            class="fixed left-2 right-2 top-14 lg:absolute lg:left-auto lg:top-auto lg:right-0 lg:mt-2 lg:w-80 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 z-50 max-h-96 overflow-y-auto"
          >
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-600">
              <span class="text-sm font-medium text-gray-900 dark:text-white">Уведомления</span>
              <button
                v-if="notificationsStore.unreadCount > 0"
                @click="notificationsStore.markAllRead()"
                class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
              >
                Прочитать все
              </button>
            </div>
            <div v-if="notificationsStore.notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
              Нет уведомлений
            </div>
            <div v-else>
              <div
                v-for="n in notificationsStore.notifications"
                :key="n.id"
                @click="handleNotificationClick(n)"
                class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                :class="!n.read_at ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''"
              >
                <div class="text-sm text-gray-900 dark:text-gray-100">
                  <template v-if="n.data?.type === 'task_changed'">
                    <span class="font-medium">{{ n.data.task_title }}</span>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                      {{ n.data.changer_name }}: {{ n.data.changes?.join(', ') }}
                    </div>
                  </template>
                  <template v-else-if="n.data?.type === 'contact_invite'">
                    <span class="font-medium">{{ n.data.sender_name }}</span>
                    <span> хочет добавить вас в контакты</span>
                  </template>
                  <template v-else>
                    {{ JSON.stringify(n.data) }}
                  </template>
                </div>
                <div class="text-[10px] text-gray-400 mt-1">
                  {{ formatTimeAgo(n.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="relative">
          <button
            @click.stop="showUserMenu = !showUserMenu"
            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
              {{ userInitials }}
            </div>
            <div class="hidden lg:block text-left">
              <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</div>
            </div>
            <svg class="hidden lg:block w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- User Dropdown -->
          <div
            v-if="showUserMenu"
            v-click-outside="() => showUserMenu = false"
            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-2"
          >
            <button
              @click="handleProfile"
              class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
            >
              <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Профиль</span>
              </div>
            </button>
            <button
              @click="handleSettings"
              class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
            >
              <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Настройки</span>
              </div>
            </button>
            <button
              @click="handleToggleTheme"
              class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
            >
              <div class="flex items-center space-x-2">
                <svg v-if="themeStore.isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <span>{{ themeStore.isDark ? 'Светлая тема' : 'Тёмная тема' }}</span>
              </div>
            </button>
            <div class="border-t border-gray-200 dark:border-gray-600 my-2" />
            <button
              @click="handleLogout"
              class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
            >
              <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Выйти</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useThemeStore } from '@/stores/theme'
import { useNotificationsStore } from '@/stores/notifications'
import logoLight from '@/assets/images/logo.svg'
import logoDark from '@/assets/images/logo-dark.svg'

const route = useRoute()
const router = useRouter()
const themeStore = useThemeStore()
const notificationsStore = useNotificationsStore()
const logo = computed(() => themeStore.isDark ? logoDark : logoLight)

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
  inboxCount: {
    type: Number,
    default: 0,
  },
})

const inboxRoute = '/inbox'

const isDashboardActive = computed(() => route.path === '/')
const isInboxActive = computed(() => route.path === '/inbox')

const emit = defineEmits(['quick-add', 'quick-add-task', 'quick-add-project', 'quick-add-goal', 'search', 'logout', 'profile', 'settings'])

const searchQuery = ref('')
const showUserMenu = ref(false)
const showQuickAddMenu = ref(false)
const showNotifications = ref(false)
const quickAddMenuContainer = ref(null)

const userInitials = computed(() => {
  const name = props.user?.name || ''
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const currentDate = computed(() => {
  const now = new Date()
  const days = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
  const months = ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек']

  const dayName = days[now.getDay()]
  const day = now.getDate()
  const month = months[now.getMonth()]

  return `${dayName}, ${day} ${month}`
})

const toggleQuickAddMenu = () => {
  showQuickAddMenu.value = !showQuickAddMenu.value
}

const handleQuickAddTask = () => {
  showQuickAddMenu.value = false
  emit('quick-add-task')
}

const handleQuickAddProject = () => {
  showQuickAddMenu.value = false
  emit('quick-add-project')
}

const handleQuickAddGoal = () => {
  showQuickAddMenu.value = false
  emit('quick-add-goal')
}

// Обработчик клика вне меню
const handleClickOutside = (event) => {
  if (quickAddMenuContainer.value && !quickAddMenuContainer.value.contains(event.target)) {
    showQuickAddMenu.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    showQuickAddMenu.value = false
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    emit('search', searchQuery.value)
  }
}

const handleSearchEnter = () => {
  handleSearch()
}

const handleLogout = () => {
  showUserMenu.value = false
  emit('logout')
}

const handleProfile = () => {
  showUserMenu.value = false
  emit('profile')
}

const handleSettings = () => {
  showUserMenu.value = false
  emit('settings')
}

const handleToggleTheme = () => {
  themeStore.toggleTheme()
  showUserMenu.value = false
}

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    notificationsStore.fetchNotifications()
  }
}

const handleNotificationClick = (n) => {
  if (!n.read_at) notificationsStore.markRead(n.id)
  showNotifications.value = false
  if (n.data?.type === 'contact_invite') {
    router.push('/contacts')
  } else if (n.data?.task_id) {
    // Task notifications will be handled by opening the task
    router.push('/today')
  }
}

const formatTimeAgo = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'только что'
  if (diff < 3600) return `${Math.floor(diff / 60)} мин назад`
  if (diff < 86400) return `${Math.floor(diff / 3600)} ч назад`
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

// Директива для закрытия при клике вне элемента (для других dropdown)
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  },
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
</style>

