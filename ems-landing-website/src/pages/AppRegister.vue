<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="auth-card no-shadow bg-white q-pb-lg">
      <q-card-section class="text-center q-pt-xl q-pb-md">
        <div class="text-h4 text-weight-bold text-secondary q-mb-xs">Join Us</div>
        <div class="text-grey-7">Create your new account</div>
      </q-card-section>

      <q-tabs
        v-model="role"
        dense
        class="text-grey"
        active-color="secondary"
        indicator-color="secondary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="student" label="Student Registration" icon="school" />
        <q-tab name="teacher" label="Teacher Registration" icon="person_outline" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="role" animated class="q-pa-none">
        <!-- STUDENT REGISTRATION FORM -->
        <q-tab-panel name="student" class="q-pa-lg">
          <q-form @submit="onSubmit" class="q-gutter-y-md">
            
            <!-- Personal Info Section -->
            <div>
              <div class="text-caption text-weight-bold text-grey-7 text-uppercase q-mb-sm">Personal Information</div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                   <q-input outlined v-model="form.name" label="Full Name (First, Second)" :rules="[val => !!val || 'Name is required']">
                      <template v-slot:prepend><q-icon name="person" /></template>
                   </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input outlined v-model="form.dob" label="Date of Birth" type="date" stack-label />
                </div>
                <div class="col-12 col-md-6">
                     <q-select outlined v-model="form.gender" :options="['Male', 'Female']" label="Gender" />
                </div>
                <!-- Contact -->
                <div class="col-12 col-md-6">
                    <q-input outlined v-model="form.phone" label="Phone Number" mask="###-#######">
                      <template v-slot:prepend><q-icon name="phone" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input outlined v-model="form.whatsapp" label="Whatsapp Number" mask="###-#######">
                      <template v-slot:prepend><q-icon name="chat" /></template>
                    </q-input>
                </div>
              </div>
            </div>

            <q-separator spaced />

            <!-- Academic Info Section -->
            <div>
              <div class="text-caption text-weight-bold text-grey-7 text-uppercase q-mb-sm">Academic Details</div>
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-input outlined v-model="form.school" label="School">
                       <template v-slot:prepend><q-icon name="school" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-select outlined v-model="form.grade" :options="['Grade 01','Grade 02','Grade 03','Grade 04','Grade 05','Grade 06','Grade 07','Grade 08','Grade 09','Grade 10','Grade 11','O/L','Grade 12','Grade 13','A/L','Others']" label="Grade" />
                </div>
              </div>
            </div>

            <q-separator spaced />

            <!-- Login Info -->
             <div>
              <div class="row q-col-gutter-md">
                <div class="col-12">
                   <q-input outlined v-model="form.email" label="Email Address (Optional)" type="email">
                      <template v-slot:prepend><q-icon name="email" /></template>
                   </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input outlined v-model="form.password" label="Password" type="password" :rules="[val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']">
                        <template v-slot:prepend><q-icon name="lock" /></template>
                     </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input outlined v-model="form.password_confirmation" label="Confirm Password" type="password" :rules="[val => val === form.password || 'Passwords match']">
                        <template v-slot:prepend><q-icon name="lock_clock" /></template>
                     </q-input>
                </div>
              </div>
            </div>

            <q-separator spaced />

            <!-- Parent Info -->
            <div>
               <div class="text-caption text-weight-bold text-grey-7 text-uppercase q-mb-sm">Parent / Guardian Details</div>
               <div class="row q-col-gutter-md">
                  <div class="col-12">
                     <q-input outlined v-model="form.parent_name" label="Parent Name" :rules="[val => !!val || 'Parent Name is required']">
                        <template v-slot:prepend><q-icon name="family_restroom" /></template>
                     </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input outlined v-model="form.parent_phone" label="Emergency / WhatsApp" mask="###-#######" :rules="[val => !!val || 'Phone is required']">
                        <template v-slot:prepend><q-icon name="contact_phone" /></template>
                     </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input outlined v-model="form.parent_email" label="Parent Email (Optional)" type="email" />
                  </div>
               </div>
            </div>
            
            <q-btn unelevated color="secondary" size="lg" class="full-width q-mt-lg" label="Register Student" type="submit" :loading="loading" />
          </q-form>
        </q-tab-panel>

        <!-- TEACHER REGISTRATION FORM -->
        <q-tab-panel name="teacher" class="q-pa-lg">
          <q-form @submit="onSubmit" class="q-gutter-y-md">
            
            <!-- Personal Info -->
            <div>
              <div class="text-caption text-weight-bold text-grey-7 text-uppercase q-mb-sm">Personal Information</div>
              <div class="row q-col-gutter-md">
                  <div class="col-12">
                      <q-input outlined v-model="form.name" label="Full Name" :rules="[val => !!val || 'Name is required']">
                          <template v-slot:prepend><q-icon name="person" /></template>
                      </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                      <q-input outlined v-model="form.nic" label="NIC Number" mask="#############a">
                         <template v-slot:prepend><q-icon name="badge" /></template>
                      </q-input>
                  </div>
                  <div class="col-12 col-md-6">
                      <q-input outlined v-model="form.phone" label="Phone Number" mask="###-#######">
                         <template v-slot:prepend><q-icon name="phone" /></template>
                      </q-input>
                  </div>
                  <div class="col-12">
                      <q-input outlined v-model="form.email" label="Email Address" type="email" :rules="[val => !!val || 'Email is required']">
                          <template v-slot:prepend><q-icon name="email" /></template>
                      </q-input>
                  </div>
              </div>
            </div>

            <!-- Credentials -->
             <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                     <q-input outlined v-model="form.password" label="Password" type="password" :rules="[val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']">
                        <template v-slot:prepend><q-icon name="lock" /></template>
                     </q-input>
                </div>
                <div class="col-12 col-md-6">
                     <q-input outlined v-model="form.password_confirmation" label="Confirm Password" type="password" :rules="[val => val === form.password || 'Passwords match']">
                        <template v-slot:prepend><q-icon name="lock_clock" /></template>
                     </q-input>
                </div>
            </div>

            <q-separator spaced />

            <!-- Professional Info -->
            <div>
                <div class="text-caption text-weight-bold text-grey-7 text-uppercase q-mb-sm">Professional Qualifications</div>
                
                <div class="q-mb-md">
                  <div class="text-grey-8 q-mb-xs">Educational Level</div>
                  <div class="row q-gutter-sm bg-grey-1 q-pa-sm rounded-borders">
                      <q-checkbox v-model="form.qualifications" val="O/L" label="O/L" color="secondary" />
                      <q-checkbox v-model="form.qualifications" val="A/L" label="A/L" color="secondary" />
                      <q-checkbox v-model="form.qualifications" val="Degree" label="Degree" color="secondary" />
                      <q-checkbox v-model="form.qualifications" val="Masters" label="Masters" color="secondary" />
                      <q-checkbox v-model="form.qualifications" val="Other" label="Other" color="secondary" />
                  </div>
                </div>

                <div class="row q-col-gutter-md">
                    <div class="col-12">
                        <q-select 
                            outlined 
                            v-model="form.subjects" 
                            multiple 
                            use-chips 
                            :options="['Mathematics', 'Science', 'English', 'Sinhala', 'History', 'ICT', 'Commerce', 'Art']" 
                            label="Expertise Subjects" 
                            hint="Select multiple if applicable"
                        >
                           <template v-slot:prepend><q-icon name="menu_book" /></template>
                        </q-select>
                    </div>
                    <div class="col-12">
                         <q-input outlined v-model="form.experience" label="Teaching Experience" type="textarea" rows="3" placeholder="Briefly describe your experience..." />
                    </div>
                </div>
            </div>

            <q-btn unelevated color="secondary" size="lg" class="full-width q-mt-lg" label="Register as Teacher" type="submit" :loading="loading" />
          </q-form>
        </q-tab-panel>

      </q-tab-panels>
      
      <div class="text-center q-mt-sm">
        <router-link to="/login" class="text-secondary" style="text-decoration: none">Already have an account? Login</router-link>
      </div>
    </q-card>

    <!-- SUCCESS DIALOG -->
    <q-dialog v-model="showSuccessDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6 text-positive">Registration Successful!</div>
        </q-card-section>

        <q-card-section class="q-pt-none text-center">
          <p>Your Index Number (User ID) is:</p>
          <div class="text-h4 text-weight-bold text-primary">{{ generatedIndexNumber }}</div>
          <p class="text-caption text-grey q-mt-sm">Please save this number. You will need it to login.</p>
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Proceed to Dashboard" @click="goToStudentDashboard" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
const $q = useQuasar()
const role = ref('student')
const loading = ref(false)
const showSuccessDialog = ref(false)
const generatedIndexNumber = ref('')
const registeredToken = ref('')

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
        registeredToken.value = response.data.token // Save token
        showSuccessDialog.value = true
    } else {
        $q.notify({
            color: 'positive',
            message: 'Registration Successful!'
        })
        // Direct redirect for teachers or if no index number
        if (response.data.redirect_url) {
             const targetUrl = new URL(response.data.redirect_url)
             if (response.data.token) {
                 targetUrl.searchParams.append('token', response.data.token)
             }
             window.location.href = targetUrl.toString()
        }
    }

  } catch (error) {
    console.error(error)
    let msg = error.response?.data?.message || 'Registration failed. Check your inputs.'
    
    // Check for "Already Registered" condition (validation error on unique fields)
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
    if (error.response?.data?.errors) {
         // Optional: Show validation errors more explicitly
    }
  } finally {
    loading.value = false
  }
}

const goToStudentDashboard = () => {
    const targetUrl = new URL('http://localhost:9001/student/dashboard')
    if (registeredToken.value) {
        targetUrl.searchParams.append('token', registeredToken.value)
    }
    window.location.href = targetUrl.toString()
}
</script>

<style scoped>
.auth-card { width: 100%; max-width: 600px; border-radius: 16px; }
</style>
