<template>
  <q-layout view="lHh Lpr lFf" class="bg-grey-1">
    <q-header class="bg-deep-purple text-white">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title class="text-weight-bold row items-center">
          <q-icon name="family_restroom" size="md" class="q-mr-sm" />
          <span>EMS</span> <span class="q-ml-sm text-subtitle2 opacity-80">Parent Portal</span>
        </q-toolbar-title>

        <q-space />

        <div class="row q-gutter-sm items-center">
          <q-btn round flat icon="notifications">
            <q-badge floating color="orange" rounded dot />
          </q-btn>
          <div class="column items-end q-mr-sm display-xs-none">
             <div class="text-subtitle2" style="line-height: 1.2">{{ authStore.user?.name || 'Loading...' }}</div>
             <div class="text-caption opacity-80" style="font-size: 10px;">Parent</div>
          </div>
          <q-avatar size="36px" class="cursor-pointer bg-white text-deep-purple">
            <span class="text-weight-bold">{{ authStore.user?.name?.charAt(0) || 'P' }}</span>
            <q-menu>
              <q-list style="min-width: 220px">
                <q-item class="bg-deep-purple text-white">
                  <q-item-section>
                    <div class="text-subtitle2">{{ authStore.user?.name || 'Guest' }}</div>
                    <div class="text-caption">{{ authStore.user?.email || authStore.user?.phone }}</div>
                  </q-item-section>
                </q-item>

                <q-separator />
                
                <q-item clickable v-close-popup to="/parent/profile">
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
                  active-class="bg-deep-purple-1 text-deep-purple"
                >
                   <q-item-section avatar>
                     <q-avatar size="24px" color="deep-purple" text-color="white" font-size="12px">
                        {{ acc.user?.name?.charAt(0) || 'U' }}
                     </q-avatar>
                   </q-item-section>
                   <q-item-section>
                     <q-item-label>{{ acc.user?.name }}</q-item-label>
                   </q-item-section>
                   <q-item-section side v-if="idx === authStore.activeAccountIndex">
                     <q-icon name="check" color="deep-purple" size="xs" />
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
      <div class="q-pa-md bg-deep-purple-1">
        <div class="text-subtitle1 text-weight-bold text-deep-purple">My Children</div>
        <div class="row items-center q-mt-sm bg-white q-pa-sm rounded-borders shadow-1 cursor-pointer">
           <q-avatar size="32px">
             <img src="https://cdn.quasar.dev/img/boy-avatar.png">
           </q-avatar>
           <div class="q-ml-sm">
             <div class="text-weight-bold text-caption">Kasun Perera</div>
             <div class="text-caption text-grey" style="font-size: 10px;">Grade 10</div>
           </div>
           <q-space />
           <q-icon name="expand_more" color="grey" />
        </div>
      </div>
      
      <q-separator />
      
      <q-list class="q-mt-md">
        <q-item clickable v-ripple to="/parent/dashboard" active-class="text-deep-purple bg-deep-purple-1">
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Overview</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple to="/parent/results" active-class="text-deep-purple bg-deep-purple-1">
          <q-item-section avatar><q-icon name="school" /></q-item-section>
          <q-item-section>Academic Progress</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple to="/parent/payments" active-class="text-deep-purple bg-deep-purple-1">
          <q-item-section avatar><q-icon name="payments" /></q-item-section>
          <q-item-section>Fees & Payments</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple to="/parent/messages" active-class="text-deep-purple bg-deep-purple-1">
          <q-item-section avatar><q-icon name="chat" /></q-item-section>
          <q-item-section>Messages</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple to="/parent/profile" active-class="text-deep-purple bg-deep-purple-1">
          <q-item-section avatar><q-icon name="person" /></q-item-section>
          <q-item-section>Profile</q-item-section>
        </q-item>

        <q-separator class="q-my-md" />

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
import { ref, onMounted } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useRoute } from 'vue-router'

const leftDrawerOpen = ref(false)
const authStore = useAuthStore()
const route = useRoute()

onMounted(async () => {
    authStore.init()
    if (route.query.token) {
        await authStore.addAccountFromToken(route.query.token)
        const url = new URL(window.location.href)
        url.searchParams.delete('token')
        window.history.replaceState({}, document.title, url.toString())
    } else if (!authStore.user) {
        await authStore.fetchUser()
    }
})

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
