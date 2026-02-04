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

                  <q-item v-for="note in notifications" :key="note.id" clickable v-close-popup class="q-py-md" @click="handleNotificationClick(note)">
                     <q-item-section avatar>
                        <q-avatar :color="getIconColor(note.type)" text-color="white" :icon="getIcon(note.type)" font-size="20px" size="md" />
                     </q-item-section>
                     <q-item-section>
                        <q-item-label :class="{'text-weight-bold': !note.read_at}">{{ note.title }}</q-item-label>
                        <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ note.message }}</q-item-label>
                        <div class="text-caption text-grey-6 q-mt-xs">{{ note.time }}</div>
                     </q-item-section>
                     <q-item-section side v-if="!note.read_at">
                         <q-badge color="blue" rounded dot />
                     </q-item-section>
                  </q-item>

                  <div v-if="notifications.length === 0" class="text-center q-pa-lg text-grey">
                      <q-icon name="notifications_off" size="xl" class="q-mb-sm" />
                      <div>No new notifications</div>
                  </div>

                  <q-separator v-if="notifications.length > 0" />
                  <q-item v-if="notifications.length > 0" clickable class="text-center justify-center text-primary" @click="notificationStore.markAllRead()">
                      <q-item-label class="text-caption">Mark all as read</q-item-label>
                  </q-item>
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

      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue' // Added onMounted
import { useAuthStore } from 'stores/auth-store'
import { useNotificationStore } from 'stores/notification-store' // Added
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { storeToRefs } from 'pinia'

const leftDrawerOpen = ref(false)
const authStore = useAuthStore()
const notificationStore = useNotificationStore() // Added
const { notifications } = storeToRefs(notificationStore) // Added (removed unreadCount to avoid unused var warning if applicable)
const router = useRouter()
const $q = useQuasar()

// Visual fix for ID display (STU -> TCH)
const displayTeacherId = computed(() => {
    let id = authStore.user?.username || ''
    if (id && id.startsWith('STU')) {
        return id.replace('STU', 'TCH')
    }
    return id
})

onMounted(async () => {
    // Fetch Notifications
    await notificationStore.fetchNotifications()
    setInterval(() => notificationStore.fetchNotifications(), 60000)

    // Auth check if needed
    if (!authStore.user) {
         await authStore.fetchUser()
    }
})

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

// Notification Helpers
function getIcon(type) {
    switch(type) {
        case 'new_student': return 'person_add'
        case 'class_status_update': return 'verified'
        case 'notice': return 'campaign'
        case 'meeting': return 'groups'
        default: return 'notifications'
    }
}

function getIconColor(type) {
    switch(type) {
        case 'new_student': return 'info'
        case 'class_status_update': return 'positive'
        case 'notice': return 'orange'
        case 'meeting': return 'teal'
        default: return 'primary'
    }
}

function handleNotificationClick(note) {
    if (!note.read_at) {
        notificationStore.markAsRead(note.id)
    }

    if (note.type === 'new_student' || note.type === 'class_status_update') {
        router.push('/teacher/classes')
    }
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
