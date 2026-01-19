<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="flex flex-center bg-dark text-white relative-position overflow-hidden">
        
        <!-- 3D Background -->
        <div ref="authContainer" class="absolute-full" style="z-index: 0;"></div>

        <q-card class="auth-card glass-panel no-shadow q-pb-lg relative-position z-10">
          <q-card-section class="text-center q-pt-xl q-pb-md">
            <!-- Optional Logo -->
             <div class="q-mb-md">
                <q-icon name="school" size="50px" class="text-gradient-gold" />
             </div>
             <!-- Home Button -->
             <q-btn flat round icon="home" color="white" class="absolute-top-right q-ma-md hover-scale" to="/" title="Back to Home" />
            <div class="text-h4 text-weight-bolder text-white q-mb-xs">Welcome Back</div>
            <div class="text-grey-4">Login to your dashboard</div>
          </q-card-section>

          <q-tabs
            v-model="role"
            dense
            class="text-grey-4"
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
                  <div class="text-caption text-grey-5 q-mb-sm">Enter the Student ID and Registered Parent Phone Number</div>
                  <q-input 
                    dark outlined 
                    bg-color="transparent"
                    color="primary"
                    v-model="studentId" 
                    label="Student ID (e.g. STU2026...)" 
                    :rules="[val => !!val || 'Student ID is required']"
                  >
                    <template v-slot:prepend><q-icon name="badge" color="primary" /></template>
                  </q-input>
                  <q-input 
                    dark outlined 
                    bg-color="transparent"
                    color="primary"
                    v-model="parentPhone" 
                    label="Parent Phone Number" 
                    :rules="[val => !!val || 'Phone Number is required']"
                  >
                    <template v-slot:prepend><q-icon name="phone" color="primary" /></template>
                  </q-input>
              </div>

              <div v-else>
                  <q-input 
                    dark outlined 
                    bg-color="transparent"
                    color="primary"
                    v-model="email" 
                    label="Email or Index Number" 
                    :rules="[val => !!val || 'This field is required']"
                  >
                    <template v-slot:prepend>
                      <q-icon name="account_circle" color="primary" />
                    </template>
                  </q-input>

                  <q-input 
                    dark outlined 
                    bg-color="transparent"
                    color="primary"
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
              
              <q-btn unelevated color="primary" size="lg" class="full-width q-mt-sm shadow-10 glow-btn-pulse" label="Login" type="submit" :loading="loading" />
            </q-form>
          </q-card-section>

          <div class="text-center q-mt-sm">
            <router-link to="/register" class="text-primary hover-underline" style="text-decoration: none">Create an account</router-link>
          </div>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'stores/auth-store'
import * as THREE from 'three'

const $q = useQuasar()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const role = ref('student')
const loading = ref(false)
const authContainer = ref(null)

// Parent specific fields
const parentPhone = ref('')
const studentId = ref('')

// 3D Background Logic
let renderer, scene, camera, particlesMesh
let animationId = null

function init3DBackground() {
   if (!authContainer.value) return
   
   scene = new THREE.Scene()
   camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000)
   camera.position.z = 100

   renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
   renderer.setSize(window.innerWidth, window.innerHeight)
   authContainer.value.appendChild(renderer.domElement)

   // Particles
   const geometry = new THREE.BufferGeometry()
   const vertices = []
   const colors = []
   const color1 = new THREE.Color(0x2563EB)
   const color2 = new THREE.Color(0xFFD700)
   
   for (let i = 0; i < 1000; i++) {
      const x = (Math.random() - 0.5) * 600
      const y = (Math.random() - 0.5) * 600
      const z = (Math.random() - 0.5) * 600
      vertices.push(x, y, z)
      
      const mixedColor = Math.random() > 0.5 ? color1 : color2
      colors.push(mixedColor.r, mixedColor.g, mixedColor.b)
   }

   geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3))
   geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3))

   const material = new THREE.PointsMaterial({ size: 2, vertexColors: true, opacity: 0.6, transparent: true })
   particlesMesh = new THREE.Points(geometry, material)
   scene.add(particlesMesh)

   const animate = () => {
      animationId = requestAnimationFrame(animate)
      particlesMesh.rotation.y += 0.001
      particlesMesh.rotation.x += 0.0005
      renderer.render(scene, camera)
   }
   animate()
   
   window.addEventListener('resize', onWindowResize)
}

function onWindowResize() {
    if (!camera || !renderer) return
    camera.aspect = window.innerWidth / window.innerHeight
    camera.updateProjectionMatrix()
    renderer.setSize(window.innerWidth, window.innerHeight)
}

onMounted(() => {
   init3DBackground()
})

onUnmounted(() => {
   if (animationId) cancelAnimationFrame(animationId)
   window.removeEventListener('resize', onWindowResize)
})


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
                 await authStore.logout()
                 return
            }

            $q.notify({ type: 'positive', message: `Welcome ${res.role.toUpperCase()}!` })
            
            let targetPath = '/student/dashboard'
            switch(res.role) {
                case 'teacher': targetPath = '/teacher/dashboard'; break;
                case 'parent': targetPath = '/parent/dashboard'; break;
            }
            window.location.href = targetPath;
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
.auth-card { width: 100%; max-width: 450px; border-radius: 24px; margin-top: 0px; }
.glass-panel {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.text-gradient-gold {
  background: linear-gradient(to right, #FFD700, #FDB931);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.glow-btn-pulse {
   position: relative;
   overflow: hidden;
   animation: pulse-glow 2s infinite;
}
@keyframes pulse-glow {
   0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
   70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
   100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
}

.hover-scale { transition: transform 0.3s ease; }
.hover-scale:hover { transform: scale(1.1); }
</style>
