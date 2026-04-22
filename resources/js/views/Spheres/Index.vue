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
    <div v-if="spheres.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div
        v-for="sphere in spheres"
        :key="sphere.id"
        class="relative rounded-2xl overflow-hidden cursor-pointer group transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
        :class="{ 'grayscale opacity-60': sphere.is_hidden }"
        :style="{ minHeight: '380px' }"
        @click="openSphere(sphere)"
      >
        <!-- Background image or gradient -->
        <div class="absolute inset-0">
          <img
            v-if="sphere.image_url"
            :src="sphere.image_url"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div
            v-else
            class="w-full h-full"
            :style="{ background: `linear-gradient(160deg, ${sphere.color}30 0%, ${sphere.color}15 50%, ${sphere.color}05 100%)` }"
          ></div>
        </div>

        <!-- Gradient overlay: image fades into solid bottom -->
        <div
          class="absolute inset-0"
          :style="{
            background: sphere.image_url
              ? `linear-gradient(to bottom, transparent 15%, rgba(255,255,255,0.4) 40%, rgba(255,255,255,0.92) 60%, rgba(255,255,255,1) 75%)`
              : 'none'
          }"
        ></div>
        <div
          class="absolute inset-0 dark:hidden"
          :style="{ background: !sphere.image_url ? `linear-gradient(to bottom, transparent 30%, white 75%)` : 'none' }"
        ></div>
        <div
          class="absolute inset-0 hidden dark:block"
          :style="{
            background: sphere.image_url
              ? `linear-gradient(to bottom, transparent 15%, rgba(31,41,55,0.4) 40%, rgba(31,41,55,0.92) 60%, rgba(31,41,55,1) 75%)`
              : `linear-gradient(to bottom, transparent 30%, rgb(31,41,55) 75%)`
          }"
        ></div>

        <!-- Content overlay -->
        <div class="relative h-full flex flex-col justify-between p-5" style="min-height: 380px">
          <!-- Top: counters -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span
                v-if="sphere.tasks_count > 0"
                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-full backdrop-blur-sm"
                :style="{
                  backgroundColor: sphere.image_url ? 'rgba(0,0,0,0.35)' : sphere.color + '20',
                  color: sphere.image_url ? 'white' : sphere.color
                }"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                </svg>
                {{ sphere.tasks_count }}
              </span>
              <span
                v-if="sphere.goals_count > 0"
                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-full backdrop-blur-sm"
                :style="{
                  backgroundColor: sphere.image_url ? 'rgba(0,0,0,0.35)' : sphere.color + '20',
                  color: sphere.image_url ? 'white' : sphere.color
                }"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                {{ sphere.goals_count }}
              </span>
            </div>
            <span
              v-if="sphere.is_hidden"
              class="text-[10px] font-medium px-2 py-0.5 rounded-full backdrop-blur-sm"
              :style="{
                backgroundColor: sphere.image_url ? 'rgba(0,0,0,0.35)' : 'rgba(0,0,0,0.08)',
                color: sphere.image_url ? 'white' : '#9ca3af'
              }"
            >Скрыта</span>
          </div>

          <!-- Bottom: name + vision text -->
          <div>
            <!-- Color accent line -->
            <div class="w-8 h-0.5 rounded-full mb-3" :style="{ backgroundColor: sphere.color }"></div>

            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1.5 leading-tight">
              {{ sphere.name }}
            </h3>

            <p
              v-if="sphere.description"
              class="text-[13px] text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3"
            >
              {{ sphere.description }}
            </p>
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
