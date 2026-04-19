<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Контакты</h1>
      <button
        @click="openCreate"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Добавить контакт</span>
      </button>
    </div>

    <!-- Входящие приглашения -->
    <div v-if="pendingInvites.length > 0" class="mb-4 space-y-2">
      <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Входящие приглашения</h3>
      <div
        v-for="invite in pendingInvites"
        :key="invite.id"
        class="flex items-center justify-between bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg px-4 py-3"
      >
        <div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">{{ invite.sender?.name }}</span>
          <span class="text-sm text-gray-500 dark:text-gray-400"> хочет добавить вас в контакты</span>
        </div>
        <div class="flex gap-2">
          <button
            @click="respondToInvite(invite.id, 'accept')"
            class="px-3 py-1 text-xs bg-emerald-600 text-white rounded hover:bg-emerald-700"
          >
            Принять
          </button>
          <button
            @click="respondToInvite(invite.id, 'decline')"
            class="px-3 py-1 text-xs text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
          >
            Отклонить
          </button>
        </div>
      </div>
    </div>

    <!-- Фильтры -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-2">
      <div class="relative flex-1 min-w-[200px]">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Поиск по имени, email, специализации, заметкам..."
          class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white"
        />
      </div>

      <button
        @click="filters.favoritesOnly = !filters.favoritesOnly"
        class="px-3 py-2 text-sm rounded border flex items-center gap-1.5 transition-colors"
        :class="filters.favoritesOnly
          ? 'bg-yellow-50 border-yellow-300 text-yellow-700 dark:bg-yellow-900/30 dark:border-yellow-600 dark:text-yellow-300'
          : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'"
        title="Только избранные"
      >
        <svg class="w-4 h-4" :class="filters.favoritesOnly ? 'fill-current' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
        Избранные
      </button>

      <select v-model="filters.type" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
        <option value="">Все типы</option>
        <option value="connector">Коннекторы</option>
        <option value="condenser">Конденсаторы</option>
        <option value="bridge">Мостики</option>
        <option value="regular">Обычные</option>
      </select>

      <select v-model="filters.link" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
        <option value="">Все контакты</option>
        <option value="linked">Пользователи системы</option>
        <option value="external">Внешние</option>
      </select>

      <select v-model="filters.activity" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
        <option value="">Любая активность</option>
        <option value="active">С активными задачами</option>
        <option value="none">Без задач</option>
      </select>

      <select v-model="filters.sort" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:text-white">
        <option value="favorite">Избранные → имя</option>
        <option value="name">Имя А→Я</option>
        <option value="active_tasks">Больше активных задач</option>
        <option value="type">По типу</option>
      </select>

      <button
        v-if="hasActiveFilters"
        @click="resetFilters"
        class="px-2 py-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
      >
        Сбросить
      </button>

      <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">
        {{ filteredContacts.length }} из {{ contacts.length }}
      </span>
    </div>

    <div v-if="loading" class="text-center text-gray-500 py-10">Загрузка...</div>

    <div v-else-if="contacts.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-10">
      Контактов пока нет
    </div>

    <div v-else-if="filteredContacts.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-10">
      По заданным фильтрам ничего не найдено
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="contact in filteredContacts"
        :key="contact.id"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5 hover:shadow-lg transition-shadow"
      >
        <div class="flex items-start justify-between mb-2 gap-2">
          <div class="flex items-start gap-2 min-w-0">
            <button @click="toggleFavorite(contact)" :title="contact.is_favorite ? 'Убрать из избранного' : 'В избранное'" class="flex-shrink-0 mt-0.5">
              <svg class="w-5 h-5" :class="contact.is_favorite ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </button>
            <div class="min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                {{ contact.name }}
              </h3>
              <div class="flex items-center gap-2 flex-wrap mt-0.5">
                <span v-if="contact.contact_type && contact.contact_type !== 'regular'" class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-medium rounded" :class="typeBadgeClass(contact.contact_type)">
                  {{ typeLabel(contact.contact_type) }}
                </span>
                <span v-if="contact.specialization" class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  {{ contact.specialization }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex gap-2 flex-shrink-0">
            <button @click="openEdit(contact)" class="text-gray-400 hover:text-primary-600" title="Редактировать">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button v-if="!contact.contact_user_id" @click="remove(contact)" class="text-gray-400 hover:text-red-600" title="Удалить">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
              </svg>
            </button>
          </div>
        </div>

        <p v-if="contact.email" class="text-sm text-gray-600 dark:text-gray-300 truncate">
          ✉ {{ contact.email }}
        </p>
        <p v-if="contact.phone" class="text-sm text-gray-600 dark:text-gray-300">
          ☎ {{ contact.phone }}
        </p>
        <p v-if="contact.personal_phone && contact.personal_phone !== contact.phone" class="text-sm text-gray-500 dark:text-gray-400">
          ☎ {{ contact.personal_phone }} <span class="text-xs">(личный)</span>
        </p>
        <p v-if="contact.address" class="text-sm text-gray-500 dark:text-gray-400 truncate">
          📍 {{ contact.address }}
        </p>
        <div v-if="contact.messengers && Object.keys(contact.messengers).length" class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap gap-x-3">
          <span v-for="(val, key) in contact.messengers" :key="key">{{ key }}: {{ val }}</span>
        </div>
        <p v-if="contact.notes" class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
          {{ contact.notes }}
        </p>
        <div v-if="contact.active_tasks_count || contact.completed_tasks_count" class="flex items-center gap-3 mt-3 text-xs">
          <span v-if="contact.active_tasks_count" class="text-primary-600 dark:text-primary-400">
            В работе: {{ contact.active_tasks_count }}
          </span>
          <span v-if="contact.completed_tasks_count" class="text-gray-500 dark:text-gray-400">
            Выполнено: {{ contact.completed_tasks_count }}
          </span>
        </div>
      </div>
    </div>

    <!-- Модалка -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
          {{ form.id ? 'Редактировать контакт' : 'Новый контакт' }}
        </h2>

        <div v-if="isLinked" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 text-xs text-blue-700 dark:text-blue-300 rounded">
          Этот контакт связан с пользователем системы. Имя, email и основной телефон — его данные, их изменять нельзя.
          Свои заметки и доп. контакты — ниже.
        </div>

        <!-- Поиск пользователей системы (только при создании) -->
        <div v-if="!form.id && !isLinked" class="mb-4">
          <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Найти в системе (по email или имени)</label>
          <div class="relative">
            <input
              v-model="userSearchQuery"
              @input="searchSystemUsers"
              type="text"
              placeholder="Введите email или имя (от 3 символов)..."
              class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="userSearchLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
              <svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-20" /><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
            </div>
          </div>
          <div v-if="foundUsers.length > 0" class="mt-2 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
            <div
              v-for="u in foundUsers"
              :key="u.id"
              class="flex items-center justify-between px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 border-b last:border-b-0 border-gray-100 dark:border-gray-600"
            >
              <div class="min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ u.name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ u.email }}</div>
              </div>
              <div class="flex-shrink-0 ml-2">
                <span v-if="u.already_linked" class="text-xs text-emerald-600 dark:text-emerald-400">Уже связан</span>
                <span v-else-if="u.pending_invite" class="text-xs text-amber-600 dark:text-amber-400">Приглашение отправлено</span>
                <button
                  v-else
                  @click="sendInviteToUser(u)"
                  class="px-3 py-1 text-xs bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors"
                >
                  Пригласить
                </button>
              </div>
            </div>
          </div>
          <div v-if="userSearchQuery.length >= 3 && !userSearchLoading && foundUsers.length === 0" class="mt-1 text-xs text-gray-400">
            Не найдено. Создайте локальный контакт ниже.
          </div>
          <div v-if="inviteSent" class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">
            Приглашение отправлено!
          </div>
        </div>

        <form @submit.prevent="save" class="space-y-3">
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Имя {{ isLinked ? '(из профиля пользователя)' : '' }}</label>
            <input v-model="form.name" required placeholder="Имя" :disabled="isLinked" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-60" />
          </div>
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Email</label>
            <input v-model="form.email" type="email" placeholder="Email" :disabled="isLinked" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-60" />
          </div>
          <div>
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Телефон</label>
            <input v-model="form.phone" placeholder="Телефон" :disabled="isLinked" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-60" />
          </div>

          <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-3">
            <div class="flex items-center gap-2">
              <input id="fav" type="checkbox" v-model="form.is_favorite" class="rounded" />
              <label for="fav" class="text-sm text-gray-700 dark:text-gray-300">⭐ Избранный</label>
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Тип (по «Нетворкингу для разведчиков»)</label>
              <select v-model="form.contact_type" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="regular">— обычный</option>
                <option value="connector">Коннектор (широкий круг связей)</option>
                <option value="condenser">Конденсатор (эксперт с влиянием)</option>
                <option value="bridge">Мостик (доступ к конденсаторам)</option>
              </select>
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Специализация / сфера</label>
              <input v-model="form.specialization" placeholder="например: венчурные инвестиции, AI, госсектор" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Доп. телефон</label>
              <input v-model="form.personal_phone" placeholder="личный/второй телефон" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Доп. email</label>
              <input v-model="form.personal_email" type="email" placeholder="личный email" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Мессенджеры</label>
              <div class="grid grid-cols-2 gap-2">
                <input v-model="messengersDraft.telegram" placeholder="Telegram @..." class="px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                <input v-model="messengersDraft.whatsapp" placeholder="WhatsApp" class="px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                <input v-model="messengersDraft.signal" placeholder="Signal" class="px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                <input v-model="messengersDraft.other" placeholder="Другое" class="px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
              </div>
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Адрес</label>
              <input v-model="form.address" placeholder="город / адрес" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
            </div>

            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Заметки</label>
              <textarea v-model="form.notes" placeholder="Контекст знакомства, интересы, чем могу помочь и чем помог он, общие знакомые..." rows="4" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="closeModal" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
              Отмена
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded disabled:opacity-50">
              {{ saving ? 'Сохранение...' : 'Сохранить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useConfirmStore } from '@/stores/confirm'

const confirmStore = useConfirmStore()

const contacts = ref([])
const loading = ref(false)
const showModal = ref(false)
const saving = ref(false)
const editingContact = ref(null)

// User search & invites
const userSearchQuery = ref('')
const userSearchLoading = ref(false)
const foundUsers = ref([])
const inviteSent = ref(false)
const pendingInvites = ref([])
let searchTimeout = null

const emptyForm = () => ({
  id: null,
  contact_user_id: null,
  name: '',
  email: '',
  phone: '',
  notes: '',
  is_favorite: false,
  contact_type: 'regular',
  specialization: '',
  personal_phone: '',
  personal_email: '',
  address: '',
})

const form = ref(emptyForm())
const messengersDraft = ref({ telegram: '', whatsapp: '', signal: '', other: '' })

const filters = ref({
  search: '',
  favoritesOnly: false,
  type: '',
  link: '',
  activity: '',
  sort: 'favorite',
})

const hasActiveFilters = computed(() =>
  filters.value.search
  || filters.value.favoritesOnly
  || filters.value.type
  || filters.value.link
  || filters.value.activity
  || filters.value.sort !== 'favorite'
)

const resetFilters = () => {
  filters.value = { search: '', favoritesOnly: false, type: '', link: '', activity: '', sort: 'favorite' }
}

const typeOrder = { connector: 1, condenser: 2, bridge: 3, regular: 4 }

const filteredContacts = computed(() => {
  const q = filters.value.search.trim().toLowerCase()
  let list = contacts.value.filter(c => {
    if (filters.value.favoritesOnly && !c.is_favorite) return false
    if (filters.value.type && (c.contact_type || 'regular') !== filters.value.type) return false
    if (filters.value.link === 'linked' && !c.contact_user_id) return false
    if (filters.value.link === 'external' && c.contact_user_id) return false
    if (filters.value.activity === 'active' && !c.active_tasks_count) return false
    if (filters.value.activity === 'none' && (c.active_tasks_count || c.completed_tasks_count)) return false
    if (q) {
      const hay = [c.name, c.email, c.phone, c.specialization, c.address, c.notes,
                   c.personal_email, c.personal_phone,
                   ...(c.messengers ? Object.values(c.messengers) : [])]
        .filter(Boolean).join(' ').toLowerCase()
      if (!hay.includes(q)) return false
    }
    return true
  })

  const byName = (a, b) => (a.name || '').localeCompare(b.name || '')
  const sort = filters.value.sort
  list = [...list]
  if (sort === 'name') list.sort(byName)
  else if (sort === 'active_tasks') list.sort((a, b) => (b.active_tasks_count || 0) - (a.active_tasks_count || 0) || byName(a, b))
  else if (sort === 'type') list.sort((a, b) => (typeOrder[a.contact_type || 'regular'] - typeOrder[b.contact_type || 'regular']) || byName(a, b))
  else list.sort((a, b) => ((b.is_favorite ? 1 : 0) - (a.is_favorite ? 1 : 0)) || byName(a, b))
  return list
})

const isLinked = computed(() => !!form.value.contact_user_id)

const typeLabel = (t) => ({
  connector: 'Коннектор',
  condenser: 'Конденсатор',
  bridge: 'Мостик',
}[t] || '')

const typeBadgeClass = (t) => ({
  connector: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
  condenser: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  bridge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
}[t] || 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300')

const load = async () => {
  loading.value = true
  try {
    const res = await api.get('/v1/contacts')
    contacts.value = res.data || []
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  form.value = emptyForm()
  messengersDraft.value = { telegram: '', whatsapp: '', signal: '', other: '' }
  userSearchQuery.value = ''
  foundUsers.value = []
  inviteSent.value = false
  showModal.value = true
}

const openEdit = (contact) => {
  form.value = {
    id: contact.id,
    contact_user_id: contact.contact_user_id,
    name: contact.name || '',
    email: contact.email || '',
    phone: contact.phone || '',
    notes: contact.notes || '',
    is_favorite: !!contact.is_favorite,
    contact_type: contact.contact_type || 'regular',
    specialization: contact.specialization || '',
    personal_phone: contact.personal_phone || '',
    personal_email: contact.personal_email || '',
    address: contact.address || '',
  }
  const m = contact.messengers || {}
  messengersDraft.value = {
    telegram: m.telegram || '',
    whatsapp: m.whatsapp || '',
    signal: m.signal || '',
    other: m.other || '',
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const buildMessengers = () => {
  const out = {}
  for (const [k, v] of Object.entries(messengersDraft.value)) {
    if (v && v.trim()) out[k] = v.trim()
  }
  return Object.keys(out).length ? out : null
}

const save = async () => {
  saving.value = true
  try {
    const payload = {
      notes: form.value.notes || null,
      is_favorite: form.value.is_favorite,
      contact_type: form.value.contact_type,
      specialization: form.value.specialization || null,
      personal_phone: form.value.personal_phone || null,
      personal_email: form.value.personal_email || null,
      address: form.value.address || null,
      messengers: buildMessengers(),
    }
    if (!isLinked.value) {
      payload.name = form.value.name
      payload.email = form.value.email || null
      payload.phone = form.value.phone || null
    }
    if (form.value.id) {
      await api.put(`/v1/contacts/${form.value.id}`, payload)
    } else {
      await api.post('/v1/contacts', payload)
    }
    closeModal()
    await load()
  } finally {
    saving.value = false
  }
}

const toggleFavorite = async (contact) => {
  await api.put(`/v1/contacts/${contact.id}`, { is_favorite: !contact.is_favorite })
  contact.is_favorite = !contact.is_favorite
  contacts.value.sort((a, b) => (b.is_favorite - a.is_favorite) || a.name.localeCompare(b.name))
}

const remove = async (contact) => {
  const ok = await confirmStore.ask({
    title: 'Удалить контакт?',
    message: contact.name,
    confirmText: 'Удалить',
  })
  if (!ok) return
  await api.delete(`/v1/contacts/${contact.id}`)
  await load()
}

const searchSystemUsers = () => {
  clearTimeout(searchTimeout)
  inviteSent.value = false
  const q = userSearchQuery.value.trim()
  if (q.length < 3) { foundUsers.value = []; userSearchLoading.value = false; return }
  userSearchLoading.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const res = await api.get('/v1/contacts-search-users', { params: { query: q } })
      foundUsers.value = Array.isArray(res) ? res : (res.data || res || [])
    } catch (e) {
      foundUsers.value = []
      console.error('User search error:', e)
    } finally {
      userSearchLoading.value = false
    }
  }, 400)
}

const sendInviteToUser = async (user) => {
  // First create a local contact, then send invite
  try {
    const contactRes = await api.post('/v1/contacts', {
      name: user.name,
      email: user.email,
    })
    const contactId = contactRes.data.id
    await api.post('/v1/contacts-invite', {
      contact_id: contactId,
      receiver_id: user.id,
    })
    user.pending_invite = true
    inviteSent.value = true
    await load()
  } catch (e) {
    console.error('Invite error', e)
    alert(e.response?.data?.message || 'Ошибка отправки приглашения')
  }
}

const loadPendingInvites = async () => {
  try {
    const res = await api.get('/v1/contacts-invites')
    pendingInvites.value = Array.isArray(res) ? res : (res.data || [])
  } catch (e) { pendingInvites.value = [] }
}

const respondToInvite = async (inviteId, action) => {
  try {
    await api.post(`/v1/contacts-invites/${inviteId}/respond`, { action })
    await Promise.all([loadPendingInvites(), load()])
  } catch (e) {
    alert(e.response?.data?.message || 'Ошибка')
  }
}

const openCreateClean = openCreate
const originalOpenCreate = openCreate

onMounted(() => {
  load()
  loadPendingInvites()
})
</script>
