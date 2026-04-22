<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center justify-between mb-2 sm:mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Сферы жизни</h1>
      <div class="flex items-center gap-2">
        <button
          v-if="spheres.length === 0"
          @click="handleSeedSpheres"
          :disabled="loading"
          class="px-4 py-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 transition-colors hidden sm:block"
        >
          Заполнить по умолчанию
        </button>
        <button
          @click="showCreateModal = true"
          class="sm:px-4 sm:py-2 p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span class="hidden sm:inline">Создать сферу</span>
        </button>
      </div>
    </div>
    <button
      @click="showInfoModal = true"
      class="text-sm text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors mb-4 sm:mb-6 sm:-mt-4"
    >
      Что такое сферы жизни?
    </button>

    <!-- Spheres Grid -->
    <draggable
      v-if="spheres.length > 0"
      v-model="spheres"
      item-key="id"
      ghost-class="opacity-30"
      animation="200"
      @end="handleReorder"
      class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5"
    >
      <template #item="{ element: sphere }">
      <div
        class="relative rounded-2xl overflow-hidden cursor-pointer group transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
        :class="{ 'grayscale opacity-60': sphere.is_hidden }"
        :style="{ minHeight: '500px' }"
        @click="openSphere(sphere)"
      >
        <!-- Background image carousel or gradient -->
        <div class="absolute inset-0">
          <template v-if="sphere.images && sphere.images.length > 0">
            <img
              v-for="(img, imgIdx) in sphere.images"
              :key="img.id"
              :src="img.url"
              class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 group-hover:scale-105 transition-transform"
              :class="imgIdx === (activeSlides[sphere.id] || 0) ? 'opacity-100' : 'opacity-0'"
            />
          </template>
          <div
            v-else
            class="w-full h-full"
            :style="{ background: `linear-gradient(160deg, ${sphere.color}30 0%, ${sphere.color}15 50%, ${sphere.color}05 100%)` }"
          ></div>
        </div>

        <!-- Gradient overlay: image fades into solid bottom -->
        <div
          class="absolute inset-0 dark:hidden"
          :style="{
            background: hasImages(sphere)
              ? `linear-gradient(to bottom, transparent 55%, rgba(255,255,255,0.3) 70%, rgba(255,255,255,0.75) 80%, rgba(255,255,255,0.95) 88%, white 94%)`
              : `linear-gradient(to bottom, transparent 30%, white 75%)`
          }"
        ></div>
        <div
          class="absolute inset-0 hidden dark:block"
          :style="{
            background: hasImages(sphere)
              ? `linear-gradient(to bottom, transparent 55%, rgba(31,41,55,0.3) 70%, rgba(31,41,55,0.75) 80%, rgba(31,41,55,0.95) 88%, rgb(31,41,55) 94%)`
              : `linear-gradient(to bottom, transparent 30%, rgb(31,41,55) 75%)`
          }"
        ></div>

        <!-- Content overlay -->
        <div class="relative h-full flex flex-col justify-between p-5" style="min-height: 500px">
          <!-- Top: counters -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span
                v-if="sphere.tasks_count > 0"
                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-full backdrop-blur-sm"
                :style="{
                  backgroundColor: hasImages(sphere) ? 'rgba(0,0,0,0.35)' : sphere.color + '20',
                  color: hasImages(sphere) ? 'white' : sphere.color
                }"
              >
                {{ sphere.tasks_count }} {{ tasksWord(sphere.tasks_count) }}
              </span>
              <span
                v-if="sphere.goals_count > 0"
                class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-full backdrop-blur-sm"
                :style="{
                  backgroundColor: hasImages(sphere) ? 'rgba(0,0,0,0.35)' : sphere.color + '20',
                  color: hasImages(sphere) ? 'white' : sphere.color
                }"
              >
                {{ sphere.goals_count }} {{ goalsWord(sphere.goals_count) }}
              </span>
            </div>
            <span
              v-if="sphere.is_hidden"
              class="text-[10px] font-medium px-2 py-0.5 rounded-full backdrop-blur-sm"
              :style="{
                backgroundColor: hasImages(sphere) ? 'rgba(0,0,0,0.35)' : 'rgba(0,0,0,0.08)',
                color: hasImages(sphere) ? 'white' : '#9ca3af'
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
      </template>
    </draggable>

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
      @deleted="handleDeleted"
    />

    <!-- Info Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showInfoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="showInfoModal = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-800">
              <span class="text-sm font-medium text-gray-900 dark:text-white">Что такое сферы жизни?</span>
              <button @click="showInfoModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
              <p class="text-base font-medium text-gray-900 dark:text-white">
                Сферы жизни — это ключевые области, из которых складывается ваша жизнь как целое.
              </p>

              <p>
                Представьте свою жизнь как колесо, где каждая спица — это отдельная сфера. Если одна спица короче других, колесо едет неровно. Сферы помогают увидеть общую картину и не забывать о том, что действительно важно, пока рутина затягивает в привычные области.
              </p>

              <p>
                Это не просто список категорий для задач. Каждая сфера — это <strong>направление, в котором вы хотите расти</strong>. У каждой есть ваше личное видение: как выглядит идеальное состояние этой области через год, три, пять лет.
              </p>

              <div>
                <p class="font-semibold text-gray-900 dark:text-white mb-2">Классические сферы жизни:</p>
                <ul class="space-y-1.5 ml-1">
                  <li class="flex items-start gap-2">
                    <span class="text-purple-500 mt-0.5">&#x2022;</span>
                    <span><strong>Духовная</strong> — вера, смысл жизни, внутренний мир, медитация, благодарность</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-pink-500 mt-0.5">&#x2022;</span>
                    <span><strong>Семья и отношения</strong> — супруг(а), дети, родители, близкие люди</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-green-500 mt-0.5">&#x2022;</span>
                    <span><strong>Здоровье</strong> — физическая форма, питание, сон, энергия, спорт</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">&#x2022;</span>
                    <span><strong>Финансы</strong> — доход, накопления, инвестиции, финансовая свобода</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">&#x2022;</span>
                    <span><strong>Карьера и дело</strong> — профессиональный рост, проекты, призвание</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-indigo-500 mt-0.5">&#x2022;</span>
                    <span><strong>Развитие</strong> — обучение, навыки, книги, новый опыт</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-teal-500 mt-0.5">&#x2022;</span>
                    <span><strong>Окружение</strong> — друзья, сообщество, нетворкинг, вклад в общество</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-orange-500 mt-0.5">&#x2022;</span>
                    <span><strong>Отдых и яркость жизни</strong> — хобби, путешествия, впечатления, радость</span>
                  </li>
                </ul>
              </div>

              <div>
                <p class="font-semibold text-gray-900 dark:text-white mb-2">Как работать со сферами:</p>
                <ol class="space-y-2 ml-1 list-decimal list-inside">
                  <li><strong>Определите свои сферы.</strong> Классические 8 сфер — отличная отправная точка, но адаптируйте под себя. У кого-то «Творчество» важнее «Финансов», у кого-то «Служение» — отдельная большая область.</li>
                  <li><strong>Напишите видение</strong> для каждой сферы. Не цели, а именно картинку: «Как выглядит эта область моей жизни, когда всё идеально?» Пишите от первого лица и в настоящем времени.</li>
                  <li><strong>Добавьте вдохновляющую картинку</strong> — визуальный образ, который вызывает эмоции и напоминает, к чему вы стремитесь. Это может быть фото мечты, место, которое хотите посетить, или образ, который отражает ваше видение.</li>
                  <li><strong>Привязывайте задачи и цели</strong> к сферам. Так вы увидите, в какие области вкладываете энергию, а какие забываете.</li>
                  <li><strong>Пересматривайте</strong> раз в неделю: какие сферы получили внимание? Какие остались без движения?</li>
                </ol>
              </div>

              <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                <p class="font-semibold text-gray-900 dark:text-white mb-1.5">Зачем это нужно?</p>
                <p>
                  Без осознанного взгляда на сферы жизни мы естественным образом «проваливаемся» в одну-две области (обычно работа) и забываем об остальных. Проходят месяцы, и вдруг оказывается, что здоровье запущено, с близкими давно не общались, а хобби забыто.
                </p>
                <p class="mt-2">
                  Сферы жизни — это ваш личный радар. Они не требуют идеального баланса каждый день, но помогают заметить перекосы вовремя и скорректировать курс.
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import draggable from 'vuedraggable'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import SphereModal from '@/components/spheres/SphereModal.vue'

const router = useRouter()
const store = useLifeSpheresStore()

const loading = ref(false)
const showCreateModal = ref(false)
const showInfoModal = ref(false)
const editingSphere = ref(null)

// Carousel
const activeSlides = ref({})
let carouselInterval = null

const hasImages = (sphere) => sphere.images && sphere.images.length > 0

const startCarousel = () => {
  stopCarousel()
  carouselInterval = setInterval(() => {
    for (const sphere of spheres.value) {
      if (!sphere.images || sphere.images.length <= 1) continue
      const current = activeSlides.value[sphere.id] || 0
      activeSlides.value[sphere.id] = (current + 1) % sphere.images.length
    }
  }, 3000)
}

const stopCarousel = () => {
  if (carouselInterval) {
    clearInterval(carouselInterval)
    carouselInterval = null
  }
}

const pluralize = (n, one, few, many) => {
  const abs = Math.abs(n) % 100
  const last = abs % 10
  if (abs > 10 && abs < 20) return many
  if (last > 1 && last < 5) return few
  if (last === 1) return one
  return many
}
const tasksWord = (n) => pluralize(n, 'задача', 'задачи', 'задач')
const goalsWord = (n) => pluralize(n, 'цель', 'цели', 'целе��')

const spheres = computed({
  get: () => [...store.allSpheres].sort((a, b) => a.position - b.position),
  set: (val) => { store.allSpheres = val },
})

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

const handleReorder = () => {
  const ids = spheres.value.map(s => s.id)
  store.reorder(ids)
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

const handleDeleted = () => {
  showCreateModal.value = false
  editingSphere.value = null
}

onMounted(async () => {
  await store.fetchAll()
  startCarousel()
})

onBeforeUnmount(() => {
  stopCarousel()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
