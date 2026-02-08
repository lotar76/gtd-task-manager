import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './assets/css/app.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Инициализация темы до монтирования
import { useThemeStore } from './stores/theme'
useThemeStore()

app.mount('#app')


