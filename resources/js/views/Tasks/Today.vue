<template>
  <div class="p-4 lg:p-8">
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">Сегодня</h1>
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
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
              <CalendarIcon class="w-8 h-8 text-amber-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">На сегодня задач нет</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 leading-relaxed">
              Здесь находятся задачи, которые вы решили выполнить именно сегодня.
            </p>
            <button
              @click="handleAddTask"
              class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors"
            >
              <PlusIcon class="w-5 h-5 mr-1.5" />
              Запланировать на сегодня
            </button>
          </div>
        </div>
      </template>

      <TaskView :show="showTaskView" :task="selectedTask" @close="showTaskView = false; selectedTask = null" />
      <TaskView :show="showDraft" :task="draftTask" @close="closeDraft" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useTasksStore } from '@/stores/tasks'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import { CalendarIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { useTaskDraft } from '@/composables/useTaskDraft'

const tasksStore = useTasksStore()
const tasks = computed(() => tasksStore.todayTasks)
const loading = computed(() => tasksStore.loading)
const showTaskView = ref(false)
const selectedTask = ref(null)

const { draftTask, showDraft, startDraft, closeDraft } = useTaskDraft(() => tasksStore.fetchTasks?.())

const handleTaskClick = (task) => { selectedTask.value = task; showTaskView.value = true }
const handleAddTask = () => startDraft({ status: 'today', due_date: new Date().toISOString().split('T')[0] })

const handleToggleComplete = async (task) => {
  if (task.completed_at) await tasksStore.uncompleteTask(task.id)
  else await tasksStore.completeTask(task.id)
}
</script>
