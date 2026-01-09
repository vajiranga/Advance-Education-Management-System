<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">System Settings</div>
      <q-btn color="primary" label="Save All Changes" icon="save" @click="saveSettings" />
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
        <q-tab name="branding" icon="palette" label="Branding" />
        <q-tab name="academic" icon="school" label="Academic" />
        <q-tab name="security" icon="security" label="Security" />
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

        <!-- Branding Tab -->
        <q-tab-panel name="branding">
           <div class="text-h6 q-mb-md">Look & Feel</div>
           <div class="row q-col-gutter-md items-center">
             <div class="col-12 col-md-4 text-center">
                <q-avatar size="100px" font-size="52px" color="primary" text-color="white" icon="school" />
                <div class="q-mt-sm">
                  <q-btn size="sm" color="grey-8" label="Change Logo" icon="cloud_upload" />
                </div>
             </div>
             <div class="col-12 col-md-8">
                <div class="text-subtitle1">Theme Color</div>
                <div class="row q-gutter-sm q-mt-sm">
                  <q-btn round color="primary" @click="setColor('primary')" icon="check" />
                  <q-btn round color="secondary" @click="setColor('secondary')" />
                  <q-btn round color="purple" @click="setColor('purple')" />
                  <q-btn round color="orange" @click="setColor('orange')" />
                  <q-btn round color="brown" @click="setColor('brown')" />
                </div>
                <div class="q-mt-md">
                   <q-toggle v-model="settings.darkMode" label="Enable Dark Mode" />
                </div>
             </div>
           </div>
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

        <!-- Security Tab -->
        <q-tab-panel name="security">
          <div class="text-h6 q-mb-md">Access Control</div>
          <div class="row q-col-gutter-md">
             <div class="col-12 col-md-6">
               <q-input type="password" label="Current Password" outlined />
             </div>
             <div class="col-12"></div>
             <div class="col-12 col-md-6">
               <q-input type="password" label="New Password" outlined />
             </div>
             <div class="col-12 col-md-6">
               <q-input type="password" label="Confirm New Password" outlined />
             </div>
             <div class="col-12">
               <q-btn color="red" label="Update Password" outline />
             </div>
          </div>
        </q-tab-panel>

      </q-tab-panels>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const activeTab = ref('general')

const settings = ref({
  instituteName: 'Royal College of Education',
  registrationNo: 'REG-2026-001',
  address: 'No 123, Main Street, Colombo',
  contactPhone: '+94 11 234 5678',
  contactEmail: 'admin@royalcollege.lk',
  darkMode: false,
  onlinePayments: true,
  smsAlerts: false,
  guestAccess: true
})

const saveSettings = () => {
  // Simulate API call
  $q.loading.show({ message: 'Saving Settings...' })
  setTimeout(() => {
    $q.loading.hide()
    $q.notify({ type: 'positive', message: 'Settings saved successfully!' })
  }, 1000)
}

const setColor = (color) => {
   // Just demo feedback
   $q.notify({ message: `Theme color updated to ${color}`, color: color })
}
</script>
