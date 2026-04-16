import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useConfirmStore = defineStore('confirm', () => {
  const state = ref({
    open: false,
    title: '',
    message: '',
    confirmText: 'Удалить',
    cancelText: 'Отмена',
    danger: true,
    resolver: null,
  })

  const ask = (opts = {}) => {
    return new Promise((resolve) => {
      state.value = {
        open: true,
        title: opts.title || 'Подтвердите действие',
        message: opts.message || '',
        confirmText: opts.confirmText || 'Удалить',
        cancelText: opts.cancelText || 'Отмена',
        danger: opts.danger !== false,
        resolver: resolve,
      }
    })
  }

  const resolve = (value) => {
    const r = state.value.resolver
    state.value.open = false
    state.value.resolver = null
    if (r) r(value)
  }

  return { state, ask, resolve }
})
