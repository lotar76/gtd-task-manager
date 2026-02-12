<template>
  <div class="p-2 sm:p-4 lg:p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 lg:mb-6">
        <div>
          <h1 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900 dark:text-white">Календарь</h1>
        </div>
        
        <div class="flex items-center space-x-1 sm:space-x-2 mt-3 lg:mt-0 overflow-x-auto pb-2 lg:pb-0">
          <!-- View Mode Switcher -->
          <button
            @click="viewMode = 'day'"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'day' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 active:bg-gray-200 dark:active:bg-gray-600'"
          >
            День
          </button>
          <button
            @click="viewMode = 'week'"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'week' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 active:bg-gray-200 dark:active:bg-gray-600'"
          >
            Неделя
          </button>
          <button
            @click="viewMode = 'month'"
            class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors whitespace-nowrap touch-manipulation"
            :class="viewMode === 'month' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 active:bg-gray-200 dark:active:bg-gray-600'"
          >
            Месяц
          </button>
        </div>
      </div>

      <!-- Calendar Navigation -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 sm:p-4 mb-4 lg:mb-6">
        <div class="flex items-center justify-between">
          <button @click="previousPeriod" class="p-2 sm:p-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronLeftIcon class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700 dark:text-gray-300" />
          </button>

          <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white text-center px-2">
            {{ currentPeriodTitle }}
          </h2>

          <button @click="nextPeriod" class="p-2 sm:p-3 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 active:bg-gray-300 transition-colors touch-manipulation">
            <ChevronRightIcon class="w-5 h-5 sm:w-6 sm:h-6 text-gray-700 dark:text-gray-300" />
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
              <!-- Time Slots -->
              <div class="relative">
                <div
                  v-for="hour in weekHours"
                  :key="hour"
                  class="h-16 border-b border-gray-100 dark:border-gray-700/50"
                ></div>
                
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
                <div class="absolute inset-0">
                  <div
                    v-for="task in getTasksForDayWithTime(day.date)"
                    :key="task.id"
                    @click.stop="handleTaskClick(task)"
                    class="absolute left-1 right-1 rounded cursor-pointer p-1.5 text-xs touch-manipulation border-l-2 shadow-sm z-10"
                    :class="[
                      task.completed_at
                        ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 line-through border-gray-300 dark:border-gray-500'
                        : getDurationGradientClass(task) + ' text-primary-700 dark:text-primary-300 border-primary-500'
                    ]"
                    :style="getTaskPositionStyle(task, weekHours)"
                  >
                    <div class="flex items-start gap-1">
                      <button
                        @click.stop="handleToggleComplete(task)"
                        class="flex-shrink-0 w-3.5 h-3.5 mt-0.5 rounded border flex items-center justify-center transition-colors"
                        :class="task.completed_at
                          ? 'bg-green-500 border-green-500'
                          : 'border-gray-400 hover:border-primary-500'"
                      >
                        <svg v-if="task.completed_at" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                      <div class="flex-1 min-w-0">
                        <div class="font-medium truncate text-[11px]">{{ task.title }}</div>
                        <div v-if="task.estimated_time || task.end_time" class="text-[9px] opacity-75 mt-0.5">
                          <span v-if="task.estimated_time && task.end_time">
                            {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
                          </span>
                          <span v-else-if="task.end_time">
                            до {{ formatTime(task.end_time) }}
                          </span>
                          <span v-else-if="task.estimated_time">
                            {{ formatTime(task.estimated_time) }}
                          </span>
                        </div>
                        <!-- Compact badges -->
                        <div class="flex flex-wrap gap-0.5 mt-0.5">
                          <span v-if="task.project" class="inline-block px-0.5 rounded text-[8px] font-medium" :style="{ backgroundColor: task.project.color + '20', color: task.project.color }">{{ task.project.name }}</span>
                          <span v-if="task.assignee" class="inline-block px-0.5 rounded text-[8px] font-medium bg-gray-200 dark:bg-gray-600">{{ task.assignee.name }}</span>
                        </div>
                      </div>
                      <!-- Priority dot -->
                      <div
                        v-if="task.priority !== 'medium'"
                        class="flex-shrink-0 w-1.5 h-1.5 rounded-full mt-0.5"
                        :class="{
                          'bg-red-500': task.priority === 'urgent',
                          'bg-orange-500': task.priority === 'high',
                          'bg-blue-500': task.priority === 'low'
                        }"
                      />
                    </div>
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
              class="border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-2"
              :class="{
                'bg-primary-50/30 dark:bg-primary-900/20': day.isToday
              }"
            >
              <div class="space-y-1 min-w-0">
                <div
                  v-for="task in getTasksForDayWithoutTime(day.date)"
                  :key="task.id"
                  @click.stop="handleTaskClick(task)"
                  class="p-1.5 rounded cursor-pointer text-xs touch-manipulation border-l-2 w-full max-w-full overflow-hidden"
                  :class="[
                    task.completed_at
                      ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 line-through border-gray-300 dark:border-gray-500'
                      : getDurationGradientClass(task) + ' text-primary-700 dark:text-primary-300 border-primary-500'
                  ]"
                >
                  <div class="flex items-start gap-1">
                    <button
                      @click.stop="handleToggleComplete(task)"
                      class="flex-shrink-0 w-3.5 h-3.5 mt-0.5 rounded border flex items-center justify-center transition-colors"
                      :class="task.completed_at
                        ? 'bg-green-500 border-green-500'
                        : 'border-gray-400 hover:border-primary-500'"
                    >
                      <svg v-if="task.completed_at" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <div class="flex-1 min-w-0">
                      <div class="font-medium truncate block text-[11px]">{{ task.title }}</div>
                      <!-- Meta badges -->
                      <div class="flex flex-wrap gap-0.5 mt-0.5">
                        <span v-if="task.project" class="inline-block px-1 py-0 rounded text-[9px] font-medium" :style="{ backgroundColor: task.project.color + '20', color: task.project.color }">{{ task.project.name }}</span>
                        <span v-if="task.workspace" class="inline-block px-1 py-0 rounded text-[9px] font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">{{ task.workspace.name }}</span>
                        <span v-if="task.assignee" class="inline-block px-1 py-0 rounded text-[9px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ task.assignee.name }}</span>
                        <span v-if="task.context" class="inline-block px-1 py-0 rounded text-[9px] font-medium" :style="{ backgroundColor: task.context.color + '20', color: task.context.color }">{{ task.context.name }}</span>
                        <span v-for="tag in task.tags" :key="tag.id" class="inline-block px-1 py-0 rounded text-[9px] font-medium" :style="{ backgroundColor: tag.color + '20', color: tag.color }">{{ tag.name }}</span>
                      </div>
                    </div>
                    <!-- Priority dot -->
                    <div
                      v-if="task.priority !== 'medium'"
                      class="flex-shrink-0 w-1.5 h-1.5 rounded-full mt-1"
                      :class="{
                        'bg-red-500': task.priority === 'urgent',
                        'bg-orange-500': task.priority === 'high',
                        'bg-blue-500': task.priority === 'low'
                      }"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile: Horizontal Scroll -->
        <div class="lg:hidden overflow-x-auto">
          <div class="flex min-w-max">
            <div
              v-for="(day, index) in weekDaysData"
              :key="index"
              class="w-[280px] min-w-[280px] border-r border-gray-200 dark:border-gray-700 last:border-r-0 p-3"
              :class="{
                'bg-primary-50 dark:bg-primary-900/30': day.isToday
              }"
            >
              <div class="mb-3 sticky top-0 bg-inherit pb-2 border-b border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ weekDays[index] }}</div>
                <span
                  class="text-base font-semibold"
                  :class="{
                    'text-primary-700 dark:text-primary-400': day.isToday,
                    'text-gray-900 dark:text-white': !day.isToday
                  }"
                >
                  {{ day.day }} {{ day.monthName }}
                </span>
              </div>

              <!-- Tasks for this day -->
              <div class="space-y-2">
                <div
                  v-for="task in day.tasks"
                  :key="task.id"
                  @click="handleTaskClick(task)"
                  class="p-3 rounded-lg cursor-pointer touch-manipulation border-l-4"
                  :class="[
                    task.completed_at
                      ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 line-through border-gray-300 dark:border-gray-500'
                      : getDurationGradientClass(task) + ' text-primary-700 dark:text-primary-300 border-primary-500'
                  ]"
                >
                  <div class="flex items-start gap-2">
                    <button
                      @click.stop="handleToggleComplete(task)"
                      class="flex-shrink-0 w-4 h-4 mt-0.5 rounded border-2 flex items-center justify-center transition-colors"
                      :class="task.completed_at
                        ? 'bg-green-500 border-green-500'
                        : 'border-gray-400 hover:border-primary-500'"
                    >
                      <svg v-if="task.completed_at" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-sm mb-1">{{ task.title }}</div>

                      <!-- Meta информация -->
                      <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                        <!-- Workspace -->
                        <span
                          v-if="task.workspace"
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                        >
                          {{ task.workspace.name }}
                        </span>

                        <!-- Проект -->
                        <span
                          v-if="task.project"
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                          :style="{ backgroundColor: task.project.color + '20', color: task.project.color }"
                        >
                          {{ task.project.name }}
                        </span>

                        <!-- Время -->
                        <span v-if="task.estimated_time || task.end_time" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                          <span v-if="task.estimated_time && task.end_time">
                            {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
                          </span>
                          <span v-else-if="task.estimated_time">
                            {{ formatTime(task.estimated_time) }}
                          </span>
                          <span v-else-if="task.end_time">
                            до {{ formatTime(task.end_time) }}
                          </span>
                        </span>

                        <!-- Assignee -->
                        <span v-if="task.assignee" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                          {{ task.assignee.name }}
                        </span>

                        <!-- Context -->
                        <span
                          v-if="task.context"
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                          :style="{ backgroundColor: task.context.color + '20', color: task.context.color }"
                        >
                          {{ task.context.name }}
                        </span>

                        <!-- Tags -->
                        <span
                          v-for="tag in task.tags"
                          :key="tag.id"
                          class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                          :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                        >
                          {{ tag.name }}
                        </span>
                      </div>
                    </div>

                    <!-- Priority Badge -->
                    <div
                      v-if="task.priority !== 'medium'"
                      class="flex-shrink-0 w-2 h-2 rounded-full mt-2"
                      :class="{
                        'bg-red-500': task.priority === 'urgent',
                        'bg-orange-500': task.priority === 'high',
                        'bg-blue-500': task.priority === 'low'
                      }"
                    />
                  </div>
                </div>
                <div v-if="day.tasks.length === 0" class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">
                  Нет задач
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Day View -->
      <div v-if="viewMode === 'day'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
            {{ currentDate.format('D MMMM YYYY') }}
          </h3>
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

            <!-- Time Slots Column -->
            <div class="relative">
              <!-- Time Slots -->
              <div class="relative">
                <div
                  v-for="hour in dayHours"
                  :key="hour"
                  class="h-16 border-b border-gray-100 dark:border-gray-700/50"
                ></div>
                
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
                <div class="absolute inset-0">
                  <div
                    v-for="task in dayTasksWithTime"
                    :key="task.id"
                    @click.stop="handleTaskClick(task)"
                    class="absolute left-2 right-2 rounded-lg cursor-pointer p-2 sm:p-3 border-l-4 shadow-sm z-10 touch-manipulation"
                    :class="[
                      task.completed_at
                        ? 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-500 line-through'
                        : getDurationGradientClass(task) + ' border-primary-500'
                    ]"
                    :style="getTaskPositionStyle(task, dayHours)"
                  >
                    <div class="flex items-start gap-2">
                      <button
                        @click.stop="handleToggleComplete(task)"
                        class="flex-shrink-0 w-5 h-5 mt-0.5 rounded border-2 flex items-center justify-center transition-colors"
                        :class="task.completed_at
                          ? 'bg-green-500 border-green-500'
                          : 'border-gray-300 hover:border-primary-500'"
                      >
                        <svg v-if="task.completed_at" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                      <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm sm:text-base text-gray-900 dark:text-white mb-1">{{ task.title }}</div>
                        <div v-if="task.description" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ task.description }}</div>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                          <span v-if="task.estimated_time || task.end_time" class="text-xs text-gray-600 dark:text-gray-400 flex items-center">
                            <ClockIcon class="w-3 h-3 mr-1" />
                            <span v-if="task.estimated_time && task.end_time">
                              {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
                            </span>
                            <span v-else-if="task.estimated_time">
                              {{ formatTime(task.estimated_time) }}
                            </span>
                            <span v-else-if="task.end_time">
                              до {{ formatTime(task.end_time) }}
                            </span>
                          </span>
                          <span
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="{
                              'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': task.priority === 'urgent',
                              'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': task.priority === 'high',
                              'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': task.priority === 'medium',
                              'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': task.priority === 'low'
                            }"
                          >
                            {{ getPriorityLabel(task.priority) }}
                          </span>

                          <!-- Workspace -->
                          <span
                            v-if="task.workspace"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                          >
                            {{ task.workspace.name }}
                          </span>

                          <!-- Project -->
                          <span
                            v-if="task.project"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                            :style="{ backgroundColor: task.project.color + '20', color: task.project.color }"
                          >
                            {{ task.project.name }}
                          </span>

                          <!-- Assignee -->
                          <span v-if="task.assignee" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            {{ task.assignee.name }}
                          </span>

                          <!-- Context -->
                          <span
                            v-if="task.context"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                            :style="{ backgroundColor: task.context.color + '20', color: task.context.color }"
                          >
                            {{ task.context.name }}
                          </span>

                          <!-- Tags -->
                          <span
                            v-for="tag in task.tags"
                            :key="tag.id"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                            :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                          >
                            {{ tag.name }}
                          </span>
                        </div>
                      </div>
                    </div>
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
                  @click.stop="handleTaskClick(task)"
                  class="p-3 rounded-lg cursor-pointer border-l-4 touch-manipulation"
                  :class="[
                    task.completed_at
                      ? 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-500 line-through'
                      : getDurationGradientClass(task) + ' border-primary-500'
                  ]"
                >
                  <div class="flex items-start gap-2">
                    <button
                      @click.stop="handleToggleComplete(task)"
                      class="flex-shrink-0 w-5 h-5 mt-0.5 rounded border-2 flex items-center justify-center transition-colors"
                      :class="task.completed_at
                        ? 'bg-green-500 border-green-500'
                        : 'border-gray-300 hover:border-primary-500'"
                    >
                      <svg v-if="task.completed_at" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-sm sm:text-base text-gray-900 dark:text-white mb-1">{{ task.title }}</div>
                      <div v-if="task.description" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ task.description }}</div>
                      <div class="flex flex-wrap items-center gap-2 mt-2">
                        <!-- Time -->
                        <span v-if="task.estimated_time || task.end_time" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                          <ClockIcon class="w-3 h-3 mr-1" />
                          <span v-if="task.estimated_time && task.end_time">
                            {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
                          </span>
                          <span v-else-if="task.estimated_time">
                            {{ formatTime(task.estimated_time) }}
                          </span>
                          <span v-else-if="task.end_time">
                            до {{ formatTime(task.end_time) }}
                          </span>
                        </span>

                        <!-- Priority -->
                        <span
                          class="text-xs px-2 py-0.5 rounded-full"
                          :class="{
                            'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': task.priority === 'urgent',
                            'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': task.priority === 'high',
                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': task.priority === 'medium',
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': task.priority === 'low'
                          }"
                        >
                          {{ getPriorityLabel(task.priority) }}
                        </span>

                        <!-- Workspace -->
                        <span
                          v-if="task.workspace"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                        >
                          {{ task.workspace.name }}
                        </span>

                        <!-- Project -->
                        <span
                          v-if="task.project"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                          :style="{ backgroundColor: task.project.color + '20', color: task.project.color }"
                        >
                          {{ task.project.name }}
                        </span>

                        <!-- Assignee -->
                        <span v-if="task.assignee" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                          {{ task.assignee.name }}
                        </span>

                        <!-- Context -->
                        <span
                          v-if="task.context"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                          :style="{ backgroundColor: task.context.color + '20', color: task.context.color }"
                        >
                          {{ task.context.name }}
                        </span>

                        <!-- Tags -->
                        <span
                          v-for="tag in task.tags"
                          :key="tag.id"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                          :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                        >
                          {{ tag.name }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile: Simple List -->
        <div class="lg:hidden p-3 sm:p-4">
          <div class="space-y-2 sm:space-y-3">
            <div
              v-for="task in dayTasks"
              :key="task.id"
              @click="handleTaskClick(task)"
              class="p-3 sm:p-4 rounded-lg cursor-pointer border-l-4 touch-manipulation active:scale-[0.98] transition-transform"
              :class="[
                task.completed_at
                  ? 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-500 line-through'
                  : getDurationGradientClass(task) + ' border-primary-500'
              ]"
            >
              <div class="flex items-start gap-3">
                <button
                  @click.stop="handleToggleComplete(task)"
                  class="flex-shrink-0 w-5 h-5 mt-0.5 rounded border-2 flex items-center justify-center transition-colors"
                  :class="task.completed_at
                    ? 'bg-green-500 border-green-500'
                    : 'border-gray-300 hover:border-primary-500'"
                >
                  <svg v-if="task.completed_at" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-sm sm:text-base text-gray-900 dark:text-white mb-1">{{ task.title }}</div>
                  <div v-if="task.description" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ task.description }}</div>
                  <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span v-if="task.estimated_time || task.end_time" class="text-xs text-gray-600 dark:text-gray-400 flex items-center">
                      <ClockIcon class="w-3 h-3 mr-1" />
                      <span v-if="task.estimated_time && task.end_time">
                        {{ formatTime(task.estimated_time) }} - {{ formatTime(task.end_time) }}
                      </span>
                      <span v-else-if="task.estimated_time">
                        {{ formatTime(task.estimated_time) }}
                      </span>
                      <span v-else-if="task.end_time">
                        до {{ formatTime(task.end_time) }}
                      </span>
                    </span>
                    <span
                      class="text-xs px-2 py-0.5 rounded-full"
                      :class="{
                        'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400': task.priority === 'urgent',
                        'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400': task.priority === 'high',
                        'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': task.priority === 'medium',
                        'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300': task.priority === 'low'
                      }"
                    >
                      {{ getPriorityLabel(task.priority) }}
                    </span>

                    <!-- Workspace -->
                    <span
                      v-if="task.workspace"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400"
                    >
                      {{ task.workspace.name }}
                    </span>

                    <!-- Проект -->
                    <span
                      v-if="task.project"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :style="{ backgroundColor: task.project.color + '20', color: task.project.color }"
                    >
                      {{ task.project.name }}
                    </span>

                    <!-- Assignee -->
                    <span v-if="task.assignee" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                      {{ task.assignee.name }}
                    </span>

                    <!-- Context -->
                    <span
                      v-if="task.context"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :style="{ backgroundColor: task.context.color + '20', color: task.context.color }"
                    >
                      {{ task.context.name }}
                    </span>

                    <!-- Tags -->
                    <span
                      v-for="tag in task.tags"
                      :key="tag.id"
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :style="{ backgroundColor: tag.color + '20', color: tag.color }"
                    >
                      {{ tag.name }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="dayTasks.length === 0" class="text-center py-8 sm:py-12 text-gray-500 dark:text-gray-400 text-sm sm:text-base">
              Нет задач на этот день
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

      <!-- Task Modal -->
      <TaskModal
        :show="showTaskModal"
        :task="selectedTask"
        :server-error="taskError"
        :default-date="newTaskDate"
        @close="handleCloseTaskModal"
        @submit="handleSaveTask"
      />

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
import { useRoute } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'
import TaskModal from '@/components/tasks/TaskModal.vue'
import TaskView from '@/components/tasks/TaskView.vue'
import ConfirmDialog from '@/components/common/ConfirmDialog.vue'
import dayjs from 'dayjs'
import 'dayjs/locale/ru'
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter'
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'
import { ChevronLeftIcon, ChevronRightIcon, ClockIcon, PlusIcon } from '@heroicons/vue/24/outline'

dayjs.extend(isSameOrAfter)
dayjs.extend(isSameOrBefore)
dayjs.locale('ru')

const route = useRoute()
const tasksStore = useTasksStore()

const currentDate = ref(dayjs())
const loading = computed(() => tasksStore.loading)
const viewMode = ref('month')

// Следим за изменениями query параметра view
watch(() => route.query.view, (newView) => {
  if (newView && ['day', 'week', 'month'].includes(newView)) {
    viewMode.value = newView
  }
}, { immediate: true })
const showTaskModal = ref(false)
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
    const dayTasks = tasks.value.filter(task => 
      task.due_date && 
      dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
      task.status !== 'completed'
    )
    
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

const dayTasks = computed(() => {
  const dateString = currentDate.value.format('YYYY-MM-DD')
  return tasks.value.filter(task => 
    task.due_date && 
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed'
  )
})

// Задачи для дня с временем (для отображения в сетке)
const dayTasksWithTime = computed(() => {
  return dayTasks.value.filter(task => task.estimated_time || task.end_time)
})

// Задачи для дня без времени (для отображения ниже сетки)
const dayTasksWithoutTime = computed(() => {
  return dayTasks.value.filter(task => !task.estimated_time && !task.end_time)
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
  const days = []
  const startOfMonth = currentDate.value.startOf('month')
  const endOfMonth = currentDate.value.endOf('month')
  const startDay = startOfMonth.day() === 0 ? 6 : startOfMonth.day() - 1 // Понедельник = 0
  
  // Предыдущий месяц
  for (let i = startDay - 1; i >= 0; i--) {
    const date = startOfMonth.subtract(i + 1, 'day')
    days.push(createDayObject(date, false))
  }
  
  // Текущий месяц
  for (let i = 0; i < endOfMonth.date(); i++) {
    const date = startOfMonth.add(i, 'day')
    days.push(createDayObject(date, true))
  }
  
  // Следующий месяц
  const remainingDays = 42 - days.length // 6 недель
  for (let i = 0; i < remainingDays; i++) {
    const date = endOfMonth.add(i + 1, 'day')
    days.push(createDayObject(date, false))
  }
  
  return days
})

const createDayObject = (date, currentMonth) => {
  const dateString = date.format('YYYY-MM-DD')
  const dayTasks = tasks.value.filter(task => 
    task.due_date && 
    dayjs(task.due_date).format('YYYY-MM-DD') === dateString &&
    task.status !== 'completed'
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

  if (day.isToday) {
    base.push('ring-2 ring-inset ring-primary-500')
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
  showTaskModal.value = true
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

const handleAddTaskForDay = (dateString) => {
  selectedTask.value = null
  newTaskDate.value = dateString
  showTaskModal.value = true
}

const handleDayClick = (dateString) => {
  currentDate.value = dayjs(dateString)
  viewMode.value = 'day'
}

const handleSaveTask = async (taskData) => {
  taskError.value = ''
  try {
    if (selectedTask.value) {
      await tasksStore.updateTask(selectedTask.value.id, taskData)
    } else {
      await tasksStore.createTask(taskData)
    }
    showTaskModal.value = false
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

const handleCloseTaskModal = () => {
  showTaskModal.value = false
  selectedTask.value = null
  newTaskDate.value = ''
  taskError.value = ''
}

const handleToggleComplete = (task) => {
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

// Обновление линии текущего времени
let timeUpdateInterval = null

onMounted(() => {
  currentTime.value = dayjs()
  timeUpdateInterval = setInterval(() => {
    currentTime.value = dayjs()
  }, 60000)
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

