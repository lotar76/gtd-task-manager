<template>
  <div>
    <div class="p-4 lg:p-8">
      <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-6">
        <!-- LEFT: основное содержимое -->
        <div class="min-w-0 space-y-6">
          <!-- Картинка / плейсхолдер с overlay -->
          <div class="group relative w-full aspect-video max-h-48 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
            <img v-if="goal?.image_url" :src="goal.image_url" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
              <svg class="w-16 h-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>

            <!-- Overlay: иконки редактирования и архива (top-right) -->
            <div class="absolute top-2 right-2 flex items-center gap-1">
              <button @click.stop="handleEditGoal" class="p-1.5 bg-black/50 hover:bg-black/70 text-white rounded-md backdrop-blur-sm" title="Редактировать">
                <PencilIcon class="w-4 h-4" />
              </button>
              <button v-if="goal?.status !== 'archived'" @click.stop="handleArchiveGoal" class="p-1.5 bg-black/50 hover:bg-red-600/80 text-white rounded-md backdrop-blur-sm" title="Архивировать">
                <ArchiveBoxArrowDownIcon class="w-4 h-4" />
              </button>
              <button v-else @click.stop="handleUnarchiveGoal" class="p-1.5 bg-black/50 hover:bg-black/70 text-white rounded-md backdrop-blur-sm" title="Восстановить">
                <ArrowUturnLeftIcon class="w-4 h-4" />
              </button>
            </div>

            <!-- Overlay: сфера (top-left) -->
            <span v-if="goalSphere" class="absolute top-2 left-2 inline-flex items-center gap-1.5 text-xs text-white bg-black/40 backdrop-blur-sm rounded-md px-2 py-1">
              <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: goalSphere.color }"></span>
              {{ goalSphere.name }}
            </span>

            <!-- Overlay: дедлайн (bottom-left) -->
            <span v-if="goal?.deadline" class="absolute bottom-2 left-2 inline-flex items-center gap-1.5 text-xs text-white bg-black/40 backdrop-blur-sm rounded-md px-2 py-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              {{ formatDeadline(goal.deadline) }}
            </span>

            <!-- Overlay: архив бейдж (bottom-right) -->
            <span v-if="goal?.status === 'archived'" class="absolute bottom-2 right-2 text-xs text-white bg-black/40 backdrop-blur-sm rounded-md px-2 py-1">Архив</span>
          </div>

          <!-- Title -->
          <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">
            {{ goal?.name || 'Загрузка...' }}
          </h1>

          <!-- Description -->
          <div v-if="goal?.description">
            <div class="text-[10px] uppercase tracking-wider text-gray-300 dark:text-gray-600 mb-0.5">Видение</div>
            <p class="text-[15px] text-gray-700 dark:text-gray-300 leading-relaxed">
              {{ goal.description }}
            </p>
          </div>

          <!-- Bible verse -->
          <div v-if="goal?.bible_verse">
            <div class="text-[10px] uppercase tracking-wider text-gray-300 dark:text-gray-600 mb-0.5">Основание</div>
            <p class="text-sm italic text-gray-500 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-3">
              {{ goal.bible_verse }}
            </p>
          </div>

          <!-- Progress -->
          <div v-if="allTasks.length > 0" class="flex items-center gap-2">
            <div class="flex-1 h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
              <div class="h-full bg-emerald-500 transition-all" :style="{ width: progressPct + '%' }"></div>
            </div>
            <span class="text-[11px] text-gray-400">{{ allCompletedCount }}/{{ allTasks.length }}</span>
          </div>

          <template v-if="goal">
            <!-- Табы: Задачи + Потоки -->
            <div v-if="tabs.length > 0">
              <!-- Табы -->
              <div class="flex items-center gap-0 border-b border-gray-200 dark:border-gray-700 mb-3">
                <div class="flex-1 flex items-center gap-0 overflow-x-auto hide-scrollbar min-w-0">
                  <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    class="px-3 py-1.5 text-[12.5px] whitespace-nowrap border-b-2 transition-colors -mb-px"
                    :class="activeTab === tab.key
                      ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400'
                      : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                  >
                    {{ tab.label }}
                    <span class="ml-1 text-[10px] opacity-60">{{ tab.count }}</span>
                  </button>
                </div>
                <button
                  @click="openProjectModal"
                  class="flex-shrink-0 px-2 py-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 -mb-px"
                  title="Создать поток"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                </button>
              </div>

              <!-- Контент таба: прямые задачи -->
              <div v-if="activeTab === 'direct'">
                <TaskList v-if="activeTasks.length > 0" :tasks="activeTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
                <div v-if="completedTasks.length > 0" class="mt-3 opacity-60">
                  <TaskList :tasks="completedTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
                </div>
                <button @click="openNewTask(null)" class="mt-2 text-[12.5px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                  Создать задачу
                </button>
              </div>

              <!-- Контент таба: задачи потока -->
              <div v-for="project in goalProjects" :key="project.id" v-show="activeTab === 'project-' + project.id">
                <TaskList
                  v-if="projectActiveTasks(project).length > 0"
                  :tasks="projectActiveTasks(project)"
                  @task-click="handleTaskClick"
                  @toggle-complete="handleToggleComplete"
                />
                <div v-if="projectCompletedTasks(project).length > 0" class="mt-3 opacity-60">
                  <TaskList
                    :tasks="projectCompletedTasks(project)"
                    @task-click="handleTaskClick"
                    @toggle-complete="handleToggleComplete"
                  />
                </div>
                <div v-if="!project.tasks?.length" class="py-4 text-center text-sm text-gray-400">
                  В потоке пока нет задач
                </div>
                <div class="flex items-center gap-3 mt-2">
                  <button @click="openNewTask(project)" class="text-[12.5px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Создать задачу
                  </button>
                  <button @click="editProject(project)" class="text-[12.5px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Редактировать поток
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="py-10 text-center text-sm text-gray-400">
              В цели пока нет задач и потоков.<br>
              <button @click="openNewTask(null)" class="text-primary-600 dark:text-primary-400 hover:underline">Создайте задачу</button>
              или
              <button @click="openProjectModal" class="text-primary-600 dark:text-primary-400 hover:underline">добавьте поток</button>.
            </div>
          </template>
        </div>

        <!-- RIGHT: sidebar в стиле TaskDetail -->
        <aside class="lg:sticky lg:top-4 self-start space-y-6 bg-gray-50/60 dark:bg-gray-800/30 rounded-lg p-4">
          <!-- Участники -->
          <div>
            <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              Участники
            </div>
            <div class="space-y-1.5 mb-2" v-if="participantContacts.length">
              <div
                v-for="c in participantContacts"
                :key="c.id"
                class="group flex items-center gap-2.5 px-2 py-1.5 rounded-lg bg-white dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/50 hover:border-gray-200 dark:hover:border-gray-600 transition-colors cursor-pointer"
                @click="openContactCard(c)"
              >
                <div
                  class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-semibold text-white"
                  :style="{ backgroundColor: contactColor(c.id) }"
                >
                  {{ contactInitials(c.name) }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-[12.5px] text-gray-800 dark:text-gray-200 truncate leading-tight">{{ c.name }}</div>
                  <div v-if="c.phone" class="text-[10px] text-gray-400 truncate leading-tight">{{ c.phone }}</div>
                </div>
                <button
                  @click.stop="removeParticipant(c.id)"
                  class="flex-shrink-0 p-0.5 rounded text-gray-300 dark:text-gray-600 opacity-0 group-hover:opacity-100 hover:text-red-500 dark:hover:text-red-400 transition-all"
                  title="Убрать участника"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
            <div class="relative">
              <button
                @click="pickerOpen = !pickerOpen"
                class="text-[12.5px] text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 flex items-center gap-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Добавить участника
              </button>
              <div
                v-if="pickerOpen"
                class="absolute left-0 top-full mt-1 z-20 w-full min-w-[220px] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-1 max-h-60 overflow-y-auto thin-scroll"
              >
                <button
                  v-for="c in availableContacts"
                  :key="c.id"
                  @click="addParticipant(c.id)"
                  class="w-full text-left px-2.5 py-1 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 truncate"
                >
                  {{ c.name }}
                </button>
                <div v-if="!availableContacts.length" class="px-2.5 py-2 text-xs text-gray-400 text-center">
                  Все контакты уже добавлены
                </div>
              </div>
            </div>
          </div>

          <!-- Задачи на сегодня -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              Сегодня
            </div>
            <div v-if="todayTasks.length === 0" class="text-xs text-gray-400 py-1">Нет задач на сегодня</div>
            <div v-else class="space-y-1">
              <div
                v-for="task in todayTasks"
                :key="task.id"
                class="px-2 py-1.5 rounded border border-gray-100 dark:border-gray-700/50 hover:bg-white/60 dark:hover:bg-gray-800/60 cursor-pointer"
                @click="handleTaskClick(task)"
              >
                <div class="text-[12.5px] truncate" :class="task.completed_at ? 'text-gray-400 line-through' : 'text-gray-700 dark:text-gray-200'">
                  {{ task.title }}
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span v-if="taskAssignees(task).length" class="text-[10px] text-gray-400 truncate">
                    {{ taskAssignees(task).join(', ') }}
                  </span>
                  <span v-if="task.estimated_time" class="text-[10px] text-gray-400">
                    {{ task.estimated_time?.substring(0, 5) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Ожидание -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Ожидание
            </div>
            <div v-if="waitingTasks.length === 0" class="text-xs text-gray-400 py-1">Нет задач в ожидании</div>
            <div v-else class="space-y-1">
              <div
                v-for="task in waitingTasks"
                :key="task.id"
                class="px-2 py-1.5 rounded border border-gray-100 dark:border-gray-700/50 hover:bg-white/60 dark:hover:bg-gray-800/60 cursor-pointer"
                @click="handleTaskClick(task)"
              >
                <div class="text-[12.5px] truncate" :class="task.completed_at ? 'text-gray-400 line-through' : 'text-gray-700 dark:text-gray-200'">
                  {{ task.title }}
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span v-if="taskAssignees(task).length" class="text-[10px] text-gray-400 truncate">
                    {{ taskAssignees(task).join(', ') }}
                  </span>
                  <span v-if="task.due_date" class="text-[10px] text-gray-400">
                    {{ formatSidebarDate(task.due_date) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <!-- Заметки -->
          <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-1.5 text-[11px] font-medium uppercase tracking-wider text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Заметки
              </div>
              <button @click="openNewNote" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Создать заметку">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
              </button>
            </div>
            <div v-if="allGoalNotes.length === 0" class="text-xs text-gray-400 py-1">Нет заметок</div>
            <div v-else class="space-y-1">
              <div
                v-for="note in allGoalNotes"
                :key="note.id"
                class="px-2 py-1.5 rounded border border-gray-100 dark:border-gray-700/50 hover:bg-white/60 dark:hover:bg-gray-800/60 cursor-pointer"
                @click="openEditNote(note)"
              >
                <div class="text-[12.5px] text-gray-700 dark:text-gray-200 truncate">{{ note.title }}</div>
                <div v-if="note.content" class="text-[10px] text-gray-400 mt-0.5 line-clamp-1">{{ note.content }}</div>
                <div v-if="note.task" class="text-[10px] text-gray-300 dark:text-gray-600 mt-0.5 truncate">{{ note.task?.title }}</div>
              </div>
            </div>
          </div>

          <!-- Файлы -->
          <div v-if="allFiles.length > 0" class="pt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-1.5 mb-2 text-[11px] font-medium uppercase tracking-wider text-gray-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
              Файлы
            </div>
            <div class="space-y-1">
              <a
                v-for="file in allFiles"
                :key="file.id"
                :href="`/api/v1/attachments/${file.id}/download`"
                target="_blank"
                class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-white/60 dark:hover:bg-gray-800/60"
              >
                <div class="flex-shrink-0 w-6 h-6 rounded overflow-hidden bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                  <img v-if="file.mime_type?.startsWith('image/')" :src="`/api/v1/attachments/${file.id}/download`" class="w-full h-full object-cover" />
                  <svg v-else class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <span class="text-[12px] text-gray-600 dark:text-gray-300 truncate">{{ file.original_name || file.filename }}</span>
              </a>
            </div>
          </div>
        </aside>

        <!-- Task view -->
        <TaskView
          :show="showTaskView"
          :task="selectedTask"
          @close="showTaskView = false; selectedTask = null"
          @complete-task="handleCompleteTask"
          @uncomplete-task="handleUncompleteTask"
        />

        <!-- Goal modal -->
        <GoalModal
          :show="showGoalModal"
          :goal="goal"
          :server-error="goalError"
          @close="handleCloseGoalModal"
          @submit="handleSaveGoal"
        />

        <!-- Task modal -->
        <TaskModal
          :show="showTaskModal"
          :task="taskForModal"
          :server-error="taskError"
          @close="showTaskModal = false; taskForModal = null; taskError = ''"
          @submit="handleSaveTask"
        />

        <!-- Note modal -->
        <NoteModal
          :show="showNoteModal"
          :note="noteForModal"
          :goal-id="currentGoalId"
          @close="showNoteModal = false; noteForModal = null"
          @submit="handleSaveNote"
          @delete="handleDeleteNote"
        />

        <!-- Project modal (edit) -->
        <ProjectModal
          :show="showProjectModal"
          :project="projectForModal"
          :server-error="projectError"
          @close="showProjectModal = false; projectForModal = null; projectError = ''"
          @submit="handleSaveProject"
        />

        <!-- Project add dialog -->
        <div v-if="showProjectPicker" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" @click.self="showProjectPicker = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-sm p-1">
            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800 text-[11px] uppercase tracking-wider text-gray-400">
              Поток
            </div>
            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
              <div class="flex items-center gap-2">
                <input
                  v-model="newProjectName"
                  @keydown.enter.prevent="createAndAttachProject"
                  placeholder="Новый поток"
                  class="flex-1 px-2.5 py-1.5 text-sm bg-gray-50 dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-300 dark:focus:ring-gray-600"
                />
                <button
                  @click="createAndAttachProject"
                  :disabled="!newProjectName.trim() || creatingProject"
                  class="px-3 py-1.5 text-sm bg-gray-900 text-white dark:bg-white dark:text-gray-900 rounded disabled:opacity-40 hover:opacity-90"
                >
                  Создать
                </button>
              </div>
            </div>
            <div class="max-h-60 overflow-y-auto thin-scroll p-1">
              <div class="px-2 pt-1 pb-0.5 text-[10px] uppercase tracking-wider text-gray-400">Или привязать существующий</div>
              <button
                v-for="p in availableProjects"
                :key="p.id"
                @click="attachProject(p)"
                class="w-full text-left px-2.5 py-1.5 text-sm rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 truncate"
              >
                {{ p.name }}
              </button>
              <div v-if="!availableProjects.length" class="px-2.5 py-2 text-xs text-gray-400 text-center">
                Свободных потоков нет
              </div>
            </div>
          </div>
        </div>
        <!-- Contact card modal -->
        <div v-if="contactCardData" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" @click.self="contactCardData = null">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-xs overflow-hidden">
            <!-- Header with avatar -->
            <div class="px-5 pt-5 pb-4 flex items-center gap-3">
              <div
                class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-sm font-bold text-white"
                :style="{ backgroundColor: contactColor(contactCardData.id) }"
              >
                {{ contactInitials(contactCardData.name) }}
              </div>
              <div class="min-w-0">
                <div class="text-base font-semibold text-gray-900 dark:text-white truncate">{{ contactCardData.name }}</div>
                <div v-if="contactCardData.specialization" class="text-xs text-gray-400 truncate">{{ contactCardData.specialization }}</div>
              </div>
            </div>
            <!-- Info rows -->
            <div class="px-5 pb-4 space-y-2">
              <a v-if="contactCardData.phone" :href="'tel:' + contactCardData.phone" class="flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                {{ contactCardData.phone }}
              </a>
              <a v-if="contactCardData.personal_phone && contactCardData.personal_phone !== contactCardData.phone" :href="'tel:' + contactCardData.personal_phone" class="flex items-center gap-2.5 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                {{ contactCardData.personal_phone }} <span class="text-xs text-gray-400">(личный)</span>
              </a>
              <a v-if="contactCardData.email" :href="'mailto:' + contactCardData.email" class="flex items-center gap-2.5 text-sm text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                {{ contactCardData.email }}
              </a>
              <div v-if="contactCardData.address" class="flex items-center gap-2.5 text-sm text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                {{ contactCardData.address }}
              </div>
              <div v-if="contactCardData.messengers && Object.keys(contactCardData.messengers).length" class="flex flex-wrap gap-2 pt-1">
                <span v-for="(val, key) in contactCardData.messengers" :key="key" class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-xs text-gray-600 dark:text-gray-300">
                  <span class="font-medium">{{ key }}:</span> {{ val }}
                </span>
              </div>
              <p v-if="contactCardData.notes" class="text-xs text-gray-400 pt-1 leading-relaxed">{{ contactCardData.notes }}</p>
            </div>
            <!-- Footer -->
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800 flex justify-between">
              <button @click="router.push('/contacts')" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">Все контакты</button>
              <button @click="contactCardData = null" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">Закрыть</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useGoalsStore } from '@/stores/goals'
import { useTasksStore } from '@/stores/tasks'
import { useProjectsStore } from '@/stores/projects'
import { useConfirmStore } from '@/stores/confirm'
import { useAuthStore } from '@/stores/auth'
import { PencilIcon, ArchiveBoxArrowDownIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import TaskModal from '@/components/tasks/TaskModal.vue'
import GoalModal from '@/components/goals/GoalModal.vue'
import ProjectModal from '@/components/projects/ProjectModal.vue'
import NoteModal from '@/components/notes/NoteModal.vue'
import { useNotesStore } from '@/stores/notes'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const goalsStore = useGoalsStore()
const tasksStore = useTasksStore()
const projectsStore = useProjectsStore()
const confirmStore = useConfirmStore()
const authStore = useAuthStore()
const notesStore = useNotesStore()

const showTaskView = ref(false)
const showGoalModal = ref(false)
const showProjectPicker = ref(false)
const showTaskModal = ref(false)
const showProjectModal = ref(false)
const showNoteModal = ref(false)
const selectedTask = ref(null)
const taskForModal = ref(null)
const projectForModal = ref(null)
const noteForModal = ref(null)
const goalError = ref('')
const taskError = ref('')
const projectError = ref('')
const participants = ref([])
const allContacts = ref([])
const pickerOpen = ref(false)
const contactCardData = ref(null)

const currentGoalId = computed(() => parseInt(route.params.goalId))
const goal = computed(() => goalsStore.allGoals.find(g => g.id === currentGoalId.value) || null)

const goalProjects = computed(() =>
  projectsStore.allProjects
    .filter(p => p.goal_id === currentGoalId.value)
    .map(p => ({ ...p, tasks: tasksStore.allTasks.filter(t => t.project_id === p.id) }))
)
const availableProjects = computed(() =>
  projectsStore.allProjects.filter(p => p.goal_id !== currentGoalId.value && p.status !== 'archived')
)

const goalSphere = computed(() => goal.value?.life_sphere || null)

const directTasks = computed(() => tasksStore.allTasks.filter(t => t.goal_id === currentGoalId.value && !t.project_id))
const activeTasks = computed(() => directTasks.value.filter(t => !t.completed_at))
const completedTasks = computed(() => directTasks.value.filter(t => t.completed_at))

const activeTab = ref('direct')

const goalNotes = computed(() => notesStore.notesByGoalId(currentGoalId.value))
const allGoalNotes = computed(() => {
  const taskIds = new Set(allTasks.value.map(t => t.id))
  const fromTasks = notesStore.allNotes.filter(n => n.task_id && taskIds.has(n.task_id))
  const fromGoal = goalNotes.value
  const seen = new Set()
  return [...fromGoal, ...fromTasks].filter(n => {
    if (seen.has(n.id)) return false
    seen.add(n.id)
    return true
  })
})

const tabs = computed(() => {
  const result = []
  if (directTasks.value.length > 0) {
    result.push({ key: 'direct', label: 'Задачи', count: directTasks.value.length })
  }
  for (const p of goalProjects.value) {
    result.push({ key: 'project-' + p.id, label: p.name, count: p.tasks?.length || 0 })
  }
  return result
})

const projectActiveTasks = (project) => (project.tasks || []).filter(t => !t.completed_at)
const projectCompletedTasks = (project) => (project.tasks || []).filter(t => t.completed_at)

const today = new Date().toISOString().split('T')[0]
const todayTasks = computed(() =>
  allTasks.value.filter(t => t.due_date === today || t.status === 'today')
)
const isDelegated = (t) => {
  const uid = authStore.user?.id
  if (!uid) return false
  return (t.contacts || []).some(c => c.pivot?.role === 'assignee' && c.contact_user_id !== uid)
}
const waitingTasks = computed(() =>
  allTasks.value.filter(t => !t.completed_at && (t.status === 'waiting' || isDelegated(t)))
)
const allFiles = computed(() =>
  allTasks.value.flatMap(t => (t.attachments || []).map(a => ({ ...a, taskTitle: t.title })))
)

// Автовыбор первого таба
watch(tabs, (t) => {
  if (t.length && !t.find(tab => tab.key === activeTab.value)) {
    activeTab.value = t[0].key
  }
}, { immediate: true })

const allTasks = computed(() => tasksStore.allTasks.filter(t =>
  t.goal_id === currentGoalId.value || goalProjects.value.some(p => p.id === t.project_id)
))
const allCompletedCount = computed(() => allTasks.value.filter(t => t.completed_at).length)
const progressPct = computed(() => allTasks.value.length ? Math.round(allCompletedCount.value / allTasks.value.length * 100) : 0)

const avatarColors = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#eab308','#22c55e','#14b8a6','#06b6d4','#3b82f6']
const contactColor = (id) => avatarColors[id % avatarColors.length]
const contactInitials = (name) => {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  return parts.length >= 2
    ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
    : name.substring(0, 2).toUpperCase()
}

const openContactCard = (contact) => { contactCardData.value = contact }

const participantContacts = computed(() => {
  const set = new Set(participants.value)
  return allContacts.value.filter(c => set.has(c.id))
})
const availableContacts = computed(() => {
  const set = new Set(participants.value)
  return allContacts.value.filter(c => !set.has(c.id))
})

const projectProgress = (p) => {
  if (!p.tasks?.length) return 0
  return Math.round(p.tasks.filter(t => t.completed_at).length / p.tasks.length * 100)
}

const formatDeadline = (s) => {
  if (!s) return ''
  const d = new Date(s); const today = new Date()
  today.setHours(0,0,0,0); d.setHours(0,0,0,0)
  const days = Math.ceil((d - today) / 86400000)
  if (days < 0) return `Просрочено на ${Math.abs(days)} дн.`
  if (days === 0) return 'Сегодня'
  if (days === 1) return 'Завтра'
  if (days <= 30) return `${days} дн.`
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', year: 'numeric' })
}
const deadlineClass = computed(() => {
  if (!goal.value?.deadline) return 'text-gray-500 dark:text-gray-400'
  const d = new Date(goal.value.deadline); const today = new Date()
  today.setHours(0,0,0,0); d.setHours(0,0,0,0)
  const days = Math.ceil((d - today) / 86400000)
  if (days < 0) return 'text-red-500 dark:text-red-400'
  if (days <= 3) return 'text-orange-500 dark:text-orange-400'
  if (days <= 7) return 'text-yellow-600 dark:text-yellow-400'
  return 'text-gray-500 dark:text-gray-400'
})

const loadGoalData = async () => {
  if (!currentGoalId.value) return
  try {
    const res = await api.get(`/v1/goals/${currentGoalId.value}`)
    participants.value = (res.data?.contacts || []).map(c => c.id)
  } catch (e) { console.error(e) }
}
const loadContacts = async () => {
  try {
    const res = await api.get('/v1/contacts')
    allContacts.value = res.data || []
  } catch (e) { allContacts.value = [] }
}

onMounted(async () => {
  await Promise.all([loadGoalData(), loadContacts(), notesStore.fetchAllNotes()])
})

const saveParticipants = async () => {
  try {
    await api.put(`/v1/goals/${currentGoalId.value}`, { contact_ids: participants.value })
  } catch (e) { console.error(e) }
}
const addParticipant = (id) => {
  if (!participants.value.includes(id)) {
    participants.value.push(id)
    saveParticipants()
  }
  pickerOpen.value = false
}
const removeParticipant = async (id) => {
  const c = allContacts.value.find(x => x.id === id)
  const ok = await confirmStore.ask({ title: 'Убрать участника?', message: c?.name || '', confirmText: 'Убрать' })
  if (!ok) return
  participants.value = participants.value.filter(x => x !== id)
  saveParticipants()
}

const newProjectName = ref('')
const creatingProject = ref(false)

const attachProject = async (project) => {
  await projectsStore.updateProject(project.id, { goal_id: currentGoalId.value })
  showProjectPicker.value = false
}

const createAndAttachProject = async () => {
  const name = newProjectName.value.trim()
  if (!name) return
  creatingProject.value = true
  try {
    await projectsStore.createProject({
      name,
      goal_id: currentGoalId.value,
      life_sphere_id: goal.value?.life_sphere_id || null,
    })
    newProjectName.value = ''
    showProjectPicker.value = false
  } catch (e) {
    console.error('Ошибка создания потока', e)
  } finally {
    creatingProject.value = false
  }
}
const detachProject = async (project) => {
  const ok = await confirmStore.ask({ title: 'Отвязать поток от цели?', message: project.name, confirmText: 'Отвязать' })
  if (!ok) return
  await projectsStore.updateProject(project.id, { goal_id: null })
}
const openProjectModal = () => { showProjectPicker.value = true }
const openProject = (project) => router.push(`/projects/${project.id}`)

const handleEditGoal = () => { showGoalModal.value = true }

const handleArchiveGoal = async () => {
  const ok = await confirmStore.ask({ title: 'Архивировать цель?', message: goal.value?.name || '', confirmText: 'Архивировать' })
  if (!ok) return
  await goalsStore.updateGoal(goal.value.id, { status: 'archived' })
  router.push('/goals')
}
const handleUnarchiveGoal = async () => {
  await goalsStore.updateGoal(goal.value.id, { status: 'active' })
}

const taskAssignees = (task) =>
  (task.contacts || []).filter(c => c.pivot?.role === 'assignee').map(c => c.name)

const formatSidebarDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })
}

const openNewTask = (project) => {
  taskForModal.value = {
    goal_id: currentGoalId.value,
    project_id: project?.id || null,
    life_sphere_id: goal.value?.life_sphere_id || null,
  }
  showTaskModal.value = true
}

const editProject = (project) => {
  projectForModal.value = project
  showProjectModal.value = true
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (taskData.id) {
      await tasksStore.updateTask(taskData.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
    taskForModal.value = null
  } catch (e) {
    taskError.value = e.response?.data?.message || 'Ошибка сохранения задачи'
  }
}

const handleSaveProject = async (projectData) => {
  projectError.value = ''
  try {
    if (projectForModal.value?.id) {
      await projectsStore.updateProject(projectForModal.value.id, projectData)
    } else {
      await projectsStore.createProject({ ...projectData, goal_id: currentGoalId.value })
    }
    showProjectModal.value = false
    projectForModal.value = null
  } catch (e) {
    projectError.value = e.response?.data?.message || 'Ошибка сохранения потока'
  }
}

const openNewNote = () => {
  noteForModal.value = null
  showNoteModal.value = true
}
const openEditNote = (note) => {
  noteForModal.value = note
  showNoteModal.value = true
}
const handleSaveNote = async (data) => {
  try {
    if (data.id) {
      await notesStore.updateNote(data.id, data)
    } else {
      await notesStore.createNote(data)
    }
    showNoteModal.value = false
    noteForModal.value = null
  } catch (e) {
    console.error('Error saving note:', e)
  }
}
const handleDeleteNote = async (note) => {
  if (!confirm(`Удалить заметку "${note.title}"?`)) return
  await notesStore.deleteNote(note.id)
  showNoteModal.value = false
  noteForModal.value = null
}

const formatNoteDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const handleTaskClick = (task) => { selectedTask.value = task; showTaskView.value = true }
const handleToggleComplete = async (task) => {
  if (task.completed_at) await tasksStore.uncompleteTask(task.id)
  else await tasksStore.completeTask(task.id)
}
const handleCompleteTask = (task) => tasksStore.completeTask(task.id)
const handleUncompleteTask = (task) => tasksStore.uncompleteTask(task.id)

const handleSaveGoal = async (goalData) => {
  goalError.value = ''
  try {
    await goalsStore.updateGoal(goal.value.id, goalData)
    showGoalModal.value = false
    await goalsStore.fetchAllGoals({ force: true })
  } catch (error) {
    goalError.value = error.response?.data?.message || 'Ошибка сохранения'
  }
}
const handleCloseGoalModal = () => { showGoalModal.value = false; goalError.value = '' }
</script>

<style scoped>
.thin-scroll { scrollbar-width: thin; scrollbar-color: rgba(156,163,175,0.35) transparent; }
.thin-scroll::-webkit-scrollbar { width: 6px; }
.thin-scroll::-webkit-scrollbar-thumb { background-color: rgba(156,163,175,0.25); border-radius: 3px; }
.thin-scroll::-webkit-scrollbar-thumb:hover { background-color: rgba(156,163,175,0.5); }
.hide-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
.hide-scrollbar::-webkit-scrollbar { display: none; }
</style>
