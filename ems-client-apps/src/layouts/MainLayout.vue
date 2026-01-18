<template>
  <q-layout view="lHh Lpr lFf" :class="$q.dark.isActive ? 'bg-dark-page text-white' : 'bg-grey-1'">
    <q-header elevated :class="$q.dark.isActive ? 'bg-dark text-white border-bottom-dark' : 'bg-white text-primary'">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <q-icon name="school" size="md" class="q-mr-sm" />
          <span>EMS</span> 
          <span class="q-ml-sm text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">
            Student Portal
          </span>
        </q-toolbar-title>

        <q-space />

        <div class="row q-gutter-sm items-center">
          <!-- Dark Mode Toggle -->
          <q-btn 
            flat 
            round 
            :icon="$q.dark.isActive ? 'light_mode' : 'dark_mode'" 
            :color="$q.dark.isActive ? 'yellow' : 'grey-7'" 
            @click="$q.dark.toggle()" 
          >
            <q-tooltip>Toggle {{ $q.dark.isActive ? 'Light' : 'Dark' }} Mode</q-tooltip>
          </q-btn>

          <!-- Notification Button -->
          <q-btn round flat icon="notifications_none" :color="$q.dark.isActive ? 'white' : 'grey-7'">
            <q-badge floating color="red" rounded dot v-if="notifications.length > 0" />
            <q-menu :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white'">
               <q-list style="min-width: 300px">
                  <q-item-label header :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">Notifications</q-item-label>
                  
                  <q-item v-for="note in notifications" :key="note.id" clickable v-close-popup class="q-py-md">
                     <q-item-section avatar>
                        <q-avatar color="primary" text-color="white" icon="info" font-size="20px" size="md" />
                     </q-item-section>
                     <q-item-section>
                        <q-item-label>{{ note.title }}</q-item-label>
                        <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ note.message }}</q-item-label>
                        <div class="text-caption text-grey-6 q-mt-xs">{{ note.time }}</div>
                     </q-item-section>
                  </q-item>

                  <div v-if="notifications.length === 0" class="text-center q-pa-lg text-grey">
                      <q-icon name="notifications_off" size="xl" class="q-mb-sm" />
                      <div>No new notifications</div>
                  </div>
               </q-list>
            </q-menu>
          </q-btn>

          <q-avatar size="36px" class="q-ml-sm cursor-pointer shadow-1 bg-primary text-white">
            <span class="text-weight-bold">{{ authStore.user?.name?.charAt(0) || 'S' }}</span>
            <q-menu :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white'">
              <q-list style="min-width: 220px">
                <q-item class="bg-primary text-white">
                  <q-item-section>
                    <div class="text-subtitle2">{{ authStore.user?.name || 'Student' }}</div>
                    <div class="text-caption">{{ authStore.user?.username }}</div>
                  </q-item-section>
                </q-item>

                <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                
                <q-item clickable v-close-popup to="/student/profile">
                  <q-item-section avatar><q-icon name="person" /></q-item-section>
                  <q-item-section>My Profile</q-item-section>
                </q-item>

                <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                <q-item-label header :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">Switch Account</q-item-label>

                <q-item 
                  v-for="(acc, idx) in authStore.accounts" 
                  :key="idx" 
                  clickable 
                  v-close-popup 
                  @click="authStore.switchAccount(idx)"
                  :active="idx === authStore.activeAccountIndex"
                  :active-class="$q.dark.isActive ? 'bg-grey-8 text-primary' : 'bg-blue-1 text-primary'"
                >
                   <q-item-section avatar>
                     <q-avatar size="24px" color="primary" text-color="white" font-size="12px">
                        {{ acc.user?.name?.charAt(0) || 'U' }}
                     </q-avatar>
                   </q-item-section>
                   <q-item-section>
                     <q-item-label>{{ acc.user?.name }}</q-item-label>
                     <q-item-label caption class="text-grey">{{ acc.user?.username }}</q-item-label>
                   </q-item-section>
                   <q-item-section side v-if="idx === authStore.activeAccountIndex">
                     <q-icon name="check" color="primary" size="xs" />
                   </q-item-section>
                </q-item>

                <q-item clickable v-close-popup href="http://localhost:9000/login">
                  <q-item-section avatar><q-icon name="person_add" /></q-item-section>
                  <q-item-section>Add Another Account</q-item-section>
                </q-item>

                <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

                <q-item clickable v-close-popup @click="authStore.logout('/login')">
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
      :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'"
    >
      <div class="q-pa-lg">
         <div class="row items-center">
            <q-icon name="school" size="sm" class="q-mr-sm" :color="$q.dark.isActive ? 'white' : 'primary'" />
            <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">EMS Portal</div>
         </div>
         <div class="text-caption q-ml-none" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Student Dashboard</div>
      </div>
      <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
      <q-list class="q-mt-md">
        <q-item clickable v-ripple to="/student/dashboard" :active-class="$q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/courses" :active-class="$q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="library_books" /></q-item-section>
          <q-item-section>My Classes</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/exams" :active-class="$q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="assignment" /></q-item-section>
          <q-item-section>Exams & Results</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/attendance" :active-class="$q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="how_to_reg" /></q-item-section>
          <q-item-section>Attendance</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/payments" :active-class="$q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="receipt_long" /></q-item-section>
          <q-item-section>Payments</q-item-section>
        </q-item>
        <q-item clickable v-ripple to="/student/profile" :active-class="$q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="person" /></q-item-section>
          <q-item-section>Profile</q-item-section>
        </q-item>
        
        <!-- Sidebar logout removed -->
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
import { useQuasar } from 'quasar'

const leftDrawerOpen = ref(false)
const authStore = useAuthStore()
const route = useRoute()
const $q = useQuasar()

const notifications = ref([
    { id: 1, title: 'Welcome to EMS', message: 'Your student portal is ready to use.', time: 'Just now' },
    { id: 2, title: 'Exam Schedule Released', message: 'The schedule for Physics has been updated.', time: '2 hours ago' }
])

// Set default dark mode if saved or system preference (Quasar might do this auto if configured)
// For now, let's verify if toggle works.

onMounted(async () => {
    // ... logic ...
    authStore.init()

  if (route.query.token) {
    // Add new account from token
    await authStore.addAccountFromToken(route.query.token)
    
    // Clean URL
    const url = new URL(window.location.href)
    url.searchParams.delete('token')
    window.history.replaceState({}, document.title, url.toString())
  } else if (!authStore.user) {
    // Try to fetch user if token exists but no user loaded
    await authStore.fetchUser()
  }
})

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>