<template>
  <q-layout view="lHh Lpr lFf" :class="$q.dark.isActive ? 'bg-dark-page' : 'bg-grey-1'">
    <q-header elevated :class="$q.dark.isActive ? 'bg-dark text-white border-bottom-dark' : 'bg-white text-primary'">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <q-icon name="school" size="md" class="q-mr-sm" />
          <span>EMS</span> <span class="q-ml-sm text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Teacher Portal</span>
        </q-toolbar-title>

        <q-space />

        <div class="row q-gutter-sm items-center">
          <q-btn 
            flat 
            round 
            :icon="$q.dark.isActive ? 'light_mode' : 'dark_mode'" 
            :color="$q.dark.isActive ? 'yellow' : 'grey-7'" 
            @click="$q.dark.toggle()" 
          >
            <q-tooltip>Toggle {{ $q.dark.isActive ? 'Light' : 'Dark' }} Mode</q-tooltip>
          </q-btn>

          <q-btn round flat icon="notifications" :color="$q.dark.isActive ? 'white' : 'grey-7'">
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
          <q-avatar size="36px" class="cursor-pointer bg-primary text-white shadow-1">
             <span class="text-weight-bold">{{ authStore.user?.name?.charAt(0) || 'T' }}</span>
             <q-menu :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white'">
               <q-list style="min-width: 220px">
                 <q-item class="bg-primary text-white">
                   <q-item-section>
                     <div class="text-subtitle2">{{ authStore.user?.name || 'Teacher' }}</div>
                     <div class="text-caption">{{ displayTeacherId }}</div>
                   </q-item-section>
                 </q-item>

                 <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                 
                 <q-item clickable v-close-popup to="/teacher/dashboard">
                   <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
                   <q-item-section>Dashboard</q-item-section>
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
            <q-icon name="cast_for_education" size="sm" class="q-mr-sm" :color="$q.dark.isActive ? 'white' : 'primary'" />
            <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">EMS Portal</div>
         </div>
         <div class="text-caption q-ml-none" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Teacher Dashboard</div>
      </div>
      
      <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
      
      <q-list class="q-mt-md">
        <q-item clickable v-ripple to="/teacher/dashboard" :active-class="$q.dark.isActive ? 'text-teal-2 bg-grey-9 border-l-teal' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple to="/teacher/classes" :active-class="$q.dark.isActive ? 'text-teal-2 bg-grey-9 border-l-teal' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="class" /></q-item-section>
          <q-item-section>My Classes</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple to="/teacher/attendance" :active-class="$q.dark.isActive ? 'text-teal-2 bg-grey-9 border-l-teal' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="fact_check" /></q-item-section>
          <q-item-section>Attendance</q-item-section>
        </q-item>

         <q-item clickable v-ripple to="/teacher/exams" :active-class="$q.dark.isActive ? 'text-teal-2 bg-grey-9 border-l-teal' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="assignment" /></q-item-section>
          <q-item-section>Exams & Marks</q-item-section>
        </q-item>

        <q-separator class="q-my-md" :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

        <!-- Sidebar logout removed as requested -->
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useQuasar } from 'quasar'

const leftDrawerOpen = ref(false)
const authStore = useAuthStore()
const $q = useQuasar()

// Visual fix for ID display (STU -> TCH)
const displayTeacherId = computed(() => {
    let id = authStore.user?.username || ''
    if (id && id.startsWith('STU')) {
        return id.replace('STU', 'TCH')
    }
    return id
})

const notifications = ref([
    { id: 1, title: 'Class Cancelled', message: 'Physics 2026 class postponed.', time: '1 hour ago' }
])

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

<style scoped>
.opacity-80 {
  opacity: 0.8;
}
.display-xs-none {
  @media (max-width: 599px) {
    display: none;
  }
}
</style>
