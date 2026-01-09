<template>
  <q-layout view="hHh lpR fFf">

    <q-header class="bg-transparent text-primary q-py-sm" :class="{'bg-white shadow-2': headerScrolled}">
      <q-toolbar class="container">
        <q-toolbar-title class="text-weight-bold row items-center">
          <q-icon name="school" size="md" color="secondary" class="q-mr-sm" />
          EMS Platform
        </q-toolbar-title>

        <div class="gt-sm row q-gutter-md">
          <q-btn flat label="Features" no-caps />
          <q-btn flat label="Use Cases" no-caps />
          <q-btn flat label="Pricing" no-caps />
          <q-btn flat label="Contact" no-caps />
        </div>

        <q-space />

        <div class="row q-gutter-md">
          <q-btn outline color="primary" label="Login" no-caps type="a" href="http://localhost:9000" />
          <q-btn unelevated color="secondary" label="Get Started" no-caps class="shadow-4" />
        </div>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>

    <q-footer class="bg-primary text-white q-py-xl">
      <div class="container text-center">
        <div class="text-h5 text-weight-bold q-mb-md">Ready to transform your education business?</div>
        <q-btn color="white" text-color="primary" label="Start Free Trial" size="lg" no-caps />
        <div class="q-mt-xl text-grey-5">
          © 2026 EMS Platform. All rights reserved.
        </div>
      </div>
    </q-footer>

  </q-layout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const headerScrolled = ref(false)

const handleScroll = () => {
  headerScrolled.value = window.scrollY > 50
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style lang="scss" scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}
</style>
