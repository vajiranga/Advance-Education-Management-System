<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">My Profile</div>
    </div>

    <div class="row q-col-gutter-lg items-stretch">
      <!-- Left Column: Profile Card & ID Download -->
      <div class="col-12 col-md-4 column q-gutter-y-lg">
          <!-- Profile Summary -->
          <q-card class="col-auto text-center q-pa-lg shadow-2 rounded-borders" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
              <q-avatar size="100px" font-size="50px" :color="$q.dark.isActive ? 'deep-purple-9' : 'primary'" text-color="white" class="shadow-3 q-mb-md">
                {{ form.name.charAt(0) }}
              </q-avatar>
              <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-grey-9'">{{ authStore.user?.name }}</div>
              <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-blue-2' : 'text-primary'">{{ displayId }}</div>
              <div class="text-caption text-uppercase q-mt-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">{{ form.role }}</div>
          </q-card>

          <!-- Barcode / Download Section -->
          <q-card class="col-auto text-center q-pa-lg shadow-2 rounded-borders" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
             <div class="text-subtitle1 text-weight-bold q-mb-md" :class="$q.dark.isActive ? 'text-white' : 'text-grey-9'">Student ID</div>

             <!-- Barcode Display -->
             <div class="q-pa-sm rounded-borders inline-block q-mb-md" :class="$q.dark.isActive ? 'bg-white' : 'bg-grey-2'">
               <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 48px; line-height: 1; color: black !important;">
                 *{{ displayId }}*
               </div>
             </div>
             <div class="text-caption q-mb-md" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">Scan at Entrance</div>

             <q-btn
                icon="download"
                label="Download ID Card"
                color="secondary"
                unelevated
                class="full-width"
                @click="downloadIdCard"
                :loading="downloading"
             />
          </q-card>
      </div>

      <!-- Right Column: Edit Form -->
      <div class="col-12 col-md-8">
        <q-card class="full-height q-pa-lg shadow-2 rounded-borders" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
          <q-form @submit="updateProfile" class="column full-height">

            <!-- Personal Info -->
            <div class="text-h6 q-mb-md" :class="$q.dark.isActive ? 'text-blue-2' : 'text-primary'">
                <q-icon name="person_outline" size="sm" class="q-mr-sm" />Personal Information
            </div>

            <div class="row q-col-gutter-lg q-mb-lg">
                <div class="col-12 col-md-6">
                    <q-input
                        v-model="form.name"
                        label="Full Name"
                        outlined
                        dense
                        :dark="$q.dark.isActive"
                    >
                      <template v-slot:prepend><q-icon name="badge" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input
                        v-model="form.email"
                        label="Email Address"
                        outlined
                        dense
                        disable
                        readonly
                        :dark="$q.dark.isActive"
                        bg-color="transparent"
                    >
                      <template v-slot:prepend><q-icon name="email" /></template>
                      <template v-slot:append><q-icon name="lock" size="xs" color="grey" /></template>
                    </q-input>
                </div>
            </div>

            <q-separator class="q-my-md" :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

            <!-- Contact Details -->
            <div class="text-h6 q-mb-md" :class="$q.dark.isActive ? 'text-blue-2' : 'text-primary'">
                <q-icon name="contact_phone" size="sm" class="q-mr-sm" />Contact Details
            </div>

            <div class="row q-col-gutter-lg q-mb-lg">
              <div class="col-12 col-md-6">
                 <q-input
                    v-model="form.phone"
                    label="Phone Number"
                    outlined
                    dense
                    :dark="$q.dark.isActive"
                >
                   <template v-slot:prepend><q-icon name="phone" /></template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                 <q-input
                    v-model="form.whatsapp"
                    label="WhatsApp Number"
                    outlined
                    dense
                    :dark="$q.dark.isActive"
                >
                   <template v-slot:prepend><q-icon name="chat" /></template>
                </q-input>
              </div>
            </div>

            <q-separator class="q-my-md" :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

            <!-- Security -->
            <div class="text-h6 q-mb-md" :class="$q.dark.isActive ? 'text-blue-2' : 'text-primary'">
                <q-icon name="security" size="sm" class="q-mr-sm" />Security
            </div>

            <div class="row q-col-gutter-lg">
                <div class="col-12 col-md-6">
                    <q-input
                        v-model="form.password"
                        type="password"
                        label="New Password"
                        outlined
                        dense
                        :dark="$q.dark.isActive"
                    >
                       <template v-slot:prepend><q-icon name="lock_open" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input
                        v-model="form.password_confirmation"
                        type="password"
                        label="Confirm Password"
                        outlined
                        dense
                        :dark="$q.dark.isActive"
                    >
                       <template v-slot:prepend><q-icon name="lock" /></template>
                    </q-input>
                </div>
            </div>

            <q-space />

            <!-- Actions -->
            <div class="row justify-end q-mt-xl">
              <q-btn label="Update Profile" icon="save" type="submit" color="primary" :loading="loading" class="q-px-xl q-py-xs" unelevated />
            </div>

          </q-form>
        </q-card>
      </div>
    </div>

    <!-- Hidden ID Card Template (Matches Dashboard) -->
    <div style="position: fixed; left: -9999px; top: 0;">
        <div ref="idCardRef" id="id-card-element" style="width: 400px; height: 280px; background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%); color: white; border-radius: 12px; padding: 20px; font-family: 'Roboto', sans-serif; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <!-- Decorative Circle -->
            <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>

            <div class="row items-center no-wrap full-height">
                 <!-- Photo Placeholder -->
                  <div style="width: 100px; height: 100px; background: white; border-radius: 8px; margin-right: 20px; display: flex; align-items: center; justify-content: center; color: #1976D2; font-weight: bold; font-size: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                       {{ authStore.user?.name?.charAt(0) || 'S' }}
                  </div>

                  <!-- Details -->
                  <div style="flex: 1; z-index: 1;">
                       <div style="font-size: 12px; opacity: 0.8; letter-spacing: 1px; margin-bottom: 4px;">EMS STUDENT IDENTITY</div>
                       <div style="font-size: 20px; font-weight: bold; margin-bottom: 4px; line-height: 1.2;">{{ authStore.user?.name || 'Student Name' }}</div>
                       <div style="font-size: 14px; opacity: 0.9; margin-bottom: 12px;">{{ displayId }}</div>

                       <div class="row q-col-gutter-xs">
                            <div class="col-6">
                                <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Grade</div>
                                <div style="font-size: 13px; font-weight: 500;">{{ authStore.user?.grade || 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Date of Birth</div>
                                <div style="font-size: 13px; font-weight: 500;">{{ authStore.user?.dob || 'N/A' }}</div>
                            </div>
                       </div>

                       <div class="row q-col-gutter-xs q-mt-xs">
                           <div class="col-6">
                               <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Phone</div>
                               <div style="font-size: 13px; font-weight: 500;">{{ authStore.user?.phone || 'N/A' }}</div>
                           </div>
                           <div class="col-6">
                               <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Year</div>
                               <div style="font-size: 13px; font-weight: 500;">2026</div>
                           </div>
                       </div>
                  </div>
            </div>

            <!-- Barcode Footer -->
            <div style="position: absolute; bottom: 15px; left: 20px; right: 20px; background: white; padding: 4px 10px; text-align: center; border-radius: 6px;">
                 <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 36px; color: black; line-height: 1; margin-bottom: -5px;">*{{ displayId }}*</div>
            </div>
        </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import html2canvas from 'html2canvas'

const $q = useQuasar()
const authStore = useAuthStore()
const loading = ref(false)
const downloading = ref(false)
const idCardRef = ref(null)

const form = ref({
  name: '',
  email: '',
  phone: '',
  whatsapp: '',
  role: 'student',
  password: '',
  password_confirmation: ''
})

const displayId = computed(() => {
    let id = authStore.user?.username || ''
    if (id && id.startsWith('TCH')) {
         return id.replace('TCH', 'STD')
    }
    return id
})

onMounted(() => {
  if (authStore.user) {
    form.value.name = authStore.user.name || ''
    form.value.email = authStore.user.email || ''
    form.value.phone = authStore.user.phone || ''
    form.value.whatsapp = authStore.user.whatsapp || ''
    form.value.role = authStore.user.role || 'student'
  }
})

const updateProfile = async () => {
  loading.value = true
  try {
    const payload = {
        name: form.value.name,
        phone: form.value.phone,
        whatsapp: form.value.whatsapp,
        ...(form.value.password ? { password: form.value.password, password_confirmation: form.value.password_confirmation } : {})
    }

    const res = await api.put('/user/profile', payload)
    authStore.updateUserInAccount(res.data.user)

    $q.notify({ type: 'positive', message: 'Profile Updated Successfully' })
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (error) {
    console.error(error)
    $q.notify({ type: 'negative', message: error.response?.data?.message || 'Update Failed' })
  } finally {
    loading.value = false
  }
}

const downloadIdCard = async () => {
    if (!idCardRef.value) return
    downloading.value = true
    try {
        const canvas = await html2canvas(idCardRef.value, {
            scale: 3,
            useCORS: true,
            backgroundColor: null
        })
        const link = document.createElement('a')
        link.download = `Student_ID_${displayId.value}.png`
        link.href = canvas.toDataURL('image/png')
        link.click()
        $q.notify({ type: 'positive', message: 'ID Card Downloaded' })
    } catch (e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Failed to download ID Card' })
    } finally {
        downloading.value = false
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap');

.rounded-borders { border-radius: 12px; }
</style>
