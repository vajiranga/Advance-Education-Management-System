<template>
  <q-layout view="lHh Lpr lff">
    <!-- Navbar -->
    <q-header class="bg-transparent text-white q-py-md transition-all" :class="{'glass-header text-dark': isScrolled}">
      <q-toolbar class="container">
        <!-- Logo -->
        <q-toolbar-title class="text-weight-bold flex items-center cursor-pointer" @click="$router.push('/')">
           <q-icon name="school" size="md" :color="isScrolled ? 'primary' : 'white'" class="q-mr-sm" />
           <span :class="isScrolled ? 'text-primary' : 'text-white'">EMS</span>
           <span class="text-weight-light q-ml-xs" :class="isScrolled ? 'text-grey-8' : 'text-grey-3'">Platform</span>
        </q-toolbar-title>

        <!-- Desktop Menu -->
        <div class="gt-sm row q-gutter-lg">
           <q-btn flat no-caps label="Features" :class="isScrolled ? 'text-grey-8' : 'text-grey-3'" />
           <q-btn flat no-caps label="Solutions" :class="isScrolled ? 'text-grey-8' : 'text-grey-3'" />
           <q-btn flat no-caps label="Pricing" :class="isScrolled ? 'text-grey-8' : 'text-grey-3'" />
           <q-btn flat no-caps label="Resources" :class="isScrolled ? 'text-grey-8' : 'text-grey-3'" />
        </div>

        <q-space />

        <!-- Auth Buttons -->
        <div class="row q-gutter-md items-center">
           <q-btn 
             flat 
             no-caps 
             label="Login" 
             :color="isScrolled ? 'primary' : 'white'"
             to="/login"
           />
           <q-btn 
             unelevated 
             no-caps 
             label="Register" 
             color="secondary" 
             class="btn-rounded shadow-2"
             to="/register"
           />
        </div>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <router-view />
    </q-page-container>

    <!-- Footer -->
    <q-footer class="bg-dark text-grey-4 q-py-xl border-top-light">
       <div class="container">
          <div class="row q-col-gutter-xl">
             <div class="col-12 col-md-4">
                <div class="text-h5 text-white text-weight-bold q-mb-md flex items-center">
                   <q-icon name="school" color="primary" class="q-mr-sm" /> EMS Platform
                </div>
                <p class="text-grey-5 leading-relaxed">
                   The world's tailored learning management system designed to help institutes grow and students succeed.
                </p>
                <div class="row q-gutter-sm q-mt-md">
                   <q-btn flat round icon="facebook" color="grey-5" />
                   <q-btn flat round icon="smart_display" color="grey-5" /> <!-- using smart_display instead of twitter for generic -->
                   <q-btn flat round icon="public" color="grey-5" />
                </div>
             </div>
             
             <div class="col-6 col-md-2">
                <div class="text-subtitle1 text-white text-weight-bold q-mb-md">Product</div>
                <div class="column q-gutter-sm">
                   <a href="#" class="footer-link">Features</a>
                   <a href="#" class="footer-link">Pricing</a>
                   <a href="#" class="footer-link">Demo</a>
                   <a href="#" class="footer-link">Updates</a>
                </div>
             </div>

             <div class="col-6 col-md-2">
                <div class="text-subtitle1 text-white text-weight-bold q-mb-md">Company</div>
                <div class="column q-gutter-sm">
                   <a href="#" class="footer-link">About Us</a>
                   <a href="#" class="footer-link">Careers</a>
                   <a href="#" class="footer-link">Blog</a>
                   <a href="#" class="footer-link">Contact</a>
                </div>
             </div>

             <div class="col-12 col-md-4">
                <div class="text-subtitle1 text-white text-weight-bold q-mb-md">Subscribe</div>
                <p class="text-caption text-grey-6 q-mb-md">Get the latest news and updates right to your inbox.</p>
                <q-input 
                  dark 
                  outlined 
                  v-model="email" 
                  placeholder="Enter your email" 
                  dense
                  class="email-input"
                >
                   <template v-slot:append>
                      <q-btn round flat icon="send" color="secondary" />
                   </template>
                </q-input>
             </div>
          </div>

          <q-separator dark class="q-my-xl opacity-20" />

          <div class="row justify-between items-center copyright-row">
             <div>© 2026 EMS Platform. All rights reserved.</div>
             <div class="row q-gutter-lg">
                <a href="#" class="footer-link text-caption">Privacy Policy</a>
                <a href="#" class="footer-link text-caption">Terms of Service</a>
                <a href="#" class="footer-link text-caption">Cookie Settings</a>
             </div>
          </div>
       </div>
    </q-footer>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const isScrolled = ref(false)
const email = ref('')

const handleScroll = () => {
   isScrolled.value = window.scrollY > 50
}

onMounted(() => {
   window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
   window.removeEventListener('scroll', handleScroll)
})
</script>

<style lang="scss" scoped>
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 24px;
}

.glass-header {
  background: rgba(255, 255, 255, 0.95) !important;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
  padding-top: 10px !important;
  padding-bottom: 10px !important;
}

.btn-rounded {
  border-radius: 50px;
  padding: 8px 24px;
  font-weight: 600;
}

.footer-link {
  color: #94a3b8;
  text-decoration: none;
  transition: color 0.3s;
  &:hover {
     color: white;
  }
}

.border-top-light {
  border-top: 1px solid rgba(255,255,255,0.05);
}

.copyright-row {
  opacity: 0.6;
  font-size: 0.9em;
}
</style>
