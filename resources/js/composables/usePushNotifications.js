import { ref } from 'vue'
import api from '@/services/api'

const permission = ref(Notification.permission ?? 'default')
const isSubscribed = ref(false)

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4)
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
  const raw = atob(base64)
  const arr = new Uint8Array(raw.length)
  for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i)
  return arr
}

export function usePushNotifications() {
  async function subscribe() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return false

    const result = await Notification.requestPermission()
    permission.value = result
    if (result !== 'granted') return false

    const registration = await navigator.serviceWorker.ready

    // Получаем VAPID ключ с сервера
    const { public_key } = await api.get('/v1/push/vapid-key')

    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(public_key),
    })

    const sub = subscription.toJSON()
    await api.post('/v1/push/subscribe', {
      endpoint: sub.endpoint,
      keys: sub.keys,
    })

    isSubscribed.value = true
    return true
  }

  async function unsubscribe() {
    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.getSubscription()
    if (!subscription) return

    await api.post('/v1/push/unsubscribe', { endpoint: subscription.endpoint })
    await subscription.unsubscribe()
    isSubscribed.value = false
  }

  async function checkSubscription() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return
    permission.value = Notification.permission
    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.getSubscription()
    isSubscribed.value = !!subscription
  }

  return { permission, isSubscribed, subscribe, unsubscribe, checkSubscription }
}
