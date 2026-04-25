<template>
  <div class="p-2 sm:p-4 lg:p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4 lg:mb-6">
        <!-- Desktop: заголовок "Календарь" -->
        <div class="hidden lg:block">
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Календарь</h1>
        </div>

        <!-- Mobile: навигация вместо заголовка -->
        <div class="flex lg:hidden items-center gap-1">
          <button @click="previousPeriod" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronLeftIcon class="w-5 h-5 text-gray-700 dark:text-gray-300" />
          </button>
          <h2 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white text-center px-1 min-w-0 truncate">
            {{ currentPeriodTitle }}
          </h2>
          <button @click="nextPeriod" class="p-1.5 rounded-lg bg-gray-100 dark:bg-gray-700 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronRightIcon class="w-5 h-5 text-gray-700 dark:text-gray-300" />
          </button>
        </div>

        <div class="flex items-center space-x-1 sm:space-x-2">
          <button
            @click="setView('day')"
            class="p-2 lg:px-4 lg:py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'day' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 active:bg-gray-200 dark:active:bg-gray-600'"
          >
            <!-- Mobile: icon -->
            <svg class="w-5 h-5 lg:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <rect x="4" y="4" width="16" height="16" rx="2" />
              <path d="M4 9h16" />
              <text x="12" y="18" text-anchor="middle" font-size="8" fill="currentColor" stroke="none" font-weight="bold">1</text>
            </svg>
            <!-- Desktop: text -->
            <span class="hidden lg:inline">День</span>
          </button>
          <button
            @click="setView('week')"
            class="p-2 lg:px-4 lg:py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'week' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 active:bg-gray-200 dark:active:bg-gray-600'"
          >
            <!-- Mobile: icon (columns) -->
            <svg class="w-5 h-5 lg:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <rect x="4" y="4" width="16" height="16" rx="2" />
              <path d="M4 9h16M9.33 9v11M14.66 9v11" />
            </svg>
            <!-- Desktop: text -->
            <span class="hidden lg:inline">Неделя</span>
          </button>
          <button
            @click="setView('month')"
            class="p-2 lg:px-4 lg:py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'month' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 active:bg-gray-200 dark:active:bg-gray-600'"
          >
            <!-- Mobile: icon (grid) -->
            <svg class="w-5 h-5 lg:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <rect x="4" y="4" width="16" height="16" rx="2" />
              <path d="M4 9h16M4 14h16M9.33 9v11M14.66 9v11" />
            </svg>
            <!-- Desktop: text -->
            <span class="hidden lg:inline">Месяц</span>
          </button>
        </div>
      </div>

      <!-- Calendar Navigation (desktop only) -->
      <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex items-center justify-between">
          <button @click="previousPeriod" class="p-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronLeftIcon class="w-6 h-6 text-gray-700 dark:text-gray-300" />
          </button>

          <h2 class="text-lg font-semibold text-gray-900 dark:text-white text-center px-2">
            {{ currentPeriodTitle }}
          </h2>

          <button @click="nextPeriod" class="p-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronRightIcon class="w-6 h-6 text-gray-700 dark:text-gray-300" />
          </button>
        </div>
      </div>

      <!-- Month View -->
      <div v-if="viewMode === 'month'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Days Header -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
          <div
            v-for="day in weekDays"
            :key="day"
            class="text-center py-2 sm:py-3 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 last:border-r-0"
          >
            {{ day }}
          </div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7">
          <div
            v-for="(day, index) in calendarDays"
            :key="index"
            @click="handleDayClick(day.date)"
            class="group/day relative min-h-[60px] sm:min-h-[80px] lg:min-h-[100px] border-b border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-1 sm:p-2 cursor-pointer touch-manipulation transition-colors"
            :class="getDayCellClass(day)"
          >
            <div class="flex flex-col h-full">
              <div class="flex justify-between items-start mb-1">
                <span
                  class="text-xs sm:text-sm font-medium"
                  :class="{
                    'text-gray-400 dark:text-gray-600': !day.currentMonth,
                    'text-primary-700 dark:text-primary-400': day.isToday,
                    'text-gray-900 dark:text-gray-100': day.currentMonth && !day.isToday
                  }"
                >
                  {{ day.day }}
                </span>
                <span
                  v-if="day.taskCount > 0"
                  class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-semibold bg-primary-600 text-white rounded-full"
                >
                  {{ day.taskCount }}
                </span>
              </div>
              <!-- Иконка добавления задачи в пустом дне -->
              <div
                v-if="day.taskCount === 0 && day.currentMonth"
                class="flex-1 flex items-center justify-center"
              >
                <button
                  @click.stop="handleAddTaskForDay(day.date)"
                  class="w-6 h-6 flex items-center justify-center rounded-full text-gray-300 dark:text-gray-600 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <PlusIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Tooltip with task names (desktop only) -->
            <div
              v-if="day.taskCount > 0"
              class="hidden lg:block absolute left-1/2 -translate-x-1/2 bottom-full mb-2 z-50 pointer-events-none opacity-0 group-hover/day:opacity-100 transition-opacity duration-150"
            >
              <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg shadow-lg px-3 py-2 whitespace-nowrap max-w-[250px]">
                <div v-for="task in day.tasks.slice(0, 8)" :key="task.id" class="truncate py-0.5">
                  {{ task.title }}
                </div>
                <div v-if="day.tasks.length > 8" class="text-gray-400 text-[10px] pt-1">
                  +{{ day.tasks.length - 8 }} ещё
                </div>
                <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sphere filter chips -->
      <div v-if="viewMode === 'month'" class="mt-2 flex flex-wrap gap-1">
        <button
          @click="calOnlyMine = !calOnlyMine"
          class="px-1.5 py-0.5 text-[10px] rounded-full transition-all"
          :class="calOnlyMine
            ? 'text-gray-900 dark:text-white font-medium'
            : 'text-gray-400 dark:text-gray-500'"
          :style="calOnlyMine ? { background: 'rgba(99,102,241,0.1)' } : {}"
        >Мои</button>
        <button
          v-for="sphere in monthSpheres"
          :key="sphere.id"
          @click="toggleCalSphere(sphere.id)"
          class="px-1.5 py-0.5 text-[10px] rounded-full transition-all flex items-center gap-0.5"
          :class="isCalSphereHidden(sphere.id)
            ? 'text-gray-300 dark:text-gray-600 line-through'
            : 'font-medium'"
          :style="!isCalSphereHidden(sphere.id)
            ? { color: sphere.color, background: `${sphere.color}15` }
            : {}"
        >
          <span v-if="isCalSphereHidden(sphere.id)" class="w-1.5 h-1.5 rounded-full opacity-40" :style="{ backgroundColor: sphere.color }"></span>
          {{ sphere.name }}
        </button>
        <button
          @click="calHideNoSphere = !calHideNoSphere"
          class="px-1.5 py-0.5 text-[10px] rounded-full transition-all"
          :class="calHideNoSphere
            ? 'text-gray-300 dark:text-gray-600 line-through'
            : 'text-gray-500 dark:text-gray-400 font-medium'"
          :style="!calHideNoSphere ? { background: 'rgba(156,163,175,0.1)' } : {}"
        >Без сферы</button>
      </div>

      <!-- Month Day Tasks Panel -->
      <div v-if="viewMode === 'month'" class="mt-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
            {{ dayjs(selectedMonthDay).format('D MMMM YYYY') }}
          </h3>
          <div class="flex items-center gap-1">
            <button
              @click="monthDaySortMode = 'time'"
              class="p-1.5 rounded-lg transition-colors touch-manipulation"
              :class="monthDaySortMode === 'time' ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
              title="Сортировка по времени"
            >
              <ClockIcon class="w-4 h-4" />
            </button>
            <button
              @click="monthDaySortMode = 'priority'"
              class="p-1.5 rounded-lg transition-colors touch-manipulation"
              :class="monthDaySortMode === 'priority' ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
              title="Сортировка по важности"
            >
              <ExclamationTriangleIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <div class="p-3 sm:p-4">
          <div class="space-y-2">
            <TaskItem
              v-for="task in selectedMonthDayTasks"
              :key="task.id"
              :task="task"
              @task-click="handleTaskClick"
              @toggle-complete="handleToggleComplete"
            />
            <div v-if="selectedMonthDayTasks.length === 0 && selectedMonthDayCompleted.length === 0" class="text-center py-6 text-gray-400 dark:text-gray-500 text-sm">
              Нет задач на этот день
            </div>
          </div>

          <!-- Completed tasks -->
          <div v-if="selectedMonthDayCompleted.length > 0" class="mt-3 border-t border-gray-100 dark:border-gray-800 pt-3">
            <button
              @click="showCompleted = !showCompleted"
              class="flex items-center gap-2 w-full text-left px-1 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <span class="text-sm text-gray-500 dark:text-gray-400">Выполненные</span>
              <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">{{ selectedMonthDayCompleted.length }}</span>
              <svg
                class="w-3.5 h-3.5 text-gray-400 ml-auto transition-transform"
                :class="showCompleted ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="showCompleted" class="mt-2 opacity-60 space-y-2">
              <TaskItem
                v-for="task in selectedMonthDayCompleted"
                :key="task.id"
                :task="task"
                @task-click="handleTaskClick"
                @toggle-complete="handleToggleComplete"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Week View -->
      <div v-if="viewMode === 'week'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Desktop: Grid Layout with Time Grid -->
        <div class="hidden lg:block overflow-x-auto h-[calc(100vh-16rem)] overflow-y-auto">
          <!-- Days Header -->
          <div class="grid border-b border-gray-200 dark:border-gray-700" :style="{ gridTemplateColumns: '60px repeat(7, 1fr)' }">
            <div class="border-r border-gray-200 dark:border-gray-700"></div>
            <div
              v-for="(day, index) in weekDaysData"
              :key="index"
              class="text-center py-3 text-sm font-medium text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 last:border-r-0"
              :class="{
                'bg-primary-50 dark:bg-primary-900/30': day.isToday
              }"
            >
              <div class="font-semibold">{{ weekDays[index] }}</div>
              <div class="text-xs mt-1">{{ day.day }} {{ day.monthName }}</div>
            </div>
          </div>

          <!-- Time Grid -->
          <div class="grid border-b border-gray-200 dark:border-gray-700" :style="{ gridTemplateColumns: '60px repeat(7, 1fr)' }">
            <!-- Time Column -->
            <div class="border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
              <div
                v-for="hour in weekHours"
                :key="hour"
                class="h-16 border-b border-gray-200 dark:border-gray-700 flex items-start justify-end pr-2 pt-1"
              >
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatHour(hour) }}</span>
              </div>
            </div>

            <!-- Days Columns with Time Slots -->
            <div
              v-for="(day, dayIndex) in weekDaysData"
              :key="dayIndex"
              class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 relative"
              :class="{
                'bg-primary-50/30 dark:bg-primary-900/20': day.isToday
              }"
            >
              <!-- Time Slots (drop zone for time-based drop) -->
              <div
                class="relative"
                @dragover.prevent="onDragOver($event, day.date)"
                @dragleave="onDragLeave($event, day.date)"
                @drop="onDropTimeGrid($event, day.date, weekHours)"
                :class="{ 'ring-2 ring-inset ring-primary-400/50 bg-primary-50/30 dark:bg-primary-900/20': dropTargetDate === day.date }"
              >
                <div
                  v-for="hour in weekHours"
                  :key="hour"
                  class="h-16 border-b border-gray-100 dark:border-gray-700/50 group/slot flex items-center justify-center"
                >
                  <button
                    @click.stop="handleAddTaskForDayTime(day.date, hour)"
                    class="w-4 h-4 flex items-center justify-center rounded-full text-gray-200 dark:text-gray-700 opacity-0 group-hover/slot:opacity-100 hover:text-gray-400 dark:hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
                  >
                    <PlusIcon class="w-3 h-3" />
                  </button>
                </div>

                <!-- Current time line (only for today) -->
                <div
                  v-if="day.isToday && getCurrentTimePosition(weekHours)"
                  class="absolute left-0 right-0 z-20 pointer-events-none"
                  :style="{ top: getCurrentTimePosition(weekHours) + 'px' }"
                >
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-red-500 rounded-full -ml-1"></div>
                    <div class="flex-1 h-0.5 bg-red-500"></div>
                  </div>
                </div>

                <!-- Tasks positioned by time -->
                <div class="absolute inset-0 pointer-events-none">
                  <div
                    v-for="task in getTasksForDayWithTime(day.date)"
                    :key="task.id"
                    draggable="true"
                    @dragstart="onDragStart($event, task)"
                    @dragend="onDragEnd"
                    class="absolute left-1 right-1 z-10 overflow-hidden cursor-grab active:cursor-grabbing pointer-events-auto"
                    :style="getTaskPositionStyle(task, weekHours)"
                  >
                    <TaskItem :task="task" mini @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tasks without time (below the grid) -->
          <div class="grid border-t border-gray-200 dark:border-gray-700" :style="{ gridTemplateColumns: '60px repeat(7, 1fr)' }">
            <div class="border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-2">
              <span class="text-xs text-gray-500 dark:text-gray-400">Без времени</span>
            </div>
            <div
              v-for="(day, dayIndex) in weekDaysData"
              :key="dayIndex"
              class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-2 min-w-0 overflow-hidden"
              :class="[
                day.isToday ? 'bg-primary-50/30 dark:bg-primary-900/20' : '',
                dropTargetDate === day.date ? 'ring-2 ring-inset ring-primary-400/50' : ''
              ]"
              @dragover.prevent="onDragOver($event, day.date)"
              @dragleave="onDragLeave($event, day.date)"
              @drop="onDropDay($event, day.date)"
            >
              <div class="space-y-1 min-w-0">
                <div
                  v-for="task in getTasksForDayWithoutTime(day.date)"
                  :key="task.id"
                  draggable="true"
                  @dragstart="onDragStart($event, task)"
                  @dragend="onDragEnd"
                  class="cursor-grab active:cursor-grabbing"
                >
                  <TaskItem
                    :task="task"
                    mini
                    @task-click="handleTaskClick"
                    @toggle-complete="handleToggleComplete"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile: 2-column grid + full-width Sunday -->
        <div class="lg:hidden">
          <!-- Пн-Сб: 3 ряда по 2 колонки -->
          <div class="grid grid-cols-2">
            <div
              v-for="(day, index) in weekDaysData.slice(0, 6)"
              :key="index"
              :data-drop-date="day.date"
              class="p-2 border-b border-r border-gray-200 dark:border-gray-700 min-h-[120px] transition-colors"
              :class="[
                index % 2 === 1 ? 'border-r-0' : '',
                day.isToday ? 'bg-primary-50 dark:bg-primary-900/30' : '',
                dropTargetDate === day.date ? 'ring-2 ring-inset ring-primary-400/50 bg-primary-50/50 dark:bg-primary-900/30' : ''
              ]"
            >
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-baseline gap-1.5">
                  <span
                    class="text-base font-semibold"
                    :class="day.isToday ? 'text-primary-700 dark:text-primary-400' : 'text-gray-900 dark:text-white'"
                  >{{ day.day }}</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">{{ weekDays[index] }}</span>
                </div>
                <button
                  @click.stop="handleAddTaskForDay(day.date)"
                  class="w-5 h-5 flex items-center justify-center rounded-full text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors touch-manipulation"
                >
                  <PlusIcon class="w-3.5 h-3.5" />
                </button>
              </div>
              <div class="space-y-1">
                <div
                  v-for="task in day.tasks"
                  :key="task.id"
                  :data-task-id="task.id"
                  @touchstart="onTouchStart($event, task)"
                  @touchmove="onTouchMove($event)"
                  @touchend="onTouchEnd"
                >
                  <TaskItem
                    :task="task"
                    mini
                    @task-click="handleTaskClick"
                    @toggle-complete="handleToggleComplete"
                  />
                </div>
                <div v-if="day.tasks.length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">
                  Нет задач
                </div>
              </div>
            </div>
          </div>
          <!-- Вс: на всю ширину -->
          <div
            v-if="weekDaysData[6]"
            :data-drop-date="weekDaysData[6].date"
            class="p-2 min-h-[80px] transition-colors"
            :class="[
              weekDaysData[6].isToday ? 'bg-primary-50 dark:bg-primary-900/30' : '',
              dropTargetDate === weekDaysData[6].date ? 'ring-2 ring-inset ring-primary-400/50 bg-primary-50/50 dark:bg-primary-900/30' : ''
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-baseline gap-1.5">
                <span
                  class="text-base font-semibold"
                  :class="weekDaysData[6].isToday ? 'text-primary-700 dark:text-primary-400' : 'text-gray-900 dark:text-white'"
                >{{ weekDaysData[6].day }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ weekDays[6] }}</span>
              </div>
              <button
                @click.stop="handleAddTaskForDay(weekDaysData[6].date)"
                class="w-5 h-5 flex items-center justify-center rounded-full text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors touch-manipulation"
              >
                <PlusIcon class="w-3.5 h-3.5" />
              </button>
            </div>
            <div class="space-y-1">
              <div
                v-for="task in weekDaysData[6].tasks"
                :key="task.id"
                :data-task-id="task.id"
                @touchstart="onTouchStart($event, task)"
                @touchmove="onTouchMove($event)"
                @touchend="onTouchEnd"
              >
                <TaskItem
                  :task="task"
                  mini
                  @task-click="handleTaskClick"
                  @toggle-complete="handleToggleComplete"
                />
              </div>
              <div v-if="weekDaysData[6].tasks.length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">
                Нет задач
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Day View -->
      <div v-if="viewMode === 'day'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
            {{ currentDate.format('D MMMM YYYY') }}
          </h3>
          <div class="flex items-center gap-1">
            <button
              @click="daySortMode = 'time'"
              class="p-1.5 rounded-lg transition-colors touch-manipulation"
              :class="daySortMode === 'time' ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
              title="Сортировка по времени"
            >
              <ClockIcon class="w-5 h-5" />
            </button>
            <button
              @click="daySortMode = 'priority'"
              class="p-1.5 rounded-lg transition-colors touch-manipulation"
              :class="daySortMode === 'priority' ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
              title="Сортировка по важности"
            >
              <ExclamationTriangleIcon class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Desktop: Time Grid -->
        <div class="hidden lg:block overflow-x-auto">
          <div class="grid border-b border-gray-200 dark:border-gray-700" :style="{ gridTemplateColumns: '60px 1fr' }">
            <!-- Time Column -->
            <div class="border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
              <div
                v-for="hour in dayHours"
                :key="hour"
                class="h-16 border-b border-gray-200 dark:border-gray-700 flex items-start justify-end pr-2 pt-1"
              >
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatHour(hour) }}</span>
              </div>
            </div>

            <!-- Time Slots Column (drop zone) -->
            <div
              class="relative"
              @dragover.prevent="onDragOver($event, currentDate.format('YYYY-MM-DD'))"
              @dragleave="onDragLeave($event, currentDate.format('YYYY-MM-DD'))"
              @drop="onDropTimeGrid($event, currentDate.format('YYYY-MM-DD'), dayHours)"
            >
              <!-- Time Slots -->
              <div class="relative">
                <div
                  v-for="hour in dayHours"
                  :key="hour"
                  class="h-16 border-b border-gray-100 dark:border-gray-700/50 group/slot flex items-center justify-center"
                >
                  <button
                    @click.stop="handleAddTaskForDayTime(currentDate.format('YYYY-MM-DD'), hour)"
                    class="w-5 h-5 flex items-center justify-center rounded-full text-gray-200 dark:text-gray-700 opacity-0 group-hover/slot:opacity-100 hover:text-gray-400 dark:hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
                  >
                    <PlusIcon class="w-3.5 h-3.5" />
                  </button>
                </div>

                <!-- Current time line (only for today) -->
                <div
                  v-if="currentDate.isSame(dayjs(), 'day') && getCurrentTimePosition(dayHours)"
                  class="absolute left-0 right-0 z-20 pointer-events-none"
                  :style="{ top: getCurrentTimePosition(dayHours) + 'px' }"
                >
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-red-500 rounded-full -ml-1"></div>
                    <div class="flex-1 h-0.5 bg-red-500"></div>
                  </div>
                </div>

                <!-- Tasks positioned by time -->
                <div class="absolute inset-0 pointer-events-none">
                  <div
                    v-for="task in dayTasksWithTime"
                    :key="task.id"
                    draggable="true"
                    @dragstart="onDragStart($event, task)"
                    @dragend="onDragEnd"
                    class="absolute left-2 right-2 rounded-lg border-l-4 shadow-sm z-10 overflow-hidden cursor-grab active:cursor-grabbing pointer-events-auto"
                    :class="[
                      task.completed_at
                        ? 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-500'
                        : getDurationGradientClass(task) + ' border-primary-500'
                    ]"
                    :style="getTaskPositionStyle(task, dayHours)"
                  >
                    <TaskItem :task="task" @task-click="handleTaskClick" @toggle-complete="handleToggleComplete" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tasks without time (below the grid) -->
          <div class="grid border-t border-gray-200 dark:border-gray-700" :style="{ gridTemplateColumns: '60px 1fr' }">
            <div class="border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3">
              <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Без времени</span>
            </div>
            <div class="p-3">
              <div class="space-y-2">
                <div
                  v-for="task in dayTasksWithoutTime"
                  :key="task.id"
                  draggable="true"
                  @dragstart="onDragStart($event, task)"
                  @dragend="onDragEnd"
                  class="cursor-grab active:cursor-grabbing"
                >
                  <TaskItem
                    :task="task"
                    @task-click="handleTaskClick"
                    @toggle-complete="handleToggleComplete"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Completed tasks (desktop) -->
          <div v-if="dayCompletedTasks.length > 0" class="border-t border-gray-100 dark:border-gray-800 px-4 py-3">
            <button
              @click="showCompleted = !showCompleted"
              class="flex items-center gap-2 w-full text-left px-1 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 8-4-4m5.5-7h5.3c1.12 0 1.68 0 2.11.22a2 2 0 01.87.87c.22.43.22.99.22 2.11v9.6c0 1.12 0 1.68-.22 2.11a2 2 0 01-.87.87c-.43.22-.99.22-2.11.22H6.8c-1.12 0-1.68 0-2.11-.22a2 2 0 01-.87-.87C3.6 18.48 3.6 17.92 3.6 16.8V7.2c0-1.12 0-1.68.22-2.11a2 2 0 01.87-.87C5.12 4 5.68 4 6.8 4h2.7" />
              </svg>
              <span class="text-sm text-gray-500 dark:text-gray-400">Выполненные</span>
              <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">{{ dayCompletedTasks.length }}</span>
              <svg
                class="w-3.5 h-3.5 text-gray-400 ml-auto transition-transform"
                :class="showCompleted ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="showCompleted" class="mt-2 opacity-60 space-y-2">
              <TaskItem
                v-for="task in dayCompletedTasks"
                :key="task.id"
                :task="task"
                @task-click="handleTaskClick"
                @toggle-complete="handleToggleComplete"
              />
            </div>
          </div>
        </div>

        <!-- Mobile: Simple List -->
        <div class="lg:hidden p-3 sm:p-4">
          <div class="space-y-2 sm:space-y-3">
            <TaskItem
              v-for="task in dayTasks"
              :key="task.id"
              :task="task"
              @task-click="handleTaskClick"
              @toggle-complete="handleToggleComplete"
            />
            <div v-if="dayTasks.length === 0 && dayCompletedTasks.length === 0" class="text-center py-8 sm:py-12 text-gray-500 dark:text-gray-400 text-sm sm:text-base">
              Нет задач на этот день
            </div>
          </div>

          <!-- Completed tasks (mobile) -->
          <div v-if="dayCompletedTasks.length > 0" class="mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
            <button
              @click="showCompleted = !showCompleted"
              class="flex items-center gap-2 w-full text-left px-1 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 8-4-4m5.5-7h5.3c1.12 0 1.68 0 2.11.22a2 2 0 01.87.87c.22.43.22.99.22 2.11v9.6c0 1.12 0 1.68-.22 2.11a2 2 0 01-.87.87c-.43.22-.99.22-2.11.22H6.8c-1.12 0-1.68 0-2.11-.22a2 2 0 01-.87-.87C3.6 18.48 3.6 17.92 3.6 16.8V7.2c0-1.12 0-1.68.22-2.11a2 2 0 01.87-.87C5.12 4 5.68 4 6.8 4h2.7" />
              </svg>
              <span class="text-sm text-gray-500 dark:text-gray-400">Выполненные</span>
              <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded-full">{{ dayCompletedTasks.length }}</span>
              <svg
                class="w-3.5 h-3.5 text-gray-400 ml-auto transition-transform"
                :class="showCompleted ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="showCompleted" class="mt-2 opacity-60 space-y-2">
              <TaskItem
                v-for="task in dayCompletedTasks"
                :key="task.id"
                :task="task"
                @task-click="handleTaskClick"
                @toggle-complete="handleToggleComplete"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Task View -->
      <TaskView
        :show="showTaskView"
        :task="selectedTask"
        @close="showTaskView = false; selectedTask = null"
        @enter-edit="handleEnterEdit"
        @complete-task="handleCompleteTask"
        @uncomplete-task="handleUncompleteTask"
      />
      <TaskView :show="showDraft" :task="draftTask" @close="closeDraft" />

      <!-- Task Modal -->
      <!-- Confirm Dialog -->
      <ConfirmDialog
        :show="showConfirm"
        :title="confirmTitle"
        :message="confirmMessage"
        confirm-text="Завершить"
        cancel-text="Отмена"
        variant="success"
        @confirm="confirmComplete"
        @cancel="cancelComplete"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'
import { useLifeSpheresStore } from '@/stores/lifeSpheres'
import TaskView from '@/components/tasks/TaskView.vue'
import TaskItem from '@/components/tasks/TaskItem.vue'
import { useTaskDraft } from '@/composables/useTaskDraft'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter'
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'
import { ChevronLeftIcon, ChevronRightIcon, ClockIcon, PlusIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

dayjs.extend(isSameOrAfter)
dayjs.extend(isSameOrBefore)
dayjs.locale('ru')

const route = useRoute()
const router = useRouter()
const tasksStore = useTasksStore()
const authStore = useAuthStore()
const workspaceStore = useWorkspaceStore()
const spheresStore = useLifeSpheresStore()
const hiddenCalSpheres = ref([])
const calOnlyMine = ref(false)
const calHideNoSphere = ref(false)
const isCalSphereHidden = (id) => hiddenCalSpheres.value.includes(id)

const monthSpheres = computed(() => {
  const startOfMonth = currentDate.value.startOf('month').format('YYYY-MM')
  const sphereIds = new Set()
  for (const task of tasks.value) {
    if (task.due_date && task.life_sphere_id && dayjs(task.due_date).format('YYYY-MM') === startOfMonth) {
      sphereIds.add(task.life_sphere_id)
    }
  }
  return spheresStore.visibleSpheres.filter(s => sphereIds.has(s.id))
})

const toggleCalSphere = (id) => {
  const idx = hiddenCalSpheres.value.indexOf(id)
  if (idx !== -1) {
    hiddenCalSpheres.value.splice(idx, 1)
  } else {
    hiddenCalSpheres.value.push(id)
  }
}

const taskPeople = (task) => {
  return (task.contacts || [])
    .filter(c => c.pivot?.role === 'assignee' || c.pivot?.role === 'watcher')
    .map(c => ({ name: c.name, role: c.pivot.role }))
}

const viewerRole = (task) => {
  const uid = authStore.user?.id
  if (!uid) return 'owner'
  const myLink = (task.contacts || []).find(c => c.contact_user_id === uid)
  if (myLink) {
    if (myLink.pivot?.role === 'assignee') return 'assignee'
    return 'watcher'
  }
  return 'owner'
}

const myWorkspaceId = computed(() => {
  const uid = authStore.user?.id
  if (!uid) return null
  const owned = workspaceStore.workspaces.find(w => w.owner_id === uid)
  return owned?.id || workspaceStore.workspaces[0]?.id || null
})
const isOwnTask = (task) => {
  if (!myWorkspaceId.value) return true
  return task.workspace_id == null || task.workspace_id === myWorkspaceId.value
}

const currentDate = ref(dayjs())
const loading = computed(() => tasksStore.loading)
const viewMode = ref('month')

const setView = (view) => {
  // При переключении с месяца — используем выделенный день
  if (viewMode.value === 'month' && selectedMonthDay.value) {
    currentDate.value = dayjs(selectedMonthDay.value)
  }
  viewMode.value = view
  router.replace({ query: { ...route.query, view } })
}

// Следим за изменениями query параметра view
watch(() => route.query.view, (newView) => {
  if (newView && ['day', 'week', 'month'].includes(newView)) {
    viewMode.value = newView
  }
}, { immediate: true })

// Сброс сортировки при смене даты или вида
watch([currentDate, viewMode], () => {
  daySortMode.value = 'time'
})
const showCompleted = ref(false)
const daySortMode = ref('time')
const selectedMonthDay = ref(dayjs().format('YYYY-MM-DD'))
const monthDaySortMode = ref('time')
const showTaskView = ref(false)
const selectedTask = ref(null)
const taskError = ref('')
const newTaskDate = ref('')
const currentTime = ref(dayjs())

// Состояния для ConfirmDialog
const showConfirm = ref(false)
const taskToComplete = ref(null)
const confirmTitle = ref('Завершить задачу?')
const confirmMessage = ref('')

// Вычисляемый диапазон дат для текущего вида
const dateRange = computed(() => {
  if (viewMode.value === 'month') {
    return {
      start: currentDate.value.startOf('month').subtract(7, 'days').format('YYYY-MM-DD'),
      end: currentDate.value.endOf('month').add(7, 'days').format('YYYY-MM-DD'),
    }
  } else if (viewMode.value === 'week') {
    const weekStart = currentDate.value.startOf('week')
    return {
      start: weekStart.format('YYYY-MM-DD'),
      end: weekStart.add(6, 'day').format('YYYY-MM-DD'),
    }
  } else {
    const d = currentDate.value.format('YYYY-MM-DD')
    return { start: d, end: d }
  }
})

// Задачи автоматически фильтруются по диапазону дат
const tasks = computed(() => tasksStore.calendarTasks(dateRange.value.start, dateRange.value.end))

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']

const currentMonthName = computed(() => currentDate.value.format('MMMM'))
const currentYear = computed(() => currentDate.value.format('YYYY'))

// Счетчик не завершенных задач в текущем месяце
const monthTaskCount = computed(() => {
  if (viewMode.value !== 'month') return 0
  if (tasks.value.length === 0) return 0
  
  const startOfMonth = currentDate.value.startOf('month')
  const endOfMonth = currentDate.value.endOf('month')
  
  const count = tasks.value.filter(task => {
    if (!task.due_date || task.completed_at) return false
    
    // Парсим дату - может быть в формате YYYY-MM-DD или ISO
    const taskDate = dayjs(task.due_date)
    if (!taskDate.isValid()) return false
    
    const isInRange = taskDate.isSameOrAfter(startOfMonth, 'day') && 
                      taskDate.isSameOrBefore(endOfMonth, 'day')
    
    return isInRange
  }).length
  
  return count
})

const currentPeriodTitle = computed(() => {
  if (viewMode.value === 'month') {
    return `${currentMonthName.value} ${currentYear.value}`
  } else if (viewMode.value === 'week') {
    const weekStart = currentDate.value.startOf('week')
    const weekEnd = weekStart.add(6, 'day') // Используем add(6, 'day') для консистентности
    if (weekStart.month() === weekEnd.month()) {
      return `${weekStart.format('D')} - ${weekEnd.format('D MMMM YYYY')}`
    } else {
      return `${weekStart.format('D MMM')} - ${weekEnd.format('D MMM YYYY')}`
    }
  } else {
    return currentDate.value.format('D MMMM YYYY')
  }
})

const weekDaysData = computed(() => {
  const weekStart = currentDate.value.startOf('week')
  const days = []
  
  for (let i = 0; i < 7; i++) {
    const date = weekStart.add(i, 'day')
    const dateString = date.format('YYYY-MM-DD')
    const dayTasks = tasks.value
      .filter(task =>
        task.due_date &&
        dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
        task.status !== 'completed'
      )
      .sort((a, b) => {
        const timeA = a.estimated_time || a.end_time
        const timeB = b.estimated_time || b.end_time
        if (!timeA && !timeB) return 0
        if (!timeA) return 1
        if (!timeB) return -1
        return timeA.localeCompare(timeB)
      })

    days.push({
      day: date.date(),
      monthName: date.format('MMM'),
      date: dateString,
      isToday: date.isSame(dayjs(), 'day'),
      tasks: dayTasks,
    })
  }
  
  return days
})

const priorityOrder = { urgent: 0, high: 1, medium: 2, low: 3 }

const dayTasks = computed(() => {
  const dateString = currentDate.value.format('YYYY-MM-DD')
  const filtered = tasks.value.filter(task =>
    task.due_date &&
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed'
  )

  if (daySortMode.value === 'priority') {
    return filtered.sort((a, b) => {
      const pa = priorityOrder[a.priority] ?? 4
      const pb = priorityOrder[b.priority] ?? 4
      if (pa !== pb) return pa - pb
      const timeA = a.estimated_time || a.end_time
      const timeB = b.estimated_time || b.end_time
      if (!timeA && !timeB) return 0
      if (!timeA) return 1
      if (!timeB) return -1
      return timeA.localeCompare(timeB)
    })
  }

  return filtered.sort((a, b) => {
    const timeA = a.estimated_time || a.end_time
    const timeB = b.estimated_time || b.end_time
    if (!timeA && !timeB) return 0
    if (!timeA) return 1
    if (!timeB) return -1
    return timeA.localeCompare(timeB)
  })
})

// Задачи для дня с временем (для отображения в сетке)
const dayTasksWithTime = computed(() => {
  return dayTasks.value.filter(task => task.estimated_time || task.end_time)
})

// Задачи для дня без времени (для отображения ниже сетки)
const dayTasksWithoutTime = computed(() => {
  return dayTasks.value.filter(task => !task.estimated_time && !task.end_time)
})

// Задачи выбранного дня в месячном виде
const selectedMonthDayTasks = computed(() => {
  const dateString = selectedMonthDay.value
  const myId = authStore.user?.id
  const filtered = tasks.value.filter(task =>
    task.due_date &&
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed' &&
    !hiddenCalSpheres.value.includes(task.life_sphere_id) &&
    (!calOnlyMine.value || task.creator?.id === myId) &&
    (!calHideNoSphere.value || task.life_sphere_id)
  )

  if (monthDaySortMode.value === 'priority') {
    return filtered.sort((a, b) => {
      const pa = priorityOrder[a.priority] ?? 4
      const pb = priorityOrder[b.priority] ?? 4
      if (pa !== pb) return pa - pb
      const timeA = a.estimated_time || a.end_time
      const timeB = b.estimated_time || b.end_time
      if (!timeA && !timeB) return 0
      if (!timeA) return 1
      if (!timeB) return -1
      return timeA.localeCompare(timeB)
    })
  }

  return filtered.sort((a, b) => {
    const timeA = a.estimated_time || a.end_time
    const timeB = b.estimated_time || b.end_time
    if (!timeA && !timeB) return 0
    if (!timeA) return 1
    if (!timeB) return -1
    return timeA.localeCompare(timeB)
  })
})

const selectedMonthDayCompleted = computed(() => {
  const dateString = selectedMonthDay.value
  const myId = authStore.user?.id
  return tasksStore.allTasks.filter(task =>
    task.due_date &&
    task.due_date.substring(0, 10) === dateString &&
    task.completed_at &&
    !hiddenCalSpheres.value.includes(task.life_sphere_id) &&
    (!calOnlyMine.value || task.creator?.id === myId) &&
    (!calHideNoSphere.value || task.life_sphere_id)
  )
})

// Выполненные задачи дня
const dayCompletedTasks = computed(() => {
  const dateString = currentDate.value.format('YYYY-MM-DD')
  return tasksStore.allTasks.filter(task =>
    task.due_date &&
    task.due_date.substring(0, 10) === dateString &&
    task.completed_at
  )
})

// Вычисление диапазона часов для сетки на основе задач
const getHoursRange = (tasks) => {
  if (!tasks || tasks.length === 0) {
    return Array.from({ length: 24 }, (_, i) => i) // По умолчанию 0-23
  }
  
  let minHour = 23
  let maxHour = 0
  
  tasks.forEach(task => {
    const startTime = task.estimated_time || task.end_time
    const endTime = task.end_time || task.estimated_time
    
    if (startTime) {
      const timeStr = formatTime(startTime)
      const [hours] = timeStr.split(':').map(Number)
      minHour = Math.min(minHour, hours)
      maxHour = Math.max(maxHour, hours)
    }
    
    if (endTime) {
      const timeStr = formatTime(endTime)
      const [hours] = timeStr.split(':').map(Number)
      minHour = Math.min(minHour, hours)
      maxHour = Math.max(maxHour, hours)
    }
  })
  
  // Округляем minHour вниз и maxHour вверх, добавляем запас
  minHour = Math.max(0, Math.floor(minHour) - 1) // Начинаем на час раньше
  maxHour = Math.min(23, Math.ceil(maxHour) + 1) // Заканчиваем на час позже
  
  // Создаем массив часов
  const hours = []
  for (let i = minHour; i <= maxHour; i++) {
    hours.push(i)
  }
  
  return hours.length > 0 ? hours : Array.from({ length: 24 }, (_, i) => i)
}

// Часы для дневного вида
const dayHours = computed(() => {
  return getHoursRange(dayTasksWithTime.value)
})

// Часы для недельного вида (полные сутки для заполнения экрана)
const weekHours = computed(() => {
  // Показываем полные сутки 0:00 - 23:00 (24 часа)
  // Высота: 24 × 64px = 1536px - заполняет весь экран с прокруткой
  return Array.from({ length: 24 }, (_, i) => i)
})

const calendarDays = computed(() => {
  const hidden = [...hiddenCalSpheres.value] // force reactive track
  const onlyMine = calOnlyMine.value
  const hideNoSphere = calHideNoSphere.value
  const days = []
  const startOfMonth = currentDate.value.startOf('month')
  const endOfMonth = currentDate.value.endOf('month')
  const startDay = startOfMonth.day() === 0 ? 6 : startOfMonth.day() - 1 // Понедельник = 0

  // Предыдущий месяц
  for (let i = startDay - 1; i >= 0; i--) {
    const date = startOfMonth.subtract(i + 1, 'day')
    days.push(createDayObject(date, false, hidden, onlyMine, hideNoSphere))
  }

  // Текущий месяц
  for (let i = 0; i < endOfMonth.date(); i++) {
    const date = startOfMonth.add(i, 'day')
    days.push(createDayObject(date, true, hidden, onlyMine, hideNoSphere))
  }

  // Следующий месяц
  const remainingDays = 42 - days.length // 6 недель
  for (let i = 0; i < remainingDays; i++) {
    const date = endOfMonth.add(i + 1, 'day')
    days.push(createDayObject(date, false, hidden, onlyMine, hideNoSphere))
  }
  
  return days
})

const createDayObject = (date, currentMonth, hidden, onlyMine, hideNoSphere) => {
  const dateString = date.format('YYYY-MM-DD')
  const myId = authStore.user?.id
  const dayTasks = tasks.value.filter(task =>
    task.due_date &&
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed' &&
    !hidden.includes(task.life_sphere_id) &&
    (!onlyMine || task.creator?.id === myId) &&
    (!hideNoSphere || task.life_sphere_id)
  )
  
  return {
    day: date.date(),
    date: dateString,
    currentMonth,
    isToday: date.isSame(dayjs(), 'day'),
    taskCount: dayTasks.length,
    tasks: dayTasks,
  }
}

const previousPeriod = () => {
  if (viewMode.value === 'month') {
    currentDate.value = currentDate.value.subtract(1, 'month')
  } else if (viewMode.value === 'week') {
    currentDate.value = currentDate.value.subtract(1, 'week')
  } else {
    currentDate.value = currentDate.value.subtract(1, 'day')
  }
}

const nextPeriod = () => {
  if (viewMode.value === 'month') {
    currentDate.value = currentDate.value.add(1, 'month')
  } else if (viewMode.value === 'week') {
    currentDate.value = currentDate.value.add(1, 'week')
  } else {
    currentDate.value = currentDate.value.add(1, 'day')
  }
}

const formatTime = (time) => {
  if (!time) return ''
  // Если время уже в формате HH:mm, возвращаем как есть
  if (/^\d{2}:\d{2}$/.test(time)) return time
  // Иначе извлекаем HH:mm из формата HH:mm:ss
  return time.substring(0, 5)
}

// Форматирование часа для отображения в сетке
const formatHour = (hour) => {
  return `${hour.toString().padStart(2, '0')}:00`
}

// Получить задачи для дня с временем
const getTasksForDayWithTime = (dateString) => {
  return tasks.value.filter(task => 
    task.due_date && 
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed' &&
    (task.estimated_time || task.end_time)
  )
}

// Получить задачи для дня без времени
const getTasksForDayWithoutTime = (dateString) => {
  return tasks.value.filter(task => 
    task.due_date && 
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed' &&
    !task.estimated_time && !task.end_time
  )
}

// Вычислить позицию текущего времени в сетке
const getCurrentTimePosition = (hoursRange = null) => {
  if (!hoursRange || hoursRange.length === 0) return null
  
  // Используем реактивное currentTime для обновления линии
  const now = currentTime.value
  const currentHour = now.hour()
  const currentMinute = now.minute()
  
  const minHour = hoursRange[0]
  const maxHour = hoursRange[hoursRange.length - 1]
  
  // Проверяем, попадает ли текущее время в диапазон
  if (currentHour < minHour || currentHour > maxHour) {
    return null
  }
  
  // Вычисляем позицию в пикселях
  const hourHeight = 64
  const currentTotalMinutes = currentHour * 60 + currentMinute
  const minHourTotalMinutes = minHour * 60
  const position = ((currentTotalMinutes - minHourTotalMinutes) / 60) * hourHeight
  
  return Math.max(0, position)
}

// Вычислить позицию задачи в сетке времени
const getTaskPositionStyle = (task, hoursRange = null) => {
  if (!task.estimated_time && !task.end_time) {
    return { display: 'none' }
  }
  
  // Используем estimated_time как время начала, если нет - используем end_time
  const startTime = task.estimated_time || task.end_time
  const endTime = task.end_time || task.estimated_time
  
  if (!startTime || !endTime) {
    return { display: 'none' }
  }
  
  const startTimeStr = formatTime(startTime)
  const endTimeStr = formatTime(endTime)
  
  // Парсим время
  const [startHours, startMinutes] = startTimeStr.split(':').map(Number)
  const [endHours, endMinutes] = endTimeStr.split(':').map(Number)
  
  // Определяем минимальный час в сетке
  const minHour = hoursRange && hoursRange.length > 0 ? hoursRange[0] : 0
  
  // Вычисляем позицию в пикселях относительно начала сетки
  // Каждый час = 64px (h-16 = 4rem = 64px)
  const hourHeight = 64
  const startTotalMinutes = startHours * 60 + startMinutes
  const minHourTotalMinutes = minHour * 60
  const startPosition = ((startTotalMinutes - minHourTotalMinutes) / 60) * hourHeight
  
  // Вычисляем продолжительность
  let endTotalMinutes = endHours * 60 + endMinutes
  
  // Если end_time меньше start_time, значит задача переходит на следующий день
  if (endTotalMinutes < startTotalMinutes) {
    endTotalMinutes += 24 * 60
  }
  
  const durationMinutes = endTotalMinutes - startTotalMinutes
  const height = (durationMinutes / 60) * hourHeight
  
  // Минимальная высота для видимости
  const minHeight = 40
  const finalHeight = Math.max(height, minHeight)
  
  return {
    top: `${Math.max(0, startPosition)}px`,
    height: `${finalHeight}px`,
  }
}

// Вычисление продолжительности задачи в часах
const getTaskDuration = (task) => {
  if (!task.estimated_time || !task.end_time) {
    return 0 // Без времени
  }
  
  const startTime = formatTime(task.estimated_time)
  const endTime = formatTime(task.end_time)
  
  if (!startTime || !endTime) {
    return 0
  }
  
  // Парсим время в формате HH:mm
  const [startHours, startMinutes] = startTime.split(':').map(Number)
  const [endHours, endMinutes] = endTime.split(':').map(Number)
  
  // Вычисляем разницу в минутах
  const startTotalMinutes = startHours * 60 + startMinutes
  const endTotalMinutes = endHours * 60 + endMinutes
  
  // Если end_time меньше start_time, значит задача переходит на следующий день
  let diffMinutes = endTotalMinutes - startTotalMinutes
  if (diffMinutes < 0) {
    diffMinutes += 24 * 60 // Добавляем 24 часа
  }
  
  // Конвертируем в часы (с десятичными долями)
  return diffMinutes / 60
}

// Получение класса градиента в зависимости от продолжительности
const getDurationGradientClass = (task) => {
  if (task.completed_at) {
    return 'bg-gray-100 dark:bg-gray-700'
  }

  const duration = getTaskDuration(task)

  if (duration === 0 || duration < 1) {
    return 'bg-gradient-to-l from-white via-green-50 to-green-100 dark:from-gray-800 dark:via-green-900/20 dark:to-green-900/40'
  }

  if (duration >= 1 && duration < 2) {
    return 'bg-gradient-to-l from-white via-orange-50 to-orange-100 dark:from-gray-800 dark:via-orange-900/20 dark:to-orange-900/40'
  }

  if (duration >= 2) {
    return 'bg-gradient-to-l from-white via-red-50 to-red-100 dark:from-gray-800 dark:via-red-900/20 dark:to-red-900/40'
  }

  return 'bg-white dark:bg-gray-800'
}

// Определение цвета фона ячейки дня в месячном календаре (тепловая карта)
const getDayCellClass = (day) => {
  const base = []

  if (day.date === selectedMonthDay.value && viewMode.value === 'month') {
    base.push('ring-1 ring-inset ring-gray-300 dark:ring-gray-600')
  }
  if (day.isToday) {
    base.push('ring-1 ring-inset ring-primary-300 dark:ring-primary-600')
  }

  if (!day.currentMonth) {
    base.push('bg-gray-50/50 dark:bg-gray-900/30')
    return base.join(' ')
  }

  if (day.taskCount === 0) {
    base.push('hover:bg-gray-50 dark:hover:bg-gray-700/50')
    return base.join(' ')
  }

  // Определяем максимальную продолжительность задач для этого дня
  let maxDuration = 0
  let hasTasksWithoutTime = false

  day.tasks.forEach(task => {
    const duration = getTaskDuration(task)
    if (duration === 0) {
      hasTasksWithoutTime = true
    }
    if (duration > maxDuration) {
      maxDuration = duration
    }
  })

  // Раскраска по максимальной длительности задачи
  if (maxDuration >= 2) {
    base.push('bg-red-50/70 dark:bg-red-900/15 hover:bg-red-100/80 dark:hover:bg-red-900/25')
  } else if (maxDuration >= 1) {
    base.push('bg-orange-50/70 dark:bg-orange-900/15 hover:bg-orange-100/80 dark:hover:bg-orange-900/25')
  } else {
    base.push('bg-green-50/70 dark:bg-green-900/15 hover:bg-green-100/80 dark:hover:bg-green-900/25')
  }

  return base.join(' ')
}

const getPriorityLabel = (priority) => {
  const labels = {
    high: 'Высокий',
    medium: 'Средний',
    low: 'Низкий',
    urgent: 'Срочный'
  }
  return labels[priority] || priority
}

const handleTaskClick = (task) => {
  selectedTask.value = task
  newTaskDate.value = ''
  showTaskView.value = true
}

const handleEnterEdit = () => {
  showTaskView.value = false
  startDraft({ status: 'scheduled' })
}

const handleCompleteTask = async (task) => {
  try {
    await tasksStore.completeTask(task.id)
  } catch (error) {
    console.error('Error completing task:', error)
  }
}

const handleUncompleteTask = async (task) => {
  try {
    await tasksStore.uncompleteTask(task.id)
  } catch (error) {
    console.error('Error uncompleting task:', error)
  }
}

const { draftTask, showDraft, startDraft, closeDraft } = useTaskDraft(() => tasksStore.fetchTasks?.())
const handleAddTaskForDay = (dateString) => startDraft({ status: 'scheduled', due_date: dateString })
const handleAddTaskForDayTime = (dateString, hour) => {
  const timeStr = `${String(hour).padStart(2, '0')}:00`
  const endStr = `${String(Math.min(hour + 1, 23)).padStart(2, '0')}:00`
  startDraft({ status: 'scheduled', due_date: dateString, estimated_time: timeStr, end_time: endStr })
}

const handleDayClick = (dateString) => {
  if (viewMode.value === 'month') {
    selectedMonthDay.value = dateString
  } else {
    currentDate.value = dayjs(dateString)
    setView('day')
  }
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    
    selectedTask.value = null
  } catch (error) {
    console.error('Error saving task:', error)
    let errorMessage = 'Ошибка при сохранении задачи'
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    taskError.value = errorMessage
  }
}

const handleToggleComplete = (task) => {
  // Наблюдатели и чужие задачи — нельзя завершать
  if (viewerRole(task) === 'watcher') return
  if (!isOwnTask(task)) return

  // Если задача уже завершена, сразу отменяем завершение без подтверждения
  if (task.completed_at) {
    handleUncompleteTask(task)
    return
  }

  // Если задача не завершена, показываем подтверждение
  taskToComplete.value = task
  confirmMessage.value = `Вы уверены, что хотите завершить задачу "${task.title}"?`
  showConfirm.value = true
}

const confirmComplete = async () => {
  if (taskToComplete.value) {
    try {
      await tasksStore.completeTask(taskToComplete.value.id)
    } catch (error) {
      console.error('Error completing task:', error)
    }
  }
  showConfirm.value = false
  taskToComplete.value = null
}

const cancelComplete = () => {
  showConfirm.value = false
  taskToComplete.value = null
}

// ==================== Drag & Drop ====================
const draggedTask = ref(null)
const dropTargetDate = ref(null)

// Pending API requests — отмена предыдущего при новом drag
const pendingAbort = ref(null)

// Optimistic update: сначала UI, потом API
const optimisticMove = (taskId, changes) => {
  // Отменяем предыдущий запрос если есть
  if (pendingAbort.value) {
    pendingAbort.value.abort()
  }

  // Мгновенно обновляем store
  const task = tasksStore.allTasks.find(t => t.id === taskId)
  if (!task) return
  const snapshot = { ...task }
  tasksStore.upsertTask({ ...task, ...changes })

  // Запускаем API в фоне
  const controller = new AbortController()
  pendingAbort.value = controller

  tasksStore.updateTask(taskId, changes)
    .catch((error) => {
      if (error?.name === 'CanceledError' || error?.name === 'AbortError') return
      // Revert on error
      console.error('Drag drop error:', error)
      tasksStore.upsertTask(snapshot)
    })
    .finally(() => {
      if (pendingAbort.value === controller) {
        pendingAbort.value = null
      }
    })
}

// Desktop: HTML5 Drag API
const onDragStart = (e, task) => {
  draggedTask.value = task
  e.dataTransfer.effectAllowed = 'move'
  e.dataTransfer.setData('text/plain', task.id)
  e.target.style.opacity = '0.4'
}

const onDragEnd = (e) => {
  e.target.style.opacity = '1'
  draggedTask.value = null
  dropTargetDate.value = null
}

const onDragOver = (e, dateString) => {
  e.preventDefault()
  e.dataTransfer.dropEffect = 'move'
  dropTargetDate.value = dateString
}

const onDragLeave = (_e, dateString) => {
  if (dropTargetDate.value === dateString) {
    dropTargetDate.value = null
  }
}

// Вычислить длительность задачи в минутах
const getTaskDurationMinutes = (task) => {
  if (!task.estimated_time || !task.end_time) return 60
  const st = formatTime(task.estimated_time)
  const et = formatTime(task.end_time)
  const [sh, sm] = st.split(':').map(Number)
  const [eh, em] = et.split(':').map(Number)
  let diff = (eh * 60 + em) - (sh * 60 + sm)
  if (diff <= 0) diff += 24 * 60
  return diff
}

const minutesToTimeStr = (totalMinutes) => {
  const h = Math.min(23, Math.max(0, Math.floor(totalMinutes / 60)))
  const m = Math.max(0, totalMinutes % 60)
  return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}

// Desktop week/day: drop на сетку времени — вычисляем час по Y
const onDropTimeGrid = (e, dateString, hoursRange) => {
  e.preventDefault()
  dropTargetDate.value = null
  if (!draggedTask.value) return

  const rect = e.currentTarget.getBoundingClientRect()
  const y = e.clientY - rect.top
  const hourHeight = 64
  const minHour = hoursRange[0]
  const rawMinutes = (y / hourHeight) * 60 + minHour * 60
  const snappedMinutes = Math.round(rawMinutes / 15) * 15

  const startStr = minutesToTimeStr(snappedMinutes)
  const duration = getTaskDurationMinutes(draggedTask.value)
  const endStr = minutesToTimeStr(snappedMinutes + duration)

  optimisticMove(draggedTask.value.id, {
    due_date: dateString,
    estimated_time: startStr,
    end_time: endStr,
  })
  draggedTask.value = null
}

// Desktop/Mobile: drop без времени — только дата
const onDropDay = (e, dateString) => {
  e.preventDefault()
  dropTargetDate.value = null
  if (!draggedTask.value) return

  optimisticMove(draggedTask.value.id, { due_date: dateString })
  draggedTask.value = null
}

// Mobile: Touch Drag
const touchState = ref(null)
const touchClone = ref(null)

const onTouchStart = (e, task) => {
  const touch = e.touches[0]
  touchState.value = {
    task,
    startX: touch.clientX,
    startY: touch.clientY,
    isDragging: false,
    holdTimer: setTimeout(() => {
      if (!touchState.value) return
      touchState.value.isDragging = true
      draggedTask.value = task

      const el = e.target.closest('[data-task-id]')
      if (el) {
        const clone = el.cloneNode(true)
        clone.style.cssText = `
          position: fixed; z-index: 9999; pointer-events: none;
          width: ${el.offsetWidth}px; opacity: 0.8;
          transform: translate(-50%, -50%);
          left: ${touch.clientX}px; top: ${touch.clientY}px;
        `
        document.body.appendChild(clone)
        touchClone.value = clone
        el.style.opacity = '0.3'
      }
    }, 300),
  }
}

const onTouchMove = (e) => {
  if (!touchState.value) return
  const touch = e.touches[0]

  if (!touchState.value.isDragging) {
    const dx = Math.abs(touch.clientX - touchState.value.startX)
    const dy = Math.abs(touch.clientY - touchState.value.startY)
    if (dx > 10 || dy > 10) {
      clearTimeout(touchState.value.holdTimer)
      touchState.value = null
      return
    }
    return
  }

  e.preventDefault()

  if (touchClone.value) {
    touchClone.value.style.left = `${touch.clientX}px`
    touchClone.value.style.top = `${touch.clientY}px`
  }

  const el = document.elementFromPoint(touch.clientX, touch.clientY)
  const dropZone = el?.closest('[data-drop-date]')
  dropTargetDate.value = dropZone?.dataset.dropDate || null
}

const onTouchEnd = () => {
  if (!touchState.value) return
  clearTimeout(touchState.value.holdTimer)

  const wasDragging = touchState.value.isDragging
  const task = touchState.value.task

  if (touchClone.value) {
    touchClone.value.remove()
    touchClone.value = null
  }

  const el = document.querySelector(`[data-task-id="${task.id}"]`)
  if (el) el.style.opacity = '1'

  if (wasDragging && dropTargetDate.value && draggedTask.value) {
    optimisticMove(draggedTask.value.id, { due_date: dropTargetDate.value })
  }

  touchState.value = null
  draggedTask.value = null
  dropTargetDate.value = null
}

// Обновление линии текущего времени
let timeUpdateInterval = null

onMounted(() => {
  currentTime.value = dayjs()
  timeUpdateInterval = setInterval(() => {
    currentTime.value = dayjs()
  }, 60000)
  spheresStore.fetchAll()
})

onUnmounted(() => {
  if (timeUpdateInterval) {
    clearInterval(timeUpdateInterval)
  }
})
</script>

<style scoped>
.touch-manipulation {
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent;
}

/* Улучшение прокрутки на мобильных */
@media (max-width: 1023px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }
  
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
  }
}
</style>

