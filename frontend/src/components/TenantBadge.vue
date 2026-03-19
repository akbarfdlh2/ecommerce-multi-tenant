<template>
  <span v-if="domain" class="badge bg-indigo-100 text-indigo-700 text-xs">
    🏪 {{ displayName }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const domain = computed(() => localStorage.getItem('tenant_domain') || '')

const displayName = computed(() => {
  if (!domain.value) return ''
  // Convert "my-store.localhost" → "My Store"
  return domain.value
    .replace(/\.localhost$|\.local$/, '')
    .split('-')
    .map(w => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ')
})
</script>
