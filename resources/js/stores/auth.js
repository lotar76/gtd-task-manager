import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)
  const emailVerified = ref(true)

  const setToken = (newToken) => {
    token.value = newToken
    localStorage.setItem('token', newToken)
  }

  const clearAuth = () => {
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await api.post('/v1/login', credentials)
      setToken(response.data.token)
      user.value = response.data.user
      return response.data
    } finally {
      loading.value = false
    }
  }

  const register = async (userData) => {
    loading.value = true
    try {
      const response = await api.post('/v1/register', userData)
      setToken(response.data.token)
      user.value = response.data.user
      return response.data
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/v1/logout')
    } finally {
      clearAuth()
      window.location.href = '/welcome'
    }
  }

  const checkAuth = async () => {
    if (!token.value) return false

    try {
      const response = await api.get('/v1/me')
      user.value = response.data.user || response.data
      emailVerified.value = response.data.email_verified !== false
      return true
    } catch {
      clearAuth()
      return false
    }
  }

  const updateProfile = async (name) => {
    const response = await api.put('/v1/profile', { name })
    user.value = response.data.user
    return response.data
  }

  const updatePassword = async (passwordData) => {
    const response = await api.put('/v1/password', passwordData)
    return response.data
  }

  return {
    user,
    token,
    loading,
    emailVerified,
    setToken,
    login,
    register,
    logout,
    checkAuth,
    updateProfile,
    updatePassword,
  }
})

