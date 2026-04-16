<template>
  <div class="space-y-4">
    <div>
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Контексты</h2>
      <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 max-w-prose">
        Контекст отвечает на вопрос «где/чем/с кем я могу это сделать прямо сейчас» —
        `@дом`, `@телефон`, `@компьютер`. Используется в «Следующих действиях» для фильтра
        по текущей ситуации.
      </p>
    </div>

    <div class="space-y-1">
      <div
        v-for="c in contextsStore.allContexts"
        :key="c.id"
        class="group flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-800/60"
      >
        <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: c.color }"></span>
        <input
          :value="c.name"
          @change="rename(c, $event.target.value)"
          class="flex-1 bg-transparent border-0 outline-none text-sm text-gray-800 dark:text-gray-100 py-0.5"
        />
        <input
          type="color"
          :value="c.color"
          @change="recolor(c, $event.target.value)"
          class="w-5 h-5 rounded cursor-pointer border border-gray-200 dark:border-gray-700 opacity-0 group-hover:opacity-100"
          title="Цвет"
        />
        <button
          @click="remove(c)"
          class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500"
          title="Удалить"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>
      <div v-if="!contextsStore.allContexts.length" class="text-sm text-gray-400 py-2">Контекстов ещё нет</div>
    </div>

    <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
      <div class="flex items-center gap-2">
        <input type="color" v-model="newColor" class="w-6 h-6 rounded border border-gray-200 dark:border-gray-700 cursor-pointer" />
        <input
          v-model="newName"
          @keydown.enter.prevent="add"
          placeholder="@новый_контекст"
          class="flex-1 px-3 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600"
        />
        <button
          @click="add"
          :disabled="!newName.trim() || adding"
          class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded disabled:opacity-40 hover:opacity-90"
        >
          Добавить
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useContextsStore } from '@/stores/contexts'
import { useConfirmStore } from '@/stores/confirm'

const contextsStore = useContextsStore()
const confirmStore = useConfirmStore()

const newName = ref('')
const newColor = ref('#8B5CF6')
const adding = ref(false)

onMounted(() => contextsStore.fetchAll())

const add = async () => {
  const name = newName.value.trim()
  if (!name) return
  adding.value = true
  try {
    await contextsStore.create({ name, color: newColor.value })
    newName.value = ''
  } catch (e) { console.error(e) } finally { adding.value = false }
}

const rename = async (c, name) => {
  const trimmed = (name || '').trim()
  if (!trimmed || trimmed === c.name) return
  try {
    await api.put(`/v1/contexts/${c.id}`, { name: trimmed })
    c.name = trimmed
  } catch (e) { console.error(e) }
}

const recolor = async (c, color) => {
  try {
    await api.put(`/v1/contexts/${c.id}`, { color })
    c.color = color
  } catch (e) { console.error(e) }
}

const remove = async (c) => {
  const ok = await confirmStore.ask({
    title: 'Удалить контекст?',
    message: c.name,
    confirmText: 'Удалить',
  })
  if (!ok) return
  await contextsStore.remove(c.id)
}
</script>
