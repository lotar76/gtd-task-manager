<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ principle ? 'Редактирование принципа' : 'Новый принцип' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <div>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Название принципа"
                class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
                ref="nameInput"
              />
            </div>

            <div>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Описание (необязательно)"
                class="w-full bg-transparent border border-gray-200 dark:border-gray-700 rounded-lg p-3 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-y focus:border-gray-400 dark:focus:border-gray-500 transition-colors"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
              <button
                type="button"
                @click="$emit('close')"
                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
              >
                Отмена
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-4 py-2 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors disabled:opacity-50"
              >
                {{ saving ? 'Сохранение...' : (principle ? 'Сохранить' : 'Создать') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, reactive, watch, nextTick } from 'vue'
import { usePrinciplesStore } from '@/stores/principles'

const props = defineProps({
  show: Boolean,
  principle: { type: Object, default: null },
})

const emit = defineEmits(['close', 'saved'])

const store = usePrinciplesStore()
const saving = ref(false)
const nameInput = ref(null)

const form = reactive({
  name: '',
  description: '',
})

watch(() => props.show, (val) => {
  if (val) {
    form.name = props.principle?.name || ''
    form.description = props.principle?.description || ''
    nextTick(() => nameInput.value?.focus())
  }
})

const handleSubmit = async () => {
  saving.value = true
  try {
    if (props.principle) {
      await store.update(props.principle.id, { ...form })
    } else {
      await store.create({ ...form })
    }
    emit('saved')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
