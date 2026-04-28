import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useArticlesStore = defineStore('articles', () => {
  const allArticles = ref([])
  const allFolders = ref([])
  const allAuthors = ref([])
  const loading = ref(false)
  const loaded = ref(false)

  const fetchAll = async ({ force = false } = {}) => {
    if (loaded.value && !force) return
    loading.value = true
    try {
      const [articlesRes, foldersRes, authorsRes] = await Promise.all([
        api.get('/v1/articles'),
        api.get('/v1/article-folders'),
        api.get('/v1/article-authors'),
      ])
      allArticles.value = articlesRes.data.data || articlesRes.data || []
      allFolders.value = foldersRes.data.data || foldersRes.data || []
      allAuthors.value = authorsRes.data.data || authorsRes.data || []
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

  // Authors
  const createAuthor = async (data) => {
    const response = await api.post('/v1/article-authors', data)
    const item = response.data.data || response.data
    allAuthors.value.push(item)
    return item
  }

  const updateAuthor = async (id, data) => {
    const response = await api.put(`/v1/article-authors/${id}`, data)
    const updated = response.data.data || response.data
    const idx = allAuthors.value.findIndex(a => a.id === id)
    if (idx !== -1) allAuthors.value[idx] = updated
    return updated
  }

  const removeAuthor = async (id) => {
    await api.delete(`/v1/article-authors/${id}`)
    allAuthors.value = allAuthors.value.filter(a => a.id !== id)
  }

  return {
    allArticles,
    allFolders,
    allAuthors,
    loading,
    loaded,
    fetchAll,
    createArticle,
    updateArticle,
    removeArticle,
    createFolder,
    updateFolder,
    removeFolder,
    createAuthor,
    updateAuthor,
    removeAuthor,
  }
})
