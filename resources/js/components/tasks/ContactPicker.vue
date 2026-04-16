<template>
  <div class="relative" ref="rootRef">
    <div class="flex flex-wrap gap-1.5 mb-1.5">
      <span
        v-for="id in selected"
        :key="id"
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs"
        :class="badgeClass"
      >
        {{ contactName(id) }}
        <button @click="remove(id)" class="hover:text-red-500">✕</button>
      </span>
    </div>
    <div class="relative">
      <input
        v-model="query"
        @focus="open = true"
        @input="open = true"
        type="text"
        :placeholder="placeholder"
        class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm"
      />
      <div
        v-if="open && filtered.length"
        class="absolute z-10 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto"
      >
        <button
          v-for="c in filtered"
          :key="c.id"
          type="button"
          @click="add(c.id)"
          class="w-full text-left px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between"
        >
          <span class="truncate">{{ c.name }}</span>
          <span v-if="c.specialization" class="ml-2 text-xs text-gray-400 truncate">{{ c.specialization }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  contacts: { type: Array, default: () => [] },
  selected: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Добавить' },
  badgeClass: { type: String, default: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' },
})
const emit = defineEmits(['update:selected'])

const rootRef = ref(null)
const query = ref('')
const open = ref(false)

const contactName = (id) => props.contacts.find(c => c.id === id)?.name || '—'

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  return props.contacts
    .filter(c => !props.selected.includes(c.id))
    .filter(c => !q || (c.name || '').toLowerCase().includes(q) || (c.specialization || '').toLowerCase().includes(q))
    .slice(0, 20)
})

const add = (id) => {
  emit('update:selected', [...props.selected, id])
  query.value = ''
  open.value = false
}
const remove = (id) => {
  emit('update:selected', props.selected.filter(x => x !== id))
}

const onDocClick = (e) => {
  if (rootRef.value && !rootRef.value.contains(e.target)) open.value = false
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>
