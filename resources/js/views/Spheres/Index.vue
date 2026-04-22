<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Сферы жизни</h1>
      <div class="flex items-center gap-2">
        <button
          v-if="spheres.length === 0"
          @click="handleSeedSpheres"
          :disabled="loading"
          class="px-4 py-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 transition-colors"
        >
          Заполнить по умолчанию
        </button>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Создать сферу</span>
        </button>
      </div>
    </div>

    <!-- Spheres Grid -->
    <div v-if="spheres.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="sphere in spheres"
        :key="sphere.id"
        class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-all cursor-pointer overflow-hidden group"
        :class="{ 'opacity-50': sphere.is_hidden }"
        @click="openSphere(sphere)"
      >
        <!-- Картинка -->
        <div class="h-36 overflow-hidden relative">
          <img v-if="sphere.image_url" :src="sphere.image_url" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center" :style="{ background: `linear-gradient(135deg, ${sphere.color}20, ${sphere.color}40)` }">
            <div class="w-16 h-16 rounded-full flex items-center justify-center" :style="{ backgroundColor: sphere.color + '30' }">
              <svg class="w-8 h-8" :style="{ color: sphere.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4" />
              </svg>
            </div>
          </div>
          <!-- Hidden badge -->
          <span
            v-if="sphere.is_hidden"
            class="absolute top-2 right-2 text-[10px] text-white bg-black/40 backdrop-blur-sm rounded-md px-2 py-0.5"
          >Скрыта</span>
        </div>

        <div class="p-4">
          <!-- Название с цветом -->
          <div class="flex items-center gap-2 mb-1">
            <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: sphere.color }"></div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ sphere.name }}</h3>
          </div>

          <!-- Описание -->
          <p v-if="sphere.description" class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
            {{ sphere.description }}
          </p>
          <div v-else class="mb-3"></div>

          <!-- Счётчики -->
          <div class="flex items-center gap-4 text-xs text-gray-400 dark:text-gray-500">
            <span v-if="sphere.tasks_count > 0" class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ sphere.tasks_count }} задач
            </span>
            <span v-if="sphere.goals_count > 0" class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              {{ sphere.goals_count }} целей
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading" class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Сферы жизни не созданы</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-2 max-w-md mx-auto leading-relaxed">
        Сферы помогают организовать задачи и цели по ключевым областям вашей жизни.
      </p>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto leading-relaxed">
        Добавьте описание-видение для каждой сферы, чтобы помнить, к чему вы стремитесь.
      </p>
      <div class="flex items-center justify-center gap-3">
        <button
          @click="handleSeedSpheres"
          :disabled="loading"
          class="px-4 py-2 text-primary-600 hover:text-primary-700 dark:text-primary-400 border border-primary-300 dark:border-primary-700 rounded-lg transition-colors"
        >
          Заполнить по умолчанию
        </button>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
        >
          Создать сферу
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <SphereModal
      :show="showCreateModal"
      :sphere="editingSphere"
      @close="handleCloseModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import SphereModal from '@/components/spheres/SphereModal.vue'

const router = useRouter()
const store = useLifeSpheresStore()

const loading = ref(false)
const showCreateModal = ref(false)
const editingSphere = ref(null)

const spheres = computed(() =>
  [...store.allSpheres].sort((a, b) => a.position - b.position)
)

const openSphere = (sphere) => {
  router.push(`/spheres/${sphere.id}`)
}

const handleSeedSpheres = async () => {
  loading.value = true
  try {
    await store.seedDefaults()
  } finally {
    loading.value = false
  }
}

const handleCloseModal = () => {
  showCreateModal.value = false
  editingSphere.value = null
}

const handleSaved = () => {
  showCreateModal.value = false
  editingSphere.value = null
  store.fetchAll({ force: true })
}

onMounted(async () => {
  await store.fetchAll()
})
</script>
