<template>
  <router-view />
</template>

<script setup>
import { useAuthStore } from 'stores/auth-store'

const authStore = useAuthStore()
authStore.init()
</script>
