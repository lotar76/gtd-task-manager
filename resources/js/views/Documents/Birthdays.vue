<template>
  <div class="p-4 lg:p-8">
    <div class="flex items-center gap-3 mb-6">
      <router-link to="/documents" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
      </router-link>
      <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Дни рождения</h1>
    </div>
    <BirthdayList />
  </div>
</template>

<script setup>
import BirthdayList from '@/components/library/BirthdayList.vue'
</script>
