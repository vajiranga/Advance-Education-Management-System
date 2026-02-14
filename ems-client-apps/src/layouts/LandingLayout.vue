<template>
  <q-layout view="lHh Lpr lff" class="bg-dark text-white">
    <!-- Transparent/Dark Header -->
    <q-header class="bg-dark text-white q-py-sm" style="border-bottom: 1px solid rgba(255,255,255,0.05)">
      <q-toolbar class="container">
        <q-btn flat no-caps no-wrap class="q-ml-xs" to="/">
          <q-avatar size="40px" class="q-mr-sm">
             <q-icon name="public" size="40px" color="primary" />
          </q-avatar>
          <div class="column items-start">
             <div class="text-h6 text-weight-bolder leading-none text-uppercase tracking-wide" style="font-size: 1.25rem;">{{ settingsStore.instituteName }}</div>
             <div class="text-caption text-primary text-weight-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Home</div>
          </div>
        </q-btn>

        <q-space />

        <!-- Desktop Menu -->
        <div class="gt-sm row items-center q-gutter-md">
           <q-btn flat no-caps label="Home" to="/" />
           <q-btn flat no-caps label="Classes" href="#classes" />
           <q-btn flat no-caps label="Gallery" href="#gallery" />
           <q-btn flat no-caps label="Contact" href="#footer" />
           <div class="q-mx-sm" style="width: 1px; height: 20px; background: rgba(255,255,255,0.2)"></div>
           <q-btn flat no-caps label="Login" to="/login" icon="login" />
           <q-btn unelevated rounded color="primary" text-color="white" no-caps label="Register" to="/register" class="q-px-lg" />
        </div>

        <!-- Mobile Menu -->
        <q-btn flat round dense icon="menu" class="lt-md" @click="toggleLeftDrawer" />
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" side="right" bordered class="bg-dark text-white">
       <q-list class="q-mt-lg">
          <q-item to="/" clickable v-ripple><q-item-section>Home</q-item-section></q-item>
          <q-item href="#classes" clickable v-ripple><q-item-section>Classes</q-item-section></q-item>
          <q-item href="#gallery" clickable v-ripple><q-item-section>Gallery</q-item-section></q-item>
          <q-item href="#footer" clickable v-ripple><q-item-section>Contact</q-item-section></q-item>
          <q-separator dark class="q-my-md" />
          <q-item to="/login" clickable v-ripple><q-item-section>Login</q-item-section></q-item>
          <q-item to="/register" clickable v-ripple class="text-primary text-weight-bold"><q-item-section>Register</q-item-section></q-item>
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

const leftDrawerOpen = ref(false)
const settingsStore = useSettingsStore()

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

<style scoped>
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.no-decoration { text-decoration: none; transition: color 0.3s; }
.hover-text-primary:hover { color: #1976D2 !important; }
.leading-none { line-height: 1; }
.leading-relaxed { line-height: 1.6; }
</style>
