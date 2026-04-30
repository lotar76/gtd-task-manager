<template>
  <div class="landing">
    <!-- Navbar -->
    <nav class="nav">
      <div class="nav__inner">
        <div class="nav__logo">
          <img :src="logoImg" alt="Крепкая Башня" class="nav__logo-icon" />
          <span>Крепкая Башня</span>
        </div>
        <div class="nav__actions">
          <router-link to="/login" class="nav__link">Войти</router-link>
          <router-link to="/register" class="nav__btn">Начать бесплатно</router-link>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
      <div class="hero__bg">
        <div class="hero__orb hero__orb--1"></div>
        <div class="hero__orb hero__orb--2"></div>
        <div class="hero__orb hero__orb--3"></div>
        <div class="hero__grid"></div>
      </div>

      <div class="hero__inner">
        <div class="hero__content">
          <div class="hero__badge">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1l2.47 4.56L16 6.48l-3.76 3.93L12.94 16 8 13.27 3.06 16l.7-5.59L0 6.48l5.53-.92L8 1z" fill="#f59e0b"/></svg>
            Система продуктивности GTD
          </div>
          <h1 class="hero__title">
            Управляй <span class="hero__title--accent">жизнью</span>,<br>
            а не списком дел
          </h1>
          <p class="hero__subtitle">
            Задачи, проекты, цели и сферы жизни в одном месте. Ясная голова и порядок каждый день.
          </p>
          <div class="hero__buttons">
            <router-link to="/register" class="hero__cta">
              Попробовать бесплатно
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </router-link>
          </div>
          <div class="hero__stats">
            <div class="hero__stat">
              <span class="hero__stat-num">6</span>
              <span class="hero__stat-label">горизонтов планирования</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
              <span class="hero__stat-num">GTD</span>
              <span class="hero__stat-label">методология</span>
            </div>
            <div class="hero__stat-divider"></div>
            <div class="hero__stat">
              <span class="hero__stat-num">24/7</span>
              <span class="hero__stat-label">уведомления</span>
            </div>
          </div>
        </div>

        <div class="hero__visual">
          <!-- Floating cards -->
          <div class="hero__mockup">
            <div class="mockup-card mockup-card--main">
              <div class="mockup-card__header">
                <div class="mockup-card__dot mockup-card__dot--green"></div>
                <span>Сегодня</span>
                <span class="mockup-card__badge">5 задач</span>
              </div>
              <div class="mockup-card__item">
                <div class="mockup-card__check mockup-card__check--done"></div>
                <span class="mockup-card__text mockup-card__text--done">Утренняя пробежка</span>
              </div>
              <div class="mockup-card__item">
                <div class="mockup-card__check mockup-card__check--done"></div>
                <span class="mockup-card__text mockup-card__text--done">Ответить клиенту</span>
              </div>
              <div class="mockup-card__item mockup-card__item--active">
                <div class="mockup-card__check"></div>
                <span class="mockup-card__text">Подготовить презентацию</span>
              </div>
              <div class="mockup-card__item">
                <div class="mockup-card__check"></div>
                <span class="mockup-card__text">Встреча с командой</span>
              </div>
              <div class="mockup-card__item">
                <div class="mockup-card__check"></div>
                <span class="mockup-card__text">Прочитать главу книги</span>
              </div>
            </div>

            <div class="mockup-card mockup-card--float mockup-card--goals">
              <div class="mockup-card__header">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="#8b5cf6" stroke-width="2"/><circle cx="8" cy="8" r="3" fill="#8b5cf6"/></svg>
                <span>Цели</span>
              </div>
              <div class="mockup-card__progress">
                <span>Выучить английский</span>
                <div class="mockup-card__bar"><div class="mockup-card__bar-fill" style="width:72%"></div></div>
                <span class="mockup-card__percent">72%</span>
              </div>
            </div>

            <div class="mockup-card mockup-card--float mockup-card--sphere">
              <div class="mockup-card__header">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 1a7 7 0 110 14A7 7 0 018 1z" stroke="#10b981" stroke-width="2"/><path d="M8 4v4l3 3" stroke="#10b981" stroke-width="2" stroke-linecap="round"/></svg>
                <span>Баланс жизни</span>
              </div>
              <div class="mockup-card__spheres">
                <div class="sphere-item"><span class="sphere-dot sphere-dot--green"></span>Здоровье</div>
                <div class="sphere-item"><span class="sphere-dot sphere-dot--blue"></span>Карьера</div>
                <div class="sphere-item"><span class="sphere-dot sphere-dot--purple"></span>Семья</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
      <div class="section__inner">
        <div class="section__header">
          <span class="section__tag">Возможности</span>
          <h2 class="section__title">Все инструменты для продуктивной жизни</h2>
          <p class="section__desc">Не просто список дел -- полноценная система управления жизнью</p>
        </div>

        <div class="features__grid">
          <div v-for="feature in features" :key="feature.title" class="feature-card">
            <div class="feature-card__icon" :style="{ background: feature.gradient }">
              <div v-html="feature.icon"></div>
            </div>
            <h3 class="feature-card__title">{{ feature.title }}</h3>
            <p class="feature-card__desc">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section class="how">
      <div class="section__inner">
        <div class="section__header">
          <span class="section__tag">Как это работает</span>
          <h2 class="section__title">5 шагов к ясной голове</h2>
          <p class="section__desc">Методология GTD -- Getting Things Done</p>
        </div>

        <div class="how__steps">
          <div v-for="(step, i) in steps" :key="step.title" class="how__step">
            <div class="how__step-num">{{ i + 1 }}</div>
            <div class="how__step-content">
              <h3>{{ step.title }}</h3>
              <p>{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Screenshots -->
    <section class="screens">
      <div class="section__inner">
        <div class="section__header">
          <span class="section__tag">Интерфейс</span>
          <h2 class="section__title">Продуманный до мелочей</h2>
          <p class="section__desc">Адаптивный дизайн для любого устройства. Темная и светлая тема.</p>
        </div>

        <div class="screens__showcase">
          <div class="screen-frame screen-frame--desktop">
            <div class="screen-frame__bar">
              <div class="screen-frame__dots">
                <span></span><span></span><span></span>
              </div>
              <div class="screen-frame__url">skylit.ru</div>
            </div>
            <div class="screen-frame__body">
              <div class="screen-mock">
                <div class="screen-mock__sidebar">
                  <div class="sm-item sm-item--active">Сегодня</div>
                  <div class="sm-item">Входящие</div>
                  <div class="sm-item">Следующие</div>
                  <div class="sm-item">Календарь</div>
                  <div class="sm-item">Проекты</div>
                  <div class="sm-item">Цели</div>
                  <div class="sm-item">Сферы жизни</div>
                </div>
                <div class="screen-mock__main">
                  <div class="sm-title">Сегодня <span>5 задач</span></div>
                  <div class="sm-task"><div class="sm-check sm-check--done"></div><span class="sm-done">Утренняя рутина</span></div>
                  <div class="sm-task"><div class="sm-check sm-check--done"></div><span class="sm-done">Ответить на письма</span></div>
                  <div class="sm-task sm-task--hl"><div class="sm-check"></div><span>Подготовить отчёт</span><span class="sm-tag">Работа</span></div>
                  <div class="sm-task"><div class="sm-check"></div><span>Позвонить врачу</span></div>
                  <div class="sm-task"><div class="sm-check"></div><span>Тренировка</span><span class="sm-tag sm-tag--green">Здоровье</span></div>
                </div>
              </div>
            </div>
          </div>

          <div class="screen-frame screen-frame--phone">
            <div class="screen-frame__notch"></div>
            <div class="screen-frame__body screen-frame__body--phone">
              <div class="screen-mock-phone">
                <div class="smp-header">Календарь</div>
                <div class="smp-day">
                  <span class="smp-date">Пн 28</span>
                  <div class="smp-event smp-event--blue">09:00 Планёрка</div>
                  <div class="smp-event smp-event--green">12:00 Обед с партнером</div>
                  <div class="smp-event smp-event--purple">16:00 Код-ревью</div>
                </div>
                <div class="smp-day">
                  <span class="smp-date">Вт 29</span>
                  <div class="smp-event smp-event--orange">10:00 Презентация</div>
                  <div class="smp-event smp-event--blue">14:00 Встреча</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="reviews">
      <div class="section__inner">
        <div class="section__header">
          <span class="section__tag">Отзывы</span>
          <h2 class="section__title">Что говорят пользователи</h2>
        </div>

        <div class="reviews__grid">
          <div v-for="review in reviews" :key="review.name" class="review-card">
            <div class="review-card__stars">
              <svg v-for="n in 5" :key="n" width="16" height="16" viewBox="0 0 16 16"><path d="M8 1l2.47 4.56L16 6.48l-3.76 3.93L12.94 16 8 13.27 3.06 16l.7-5.59L0 6.48l5.53-.92L8 1z" fill="#f59e0b"/></svg>
            </div>
            <p class="review-card__text">{{ review.text }}</p>
            <div class="review-card__author">
              <div class="review-card__avatar" :style="{ background: review.color }">{{ review.initials }}</div>
              <div>
                <div class="review-card__name">{{ review.name }}</div>
                <div class="review-card__role">{{ review.role }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta">
      <div class="cta__inner">
        <div class="cta__orb cta__orb--1"></div>
        <div class="cta__orb cta__orb--2"></div>
        <h2 class="cta__title">Начни управлять жизнью сегодня</h2>
        <p class="cta__desc">Бесплатно. Без ограничений. Регистрация за 30 секунд.</p>
        <router-link to="/register" class="cta__btn">
          Создать аккаунт
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </router-link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer__inner">
        <span>Крепкая Башня</span>
        <span>skylit.ru</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import logoImg from '@/assets/images/logo_small.png'

const features = [
  {
    icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 2L3 7v10l9 5 9-5V7l-9-5z" stroke="white" stroke-width="2"/><path d="M12 12l9-5M12 12v10M12 12L3 7" stroke="white" stroke-width="2"/></svg>',
    title: 'Входящие',
    description: 'Записывай мысли и задачи мгновенно. Разбирай когда удобно. Голова всегда свободна.',
    gradient: 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
  },
  {
    icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="white" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="white" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="white" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="white" stroke-width="2"/></svg>',
    title: 'Проекты',
    description: 'Группируй задачи, отслеживай прогресс. Ни один проект не затеряется.',
    gradient: 'linear-gradient(135deg, #10b981, #059669)',
  },
  {
    icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="white" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="white" stroke-width="2"/><circle cx="12" cy="12" r="1" fill="white"/></svg>',
    title: 'Цели',
    description: 'Ставь долгосрочные цели, привязывай проекты. Видь как дела ведут к мечте.',
    gradient: 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
  },
  {
    icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="17" rx="2" stroke="white" stroke-width="2"/><path d="M3 9h18M8 2v4M16 2v4" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>',
    title: 'Календарь',
    description: 'Визуальное расписание. День, неделя, месяц. Планируй наглядно.',
    gradient: 'linear-gradient(135deg, #f59e0b, #d97706)',
  },
  {
    icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 2a10 10 0 100 20 10 10 0 000-20z" stroke="white" stroke-width="2"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20" stroke="white" stroke-width="2"/></svg>',
    title: 'Сферы жизни',
    description: 'Баланс между работой, семьёй, здоровьем. Зеркало жизни покажет перекосы.',
    gradient: 'linear-gradient(135deg, #ec4899, #db2777)',
  },
  {
    icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9z" stroke="white" stroke-width="2"/><path d="M13.73 21a2 2 0 01-3.46 0" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>',
    title: 'Уведомления',
    description: 'Push и Telegram. Напоминания о задачах вовремя. Ничего не забудешь.',
    gradient: 'linear-gradient(135deg, #ef4444, #dc2626)',
  },
]

const steps = [
  { title: 'Собери', desc: 'Запиши все задачи, идеи, обязательства во Входящие. Освободи голову.' },
  { title: 'Обработай', desc: 'Определи что это: задача, проект, справочная информация? Что делать дальше?' },
  { title: 'Организуй', desc: 'Распредели по спискам: Сегодня, Следующие, Ожидание, Когда-нибудь.' },
  { title: 'Обзор', desc: 'Еженедельно просматривай все списки. Актуализируй проекты и цели.' },
  { title: 'Действуй', desc: 'Выбирай задачу осознанно по контексту, энергии и приоритету.' },
]

const reviews = [
  {
    name: 'Анастасия Г.',
    role: 'Дизайнер',
    text: 'Наконец-то нашла приложение, где можно вести и рабочие задачи, и личные цели. Сферы жизни -- это находка!',
    initials: 'АГ',
    color: 'linear-gradient(135deg, #ec4899, #8b5cf6)',
  },
  {
    name: 'Никита Т.',
    role: 'Разработчик',
    text: 'Перешёл с Todoist. Здесь GTD реализован правильно -- входящие, проекты, контексты. Все как в книге Аллена.',
    initials: 'НТ',
    color: 'linear-gradient(135deg, #3b82f6, #10b981)',
  },
  {
    name: 'Александр К.',
    role: 'Предприниматель',
    text: 'Календарь и зеркало жизни помогают видеть картину целиком. Рекомендую всем, кто хочет навести порядок.',
    initials: 'АК',
    color: 'linear-gradient(135deg, #f59e0b, #ef4444)',
  },
]
</script>

<style scoped>
/* ===== Base ===== */
.landing { --primary: #6366f1; --primary-dark: #4f46e5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; color: #1e293b; overflow-x: hidden; }

/* ===== Nav ===== */
.nav { position: fixed; top: 0; width: 100%; z-index: 100; background: rgba(255,255,255,.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0,0,0,.06); }
.nav__inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; height: 64px; display: flex; align-items: center; justify-content: space-between; }
.nav__logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #0f172a; }
.nav__logo-icon { width: auto; height: 36px; border-radius: 8px; object-fit: contain; flex-shrink: 0; }
.nav__actions { display: flex; align-items: center; gap: 8px; }
.nav__link { font-size: 14px; font-weight: 500; color: #64748b; padding: 8px 16px; text-decoration: none; transition: color .2s; }
.nav__link:hover { color: #0f172a; }
.nav__btn { font-size: 14px; font-weight: 600; color: white; background: var(--primary); padding: 8px 20px; border-radius: 10px; text-decoration: none; transition: all .2s; }
.nav__btn:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.3); }

/* ===== Hero ===== */
.hero { position: relative; padding: 120px 24px 80px; min-height: 100vh; display: flex; align-items: center; }
.hero__bg { position: absolute; inset: 0; overflow: hidden; }
.hero__orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: .4; animation: float 8s ease-in-out infinite; }
.hero__orb--1 { width: 500px; height: 500px; background: #818cf8; top: -100px; right: -100px; }
.hero__orb--2 { width: 400px; height: 400px; background: #c084fc; bottom: -50px; left: -100px; animation-delay: -3s; }
.hero__orb--3 { width: 300px; height: 300px; background: #38bdf8; top: 50%; left: 50%; animation-delay: -5s; }
.hero__grid { position: absolute; inset: 0; background-image: radial-gradient(circle, #e2e8f0 1px, transparent 1px); background-size: 40px 40px; opacity: .3; }

.hero__inner { position: relative; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
.hero__badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; background: rgba(99,102,241,.08); border: 1px solid rgba(99,102,241,.15); border-radius: 100px; font-size: 13px; font-weight: 500; color: var(--primary); margin-bottom: 24px; }
.hero__title { font-size: clamp(36px, 5vw, 56px); font-weight: 800; line-height: 1.1; letter-spacing: -1.5px; color: #0f172a; margin: 0 0 20px; }
.hero__title--accent { background: linear-gradient(135deg, #6366f1, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.hero__subtitle { font-size: 18px; color: #64748b; line-height: 1.7; margin: 0 0 32px; max-width: 480px; }
.hero__buttons { display: flex; gap: 12px; margin-bottom: 48px; }
.hero__cta { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; background: var(--primary); color: white; font-size: 16px; font-weight: 600; border-radius: 14px; text-decoration: none; transition: all .2s; box-shadow: 0 8px 24px rgba(99,102,241,.3); }
.hero__cta:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 12px 32px rgba(99,102,241,.4); }

.hero__stats { display: flex; align-items: center; gap: 24px; }
.hero__stat { text-align: center; }
.hero__stat-num { display: block; font-size: 24px; font-weight: 800; color: #0f172a; }
.hero__stat-label { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
.hero__stat-divider { width: 1px; height: 32px; background: #e2e8f0; }

/* Hero Mockup */
.hero__visual { position: relative; }
.hero__mockup { position: relative; height: 440px; }

.mockup-card { background: white; border-radius: 16px; padding: 16px; box-shadow: 0 8px 32px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04); border: 1px solid rgba(0,0,0,.06); }
.mockup-card--main { position: absolute; top: 20px; left: 0; right: 40px; animation: cardFloat 6s ease-in-out infinite; }
.mockup-card--float { position: absolute; }
.mockup-card--goals { bottom: 60px; right: 0; width: 220px; animation: cardFloat 6s ease-in-out infinite; animation-delay: -2s; }
.mockup-card--sphere { bottom: 0; left: 20px; width: 200px; animation: cardFloat 6s ease-in-out infinite; animation-delay: -4s; }

.mockup-card__header { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 12px; }
.mockup-card__badge { margin-left: auto; font-size: 11px; font-weight: 500; background: #ede9fe; color: #7c3aed; padding: 2px 8px; border-radius: 100px; }
.mockup-card__dot { width: 8px; height: 8px; border-radius: 50%; }
.mockup-card__dot--green { background: #10b981; }
.mockup-card__item { display: flex; align-items: center; gap: 10px; padding: 8px 0; font-size: 13px; color: #334155; }
.mockup-card__item--active { background: #f1f5f9; margin: 0 -16px; padding: 8px 16px; border-radius: 8px; }
.mockup-card__check { width: 18px; height: 18px; border-radius: 50%; border: 2px solid #cbd5e1; flex-shrink: 0; }
.mockup-card__check--done { border-color: #10b981; background: #10b981; position: relative; }
.mockup-card__check--done::after { content: ''; position: absolute; top: 3px; left: 5px; width: 5px; height: 8px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }
.mockup-card__text--done { text-decoration: line-through; color: #94a3b8; }

.mockup-card__progress { font-size: 12px; color: #475569; }
.mockup-card__bar { height: 6px; background: #e2e8f0; border-radius: 3px; margin: 6px 0 4px; }
.mockup-card__bar-fill { height: 100%; background: linear-gradient(90deg, #8b5cf6, #a855f7); border-radius: 3px; }
.mockup-card__percent { font-weight: 600; color: #8b5cf6; }

.mockup-card__spheres { display: flex; flex-direction: column; gap: 6px; }
.sphere-item { font-size: 12px; color: #475569; display: flex; align-items: center; gap: 6px; }
.sphere-dot { width: 8px; height: 8px; border-radius: 50%; }
.sphere-dot--green { background: #10b981; }
.sphere-dot--blue { background: #3b82f6; }
.sphere-dot--purple { background: #8b5cf6; }

/* ===== Sections shared ===== */
.section__inner { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.section__header { text-align: center; margin-bottom: 60px; }
.section__tag { display: inline-block; font-size: 13px; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; }
.section__title { font-size: clamp(28px, 4vw, 40px); font-weight: 800; color: #0f172a; margin: 0 0 16px; letter-spacing: -.5px; }
.section__desc { font-size: 17px; color: #64748b; max-width: 560px; margin: 0 auto; }

/* ===== Features ===== */
.features { padding: 100px 24px; background: #f8fafc; }
.features__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.feature-card { background: white; border-radius: 20px; padding: 32px; border: 1px solid #e2e8f0; transition: all .3s; }
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,.06); border-color: transparent; }
.feature-card__icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
.feature-card__title { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
.feature-card__desc { font-size: 14px; color: #64748b; line-height: 1.6; margin: 0; }

/* ===== How ===== */
.how { padding: 100px 24px; }
.how__steps { display: flex; gap: 16px; max-width: 900px; margin: 0 auto; }
.how__step { flex: 1; text-align: center; padding: 24px 16px; background: white; border-radius: 16px; border: 1px solid #e2e8f0; position: relative; }
.how__step-num { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #a855f7); color: white; font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.how__step h3 { font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 6px; }
.how__step p { font-size: 13px; color: #64748b; line-height: 1.5; margin: 0; }

/* ===== Screenshots ===== */
.screens { padding: 100px 24px; background: #f8fafc; }
.screens__showcase { display: flex; align-items: flex-start; justify-content: center; gap: 40px; }

.screen-frame--desktop { width: 640px; background: #1e293b; border-radius: 12px; overflow: hidden; box-shadow: 0 24px 48px rgba(0,0,0,.12); }
.screen-frame__bar { display: flex; align-items: center; padding: 10px 16px; background: #0f172a; gap: 12px; }
.screen-frame__dots { display: flex; gap: 6px; }
.screen-frame__dots span { width: 10px; height: 10px; border-radius: 50%; }
.screen-frame__dots span:nth-child(1) { background: #ef4444; }
.screen-frame__dots span:nth-child(2) { background: #f59e0b; }
.screen-frame__dots span:nth-child(3) { background: #22c55e; }
.screen-frame__url { flex: 1; text-align: center; font-size: 12px; color: #94a3b8; background: #1e293b; padding: 4px; border-radius: 6px; }
.screen-frame__body { padding: 0; background: #f1f5f9; }

.screen-mock { display: flex; min-height: 320px; }
.screen-mock__sidebar { width: 160px; background: white; padding: 16px 0; border-right: 1px solid #e2e8f0; }
.sm-item { padding: 8px 16px; font-size: 12px; color: #64748b; cursor: pointer; }
.sm-item--active { color: var(--primary); font-weight: 600; background: #ede9fe; border-right: 2px solid var(--primary); }
.screen-mock__main { flex: 1; padding: 20px; }
.sm-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; }
.sm-title span { font-weight: 400; font-size: 13px; color: #94a3b8; margin-left: 8px; }
.sm-task { display: flex; align-items: center; gap: 10px; padding: 8px 12px; font-size: 13px; color: #334155; border-radius: 8px; margin-bottom: 4px; }
.sm-task--hl { background: #ede9fe; }
.sm-check { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #cbd5e1; flex-shrink: 0; }
.sm-check--done { border-color: #10b981; background: #10b981; }
.sm-done { text-decoration: line-through; color: #94a3b8; }
.sm-tag { font-size: 10px; padding: 2px 8px; border-radius: 100px; background: #ede9fe; color: #7c3aed; margin-left: auto; }
.sm-tag--green { background: #d1fae5; color: #059669; }

.screen-frame--phone { width: 180px; background: #1e293b; border-radius: 24px; padding: 8px; box-shadow: 0 24px 48px rgba(0,0,0,.12); }
.screen-frame__notch { width: 60px; height: 6px; background: #0f172a; border-radius: 3px; margin: 4px auto 8px; }
.screen-frame__body--phone { border-radius: 18px; overflow: hidden; background: white; }
.screen-mock-phone { padding: 12px; min-height: 300px; }
.smp-header { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 12px; text-align: center; }
.smp-day { margin-bottom: 12px; }
.smp-date { font-size: 11px; font-weight: 600; color: #94a3b8; display: block; margin-bottom: 6px; }
.smp-event { font-size: 11px; padding: 6px 8px; border-radius: 6px; margin-bottom: 4px; font-weight: 500; }
.smp-event--blue { background: #dbeafe; color: #1d4ed8; }
.smp-event--green { background: #d1fae5; color: #059669; }
.smp-event--purple { background: #ede9fe; color: #7c3aed; }
.smp-event--orange { background: #ffedd5; color: #c2410c; }

/* ===== Reviews ===== */
.reviews { padding: 100px 24px; }
.reviews__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.review-card { background: white; border-radius: 20px; padding: 32px; border: 1px solid #e2e8f0; }
.review-card__stars { display: flex; gap: 2px; margin-bottom: 16px; }
.review-card__text { font-size: 15px; color: #475569; line-height: 1.7; margin: 0 0 20px; }
.review-card__author { display: flex; align-items: center; gap: 12px; }
.review-card__avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; font-weight: 700; }
.review-card__name { font-size: 14px; font-weight: 600; color: #0f172a; }
.review-card__role { font-size: 12px; color: #94a3b8; }

/* ===== CTA ===== */
.cta { padding: 100px 24px; }
.cta__inner { position: relative; max-width: 800px; margin: 0 auto; text-align: center; padding: 80px 40px; background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7); border-radius: 32px; overflow: hidden; }
.cta__orb { position: absolute; border-radius: 50%; background: rgba(255,255,255,.1); }
.cta__orb--1 { width: 300px; height: 300px; top: -100px; right: -50px; }
.cta__orb--2 { width: 200px; height: 200px; bottom: -60px; left: -30px; }
.cta__title { font-size: clamp(24px, 4vw, 36px); font-weight: 800; color: white; margin: 0 0 16px; position: relative; }
.cta__desc { font-size: 17px; color: rgba(255,255,255,.8); margin: 0 0 32px; position: relative; }
.cta__btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 32px; background: white; color: var(--primary); font-size: 16px; font-weight: 700; border-radius: 14px; text-decoration: none; transition: all .2s; position: relative; }
.cta__btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.2); }

/* ===== Footer ===== */
.footer { padding: 24px; border-top: 1px solid #e2e8f0; }
.footer__inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; font-size: 13px; color: #94a3b8; }

/* ===== Animations ===== */
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
@keyframes cardFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

/* ===== Responsive ===== */
@media (max-width: 1024px) {
  .hero { padding: 100px 16px 60px; min-height: auto; }
  .hero__inner { grid-template-columns: 1fr; text-align: center; gap: 40px; }
  .hero__subtitle { margin-left: auto; margin-right: auto; }
  .hero__buttons { justify-content: center; }
  .hero__stats { justify-content: center; flex-wrap: wrap; }
  .hero__visual { display: none; }
  .features__grid { grid-template-columns: repeat(2, 1fr); }
  .how__steps { flex-wrap: wrap; }
  .how__step { flex: 1 1 calc(50% - 8px); }
  .screens__showcase { flex-direction: column; align-items: center; }
  .screen-frame--desktop { width: 100%; max-width: 640px; }
  .features, .how, .screens, .reviews, .cta { padding: 60px 16px; }
}
@media (max-width: 640px) {
  .hero { padding: 80px 16px 40px; }
  .hero__badge { font-size: 11px; padding: 4px 12px; }
  .hero__title { font-size: 28px; letter-spacing: -1px; }
  .hero__subtitle { font-size: 15px; }
  .hero__cta { padding: 12px 24px; font-size: 15px; width: 100%; justify-content: center; }
  .hero__stats { gap: 16px; }
  .hero__stat-num { font-size: 20px; }
  .hero__stat-label { font-size: 10px; }
  .hero__stat-divider { height: 24px; }
  .nav__inner { padding: 0 16px; }
  .nav__logo span { font-size: 15px; }
  .nav__link { display: none; }
  .nav__btn { font-size: 13px; padding: 6px 14px; }
  .section__header { margin-bottom: 32px; }
  .section__tag { font-size: 11px; }
  .section__title { font-size: 24px; }
  .section__desc { font-size: 14px; }
  .features__grid, .reviews__grid { grid-template-columns: 1fr; gap: 16px; }
  .feature-card { padding: 24px; }
  .how__steps { flex-direction: column; gap: 12px; }
  .how__step { flex: 1 1 100%; padding: 20px 16px; }
  .screen-frame--phone { width: 160px; }
  .screen-mock__sidebar { width: 120px; }
  .sm-item { font-size: 11px; padding: 6px 12px; }
  .review-card { padding: 24px; }
  .review-card__text { font-size: 14px; }
  .cta__inner { padding: 48px 24px; border-radius: 20px; }
  .cta__title { font-size: 22px; }
  .cta__desc { font-size: 14px; }
  .cta__btn { width: 100%; justify-content: center; padding: 12px 24px; font-size: 15px; }
  .footer__inner { flex-direction: column; align-items: center; gap: 4px; }
}
</style>
