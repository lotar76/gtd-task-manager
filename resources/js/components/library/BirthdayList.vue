<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Дни рождения</h2>
      <button @click="openNew" class="w-8 h-8 flex items-center justify-center rounded-full bg-primary-600 active:bg-primary-700 text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
      </button>
    </div>

    <div v-if="store.loading" class="text-center py-8 text-gray-400 text-sm">Загрузка...</div>
    <div v-else-if="sorted.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500 text-sm">Нет записей</div>
    <div v-else class="space-y-2">
      <div
        v-for="b in sorted" :key="b.id"
        @click="openEdit(b)"
        class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
      >
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-pink-600 dark:text-pink-400 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265z" />
            </svg>
          </div>
          <div class="min-w-0">
            <div class="font-medium text-gray-900 dark:text-white truncate">{{ b.name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(b.date) }}</div>
          </div>
        </div>
        <div class="text-xs font-medium px-2 py-1 rounded-full flex-shrink-0"
          :class="daysUntil(b.date) === 0 ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
        >
          {{ daysUntilLabel(b.date) }}
        </div>
      </div>
    </div>

    <BirthdayModal
      :show="showModal"
      :item="editItem"
      @close="showModal = false; editItem = null"
      @submit="handleSubmit"
      @delete="handleDelete"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useBirthdaysStore } from '@/stores/birthdays'
import BirthdayModal from './BirthdayModal.vue'
import dayjs from 'dayjs'

const store = useBirthdaysStore()
const showModal = ref(false)
const editItem = ref(null)

onMounted(() => store.fetchAll())

const sorted = computed(() => {
  return [...store.allBirthdays].sort((a, b) => daysUntil(a.date) - daysUntil(b.date))
})

const daysUntil = (dateStr) => {
  const today = dayjs().startOf('day')
  const bday = dayjs(dateStr)
  let next = bday.year(today.year())
  if (next.isBefore(today)) next = next.add(1, 'year')
  return next.diff(today, 'day')
}

const daysUntilLabel = (dateStr) => {
  const d = daysUntil(dateStr)
  if (d === 0) return 'Сегодня!'
  if (d === 1) return 'Завтра'
  return `через ${d} дн.`
}

const formatDate = (dateStr) => dayjs(dateStr).format('D MMMM')

const openNew = () => { editItem.value = null; showModal.value = true }
const openEdit = (item) => { editItem.value = item; showModal.value = true }

const handleSubmit = async (data) => {
  try {
    if (editItem.value) {
      await store.update(editItem.value.id, data)
    } else {
      await store.create(data)
    }
    showModal.value = false
    editItem.value = null
  } catch (e) { console.error(e) }
}

const handleDelete = async (item) => {
  if (!item) return
  await store.remove(item.id)
  showModal.value = false
  editItem.value = null
}
</script>
