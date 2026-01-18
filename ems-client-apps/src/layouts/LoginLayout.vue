<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="flex flex-center bg-grey-2">
        <q-card class="auth-card no-shadow bg-white q-pb-lg">
          <q-card-section class="text-center q-pt-xl q-pb-md">
            <!-- Optional Logo -->
             <q-avatar size="60px" class="q-mb-sm">
                <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-cyan.svg" />
             </q-avatar>
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
              
              <div v-if="role === 'parent'">
                  <div class="text-caption text-grey-7 q-mb-sm">Enter the Student ID and Registered Parent Phone Number</div>
                  <q-input 
                    outlined 
                    bg-color="grey-1"
                    color="primary"
                    label-color="grey-8"
                    input-class="text-grey-9"
                    v-model="studentId" 
                    label="Student ID (e.g. STU2026...)" 
                    :rules="[val => !!val || 'Student ID is required']"
                  >
                    <template v-slot:prepend><q-icon name="badge" color="primary" /></template>
                  </q-input>
                  <q-input 
                    outlined 
                    bg-color="grey-1"
                    color="primary"
                    label-color="grey-8"
                    input-class="text-grey-9"
                    v-model="parentPhone" 
                    label="Parent Phone Number" 
                    :rules="[val => !!val || 'Phone Number is required']"
                  >
                    <template v-slot:prepend><q-icon name="phone" color="primary" /></template>
                  </q-input>
              </div>

              <div v-else>
                  <q-input 
                    outlined 
                    bg-color="grey-1"
                    color="primary"
                    label-color="grey-8"
                    input-class="text-grey-9"
                    v-model="email" 
                    label="Email or Index Number" 
                    :rules="[val => !!val || 'This field is required']"
                  >
                    <template v-slot:prepend>
                      <q-icon name="account_circle" color="primary" />
                    </template>
                  </q-input>

                  <q-input 
                    outlined 
                    bg-color="grey-1"
                    color="primary"
                    label-color="grey-8"
                    input-class="text-grey-9"
                    v-model="password" 
                    label="Password" 
                    type="password" 
                    :rules="[val => !!val || 'Password is required']"
                  >
                    <template v-slot:prepend>
                      <q-icon name="lock" color="primary" />
                    </template>
                  </q-input>
              </div>
              
              <q-btn unelevated color="primary" size="lg" class="full-width q-mt-sm shadow-2" label="Login" type="submit" :loading="loading" />
            </q-form>
          </q-card-section>

          <div class="text-center q-mt-sm">
            <router-link to="/register" class="text-primary" style="text-decoration: none">Create an account</router-link>
          </div>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'stores/auth-store'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const role = ref('student')
const loading = ref(false)

// Parent specific fields
const parentPhone = ref('')
const studentId = ref('')

async function onSubmit() {
    loading.value = true
    try {
        let res;
        
        if (role.value === 'parent') {
             res = await authStore.loginParent({
                 phone: parentPhone.value,
                 student_id: studentId.value
             })
        } else {
             res = await authStore.login({
                 username: email.value,
                 password: password.value
             })
        }
        
        if (res.success) {
            // Strict Role Validation
            if (res.role !== role.value) {
                 $q.notify({ 
                     type: 'negative', 
                     message: `Access Denied: You cannot login as ${role.value.toUpperCase()} using ${res.role.toUpperCase()} credentials.` 
                 })
                 // Force logout to clear the invalid session
                 await authStore.logout()
                 return
            }

            $q.notify({ type: 'positive', message: `Welcome ${res.role.toUpperCase()}!` })
            
            // Redirect based on role
            switch(res.role) {
                case 'student': router.push('/student/dashboard'); break;
                case 'teacher': router.push('/teacher/dashboard'); break;
                case 'parent': router.push('/parent/dashboard'); break;
                default: 
                    // Fallback
                    router.push('/student/dashboard')
            }
        } else {
            $q.notify({ type: 'negative', message: res.message })
        }
    } catch {
        $q.notify({ type: 'negative', message: 'Login Error Occurred' })
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.auth-card { width: 100%; max-width: 450px; border-radius: 16px; margin-top: 50px; }
.login-container { background-color: #f5f5f5; }
</style>
