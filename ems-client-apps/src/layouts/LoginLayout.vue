<template>
  <q-layout view="lHh Lpr fff">
    <q-page-container>
      <q-page class="login-page window-height window-width flex flex-center relative-position overflow-hidden">

        <!-- Animated Background Elements (Fixed: pointer-events-none added) -->
        <div class="stars"></div>
        <div class="glow-spot spot-1"></div>
        <div class="glow-spot spot-2"></div>
        <div class="glow-spot spot-3"></div>

        <q-card class="login-card glass-panel shadow-24 q-pa-lg relative-position z-10" style="width: 100%; max-width: 480px;">

          <div class="text-center q-mb-lg">
             <!-- Dynamic Logo Icon -->
             <div class="avatar-glow q-mx-auto q-mb-md flex flex-center" :class="`bg-${activeColor}-dim`">
                <q-icon :name="activeIcon" size="40px" :color="activeColor" />
             </div>

             <h2 class="text-h4 text-white text-weight-bolder q-my-none tracking-wide">Welcome Back</h2>
             <div class="text-grey-4 text-subtitle1 q-mt-sm">
                Login as <span :class="`text-${activeColor} text-weight-bold`">{{ activeRoleLabel }}</span>
             </div>
          </div>

          <!-- Role Selection (Fixed: Using q-btn-toggle for reliable clicking) -->
          <div class="q-mb-xl">
            <q-btn-toggle
              v-model="tab"
              spread
              no-caps
              rounded
              unelevated
              toggle-color="white"
              color="white-dim"
              text-color="grey-5"
              toggle-text-color="dark"
              :options="[
                {label: 'Student', value: 'student', icon: 'school'},
                {label: 'Parent', value: 'parent', icon: 'escalator_warning'},
                {label: 'Teacher', value: 'teacher', icon: 'cast_for_education'}
              ]"
              class="glass-toggle shadow-2"
            />
          </div>

          <q-tab-panels v-model="tab" animated class="bg-transparent text-white panels-container">

            <!-- STUDENT LOGIN -->
            <q-tab-panel name="student" class="q-pa-none">
              <q-form @submit="handleLogin('student')" class="q-gutter-y-lg">
                <q-input
                    dark outlined
                    v-model="credentials.student.id"
                    label="Student ID"
                    color="blue"
                    class="input-glass"
                    placeholder="STU202XXXXX"
                    autocomplete="username"
                    :rules="[val => !!val || 'Student ID is required']"
                >
                    <template v-slot:prepend><q-icon name="badge" color="blue-4" /></template>
                </q-input>

                <q-input
                    dark outlined
                    v-model="credentials.student.password"
                    type="password"
                    label="Password"
                    color="blue"
                    class="input-glass"
                    autocomplete="current-password"
                    :rules="[val => !!val || 'Password is required']"
                >
                    <template v-slot:prepend><q-icon name="lock" color="blue-4" /></template>
                </q-input>

                <q-btn
                    type="submit"
                    color="primary"
                    class="full-width glow-btn-blue q-py-md text-weight-bold"
                    label="Login to Portal"
                    rounded
                    unelevated
                    :loading="loading"
                    no-caps
                    icon-right="arrow_forward"
                />
              </q-form>
            </q-tab-panel>

            <!-- PARENT LOGIN -->
            <q-tab-panel name="parent" class="q-pa-none">
              <div class="glass-alert q-mb-md row items-center q-pa-sm rounded-borders text-amber-2">
                 <q-icon name="info" class="q-mr-sm" size="xs"/>
                 <div class="text-caption">Use registered phone number</div>
              </div>

              <q-form @submit="handleLogin('parent')" class="q-gutter-y-lg">
                <q-input
                    dark outlined
                    v-model="credentials.parent.studentId"
                    label="Student ID"
                    color="amber"
                    class="input-glass"
                    placeholder="STU202XXXXX"
                    :rules="[val => !!val || 'Student ID is required']"
                >
                    <template v-slot:prepend><q-icon name="child_care" color="amber-4" /></template>
                </q-input>

                <q-input
                    dark outlined
                    v-model="credentials.parent.phone"
                    label="Phone Number"
                    color="amber"
                    class="input-glass"
                    mask="###-#######"
                    hint="Format: 07X-XXXXXXX"
                    autocomplete="tel"
                    :rules="[val => !!val || 'Phone is required', val => val.length === 11 || 'Invalid format']"
                >
                    <template v-slot:prepend><q-icon name="smartphone" color="amber-4" /></template>
                </q-input>

                <q-btn
                    type="submit"
                    color="amber-9"
                    text-color="dark"
                    class="full-width glow-btn-gold q-py-md text-weight-bold"
                    label="Parent Login"
                    rounded
                    unelevated
                    :loading="loading"
                    no-caps
                    icon-right="family_restroom"
                />
              </q-form>
            </q-tab-panel>

            <!-- TEACHER LOGIN -->
            <q-tab-panel name="teacher" class="q-pa-none">
              <q-form @submit="handleLogin('teacher')" class="q-gutter-y-lg">
                <q-input
                    dark outlined
                    v-model="credentials.teacher.id"
                    label="Teacher ID"
                    color="purple"
                    class="input-glass"
                    placeholder="TCH202XXXXX"
                    autocomplete="username"
                    :rules="[val => !!val || 'Teacher ID is required']"
                >
                    <template v-slot:prepend><q-icon name="school" color="purple-4" /></template>
                </q-input>

                <q-input
                    dark outlined
                    v-model="credentials.teacher.password"
                    type="password"
                    label="Password"
                    color="purple"
                    class="input-glass"
                    autocomplete="current-password"
                    :rules="[val => !!val || 'Password is required']"
                >
                    <template v-slot:prepend><q-icon name="vpn_key" color="purple-4" /></template>
                </q-input>

                <q-btn
                    type="submit"
                    color="purple-6"
                    class="full-width glow-btn-purple q-py-md text-weight-bold"
                    label="Teacher Access"
                    rounded
                    unelevated
                    :loading="loading"
                    no-caps
                    icon-right="login"
                />
              </q-form>
            </q-tab-panel>

          </q-tab-panels>

          <!-- Footer Links -->
          <div class="text-center q-mt-xl">
             <div class="text-grey-5 q-mb-sm text-caption">New to our institute?</div>
             <q-btn
                to="/register"
                outline
                rounded
                color="white"
                label="Create Student Account"
                no-caps
                class="full-width hover-glass"
             />
             <div class="q-mt-md">
                 <q-btn flat dense no-caps label="← Back to Home" to="/" color="grey-6" size="sm" class="hover-text-white transition-colors" />
             </div>
          </div>

        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'stores/auth-store'

const router = useRouter()
const $q = useQuasar()
const authStore = useAuthStore()

const tab = ref('student')
const loading = ref(false)

const credentials = reactive({
    student: { id: '', password: '' },
    teacher: { id: '', password: '' },
    parent: { studentId: '', phone: '' }
})

const activeColor = computed(() => {
    if (tab.value === 'student') return 'blue';
    if (tab.value === 'parent') return 'amber';
    return 'purple';
})

const activeIcon = computed(() => {
    if (tab.value === 'student') return 'local_library';
    if (tab.value === 'parent') return 'family_restroom';
    return 'psychology'; // Teacher
})

const activeRoleLabel = computed(() => {
    if (tab.value === 'student') return 'Student';
    if (tab.value === 'parent') return 'Parent';
    return 'Teacher';
})

const handleLogin = async (role) => {
    loading.value = true
    let result = { success: false, message: '' }

    try {
        if (role === 'student') {
            result = await authStore.login({
                username: credentials.student.id,
                password: credentials.student.password
            })
        }
        else if (role === 'teacher') {
             result = await authStore.login({
                username: credentials.teacher.id,
                password: credentials.teacher.password
            })
        }
        else if (role === 'parent') {
             result = await authStore.loginParent({
                student_id: credentials.parent.studentId,
                phone: credentials.parent.phone
            })
        }

        if (result.success) {
            $q.notify({ type: 'positive', message: `Welcome ${role}!` })

            // Redirect based on role returned from store or current tab
            if (role === 'parent') router.push('/parent/dashboard')
            else if (result.role === 'teacher') router.push('/teacher/dashboard')
            else router.push('/student/dashboard')
        } else {
            $q.notify({ type: 'negative', message: result.message || 'Login Failed' })
        }
    } catch (error) {
        console.error(error)
        $q.notify({ type: 'negative', message: 'An unexpected connection error.' })
    } finally {
        loading.value = false
    }
}
</script>

<style lang="scss" scoped>
.login-page {
  background-color: #050505;
  font-family: 'Inter', sans-serif;
}

/* Background Effects - Pointer Events NONE Added */
.stars {
  position: absolute;
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
  pointer-events: none; /* Allows clicks to pass through */
}

@keyframes stars-move {
    from { background-position: 0 0; }
    to { background-position: -200px 200px; }
}

/* Glow Spots - Pointer Events NONE Added */
.glow-spot {
   position: absolute;
   border-radius: 50%;
   filter: blur(100px);
   opacity: 0.15;
   z-index: 2;
   pointer-events: none; /* Allows clicks to pass through */
}
.spot-1 { width: 500px; height: 500px; top: -100px; left: -100px; background: #2563eb; }
.spot-2 { width: 400px; height: 400px; bottom: -50px; right: -50px; background: #7c3aed; }
.spot-3 { width: 300px; height: 300px; top: 40%; left: 50%; transform: translate(-50%, -50%); background: #FFD700; opacity: 0.08; }

/* Glass Card */
.glass-panel {
  background: rgba(15, 15, 20, 0.6);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 32px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

/* Toggle Styles */
.glass-toggle {
    border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.05);
    padding: 4px;
}
.text-white-dim { color: rgba(255,255,255,0.7); }
.bg-white-dim { background: rgba(255,255,255,0.05) !important; }

/* Avatar Glows */
.avatar-glow {
    width: 80px; height: 80px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 0 30px rgba(0,0,0,0.5);
}
.bg-blue-dim { background: rgba(37, 99, 235, 0.15); box-shadow: 0 0 30px rgba(37, 99, 235, 0.2); }
.bg-amber-dim { background: rgba(245, 158, 11, 0.15); box-shadow: 0 0 30px rgba(245, 158, 11, 0.2); }
.bg-purple-dim { background: rgba(124, 58, 237, 0.15); box-shadow: 0 0 30px rgba(124, 58, 237, 0.2); }

/* Inputs */
.input-glass :deep(.q-field__control) {
  background: rgba(255, 255, 255, 0.03) !important;
  backdrop-filter: blur(4px);
  border-radius: 12px;
}
.input-glass :deep(.q-field__control:before) {
    border-color: rgba(255,255,255,0.1);
}

/* Alert Box */
.glass-alert {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

/* Button Glows */
.glow-btn-blue {
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
    transition: all 0.3s ease;
}
.glow-btn-blue:hover { box-shadow: 0 4px 30px rgba(37, 99, 235, 0.6); transform: translateY(-2px); }

.glow-btn-gold {
    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
    transition: all 0.3s ease;
}
.glow-btn-gold:hover { box-shadow: 0 4px 30px rgba(245, 158, 11, 0.5); transform: translateY(-2px); }

.glow-btn-purple {
    box-shadow: 0 4px 20px rgba(124, 58, 237, 0.3);
    transition: all 0.3s ease;
}
.glow-btn-purple:hover { box-shadow: 0 4px 30px rgba(124, 58, 237, 0.5); transform: translateY(-2px); }

.hover-glass:hover {
   background: rgba(255, 255, 255, 0.08) !important;
}

.tracking-wide { letter-spacing: 0.025em; }
.transition-colors { transition: color 0.3s ease; }
.hover-text-white:hover { color: white !important; }
</style>
