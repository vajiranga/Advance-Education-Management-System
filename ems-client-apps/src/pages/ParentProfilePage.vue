<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">My Profile</div>
    </div>

    <div class="row q-col-gutter-lg items-stretch">
      <!-- Left Column: Profile Card & Student Info -->
      <div class="col-12 col-md-4 column q-gutter-y-lg">
          <!-- Profile Summary -->
          <q-card class="col-auto text-center q-pa-lg shadow-2 rounded-borders" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
              <q-avatar size="100px" font-size="50px" :color="$q.dark.isActive ? 'deep-purple-9' : 'deep-purple'" text-color="white" class="shadow-3 q-mb-md">
                {{ form.name.charAt(0) }}
              </q-avatar>
              <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-grey-9'">{{ authStore.user?.name }}</div>
              <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">{{ authStore.user?.username || authStore.user?.email }}</div>
              <div class="text-caption text-uppercase q-mt-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">{{ form.role }}</div>
          </q-card>

          <!-- Student Information & ID Card -->
          <q-card v-if="selectedChild" class="col-auto shadow-2 rounded-borders overflow-hidden" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
             <q-card-section>
                 <div class="text-subtitle1 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : 'text-grey-9'">Student Information</div>

                 <!-- Read-Only Details -->
                 <q-list dense>
                    <q-item class="q-px-none">
                        <q-item-section>
                            <q-item-label caption>Student Name</q-item-label>
                            <q-item-label class="text-weight-bold">{{ selectedChild.name }}</q-item-label>
                        </q-item-section>
                    </q-item>
                    <q-item class="q-px-none">
                        <q-item-section>
                            <q-item-label caption>Student ID</q-item-label>
                            <q-item-label class="text-weight-bold">{{ displayId }}</q-item-label>
                        </q-item-section>
                    </q-item>
                    <q-item class="q-px-none">
                        <q-item-section>
                            <q-item-label caption>Grade/Batch</q-item-label>
                            <q-item-label class="text-weight-bold">{{ selectedChild.grade }}</q-item-label>
                        </q-item-section>
                    </q-item>
                 </q-list>

                 <q-separator class="q-my-md" />

                 <!-- Barcode Display -->
                 <div class="text-center">
                    <div class="text-caption text-grey q-mb-xs">Student ID Barcode</div>
                    <div class="q-pa-sm rounded-borders inline-block" :class="$q.dark.isActive ? 'bg-white' : 'bg-grey-2'">
                      <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 40px; line-height: 1; color: black !important;">
                        *{{ displayId }}*
                      </div>
                    </div>
                 </div>
             </q-card-section>

             <q-card-actions align="center" class="q-py-md">
                 <q-btn
                    icon="download"
                    label="Download Student ID"
                    color="primary"
                    unelevated
                    class="full-width"
                    @click="downloadIdCard"
                    :loading="downloading"
                 />
             </q-card-actions>
          </q-card>
      </div>

      <!-- Right Column: Edit Form -->
      <div class="col-12 col-md-8">
        <q-card class="full-height q-pa-lg shadow-2 rounded-borders" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
          <q-form @submit="updateProfile" class="column full-height">

            <!-- Personal Info -->
            <div class="text-h6 q-mb-md" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">
                <q-icon name="person_outline" size="sm" class="q-mr-sm" />Personal Information
            </div>

            <div class="row q-col-gutter-lg q-mb-lg">
                <div class="col-12 col-md-6">
                    <q-input
                        v-model="form.name"
                        label="Parent Full Name"
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
            <div class="text-h6 q-mb-md" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">
                <q-icon name="contact_phone" size="sm" class="q-mr-sm" />Contact Details
            </div>

            <div class="row q-col-gutter-lg q-mb-lg">
              <div class="col-12 col-md-6">
                 <q-input
                    v-model="form.phone"
                    label="Parent Account Phone"
                    hint="Used for login and admin contact"
                    outlined
                    dense
                    :dark="$q.dark.isActive"
                >
                   <template v-slot:prepend><q-icon name="phone_iphone" /></template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                 <q-input
                    :model-value="selectedChild?.phone"
                    label="Student Phone Number"
                    hint="Registered student contact"
                    outlined
                    dense
                    readonly
                    bg-color="transparent"
                    :dark="$q.dark.isActive"
                >
                   <template v-slot:prepend><q-icon name="phone" /></template>
                   <template v-slot:append><q-icon name="lock" size="xs" color="grey" /></template>
                </q-input>
              </div>
            </div>

            <q-space />

            <!-- Actions -->
            <div class="row justify-end q-mt-xl">
              <q-btn label="Update Profile" icon="save" type="submit" color="deep-purple" :loading="loading" class="q-px-xl q-py-xs" unelevated />
            </div>

          </q-form>
        </q-card>
      </div>
    </div>

    <!-- Hidden ID Card Template for Download (Dashboard Style) -->
    <div style="position: absolute; left: -9999px;">
        <div ref="idCardRef" id="student-id-card-dl" style="width: 400px; height: 280px; background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%); color: white; border-radius: 12px; padding: 20px; font-family: 'Roboto', sans-serif; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <!-- Decorative Circle -->
            <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>

            <div class="row items-center no-wrap full-height">
                 <!-- Photo Placeholder -->
                  <div style="width: 100px; height: 100px; background: white; border-radius: 8px; margin-right: 20px; display: flex; align-items: center; justify-content: center; color: #1976D2; font-weight: bold; font-size: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                       {{ selectedChild?.name?.charAt(0) || 'S' }}
                  </div>

                  <!-- Details -->
                  <div style="flex: 1; z-index: 1;">
                       <div style="font-size: 12px; opacity: 0.8; letter-spacing: 1px; margin-bottom: 4px;">EMS STUDENT IDENTITY</div>
                       <div style="font-size: 20px; font-weight: bold; margin-bottom: 4px; line-height: 1.2;">{{ selectedChild?.name || 'Student Name' }}</div>
                       <div style="font-size: 14px; opacity: 0.9; margin-bottom: 12px;">{{ displayId }}</div>

                       <div class="row q-col-gutter-xs">
                            <div class="col-6">
                                <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Grade</div>
                                <div style="font-size: 13px; font-weight: 500;">{{ selectedChild?.grade || 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Date of Birth</div>
                                <div style="font-size: 13px; font-weight: 500;">{{ selectedChild?.dob || 'N/A' }}</div>
                            </div>
                       </div>

                       <div class="row q-col-gutter-xs q-mt-xs">
                           <div class="col-6">
                               <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Phone</div>
                               <div style="font-size: 13px; font-weight: 500;">{{ selectedChild?.phone || 'N/A' }}</div>
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

const selectedChild = computed(() => authStore.selectedChild)

const form = ref({
  name: '',
  email: '',
  phone: '',
  whatsapp: '',
  role: 'parent'
})

// Display ID logic (same as Dashboard)
const displayId = computed(() => {
    let id = selectedChild.value?.username || ''
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
    form.value.role = authStore.user.role || 'parent'
  }
})

const updateProfile = async () => {
  loading.value = true
  try {
    const payload = {
        name: form.value.name,
        phone: form.value.phone,
        whatsapp: form.value.whatsapp
    }

    const res = await api.put('/user/profile', payload)
    authStore.updateUserInAccount(res.data.user)

    $q.notify({ type: 'positive', message: 'Profile Updated Successfully' })
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
        $q.notify({ type: 'positive', message: 'Student ID Card Downloaded' })
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
