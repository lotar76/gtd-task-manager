<template>
  <div class="p-4 lg:p-8 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-2 sm:mb-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Принципы</h1>
      <button
        @click="openCreateModal"
        class="sm:px-4 sm:py-2 p-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg flex items-center space-x-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="hidden sm:inline">Добавить принцип</span>
      </button>
    </div>
    <button
      @click="showInfoModal = true"
      class="text-sm text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors mb-4 sm:mb-6 sm:-mt-4"
    >
      Что такое принципы?
    </button>

    <!-- Info Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showInfoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm overflow-y-auto" @click.self="showInfoModal = false">
          <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden my-6">
            <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-800">
              <span class="text-sm font-medium text-gray-900 dark:text-white">Что такое принципы?</span>
              <button @click="showInfoModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto space-y-5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
              <p class="text-base font-medium text-gray-900 dark:text-white">
                Принципы — это фундамент, на котором строится ваша жизнь и все ваши решения.
              </p>

              <p>
                Это не цели и не задачи. Принципы — это ваши внутренние ориентиры, которые не меняются в зависимости от обстоятельств. Они отвечают на вопрос <strong>«Кто я и как я действую?»</strong>, а не «Чего я хочу достичь?».
              </p>

              <p>
                Когда вы стоите перед сложным выбором, именно принципы подсказывают правильный ответ. Они работают как компас — не говорят, куда конкретно идти, но показывают направление.
              </p>

              <div>
                <p class="font-semibold text-gray-900 dark:text-white mb-2">Примеры принципов:</p>
                <ul class="space-y-1.5 ml-1">
                  <li class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">&#x2022;</span>
                    <span><strong>Честность прежде комфорта</strong> — я говорю правду, даже когда это неудобно</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">&#x2022;</span>
                    <span><strong>Здоровье — приоритет номер один</strong> — без энергии всё остальное теряет смысл</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">&#x2022;</span>
                    <span><strong>Семья важнее карьеры</strong> — при конфликте интересов выбираю близких</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">&#x2022;</span>
                    <span><strong>Делай или не делай, но не жалуйся</strong> — энергию трачу на действия, а не на сожаления</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">&#x2022;</span>
                    <span><strong>Всегда учись</strong> — каждый день узнавать что-то новое</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-primary-500 mt-0.5">&#x2022;</span>
                    <span><strong>Обещал — сделай</strong> — моё слово имеет вес</span>
                  </li>
                </ul>
              </div>

              <div>
                <p class="font-semibold text-gray-900 dark:text-white mb-2">Вопросы, которые помогут определить ваши принципы:</p>
                <ul class="space-y-1.5 ml-1">
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">?</span>
                    <span>Что для меня по-настоящему важно, вне зависимости от обстоятельств?</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">?</span>
                    <span>Какие ценности я не готов нарушить ни при каких условиях?</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">?</span>
                    <span>Каким человеком я хочу быть? Как бы я хотел, чтобы меня описывали?</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">?</span>
                    <span>Когда я чувствовал себя по-настоящему «на своём месте» — какие ценности я в тот момент проживал?</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">?</span>
                    <span>Какое поведение других людей вызывает у меня уважение? А какое — отторжение?</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="text-amber-500 mt-0.5">?</span>
                    <span>Если бы мой ребёнок перенял только 5 моих качеств — какие бы я выбрал?</span>
                  </li>
                </ul>
              </div>

              <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                <p class="font-semibold text-gray-900 dark:text-white mb-1.5">Как работать с принципами</p>
                <p>
                  Не пытайтесь написать идеальный список сразу. Начните с 3–5 принципов, которые резонируют прямо сейчас. Возвращайтесь к ним раз в месяц — уточняйте формулировки, добавляйте новые, убирайте те, что перестали отражать вас настоящего.
                </p>
                <p class="mt-2">
                  Расположите принципы в порядке приоритета — перетаскивайте их, чтобы самые важные были наверху. Когда два принципа конфликтуют, побеждает тот, что выше в списке.
                </p>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Principles List -->
    <draggable
      v-if="principles.length > 0"
      v-model="principles"
      item-key="id"
      handle=".drag-handle"
      ghost-class="opacity-30"
      animation="200"
      @end="handleReorder"
      class="space-y-2"
    >
      <template #item="{ element, index }">
        <div
          class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md transition-all duration-200"
        >
          <div class="flex items-start gap-3 p-4">
            <!-- Drag handle -->
            <div class="drag-handle cursor-grab active:cursor-grabbing mt-1 text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
              </svg>
            </div>

            <!-- Number -->
            <span class="text-sm font-bold text-gray-300 dark:text-gray-600 mt-0.5 select-none w-6 text-right flex-shrink-0">
              {{ index + 1 }}
            </span>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-snug">
                {{ element.name }}
              </h3>
              <p v-if="element.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                {{ element.description }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1 flex-shrink-0" :class="deletingId === element.id ? '' : 'opacity-0 group-hover:opacity-100 transition-opacity'">
              <template v-if="deletingId === element.id">
                <span class="text-xs text-red-500 mr-1">Удалить?</span>
                <button
                  @click="doDelete(element)"
                  class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                  title="Да"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                <button
                  @click="deletingId = null"
                  class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                  title="Отмена"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </template>
              <template v-else>
                <button
                  @click="openEditModal(element)"
                  class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                  title="Редактировать"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <button
                  @click="handleDelete(element)"
                  class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                  title="Удалить"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </template>
            </div>
          </div>
        </div>
      </template>
    </draggable>

    <!-- Empty State -->
    <div v-else-if="!store.loading" class="text-center py-16">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Принципы не добавлены</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto leading-relaxed">
        Запишите свои ключевые принципы — они помогут принимать решения и оставаться на верном пути.
      </p>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors"
      >
        Добавить принцип
      </button>
    </div>

    <!-- Modal -->
    <PrincipleModal
      :show="showModal"
      :principle="editingPrinciple"
      @close="handleCloseModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import draggable from 'vuedraggable'
import { usePrinciplesStore } from '@/stores/principles'
import PrincipleModal from '@/components/principles/PrincipleModal.vue'

const store = usePrinciplesStore()

const showModal = ref(false)
const showInfoModal = ref(false)
const editingPrinciple = ref(null)
const deletingId = ref(null)

const principles = computed({
  get: () => [...store.allPrinciples].sort((a, b) => a.position - b.position),
  set: (val) => {
    store.allPrinciples = val
  },
})

const openCreateModal = () => {
  editingPrinciple.value = null
  showModal.value = true
}

const openEditModal = (principle) => {
  editingPrinciple.value = principle
  showModal.value = true
}

const handleCloseModal = () => {
  showModal.value = false
  editingPrinciple.value = null
}

const handleSaved = () => {
  showModal.value = false
  editingPrinciple.value = null
  store.fetchAll({ force: true })
}

const handleReorder = () => {
  const ids = principles.value.map(p => p.id)
  store.reorder(ids)
}

const handleDelete = (principle) => {
  deletingId.value = principle.id
}

const doDelete = async (principle) => {
  await store.remove(principle.id)
  deletingId.value = null
}

onMounted(async () => {
  await store.fetchAll()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
