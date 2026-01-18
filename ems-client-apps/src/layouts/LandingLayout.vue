<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-white text-primary">
      <q-toolbar class="container">
        <q-btn flat no-caps no-wrap class="q-ml-xs" to="/">
          <q-avatar size="32px">
            <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-cyan.svg" />
          </q-avatar>
          <q-toolbar-title shrink class="text-weight-bold">EMS Platform</q-toolbar-title>
        </q-btn>

        <q-space />

        <div class="q-gutter-sm row items-center no-wrap">
          <q-btn flat round dense icon="menu" class="lt-md" @click="toggleLeftDrawer" />
          <div class="gt-sm row items-center q-gutter-md">
             <q-btn flat no-caps label="Features" />
             <q-btn flat no-caps label="Solutions" />
             <q-btn flat no-caps label="Pricing" />
             <q-btn flat no-caps label="Resources" />
             <q-separator vertical spaced />
             <q-btn flat no-caps label="Login" to="/login" />
             <q-btn unelevated color="secondary" text-color="white" no-caps label="Register" to="/register" />
          </div>
        </div>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>

    <q-footer class="bg-dark text-white q-py-lg">
       <div class="container">
          <div class="row q-col-gutter-xl">
             <div class="col-12 col-md-4">
                <div class="row items-center q-mb-md">
                   <q-icon name="school" size="md" class="q-mr-sm text-primary" />
                   <div class="text-h6 text-weight-bold">EMS Platform</div>
                </div>
                <p class="text-grey-5">The world's tailored learning management system designed to help institutes grow and students succeed.</p>
                <div class="row q-gutter-sm">
                   <q-btn flat round size="sm" icon="facebook" class="bg-white text-dark" />
                   <q-btn flat round size="sm" icon="smart_display" class="bg-white text-dark" />
                   <q-btn flat round size="sm" icon="public" class="bg-white text-dark" />
                </div>
             </div>
             
             <div class="col-6 col-md-2">
                <div class="text-subtitle1 text-weight-bold q-mb-md">Product</div>
                <div class="column q-gutter-sm text-grey-5">
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Features</a>
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Pricing</a>
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Demo</a>
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Updates</a>
                </div>
             </div>
             
             <div class="col-6 col-md-2">
                <div class="text-subtitle1 text-weight-bold q-mb-md">Company</div>
                <div class="column q-gutter-sm text-grey-5">
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">About Us</a>
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Careers</a>
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Blog</a>
                   <a href="#" class="no-decoration text-grey-5 hover-text-white">Contact</a>
                </div>
             </div>

             <div class="col-12 col-md-4">
                <div class="text-subtitle1 text-weight-bold q-mb-md">Subscribe</div>
                <div class="text-grey-5 q-mb-sm">Get the latest news and updates right to your inbox.</div>
                <q-input dark outlined dense placeholder="Enter your email" bg-color="dark">
                   <template v-slot:append>
                      <q-btn flat round color="primary" icon="send" />
                   </template>
                </q-input>
             </div>
          </div>
          
          <q-separator class="q-my-lg bg-grey-8" />
          
          <div class="row justify-between items-center text-grey-6 text-caption">
             <div>&copy; 2026 EMS Platform. All rights reserved.</div>
             <div class="row q-gutter-md">
                <a href="#" class="no-decoration text-grey-6">Privacy Policy</a>
                <a href="#" class="no-decoration text-grey-6">Terms of Service</a>
                <a href="#" class="no-decoration text-grey-6">Cookie Settings</a>
             </div>
          </div>
       </div>
    </q-footer>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'

const leftDrawerOpen = ref(false)

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

<style scoped>
.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
.no-decoration { text-decoration: none; }
.hover-text-white:hover { color: white !important; }
</style>
