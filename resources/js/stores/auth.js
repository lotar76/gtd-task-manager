import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token'))
  const loading = ref(false)

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
    }
  }

  const checkAuth = async () => {
    if (!token.value) return false
    
    try {
      const response = await api.get('/v1/me')
      console.log('Full response from /v1/me:', response.data)
      console.log('User object:', response.data.user)
      console.log('User email:', response.data.user?.email)
      user.value = response.data.user || response.data
      return true
    } catch {
      clearAuth()
      return false
    }
  }

  return {
    user,
    token,
    loading,
    login,
    register,
    logout,
    checkAuth,
  }
})

