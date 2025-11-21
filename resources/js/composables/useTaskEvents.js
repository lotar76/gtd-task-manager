import { ref } from 'vue'

const taskUpdatedEvent = ref(0)

export const useTaskEvents = () => {
  const triggerTaskUpdate = () => {
    taskUpdatedEvent.value++
  }

  const onTaskUpdated = (callback) => {
    return taskUpdatedEvent
  }

  return {
    triggerTaskUpdate,
    onTaskUpdated,
    taskUpdatedEvent,
  }
}

