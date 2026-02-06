<template>
  <q-page class="register-page flex flex-center relative-position">

    <!-- Background Elements (CSS Based - Matches Login) -->
    <div class="stars"></div>
    <div class="glow-spot spot-1"></div>
    <div class="glow-spot spot-2"></div>
    <div class="glow-spot spot-3"></div>

    <q-card class="auth-card glass-panel shadow-24 q-my-xl relative-position z-10">
      <q-card-section class="text-center q-pt-xl q-pb-md">
         <!-- Home Button -->
         <q-btn flat round icon="arrow_back" color="white" class="absolute-top-left q-ma-md hover-scale" to="/" title="Back to Home" />

         <!-- Icon -->
         <div class="avatar-glow q-mx-auto q-mb-md flex flex-center" :class="role === 'student' ? 'bg-blue-dim' : 'bg-purple-dim'">
            <q-icon :name="role === 'student' ? 'person_add' : 'cast_for_education'" size="40px" :color="role === 'student' ? 'blue' : 'purple'" />
         </div>

         <div class="text-h4 text-weight-bolder text-white q-mb-xs tracking-wide">Create Account</div>
         <div class="text-grey-4">Join our community as a <span class="text-weight-bold" :class="role === 'student' ? 'text-blue' : 'text-purple'">{{ role === 'student' ? 'Student' : 'Teacher' }}</span></div>
      </q-card-section>

      <!-- Role Tabs -->
      <div class="q-px-lg q-mb-md">
        <div class="glass-tabs row no-wrap p-1">
             <div
                class="col tab-item text-center q-py-sm cursor-pointer relative-position"
                :class="{ 'active': role === 'student' }"
                @click="role = 'student'"
             >
                <q-icon name="school" size="20px" class="q-mr-xs" />
                <span class="text-weight-medium">Student</span>
                <div v-if="role === 'student'" class="absolute-full bg-white-10 rounded-borders transition-bg"></div>
             </div>
             <div
                class="col tab-item text-center q-py-sm cursor-pointer relative-position"
                :class="{ 'active': role === 'teacher' }"
                @click="selectTeacher"
             >
                <q-icon name="psychology" size="20px" class="q-mr-xs" />
                <span class="text-weight-medium">Teacher</span>
                <div v-if="role === 'teacher'" class="absolute-full bg-white-10 rounded-borders transition-bg"></div>
             </div>
        </div>
      </div>

      <q-tab-panels v-model="role" animated class="bg-transparent text-white">
        <!-- STUDENT REGISTRATION FORM -->
        <q-tab-panel name="student" class="q-pa-lg">
          <q-form @submit="onSubmit" class="q-gutter-y-lg">

            <!-- Personal Info Section -->
            <div>
              <div class="text-caption text-weight-bold text-blue-4 text-uppercase q-mb-sm letter-spacing-1">Personal Information</div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                   <q-input dark outlined class="input-glass" v-model="form.name" label="Full Name (First, Second)" color="blue" :rules="[val => !!val || 'Name is required']">
                      <template v-slot:prepend><q-icon name="person" color="blue-5" /></template>
                   </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input dark outlined class="input-glass" v-model="form.dob" label="Date of Birth" type="date" stack-label color="blue" />
                </div>
                <div class="col-12 col-md-6">
                     <q-select dark outlined class="input-glass" v-model="form.gender" :options="['Male', 'Female']" label="Gender" color="blue" />
                </div>
                <!-- Contact -->
                <div class="col-12 col-md-6">
                    <q-input dark outlined class="input-glass" v-model="form.phone" label="Phone Number" mask="###-#######" color="blue">
                      <template v-slot:prepend><q-icon name="phone" color="blue-5" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input dark outlined class="input-glass" v-model="form.whatsapp" label="Whatsapp Number" mask="###-#######" color="green">
                      <template v-slot:prepend><q-icon name="chat" color="green-4" /></template>
                    </q-input>
                </div>
              </div>
            </div>

            <q-separator dark class="opacity-20" />

            <!-- Academic Info Section -->
            <div>
              <div class="text-caption text-weight-bold text-blue-4 text-uppercase q-mb-sm letter-spacing-1">Academic Details</div>
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-input dark outlined class="input-glass" v-model="form.school" label="School" color="blue">
                       <template v-slot:prepend><q-icon name="school" color="blue-5" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-select dark outlined class="input-glass" v-model="form.grade" :options="['Grade 01','Grade 02','Grade 03','Grade 04','Grade 05','Grade 06','Grade 07','Grade 08','Grade 09','Grade 10','Grade 11','O/L','Grade 12','Grade 13','A/L','Others']" label="Grade" color="blue" />
                </div>
              </div>
            </div>

            <q-separator dark class="opacity-20" />

            <!-- Login Info -->
             <div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                   <q-input dark outlined class="input-glass" v-model="form.email" label="Email Address (Optional)" type="email" color="blue">
                      <template v-slot:prepend><q-icon name="email" color="blue-5" /></template>
                   </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input dark outlined class="input-glass" v-model="form.password" label="Password" type="password" color="blue" :rules="[val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']">
                        <template v-slot:prepend><q-icon name="lock" color="blue-5" /></template>
                     </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input dark outlined class="input-glass" v-model="form.password_confirmation" label="Confirm Password" type="password" color="blue" :rules="[val => val === form.password || 'Passwords match']">
                        <template v-slot:prepend><q-icon name="lock_clock" color="blue-5" /></template>
                     </q-input>
                </div>
              </div>
            </div>

            <q-separator dark class="opacity-20" />

            <!-- Parent Info -->
            <div>
               <div class="text-caption text-weight-bold text-amber-4 text-uppercase q-mb-sm letter-spacing-1">Parent / Guardian Details</div>
               <div class="glass-alert row items-center q-pa-sm rounded-borders text-amber-2 q-mb-md">
                   <q-icon name="info" class="q-mr-sm" size="xs"/>
                   <div class="text-caption">Parent account will be created automatically using this number.</div>
               </div>

               <div class="row q-col-gutter-md">
                  <div class="col-12">
                     <q-input dark outlined class="input-glass" v-model="form.parent_name" label="Parent Name" color="amber" :rules="[val => !!val || 'Parent Name is required']">
                        <template v-slot:prepend><q-icon name="family_restroom" color="amber-5" /></template>
                     </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input dark outlined class="input-glass" v-model="form.parent_phone" label="Parent Phone (Login ID)" mask="###-#######" color="amber" :rules="[val => !!val || 'Phone is required']">
                        <template v-slot:prepend><q-icon name="contact_phone" color="amber-5" /></template>
                     </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input dark outlined class="input-glass" v-model="form.parent_email" label="Parent Email (Optional)" type="email" color="amber" />
                  </div>
               </div>
            </div>

            <q-btn unelevated rounded color="primary" size="lg" class="full-width q-mt-lg glow-btn-blue text-weight-bold" label="Register Student" type="submit" :loading="loading" />
          </q-form>
        </q-tab-panel>

        <!-- TEACHER REGISTRATION FORM -->
        <q-tab-panel name="teacher" class="q-pa-lg">
          <q-form @submit="onSubmit" class="q-gutter-y-lg">

            <!-- Personal Info -->
            <div>
              <div class="text-caption text-weight-bold text-purple-3 text-uppercase q-mb-sm letter-spacing-1">Personal Information</div>
              <div class="row q-col-gutter-md">
                  <div class="col-12">
                      <q-input dark outlined class="input-glass" v-model="form.name" label="Full Name" color="purple" :rules="[val => !!val || 'Name is required']">
                          <template v-slot:prepend><q-icon name="person" color="purple-4" /></template>
                      </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                      <q-input dark outlined class="input-glass" v-model="form.nic" label="NIC Number" mask="#############a" color="purple">
                         <template v-slot:prepend><q-icon name="badge" color="purple-4" /></template>
                      </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                      <q-input dark outlined class="input-glass" v-model="form.phone" label="Phone Number" mask="###-#######" color="purple">
                         <template v-slot:prepend><q-icon name="phone" color="purple-4" /></template>
                      </q-input>
                  </div>
                  <div class="col-12">
                      <q-input dark outlined class="input-glass" v-model="form.email" label="Email Address" type="email" color="purple" :rules="[val => !!val || 'Email is required']">
                          <template v-slot:prepend><q-icon name="email" color="purple-4" /></template>
                      </q-input>
                  </div>
              </div>
            </div>

            <!-- Credentials -->
             <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                     <q-input dark outlined class="input-glass" v-model="form.password" label="Password" type="password" color="purple" :rules="[val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']">
                        <template v-slot:prepend><q-icon name="lock" color="purple-4" /></template>
                     </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input dark outlined class="input-glass" v-model="form.password_confirmation" label="Confirm Password" type="password" color="purple" :rules="[val => val === form.password || 'Passwords match']">
                        <template v-slot:prepend><q-icon name="lock_clock" color="purple-4" /></template>
                     </q-input>
                </div>
            </div>

            <q-separator dark class="opacity-20" />

            <!-- Professional Info -->
            <div>
                <div class="text-caption text-weight-bold text-purple-3 text-uppercase q-mb-sm letter-spacing-1">Professional Qualifications</div>

                <div class="q-mb-md">
                  <div class="text-grey-4 q-mb-xs">Educational Level</div>
                  <div class="row q-gutter-sm bg-white-5 q-pa-sm rounded-borders">
                      <q-checkbox dark v-model="form.qualifications" val="Degree" label="Degree" color="purple" />
                      <q-checkbox dark v-model="form.qualifications" val="Masters" label="Masters" color="purple" />
                      <q-checkbox dark v-model="form.qualifications" val="PhD" label="PhD" color="purple" />
                      <q-checkbox dark v-model="form.qualifications" val="Other" label="Other" color="purple" />
                  </div>
                </div>

                <div class="row q-col-gutter-md">
                    <div class="col-12">
                        <q-select
                            dark outlined class="input-glass"
                            v-model="form.subjects"
                            multiple
                            use-chips
                            :options="['Mathematics', 'Science', 'English', 'Sinhala', 'History', 'ICT', 'Commerce', 'Art']"
                            label="Expertise Subjects"
                            color="purple"
                        >
                           <template v-slot:prepend><q-icon name="menu_book" color="purple-4" /></template>
                        </q-select>
                    </div>
                </div>
            </div>

            <q-btn unelevated rounded color="purple-6" size="lg" class="full-width q-mt-lg glow-btn-purple text-weight-bold" label="Reqeust Teacher Account" type="submit" :loading="loading" />
          </q-form>
        </q-tab-panel>

      </q-tab-panels>

      <div class="text-center q-pb-lg">
        <router-link to="/login" class="text-grey-5 hover-text-white transition-colors" style="text-decoration: none">Already have an account? Login</router-link>
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
          <div class="text-h4 text-weight-bold text-blue-4 tracking-wide">{{ generatedIndexNumber }}</div>
          <p class="text-caption text-grey-4 q-mt-sm">Please save this number. You will need it to login.</p>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat color="white" label="Proceed to Dashboard" @click="goToStudentDashboard" />
        </q-card-actions>
      </q-card>
    </q-dialog>


    <!-- TEACHER REGISTRATION BLOCKED DIALOG -->
    <q-dialog v-model="blockedDialog">
      <q-card style="min-width: 350px" class="glass-panel text-white">
        <q-card-section>
          <div class="text-h6 text-amber-4">Registration Restricted</div>
        </q-card-section>

        <q-card-section class="q-pt-none text-center">
          <q-icon name="lock" size="60px" color="grey-5" class="q-mb-md" />
          <p>Teacher registration is currently closed or restricted by the administrator.</p>
          <p class="text-caption text-grey-4">Please contact the admin if you believe this is a mistake.</p>
        </q-card-section>

        <q-card-actions align="center" class="column q-gutter-y-sm q-pb-lg">
          <q-btn unelevated rounded color="purple" label="Login as Teacher" to="/login" class="full-width" />
          <q-btn outline rounded color="white" label="Student Registration" v-close-popup class="full-width" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth-store'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()

const role = ref('student')
const loading = ref(false)
const showSuccessDialog = ref(false)
const generatedIndexNumber = ref('')

// System Config
const config = ref({
    blockTeacherRegistration: false
})
const blockedDialog = ref(false)

onMounted(async () => {
    try {
        const res = await api.get('/v1/settings/config')
        if (res.data) {
             config.value = { ...config.value, ...res.data }
             // Ensure boolean type
             config.value.blockTeacherRegistration = String(config.value.blockTeacherRegistration) === '1' || String(config.value.blockTeacherRegistration) === 'true'
        }
    } catch (e) {
        console.error('Failed to load config', e)
    }
})

// Watch for late config load
watch(() => config.value.blockTeacherRegistration, (isBlocked) => {
    if (isBlocked && role.value === 'teacher') {
        role.value = 'student' // Immediate reset
        blockedDialog.value = true
    }
})

// Role Selection Logic
const selectTeacher = () => {
    if (config.value.blockTeacherRegistration) {
        blockedDialog.value = true
    } else {
        role.value = 'teacher'
    }
}

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
        showSuccessDialog.value = true

        // Auto-login logic
        if (response.data.user && response.data.token && authStore) {
           authStore.addAccount({ user: response.data.user, token: response.data.token })
        }

    } else {
        $q.notify({
            type: 'positive',
            message: 'Registration Successful! Please wait for approval.'
        })
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
        type: 'negative',
        message: msg
    })
  } finally {
    loading.value = false
  }
}

const goToStudentDashboard = () => {
    router.push('/student/dashboard')
}
</script>

<style lang="scss" scoped>
.register-page {
  background-color: #050505;
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
}

/* Background Effects (Matches Login Style) */
.stars {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  width: 100%; height: 100%;
  background: #000;
  background-image:
        radial-gradient(1px 1px at 20px 30px, #eee, rgba(0,0,0,0)),
        radial-gradient(1px 1px at 40px 70px, #fff, rgba(0,0,0,0)),
        radial-gradient(1px 1px at 50px 160px, #ddd, rgba(0,0,0,0)),
        radial-gradient(1px 1px at 90px 40px, #fff, rgba(0,0,0,0)),
        radial-gradient(1px 1px at 130px 80px, #fff, rgba(0,0,0,0)),
        radial-gradient(1.5px 1.5px at 160px 120px, #ddd, rgba(0,0,0,0));
  background-repeat: repeat;
  background-size: 200px 200px;
  animation: stars-move 100s linear infinite;
  opacity: 0.6;
  z-index: 0;
  pointer-events: none;
}
@keyframes stars-move {
    from { background-position: 0 0; }
    to { background-position: -200px 200px; }
}

.glow-spot {
   position: fixed;
   border-radius: 50%;
   filter: blur(100px);
   opacity: 0.15;
   z-index: 2;
   pointer-events: none;
}
.spot-1 { width: 500px; height: 500px; top: -100px; left: -100px; background: #2563eb; }
.spot-2 { width: 400px; height: 400px; bottom: -50px; right: -50px; background: #7c3aed; }
.spot-3 { width: 300px; height: 300px; top: 40%; left: 50%; transform: translate(-50%, -50%); background: #FFD700; opacity: 0.08; }

.auth-card { width: 100%; max-width: 700px; border-radius: 32px; }

/* Glass Panel */
.glass-panel {
  background: rgba(15, 15, 20, 0.6);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

/* Tabs */
.glass-tabs {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.tab-item {
  color: #9CA3AF;
  border-radius: 12px;
  transition: all 0.3s ease;
}
.tab-item.active {
  color: white;
  text-shadow: 0 0 10px rgba(255,255,255,0.3);
}
.bg-white-10 { background: rgba(255,255,255,0.1); }
.transition-bg { transition: background 0.3s ease; }

/* Inputs */
.input-glass :deep(.q-field__control) {
  background: rgba(255, 255, 255, 0.03) !important;
  backdrop-filter: blur(4px);
  border-radius: 12px;
}
.input-glass :deep(.q-field__control:before) {
    border-color: rgba(255,255,255,0.1);
}

/* Styles */
.tracking-wide { letter-spacing: 0.025em; }
.letter-spacing-1 { letter-spacing: 1px; }
.opacity-20 { opacity: 0.2; }

/* Buttons */
.glow-btn-blue {
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
    transition: all 0.3s ease;
}
.glow-btn-blue:hover { box-shadow: 0 4px 30px rgba(37, 99, 235, 0.6); transform: translateY(-2px); }

.glow-btn-purple {
    box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
    transition: all 0.3s ease;
}
.glow-btn-purple:hover { box-shadow: 0 4px 30px rgba(124, 58, 237, 0.5); transform: translateY(-2px); }

/* Avatar Glows */
.avatar-glow {
    width: 80px; height: 80px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 0 30px rgba(0,0,0,0.5);
}
.bg-blue-dim { background: rgba(37, 99, 235, 0.15); box-shadow: 0 0 30px rgba(37, 99, 235, 0.2); }
.bg-purple-dim { background: rgba(124, 58, 237, 0.15); box-shadow: 0 0 30px rgba(124, 58, 237, 0.2); }

.glass-alert {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.hover-scale { transition: transform 0.3s ease; }
.hover-scale:hover { transform: scale(1.1); }
.hover-text-white:hover { color: white !important; }
.transition-colors { transition: color 0.3s ease; }
</style>
