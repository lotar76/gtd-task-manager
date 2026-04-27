<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ item ? 'Редактирование' : 'Новый день рождения' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Имя"
              class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
            />

            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Дата рождения</label>
              <input
                v-model="form.date"
                type="date"
                required
                class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-900 dark:text-white"
              />
            </div>

            <textarea
              v-model="form.note"
              rows="3"
              placeholder="Заметка (необязательно)"
              class="w-full bg-transparent border-0 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-none"
            ></textarea>

            <div v-if="error" class="text-red-500 text-xs">{{ error }}</div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800">
              <button
                v-if="item"
                type="button"
                @click="handleDelete"
                class="text-xs text-red-500 hover:text-red-700"
              >Удалить</button>
              <div v-else></div>
              <div class="flex gap-2">
                <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
                <button type="submit" :disabled="saving" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md disabled:opacity-50 hover:opacity-90">
                  {{ saving ? 'Сохранение...' : 'Сохранить' }}
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
import { ref, watch } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  item: { type: Object, default: null },
})

const emit = defineEmits(['close', 'submit', 'delete'])

const form = ref({ name: '', date: '', note: '' })
const saving = ref(false)
const error = ref('')

watch(() => props.show, (val) => {
  if (val) {
    error.value = ''
    if (props.item) {
      form.value = {
        name: props.item.name || '',
        date: props.item.date?.slice(0, 10) || '',
        note: props.item.note || '',
      }
    } else {
      form.value = { name: '', date: '', note: '' }
    }
  }
})

const handleSubmit = async () => {
  saving.value = true
  error.value = ''
  try {
    emit('submit', { ...form.value })
  } catch (e) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}

const handleDelete = () => emit('delete', props.item)
</script>
