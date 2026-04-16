<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="$emit('close')">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md overflow-hidden my-6">
          <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-100 dark:border-gray-800">
            <span class="text-xs text-gray-400">{{ goal ? 'Редактирование цели' : 'Новая цель' }}</span>
            <button @click="$emit('close')" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="px-5 py-4 space-y-4">
            <!-- Название -->
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Что хочу достичь?"
              class="w-full bg-transparent border-0 outline-none text-xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 text-gray-900 dark:text-white px-0"
            />

            <!-- Описание (зачем) -->
            <textarea
              v-model="form.description"
              rows="2"
              placeholder="Зачем мне это нужно?"
              class="w-full bg-transparent border-0 outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed resize-none"
            ></textarea>

            <!-- Inline rows -->
            <div class="space-y-1">
              <InlineRow icon="calendar" label="Дата">
                <input v-model="form.deadline" type="date" class="w-full bg-transparent border-0 outline-none text-sm text-gray-800 dark:text-gray-100 py-0" />
              </InlineRow>
              <InlineSelect
                v-model="form.life_sphere_id"
                icon="sparkles"
                label="Сфера"
                placeholder="Не выбрано"
                :items="availableSpheres"
              />
            </div>

            <!-- Стих из Библии -->
            <div>
              <div class="flex items-center gap-1.5 mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5V6.5A2.5 2.5 0 016.5 4H20v13M4 19.5A2.5 2.5 0 006.5 22H20v-5" /></svg>
                Стих из Библии
              </div>
              <textarea v-model="form.bible_verse" rows="2" placeholder="Стих для вдохновения"
                class="w-full bg-transparent border-0 outline-none text-sm italic text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 resize-none border-l-2 border-gray-200 dark:border-gray-700 pl-3"></textarea>
            </div>

            <!-- Картинка -->
            <div>
              <div class="flex items-center gap-1.5 mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                Картинка
              </div>
              <div v-if="imagePreview || form.image_url" class="relative mb-2">
                <img :src="imagePreview || form.image_url" class="w-full h-32 object-cover rounded-lg" />
                <button type="button" @click="removeImage" class="absolute top-1 right-1 p-1 bg-black/60 text-white rounded hover:bg-red-600">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <input type="file" ref="fileInput" accept="image/*" @change="handleImageSelect" class="hidden" />
              <button type="button" @click="$refs.fileInput.click()"
                class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ imagePreview || form.image_url ? 'Заменить' : 'Выбрать изображение' }}
              </button>
            </div>

            <div v-if="error" class="text-red-500 text-xs">{{ error }}</div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
              <button type="button" @click="$emit('close')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md">Отмена</button>
              <button type="submit" :disabled="loading" class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-md disabled:opacity-50 hover:opacity-90">
                {{ loading ? 'Сохранение…' : 'Сохранить' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, h } from 'vue'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import InlineSelect from '@/components/common/InlineSelect.vue'

const ICONS = {
  calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  sparkles: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.29 5.29L20 11l-5.29 2.29L12 20l-2.29-5.29L4 11l5.29-2.29L12 3z',
}
const InlineRow = {
  props: ['icon', 'label'],
  setup(p, { slots }) {
    return () => h('div', { class: 'flex items-center gap-2.5 py-1.5 px-1 -mx-1 rounded hover:bg-gray-50 dark:hover:bg-gray-800/60' }, [
      h('div', { class: 'flex items-center gap-1.5 w-20 text-xs text-gray-400 flex-shrink-0' }, [
        h('svg', { class: 'w-3.5 h-3.5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
          h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.8', d: ICONS[p.icon] }),
        ]),
        h('span', p.label),
      ]),
      h('div', { class: 'flex-1 min-w-0' }, slots.default ? slots.default() : null),
    ])
  },
}

const props = defineProps({
  show: { type: Boolean, default: false },
  goal: { type: Object, default: null },
  serverError: { type: String, default: '' },
})
const emit = defineEmits(['close', 'submit'])

const lifeSpheresStore = useLifeSpheresStore()
const availableSpheres = computed(() => lifeSpheresStore.allSpheres)

const handleKeydown = (e) => { if (e.key === 'Escape' && props.show) emit('close') }
onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))

const form = ref({ life_sphere_id: null, name: '', deadline: '', description: '', bible_verse: '', image_url: null, imageFile: null })
const loading = ref(false)
const error = ref('')
const fileInput = ref(null)
const imagePreview = ref(null)

watch(() => props.goal, (g) => {
  form.value = {
    life_sphere_id: g?.life_sphere_id || null,
    name: g?.name || '',
    deadline: g?.deadline ? String(g.deadline).substring(0, 10) : '',
    description: g?.description || '',
    bible_verse: g?.bible_verse || '',
    image_url: g?.image_url || null,
    imageFile: null,
  }
  imagePreview.value = null
  error.value = ''
}, { immediate: true })

watch(() => props.show, (s) => { if (!s) { loading.value = false; error.value = ''; imagePreview.value = null } })
watch(() => props.serverError, (e) => { error.value = e; if (e) loading.value = false })

const handleImageSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) { error.value = 'Максимум 5MB'; return }
  if (!file.type.startsWith('image/')) { error.value = 'Это не изображение'; return }
  form.value.imageFile = file
  imagePreview.value = URL.createObjectURL(file)
  error.value = ''
}
const removeImage = () => {
  form.value.imageFile = null
  form.value.image_url = null
  imagePreview.value = null
  if (fileInput.value) fileInput.value.value = ''
}
const handleSubmit = () => {
  error.value = ''
  loading.value = true
  emit('submit', { ...form.value })
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
