<template>
  <router-view />
</template>

<script setup>
import { useAuthStore } from 'stores/auth-store'
import { useQuasar } from 'quasar'
import { onMounted, watch } from 'vue'

const authStore = useAuthStore()
const $q = useQuasar()

authStore.init()

// Theme Persistence Logic
onMounted(() => {
  const savedTheme = localStorage.getItem('ems-client-theme')
  if (savedTheme) {
    $q.dark.set(savedTheme === 'dark')
  } else {
    $q.dark.set(false) // Default to Light Mode
  }
})

watch(() => $q.dark.isActive, (val) => {
  localStorage.setItem('ems-client-theme', val ? 'dark' : 'light')
})
</script>
