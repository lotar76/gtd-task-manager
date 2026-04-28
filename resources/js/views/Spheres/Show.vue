<template>
  <div>
    <div class="p-4 lg:p-8">
      <div class="max-w-4xl mx-auto space-y-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-sm text-gray-400">
          <router-link to="/spheres" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">Сферы жизни</router-link>
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
          <span class="text-gray-700 dark:text-gray-200 truncate">{{ sphere?.name || '...' }}</span>
        </nav>

        <!-- Title + actions -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div v-if="sphere" class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: sphere.color }"></div>
            <h1 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">
              {{ sphere?.name || 'Загрузка...' }}
            </h1>
            <span v-if="sphere?.is_hidden" class="text-[10px] font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">Скрыта</span>
          </div>
          <div v-if="sphere" class="flex items-center gap-1">
            <button @click="handleEdit" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" title="Редактировать">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button
              @click="handleToggleHidden"
              class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800"
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
        </div>

        <!-- Image left + description wraps around -->
        <div v-if="sphere?.description || (sphere?.images && sphere.images.length > 0)">
          <div class="text-[10px] uppercase tracking-wider text-gray-300 dark:text-gray-600 mb-2">Видение</div>
          <div>
            <img
              v-if="sphere?.cover_image_url"
              :src="sphere.cover_image_url"
              class="float-left w-36 lg:w-48 aspect-[3/4] object-cover rounded-xl mr-5 mb-3"
            />
            <p
              v-if="sphere?.description"
              class="text-[15px] text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line"
            >{{ sphere.description }}</p>
            <div class="clear-both"></div>
          </div>

          <!-- Gallery -->
          <div v-if="sphere?.images && sphere.images.length > 1" class="mt-4">
            <div class="flex flex-wrap gap-2">
              <img
                v-for="(img, idx) in sphere.images"
                :key="img.id"
                :src="img.url"
                class="w-24 h-32 object-cover rounded-lg cursor-pointer hover:opacity-80 transition-opacity"
                @click="openLightbox(idx)"
              />
            </div>
          </div>
        </div>

        <!-- Lightbox Gallery -->
        <Teleport to="body">
          <Transition name="fade">
            <div
              v-if="lightboxIndex !== null"
              class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
              @click="lightboxIndex = null"
            >
              <!-- Image container with swipe -->
              <div
                class="relative w-full h-full flex items-center justify-center select-none"
                @touchstart="onTouchStart"
                @touchmove="onTouchMove"
                @touchend="onTouchEnd"
                @click.stop
              >
                <img
                  :src="galleryImages[lightboxIndex]?.url"
                  class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg transition-transform duration-200"
                  :style="{ transform: `translateX(${swipeOffset}px)` }"
                  draggable="false"
                />
              </div>

              <!-- Close -->
              <button @click="lightboxIndex = null" class="absolute top-4 right-4 p-2 text-white/70 hover:text-white z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>

              <!-- Prev / Next arrows (desktop) -->
              <button
                v-if="lightboxIndex > 0"
                @click.stop="lightboxIndex--"
                class="absolute left-3 top-1/2 -translate-y-1/2 p-2 text-white/50 hover:text-white z-10"
              >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
              </button>
              <button
                v-if="lightboxIndex < galleryImages.length - 1"
                @click.stop="lightboxIndex++"
                class="absolute right-3 top-1/2 -translate-y-1/2 p-2 text-white/50 hover:text-white z-10"
              >
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </button>

              <!-- Counter -->
              <div v-if="galleryImages.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/60 text-xs z-10">
                {{ lightboxIndex + 1 }} / {{ galleryImages.length }}
              </div>
            </div>
          </Transition>
        </Teleport>

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
                  <div v-if="goal.deadline" class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(goal.deadline) }}</div>
                </div>
                <span v-if="goal.progress > 0" class="text-[10px] text-primary-600 dark:text-primary-400 font-medium">
                  {{ goal.progress }}%
                </span>
              </router-link>
            </div>

            <!-- Tasks tab -->
            <div v-if="activeTab === 'tasks'">
              <TaskList v-if="activeTasks.length > 0" :tasks="activeTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
              <div v-if="activeTasks.length === 0 && completedTasks.length > 0" class="py-4 text-center text-sm text-gray-400">
                Все задачи выполнены
              </div>
              <div v-if="activeTasks.length === 0 && completedTasks.length === 0" class="py-4 text-center text-sm text-gray-400">
                Нет задач в этой сфере
              </div>

              <!-- Archived / completed -->
              <div v-if="completedTasks.length > 0" class="mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
                <button
                  @click="showCompleted = !showCompleted"
                  class="flex items-center gap-2 w-full text-left px-1 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                >
                  <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 8-4-4m5.5-7h5.3c1.12 0 1.68 0 2.11.22a2 2 0 01.87.87c.22.43.22.99.22 2.11v9.6c0 1.12 0 1.68-.22 2.11a2 2 0 01-.87.87c-.43.22-.99.22-2.11.22H6.8c-1.12 0-1.68 0-2.11-.22a2 2 0 01-.87-.87C3.6 18.48 3.6 17.92 3.6 16.8V7.2c0-1.12 0-1.68.22-2.11a2 2 0 01.87-.87C5.12 4 5.68 4 6.8 4h2.7" />
                  </svg>
                  <span class="text-sm text-gray-500 dark:text-gray-400">Выполненные</span>
                  <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">{{ completedTasks.length }}</span>
                  <svg
                    class="w-3.5 h-3.5 text-gray-400 ml-auto transition-transform"
                    :class="showCompleted ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
                <div v-if="showCompleted" class="mt-2 opacity-60">
                  <TaskList :tasks="completedTasks" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
                </div>
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
      @deleted="router.push('/spheres')"
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
const showCompleted = ref(false)
const lightboxIndex = ref(null)
const swipeOffset = ref(0)
let touchStartX = 0
let touchCurrentX = 0

const galleryImages = computed(() => sphere.value?.images || [])

const openLightbox = (index) => {
  lightboxIndex.value = index
  swipeOffset.value = 0
}

const onTouchStart = (e) => {
  touchStartX = e.touches[0].clientX
  touchCurrentX = touchStartX
}

const onTouchMove = (e) => {
  touchCurrentX = e.touches[0].clientX
  swipeOffset.value = touchCurrentX - touchStartX
}

const onTouchEnd = () => {
  const delta = touchCurrentX - touchStartX
  const threshold = 60

  if (delta < -threshold && lightboxIndex.value < galleryImages.value.length - 1) {
    lightboxIndex.value++
  } else if (delta > threshold && lightboxIndex.value > 0) {
    lightboxIndex.value--
  }
  swipeOffset.value = 0
}
const selectedTask = ref(null)
const showEditModal = ref(false)

const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const goals = computed(() => (sphere.value?.goals || []).filter(g => g.status === 'active' || !g.status))
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

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
