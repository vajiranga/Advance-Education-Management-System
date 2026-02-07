<template>
  <router-view />
</template>

<script setup>
import { useSettingsStore } from 'stores/settings-store'
const settings = useSettingsStore()
settings.fetchPublicSettings()
</script>
