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
            <q-badge floating color="red" rounded dot />
          </q-btn>
          <div class="column items-end q-mr-sm display-xs-none" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
             <div class="text-subtitle2" style="line-height: 1.2">{{ authStore.user?.name || 'Teacher' }}</div>
             <div class="text-caption opacity-80" style="font-size: 10px;">{{ authStore.user?.username || 'ID: ???' }}</div>
          </div>
          <q-avatar size="36px" class="cursor-pointer bg-primary text-white">
            <span class="text-weight-bold">{{ authStore.user?.name?.charAt(0) || 'T' }}</span>
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
      <div class="q-pa-md" :class="$q.dark.isActive ? 'bg-transparent' : 'bg-blue-1'">
        <div class="row items-center q-pa-sm">
           <q-avatar size="48px" class="bg-white text-primary">
             <img v-if="authStore.user?.avatar" :src="authStore.user.avatar">
             <span v-else class="text-weight-bold text-h6">{{ authStore.user?.name?.charAt(0) || 'T' }}</span>
           </q-avatar>
           <div class="q-ml-md">
             <div class="text-weight-bold text-subtitle1" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">{{ authStore.user?.name || 'Teacher' }}</div>
             <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ authStore.user?.username }}</div>
           </div>
        </div>
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
        
        <q-item clickable v-ripple to="/teacher/students" :active-class="$q.dark.isActive ? 'text-teal-2 bg-grey-9 border-l-teal' : 'text-primary bg-blue-1'">
          <q-item-section avatar><q-icon name="people" /></q-item-section>
          <q-item-section>Students</q-item-section>
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

        <q-item clickable v-ripple class="text-red" @click="authStore.logout()">
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
import { ref } from 'vue'
import { useAuthStore } from 'stores/auth-store'

const leftDrawerOpen = ref(false)
const authStore = useAuthStore()

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
