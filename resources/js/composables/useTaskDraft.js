import { ref } from 'vue'
import api from '@/services/api'

/**
 * Черновик задачи — живёт в памяти до первого POST в TaskDetail.save().
 * Пока нет title, задача в БД не создаётся.
 */
export function useTaskDraft(onTaskPersisted) {
  const draftTask = ref(null)
  const showDraft = ref(false)

  const startDraft = (defaults = {}) => {
    draftTask.value = {
      id: null,
      title: '',
      status: 'inbox',
      priority: 'medium',
      ...defaults,
    }
    showDraft.value = true
  }

  const closeDraft = async () => {
    const id = draftTask.value?.id
    showDraft.value = false
    draftTask.value = null
    if (!id) return // задача не была сохранена — чистить нечего
    try {
      const fresh = (await api.get(`/v1/tasks/${id}`)).data
      const title = (fresh.title || '').trim()
      const isEmpty = !title
        && !fresh.description?.trim()
        && !(fresh.checklist_items?.length)
        && !(fresh.attachments?.length)
        && !(fresh.assignees?.length)
        && !(fresh.watchers?.length)
      if (isEmpty) {
        await api.delete(`/v1/tasks/${id}`)
      } else if (typeof onTaskPersisted === 'function') {
        onTaskPersisted(fresh)
      }
    } catch (e) { console.error(e) }
  }

  return { draftTask, showDraft, startDraft, closeDraft }
}
