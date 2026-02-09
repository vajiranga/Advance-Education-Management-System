<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-white text-primary">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <img
            v-if="settings.logoUrl"
            :src="settings.logoUrl"
            style="height: 36px; object-fit: contain; margin-right: 8px;"
            alt="Logo"
          />
          <q-icon v-else name="school" class="q-mr-sm" size="md" />
          {{ settings.instituteName }}
        </q-toolbar-title>

        <div class="row q-gutter-sm items-center">
          <q-btn flat round>
            <q-avatar size="32px" class="bg-white shadow-1">
               <img v-if="settings.logoUrl" :src="settings.logoUrl" style="object-fit: contain" />
               <q-icon v-else name="account_circle" color="grey" />
            </q-avatar>
            <q-menu>
              <q-list style="min-width: 150px">
                <q-item clickable v-close-popup to="/settings">
                   <q-item-section avatar><q-icon name="settings" /></q-item-section>
                   <q-item-section>Settings</q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup @click="logout">
                   <q-item-section avatar><q-icon name="logout" color="red" /></q-item-section>
                   <q-item-section class="text-red">Logout</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="bg-grey-1">
      <div class="q-pa-md text-center">
         <q-avatar size="80px" class="q-mb-sm shadow-1 bg-white">
             <img v-if="settings.logoUrl" :src="settings.logoUrl" style="object-fit: contain" />
             <q-icon v-else name="account_circle" size="60px" color="grey-5" />
         </q-avatar>
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

import { useSettingsStore } from 'stores/settings-store'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth-store'

defineOptions({
  name: 'MainLayout',
})

const settings = useSettingsStore()
const authStore = useAuthStore()
const router = useRouter()

function logout() {
    authStore.logout()
    router.replace('/login')
}

import { onMounted } from 'vue'

onMounted(() => {
    settings.fetchPublicSettings()
})

const navigationLinks = [
  { title: 'Dashboard', icon: 'dashboard', path: '/dashboard' },
  { title: 'Hall Management', icon: 'meeting_room', path: '/institutes' },
  { title: 'Users', icon: 'people', path: '/users' },
  { title: 'Classes', icon: 'library_books', path: '/courses' },
  { title: 'Attendance', icon: 'fact_check', path: '/attendance' },
  { title: 'Cash Payment', icon: 'point_of_sale', path: '/cashpayment' },
  { title: 'Finance', icon: 'payments', path: '/finance' },
  { title: 'Settings', icon: 'settings', path: '/settings' },
]

const leftDrawerOpen = ref(false)

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>
