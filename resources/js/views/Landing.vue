<template>
  <div class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Navbar -->
    <nav class="fixed top-0 w-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-md z-50 border-b border-gray-100 dark:border-gray-800">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <span class="text-xl font-bold text-gray-900 dark:text-white">Крепкая Башня</span>
        <div class="flex items-center gap-3">
          <router-link to="/login" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors px-4 py-2">
            Войти
          </router-link>
          <router-link to="/register" class="text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 px-4 py-2 rounded-lg transition-colors">
            Начать
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
          Управляй жизнью,<br>
          <span class="text-primary-600">а не списком дел</span>
        </h1>
        <p class="mt-6 text-lg sm:text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
          Система личной продуктивности по методологии GTD. Задачи, проекты, цели, сферы жизни -- всё в одном месте.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <router-link to="/register" class="w-full sm:w-auto px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl text-lg transition-colors shadow-lg shadow-primary-600/25">
            Попробовать бесплатно
          </router-link>
          <a href="#features" class="w-full sm:w-auto px-8 py-3.5 text-gray-700 dark:text-gray-300 font-medium rounded-xl text-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            Узнать больше
          </a>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 px-4 bg-gray-50 dark:bg-gray-800/50">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Всё для продуктивности</h2>
          <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Инструменты, которые помогают фокусироваться на главном</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="feature in features" :key="feature.title" class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5" :class="feature.bgClass">
              {{ feature.icon }}
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ feature.title }}</h3>
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- GTD Method -->
    <section class="py-20 px-4">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Методология GTD</h2>
          <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">Getting Things Done -- проверенная система продуктивности</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div v-for="(step, index) in gtdSteps" :key="step.title" class="relative bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 text-center">
            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-bold text-lg flex items-center justify-center mx-auto mb-4">
              {{ index + 1 }}
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ step.title }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Horizons -->
    <section class="py-20 px-4 bg-gray-50 dark:bg-gray-800/50">
      <div class="max-w-4xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">6 горизонтов планирования</h2>
          <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">От ежедневных дел до жизненного предназначения</p>
        </div>

        <div class="space-y-4">
          <div v-for="horizon in horizons" :key="horizon.level" class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold" :class="horizon.colorClass">
              {{ horizon.level }}
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ horizon.title }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ horizon.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-4">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">Начни управлять своей жизнью</h2>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          Бесплатно. Без ограничений. Без скрытых платежей.
        </p>
        <router-link to="/register" class="mt-8 inline-block px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl text-lg transition-colors shadow-lg shadow-primary-600/25">
          Создать аккаунт
        </router-link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 px-4 border-t border-gray-100 dark:border-gray-800">
      <div class="max-w-6xl mx-auto text-center text-sm text-gray-500 dark:text-gray-400">
        Крепкая Башня -- skylit.ru
      </div>
    </footer>
  </div>
</template>

<script setup>
const features = [
  {
    icon: '\u{1F4E5}',
    title: 'Входящие',
    description: 'Быстро записывай всё, что приходит в голову. Разбирай позже, когда будет время.',
    bgClass: 'bg-blue-100 dark:bg-blue-900/30',
  },
  {
    icon: '\u{1F4CB}',
    title: 'Проекты',
    description: 'Группируй задачи в проекты. Отслеживай прогресс и не теряй из виду ни одну инициативу.',
    bgClass: 'bg-green-100 dark:bg-green-900/30',
  },
  {
    icon: '\u{1F3AF}',
    title: 'Цели',
    description: 'Ставь долгосрочные цели и привязывай к ним проекты. Видь как ежедневные дела ведут к мечте.',
    bgClass: 'bg-purple-100 dark:bg-purple-900/30',
  },
  {
    icon: '\u{1F4C5}',
    title: 'Календарь',
    description: 'Визуальное расписание задач. Планируй день, неделю, месяц. Видь загрузку с высоты.',
    bgClass: 'bg-orange-100 dark:bg-orange-900/30',
  },
  {
    icon: '\u{1F310}',
    title: 'Сферы жизни',
    description: 'Баланс между работой, семьёй, здоровьем, развитием. Зеркало жизни покажет перекосы.',
    bgClass: 'bg-teal-100 dark:bg-teal-900/30',
  },
  {
    icon: '\u{1F514}',
    title: 'Уведомления',
    description: 'Push-уведомления и Telegram-бот. Ни одна задача не будет забыта.',
    bgClass: 'bg-red-100 dark:bg-red-900/30',
  },
]

const gtdSteps = [
  { title: 'Сбор', description: 'Записывай всё во Входящие' },
  { title: 'Обработка', description: 'Определи следующее действие' },
  { title: 'Организация', description: 'Распредели по спискам' },
  { title: 'Обзор', description: 'Регулярно просматривай' },
  { title: 'Действие', description: 'Выполняй осознанно' },
]

const horizons = [
  { level: '0', title: 'Текущие дела', description: 'Задачи на сегодня и завтра', colorClass: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' },
  { level: '1', title: 'Проекты', description: 'Результаты, требующие нескольких шагов', colorClass: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' },
  { level: '2', title: 'Сферы ответственности', description: 'Роли и области, за которые отвечаешь', colorClass: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300' },
  { level: '3', title: 'Цели на 1-2 года', description: 'Куда движешься в ближайшем будущем', colorClass: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' },
  { level: '4', title: 'Видение на 3-5 лет', description: 'Каким видишь своё будущее', colorClass: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' },
  { level: '5', title: 'Предназначение', description: 'Смысл и главные принципы жизни', colorClass: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300' },
]
</script>
