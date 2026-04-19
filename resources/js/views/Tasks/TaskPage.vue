<template>
  <TaskView
    :show="true"
    :task="task"
    @close="goBack"
    @saved="onSaved"
    @complete-task="onSaved"
    @uncomplete-task="onSaved"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TaskView from '@/components/tasks/TaskView.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const task = ref(null)

const goBack = () => {
  if (window.history.length > 2) {
    router.back()
  } else {
    router.push('/today')
  }
}

const onSaved = () => {}

onMounted(async () => {
  const id = route.params.taskId
  try {
    const res = await api.get(`/v1/tasks/${id}`)
    task.value = res.data || res
  } catch (e) {
    console.error('Failed to load task', e)
    router.push('/today')
  }
})
</script>
