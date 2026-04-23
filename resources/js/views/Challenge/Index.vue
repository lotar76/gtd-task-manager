<template>
  <div class="p-4 sm:p-6 max-w-full">
    <!-- Header -->
    <div class="flex items-center justify-center lg:justify-between mb-6 relative">
      <div class="flex items-center space-x-3">
        <button
          @click="prevMonth"
          class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
        >
          <ChevronLeftIcon class="w-5 h-5" />
        </button>
        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize">
          {{ monthLabel }}
        </h1>
        <button
          @click="nextMonth"
          class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors"
        >
          <ChevronRightIcon class="w-5 h-5" />
        </button>
      </div>
      <button
        @click="showCreateModal = true"
        class="absolute right-0 p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 transition-colors lg:static"
      >
        <PlusIcon class="w-5 h-5" />
      </button>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
    </div>

    <!-- ========== MOBILE: карточки ========== -->
    <draggable
      v-if="!store.loading && store.challenges.length > 0"
      v-model="store.challenges"
      tag="div"
      item-key="id"
      :delay="500"
      :delay-on-touch-only="true"
      class="block lg:hidden grid grid-cols-2 gap-2"
      @end="onDragEnd"
    >
      <template #item="{ element: challenge }">
      <div
        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/70 active:scale-[0.97] transition-transform relative p-3"
        @click="toggleToday(challenge)"
      >
        <!-- Menu -->
        <button
          class="absolute top-2 right-2 p-1 text-gray-300 dark:text-gray-600"
          @click.stop="toggleMobileMenu(challenge.id, $event)"
        >
          <EllipsisVerticalIcon class="w-4 h-4" />
        </button>
        <!-- Status icon -->
        <div
          class="w-12 h-12 rounded-xl flex items-center justify-center transition-all mb-2"
          :class="isTodayCompleted(challenge)
            ? 'bg-emerald-500 text-white'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500'"
        >
          <CheckIcon v-if="isTodayCompleted(challenge)" class="w-6 h-6 stroke-[3]" />
          <PlayIcon v-else-if="challenge.type === 'timer'" class="w-6 h-6" />
          <span v-else-if="challenge.type === 'composite'" class="text-xs font-bold">{{ compositeProgress(challenge) }}</span>
          <DocumentTextIcon v-else-if="challenge.type === 'report'" class="w-6 h-6" />
          <span v-else-if="challenge.type === 'progressive' && challenge.progress_sets" class="text-xs font-bold">{{ progressSetsProgress(challenge) }}</span>
          <span v-else-if="challenge.type === 'progressive'" class="text-xs font-bold">{{ progressTarget(challenge, todayDay.value) }}</span>
          <CheckIcon v-else class="w-6 h-6" />
        </div>
        <!-- Title -->
        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-tight pr-5 line-clamp-2">
          {{ challenge.title }}
        </p>
      </div>
      </template>
    </draggable>

    <!-- ========== DESKTOP: таблица ========== -->
    <div v-if="!store.loading && store.challenges.length > 0" class="hidden lg:block overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-800/50">
      <table class="min-w-full border-collapse">
        <thead>
          <!-- Chart row -->
          <tr>
            <th class="sticky left-0 z-10 bg-white dark:bg-gray-900 border-none min-w-[180px]"></th>
            <th
              v-for="day in daysInMonth"
              :key="'chart-'+day"
              class="px-[2px] py-0 bg-white dark:bg-gray-900 w-9 min-w-[36px] align-bottom border-none"
            >
              <div class="flex items-end justify-center h-12 pt-2">
                <div
                  v-if="dateStr(day) <= todayStr && dayStats(day).active > 0"
                  class="w-[28px] h-[44px] rounded-t-sm bg-gray-200/50 dark:bg-gray-700/30 relative overflow-hidden"
                >
                  <div
                    class="absolute bottom-0 left-0 right-0 rounded-t-sm bg-emerald-400/60 dark:bg-emerald-500/40"
                    :style="{ height: dayChartFillPercent(day) + '%' }"
                  />
                </div>
              </div>
            </th>
          </tr>
          <!-- Day headers -->
          <tr>
            <th class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-3 py-1 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-r border-gray-100 dark:border-gray-800/50 min-w-[180px]">
              <div class="flex items-center justify-between">
                <span>Привычка</span>
                <button @click="showCreateModal = true" class="p-0.5 text-gray-400 hover:text-primary-500 transition-colors">
                  <PlusIcon class="w-3.5 h-3.5" />
                </button>
              </div>
            </th>
            <th
              v-for="day in daysInMonth"
              :key="day"
              class="px-0 py-0.5 text-center text-[10px] font-medium border-b border-gray-100 dark:border-gray-800 w-9 min-w-[36px] bg-white dark:bg-gray-900"
            >
              <div class="text-gray-400 dark:text-gray-500">{{ dayOfWeekShort(day) }}</div>
              <div
                class="w-7 h-7 flex items-center justify-center rounded-full mx-auto text-xs font-semibold"
                :class="isToday(day)
                  ? 'bg-primary-500 text-white'
                  : isWeekend(day)
                    ? 'text-gray-400 dark:text-gray-500'
                    : 'text-gray-500 dark:text-gray-400'"
              >{{ day }}</div>
            </th>
          </tr>
        </thead>
        <draggable
          v-model="store.challenges"
          tag="tbody"
          item-key="id"
          handle=".drag-handle"
          @end="onDragEnd"
        >
          <template #item="{ element: challenge }">
          <tr class="group">
            <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 border-b border-r border-gray-100 dark:border-gray-800/50 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50">
              <div class="flex items-center justify-between">
                <div class="flex items-center min-w-0 flex-1">
                  <Bars3Icon class="drag-handle w-4 h-4 text-gray-300 dark:text-gray-600 cursor-grab mr-2 flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity" />
                  <component
                    :is="typeIcon(challenge.type)"
                    v-if="typeIcon(challenge.type)"
                    class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 mr-1.5 flex-shrink-0"
                  />
                  <div class="min-w-0 flex-1">
                    <span class="cursor-default truncate block">{{ challenge.title }}</span>
                  </div>
                </div>
                <div class="flex items-center ml-2">
                  <button
                    @click="openEditModal(challenge)"
                    class="opacity-0 group-hover:opacity-100 p-0.5 text-gray-400 hover:text-primary-500 transition-all"
                  >
                    <PencilIcon class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="removeChallenge(challenge)"
                    class="opacity-0 group-hover:opacity-100 ml-1 p-0.5 text-gray-400 hover:text-red-500 transition-all"
                  >
                    <XMarkIcon class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            </td>
            <td
              v-for="day in daysInMonth"
              :key="day"
              class="px-0 py-0 text-center border-b border-gray-100 dark:border-gray-800/50 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50"
              :class="isToday(day) ? 'cursor-pointer' : 'cursor-default'"
              @click="isToday(day) && handleCellClick(challenge, day)"
            >
              <div
                class="w-8 h-8 flex items-center justify-center rounded-full mx-auto my-1"
                :class="cellClass(challenge, day)"
              >
                <CheckIcon v-if="isDayCompleted(challenge, day)" class="w-4 h-4" />
                <template v-else-if="isToday(day) && !isDayCompleted(challenge, day)">
                  <PlayIcon v-if="challenge.type === 'timer'" class="w-4 h-4 text-primary-500" />
                  <span v-else-if="challenge.type === 'composite'" class="text-[9px] font-medium text-primary-500">
                    {{ compositeProgress(challenge) }}
                  </span>
                  <DocumentTextIcon v-else-if="challenge.type === 'report'" class="w-4 h-4 text-primary-500" />
                  <span v-else-if="challenge.type === 'progressive' && challenge.progress_sets" class="text-[9px] font-medium text-primary-500">
                    {{ progressSetsProgress(challenge) }}
                  </span>
                  <span v-else-if="challenge.type === 'progressive'" class="text-[9px] font-medium text-primary-500">
                    {{ progressTarget(challenge, day) }}
                  </span>
                </template>
                <XMarkIcon v-else-if="isMissed(day) && isAfterStart(challenge, day)" class="w-3 h-3 text-red-400 dark:text-red-500" />
                <XMarkIcon v-else-if="isMissed(day) && !isAfterStart(challenge, day)" class="w-3 h-3 text-gray-300 dark:text-gray-600" />
              </div>
            </td>
          </tr>
          </template>
        </draggable>
      </table>
    </div>

    <!-- Empty state -->
    <div
      v-if="!store.loading && store.challenges.length === 0"
      class="text-center py-12 text-gray-500 dark:text-gray-400"
    >
      <TableCellsIcon class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
      <p class="text-sm">Добавь первую привычку для отслеживания</p>
      <button
        @click="showCreateModal = true"
        class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-primary-500 text-white text-sm font-medium hover:bg-primary-600 transition-colors"
      >
        <PlusIcon class="w-4 h-4 mr-2" />
        Создать привычку
      </button>
    </div>

    <!-- Create modal -->
    <Teleport to="body">
      <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="closeCreateModal" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Новая привычка</h3>

          <!-- Type selector -->
          <div class="grid grid-cols-6 gap-2 mb-4">
            <button
              v-for="(t, idx) in challengeTypes"
              :key="t.value"
              @click="newType = t.value"
              class="px-3 py-2 rounded-lg border text-xs font-medium transition-all text-center"
              :class="[
                newType === t.value
                  ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400'
                  : 'border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-gray-300',
                idx < 3 ? 'col-span-2' : 'col-span-3',
              ]"
            >
              <div class="h-6 flex items-center justify-center mb-0.5">
                <component v-if="t.component" :is="t.component" class="w-5 h-5" />
                <span v-else class="text-base">{{ t.icon }}</span>
              </div>
              {{ t.label }}
            </button>
          </div>

          <!-- Type hint -->
          <p class="text-xs text-gray-400 dark:text-gray-500 mb-3 -mt-2">
            {{ challengeTypes.find(t => t.value === newType)?.hint }}
          </p>

          <!-- Title -->
          <input
            ref="createInput"
            v-model="newTitle"
            @keydown.enter="addChallenge"
            @keydown.escape="closeCreateModal"
            placeholder="Название привычки..."
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
          />

          <!-- Timer: minutes -->
          <div v-if="newType === 'timer'" class="mt-3">
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Длительность (минуты)</label>
            <input
              v-model.number="newTimerMinutes"
              type="number"
              min="1"
              max="480"
              placeholder="25"
              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
            />
          </div>

          <!-- Composite: subtasks -->
          <div v-if="newType === 'composite'" class="mt-3">
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Подзадачи</label>
            <div v-for="(st, idx) in newSubtasks" :key="idx" class="flex items-center mb-2">
              <input
                v-model="newSubtasks[idx]"
                :placeholder="`Подзадача ${idx + 1}`"
                class="flex-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500"
              />
              <button
                v-if="newSubtasks.length > 1"
                @click="newSubtasks.splice(idx, 1)"
                class="ml-2 text-gray-400 hover:text-red-500"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
            <button
              @click="newSubtasks.push('')"
              class="text-xs text-primary-500 hover:text-primary-600 flex items-center"
            >
              <PlusIcon class="w-3 h-3 mr-1" /> Добавить
            </button>
          </div>

          <!-- Progressive: start, step, end, sets -->
          <div v-if="newType === 'progressive'" class="mt-3 space-y-3">
            <div class="grid grid-cols-3 gap-2">
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Начало</label>
                <input
                  v-model.number="newProgressStart"
                  type="number"
                  min="1"
                  placeholder="10"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                />
              </div>
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Шаг</label>
                <input
                  v-model.number="newProgressStep"
                  type="number"
                  min="1"
                  placeholder="5"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                />
              </div>
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Цель</label>
                <input
                  v-model.number="newProgressEnd"
                  type="number"
                  min="1"
                  placeholder="50"
                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                />
              </div>
            </div>
            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Подходы (необязательно)</label>
              <input
                v-model.number="newProgressSets"
                type="number"
                min="2"
                max="10"
                placeholder="без разбивки"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
              />
            </div>
          </div>

          <div class="mt-4 flex justify-end space-x-2">
            <button
              @click="closeCreateModal"
              class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
            >
              Отмена
            </button>
            <button
              @click="addChallenge"
              :disabled="!canCreate || creating"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
            >
              <div v-if="creating" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
              Создать
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Timer modal -->
    <Teleport to="body">
      <div v-if="timerModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-xs p-6 text-center">
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ timerModal.challenge?.title }}</h3>
          <div class="text-5xl font-mono font-bold text-gray-900 dark:text-gray-100 my-6">
            {{ formatTimer(timerModal.remaining) }}
          </div>
          <div v-if="!timerModal.finished" class="flex justify-center space-x-3">
            <button
              v-if="!timerModal.running"
              @click="startTimer"
              class="px-5 py-2.5 rounded-lg bg-primary-500 text-white font-medium hover:bg-primary-600 transition-colors"
            >
              Старт
            </button>
            <button
              v-else
              @click="pauseTimer"
              class="px-5 py-2.5 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition-colors"
            >
              Пауза
            </button>
            <button
              @click="cancelTimer"
              class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Отмена
            </button>
          </div>
          <button
            v-if="!timerModal.finished"
            @click="confirmTimer"
            class="mt-3 text-sm text-gray-400 dark:text-gray-500 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors"
          >
            Отметить выполненным
          </button>
          <div v-else class="space-y-3">
            <p class="text-emerald-500 font-medium">Время вышло!</p>
            <button
              @click="confirmTimer"
              class="w-full px-5 py-2.5 rounded-lg bg-emerald-500 text-white font-medium hover:bg-emerald-600 transition-colors"
            >
              Подтвердить выполнение
            </button>
            <button
              @click="cancelTimer"
              class="w-full px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Не выполнил
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Composite modal -->
    <Teleport to="body">
      <div v-if="compositeModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="compositeModal.open = false" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-xs p-6">
          <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">{{ compositeModal.challenge?.title }}</h3>
          <div class="space-y-2">
            <button
              v-for="(st, idx) in compositeModal.challenge?.subtasks"
              :key="idx"
              @click="toggleSubtask(idx)"
              class="w-full flex items-center px-3 py-2 rounded-lg border transition-all text-sm text-left"
              :class="compositeModal.states[idx]
                ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-gray-300'"
            >
              <CheckIcon v-if="compositeModal.states[idx]" class="w-4 h-4 mr-2 text-emerald-500" />
              <div v-else class="w-4 h-4 mr-2 rounded border border-gray-300 dark:border-gray-500 flex-shrink-0" />
              {{ st }}
            </button>
          </div>
          <button
            @click="compositeModal.open = false"
            class="mt-4 w-full px-4 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
          >
            Закрыть
          </button>
        </div>
      </div>
    </Teleport>

    <!-- Report modal -->
    <Teleport to="body">
      <div v-if="reportModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="reportModal.open = false" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
          <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">{{ reportModal.challenge?.title }}</h3>
          <textarea
            v-model="reportModal.text"
            rows="4"
            placeholder="Напиши свой отчёт..."
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none"
          />
          <div class="mt-4 flex justify-between">
            <button
              v-if="reportModal.text.trim()"
              @click="reportModal.text = ''"
              class="px-4 py-2 text-sm text-red-500 hover:text-red-600 transition-colors"
            >
              Очистить
            </button>
            <div v-else />
            <div class="flex space-x-2">
              <button
                @click="reportModal.open = false"
                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
              >
                Отмена
              </button>
              <button
                @click="saveReport"
                :disabled="reportModal.saving"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
              >
                <div v-if="reportModal.saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
                Сохранить
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Mobile context menu -->
    <Teleport to="body">
      <div v-if="mobileMenuId !== null" class="fixed inset-0 z-50" @click="mobileMenuId = null">
        <div
          class="absolute bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 py-1 w-48"
          :style="mobileMenuPos"
          @click.stop
        >
          <button
            @click="openEditModal(mobileMenuChallenge); mobileMenuId = null"
            class="w-full px-4 py-3 text-left text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 flex items-center space-x-2"
          >
            <PencilIcon class="w-4 h-4" />
            <span>Редактировать</span>
          </button>
          <button
            @click="removeMobile(mobileMenuChallenge)"
            class="w-full px-4 py-3 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center space-x-2"
          >
            <TrashIcon class="w-4 h-4" />
            <span>Удалить</span>
          </button>
        </div>
      </div>
    </Teleport>

    <!-- Edit modal -->
    <Teleport to="body">
      <div v-if="editModal.open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="editModal.open = false" />
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Редактировать привычку</h3>

          <!-- Type badge (read-only) -->
          <div class="mb-3">
            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
              {{ challengeTypes.find(t => t.value === editModal.type)?.label || 'Обычная' }}
            </span>
          </div>

          <!-- Title -->
          <input
            ref="editModalInput"
            v-model="editModal.title"
            @keydown.enter="saveEditModal"
            @keydown.escape="editModal.open = false"
            placeholder="Название..."
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
          />

          <!-- Timer: minutes -->
          <div v-if="editModal.type === 'timer'" class="mt-3">
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Длительность (минуты)</label>
            <input
              v-model.number="editModal.timerMinutes"
              type="number"
              min="1"
              max="480"
              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
            />
          </div>

          <!-- Composite: subtasks -->
          <div v-if="editModal.type === 'composite'" class="mt-3">
            <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Подзадачи</label>
            <div v-for="(st, idx) in editModal.subtasks" :key="idx" class="flex items-center mb-2">
              <input
                v-model="editModal.subtasks[idx]"
                :placeholder="`Подзадача ${idx + 1}`"
                class="flex-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500"
              />
              <button
                v-if="editModal.subtasks.length > 1"
                @click="editModal.subtasks.splice(idx, 1)"
                class="ml-2 text-gray-400 hover:text-red-500"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
            <button
              @click="editModal.subtasks.push('')"
              class="text-xs text-primary-500 hover:text-primary-600 flex items-center"
            >
              <PlusIcon class="w-3 h-3 mr-1" /> Добавить
            </button>
          </div>

          <!-- Progressive: edit fields -->
          <div v-if="editModal.type === 'progressive'" class="mt-3 space-y-3">
            <div class="grid grid-cols-3 gap-2">
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Начало</label>
                <input v-model.number="editModal.progressStart" type="number" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
              </div>
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Шаг</label>
                <input v-model.number="editModal.progressStep" type="number" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
              </div>
              <div>
                <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Цель</label>
                <input v-model.number="editModal.progressEnd" type="number" min="1" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
              </div>
            </div>
            <div>
              <label class="text-xs text-gray-500 dark:text-gray-400 mb-1 block">Подходы (необязательно)</label>
              <input v-model.number="editModal.progressSets" type="number" min="2" max="10" placeholder="без разбивки" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500" />
            </div>
          </div>

          <div class="mt-4 flex justify-end space-x-2">
            <button
              @click="editModal.open = false"
              class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
            >
              Отмена
            </button>
            <button
              @click="saveEditModal"
              :disabled="!editModal.title.trim() || saving"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
            >
              <div v-if="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2" />
              Сохранить
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import draggable from 'vuedraggable'
import { useChallengesStore } from '@/stores/challenges'
import { useConfirmStore } from '@/stores/confirm'
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  CheckIcon,
  XMarkIcon,
  PlusIcon,
  TableCellsIcon,
  EllipsisVerticalIcon,
  PencilIcon,
  TrashIcon,
  Bars3Icon,
  PlayIcon,
  ListBulletIcon,
  DocumentTextIcon,
  ClockIcon,
  ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline'

const store = useChallengesStore()
const confirmStore = useConfirmStore()

const now = new Date()
const year = ref(now.getFullYear())
const month = ref(now.getMonth())

const newTitle = ref('')
const editModalInput = ref(null)
const editModal = ref({ open: false, id: null, title: '', type: 'checkbox', timerMinutes: 25, subtasks: [] })

const mobileMenuId = ref(null)
const saving = ref(false)
const showCreateModal = ref(false)
const creating = ref(false)
const createInput = ref(null)
const newType = ref('checkbox')
const newTimerMinutes = ref(25)
const newSubtasks = ref(['', ''])

const challengeTypes = [
  { value: 'checkbox', label: 'Обычная', icon: '✓', component: null, hint: 'Простая ежедневная привычка. Нажми — выполнено.' },
  { value: 'timer', label: 'Таймер', icon: null, component: ClockIcon, hint: 'Привычка с таймером. Запускаешь обратный отсчёт или отмечаешь сразу.' },
  { value: 'composite', label: 'Составная', icon: '☰', component: null, hint: 'Привычка из нескольких шагов. Все шаги выполнены — день засчитан.' },
  { value: 'report', label: 'Отчёт', icon: null, component: DocumentTextIcon, hint: 'Привычка с текстовым отчётом. Напиши что сделал — и день засчитан.' },
  { value: 'progressive', label: 'Прогресс', icon: null, component: ArrowTrendingUpIcon, hint: 'Нагрузка растёт каждый день. Задай начало, шаг и цель. Можно разбить на подходы.' },
]

const typeIcons = {
  checkbox: CheckIcon,
  timer: ClockIcon,
  composite: ListBulletIcon,
  report: DocumentTextIcon,
  progressive: ArrowTrendingUpIcon,
}

function typeIcon(type) {
  return typeIcons[type] || null
}

const newProgressStart = ref(10)
const newProgressStep = ref(5)
const newProgressEnd = ref(50)
const newProgressSets = ref(null)

const canCreate = computed(() => {
  if (!newTitle.value.trim()) return false
  if (newType.value === 'timer' && (!newTimerMinutes.value || newTimerMinutes.value < 1)) return false
  if (newType.value === 'composite' && newSubtasks.value.filter(s => s.trim()).length < 1) return false
  if (newType.value === 'progressive' && (!newProgressStart.value || !newProgressStep.value || !newProgressEnd.value)) return false
  return true
})

// Timer modal state
const timerModal = ref({ open: false, challenge: null, remaining: 0, running: false, finished: false })
let timerInterval = null

// Composite modal state
const compositeModal = ref({ open: false, challenge: null, states: [] })

// Report modal state
const reportModal = ref({ open: false, challenge: null, text: '', saving: false })

const SHORT_DAYS = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб']

const monthKey = computed(() => {
  const m = String(month.value + 1).padStart(2, '0')
  return `${year.value}-${m}`
})

const monthLabel = computed(() => {
  const date = new Date(year.value, month.value, 1)
  return date.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
})

const daysInMonth = computed(() => {
  return new Date(year.value, month.value + 1, 0).getDate()
})

const todayStr = computed(() => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
})

const todayDay = computed(() => {
  const d = new Date()
  if (d.getFullYear() === year.value && d.getMonth() === month.value) {
    return d.getDate()
  }
  return 0
})

function dateStr(day) {
  const m = String(month.value + 1).padStart(2, '0')
  const d = String(day).padStart(2, '0')
  return `${year.value}-${m}-${d}`
}

function isToday(day) {
  return dateStr(day) === todayStr.value
}

function isMissed(day) {
  return dateStr(day) < todayStr.value
}

function challengeStartDate(challenge) {
  if (!challenge.created_at) return null
  return challenge.created_at.substring(0, 10)
}

function isAfterStart(challenge, day) {
  const start = challengeStartDate(challenge)
  if (!start) return true
  return dateStr(day) >= start
}

function isWeekend(day) {
  const d = new Date(year.value, month.value, day)
  return d.getDay() === 0 || d.getDay() === 6
}

function dayOfWeekShort(day) {
  const d = new Date(year.value, month.value, day)
  return SHORT_DAYS[d.getDay()]
}

function isDayCompleted(challenge, day) {
  const ds = dateStr(day)
  return challenge.entries?.some(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === ds && e.completed
  })
}

function isTodayCompleted(challenge) {
  return isDayCompleted(challenge, todayDay.value)
}

function cellClass(challenge, day) {
  const completed = isDayCompleted(challenge, day)
  const today = isToday(day)
  const afterStart = isAfterStart(challenge, day)
  const missed = isMissed(day)

  if (!afterStart) {
    return 'bg-gray-50 dark:bg-gray-800/30 text-gray-300 dark:text-gray-700'
  }
  if (completed) {
    return 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400'
  }
  if (today) {
    return 'bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30'
  }
  if (missed) {
    return 'bg-red-50 dark:bg-red-900/20 text-red-400 dark:text-red-500'
  }
  return 'bg-gray-50 dark:bg-gray-800/30 text-gray-300 dark:text-gray-600'
}

function completionPercent(challenge) {
  const start = challengeStartDate(challenge) || dateStr(1)
  // Считаем total как количество дней от старта до конца месяца
  let total = 0
  let completed = 0
  for (let d = 1; d <= daysInMonth.value; d++) {
    const ds = dateStr(d)
    if (ds >= start) {
      total++
      if (isDayCompleted(challenge, d)) completed++
    }
  }
  if (total === 0) return 0
  return Math.round((completed / total) * 100)
}

function statsBar(challenge) {
  const start = challengeStartDate(challenge) || dateStr(1)
  let total = 0, completed = 0, missed = 0
  for (let d = 1; d <= daysInMonth.value; d++) {
    const ds = dateStr(d)
    if (ds < start) continue
    total++
    if (isDayCompleted(challenge, d)) completed++
    else if (ds < todayStr.value) missed++
  }
  if (total === 0) return { green: 0, red: 0 }
  return {
    green: Math.round((completed / total) * 100),
    red: Math.round((missed / total) * 100),
  }
}

function statsBarBg(challenge) {
  const s = statsBar(challenge)
  const g = s.green
  const r = s.red
  const mid = 100 - r
  return {
    background: `linear-gradient(to right, #10b981 ${Math.max(g - 10, 0)}%, #6b7280 ${g + 10}%, #6b7280 ${mid - 10}%, #f87171 ${Math.min(mid + 10, 100)}%)`,
  }
}


function progressTarget(challenge, day) {
  const start = challenge.progress_start || 0
  const step = challenge.progress_step || 0
  const end = challenge.progress_end || start
  const created = challengeStartDate(challenge)
  if (!created) return start
  const dayDate = dateStr(day)
  const diffMs = new Date(dayDate) - new Date(created)
  const dayIndex = Math.max(0, Math.floor(diffMs / 86400000))
  return Math.min(start + dayIndex * step, end)
}

function progressSetsArray(challenge, day) {
  const sets = challenge.progress_sets
  if (!sets) return null
  const total = progressTarget(challenge, day)
  const base = Math.floor(total / sets)
  const remainder = total % sets
  return Array.from({ length: sets }, (_, i) => i < remainder ? base + 1 : base)
}

function progressSetsProgress(challenge) {
  const entry = challenge.entries?.find(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === todayStr.value
  })
  const total = challenge.progress_sets || 0
  const done = entry?.subtask_states?.filter(Boolean).length || 0
  return `${done}/${total}`
}

function compositeProgress(challenge) {
  const entry = challenge.entries?.find(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === todayStr.value
  })
  const total = challenge.subtasks?.length || 0
  const done = entry?.subtask_states?.filter(Boolean).length || 0
  return `${done}/${total}`
}

function dayStats(day) {
  let active = 0, completed = 0
  for (const ch of store.challenges) {
    if (!isAfterStart(ch, day)) continue
    active++
    if (isDayCompleted(ch, day)) completed++
  }
  return { active, completed }
}

function dayChartFillPercent(day) {
  const { active, completed } = dayStats(day)
  if (active === 0) return 0
  return Math.round((completed / active) * 100)
}

function percentClass(pct) {
  if (pct >= 80) return 'text-emerald-600 dark:text-emerald-400'
  if (pct >= 50) return 'text-amber-600 dark:text-amber-400'
  if (pct > 0) return 'text-orange-500 dark:text-orange-400'
  return 'text-gray-400 dark:text-gray-500'
}

function prevMonth() {
  if (month.value === 0) {
    month.value = 11
    year.value--
  } else {
    month.value--
  }
  store.fetchChallenges(monthKey.value)
}

function nextMonth() {
  if (month.value === 11) {
    month.value = 0
    year.value++
  } else {
    month.value++
  }
  store.fetchChallenges(monthKey.value)
}

function handleCellClick(challenge, day) {
  if (!isToday(day)) return
  if (challenge.type === 'timer' && !isDayCompleted(challenge, day)) {
    openTimer(challenge)
  } else if (challenge.type === 'composite') {
    openComposite(challenge)
  } else if (challenge.type === 'report') {
    openReport(challenge)
  } else if (challenge.type === 'progressive' && challenge.progress_sets) {
    openProgressiveSets(challenge)
  } else {
    toggleDay(challenge, day)
  }
}

async function toggleDay(challenge, day) {
  if (!isToday(day)) return
  await store.toggle(challenge.id, dateStr(day))
}

async function toggleToday(challenge) {
  if (!todayDay.value) return
  if (mobileMenuId.value || confirmStore.state.open) return
  if (challenge.type === 'timer' && !isTodayCompleted(challenge)) {
    openTimer(challenge)
  } else if (challenge.type === 'composite') {
    openComposite(challenge)
  } else if (challenge.type === 'report') {
    openReport(challenge)
  } else if (challenge.type === 'progressive' && challenge.progress_sets) {
    openProgressiveSets(challenge)
  } else {
    await store.toggle(challenge.id, todayStr.value)
  }
}

async function onDragEnd() {
  const ids = store.challenges.map(c => c.id)
  await store.reorder(ids)
}

function closeCreateModal() {
  showCreateModal.value = false
  newTitle.value = ''
  newType.value = 'checkbox'
  newTimerMinutes.value = 25
  newSubtasks.value = ['', '']
  newProgressStart.value = 10
  newProgressStep.value = 5
  newProgressEnd.value = 50
  newProgressSets.value = null
}

async function addChallenge() {
  if (!canCreate.value) return
  creating.value = true
  try {
    const payload = { title: newTitle.value.trim(), type: newType.value }
    if (newType.value === 'timer') {
      payload.timer_minutes = newTimerMinutes.value
    }
    if (newType.value === 'composite') {
      payload.subtasks = newSubtasks.value.filter(s => s.trim())
    }
    if (newType.value === 'progressive') {
      payload.progress_start = newProgressStart.value
      payload.progress_step = newProgressStep.value
      payload.progress_end = newProgressEnd.value
      if (newProgressSets.value && newProgressSets.value >= 2) {
        payload.progress_sets = newProgressSets.value
      }
    }
    await store.create(payload)
    closeCreateModal()
  } finally {
    creating.value = false
  }
}

function openEditModal(challenge) {
  editModal.value = {
    open: true,
    id: challenge.id,
    title: challenge.title,
    type: challenge.type || 'checkbox',
    timerMinutes: challenge.timer_minutes || 25,
    subtasks: challenge.subtasks ? [...challenge.subtasks] : [],
    progressStart: challenge.progress_start || 10,
    progressStep: challenge.progress_step || 5,
    progressEnd: challenge.progress_end || 50,
    progressSets: challenge.progress_sets || null,
  }
  nextTick(() => editModalInput.value?.focus())
}

async function saveEditModal() {
  const m = editModal.value
  if (!m.title.trim()) return
  saving.value = true
  try {
    const payload = { title: m.title.trim() }
    if (m.type === 'timer') payload.timer_minutes = m.timerMinutes
    if (m.type === 'composite') payload.subtasks = m.subtasks.filter(s => s.trim())
    if (m.type === 'progressive') {
      payload.progress_start = m.progressStart
      payload.progress_step = m.progressStep
      payload.progress_end = m.progressEnd
      payload.progress_sets = m.progressSets && m.progressSets >= 2 ? m.progressSets : null
    }
    await store.update(m.id, payload)
    editModal.value.open = false
  } finally {
    saving.value = false
  }
}

const mobileMenuPos = ref({})
const mobileMenuChallenge = ref(null)

function toggleMobileMenu(id, event) {
  if (mobileMenuId.value === id) {
    mobileMenuId.value = null
    return
  }
  mobileMenuId.value = id
  mobileMenuChallenge.value = store.challenges.find(c => c.id === id)
  // Позиционируем меню рядом с кнопкой
  if (event) {
    const rect = event.currentTarget.getBoundingClientRect()
    const menuWidth = 192 // w-48
    let left = rect.right - menuWidth
    if (left < 8) left = 8
    if (left + menuWidth > window.innerWidth - 8) left = window.innerWidth - menuWidth - 8
    mobileMenuPos.value = {
      top: rect.bottom + 4 + 'px',
      left: left + 'px',
    }
  }
}

async function removeMobile(challenge) {
  mobileMenuId.value = null
  // nextTick чтобы меню закрылось до показа confirm
  await nextTick()
  await removeChallenge(challenge)
}

async function removeChallenge(challenge) {
  const confirmed = await confirmStore.ask({
    title: 'Удалить привычку',
    message: `Удалить "${challenge.title}" и все отметки?`,
    confirmText: 'Удалить',
    cancelText: 'Отмена',
    danger: true,
  })
  if (confirmed) {
    await store.remove(challenge.id)
  }
}

const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (e) => {
      if (!el.contains(e.target)) binding.value()
    }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  },
}

// Timer functions
function openTimer(challenge) {
  timerModal.value = {
    open: true,
    challenge,
    remaining: challenge.timer_minutes * 60,
    running: false,
    finished: false,
  }
}

function startTimer() {
  timerModal.value.running = true
  timerInterval = setInterval(() => {
    timerModal.value.remaining--
    if (timerModal.value.remaining <= 0) {
      clearInterval(timerInterval)
      timerInterval = null
      timerModal.value.running = false
      timerModal.value.finished = true
      timerModal.value.remaining = 0
      // Play sound
      playTimerSound()
    }
  }, 1000)
}

function pauseTimer() {
  timerModal.value.running = false
  clearInterval(timerInterval)
  timerInterval = null
}

function cancelTimer() {
  clearInterval(timerInterval)
  timerInterval = null
  timerModal.value.open = false
}

async function confirmTimer() {
  const ch = timerModal.value.challenge
  const elapsed = ch.timer_minutes * 60 - timerModal.value.remaining
  await store.toggle(ch.id, todayStr.value, { timer_seconds: elapsed })
  timerModal.value.open = false
}

function formatTimer(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

function playTimerSound() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)()
    for (let rep = 0; rep < 5; rep++) {
      const offset = rep * 1.5 // 1.5 сек между повторами
      const beep = [880, 1100, 880]
      beep.forEach((freq, i) => {
        const osc = ctx.createOscillator()
        const gain = ctx.createGain()
        osc.connect(gain)
        gain.connect(ctx.destination)
        osc.frequency.value = freq
        osc.type = 'sine'
        const t = ctx.currentTime + offset + i * 0.2
        gain.gain.setValueAtTime(0.3, t)
        gain.gain.exponentialRampToValueAtTime(0.01, t + 0.18)
        osc.start(t)
        osc.stop(t + 0.2)
      })
    }
  } catch {}
}

// Composite functions
function openComposite(challenge) {
  const entry = challenge.entries?.find(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === todayStr.value
  })
  const states = entry?.subtask_states || Array(challenge.subtasks.length).fill(false)
  compositeModal.value = { open: true, challenge, states: [...states] }
}

function toggleSubtask(idx) {
  const ch = compositeModal.value.challenge
  // Optimistic: мгновенно обновляем UI
  const newStates = [...compositeModal.value.states]
  newStates[idx] = !newStates[idx]
  compositeModal.value.states = newStates

  // API в фоне, при ошибке откатываем
  store.toggle(ch.id, todayStr.value, { subtask_index: idx })
    .then(res => {
      if (res?.subtask_states && compositeModal.value?.challenge?.id === ch.id) {
        compositeModal.value.states = res.subtask_states
      }
    })
    .catch(() => {
      if (compositeModal.value?.challenge?.id === ch.id) {
        newStates[idx] = !newStates[idx]
        compositeModal.value.states = [...newStates]
      }
    })
}

// Progressive sets functions
function openProgressiveSets(challenge) {
  const entry = challenge.entries?.find(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === todayStr.value
  })
  const sets = progressSetsArray(challenge, todayDay.value)
  if (!sets) return
  const states = entry?.subtask_states || Array(sets.length).fill(false)
  // Build pseudo-subtasks for composite modal
  const subtasks = sets.map((val, i) => `Подход ${i + 1}: ${val}`)
  compositeModal.value = {
    open: true,
    challenge: { ...challenge, subtasks },
    states: [...states],
  }
}

// Report functions
function openReport(challenge) {
  const entry = challenge.entries?.find(e => {
    const ed = typeof e.date === 'string' ? e.date.substring(0, 10) : ''
    return ed === todayStr.value
  })
  reportModal.value = {
    open: true,
    challenge,
    text: entry?.report_text || '',
    saving: false,
  }
}

async function saveReport() {
  const ch = reportModal.value.challenge
  const text = reportModal.value.text.trim()
  reportModal.value.saving = true
  try {
    await store.toggle(ch.id, todayStr.value, { report_text: text || null })
    reportModal.value.open = false
  } finally {
    reportModal.value.saving = false
  }
}

watch(showCreateModal, (v) => {
  if (v) nextTick(() => createInput.value?.focus())
})

onMounted(() => {
  store.fetchChallenges(monthKey.value)
})
</script>
