<template>
  <q-layout view="lHh Lpr lFf" class="bg-grey-1">
    <q-header class="glass-nav text-dark">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <span class="text-primary">EMS</span> <span class="q-ml-sm text-subtitle2">Student Portal</span>
        </q-toolbar-title>

        <q-space />

        <div class="row q-gutter-sm items-center">
          <q-btn round flat icon="search" color="grey-7" />
          <q-btn round flat icon="notifications_none" color="grey-7">
            <q-badge floating color="red" rounded dot />
          </q-btn>
          <q-avatar size="36px" class="q-ml-sm cursor-pointer shadow-1">
            <img src="https://cdn.quasar.dev/img/avatar2.jpg">
          </q-avatar>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="bg-white"
    >
      <div class="q-pa-md">
        <div class="text-subtitle1 text-weight-bold">Kasun Perera</div>
        <div class="text-caption text-grey">Physics 2026 Batch</div>
      </div>
      <q-separator />
      <q-list class="q-mt-md">
        <q-item clickable v-ripple to="/student/dashboard" active-class="text-primary bg-indigo-1">
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/courses">
          <q-item-section avatar><q-icon name="library_books" /></q-item-section>
          <q-item-section>My Courses</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/exams">
          <q-item-section avatar><q-icon name="assignment" /></q-item-section>
          <q-item-section>Exams & Results</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/payments">
          <q-item-section avatar><q-icon name="receipt_long" /></q-item-section>
          <q-item-section>Payments</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'

const leftDrawerOpen = ref(false)

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>
