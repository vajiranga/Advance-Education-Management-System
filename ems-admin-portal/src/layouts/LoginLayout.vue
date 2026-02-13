<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container class="login-container">
      <!-- Lightweight CSS Background -->
      <div class="simple-bg"></div>

      <div class="row justify-center items-center window-height relative-position z-top">
        <div class="col-11 col-sm-8 col-md-6 col-lg-4">
          <q-card class="glass-panel q-pa-lg">
            <q-card-section class="text-center">
              <div class="text-h4 text-primary text-weight-bold q-mb-xs">{{ settings.instituteName }}</div>
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
import { ref } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useSettingsStore } from 'stores/settings-store'
import { useQuasar } from 'quasar'

const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const authStore = useAuthStore()
const settings = useSettingsStore()
const $q = useQuasar()

const onSubmit = async () => {
  try {
      await authStore.login(email.value, password.value)
      $q.notify({ type: 'positive', message: 'Welcome Back, Admin!' })
      // Force full reload to ensure auth state and headers are correctly initialized
      // This solves the issue where the page needs a refresh to load data
      window.location.href = '/attendance'
  } catch (e) {
      console.error(e)
      $q.notify({ type: 'negative', message: e.message || 'Login Failed. Check credentials.' })
  }
}
</script>

<style lang="scss" scoped>
.login-container {
  overflow: hidden;
  position: relative;
}

.simple-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.glass-panel {
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
}
</style>
