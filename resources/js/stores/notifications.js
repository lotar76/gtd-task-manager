import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useNotificationsStore = defineStore('notifications', () => {
  const notifications = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  let pollInterval = null

  const fetchNotifications = async () => {
    try {
      const res = await api.get('/v1/notifications')
      notifications.value = res.data || []
    } catch (e) {
      console.error('Failed to fetch notifications', e)
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const res = await api.get('/v1/notifications/unread-count')
      unreadCount.value = res.data.count || 0
    } catch (e) {}
  }

  const markRead = async (id) => {
    await api.post(`/v1/notifications/${id}/read`)
    const n = notifications.value.find(n => n.id === id)
    if (n) n.read_at = new Date().toISOString()
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  }

  const markAllRead = async () => {
    await api.post('/v1/notifications/read-all')
    notifications.value.forEach(n => {
      if (!n.read_at) n.read_at = new Date().toISOString()
    })
    unreadCount.value = 0
  }

  const startPolling = () => {
    fetchUnreadCount()
    pollInterval = setInterval(fetchUnreadCount, 30000)
  }

  const stopPolling = () => {
    if (pollInterval) clearInterval(pollInterval)
  }

  return {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    fetchUnreadCount,
    markRead,
    markAllRead,
    startPolling,
    stopPolling,
  }
})
