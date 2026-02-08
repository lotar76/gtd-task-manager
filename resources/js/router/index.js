import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
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
        path: 'workspaces',
        name: 'Workspaces',
        component: () => import('@/views/Workspaces/Index.vue'),
      },
      {
        path: 'workspaces/:id',
        name: 'Workspace',
        component: () => import('@/views/Workspaces/Show.vue'),
        children: [
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
            path: 'all',
            name: 'AllTasks',
            component: () => import('@/views/Tasks/AllTasks.vue'),
          },
          {
            path: 'goals',
            name: 'Goals',
            component: () => import('@/views/Goals/Index.vue'),
          },
          {
            path: 'settings',
            name: 'WorkspaceSettings',
            component: () => import('@/views/Workspaces/Settings.vue'),
          },
        ],
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const isAuthenticated = !!localStorage.getItem('token')

  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'Login' })
  } else if (to.meta.guest && isAuthenticated) {
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router

