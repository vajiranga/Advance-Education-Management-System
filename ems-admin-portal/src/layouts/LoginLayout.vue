<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container class="login-container">
      <!-- 3D Background Container -->
      <div ref="vantaRef" class="vanta-bg"></div>

      <div class="row justify-center items-center window-height relative-position z-top">
        <div class="col-11 col-sm-8 col-md-6 col-lg-4">
          <q-card class="glass-panel q-pa-lg">
            <q-card-section class="text-center">
              <div class="text-h4 text-primary text-weight-bold q-mb-xs">EMS Admin</div>
              <div class="text-caption text-grey-8">Secure Access Portal</div>
            </q-card-section>

            <q-card-section>
              <q-form @submit="onSubmit" class="q-gutter-md">
                <q-input
                  filled
                  v-model="email"
                  label="Email Address"
                  type="email"
                  lazy-rules
                  :rules="[ val => val && val.length > 0 || 'Please type your email']"
                >
                  <template v-slot:prepend>
                    <q-icon name="email" color="primary" />
                  </template>
                </q-input>

                <q-input
                  filled
                  v-model="password"
                  label="Password"
                  type="password"
                  lazy-rules
                  :rules="[ val => val && val.length > 0 || 'Please type your password']"
                >
                  <template v-slot:prepend>
                    <q-icon name="lock" color="primary" />
                  </template>
                </q-input>

                <div class="row justify-between">
                  <q-checkbox v-model="rememberMe" label="Remember me" color="primary" />
                  <q-btn flat label="Forgot Password?" color="secondary" size="sm" />
                </div>

                <div>
                  <q-btn label="Login to Dashboard" type="submit" color="primary" class="full-width q-py-sm text-weight-bold shadow-3" unelevated />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import * as THREE from 'three'
import NET from 'vanta/dist/vanta.net.min'

import { useAuthStore } from 'stores/auth-store'
import { useQuasar } from 'quasar'

const vantaRef = ref(null)
const vantaEffect = ref(null)

const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const router = useRouter()
const authStore = useAuthStore()
const $q = useQuasar()

onMounted(() => {
  window.THREE = THREE
  try {
      vantaEffect.value = NET({
        el: vantaRef.value,
        THREE: THREE,
        mouseControls: true,
        touchControls: true,
        gyroControls: false,
        minHeight: 200.00,
        minWidth: 200.00,
        scale: 1.00,
        scaleMobile: 1.00,
        color: 0x2563eb,
        backgroundColor: 0xf8fafc,
        points: 12.00,
        maxDistance: 22.00,
        spacing: 16.00
      })
  } catch(e) { console.error('Vanta Error:', e) }
})

onBeforeUnmount(() => {
  if (vantaEffect.value) {
    vantaEffect.value.destroy()
  }
})

const onSubmit = async () => {
  try {
      await authStore.login(email.value, password.value)
      $q.notify({ type: 'positive', message: 'Welcome Back, Admin!' })
      router.push('/finance') // Direct to Finance for testing
  } catch (e) {
      console.error(e)
      $q.notify({ type: 'negative', message: e.message || 'Login Failed. Check credentials.' })
  }
}
</script>

<style lang="scss" scoped>
.login-container {
  overflow: hidden;
}

.vanta-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.glass-panel {
  border-radius: 16px;
  /* Use the global glass-panel class for transparency */
}
</style>
