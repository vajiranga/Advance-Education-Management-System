<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-white text-primary">
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title class="text-weight-bold">
          <q-icon name="school" class="q-mr-sm" size="md" />
          EMS Admin Portal
        </q-toolbar-title>

        <div class="row q-gutter-sm items-center">
          <q-btn round flat icon="notifications" class="text-grey-7">
            <q-badge floating color="red" rounded />
          </q-btn>
          <q-avatar size="32px" class="cursor-pointer">
            <img src="https://cdn.quasar.dev/img/boy-avatar.png">
          </q-avatar>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="bg-grey-1"
    >
      <div class="q-pa-md text-center">
        <div class="text-h6 text-primary">Super Admin</div>
        <div class="text-caption text-grey">System Administrator</div>
      </div>

      <q-separator />

      <q-list class="q-mt-sm">
        <q-item
          v-for="link in navigationLinks"
          :key="link.title"
          clickable
          v-ripple
          :to="link.path"
          active-class="bg-blue-1 text-primary"
        >
          <q-item-section avatar>
            <q-icon :name="link.icon" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ link.title }}</q-item-label>
          </q-item-section>
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

defineOptions({
  name: 'MainLayout'
})

const navigationLinks = [
  { title: 'Dashboard', icon: 'dashboard', path: '/dashboard' },
  { title: 'Hall Management', icon: 'meeting_room', path: '/institutes' },
  { title: 'Users', icon: 'people', path: '/users' },
  { title: 'Classes', icon: 'library_books', path: '/courses' },
  { title: 'Attendance', icon: 'fact_check', path: '/attendance' },
  { title: 'Finance', icon: 'payments', path: '/finance' },
  { title: 'Settings', icon: 'settings', path: '/settings' }
]

const leftDrawerOpen = ref(false)

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>
