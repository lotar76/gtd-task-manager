import axios from 'axios'

// КРИТИЧНО: В production всегда используем относительный путь '/api'
// VITE_API_URL используется только для локальной разработки
// В production mode Vite заменяет import.meta.env.MODE на 'production'
const getApiUrl = () => {
  // В production всегда '/api'
  if (import.meta.env.MODE === 'production') {
    return '/api'
  }
  // В dev используем VITE_API_URL или fallback на '/api'
  return import.meta.env.VITE_API_URL || '/api'
}

const api = axios.create({
  baseURL: getApiUrl(),
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor для добавления токена
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor для обработки ошибок
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api


