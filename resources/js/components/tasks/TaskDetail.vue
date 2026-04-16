<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-start lg:items-center justify-center p-0 lg:p-6 bg-black/40 backdrop-blur-sm overflow-y-auto"
        @click.self="handleClose"
      >
        <div class="bg-white dark:bg-gray-900 w-full max-w-6xl min-h-screen lg:min-h-0 lg:rounded-xl lg:shadow-2xl overflow-visible flex flex-col lg:max-h-[94vh]">
          <!-- Header -->
          <div class="flex items-center justify-between px-5 py-2 border-b border-gray-100 dark:border-gray-800 gap-3">
            <div class="flex items-center gap-2 text-xs text-gray-400 flex-shrink-0">
              <span>#{{ task?.id }}</span>
              <span v-if="creatorName" class="hidden md:inline">
                от <span class="text-gray-600 dark:text-gray-300">{{ creatorName }}</span>
              </span>
            </div>

            <!-- Meta chips with popovers -->
            <div class="flex items-center gap-1.5 flex-1 min-w-0 overflow-visible" ref="chipsRef">
              <!-- Date -->
              <div class="relative">
                <Chip v-if="localTask.due_date" icon="calendar" :label="formattedDateTime" @click="isGuest || togglePicker('date')" :active="picker === 'date'" />
                <AddInline v-else-if="!isGuest" icon="calendar" label="Дата" @click="togglePicker('date')" />
                <div v-if="picker === 'date' && !isGuest" class="absolute top-full left-0 mt-1 z-20 p-3 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 flex items-center gap-2 whitespace-nowrap">
                  <input v-model="localTask.due_date" @change="onDueDateChange" type="date" class="px-2 py-1 text-sm bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700" />
                  <input v-model="localTask.estimated_time" @change="scheduleSave" type="time" class="px-2 py-1 text-sm bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700" />
                  <button v-if="localTask.due_date" @click="clearDate" class="text-xs text-gray-400 hover:text-red-500">Очистить</button>
                </div>
              </div>

              <!-- Status -->
              <div class="relative">
                <Chip icon="flag" :label="statusLabel" @click="isGuest || togglePicker('status')" :active="picker === 'status'" />
                <div v-if="picker === 'status' && !isGuest" class="absolute top-full left-0 mt-1 z-20 p-1 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 min-w-[160px]">
                  <button
                    v-for="s in statusOptions" :key="s.value"
                    @click="setField('status', s.value); picker = null"
                    class="w-full text-left px-2.5 py-1 text-sm rounded transition-colors"
                    :class="localTask.status === s.value ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
                  >{{ s.label }}</button>
                </div>
              </div>

              <!-- Priority -->
              <div class="relative">
                <Chip :dot-class="priorityDot" :label="priorityLabel" @click="isGuest || togglePicker('priority')" :active="picker === 'priority'" :label-class="priorityLabelClass" />
                <div v-if="picker === 'priority' && !isGuest" class="absolute top-full left-0 mt-1 z-20 p-1 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 min-w-[140px]">
                  <button
                    v-for="p in priorityOptions" :key="p.value"
                    @click="setField('priority', p.value); picker = null"
                    class="w-full text-left px-2.5 py-1 text-sm rounded transition-colors flex items-center gap-2"
                    :class="localTask.priority === p.value ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="p.dotClass"></span>
                    {{ p.label }}
                  </button>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
              <button
                v-if="!isGuest && !localTask.completed_at"
                @click="handleComplete"
                class="px-2.5 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-md flex items-center gap-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Завершить
              </button>
              <button
                v-else-if="!isGuest && localTask.completed_at"
                @click="handleUncomplete"
                class="px-2.5 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md"
              >
                Вернуть в работу
              </button>
              <span class="h-5 w-px bg-gray-200 dark:bg-gray-700"></span>
              <button @click="handleClose" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" title="Закрыть">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>
          </div>

          <div class="flex-1 overflow-y-auto thin-scroll min-h-0">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px]">
              <!-- Main column -->
              <div class="px-6 py-5 space-y-4 border-r border-gray-100 dark:border-gray-800 min-w-0">
                <!-- Title -->
                <input
                  v-model="localTask.title"
                  @input="scheduleSave"
                  type="text"
                  placeholder="Название задачи"
                  :readonly="isGuest"
                  :class="[localTask.completed_at ? 'line-through text-gray-400' : 'text-gray-900 dark:text-white']"
                  class="w-full bg-transparent border-0 outline-none text-2xl font-semibold placeholder-gray-300 dark:placeholder-gray-600 px-0 leading-tight"
                />

                <!-- Description -->
                <div>
                  <FieldLabel icon="align-left">Описание</FieldLabel>
                  <textarea
                    v-model="localTask.description"
                    @input="scheduleSave"
                    :readonly="isGuest"
                    placeholder="Добавьте описание…"
                    class="w-full bg-transparent border-0 outline-none text-[15px] text-gray-700 dark:text-gray-300 placeholder-gray-300 dark:placeholder-gray-600 leading-relaxed thin-scroll resize-none"
                    style="min-height: 72px; max-height: 240px;"
                    ref="descRef"
                  ></textarea>
                </div>


                <!-- Checklist -->
                <div>
                  <div class="flex items-center gap-2 mb-1">
                    <FieldLabel icon="check-square">
                      Чек-лист
                      <span v-if="localTask.checklist_items?.length" class="text-gray-400 font-normal ml-1">{{ checklistDoneCount }}/{{ localTask.checklist_items.length }}</span>
                    </FieldLabel>
                    <div v-if="localTask.checklist_items?.length" class="flex-1 h-0.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                      <div class="h-full bg-emerald-500 transition-all" :style="{ width: checklistProgress + '%' }"></div>
                    </div>
                  </div>
                  <div class="overflow-y-auto thin-scroll space-y-0.5" style="max-height: 260px;">
                    <div
                      v-for="(item, index) in localTask.checklist_items"
                      :key="item.id ?? `new-${index}`"
                      class="flex items-center gap-2 group px-1 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    >
                      <input type="checkbox" v-model="item.is_done" @change="scheduleSave" :disabled="isGuest"
                        class="w-3.5 h-3.5 rounded border-gray-300 dark:border-gray-600 text-emerald-500 focus:ring-0 focus:ring-offset-0 disabled:opacity-60" />
                      <input
                        v-model="item.text"
                        @input="scheduleSave"
                        @keydown.enter.prevent="addChecklistItem"
                        type="text"
                        :readonly="isGuest"
                        :class="item.is_done ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'"
                        class="flex-1 bg-transparent border-0 outline-none text-sm placeholder-gray-300"
                      />
                      <button v-if="!isGuest" @click="removeChecklistItem(index)" class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                      </button>
                    </div>
                  </div>
                  <button v-if="!isGuest" @click="addChecklistItem" class="mt-1 px-1 py-1 text-xs text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Добавить пункт
                  </button>
                </div>

                <!-- Обсуждение -->
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                  <FieldLabel icon="chat">Обсуждение</FieldLabel>
                  <div v-if="comments.length" class="space-y-3 mb-3 overflow-y-auto thin-scroll pr-1" style="max-height: 320px;">
                    <div v-for="c in comments" :key="c.id" class="flex gap-2">
                      <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex-shrink-0 flex items-center justify-center text-[11px] font-medium text-gray-600 dark:text-gray-300">
                        {{ initials(c.user?.name) }}
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-baseline gap-2">
                          <span class="text-[13px] font-medium text-gray-800 dark:text-gray-200">{{ c.user?.name }}</span>
                          <span class="text-[11px] text-gray-400">{{ formatDate(c.created_at) }}</span>
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ c.content }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <textarea
                      v-model="newComment"
                      @keydown.enter.prevent.meta="submitComment"
                      @keydown.enter.prevent.ctrl="submitComment"
                      placeholder="Написать комментарий… (Ctrl/Cmd+Enter — отправить)"
                      rows="2"
                      class="flex-1 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600 resize-none"
                    ></textarea>
                    <button
                      @click="submitComment"
                      :disabled="!newComment.trim() || postingComment"
                      class="px-3 py-2 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded-lg disabled:opacity-40 hover:opacity-90 self-end"
                    >
                      Отправить
                    </button>
                  </div>
                </div>

              </div>

              <!-- Side panel (inline rows, empty = ghost) -->
              <div class="px-5 py-5 space-y-1 bg-gray-50/60 dark:bg-gray-800/30 text-[13px]">
                <template v-if="!isGuest">
                  <SelectRow
                    icon="folder"
                    placeholder="Проект"
                    :items="projects"
                    :value="localTask.project_id"
                    :open="picker === 'project'"
                    @toggle="togglePicker('project')"
                    @update:value="(v) => { localTask.project_id = v; scheduleSave() }"
                  />
                  <SelectRow
                    icon="target"
                    placeholder="Цель"
                    :items="goals"
                    :value="localTask.goal_id"
                    :open="picker === 'goal'"
                    @toggle="togglePicker('goal')"
                    @update:value="(v) => { localTask.goal_id = v; scheduleSave() }"
                  />
                  <SelectRow
                    icon="sparkles"
                    placeholder="Сфера"
                    :items="lifeSpheres"
                    :value="localTask.life_sphere_id"
                    :open="picker === 'sphere'"
                    @toggle="togglePicker('sphere')"
                    @update:value="(v) => { localTask.life_sphere_id = v; scheduleSave() }"
                  />
                  <SelectRow
                    icon="map-pin"
                    placeholder="Контекст"
                    :items="contexts"
                    :value="localTask.context_id"
                    :open="picker === 'context'"
                    @toggle="togglePicker('context')"
                    @update:value="(v) => { localTask.context_id = v; scheduleSave() }"
                  />
                </template>

                <div class="space-y-1" :class="isGuest ? '' : 'pt-3 mt-2 border-t border-gray-200 dark:border-gray-700'">
                  <PeopleRow
                    v-if="!isGuest"
                    icon="user"
                    label="Исполнители"
                    :contacts="knownContacts"
                    :selected="assigneeIds"
                    badge-class="bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                    :open="picker === 'assignees'"
                    @toggle="togglePicker('assignees')"
                    @update:selected="updateAssignees"
                  />
                  <PeopleRow
                    icon="eye"
                    label="Наблюдатели"
                    :contacts="knownContacts"
                    :selected="watcherIds"
                    badge-class="bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300"
                    :open="picker === 'watchers' && !isGuest"
                    :no-add="isGuest"
                    :removable-ids="isGuest ? (myWatcherContactId ? [myWatcherContactId] : []) : null"
                    @toggle="isGuest ? null : togglePicker('watchers')"
                    @update:selected="onWatchersChanged"
                  />
                </div>

                <!-- Файлы -->
                <div v-if="!isGuest" class="pt-3 mt-2 border-t border-gray-200 dark:border-gray-700">
                  <div class="flex items-center justify-between mb-1.5">
                    <FieldLabel icon="paperclip">Файлы</FieldLabel>
                    <label class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 cursor-pointer p-0.5" :title="uploading ? 'Загружаю…' : 'Добавить файл'">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      <input type="file" class="hidden" @change="handleFileUpload" :disabled="uploading" multiple />
                    </label>
                  </div>
                  <div v-if="localTask.attachments?.length" class="space-y-1">
                    <div
                      v-for="att in localTask.attachments"
                      :key="att.id"
                      class="group flex items-center gap-2 px-1 py-1 rounded hover:bg-white/60 dark:hover:bg-gray-800/60"
                    >
                      <a :href="attachmentUrl(att)" target="_blank" class="flex-shrink-0 w-7 h-7 rounded overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <img v-if="isImage(att)" :src="attachmentUrl(att)" class="w-full h-full object-cover" />
                        <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="fileIconPath(att)" /></svg>
                      </a>
                      <a :href="attachmentUrl(att)" target="_blank" class="flex-1 min-w-0">
                        <div class="truncate text-[12.5px] text-gray-700 dark:text-gray-300">{{ att.file_name }}</div>
                        <div class="text-[10px] text-gray-400">{{ formatSize(att.file_size) }}</div>
                      </a>
                      <button
                        @click="removeAttachment(att)"
                        class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 flex-shrink-0"
                        title="Удалить"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                      </button>
                    </div>
                  </div>
                  <div v-else class="text-xs text-gray-400">Файлов нет</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer: status + primary action -->
          <div class="px-5 py-2 border-t border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/30 flex items-center justify-between min-h-[40px] text-[11px] text-gray-400">
            <div class="flex items-center gap-1.5">
              <template v-if="saveState === 'saving'">
                <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="30 60" /></svg>
                <span>сохраняю…</span>
              </template>
              <template v-else-if="saveState === 'saved'">
                <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                <span class="text-emerald-500">сохранено</span>
              </template>
              <span v-else-if="saveState === 'error'" class="text-red-500">ошибка сохранения</span>
            </div>
            <span v-if="isGuest" class="ml-auto">режим наблюдателя</span>
            <span v-else-if="localTask.completed_at" class="ml-auto text-emerald-500">✓ задача завершена</span>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onUnmounted, onMounted, onBeforeUnmount, nextTick, h, Teleport } from 'vue'
import api from '@/services/api'
import ContactPicker from '@/components/tasks/ContactPicker.vue'
import { useConfirmStore } from '@/stores/confirm'
import { useTasksStore } from '@/stores/tasks'
import { useDashboardStore } from '@/stores/dashboard'

const confirmStore = useConfirmStore()
const tasksStore = useTasksStore()
const dashboardStore = useDashboardStore()

const props = defineProps({
  show: { type: Boolean, default: false },
  task: { type: Object, default: null },
  projects: { type: Array, default: () => [] },
  goals: { type: Array, default: () => [] },
  lifeSpheres: { type: Array, default: () => [] },
  contacts: { type: Array, default: () => [] },
  contexts: { type: Array, default: () => [] },
  currentUserId: { type: Number, default: null },
  myWorkspaceId: { type: Number, default: null },
})
const emit = defineEmits(['close', 'saved', 'completed', 'uncompleted'])

const ICONS = {
  calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  folder: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
  target: 'M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-4a4 4 0 100 8 4 4 0 000-8z',
  sparkles: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.29 5.29L20 11l-5.29 2.29L12 20l-2.29-5.29L4 11l5.29-2.29L12 3z',
  user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  eye: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.46 12C3.73 7.94 7.52 5 12 5s8.27 2.94 9.54 7c-1.27 4.06-5.06 7-9.54 7S3.73 16.06 2.46 12z',
  'check-square': 'M9 12l2 2 4-4m5-6H4a1 1 0 00-1 1v16a1 1 0 001 1h16a1 1 0 001-1V5a1 1 0 00-1-1z',
  paperclip: 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13',
  flag: 'M3 21V5a2 2 0 012-2h10l-2 4 2 4H5v10',
  'align-left': 'M4 6h16M4 12h10M4 18h16',
  'map-pin': 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
  plus: 'M12 4v16m8-8H4',
  chat: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
}
const Icon = { props: ['name'], setup: (p) => () => h('svg', { class: 'w-3.5 h-3.5', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' },
  [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '1.8', d: ICONS[p.name] || '' })]) }

const FieldLabel = {
  props: ['icon'],
  setup(p, { slots }) {
    return () => h('div', { class: 'flex items-center gap-1.5 mb-1 text-[11px] font-medium uppercase tracking-wider text-gray-400' }, [
      h(Icon, { name: p.icon }),
      slots.default ? slots.default() : null,
    ])
  },
}

const InlineRow = {
  props: ['icon'],
  setup(p, { slots }) {
    return () => h('div', { class: 'flex items-center gap-2.5 py-1.5 px-1 -mx-1 rounded hover:bg-white/60 dark:hover:bg-gray-800/60 transition-colors' }, [
      h('div', { class: 'text-gray-400 flex-shrink-0' }, [h(Icon, { name: p.icon })]),
      h('div', { class: 'flex-1 min-w-0' }, slots.default ? slots.default() : null),
    ])
  },
}

const Chip = {
  props: ['icon', 'label', 'active', 'dotClass', 'labelClass'],
  emits: ['click'],
  setup(p, { emit }) {
    return () => h('button', {
      class: [
        'inline-flex items-center gap-1.5 px-2 py-1 text-[12.5px] rounded-md transition-colors whitespace-nowrap',
        p.active
          ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
          : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 ' + (p.labelClass || 'text-gray-700 dark:text-gray-300'),
      ],
      onClick: () => emit('click'),
    }, [
      p.icon ? h(Icon, { name: p.icon }) : null,
      p.dotClass ? h('span', { class: ['w-1.5 h-1.5 rounded-full', p.dotClass] }) : null,
      h('span', p.label),
    ])
  },
}

const SelectRow = {
  props: ['icon', 'placeholder', 'items', 'value', 'open'],
  emits: ['toggle', 'update:value'],
  setup(p, { emit }) {
    const query = ref('')
    const rootRef = ref(null)
    const popoverPos = ref({ top: 0, left: 0, width: 240 })

    const current = computed(() => (p.items || []).find(i => i.id === p.value))

    const filtered = computed(() => {
      const q = query.value.trim().toLowerCase()
      return (p.items || []).filter(i => !q || (i.name || '').toLowerCase().includes(q)).slice(0, 50)
    })

    const updatePos = () => {
      if (!rootRef.value) return
      const r = rootRef.value.getBoundingClientRect()
      const width = 240
      const height = 260
      let top = r.bottom + 4
      if (top + height > window.innerHeight - 8) top = Math.max(8, r.top - height - 4)
      let left = r.right - width
      if (left < 8) left = 8
      popoverPos.value = { top, left, width }
    }

    watch(() => p.open, (o) => { if (o) nextTick(updatePos) })

    const select = (id) => { emit('update:value', id); emit('toggle'); query.value = '' }

    const handleOutside = (e) => {
      if (p.open && rootRef.value && !rootRef.value.contains(e.target)) emit('toggle')
    }
    onMounted(() => document.addEventListener('click', handleOutside))
    onBeforeUnmount(() => document.removeEventListener('click', handleOutside))

    return () => h('div', { class: 'relative', ref: rootRef }, [
      h('div', {
        class: 'flex items-center gap-2.5 py-1.5 px-1 -mx-1 rounded hover:bg-white/60 dark:hover:bg-gray-800/60 cursor-pointer transition-colors',
        onClick: (e) => { e.stopPropagation(); emit('toggle') },
      }, [
        h('div', { class: 'text-gray-400 flex-shrink-0' }, [h(Icon, { name: p.icon })]),
        current.value
          ? h('span', { class: 'text-gray-800 dark:text-gray-100 font-medium truncate' }, current.value.name)
          : h('span', { class: 'text-gray-400' }, p.placeholder),
      ]),
      p.open ? h(Teleport, { to: 'body' }, [h('div', {
        class: 'fixed z-[60] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-1 max-h-64 overflow-y-auto thin-scroll',
        style: { top: popoverPos.value.top + 'px', left: popoverPos.value.left + 'px', width: popoverPos.value.width + 'px' },
        onClick: (e) => e.stopPropagation(),
      }, [
        h('button', {
          class: ['w-full text-left px-2.5 py-1 text-sm rounded transition-colors',
            p.value == null ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'],
          onClick: () => select(null),
        }, '— не выбрано'),
        ...(p.items || []).map(i => h('button', {
          class: ['w-full text-left px-2.5 py-1 text-sm rounded transition-colors truncate block',
            p.value === i.id ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'],
          onClick: () => select(i.id),
        }, i.name)),
      ])]) : null,
    ])
  },
}

const PeopleRow = {
  props: ['icon', 'label', 'contacts', 'selected', 'badgeClass', 'open', 'noAdd', 'removableIds'],
  emits: ['toggle', 'update:selected'],
  setup(p, { emit }) {
    const query = ref('')
    const rootRef = ref(null)
    const popoverPos = ref({ top: 0, left: 0 })

    const updatePos = () => {
      if (!rootRef.value) return
      const r = rootRef.value.getBoundingClientRect()
      const width = 240
      const height = 260
      let top = r.bottom + 4
      if (top + height > window.innerHeight - 8) top = Math.max(8, r.top - height - 4)
      let left = r.right - width
      if (left < 8) left = 8
      popoverPos.value = { top, left, width }
    }

    watch(() => p.open, (o) => { if (o) nextTick(updatePos) })

    const contactName = (id) => p.contacts.find(c => c.id === id)?.name || '—'
    const selectedContacts = computed(() => (p.selected || []).map(id => ({ id, name: contactName(id) })))
    const filtered = computed(() => {
      const q = query.value.trim().toLowerCase()
      return p.contacts
        .filter(c => !p.selected?.includes(c.id))
        .filter(c => !q || (c.name || '').toLowerCase().includes(q) || (c.specialization || '').toLowerCase().includes(q))
        .slice(0, 40)
    })
    const add = (id) => { emit('update:selected', [...(p.selected || []), id]); query.value = '' }
    const remove = (id) => emit('update:selected', (p.selected || []).filter(x => x !== id))

    const handleOutside = (e) => {
      if (p.open && rootRef.value && !rootRef.value.contains(e.target)) emit('toggle')
    }
    onMounted(() => document.addEventListener('click', handleOutside))
    onBeforeUnmount(() => document.removeEventListener('click', handleOutside))

    return () => h('div', { class: 'relative', ref: rootRef }, [
      h('div', {
        class: ['flex items-center gap-2 py-1.5 px-1 -mx-1 rounded transition-colors',
          p.noAdd ? '' : 'hover:bg-white/60 dark:hover:bg-gray-800/60 cursor-pointer'],
        onClick: (e) => { if (p.noAdd) return; e.stopPropagation(); emit('toggle') },
      }, [
        h('div', { class: 'text-gray-400 flex-shrink-0' }, [h(Icon, { name: p.icon })]),
        selectedContacts.value.length
          ? h('div', { class: 'flex flex-wrap gap-1 flex-1 min-w-0' },
              selectedContacts.value.map(c => {
                const canRemove = p.removableIds == null || p.removableIds.includes(c.id)
                return h('span', {
                  class: `inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11.5px] ${p.badgeClass}`,
                }, [
                  c.name,
                  canRemove ? h('button', {
                    class: 'hover:text-red-500 -mr-0.5',
                    onClick: (e) => { e.stopPropagation(); remove(c.id) },
                  }, '✕') : null,
                ])
              }))
          : h('span', { class: 'text-gray-400 text-[13px]' }, p.label),
      ]),
      p.open ? h(Teleport, { to: 'body' }, [h('div', {
        class: 'fixed z-[60] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-1',
        style: { top: popoverPos.value.top + 'px', left: popoverPos.value.left + 'px', width: popoverPos.value.width + 'px' },
        onClick: (e) => e.stopPropagation(),
      }, [
        h('input', {
          value: query.value,
          onInput: (e) => query.value = e.target.value,
          type: 'text',
          placeholder: 'Поиск…',
          class: 'w-full px-2.5 py-1 text-sm bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 mb-1 focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600',
        }),
        filtered.value.length
          ? h('div', { class: 'max-h-52 overflow-y-auto' },
              filtered.value.map(c => h('button', {
                class: 'w-full text-left px-2.5 py-1 text-sm rounded transition-colors text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-between',
                onClick: () => add(c.id),
              }, [
                h('span', { class: 'truncate' }, c.name),
                c.specialization ? h('span', { class: 'ml-2 text-[11px] text-gray-400 truncate' }, c.specialization) : null,
              ])))
          : h('div', { class: 'px-2.5 py-2 text-sm text-gray-400 text-center' }, 'Никого не найдено'),
      ])]) : null,
    ])
  },
}

const AddInline = {
  props: ['icon', 'label'],
  emits: ['click'],
  setup(p, { emit }) {
    return () => h('button', {
      class: 'inline-flex items-center gap-1 px-2 py-0.5 text-xs text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800',
      onClick: () => emit('click'),
    }, [
      h(Icon, { name: p.icon }),
      h('span', p.label),
    ])
  },
}

const statusOptions = [
  { value: 'inbox', label: 'Входящая' },
  { value: 'today', label: 'Сегодня' },
  { value: 'tomorrow', label: 'Завтра' },
  { value: 'someday', label: 'Когда-нибудь' },
]

const priorityOptions = [
  { value: 'low',    label: 'Низкий',  dotClass: 'bg-gray-400' },
  { value: 'medium', label: 'Средний', dotClass: 'bg-blue-400' },
  { value: 'high',   label: 'Высокий', dotClass: 'bg-amber-500' },
  { value: 'urgent', label: 'Срочный', dotClass: 'bg-red-500' },
]

const localTask = ref({})
const assigneeIds = ref([])
const watcherIds = ref([])
const saveState = ref('idle')
const uploading = ref(false)
const picker = ref(null)
const openSections = ref({ description: false })
const descRef = ref(null)
const chipsRef = ref(null)
const comments = ref([])
const newComment = ref('')
const showCommentInput = ref(false)
const postingComment = ref(false)

const creatorName = computed(() => localTask.value?.creator?.name || null)

// Задача «чужая» (я только наблюдатель): workspace_id задачи != моему дефолтному.
// В таком режиме — почти всё read-only, доступны только комментирование и выход из наблюдателей.
const isGuest = computed(() => {
  const tWs = localTask.value?.workspace_id
  return tWs != null && props.myWorkspaceId != null && tWs !== props.myWorkspaceId
})

// Мой собственный контакт, фигурирующий в watchers (для кнопки «выйти из наблюдателей»).
const myWatcherContactId = computed(() => {
  if (!props.currentUserId) return null
  const mine = (localTask.value?.watchers || []).find(c => c.contact_user_id === props.currentUserId)
  return mine?.id || null
})

// Контакты для отображения в исполнителях/наблюдателях: свои + уже привязанные к задаче
// (чтобы имена людей, чьи контакты нам не принадлежат, всё равно показывались).
const knownContacts = computed(() => {
  const map = new Map()
  for (const c of props.contacts || []) map.set(c.id, c)
  for (const c of localTask.value?.assignees || []) if (!map.has(c.id)) map.set(c.id, c)
  for (const c of localTask.value?.watchers || []) if (!map.has(c.id)) map.set(c.id, c)
  return Array.from(map.values())
})

const initials = (name) => {
  if (!name) return '?'
  return name.trim().split(/\s+/).slice(0, 2).map(w => w[0]?.toUpperCase() || '').join('')
}
const formatDate = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const loadComments = async () => {
  if (!localTask.value?.id) return
  try {
    const res = await api.get(`/v1/tasks/${localTask.value.id}/comments`)
    comments.value = res.data || []
  } catch (e) { comments.value = [] }
}

const submitComment = async () => {
  const text = newComment.value.trim()
  if (!text || !localTask.value.id) return
  postingComment.value = true
  try {
    const res = await api.post(`/v1/tasks/${localTask.value.id}/comments`, { content: text })
    comments.value.push(res.data)
    newComment.value = ''
  } catch (e) {
    console.error('comment post error', e)
  } finally {
    postingComment.value = false
  }
}
let saveTimer = null

const handleOutsideClick = (e) => {
  if (picker.value && chipsRef.value && !chipsRef.value.contains(e.target)) {
    picker.value = null
  }
}
onMounted(() => document.addEventListener('click', handleOutsideClick))
onBeforeUnmount(() => document.removeEventListener('click', handleOutsideClick))

const handleEscape = (e) => {
  if (e.key !== 'Escape' || !props.show) return
  // если открыт inline-picker — сначала закрываем его
  if (picker.value) { picker.value = null; return }
  handleClose()
}
onMounted(() => document.addEventListener('keydown', handleEscape))
onBeforeUnmount(() => document.removeEventListener('keydown', handleEscape))

const statusLabel = computed(() => statusOptions.find(s => s.value === localTask.value.status)?.label || 'Статус')
const priorityLabel = computed(() => priorityOptions.find(p => p.value === localTask.value.priority)?.label || 'Приоритет')
const priorityDot = computed(() => priorityOptions.find(p => p.value === localTask.value.priority)?.dotClass)
const priorityLabelClass = computed(() => ({
  low: 'text-gray-600 dark:text-gray-300',
  medium: 'text-blue-700 dark:text-blue-300',
  high: 'text-amber-700 dark:text-amber-300',
  urgent: 'text-red-700 dark:text-red-300',
}[localTask.value.priority] || 'text-gray-600 dark:text-gray-300'))

const formattedDateTime = computed(() => {
  if (!localTask.value.due_date) return ''
  const d = new Date(localTask.value.due_date + 'T00:00:00')
  const dateStr = d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
  const time = localTask.value.estimated_time
  return time ? `${dateStr} · ${time}` : dateStr
})

const checklistDoneCount = computed(() => (localTask.value.checklist_items || []).filter(i => i.is_done).length)
const checklistProgress = computed(() => {
  const total = (localTask.value.checklist_items || []).length
  return total ? Math.round((checklistDoneCount.value / total) * 100) : 0
})

const normalizeTime = (v) => (v ? String(v).slice(0, 5) : '')
const normalizeDate = (v) => (v ? String(v).slice(0, 10) : '')

watch(() => props.task, (t) => {
  if (!t) return
  localTask.value = {
    ...t,
    description: t.description || '',
    due_date: normalizeDate(t.due_date),
    estimated_time: normalizeTime(t.estimated_time),
    end_time: normalizeTime(t.end_time),
    checklist_items: (t.checklist_items || []).map(i => ({ ...i })),
    attachments: [...(t.attachments || [])],
  }
  assigneeIds.value = (t.assignees || []).map(c => c.id)
  watcherIds.value = (t.watchers || []).map(c => c.id)
  saveState.value = 'idle'
  picker.value = null
  openSections.value = { description: false }
  showCommentInput.value = false
  newComment.value = ''
  comments.value = (t.comments || []).slice().sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
  if (t.id) loadComments()
}, { immediate: true })

const togglePicker = (name) => { picker.value = picker.value === name ? null : name }
const setField = (key, value) => {
  localTask.value[key] = value
  if (key === 'status') {
    if (value === 'today') {
      localTask.value.due_date = new Date().toISOString().slice(0, 10)
    } else if (value === 'tomorrow') {
      const d = new Date(); d.setDate(d.getDate() + 1)
      localTask.value.due_date = d.toISOString().slice(0, 10)
    } else if (value === 'inbox' || value === 'someday') {
      // Входящие и «Когда-нибудь» не могут иметь дату выполнения
      localTask.value.due_date = null
      localTask.value.estimated_time = null
    }
  }
  scheduleSave()
}
const clearDate = () => {
  localTask.value.due_date = null
  localTask.value.estimated_time = null
  if (['today', 'tomorrow', 'scheduled'].includes(localTask.value.status)) {
    localTask.value.status = 'inbox'
  }
  picker.value = null
  scheduleSave()
}

const onDueDateChange = () => {
  const d = localTask.value.due_date
  if (!d) { scheduleSave(); return }
  const today = new Date(); today.setHours(0,0,0,0)
  const tomorrow = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1)
  const picked = new Date(d + 'T00:00:00')
  const iso = (dt) => dt.toISOString().slice(0, 10)
  if (iso(picked) === iso(today)) localTask.value.status = 'today'
  else if (iso(picked) === iso(tomorrow)) localTask.value.status = 'tomorrow'
  else localTask.value.status = 'scheduled'
  scheduleSave()
}

const scheduleSave = () => {
  if (isGuest.value) return
  if (saveTimer) clearTimeout(saveTimer)
  saveState.value = 'saving'
  saveTimer = setTimeout(save, 600)
}

const onWatchersChanged = async (ids) => {
  if (isGuest.value) {
    // guest может только удалить себя — с подтверждением, save напрямую
    const ok = await confirmStore.ask({
      title: 'Выйти из наблюдателей?',
      message: 'Вы больше не будете получать уведомления по этой задаче.',
      confirmText: 'Выйти',
    })
    if (!ok) return
    try {
      await api.put(`/v1/tasks/${localTask.value.id}`, {
        assignee_ids: assigneeIds.value,
        watcher_ids: ids,
      })
      watcherIds.value = ids
      // Перечитываем списки — задача, где я больше не наблюдатель, пропадает
      tasksStore.fetchAllTasks?.({ force: true })
      dashboardStore.fetchAll?.(true)
      emit('close')
    } catch (e) { console.error(e) }
    return
  }
  updateWatchers(ids)
}
const updateAssignees = (ids) => { assigneeIds.value = ids; scheduleSave() }
const updateWatchers = (ids) => { watcherIds.value = ids; scheduleSave() }

const addChecklistItem = () => {
  localTask.value.checklist_items ||= []
  localTask.value.checklist_items.push({ id: null, text: '', is_done: false, position: localTask.value.checklist_items.length })
}
const removeChecklistItem = (index) => { localTask.value.checklist_items.splice(index, 1); scheduleSave() }

const save = async () => {
  if (!localTask.value.id) return
  try {
    const payload = {
      title: localTask.value.title,
      description: localTask.value.description || null,
      status: localTask.value.status,
      priority: localTask.value.priority,
      project_id: localTask.value.project_id || null,
      goal_id: localTask.value.goal_id || null,
      life_sphere_id: localTask.value.life_sphere_id || null,
      context_id: localTask.value.context_id || null,
      due_date: localTask.value.due_date || null,
      estimated_time: localTask.value.estimated_time || null,
      end_time: localTask.value.end_time || null,
      assignee_ids: assigneeIds.value,
      watcher_ids: watcherIds.value,
      checklist: (localTask.value.checklist_items || [])
        .filter(i => i.text && i.text.trim())
        .map((i, idx) => ({
          id: i.id || null,
          text: i.text.trim(),
          is_done: !!i.is_done,
          position: idx,
        })),
    }
    const res = await api.put(`/v1/tasks/${localTask.value.id}`, payload)
    if (res.data?.checklist_items) {
      localTask.value.checklist_items = res.data.checklist_items.map(i => ({ ...i }))
    }
    // Синхронизируем глобальный стор, чтобы изменения сразу появились в списках, проектах, целях
    tasksStore.upsertTask?.(res.data)
    // Обновляем дашборд (тихо — без спиннера)
    dashboardStore.fetchAll?.(true)
    saveState.value = 'saved'
    emit('saved', res.data)
    setTimeout(() => { if (saveState.value === 'saved') saveState.value = 'idle' }, 1500)
  } catch (e) {
    saveState.value = 'error'
    console.error('Task save error', e)
  }
}

const handleClose = async () => {
  if (saveTimer) { clearTimeout(saveTimer); await save() }
  emit('close')
}

const handleComplete = async () => {
  const ok = await confirmStore.ask({
    title: 'Завершить задачу?',
    message: localTask.value.title || 'Отметить задачу выполненной',
    confirmText: 'Завершить',
    danger: false,
  })
  if (!ok) return
  await api.post(`/v1/tasks/${localTask.value.id}/complete`)
  emit('completed', localTask.value)
  emit('close')
}
const handleUncomplete = async () => {
  const ok = await confirmStore.ask({
    title: 'Вернуть задачу в работу?',
    message: localTask.value.title || '',
    confirmText: 'Вернуть',
    danger: false,
  })
  if (!ok) return
  await api.post(`/v1/tasks/${localTask.value.id}/uncomplete`)
  emit('uncompleted', localTask.value)
  emit('close')
}

const isImage = (a) => (a.mime_type || '').startsWith('image/')
const fileIconPath = (a) => {
  const m = a.mime_type || ''
  if (m.startsWith('video/')) return 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'
  if (m.startsWith('audio/')) return 'M9 19V6l12-3v13M9 19c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3zm12-3c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z'
  if (m.includes('pdf')) return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
  if (m.includes('zip') || m.includes('compressed')) return 'M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l2-3h12l2 3M4 7h16m-8 4v6m-3-3h6'
  return 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
}
const attachmentUrl = (a) => `/api/v1/attachments/${a.id}/download`
const formatSize = (b) => {
  if (!b) return ''
  if (b < 1024) return b + ' B'
  if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB'
  return (b / 1024 / 1024).toFixed(1) + ' MB'
}

const handleFileUpload = async (e) => {
  const files = Array.from(e.target.files || [])
  if (!files.length || !localTask.value.id) return
  uploading.value = true
  try {
    for (const file of files) {
      const fd = new FormData()
      fd.append('file', file)
      const res = await api.post(`/v1/tasks/${localTask.value.id}/attachments`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      localTask.value.attachments.push(res.data)
    }
  } finally {
    uploading.value = false
    e.target.value = ''
  }
}

const removeAttachment = async (att) => {
  const ok = await confirmStore.ask({
    title: 'Удалить файл?',
    message: att.file_name,
    confirmText: 'Удалить',
  })
  if (!ok) return
  await api.delete(`/v1/attachments/${att.id}`)
  localTask.value.attachments = localTask.value.attachments.filter(a => a.id !== att.id)
}

onUnmounted(() => { if (saveTimer) clearTimeout(saveTimer) })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.thin-scroll {
  scrollbar-width: thin;
  scrollbar-color: rgba(156, 163, 175, 0.35) transparent;
}
.thin-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
.thin-scroll::-webkit-scrollbar-track { background: transparent; }
.thin-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(156, 163, 175, 0.25);
  border-radius: 3px;
}
.thin-scroll::-webkit-scrollbar-thumb:hover { background-color: rgba(156, 163, 175, 0.5); }

.inline-select {
  @apply w-full bg-transparent border-0 outline-none text-sm text-gray-800 dark:text-gray-200 py-0 px-0 rounded appearance-none cursor-pointer;
  background-image: none;
}
.inline-select:focus { @apply ring-0; }
</style>
