<template>
  <div class="p-4 lg:p-8 max-w-full overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_240px] gap-6">
      <!-- LEFT: main content -->
      <div class="min-w-0">
    <div class="flex items-center justify-between mb-2">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Потоки</h1>
      <button
        @click="showProjectModal = true"
        class="p-2 sm:px-4 sm:py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="hidden sm:inline">Создать поток</span>
      </button>
    </div>
    <button
      @click="showInfoModal = true"
      class="text-sm text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors mb-4"
    >
      Что такое потоки?
    </button>

    <!-- Projects Grid -->
    <div v-if="filteredProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="project in filteredProjects"
        :key="project.id"
        class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow cursor-pointer"
        @click="$router.push(`/projects/${project.id}`)"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
              {{ project.name }}
            </h3>
            <p v-if="getSphereName(project)" class="text-sm text-gray-500 dark:text-gray-400 truncate flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ backgroundColor: getSphereColor(project) }"></span>
              {{ getSphereName(project) }}
            </p>
          </div>
          <button
            @click.stop="handleArchiveProject(project)"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 transition-colors"
            title="Архивировать"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
          </button>
        </div>

        <p v-if="project.description" class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">
          {{ project.description }}
        </p>

        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500 dark:text-gray-400">
            {{ project.tasks_count || 0 }} задач
          </span>
          <span v-if="project.status === 'active'" class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-full text-xs">
            Активен
          </span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="activeProjects.length === 0" class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Нет потоков</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-4">Создайте первый поток для организации ваших задач</p>
      <button
        @click="showProjectModal = true"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
      >
        Создать поток
      </button>
    </div>

      </div>

      <!-- RIGHT: sidebar with spheres -->
      <aside class="hidden lg:block self-start sticky top-4 space-y-4">
        <div>
          <div class="text-[10px] uppercase tracking-wider text-gray-400 font-medium mb-2">Сферы жизни</div>
          <div class="space-y-1">
            <button
              @click="selectedSphereId = null"
              class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs transition-colors text-left"
              :class="selectedSphereId === null
                ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-medium'
                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50'"
            >
              <span class="w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-white text-[8px]">*</span>
              Все сферы
              <span class="ml-auto text-[10px] text-gray-400">{{ activeProjects.length }}</span>
            </button>
            <button
              v-for="sphere in sortedSpheres"
              :key="sphere.id"
              @click="selectedSphereId = selectedSphereId === sphere.id ? null : sphere.id"
              class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs transition-colors text-left"
              :class="selectedSphereId === sphere.id
                ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-medium'
                : projectCountBySphere(sphere.id) > 0
                  ? 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50'
                  : 'text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50'"
            >
              <div
                class="w-5 h-5 rounded-full overflow-hidden border flex-shrink-0"
                :style="{ borderColor: sphere.color }"
              >
                <img v-if="sphere.cover_image_url" :src="sphere.cover_image_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-white text-[7px] font-bold" :style="{ backgroundColor: sphere.color }">
                  {{ sphere.name?.charAt(0) }}
                </div>
              </div>
              <span class="flex-1 truncate">{{ sphere.name }}</span>
              <span class="ml-auto text-[10px] text-gray-400">{{ projectCountBySphere(sphere.id) }}</span>
            </button>
            <button
              @click="selectedSphereId = 'none'"
              class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-xs transition-colors text-left"
              :class="selectedSphereId === 'none'
                ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-medium'
                : projectCountBySphere(null) > 0
                  ? 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/50'
                  : 'text-gray-400 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800/50'"
              v-if="projectCountBySphere(null) > 0"
            >
              <span class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-[8px]">?</span>
              <span class="flex-1 truncate">Без сферы</span>
              <span class="ml-auto text-[10px] text-gray-400">{{ projectCountBySphere(null) }}</span>
            </button>
          </div>
        </div>
      </aside>
    </div>

    <!-- Project Modal -->
    <ProjectModal
      :show="showProjectModal"
      :project="selectedProject"
      :server-error="projectError"
      @close="handleCloseProjectModal"
      @submit="handleSaveProject"
    />

    <!-- Info Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showInfoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="showInfoModal = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-800">
              <span class="text-sm font-medium text-gray-900 dark:text-white">Что такое потоки?</span>
              <button @click="showInfoModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
              <p class="text-base font-medium text-gray-900 dark:text-white">
                Поток — это связанный набор задач, направленный на достижение конкретного результата.
              </p>

              <p>
                Если задачи — это отдельные шаги, то поток — это маршрут, по которому эти шаги выстраиваются в путь. Поток отвечает на вопрос: «К какому результату ведут эти задачи?»
              </p>

              <p>
                Потоки помогают не теряться в списке задач. Вместо хаотичного набора дел вы видите чёткие направления: «Запуск сайта», «Переезд», «Курс английского» — каждый со своими задачами и прогрессом.
              </p>

              <div>
                <p class="font-semibold text-gray-900 dark:text-white mb-2">Как работать с потоками:</p>
                <ol class="space-y-2 ml-1 list-decimal list-inside">
                  <li><strong>Создайте поток</strong> для каждого значимого направления. Если у группы задач есть общий результат — это поток.</li>
                  <li><strong>Добавляйте задачи</strong> в поток. Каждая задача должна приближать к результату потока.</li>
                  <li><strong>Отслеживайте прогресс.</strong> Видно, сколько задач осталось и как движется работа.</li>
                  <li><strong>Архивируйте</strong> завершённые потоки, чтобы фокусироваться на активных.</li>
                </ol>
              </div>

              <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                <p class="font-semibold text-gray-900 dark:text-white mb-1.5">Потоки, цели и привычки</p>
                <p>
                  <strong>Цели расширяют</strong> сферу жизни — задают амбиции и дедлайны. <strong>Привычки поливают</strong> — питают сферу ежедневными действиями. А <strong>потоки</strong> — это связанные наборы задач, через которые цели воплощаются в жизнь.
                </p>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectsStore } from '@/stores/projects'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import ProjectModal from '@/components/projects/ProjectModal.vue'

const router = useRouter()
const projectsStore = useProjectsStore()
const spheresStore = useLifeSpheresStore()

const showProjectModal = ref(false)
const showInfoModal = ref(false)
const selectedProject = ref(null)
const projectError = ref('')
const selectedSphereId = ref(null)

const activeProjects = computed(() => projectsStore.activeProjects)

const filteredProjects = computed(() => {
  if (selectedSphereId.value === null) return activeProjects.value
  if (selectedSphereId.value === 'none') return activeProjects.value.filter(p => !p.life_sphere_id)
  return activeProjects.value.filter(p => p.life_sphere_id === selectedSphereId.value)
})

const spheres = computed(() => spheresStore.visibleSpheres)

const sortedSpheres = computed(() => {
  return [...spheres.value].sort((a, b) => projectCountBySphere(b.id) - projectCountBySphere(a.id))
})

const projectCountBySphere = (sphereId) => {
  if (sphereId === null) return activeProjects.value.filter(p => !p.life_sphere_id).length
  return activeProjects.value.filter(p => p.life_sphere_id === sphereId).length
}

const getSphereName = (project) => {
  if (!project.life_sphere_id) return null
  const s = spheres.value.find(s => s.id === project.life_sphere_id)
  return s?.name || project.life_sphere?.name || null
}

const getSphereColor = (project) => {
  if (!project.life_sphere_id) return null
  const s = spheres.value.find(s => s.id === project.life_sphere_id)
  return s?.color || project.life_sphere?.color || null
}

const handleSaveProject = async (projectData) => {
  projectError.value = ''
  try {
    if (selectedProject.value) {
      await projectsStore.updateProject(selectedProject.value.id, projectData)
    } else {
      await projectsStore.createProject(projectData)
    }
    showProjectModal.value = false
    selectedProject.value = null
    await projectsStore.fetchAllProjects({ force: true })
  } catch (error) {
    console.error('Error saving project:', error)
    projectError.value = error.response?.data?.message || error.message || 'Ошибка при сохранении потока'
  }
}

const handleCloseProjectModal = () => {
  showProjectModal.value = false
  selectedProject.value = null
  projectError.value = ''
}

const handleArchiveProject = async (project) => {
  if (!confirm(`Архивировать поток "${project.name}"?`)) {
    return
  }

  try {
    await projectsStore.archiveProject(project.id)
    await projectsStore.fetchAllProjects({ force: true })
  } catch (error) {
    console.error('Error archiving project:', error)
    alert('Ошибка при архивировании потока: ' + (error.response?.data?.message || error.message))
  }
}

onMounted(async () => {
  await Promise.all([
    projectsStore.fetchAllProjects({ force: true }),
    spheresStore.fetchAll(),
  ])
})
</script>
