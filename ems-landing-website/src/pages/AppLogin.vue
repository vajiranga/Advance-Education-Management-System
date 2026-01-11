<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="auth-card no-shadow bg-white q-pb-lg">
      <q-card-section class="text-center q-pt-xl q-pb-md">
        <div class="text-h4 text-weight-bold text-primary q-mb-xs">Welcome Back</div>
        <div class="text-grey-7">Login to your dashboard</div>
      </q-card-section>

      <q-tabs
        v-model="role"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="student" label="Student" />
        <q-tab name="teacher" label="Teacher" />
        <q-tab name="parent" label="Parent" />
      </q-tabs>
      
      <q-card-section class="q-px-lg q-mt-md">
        <q-form @submit="onSubmit" class="q-gutter-md">
          <q-input outlined v-model="email" label="Email or Index Number" :rules="[val => !!val || 'This field is required']">
            <template v-slot:prepend>
              <q-icon name="account_circle" />
            </template>
          </q-input>

          <q-input outlined v-model="password" label="Password" type="password" :rules="[val => !!val || 'Password is required']">
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
          </q-input>
          
          <q-btn unelevated color="primary" size="lg" class="full-width q-mt-sm" label="Login" type="submit" :loading="loading" />
        </q-form>
      </q-card-section>

      <div class="text-center q-mt-sm">
        <router-link to="/register" class="text-primary" style="text-decoration: none">Create an account</router-link>
      </div>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const email = ref('')
const password = ref('')
const role = ref('student') // Default role, but backend will override if user has one
const loading = ref(false)

const onSubmit = async () => {
  loading.value = true
  try {
    const response = await api.post('/login', {
        email: email.value,
        password: password.value,
        role: role.value // Optional hint for redirection if user role is ambiguous
    })

    $q.notify({
        color: 'positive',
        message: 'Login Successful!'
    })

    if (response.data.redirect_url) {
        const targetUrl = new URL(response.data.redirect_url)
        if (response.data.token) {
            targetUrl.searchParams.append('token', response.data.token)
        }
        window.location.href = targetUrl.toString()
    }

  } catch (error) {
    console.error('Login Error:', error)
    let msg = 'Login failed'
    if (error.code === 'ERR_NETWORK') {
        msg = 'Cannot connect to server. Is the backend running?'
    } else if (error.response) {
        msg = error.response.data.message || 'Invalid credentials'
    }
    
    $q.notify({
        color: 'negative',
        message: msg,
        position: 'top'
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-card { width: 100%; max-width: 450px; border-radius: 16px; }
</style>
