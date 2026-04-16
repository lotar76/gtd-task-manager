<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <div class="mb-4">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">Следующие действия</h1>
      </div>

      <!-- Контекст-фильтр -->
      <div v-if="contextsStore.allContexts.length" class="flex flex-wrap items-center gap-1.5 mb-4">
        <span class="text-[11px] uppercase tracking-wider text-gray-400 mr-2">Контекст:</span>
        <button
          @click="activeContext = null"
          class="px-2 py-0.5 text-xs rounded-md transition-colors"
          :class="activeContext === null ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
        >Все</button>
        <button
          v-for="c in contextsStore.allContexts"
          :key="c.id"
          @click="activeContext = c.id"
          class="px-2 py-0.5 text-xs rounded-md transition-colors"
          :class="activeContext === c.id ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700'"
          :style="activeContext === c.id ? {} : { color: c.color }"
        >{{ c.name }}</button>
        <button
          @click="activeContext = 'none'"
          class="px-2 py-0.5 text-xs rounded-md transition-colors"
          :class="activeContext === 'none' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
        >— без контекста</button>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <template v-else>
        <TaskList
          v-if="tasks.length > 0"
          :tasks="tasks"
          @task-click="handleTaskClick"
          @toggle-complete="handleToggleComplete"
        />

        <div v-else class="text-center py-12">
          <div class="max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-50 dark:bg-yellow-900/30 flex items-center justify-center">
              <BoltIcon class="w-8 h-8 text-yellow-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Нет следующих действий</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
              Следующие действия — это ключевой список GTD. Сюда попадают конкретные
              физические действия, которые можно выполнить прямо сейчас, без привязки к дате.
              Спросите себя: «Какой следующий конкретный шаг?» — и запишите его сюда.
            </p>
          </div>
        </div>
      </template>

      <TaskView
        :show="showTaskView"
        :task="selectedTask"
        @close="showTaskView = false; selectedTask = null"
        @complete-task="handleCompleteTask"
        @uncomplete-task="handleUncompleteTask"
      />

      </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import { useContextsStore } from '@/stores/contexts'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import { useTaskDraft } from '@/composables/useTaskDraft'
import { BoltIcon, PlusIcon } from '@heroicons/vue/24/outline'

const tasksStore = useTasksStore()
const contextsStore = useContextsStore()
const activeContext = ref(null)

onMounted(() => contextsStore.fetchAll())

const tasks = computed(() => {
  const base = tasksStore.nextActionTasks
  if (activeContext.value === null) return base
  if (activeContext.value === 'none') return base.filter(t => !t.context_id)
  return base.filter(t => t.context_id === activeContext.value)
})
const loading = computed(() => tasksStore.loading)
const showTaskView = ref(false)
const selectedTask = ref(null)

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
}

const { draftTask, showDraft, startDraft, closeDraft } = useTaskDraft(() => tasksStore.fetchTasks?.())
const handleAddTask = () => startDraft({ status: 'next_action' })

const handleCompleteTask = async (task) => {
  try {
    await tasksStore.completeTask(task.id)
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleUncompleteTask = async (task) => {
  try {
    await tasksStore.uncompleteTask(task.id)
  } catch (error) {
    console.error('Error uncompleting task:', error)
  }
}

const handleToggleComplete = async (task) => {
  try {
    if (task.completed_at) {
      await tasksStore.uncompleteTask(task.id)
    } else {
      await tasksStore.completeTask(task.id)
    }
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

</script>
