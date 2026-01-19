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
             <div class="text-h6 text-weight-bolder leading-none text-uppercase tracking-wide" style="font-size: 1.25rem;">විද්‍යානන්ද</div>
             <div class="text-caption text-primary text-weight-bold" style="font-size: 0.8rem; letter-spacing: 1px;">විද්‍යාපීඨය</div>
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

    <q-footer id="footer" class="bg-black text-white q-py-xl" style="border-top: 1px solid rgba(255,255,255,0.05)">
       <div class="container">
          <div class="row q-col-gutter-xl">
             <div class="col-12 col-md-4">
                <div class="row items-center q-mb-md">
                   <q-icon name="public" size="md" class="q-mr-sm text-primary" />
                   <div class="text-h5 text-weight-bolder">විද්‍යානන්ද විද්‍යාපීඨය</div>
                </div>
                <p class="text-grey-5 leading-relaxed">
                   The First & The Best. <br>
                   1979 සිට පවතින සුපිරිම පන්ති. <br>
                   ගිරිඋල්ල.
                </p>
                <div class="row q-gutter-md q-mt-lg">
                   <q-btn round color="blue-9" icon="facebook" size="sm" />
                   <q-btn round color="red-6" icon="smart_display" size="sm" />
                   <q-btn round color="light-blue" icon="telegram" size="sm" />
                </div>
             </div>
             
             <div class="col-6 col-md-2">
                <div class="text-subtitle1 text-weight-bold q-mb-md text-primary">Links</div>
                <div class="column q-gutter-sm text-grey-5">
                   <a href="#" class="no-decoration text-grey-5 hover-text-primary">Home</a>
                   <a href="#classes" class="no-decoration text-grey-5 hover-text-primary">Classes</a>
                   <a href="#teachers" class="no-decoration text-grey-5 hover-text-primary">Teachers</a>
                   <a href="/login" class="no-decoration text-grey-5 hover-text-primary">Student Login</a>
                </div>
             </div>
             
             <div class="col-6 col-md-2">
                <div class="text-subtitle1 text-weight-bold q-mb-md text-primary">Subjects</div>
                <div class="column q-gutter-sm text-grey-5">
                   <div>Maths</div>
                   <div>Science</div>
                   <div>Sinhala</div>
                   <div>History</div>
                   <div>ICT</div>
                </div>
             </div>

             <div class="col-12 col-md-4">
                <div class="text-subtitle1 text-weight-bold q-mb-md text-primary">Contact Us</div>
                <div class="q-list custom-list">
                    <div class="row items-start q-mb-sm">
                        <q-icon name="place" color="grey-5" class="q-mr-sm q-mt-xs" />
                        <span class="text-grey-5">Negombo Road, Giriulla</span>
                    </div>
                    <div class="row items-center q-mb-sm">
                        <q-icon name="phone" color="grey-5" class="q-mr-sm" />
                        <span class="text-grey-5">077-8282529, 037-2288145</span>
                    </div>
                    <div class="row items-center">
                         <q-icon name="email" color="grey-5" class="q-mr-sm" />
                         <span class="text-grey-5">info@vidyananda.lk</span>
                    </div>
                </div>
             </div>
          </div>
          
          <q-separator dark class="q-my-xl bg-grey-9" />
          
          <div class="text-center text-grey-7 text-caption">
             &copy; 2026 විද්‍යානන්ද විද්‍යාපීඨය. සියලු හිමිකම් ඇවිරිණි. <br>
             අධ්‍යාපනයේ උන්නතිය වෙනුවෙන් නිර්මාණය කරන ලදී. ❤️
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
.no-decoration { text-decoration: none; transition: color 0.3s; }
.hover-text-primary:hover { color: #1976D2 !important; }
.leading-none { line-height: 1; }
.leading-relaxed { line-height: 1.6; }
</style>
