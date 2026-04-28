import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/welcome',
    name: 'Landing',
    component: () => import('@/views/Landing.vue'),
    meta: { guest: true },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Auth/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/Auth/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/auth/callback',
    name: 'OAuthCallback',
    component: () => import('@/views/Auth/OAuthCallback.vue'),
  },
  {
    path: '/verify-email',
    name: 'VerifyEmail',
    component: () => import('@/views/Auth/VerifyEmail.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/',
    component: () => import('@/views/Layout/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard.vue'),
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('@/views/Settings/Index.vue'),
      },
      {
        path: 'inbox',
        name: 'Inbox',
        component: () => import('@/views/Tasks/Inbox.vue'),
      },
      {
        path: 'next-actions',
        name: 'NextActions',
        component: () => import('@/views/Tasks/NextActions.vue'),
      },
      {
        path: 'waiting',
        name: 'Waiting',
        component: () => import('@/views/Tasks/Waiting.vue'),
      },
      {
        path: 'someday',
        name: 'Someday',
        component: () => import('@/views/Tasks/Someday.vue'),
      },
      {
        path: 'today',
        name: 'Today',
        component: () => import('@/views/Tasks/Today.vue'),
      },
      {
        path: 'tomorrow',
        name: 'Tomorrow',
        component: () => import('@/views/Tasks/Tomorrow.vue'),
      },
      {
        path: 'calendar',
        name: 'Calendar',
        component: () => import('@/views/Tasks/Calendar.vue'),
      },
      {
        path: 'all',
        name: 'AllTasks',
        component: () => import('@/views/Tasks/AllTasks.vue'),
      },
      {
        path: 'archive',
        name: 'Archive',
        component: () => import('@/views/Tasks/Archive.vue'),
      },
      {
        path: 'projects',
        name: 'Projects',
        component: () => import('@/views/Projects/Index.vue'),
      },
      {
        path: 'projects/:projectId',
        name: 'ProjectShow',
        component: () => import('@/views/Projects/Show.vue'),
      },
      {
        path: 'goals',
        name: 'Goals',
        component: () => import('@/views/Goals/Index.vue'),
      },
      {
        path: 'goals/:goalId',
        name: 'GoalShow',
        component: () => import('@/views/Goals/Show.vue'),
      },
      {
        path: 'principles',
        name: 'Principles',
        component: () => import('@/views/Principles/Index.vue'),
      },
      {
        path: 'spheres',
        name: 'Spheres',
        component: () => import('@/views/Spheres/Index.vue'),
      },
      {
        path: 'spheres/:sphereId',
        name: 'SphereShow',
        component: () => import('@/views/Spheres/Show.vue'),
      },
      {
        path: 'documents',
        name: 'Documents',
        component: () => import('@/views/Documents/Index.vue'),
      },
      {
        path: 'documents/notes',
        name: 'DocumentsNotes',
        component: () => import('@/views/Documents/Notes.vue'),
      },
      {
        path: 'documents/birthdays',
        name: 'DocumentsBirthdays',
        component: () => import('@/views/Documents/Birthdays.vue'),
      },
      {
        path: 'documents/books',
        name: 'DocumentsBooks',
        component: () => import('@/views/Documents/Books.vue'),
      },
      {
        path: 'documents/films',
        name: 'DocumentsFilms',
        component: () => import('@/views/Documents/Films.vue'),
      },
      {
        path: 'documents/articles',
        name: 'DocumentsArticles',
        component: () => import('@/views/Documents/Articles.vue'),
      },
      {
        path: 'contacts',
        name: 'Contacts',
        component: () => import('@/views/Contacts/Index.vue'),
      },
      {
        path: 'challenge',
        name: 'Challenge',
        component: () => import('@/views/Challenge/Index.vue'),
      },
      {
        path: 'notifications',
        name: 'Notifications',
        component: () => import('@/views/Notifications/Index.vue'),
      },
      {
        path: 'tasks/:taskId',
        name: 'TaskPage',
        component: () => import('@/views/Tasks/TaskPage.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = !!localStorage.getItem('token')

  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'Landing' })
  } else if (to.meta.guest && isAuthenticated) {
    next({ name: 'Dashboard' })
  } else if (isAuthenticated && to.meta.requiresAuth && to.name !== 'VerifyEmail') {
    if (!authStore.user) {
      await authStore.checkAuth()
    }
    if (!authStore.emailVerified) {
      next({ name: 'VerifyEmail' })
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
