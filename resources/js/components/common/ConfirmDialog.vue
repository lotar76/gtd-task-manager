<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('cancel')">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <!-- Dialog -->
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-auto" @click.stop>

            <!-- Header -->
            <div class="p-6 pb-4">
              <div class="flex items-center space-x-3">
                <!-- Icon -->
                <div
                  class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                  :class="iconBgClass"
                >
                  <svg class="w-6 h-6" :class="iconColorClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>

                <!-- Title -->
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ title }}
                </h3>
              </div>
            </div>

            <!-- Body -->
            <div class="px-6 pb-4">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ message }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg">
              <button
                @click="$emit('cancel')"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
              >
                {{ cancelText }}
              </button>
              <button
                @click="$emit('confirm')"
                class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                :class="confirmButtonClass"
              >
                {{ confirmText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Подтверждение'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Подтвердить'
  },
  cancelText: {
    type: String,
    default: 'Отмена'
  },
  variant: {
    type: String,
    default: 'success', // success, danger, warning, info
    validator: (value) => ['success', 'danger', 'warning', 'info'].includes(value)
  }
})

defineEmits(['confirm', 'cancel'])

const iconBgClass = computed(() => {
  const classes = {
    success: 'bg-green-100 dark:bg-green-900/30',
    danger: 'bg-red-100 dark:bg-red-900/30',
    warning: 'bg-yellow-100 dark:bg-yellow-900/30',
    info: 'bg-blue-100 dark:bg-blue-900/30'
  }
  return classes[props.variant]
})

const iconColorClass = computed(() => {
  const classes = {
    success: 'text-green-600 dark:text-green-400',
    danger: 'text-red-600 dark:text-red-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    info: 'text-blue-600 dark:text-blue-400'
  }
  return classes[props.variant]
})

const confirmButtonClass = computed(() => {
  const classes = {
    success: 'bg-green-600 hover:bg-green-700',
    danger: 'bg-red-600 hover:bg-red-700',
    warning: 'bg-yellow-600 hover:bg-yellow-700',
    info: 'bg-blue-600 hover:bg-blue-700'
  }
  return classes[props.variant]
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
