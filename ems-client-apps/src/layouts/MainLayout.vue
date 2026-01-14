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
            <q-menu>
              <q-list style="min-width: 220px">
                <q-item class="bg-primary text-white">
                  <q-item-section>
                    <div class="text-subtitle2">{{ authStore.user?.name || 'Guest' }}</div>
                    <div class="text-caption">{{ authStore.user?.username }}</div>
                  </q-item-section>
                </q-item>

                <q-separator />
                
                <q-item clickable v-close-popup to="/student/profile">
                  <q-item-section avatar><q-icon name="person" /></q-item-section>
                  <q-item-section>My Profile</q-item-section>
                </q-item>

                <q-separator />
                <q-item-label header class="text-grey-7">Switch Account</q-item-label>

                <q-item 
                  v-for="(acc, idx) in authStore.accounts" 
                  :key="idx" 
                  clickable 
                  v-close-popup 
                  @click="authStore.switchAccount(idx)"
                  :active="idx === authStore.activeAccountIndex"
                  active-class="bg-blue-1 text-primary"
                >
                   <q-item-section avatar>
                     <q-avatar size="24px" color="primary" text-color="white" font-size="12px">
                        {{ acc.user?.name?.charAt(0) || 'U' }}
                     </q-avatar>
                   </q-item-section>
                   <q-item-section>
                     <q-item-label>{{ acc.user?.name }}</q-item-label>
                     <q-item-label caption>{{ acc.user?.username }}</q-item-label>
                   </q-item-section>
                   <q-item-section side v-if="idx === authStore.activeAccountIndex">
                     <q-icon name="check" color="primary" size="xs" />
                   </q-item-section>
                </q-item>

                <q-item clickable v-close-popup href="http://localhost:9000/login">
                  <q-item-section avatar><q-icon name="person_add" /></q-item-section>
                  <q-item-section>Add Another Account</q-item-section>
                </q-item>

                <q-separator />

                <q-item clickable v-close-popup @click="authStore.logout()">
                  <q-item-section avatar><q-icon name="logout" color="negative" /></q-item-section>
                  <q-item-section class="text-negative">Logout</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
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
        <div class="text-subtitle1 text-weight-bold">{{ authStore.user?.name || 'Loading...' }}</div>
        <div class="text-caption text-grey">{{ authStore.user?.username }}</div>
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
        <q-item clickable v-ripple to="/student/attendance">
          <q-item-section avatar><q-icon name="how_to_reg" /></q-item-section>
          <q-item-section>Attendance</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/payments">
          <q-item-section avatar><q-icon name="receipt_long" /></q-item-section>
          <q-item-section>Payments</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/profile">
          <q-item-section avatar><q-icon name="person" /></q-item-section>
          <q-item-section>Profile</q-item-section>
        </q-item>
        <q-item clickable v-ripple @click="authStore.logout()">
          <q-item-section avatar><q-icon name="logout" /></q-item-section>
          <q-item-section>Logout</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useRoute } from 'vue-router'

const leftDrawerOpen = ref(false)
const authStore = useAuthStore()
const route = useRoute()

onMounted(async () => {
  // Initialize store (headers etc)
  authStore.init()

  if (route.query.token) {
    // Add new account from token
    await authStore.addAccountFromToken(route.query.token)
    
    // Clean URL
    const url = new URL(window.location.href)
    url.searchParams.delete('token')
    window.history.replaceState({}, document.title, url.toString())
  } else if (!authStore.user) {
    // Try to fetch user if token exists but no user loaded (rare with new logic but safe)
    await authStore.fetchUser()
  }
})

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>
