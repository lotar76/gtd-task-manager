import { ref } from 'vue'
import api from '@/services/api'

/**
 * Универсальный черновик задачи.
 * Используется в любом месте, где нужно «создать задачу» — открывает
 * новое представление TaskDetail и при закрытии удаляет пустую.
 *
 *   const { draftTask, showDraft, startDraft, closeDraft } = useTaskDraft()
 */
export function useTaskDraft(onTaskPersisted) {
  const draftTask = ref(null)
  const showDraft = ref(false)

  const startDraft = async (defaults = {}) => {
    const payload = {
      title: '',
      status: 'inbox',
      ...defaults,
    }
    try {
      const res = await api.post('/v1/tasks', payload)
      draftTask.value = res.data
      showDraft.value = true
    } catch (e) {
      console.error('Не удалось создать черновик задачи', e)
    }
  }

  const closeDraft = async () => {
    const id = draftTask.value?.id
    showDraft.value = false
    if (!id) { draftTask.value = null; return }
    try {
      const fresh = (await api.get(`/v1/tasks/${id}`)).data
      const title = (fresh.title || '').trim()
      const isEmpty = (!title || title === 'Без названия')
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
    draftTask.value = null
  }

  return { draftTask, showDraft, startDraft, closeDraft }
}
