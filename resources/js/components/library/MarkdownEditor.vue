<template>
  <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
    <!-- Toolbar -->
    <div class="flex items-center gap-0.5 px-2 py-1.5 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex-wrap">
      <button type="button" @click="wrap('**', '**')" class="toolbar-btn" title="Bold">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>
      </button>
      <button type="button" @click="wrap('*', '*')" class="toolbar-btn" title="Italic">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>
      </button>
      <button type="button" @click="wrap('~~', '~~')" class="toolbar-btn" title="Strikethrough">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 19h4v-3h-4v3zM5 4v3h5v3h4V7h5V4H5zM3 14h18v-2H3v2z"/></svg>
      </button>
      <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
      <button type="button" @click="prefixLine('# ')" class="toolbar-btn" title="Heading 1">
        <span class="text-xs font-bold">H1</span>
      </button>
      <button type="button" @click="prefixLine('## ')" class="toolbar-btn" title="Heading 2">
        <span class="text-xs font-bold">H2</span>
      </button>
      <button type="button" @click="prefixLine('### ')" class="toolbar-btn" title="Heading 3">
        <span class="text-xs font-bold">H3</span>
      </button>
      <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
      <button type="button" @click="prefixLine('- ')" class="toolbar-btn" title="Bullet list">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg>
      </button>
      <button type="button" @click="prefixLine('1. ')" class="toolbar-btn" title="Numbered list">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg>
      </button>
      <button type="button" @click="prefixLine('> ')" class="toolbar-btn" title="Quote">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
      </button>
      <div class="w-px h-5 bg-gray-300 dark:bg-gray-600 mx-1"></div>
      <button type="button" @click="insertLink" class="toolbar-btn" title="Link">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
      </button>
      <button type="button" @click="wrap('`', '`')" class="toolbar-btn" title="Code">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" /></svg>
      </button>
      <div class="ml-auto">
        <button type="button" @click="$emit('toggle-preview')" class="toolbar-btn text-primary-600 dark:text-primary-400">
          <svg v-if="!preview" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
        </button>
      </div>
    </div>

    <!-- Editor -->
    <textarea
      v-if="!preview"
      ref="textarea"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :rows="rows"
      class="w-full bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white font-mono resize-y outline-none border-0"
    ></textarea>

    <!-- Preview -->
    <div
      v-else
      class="w-full bg-white dark:bg-gray-900 px-4 py-3 text-sm text-gray-900 dark:text-white prose prose-sm dark:prose-invert max-w-none overflow-y-auto"
      :style="{ minHeight: rows * 1.5 + 'rem' }"
      v-html="renderedContent"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { marked } from 'marked'

marked.setOptions({ breaks: true, gfm: true })

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  rows: { type: Number, default: 12 },
  preview: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'toggle-preview'])
const textarea = ref(null)

const renderedContent = computed(() => {
  if (!props.modelValue) return '<p style="color: #999;">Нет содержимого</p>'
  return marked.parse(props.modelValue)
})

const wrap = (before, after) => {
  const el = textarea.value
  if (!el) return
  const start = el.selectionStart
  const end = el.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end)
  const replacement = before + (selected || 'текст') + after
  const newText = text.substring(0, start) + replacement + text.substring(end)
  emit('update:modelValue', newText)
  requestAnimationFrame(() => {
    el.focus()
    if (selected) {
      el.setSelectionRange(start + before.length, start + before.length + selected.length)
    } else {
      el.setSelectionRange(start + before.length, start + before.length + 5)
    }
  })
}

const prefixLine = (prefix) => {
  const el = textarea.value
  if (!el) return
  const start = el.selectionStart
  const text = props.modelValue
  const lineStart = text.lastIndexOf('\n', start - 1) + 1
  const newText = text.substring(0, lineStart) + prefix + text.substring(lineStart)
  emit('update:modelValue', newText)
  requestAnimationFrame(() => {
    el.focus()
    el.setSelectionRange(start + prefix.length, start + prefix.length)
  })
}

const insertLink = () => {
  const el = textarea.value
  if (!el) return
  const start = el.selectionStart
  const end = el.selectionEnd
  const text = props.modelValue
  const selected = text.substring(start, end)
  const replacement = '[' + (selected || 'текст') + '](url)'
  const newText = text.substring(0, start) + replacement + text.substring(end)
  emit('update:modelValue', newText)
  requestAnimationFrame(() => {
    el.focus()
    const urlStart = start + (selected ? selected.length : 5) + 2
    el.setSelectionRange(urlStart, urlStart + 3)
  })
}
</script>

<style scoped>
.toolbar-btn {
  @apply p-1.5 rounded text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors;
}
</style>
