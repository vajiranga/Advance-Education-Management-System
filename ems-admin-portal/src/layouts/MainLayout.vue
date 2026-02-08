<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-white text-primary">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <!-- Institute Logo -->
          <img
            v-if="settings.logoUrl"
            :src="settings.logoUrl"
            alt="Institute Logo"
            style="height: 40px; width: auto; object-fit: contain; margin-right: 12px;"
          />
          <q-icon v-else name="school" class="q-mr-sm" size="md" />
          {{ settings.instituteName }}
        </q-toolbar-title>

        <div class="row q-gutter-sm items-center">
          <q-btn flat round icon="account_circle" size="md">
            <q-menu>
              <q-list style="min-width: 150px">
                <q-item clickable v-close-popup @click="handleLogout">
                  <q-item-section avatar>
                    <q-icon name="logout" color="negative" />
                  </q-item-section>
                  <q-item-section>Logout</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered class="bg-grey-1">
      <!-- Institute Logo Section -->
      <div class="q-pa-md text-center bg-white">
        <img
          v-if="settings.logoUrl"
          :src="settings.logoUrl"
          alt="Institute Logo"
          style="max-width: 120px; max-height: 80px; object-fit: contain;"
        />
        <q-icon v-else name="business" size="60px" color="primary" />
      </div>

      <q-separator />

      <!-- User Info Section -->
      <div class="q-pa-md text-center">
        <div class="text-h6 text-primary">{{ userDisplayName }}</div>
        <div class="text-caption text-grey">{{ userPermissionStatus }}</div>
      </div>

      <q-separator />

      <q-list class="q-mt-sm">
        <q-item
          v-for="link in filteredLinks"
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
import { ref, computed } from 'vue'
import { useSettingsStore } from 'stores/settings-store'
import { useAuthStore } from 'stores/auth-store'
import { useRouter } from 'vue-router'

defineOptions({
  name: 'MainLayout',
})

const settings = useSettingsStore()
const authStore = useAuthStore()
const router = useRouter()

const navigationLinks = [
  { title: 'Dashboard', icon: 'dashboard', path: '/dashboard', permission: 'dashboard' },
  { title: 'Hall Management', icon: 'meeting_room', path: '/institutes', permission: 'halls' },
  { title: 'Users', icon: 'people', path: '/users', permission: 'users' },
  { title: 'Classes', icon: 'library_books', path: '/courses', permission: 'classes' },
  { title: 'Attendance', icon: 'fact_check', path: '/attendance', permission: 'attendance' },
  { title: 'Cash Payment', icon: 'point_of_sale', path: '/cashpayment', permission: 'payments' },
  { title: 'Finance', icon: 'payments', path: '/finance', permission: 'finance' },
  { title: 'Settings', icon: 'settings', path: '/settings', permission: 'settings' },
]

const filteredLinks = computed(() => {
    const user = authStore.user
    if (!user) return []
    // Super Admin sees everything
    if (user.is_super_admin) return navigationLinks

    // Sub-admins see only what they have permission for
    const perms = user.permissions || []
    return navigationLinks.filter(link => {
        // Settings: show if user has 'settings' or 'settings_edit'
        if (link.permission === 'settings') {
             return perms.includes('settings') || perms.includes('settings_edit')
        }
        // Finance: show if user has 'finance' OR any finance sub-permission
        if (link.permission === 'finance') {
             return perms.includes('finance') ||
                    perms.includes('finance_pending') ||
                    perms.includes('finance_transactions') ||
                    perms.includes('finance_uncollected') ||
                    perms.includes('finance_settlement')
        }
        return perms.includes(link.permission)
    })
})

const userDisplayName = computed(() => {
    const user = authStore.user
    if (!user) return 'Admin'
    // Super Admin shows "Super Admin"
    if (user.is_super_admin) return 'Super Admin'
    // Regular admin shows their name
    return user.name || 'Admin'
})

const userPermissionStatus = computed(() => {
    const user = authStore.user
    if (!user) return ''
    // Super Admin shows "Full Access"
    if (user.is_super_admin) return 'Full Access'
    // Regular admin shows permission count
    const permCount = (user.permissions || []).length
    return `${permCount} Permission${permCount !== 1 ? 's' : ''} Active`
})

const handleLogout = () => {
    authStore.logout()
    router.replace('/login')
}

const leftDrawerOpen = ref(false)


function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>
