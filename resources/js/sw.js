import { precacheAndRoute } from 'workbox-precaching'
import { registerRoute } from 'workbox-routing'
import { CacheFirst, NetworkFirst, StaleWhileRevalidate } from 'workbox-strategies'
import { ExpirationPlugin } from 'workbox-expiration'

const SW_VERSION = 'v2'
const ALLOWED_CACHES = new Set([
  `static-assets-${SW_VERSION}`,
  `images-${SW_VERSION}`,
  `api-cache-${SW_VERSION}`,
  `pages-${SW_VERSION}`,
])

precacheAndRoute(self.__WB_MANIFEST)

self.addEventListener('install', () => {
  self.skipWaiting()
})

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys()
      await Promise.all(
        keys.filter((key) => !ALLOWED_CACHES.has(key)).map((key) => caches.delete(key))
      )
      await self.clients.claim()
    })()
  )
})

registerRoute(
  ({ request }) => ['style', 'script'].includes(request.destination),
  new NetworkFirst({
    cacheName: `static-assets-${SW_VERSION}`,
    networkTimeoutSeconds: 4,
    plugins: [new ExpirationPlugin({ maxEntries: 60, maxAgeSeconds: 7 * 24 * 60 * 60 })],
  })
)

registerRoute(
  ({ request }) => request.destination === 'font',
  new StaleWhileRevalidate({
    cacheName: `static-assets-${SW_VERSION}`,
    plugins: [new ExpirationPlugin({ maxEntries: 30, maxAgeSeconds: 30 * 24 * 60 * 60 })],
  })
)

registerRoute(
  ({ request }) => request.destination === 'image',
  new CacheFirst({
    cacheName: `images-${SW_VERSION}`,
    plugins: [new ExpirationPlugin({ maxEntries: 60, maxAgeSeconds: 30 * 24 * 60 * 60 })],
  })
)

registerRoute(
  ({ url }) => url.pathname.startsWith('/api/'),
  new NetworkFirst({
    cacheName: `api-cache-${SW_VERSION}`,
    networkTimeoutSeconds: 6,
    plugins: [new ExpirationPlugin({ maxEntries: 100, maxAgeSeconds: 300 })],
  })
)

registerRoute(
  ({ request }) => request.mode === 'navigate',
  new NetworkFirst({
    cacheName: `pages-${SW_VERSION}`,
    networkTimeoutSeconds: 4,
  })
)

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting()
})

// === PUSH NOTIFICATIONS ===

self.addEventListener('push', (event) => {
  if (!event.data) return

  let data
  try {
    data = event.data.json()
  } catch {
    data = { title: 'Крепкая Башня', body: event.data.text() }
  }

  const options = {
    body: data.body || '',
    icon: '/android-chrome-192x192.png',
    badge: '/favicon-96x96.png',
    tag: data.tag || 'default',
    data: {
      url: data.url || '/',
    },
  }

  event.waitUntil(
    self.registration.showNotification(data.title || 'Крепкая Башня', options)
  )
})

// Клик по уведомлению — открыть/фокус на приложении
self.addEventListener('notificationclick', (event) => {
  event.notification.close()

  const url = event.notification.data?.url || '/'

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if (new URL(client.url).pathname === url && 'focus' in client) {
          return client.focus()
        }
      }
      return self.clients.openWindow(url)
    })
  )
})
