<template>
  <q-layout view="lHh Lpr lFf" :class="$q.dark.isActive ? 'bg-dark-page' : 'bg-grey-1'">
    <q-header
      elevated
      :class="$q.dark.isActive ? 'bg-dark text-white border-bottom-dark' : 'bg-white text-primary'"
    >
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <!-- Institute Logo -->
          <img
            v-if="settingsStore.logoUrl"
            :src="settingsStore.logoUrl"
            alt="Institute Logo"
            style="height: 36px; width: auto; object-fit: contain; margin-right: 12px;"
          />
          <q-icon v-else name="family_restroom" size="md" class="q-mr-sm" />
          <span>{{ settingsStore.instituteName }}</span>
          <span
            class="q-ml-sm text-subtitle2"
            :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'"
            >Parent Portal</span
          >
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
            <q-badge floating color="red" rounded dot v-if="unreadCount > 0" />
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

          <div
            class="column items-end q-mr-sm display-xs-none"
            :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'"
          >
            <div class="text-subtitle2" style="line-height: 1.2">
              {{ authStore.user?.name || 'Loading...' }}
            </div>
            <div class="text-caption opacity-80" style="font-size: 10px">Parent</div>
          </div>
          <q-avatar size="36px" class="cursor-pointer bg-primary text-white shadow-1">
            <span class="text-weight-bold">{{ authStore.user?.name?.charAt(0) || 'P' }}</span>
            <q-menu :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white'">
              <q-list style="min-width: 220px">
                <q-item class="bg-primary text-white">
                  <q-item-section>
                    <div class="text-subtitle2">{{ authStore.user?.name || 'Parent' }}</div>
                    <div class="text-caption">
                      {{ authStore.user?.email || authStore.user?.phone }}
                    </div>
                  </q-item-section>
                </q-item>

                <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

                <q-item clickable v-close-popup to="/parent/profile">
                  <q-item-section avatar><q-icon name="person" /></q-item-section>
                  <q-item-section>My Profile</q-item-section>
                </q-item>

                <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                <q-item-label header :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'"
                  >Switch Account</q-item-label
                >

                <q-item
                  v-for="(acc, idx) in authStore.accounts"
                  :key="idx"
                  clickable
                  v-close-popup
                  @click="authStore.switchAccount(idx)"
                  :active="idx === authStore.activeAccountIndex"
                  :active-class="
                    $q.dark.isActive ? 'bg-grey-8 text-primary' : 'bg-blue-1 text-primary'
                  "
                >
                  <q-item-section avatar>
                    <q-avatar size="24px" color="primary" text-color="white" font-size="12px">
                      {{ acc.user?.name?.charAt(0) || 'U' }}
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ acc.user?.name }}</q-item-label>
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
      <!-- Institute Logo Section -->
      <div class="q-pa-md text-center" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-white'">
        <img
          v-if="settingsStore.logoUrl"
          :src="settingsStore.logoUrl"
          alt="Institute Logo"
          style="max-width: 100px; max-height: 70px; object-fit: contain;"
        />
        <q-icon v-else name="family_restroom" size="60px" :color="$q.dark.isActive ? 'white' : 'primary'" />
      </div>
      <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

      <!-- Institute Info -->
      <div class="q-pa-lg">
        <div class="row items-center justify-center">
          <div
            class="text-h6 text-weight-bold text-center"
            :class="$q.dark.isActive ? 'text-white' : 'text-primary'"
          >
            {{ settingsStore.instituteName }}
          </div>
        </div>
        <div
          class="text-caption text-center"
          :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'"
        >
          Parent Dashboard
        </div>
      </div>
      <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

      <div class="q-pa-md" :class="$q.dark.isActive ? 'bg-transparent' : 'bg-blue-1'">
        <div
          class="text-subtitle1 text-weight-bold"
          :class="$q.dark.isActive ? 'text-white' : 'text-primary'"
        >
          My Children
        </div>

        <div
          class="row items-center q-mt-sm q-pa-sm rounded-borders shadow-1 cursor-pointer"
          :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-white'"
        >
          <q-avatar size="32px" color="primary" text-color="white">
            <span class="text-weight-bold">{{ currentChild?.name?.charAt(0) || 'C' }}</span>
          </q-avatar>
          <div class="q-ml-sm">
            <div
              class="text-weight-bold text-caption"
              :class="$q.dark.isActive ? 'text-white' : ''"
            >
              {{ currentChild?.name || 'Select Child' }}
            </div>
            <div
              class="text-caption"
              :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'"
              style="font-size: 10px"
            >
              {{ currentChild?.grade || 'N/A' }}
            </div>
          </div>
          <q-space />
          <q-icon name="expand_more" color="grey" />

          <q-menu fit v-model="childMenuOpen">
            <q-list style="min-width: 150px">
              <q-item
                clickable
                v-close-popup
                v-for="child in children"
                :key="child.id"
                @click="selectChild(child)"
              >
                <q-item-section avatar>
                  <q-avatar size="24px" color="primary" text-color="white">
                    <span class="text-weight-bold" style="font-size: 10px">{{
                      child.name?.charAt(0) || 'C'
                    }}</span>
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ child.name }}</q-item-label>
                  <q-item-label caption>{{ child.grade }}</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </div>
      </div>

      <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

      <q-list class="q-mt-md">
        <q-item
          clickable
          v-ripple
          to="/parent/dashboard"
          :active-class="
            $q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'
          "
        >
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Overview</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          to="/parent/results"
          :active-class="
            $q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'
          "
        >
          <q-item-section avatar><q-icon name="school" /></q-item-section>
          <q-item-section>Academic Progress</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          to="/parent/attendance"
          :active-class="
            $q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'
          "
        >
          <q-item-section avatar><q-icon name="event_available" /></q-item-section>
          <q-item-section>Attendance</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          to="/parent/payments"
          :active-class="
            $q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'
          "
        >
          <q-item-section avatar><q-icon name="payments" /></q-item-section>
          <q-item-section>Payments</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          to="/parent/profile"
          :active-class="
            $q.dark.isActive ? 'text-indigo-2 bg-grey-9 border-l-primary' : 'text-primary bg-blue-1'
          "
        >
          <q-item-section avatar><q-icon name="person" /></q-item-section>
          <q-item-section>Profile</q-item-section>
        </q-item>

      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useNotificationStore } from 'stores/notification-store'
import { useSettingsStore } from 'stores/settings-store'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { storeToRefs } from 'pinia'

const leftDrawerOpen = ref(false)
const $q = useQuasar()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const settingsStore = useSettingsStore()
const { notifications, unreadCount } = storeToRefs(notificationStore)
const route = useRoute()
const router = useRouter()
const children = ref([])
const childMenuOpen = ref(false)

const currentChild = computed(() => authStore.selectedChild)

onMounted(async () => {
  authStore.init()
  if (route.query.token) {
    await authStore.addAccountFromToken(route.query.token)
    const url = new URL(window.location.href)
    url.searchParams.delete('token')
    window.history.replaceState({}, document.title, url.toString())
  }

  // Fetch user if needed
  if (!authStore.user) {
    await authStore.fetchUser()
  }

  // Fetch Children
  try {
    const { api } = await import('boot/axios')
    const res = await api.get('/v1/parent/children')
    children.value = res.data

    // Auto select first child if none selected
    if (children.value.length > 0 && !authStore.selectedChild) {
      authStore.selectedChild = children.value[0]
    }
  } catch (e) {
    console.error('Failed to load children', e)
  }

  // Fetch Notifications
  await notificationStore.fetchNotifications()
  setInterval(() => notificationStore.fetchNotifications(), 60000)
})

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

function selectChild(child) {
  authStore.selectedChild = child
  childMenuOpen.value = false
}

// Notification Helpers
function getIcon(type) {
    switch(type) {
        case 'payment_success': return 'check_circle'
        case 'payment_pending': return 'hourglass_empty'
        case 'fee_due': return 'payment'
        case 'exam_scheduled': return 'event'
        case 'exam_results': return 'assignment'
        case 'notice': return 'campaign'
        case 'meeting': return 'groups'
        default: return 'notifications'
    }
}

function getIconColor(type) {
    switch(type) {
        case 'payment_success': return 'positive'
        case 'payment_pending': return 'warning'
        case 'fee_due': return 'negative'
        case 'exam_scheduled': return 'info'
        case 'exam_results': return 'primary'
        case 'notice': return 'orange'
        case 'meeting': return 'teal'
        default: return 'primary'
    }
}

function handleNotificationClick(note) {
    if (!note.read_at) {
        notificationStore.markAsRead(note.id)
    }

    if (note.type === 'payment_success' || note.type === 'fee_due' || note.type === 'payment_pending') {
        router.push('/parent/payments')
    } else if (note.type === 'exam_scheduled' || note.type === 'exam_results') {
        router.push('/parent/results')
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
