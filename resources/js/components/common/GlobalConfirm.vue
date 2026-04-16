<template>
  <ConfirmDialog
    :show="confirmStore.state.open"
    :title="confirmStore.state.title"
    :message="confirmStore.state.message"
    :confirm-text="confirmStore.state.confirmText"
    :cancel-text="confirmStore.state.cancelText"
    :variant="confirmStore.state.danger ? 'danger' : 'info'"
    @confirm="confirmStore.resolve(true)"
    @cancel="confirmStore.resolve(false)"
  />
</template>

<script setup>
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import { useConfirmStore } from '@/stores/confirm'

const confirmStore = useConfirmStore()
</script>
