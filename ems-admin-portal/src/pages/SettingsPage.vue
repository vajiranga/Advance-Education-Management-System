<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">System Settings</div>
      <!-- This button saves the Institute Settings (General, Branding, etc) -->
      <q-btn v-if="activeTab !== 'security'" color="primary" label="Save System Settings" icon="save" @click="saveSettings" />
    </div>

    <!-- Main Settings Tabs -->
    <q-card>
      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="general" icon="business" label="General" />
        <q-tab name="controls" icon="tune" label="System Controls" />
        <q-tab name="academic" icon="school" label="Academic" />
        <q-tab name="security" icon="security" label="Access Control" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated>
        <!-- General Tab -->
        <q-tab-panel name="general">
          <div class="text-h6 q-mb-md">Institute Profile</div>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input v-model="settings.instituteName" label="Institute Name" outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="settings.registrationNo" label="Registration Number" outlined />
            </div>
            <div class="col-12">
              <q-input v-model="settings.address" label="Address" type="textarea" outlined rows="3" />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="settings.contactPhone" label="Contact Phone" outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-input v-model="settings.contactEmail" label="Contact Email" outlined />
            </div>
          </div>
        </q-tab-panel>

        <!-- System Controls Tab (Formerly Branding) -->
        <q-tab-panel name="controls">
           <div class="text-h6 q-mb-md">Platform Configurations</div>
           <div class="text-caption text-grey q-mb-lg">Manage global restrictions and automation settings.</div>

           <q-list bordered separator>
             <!-- Teacher Registration -->
             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>Block New Teacher Registration</q-item-label>
                 <q-item-label caption>Prevent new teachers from signing up. Existing teachers can still login.</q-item-label>
               </q-item-section>
               <q-item-section side>
                 <q-toggle color="red" v-model="settings.blockTeacherRegistration" />
               </q-item-section>
             </q-item>

             <!-- Auto Approve Classes -->
             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>Auto-Approve New Classes</q-item-label>
                 <q-item-label caption>Automatically approve classes created by teachers without admin review.</q-item-label>
               </q-item-section>
               <q-item-section side>
                 <q-toggle color="green" v-model="settings.autoApproveClasses" />
               </q-item-section>
             </q-item>

             <!-- Auto Approve Extra Classes -->
             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>Auto-Approve Extra Classes</q-item-label>
                 <q-item-label caption>Automatically approve extra/revision classes created by teachers.</q-item-label>
               </q-item-section>
               <q-item-section side>
                 <q-toggle color="green" v-model="settings.autoApproveExtraClasses" />
               </q-item-section>
             </q-item>

             <!-- Parent Login Access -->
             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>Allow Parent Portal Access</q-item-label>
                 <q-item-label caption>Enable or disable login access for all parents.</q-item-label>
               </q-item-section>
               <q-item-section side>
                 <q-toggle color="primary" v-model="settings.allowParentLogin" />
               </q-item-section>
             </q-item>

             <!-- Maintenance Mode -->
             <q-item tag="label" v-ripple class="bg-red-1">
               <q-item-section>
                 <q-item-label class="text-red">Maintenance Mode</q-item-label>
                 <q-item-label caption>Take the system offline for non-admin users.</q-item-label>
               </q-item-section>
               <q-item-section side>
                 <q-toggle color="red" icon="build" v-model="settings.maintenanceMode" />
               </q-item-section>
             </q-item>
           </q-list>
        </q-tab-panel>

        <!-- Academic Tab -->
        <q-tab-panel name="academic">
          <div class="text-h6 q-mb-md">Academic Configuration</div>
           <q-list bordered separator>
             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>Enable Online Payments</q-item-label>
                 <q-item-label caption>Allow students to pay fees via stripe/card</q-item-label>
               </q-item-section>
               <q-item-section side >
                 <q-toggle color="green" v-model="settings.onlinePayments" />
               </q-item-section>
             </q-item>

             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>SMS Notifications</q-item-label>
                 <q-item-label caption>Send SMS to parents when student is absent</q-item-label>
               </q-item-section>
               <q-item-section side >
                 <q-toggle color="green" v-model="settings.smsAlerts" />
               </q-item-section>
             </q-item>

             <q-item tag="label" v-ripple>
               <q-item-section>
                 <q-item-label>Guest Access</q-item-label>
                 <q-item-label caption>Allow unregistered users to view course catalog</q-item-label>
               </q-item-section>
               <q-item-section side >
                 <q-toggle color="green" v-model="settings.guestAccess" />
               </q-item-section>
             </q-item>
           </q-list>
        </q-tab-panel>

        <!-- Admin Profile Tab (Security) -->
        <q-tab-panel name="security">
          <div class="text-h6 q-mb-md">Access Control</div>
          <div class="text-caption q-mb-md text-grey">Update your login credentials here.</div>

          <div class="row q-col-gutter-md">
             <!-- Name & Email -->
             <div class="col-12 col-md-6">
               <q-input v-model="adminProfile.name" label="Admin Name" outlined />
             </div>
             <div class="col-12 col-md-6">
               <q-input v-model="adminProfile.email" label="Login Admin Email" outlined hint="This email is used for login" />
             </div>

             <div class="col-12"><q-separator class="q-my-sm" /></div>

             <!-- Password Change -->
             <div class="col-12">
               <q-input
                  v-model="adminProfile.current_password"
                  type="password"
                  label="Current Password"
                  outlined
               />
             </div>

             <div class="col-12 col-md-6">
               <q-input
                  v-model="adminProfile.password"
                  type="password"
                  label="New Password"
                  outlined
               />
             </div>
             <div class="col-12 col-md-6">
               <q-input
                  v-model="adminProfile.password_confirmation"
                  type="password"
                  label="Confirm New Password"
                  outlined
                  :disable="!adminProfile.password"
               />
             </div>

             <div class="col-12 q-mt-md row items-center q-gutter-md">
               <q-btn
                  color="primary"
                  label="Update Admin Profile"
                  :loading="loadingProfile"
                  @click="updateAdminProfile"
               />
               <q-btn
                  color="negative"
                  label="Logout"
                  icon="logout"
                  flat
                  @click="logout"
               />
             </div>
          </div>
        </q-tab-panel>

      </q-tab-panels>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { useRouter } from 'vue-router'

const $q = useQuasar()
const router = useRouter()
const activeTab = ref('general')
const loadingProfile = ref(false)

// Institute Settings (Mock Data / To be connected to backend later if needed)
const settings = ref({
  instituteName: 'Royal College of Education',
  registrationNo: 'REG-2026-001',
  address: 'No 123, Main Street, Colombo',
  contactPhone: '+94 11 234 5678',
  contactEmail: 'admin@royalcollege.lk',
  onlinePayments: true,
  smsAlerts: false,
  guestAccess: true,
  // System Controls
  blockTeacherRegistration: false,
  autoApproveClasses: false,
  autoApproveExtraClasses: false,
  allowParentLogin: true,
  maintenanceMode: false
})

// Admin Profile State
const adminProfile = ref({
    name: '',
    email: '',
    current_password: '',
    password: '',
    password_confirmation: ''
})

// Fetch Data
onMounted(async () => {
    // 1. Fetch User Profile
    try {
        const response = await api.get('/user')
        if (response.data) {
            adminProfile.value.name = response.data.name
            adminProfile.value.email = response.data.email
        }
    } catch (error) {
        console.error('Failed to fetch user', error)
        $q.notify({ type: 'negative', message: 'Failed to load user profile' })
    }

    // 2. Fetch System Settings
    try {
        const res = await api.get('/v1/admin/settings')
        const data = res.data
        if (data) {
             Object.keys(settings.value).forEach(key => {
                 if (data[key] !== undefined) {
                     if (typeof settings.value[key] === 'boolean') {
                         settings.value[key] = (data[key] === '1' || data[key] === 'true' || data[key] === true)
                     } else {
                         settings.value[key] = data[key]
                     }
                 }
             })
        }
    } catch (e) {
        console.error('Failed to fetch settings', e)
    }
})

// Save Institute Settings
const saveSettings = async () => {
  $q.loading.show({ message: 'Saving Settings...' })
  try {
      await api.post('/v1/admin/settings', settings.value)
      $q.notify({ type: 'positive', message: 'Settings saved successfully!' })
  } catch (e) {
      console.error(e)
      $q.notify({ type: 'negative', message: 'Failed to save settings' })
  } finally {
      $q.loading.hide()
  }
}

// Update Admin Profile (Real API)
const updateAdminProfile = async () => {
    if (adminProfile.value.password && adminProfile.value.password !== adminProfile.value.password_confirmation) {
        $q.notify({ type: 'warning', message: 'Passwords do not match' })
        return
    }

    loadingProfile.value = true
    try {
        const payload = {
            name: adminProfile.value.name,
            email: adminProfile.value.email,
            current_password: adminProfile.value.current_password
        }
        if (adminProfile.value.password) {
            payload.password = adminProfile.value.password
            payload.password_confirmation = adminProfile.value.password_confirmation
        }

        await api.put('/user/profile', payload)

        $q.notify({ type: 'positive', message: 'Profile Updated Successfully' })

        // Clear password fields on success
        adminProfile.value.password = ''
        adminProfile.value.password_confirmation = ''

    } catch (error) {
        console.error('Update failed', error)
        let msg = 'Update Failed'
        if (error.response && error.response.data && error.response.data.errors) {
            // Join errors if available
            msg = Object.values(error.response.data.errors).flat().join(', ')
        } else if (error.response && error.response.data.message) {
            msg = error.response.data.message
        }
        $q.notify({ type: 'negative', message: msg })
    } finally {
        loadingProfile.value = false
    }
}


// ... existing code ...

const logout = async () => {
    $q.dialog({
        title: 'Confirm Logout',
        message: 'Are you sure you want to log out?',
        cancel: true,
        persistent: true
    }).onOk(async () => {
         try {
             // Optional: Call logout endpoint if exists
             // await api.post('/logout')
         } catch (e) {
             console.error('Logout error', e)
         } finally {
             // Clear any stored tokens
             localStorage.removeItem('token')
             localStorage.removeItem('user') // clear user data if any

             $q.notify({ type: 'info', message: 'Logged out successfully' })

             // Immediate redirect to login, replace history so user cannot go back
             router.replace('/login')
         }
    })
}


</script>
