import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useArticlesStore = defineStore('articles', () => {
  const allArticles = ref([])
  const allFolders = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const [articlesRes, foldersRes] = await Promise.all([
        api.get('/v1/articles'),
        api.get('/v1/article-folders'),
      ])
      allArticles.value = articlesRes.data.data || articlesRes.data || []
      allFolders.value = foldersRes.data.data || foldersRes.data || []
      loaded.value = true
    } finally {
      loading.value = false
    }
  }

  // Articles
  const createArticle = async (data) => {
    const response = await api.post('/v1/articles', data)
    const item = response.data.data || response.data
    allArticles.value.unshift(item)
    return item
  }

  const updateArticle = async (id, data) => {
    const response = await api.put(`/v1/articles/${id}`, data)
    const updated = response.data.data || response.data
    const idx = allArticles.value.findIndex(a => a.id === id)
    if (idx !== -1) allArticles.value[idx] = updated
    return updated
  }

  const removeArticle = async (id) => {
    await api.delete(`/v1/articles/${id}`)
    allArticles.value = allArticles.value.filter(a => a.id !== id)
  }

  // Folders
  const createFolder = async (data) => {
    const response = await api.post('/v1/article-folders', data)
    const item = response.data.data || response.data
    allFolders.value.push(item)
    return item
  }

  const updateFolder = async (id, data) => {
    const response = await api.put(`/v1/article-folders/${id}`, data)
    const updated = response.data.data || response.data
    const idx = allFolders.value.findIndex(f => f.id === id)
    if (idx !== -1) allFolders.value[idx] = updated
    return updated
  }

  const removeFolder = async (id) => {
    await api.delete(`/v1/article-folders/${id}`)
    allFolders.value = allFolders.value.filter(f => f.id !== id)
  }

  return {
    allArticles,
    allFolders,
    loading,
    loaded,
    fetchAll,
    createArticle,
    updateArticle,
    removeArticle,
    createFolder,
    updateFolder,
    removeFolder,
  }
})
