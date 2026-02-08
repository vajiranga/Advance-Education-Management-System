<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-white text-primary">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold">
          <q-icon name="school" class="q-mr-sm" size="md" />
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
      <div class="q-pa-md text-center">
        <div class="text-h6 text-primary">Super Admin</div>
        <div class="text-caption text-grey">System Administrator</div>
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
    // Note: 'settings' permission grants view access. 'settings_edit' grants full access.
    // If user has 'settings_edit', they implicitly can view Settings.
    const perms = user.permissions || []
    return navigationLinks.filter(link => {
        if (link.permission === 'settings') {
             return perms.includes('settings') || perms.includes('settings_edit')
        }
        return perms.includes(link.permission)
    })
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
