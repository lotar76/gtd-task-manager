<template>
  <div>
    <div class="p-4 lg:p-8">
      <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header: image / placeholder with overlay -->
        <div class="group relative w-full aspect-video max-h-48 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
          <img v-if="sphere?.image_url" :src="sphere.image_url" class="w-full h-full object-cover" />
          <div
            v-else
            class="w-full h-full flex items-center justify-center"
            :style="sphere ? { background: `linear-gradient(135deg, ${sphere.color}20, ${sphere.color}50)` } : {}"
          >
            <svg class="w-16 h-16" :style="sphere ? { color: sphere.color } : {}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4" />
            </svg>
          </div>

          <!-- Top-right: edit / hide / delete -->
          <div class="absolute top-2 right-2 flex items-center gap-1">
            <button @click="handleEdit" class="p-1.5 bg-black/50 hover:bg-black/70 text-white rounded-md backdrop-blur-sm" title="Редактировать">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button
              @click="handleToggleHidden"
              class="p-1.5 bg-black/50 hover:bg-black/70 text-white rounded-md backdrop-blur-sm"
              :title="sphere?.is_hidden ? 'Показать сферу' : 'Скрыть сферу'"
            >
              <svg v-if="!sphere?.is_hidden" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
              </svg>
            </button>
          </div>

          <!-- Top-left: color dot -->
          <div v-if="sphere" class="absolute top-2 left-2 inline-flex items-center gap-1.5 text-xs text-white bg-black/40 backdrop-blur-sm rounded-md px-2 py-1">
            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: sphere.color }"></span>
            <span v-if="sphere.is_hidden" class="opacity-70">Скрыта</span>
          </div>
        </div>

        <!-- Title -->
        <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">
          {{ sphere?.name || 'Загрузка...' }}
        </h1>

        <!-- Description (видение) -->
        <div v-if="sphere?.description">
          <div class="text-[10px] uppercase tracking-wider text-gray-300 dark:text-gray-600 mb-0.5">Видение</div>
          <p class="text-[15px] text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
            {{ sphere.description }}
          </p>
        </div>

        <!-- Tabs: goals + tasks -->
        <template v-if="sphere">
          <div v-if="goals.length > 0 || tasks.length > 0">
            <div class="flex items-center gap-0 border-b border-gray-200 dark:border-gray-700 mb-3">
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

            <!-- Goals tab -->
            <div v-if="activeTab === 'goals'" class="space-y-2">
              <router-link
                v-for="goal in goals"
                :key="goal.id"
                :to="`/goals/${goal.id}`"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors"
              >
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ goal.name }}</div>
                  <div v-if="goal.deadline" class="text-[10px] text-gray-400 mt-0.5">{{ goal.deadline }}</div>
                </div>
                <span v-if="goal.progress > 0" class="text-[10px] text-primary-600 dark:text-primary-400 font-medium">
                  {{ goal.progress }}%
                </span>
              </router-link>
            </div>

            <!-- Tasks tab -->
            <div v-if="activeTab === 'tasks'">
              <TaskList v-if="activeTasks.length > 0" :tasks="activeTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
              <div v-if="completedTasks.length > 0" class="mt-3 opacity-60">
                <TaskList :tasks="completedTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
              </div>
              <div v-if="tasks.length === 0" class="py-4 text-center text-sm text-gray-400">
                Нет задач в этой сфере
              </div>
            </div>
          </div>

          <div v-else class="py-10 text-center text-sm text-gray-400">
            В этой сфере пока нет целей и задач.
          </div>
        </template>
      </div>
    </div>

    <!-- Task View -->
    <TaskView
      :show="showTaskView"
      :task="selectedTask"
      @close="showTaskView = false"
      @saved="handleTaskSaved"
    />

    <!-- Sphere Modal -->
    <SphereModal
      :show="showEditModal"
      :sphere="sphere"
      @close="showEditModal = false"
      @saved="handleSphereUpdated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import { useTasksStore } from '@/stores/tasks'
import TaskList from '@/components/tasks/TaskList.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import SphereModal from '@/components/spheres/SphereModal.vue'

const route = useRoute()
const router = useRouter()
const spheresStore = useLifeSpheresStore()
const tasksStore = useTasksStore()

const sphere = ref(null)
const loading = ref(false)
const activeTab = ref('goals')
const showTaskView = ref(false)
const selectedTask = ref(null)
const showEditModal = ref(false)

const goals = computed(() => sphere.value?.goals || [])
const tasks = computed(() => sphere.value?.tasks || [])
const activeTasks = computed(() => tasks.value.filter(t => !t.completed_at))
const completedTasks = computed(() => tasks.value.filter(t => t.completed_at))

const tabs = computed(() => {
  const t = []
  if (goals.value.length > 0) {
    t.push({ key: 'goals', label: 'Цели', count: goals.value.length })
  }
  t.push({ key: 'tasks', label: 'Задачи', count: tasks.value.length })
  return t
})

const fetchSphere = async () => {
  loading.value = true
  try {
    sphere.value = await spheresStore.fetchOne(route.params.sphereId)
    if (goals.value.length > 0) {
      activeTab.value = 'goals'
    } else {
      activeTab.value = 'tasks'
    }
  } finally {
    loading.value = false
  }
}

const handleEdit = () => {
  showEditModal.value = true
}

const handleToggleHidden = async () => {
  if (!sphere.value) return
  await spheresStore.update(sphere.value.id, { is_hidden: !sphere.value.is_hidden })
  await fetchSphere()
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  showTaskView.value = true
}

const handleToggleComplete = async (task) => {
  const data = task.completed_at
    ? { completed_at: null }
    : { completed_at: new Date().toISOString() }
  await tasksStore.updateTask(task.id, data)
  await fetchSphere()
}

const handleTaskSaved = async () => {
  showTaskView.value = false
  await fetchSphere()
}

const handleSphereUpdated = async () => {
  showEditModal.value = false
  await fetchSphere()
}

watch(() => route.params.sphereId, (newId) => {
  if (newId) fetchSphere()
})

onMounted(() => {
  fetchSphere()
})
</script>
