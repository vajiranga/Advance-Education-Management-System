<template>
  <q-page class="flex flex-center bg-dark text-white relative-position overflow-hidden">
    
    <!-- 3D Background -->
    <div ref="registerContainer" class="absolute-full" style="z-index: 0;"></div>

    <q-card class="auth-card glass-panel no-shadow q-pb-lg relative-position z-10">
      <q-card-section class="text-center q-pt-xl q-pb-md">
         <!-- Optional Logo -->
         <div class="q-mb-md">
            <q-icon name="person_add" size="50px" class="text-gradient-gold" />
         </div>
         <!-- Home Button -->
         <q-btn flat round icon="home" color="white" class="absolute-top-right q-ma-md hover-scale" to="/" title="Back to Home" />

        <div class="text-h4 text-weight-bolder text-white q-mb-xs">Join Us</div>
        <div class="text-grey-4">Create your new account</div>
      </q-card-section>

      <q-tabs
        v-model="role"
        dense
        class="text-grey-4"
        active-color="secondary"
        indicator-color="secondary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="student" label="Student Registration" icon="school" />
        <q-tab name="teacher" label="Teacher Registration" icon="person_outline" />
      </q-tabs>

      <q-separator dark />

      <q-tab-panels v-model="role" animated class="q-pa-none bg-transparent">
        <!-- STUDENT REGISTRATION FORM -->
        <q-tab-panel name="student" class="q-pa-lg">
          <q-form @submit="onSubmit" class="q-gutter-y-md">
            
            <!-- Personal Info Section -->
            <div>
              <div class="text-caption text-weight-bold text-grey-5 text-uppercase q-mb-sm">Personal Information</div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                   <q-input dark outlined bg-color="transparent" v-model="form.name" label="Full Name (First, Second)" :rules="[val => !!val || 'Name is required']">
                      <template v-slot:prepend><q-icon name="person" color="secondary" /></template>
                   </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input dark outlined bg-color="transparent" v-model="form.dob" label="Date of Birth" type="date" stack-label />
                </div>
                <div class="col-12 col-md-6">
                     <q-select dark outlined bg-color="transparent" v-model="form.gender" :options="['Male', 'Female']" label="Gender" />
                </div>
                <!-- Contact -->
                <div class="col-12 col-md-6">
                    <q-input dark outlined bg-color="transparent" v-model="form.phone" label="Phone Number" mask="###-#######">
                      <template v-slot:prepend><q-icon name="phone" color="secondary" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input dark outlined bg-color="transparent" v-model="form.whatsapp" label="Whatsapp Number" mask="###-#######">
                      <template v-slot:prepend><q-icon name="chat" color="green-4" /></template>
                    </q-input>
                </div>
              </div>
            </div>

            <q-separator dark spaced />

            <!-- Academic Info Section -->
            <div>
              <div class="text-caption text-weight-bold text-grey-5 text-uppercase q-mb-sm">Academic Details</div>
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-input dark outlined bg-color="transparent" v-model="form.school" label="School">
                       <template v-slot:prepend><q-icon name="school" color="secondary" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-select dark outlined bg-color="transparent" v-model="form.grade" :options="['Grade 01','Grade 02','Grade 03','Grade 04','Grade 05','Grade 06','Grade 07','Grade 08','Grade 09','Grade 10','Grade 11','O/L','Grade 12','Grade 13','A/L','Others']" label="Grade" />
                </div>
              </div>
            </div>

            <q-separator dark spaced />

            <!-- Login Info -->
             <div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                   <q-input dark outlined bg-color="transparent" v-model="form.email" label="Email Address (Optional)" type="email">
                      <template v-slot:prepend><q-icon name="email" color="secondary" /></template>
                   </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input dark outlined bg-color="transparent" v-model="form.password" label="Password" type="password" :rules="[val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']">
                        <template v-slot:prepend><q-icon name="lock" color="secondary" /></template>
                     </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input dark outlined bg-color="transparent" v-model="form.password_confirmation" label="Confirm Password" type="password" :rules="[val => val === form.password || 'Passwords match']">
                        <template v-slot:prepend><q-icon name="lock_clock" color="secondary" /></template>
                     </q-input>
                </div>
              </div>
            </div>

            <q-separator dark spaced />

            <!-- Parent Info -->
            <div>
               <div class="text-caption text-weight-bold text-grey-5 text-uppercase q-mb-sm">Parent / Guardian Details</div>
               <div class="row q-col-gutter-md">
                  <div class="col-12">
                     <q-input dark outlined bg-color="transparent" v-model="form.parent_name" label="Parent Name" :rules="[val => !!val || 'Parent Name is required']">
                        <template v-slot:prepend><q-icon name="family_restroom" color="secondary" /></template>
                     </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input dark outlined bg-color="transparent" v-model="form.parent_phone" label="Emergency / WhatsApp" mask="###-#######" :rules="[val => !!val || 'Phone is required']">
                        <template v-slot:prepend><q-icon name="contact_phone" color="secondary" /></template>
                     </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input dark outlined bg-color="transparent" v-model="form.parent_email" label="Parent Email (Optional)" type="email" />
                  </div>
               </div>
            </div>
            
            <q-btn unelevated color="secondary" size="lg" class="full-width q-mt-lg shadow-10 glow-btn-pulse" label="Register Student" type="submit" :loading="loading" />
          </q-form>
        </q-tab-panel>

        <!-- TEACHER REGISTRATION FORM -->
        <q-tab-panel name="teacher" class="q-pa-lg">
          <q-form @submit="onSubmit" class="q-gutter-y-md">
            
            <!-- Personal Info -->
            <div>
              <div class="text-caption text-weight-bold text-grey-5 text-uppercase q-mb-sm">Personal Information</div>
              <div class="row q-col-gutter-md">
                  <div class="col-12">
                      <q-input dark outlined bg-color="transparent" v-model="form.name" label="Full Name" :rules="[val => !!val || 'Name is required']">
                          <template v-slot:prepend><q-icon name="person" color="secondary" /></template>
                      </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                      <q-input dark outlined bg-color="transparent" v-model="form.nic" label="NIC Number" mask="#############a">
                         <template v-slot:prepend><q-icon name="badge" color="secondary" /></template>
                      </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                      <q-input dark outlined bg-color="transparent" v-model="form.phone" label="Phone Number" mask="###-#######">
                         <template v-slot:prepend><q-icon name="phone" color="secondary" /></template>
                      </q-input>
                  </div>
                  <div class="col-12">
                      <q-input dark outlined bg-color="transparent" v-model="form.email" label="Email Address" type="email" :rules="[val => !!val || 'Email is required']">
                          <template v-slot:prepend><q-icon name="email" color="secondary" /></template>
                      </q-input>
                  </div>
              </div>
            </div>

            <!-- Credentials -->
             <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                     <q-input dark outlined bg-color="transparent" v-model="form.password" label="Password" type="password" :rules="[val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']">
                        <template v-slot:prepend><q-icon name="lock" color="secondary" /></template>
                     </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input dark outlined bg-color="transparent" v-model="form.password_confirmation" label="Confirm Password" type="password" :rules="[val => val === form.password || 'Passwords match']">
                        <template v-slot:prepend><q-icon name="lock_clock" color="secondary" /></template>
                     </q-input>
                </div>
            </div>

            <q-separator dark spaced />

            <!-- Professional Info -->
            <div>
                <div class="text-caption text-weight-bold text-grey-5 text-uppercase q-mb-sm">Professional Qualifications</div>
                
                <div class="q-mb-md">
                  <div class="text-grey-4 q-mb-xs">Educational Level</div>
                  <div class="row q-gutter-sm bg-white-5 q-pa-sm rounded-borders">
                      <q-checkbox dark v-model="form.qualifications" val="O/L" label="O/L" color="secondary" />
                      <q-checkbox dark v-model="form.qualifications" val="A/L" label="A/L" color="secondary" />
                      <q-checkbox dark v-model="form.qualifications" val="Degree" label="Degree" color="secondary" />
                      <q-checkbox dark v-model="form.qualifications" val="Masters" label="Masters" color="secondary" />
                      <q-checkbox dark v-model="form.qualifications" val="Other" label="Other" color="secondary" />
                  </div>
                </div>

                <div class="row q-col-gutter-md">
                    <div class="col-12">
                        <q-select 
                            dark outlined bg-color="transparent"
                            v-model="form.subjects" 
                            multiple 
                            use-chips 
                            :options="['Mathematics', 'Science', 'English', 'Sinhala', 'History', 'ICT', 'Commerce', 'Art']" 
                            label="Expertise Subjects" 
                            hint="Select multiple if applicable"
                        >
                           <template v-slot:prepend><q-icon name="menu_book" color="secondary" /></template>
                        </q-select>
                    </div>
                    <div class="col-12">
                         <q-input dark outlined bg-color="transparent" v-model="form.experience" label="Teaching Experience" type="textarea" rows="3" placeholder="Briefly describe your experience..." />
                    </div>
                </div>
            </div>

            <q-btn unelevated color="secondary" size="lg" class="full-width q-mt-lg shadow-10 glow-btn-pulse" label="Register as Teacher" type="submit" :loading="loading" />
          </q-form>
        </q-tab-panel>

      </q-tab-panels>
      
      <div class="text-center q-mt-sm">
        <router-link to="/login" class="text-secondary hover-underline" style="text-decoration: none">Already have an account? Login</router-link>
      </div>
    </q-card>

    <!-- SUCCESS DIALOG -->
    <q-dialog v-model="showSuccessDialog" persistent>
      <q-card style="min-width: 350px" class="glass-panel text-white">
        <q-card-section>
          <div class="text-h6 text-green-4">Registration Successful!</div>
        </q-card-section>

        <q-card-section class="q-pt-none text-center">
          <p>Your Index Number (User ID) is:</p>
          <div class="text-h4 text-weight-bold text-secondary">{{ generatedIndexNumber }}</div>
          <p class="text-caption text-grey-4 q-mt-sm">Please save this number. You will need it to login.</p>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat color="white" label="Proceed to Dashboard" @click="goToStudentDashboard" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth-store'
import * as THREE from 'three'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()
const registerContainer = ref(null)

const role = ref('student')
const loading = ref(false)
const showSuccessDialog = ref(false)
const generatedIndexNumber = ref('')

const form = reactive({
  name: '',
  dob: '',
  gender: '',
  phone: '',
  whatsapp: '',
  school: '',
  grade: '',
  email: '',
  password: '',
  password_confirmation: '',
  parent_name: '',
  parent_phone: '',
  parent_email: '',
  // Teacher Fields
  nic: '',
  qualifications: [],
  subjects: [],
  experience: ''
})

// 3D Background Logic
let renderer, scene, camera, particlesMesh
let animationId = null

function init3DBackground() {
   if (!registerContainer.value) return
   
   scene = new THREE.Scene()
   camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000)
   camera.position.z = 100

   renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
   renderer.setSize(window.innerWidth, window.innerHeight)
   registerContainer.value.appendChild(renderer.domElement)

   // Particles
   const geometry = new THREE.BufferGeometry()
   const vertices = []
   const colors = []
   const color1 = new THREE.Color(0x2563EB)
   const color2 = new THREE.Color(0xFFD700)
   
   for (let i = 0; i < 1500; i++) {
      const x = (Math.random() - 0.5) * 800
      const y = (Math.random() - 0.5) * 800
      const z = (Math.random() - 0.5) * 800
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
      particlesMesh.rotation.y -= 0.001
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


const onSubmit = async () => {
  loading.value = true
  try {
    const payload = {
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        role: role.value,
    }

    // Add student specific fields
    if (role.value === 'student') {
        Object.assign(payload, {
            dob: form.dob,
            gender: form.gender,
            phone: form.phone,
            whatsapp: form.whatsapp,
            school: form.school,
            grade: form.grade,
            parent_name: form.parent_name,
            parent_phone: form.parent_phone,
            parent_email: form.parent_email
        })
    } else {
        // Teacher fields
        Object.assign(payload, {
            nic: form.nic,
            phone: form.phone, // Shared field
            qualifications: form.qualifications,
            subjects: form.subjects,
            experience: form.experience
        })
    }

    const response = await api.post('/register', payload)

    if (role.value === 'student' && response.data.index_number) {
        generatedIndexNumber.value = response.data.index_number
        // registeredToken.value = response.data.token // Save token - Removed as per new code
        showSuccessDialog.value = true
        
        // Auto-login logic could be here, but we let user see ID first
        if (response.data.user && response.data.token) {
           authStore.addAccount({ user: response.data.user, token: response.data.token })
        }

    } else {
        $q.notify({
            color: 'positive',
            message: 'Registration Successful!'
        })
        
        // Teacher or other Redirect
        // Since we are in the same app, just router push
        router.push('/login')
    }

  } catch (error) {
    console.error(error)
    let msg = error.response?.data?.message || 'Registration failed. Check your inputs.'
    
    if (error.response?.status === 422 && error.response.data.errors) {
        const e = error.response.data.errors
        if (e.email || e.username || e.phone || e.nic) {
             msg = 'You are already registered! Please Login.'
        }
    }

    $q.notify({
        color: 'negative',
        message: msg
    })
  } finally {
    loading.value = false
  }
}

const goToStudentDashboard = () => {
    // Already in client app, so direct push
    router.push('/student/dashboard')
}
</script>

<style scoped>
.auth-card { width: 100%; max-width: 600px; border-radius: 24px; }
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
   0% { box-shadow: 0 0 0 0 rgba(38, 166, 154, 0.7); }
   70% { box-shadow: 0 0 0 10px rgba(38, 166, 154, 0); }
   100% { box-shadow: 0 0 0 0 rgba(38, 166, 154, 0); }
}

.hover-scale { transition: transform 0.3s ease; }
.hover-scale:hover { transform: scale(1.1); }
.hover-underline:hover { text-decoration: underline !important; }

.bg-white-5 { background: rgba(255, 255, 255, 0.05); }
</style>
