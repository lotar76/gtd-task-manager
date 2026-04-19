<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ note ? 'Редактирование заметки' : 'Новая заметка' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="Название заметки"
              class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
            />

            <textarea
              v-model="form.content"
              rows="10"
              placeholder="Текст заметки..."
              class="w-full bg-transparent border-0 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-none"
            ></textarea>

            <div v-if="error" class="text-red-500 text-xs">{{ error }}</div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
              <button
                v-if="note"
                type="button"
                @click="handleDelete"
                class="text-xs text-red-500 hover:text-red-700"
              >
                Удалить
              </button>
              <div v-else></div>
              <div class="flex gap-2">
                <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
                <button type="submit" :disabled="loading" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md disabled:opacity-50 hover:opacity-90">
                  {{ loading ? 'Сохранение…' : 'Сохранить' }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  note: { type: Object, default: null },
  goalId: { type: Number, default: null },
  taskId: { type: Number, default: null },
})
const emit = defineEmits(['close', 'submit', 'delete'])

const form = ref({ title: '', content: '' })
const loading = ref(false)
const error = ref('')

const handleKeydown = (e) => { if (e.key === 'Escape' && props.show) emit('close') }
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

watch(() => props.note, (n) => {
  form.value = {
    title: n?.title || '',
    content: n?.content || '',
  }
  error.value = ''
}, { immediate: true })

watch(() => props.show, (s) => {
  if (s) {
    form.value = { title: props.note?.title || '', content: props.note?.content || '' }
    error.value = ''
  } else {
    loading.value = false
    error.value = ''
  }
})

const handleSubmit = () => {
  error.value = ''
  loading.value = true
  emit('submit', {
    ...form.value,
    goal_id: props.note?.goal_id ?? props.goalId,
    task_id: props.note?.task_id ?? props.taskId,
    id: props.note?.id || null,
  })
}

const handleDelete = () => {
  if (props.note) emit('delete', props.note)
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
