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
          
          <!-- Parent Login Fields -->
          <div v-if="role === 'parent'">
              <div class="text-caption text-grey-7 q-mb-sm">Enter the Student ID and Registered Parent Phone Number</div>
              <q-input outlined v-model="studentId" label="Student ID (e.g. STU2026...)" :rules="[val => !!val || 'Student ID is required']">
                <template v-slot:prepend><q-icon name="badge" /></template>
              </q-input>
              <q-input outlined v-model="parentPhone" label="Parent Phone Number" :rules="[val => !!val || 'Phone Number is required']">
                <template v-slot:prepend><q-icon name="phone" /></template>
              </q-input>
          </div>

          <!-- Standard Login Fields -->
          <div v-else>
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
          </div>
          
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
const role = ref('student') // Default role
const loading = ref(false)

// Parent specific fields
const parentPhone = ref('')
const studentId = ref('')

const onSubmit = async () => {
  loading.value = true
  try {
    let response;
    
    if (role.value === 'parent') {
        response = await api.post('/parent-login', {
            phone: parentPhone.value,
            student_id: studentId.value
        })
    } else {
        response = await api.post('/login', {
            email: email.value,
            password: password.value,
            role: role.value 
        })
    }

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
