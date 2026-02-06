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

            <!-- New General Settings -->
            <div class="col-12"><q-separator class="q-my-md" /></div>
            <div class="col-12 text-subtitle2">Official Details</div>

            <div class="col-12 col-md-4">
              <q-input v-model="settings.websiteUrl" label="Website URL" outlined />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="settings.taxNumber" label="Tax / VAT Number" outlined />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="settings.establishedYear" label="Established Year" outlined type="number" />
            </div>

            <div class="col-12 col-md-6">
              <q-select v-model="settings.currency" :options="['LKR', 'USD', 'EUR', 'GBP']" label="Default Currency" outlined />
            </div>
            <div class="col-12 col-md-6">
              <q-select v-model="settings.timeZone" :options="['Asia/Colombo', 'UTC', 'America/New_York']" label="Time Zone" outlined />
            </div>

            <div class="col-12 text-subtitle2 q-mt-md">Academic Calendar & Working Hours</div>
            <div class="col-12 col-md-6">
               <q-input v-model="settings.academicYearStart" type="date" label="Academic Year Start" outlined stack-label />
            </div>
            <div class="col-12 col-md-6">
               <q-input v-model="settings.academicYearEnd" type="date" label="Academic Year End" outlined stack-label />
            </div>

            <div class="col-12 col-md-6">
               <q-input v-model="settings.workingHoursStart" type="time" label="Work Start Time" outlined stack-label />
            </div>
            <div class="col-12 col-md-6">
               <q-input v-model="settings.workingHoursEnd" type="time" label="Work End Time" outlined stack-label />
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
           </q-list>

           <!-- Teacher Financial Settings (New Section Below) -->
           <div class="q-mt-xl">
             <div class="text-h6 q-mb-md">Teacher Financial Settings</div>
             <div class="text-caption text-grey q-mb-md">Configure teacher settlement and fee deduction automation.</div>

             <q-card class="q-pa-md bg-blue-1">
               <div class="row q-col-gutter-lg">
                 <!-- Teacher Fee Deduction Percentage -->
                 <div class="col-12 col-md-6">
                   <q-input
                     v-model.number="settings.teacherFeeDeductionPercentage"
                     label="Teacher Fee Deduction Percentage"
                     hint="Percentage to deduct from fees for teacher settlement"
                     outlined
                     type="number"
                     min="0"
                     max="100"
                     suffix="%"
                   />
                 </div>

                 <!-- Automation Settlement Date -->
                 <div class="col-12 col-md-6">
                   <q-input
                     v-model.number="settings.automationSettlementDate"
                     label="Automatic Settlement Date (Day of Month)"
                     hint="Day of the month (1-31) when settlements will be processed automatically"
                     outlined
                     type="number"
                     min="1"
                     max="31"
                   />
                 </div>
               </div>
             </q-card>
           </div>

           <!-- NEW SECTIONS -->
           <div class="row q-col-gutter-lg q-mt-md">
                <!-- Enrollment Controls -->
                <div class="col-12 col-md-6">
                    <q-card class="full-height">
                        <q-card-section>
                            <div class="text-subtitle1 text-weight-bold">Student Enrollment</div>
                             <q-list dense>
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Max Students Per Class</q-item-label>
                                         <q-item-label caption>Limit class size</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.maxStudentsPerClass" type="number" dense outlined style="width: 80px" />
                                     </q-item-section>
                                 </q-item>
                                 <q-item tag="label" v-ripple>
                                     <q-item-section>
                                         <q-item-label>Allow Self-Registration</q-item-label>
                                     </q-item-section>
                                     <q-item-section side><q-toggle v-model="settings.selfRegistration" /></q-item-section>
                                 </q-item>
                                 <q-item tag="label" v-ripple>
                                     <q-item-section>
                                         <q-item-label>Auto-Approve Enrollment</q-item-label>
                                     </q-item-section>
                                     <q-item-section side><q-toggle v-model="settings.autoEnrollment" /></q-item-section>
                                 </q-item>
                             </q-list>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- Payment Controls -->
                <div class="col-12 col-md-6">
                    <q-card class="full-height">
                        <q-card-section>
                            <div class="text-subtitle1 text-weight-bold">Payment Settings</div>
                             <q-list dense>
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Late Fee Penalty (Amount)</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.lateFeePenalty" type="number" dense outlined style="width: 80px" />
                                     </q-item-section>
                                 </q-item>
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Grace Period (Days)</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.gracePeriodDays" type="number" dense outlined style="width: 80px" />
                                     </q-item-section>
                                 </q-item>
                                 <q-item tag="label" v-ripple>
                                     <q-item-section>
                                         <q-item-label>Enable Payment Gateway</q-item-label>
                                     </q-item-section>
                                     <q-item-section side><q-toggle v-model="settings.paymentGateway" /></q-item-section>
                                 </q-item>
                                 <q-item tag="label" v-ripple>
                                     <q-item-section>
                                         <q-item-label>Accept Partial Payments</q-item-label>
                                     </q-item-section>
                                     <q-item-section side><q-toggle v-model="settings.partialPayments" /></q-item-section>
                                 </q-item>
                             </q-list>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- Notification Controls -->
                <div class="col-12 col-md-6">
                    <q-card class="full-height">
                        <q-card-section>
                            <div class="text-subtitle1 text-weight-bold">Notifications</div>
                             <q-list dense>
                                 <q-item tag="label" v-ripple class="bg-grey-2">
                                     <q-item-section>
                                         <q-item-label class="text-weight-bold">Mobile App Notifications</q-item-label>
                                         <q-item-label caption>Master toggle for Student, Teacher & Parent Apps</q-item-label>
                                     </q-item-section>
                                     <q-item-section side><q-toggle color="purple" v-model="settings.appNotifications" /></q-item-section>
                                 </q-item>
                                 <q-separator spaced />
                                 <q-item tag="label" v-ripple>
                                     <q-item-section><q-item-label>Email Notifications</q-item-label></q-item-section>
                                     <q-item-section side><q-toggle v-model="settings.emailNotifications" /></q-item-section>
                                 </q-item>
                                 <q-item tag="label" v-ripple>
                                     <q-item-section><q-item-label>WhatsApp Notifications</q-item-label></q-item-section>
                                     <q-item-section side><q-toggle v-model="settings.whatsappNotifications" /></q-item-section>
                                 </q-item>
                             </q-list>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- Attendance Controls -->
                <div class="col-12 col-md-6">
                    <q-card class="full-height">
                        <q-card-section>
                            <div class="text-subtitle1 text-weight-bold">Attendance</div>
                             <q-list dense>
                                 <q-item tag="label" v-ripple class="bg-red-1">
                                     <q-item-section>
                                         <q-item-label class="text-red">Disable Teacher Attendance Marking</q-item-label>
                                         <q-item-label caption>Prevent teachers from saving attendance</q-item-label>
                                     </q-item-section>
                                     <q-item-section side><q-toggle color="red" v-model="settings.disableTeacherAttendance" /></q-item-section>
                                 </q-item>
                                 <q-separator spaced />
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Min Attendance %</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.minAttendancePercent" type="number" dense outlined style="width: 80px" suffix="%" />
                                     </q-item-section>
                                 </q-item>
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Auto-mark Absent (Minutes Late)</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.autoMarkAbsentMinutes" type="number" dense outlined style="width: 80px" />
                                     </q-item-section>
                                 </q-item>
                             </q-list>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- Maintenance -->
                <div class="col-12">
                    <q-card>
                        <q-card-section class="row items-center">
                             <div class="text-subtitle1 text-weight-bold q-mr-md">System Maintenance</div>
                             <div class="q-gutter-md row">
                                 <q-select v-model="settings.backupFrequency" :options="['daily', 'weekly', 'monthly']" label="Backup" dense outlined style="min-width: 150px" />
                                 <q-input v-model.number="settings.dataRetentionMonths" label="Data Retention (Months)" type="number" dense outlined style="width: 150px" />
                                 <q-toggle label="Maintenance Mode" color="red" v-model="settings.maintenanceMode" />
                             </div>
                        </q-card-section>
                    </q-card>
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

        <!-- Access Control / Security Tab -->
        <q-tab-panel name="security">
          <div class="text-h6 q-mb-md">System Security & Policies</div>

          <div class="row q-col-gutter-lg q-mb-xl">
               <!-- Password & Login Policy -->
               <div class="col-12 col-md-6">
                   <q-card bordered flat class="full-height">
                       <q-card-section>
                           <div class="text-subtitle2">Login Security</div>
                           <q-list dense>
                               <q-item>
                                   <q-item-section>
                                       <q-item-label>Min Password Length</q-item-label>
                                   </q-item-section>
                                   <q-item-section side>
                                       <q-input v-model.number="settings.passwordMinLength" type="number" dense outlined style="width: 80px" />
                                   </q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple>
                                   <q-item-section>
                                       <q-item-label>Require Strong Passwords</q-item-label>
                                       <q-item-label caption>Mixed case, numbers, symbols</q-item-label>
                                   </q-item-section>
                                   <q-item-section side><q-toggle v-model="settings.requireStrongPasswords" /></q-item-section>
                               </q-item>
                               <q-item>
                                   <q-item-section>
                                       <q-item-label>Max Login Attempts</q-item-label>
                                   </q-item-section>
                                   <q-item-section side>
                                       <q-input v-model.number="settings.maxLoginAttempts" type="number" dense outlined style="width: 80px" />
                                   </q-item-section>
                               </q-item>
                           </q-list>
                       </q-card-section>
                   </q-card>
               </div>

               <!-- Session Timeouts -->
               <div class="col-12 col-md-6">
                   <q-card bordered flat class="full-height">
                       <q-card-section>
                           <div class="text-subtitle2">Session Timeout (Minutes)</div>
                           <div class="text-caption text-grey q-mb-sm">0 = No Timeout</div>
                           <div class="row q-col-gutter-sm">
                               <div class="col-6">
                                   <q-input v-model.number="settings.sessionTimeoutAdmin" label="Admin" type="number" dense outlined />
                               </div>
                               <div class="col-6">
                                   <q-input v-model.number="settings.sessionTimeoutTeacher" label="Teacher" type="number" dense outlined />
                               </div>
                               <div class="col-6">
                                   <q-input v-model.number="settings.sessionTimeoutParent" label="Parent" type="number" dense outlined />
                               </div>
                               <div class="col-6">
                                   <q-input v-model.number="settings.sessionTimeoutStudent" label="Student" type="number" dense outlined />
                               </div>
                           </div>
                       </q-card-section>
                   </q-card>
               </div>

               <!-- Role Permissions -->
               <div class="col-12 col-md-6">
                   <q-card bordered flat class="full-height">
                       <q-card-section>
                           <div class="text-subtitle2">Role Permissions</div>
                           <q-list dense>
                               <q-item tag="label" v-ripple>
                                   <q-item-section><q-item-label>Admin Full Access</q-item-label></q-item-section>
                                   <q-item-section side><q-checkbox v-model="settings.permAdminFull" /></q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple>
                                   <q-item-section><q-item-label>Teacher Edit Access</q-item-label></q-item-section>
                                   <q-item-section side><q-checkbox v-model="settings.permTeacherEdit" /></q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple>
                                   <q-item-section><q-item-label>Parent View Access</q-item-label></q-item-section>
                                   <q-item-section side><q-checkbox v-model="settings.permParentView" /></q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple>
                                   <q-item-section><q-item-label>Student View Access</q-item-label></q-item-section>
                                   <q-item-section side><q-checkbox v-model="settings.permStudentView" /></q-item-section>
                               </q-item>
                           </q-list>
                       </q-card-section>
                   </q-card>
               </div>

               <!-- Data Privacy -->
               <div class="col-12 col-md-6">
                   <q-card bordered flat class="full-height bg-grey-1">
                       <q-card-section>
                           <div class="text-subtitle2">Data Privacy & Export</div>
                           <q-list dense>
                               <q-item tag="label" v-ripple>
                                   <q-item-section>
                                       <q-item-label>Allow Data Export</q-item-label>
                                   </q-item-section>
                                   <q-item-section side><q-toggle v-model="settings.allowDataExport" /></q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple class="text-red">
                                   <q-item-section>
                                       <q-item-label class="text-weight-bold">Disable Report Export</q-item-label>
                                       <q-item-label caption>Block users from downloading reports</q-item-label>
                                   </q-item-section>
                                   <q-item-section side><q-toggle color="red" v-model="settings.disableReportExport" /></q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple>
                                   <q-item-section><q-item-label>GDPR Compliance Mode</q-item-label></q-item-section>
                                   <q-item-section side><q-toggle v-model="settings.gdprMode" /></q-item-section>
                               </q-item>
                               <q-item tag="label" v-ripple>
                                   <q-item-section><q-item-label>Require User Consent</q-item-label></q-item-section>
                                   <q-item-section side><q-toggle v-model="settings.userConsent" /></q-item-section>
                               </q-item>
                           </q-list>
                       </q-card-section>
                   </q-card>
               </div>
          </div>

          <q-separator />

          <div class="text-h6 q-my-md">My Admin Profile</div>
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

             <div class="col-12 q-mt-lg row items-center q-gutter-md">
               <q-btn
                  color="primary"
                  label="Update Admin Profile"
                  icon="save"
                  :loading="loadingProfile"
                  @click="updateAdminProfile"
               />
               <q-separator vertical />
               <q-btn
                  color="negative"
                  label="Logout"
                  icon="logout"
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
  // Teacher Financial Settings
  teacherFeeDeductionPercentage: 10,
  automationSettlementDate: 10,

  // General - New
  instituteLogo: null,
  websiteUrl: '',
  establishedYear: '',
  taxNumber: '',
  currency: 'LKR',
  timeZone: 'Asia/Colombo',
  academicYearStart: '',
  academicYearEnd: '',
  workingDays: [],
  workingHoursStart: '08:00',
  workingHoursEnd: '17:00',

  // System Controls - New
  maxStudentsPerClass: 50,
  autoEnrollment: false,
  selfRegistration: true,

  lateFeePenalty: 0,
  gracePeriodDays: 5,
  paymentGateway: false,
  partialPayments: true,

  emailNotifications: true,
  whatsappNotifications: false,
  notificationFrequency: 'immediate',
  appNotifications: true, // Master toggle

  minAttendancePercent: 80,
  autoMarkAbsentMinutes: 30,
  attendanceReminderTime: '09:00',
  disableTeacherAttendance: false, // Feature limitation

  backupFrequency: 'daily',
  dataRetentionMonths: 12,

  // Access Control - New
  passwordMinLength: 8,
  requireStrongPasswords: false,
  maxLoginAttempts: 5,

  sessionTimeoutAdmin: 0,
  sessionTimeoutTeacher: 0,
  sessionTimeoutStudent: 0,
  sessionTimeoutParent: 0,

  permAdminFull: true,
  permTeacherEdit: true,
  permParentView: true,
  permStudentView: true,

  allowDataExport: true,
  gdprMode: false,
  dataAnonymization: false,
  userConsent: true,
  disableReportExport: false
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
