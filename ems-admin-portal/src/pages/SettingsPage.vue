<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">System Settings</div>
      <!-- This button saves the Institute Settings (General, Branding, etc) -->
      <q-btn v-if="activeTab !== 'security' && canEditSettings" color="primary" label="Save System Settings" icon="save" @click="saveSettings" />
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
        <!-- Academic Tab Removed -->
        <q-tab name="security" icon="security" label="Access Control" />
      </q-tabs>

      <q-separator />

      <!-- Loading State -->
      <div v-if="loadingSettings" class="q-pa-xl text-center">
        <q-spinner-dots size="50px" color="primary" />
        <div class="q-mt-md text-grey-7">Loading settings...</div>
      </div>

      <q-tab-panels v-else v-model="activeTab" animated>
        <!-- General Tab -->
        <q-tab-panel name="general">
          <div class="text-h6 q-mb-md">Institute Profile</div>

          <!-- Logo Upload Section -->
          <div class="row q-col-gutter-md q-mb-lg">
            <div class="col-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle2 q-mb-md">Institute Logo</div>
                  <div class="row items-center q-col-gutter-md">
                    <!-- Logo Preview -->
                    <div class="col-12 col-md-3 text-center">
                      <div class="q-pa-md bg-grey-2 rounded-borders" style="min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <img
                          v-if="settings.logoUrl"
                          :src="settings.logoUrl"
                          alt="Institute Logo"
                          style="max-width: 100%; max-height: 150px; object-fit: contain;"
                        />
                        <q-icon v-else name="business" size="80px" color="grey-5" />
                      </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="col-12 col-md-9">
                      <div class="text-caption text-grey-7 q-mb-sm">
                        Upload your institute logo. This will be displayed in the header, reports, and certificates.
                      </div>
                      <div class="text-caption text-grey-7 q-mb-md">
                        Recommended: PNG or SVG format, transparent background, minimum 200x200px
                      </div>
                      <div class="row q-gutter-sm">
                        <q-btn
                          color="primary"
                          icon="upload"
                          label="Upload Logo"
                          @click="$refs.logoInput.click()"
                        />
                        <q-btn
                          v-if="settings.logoUrl"
                          flat
                          color="negative"
                          icon="delete"
                          label="Remove Logo"
                          @click="removeLogo"
                        />
                      </div>
                      <input
                        ref="logoInput"
                        type="file"
                        accept="image/*"
                        style="display: none"
                        @change="handleLogoUpload"
                      />
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

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
            <div class="col-12">
              <q-input v-model="settings.registrationNo" label="Institute ID" outlined readonly hint="Default System ID" />
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
                <q-select v-model="settings.currency" :options="['LKR']" label="Default Currency" outlined />
              </div>
              <div class="col-12 col-md-6">
                <q-select v-model="settings.timeZone" :options="['Asia/Colombo']" label="Time Zone" outlined />
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
          <!-- Mobile App Notifications -->
           <q-item tag="label" v-ripple>
            <q-item-section>
               <q-item-label>Mobile App Notifications</q-item-label>
               <q-item-label caption>Master toggle for Student, Teacher & Parent Apps. Turn ON to ENABLE notifications.</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-toggle color="purple" v-model="settings.enableAppNotifications" />
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

                                 <q-separator spaced />
                                 <q-item-label header class="text-weight-bold">ID Generation</q-item-label>

                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Starting Student ID Sequence</q-item-label>
                                         <q-item-label caption>Next ID will be: {{ settings.studentIdPrefix || 'STU' }}{{ settings.studentIdSequenceStart || '20000' }}</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.studentIdSequenceStart" type="number" dense outlined style="width: 100px" label="Ex: 20159" />
                                     </q-item-section>
                                 </q-item>
                                  <q-item>
                                     <q-item-section>
                                         <q-item-label>ID Prefix</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model="settings.studentIdPrefix" dense outlined style="width: 80px" label="Ex: STU" />
                                     </q-item-section>
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
                                  <q-item>
                                      <q-item-section>
                                          <q-item-label>Max Unpaid Before Drop (Months)</q-item-label>
                                          <q-item-label caption>Auto-drop student if fees unpaid for consecutive months</q-item-label>
                                      </q-item-section>
                                      <q-item-section side>
                                          <q-input v-model.number="settings.maxUnpaidMonthsBeforeDrop" type="number" dense outlined style="width: 80px" min="1" max="12" />
                                      </q-item-section>
                                  </q-item>
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Fee Cycle Start Day</q-item-label>
                                         <q-item-label caption>Day of month (1-31) that starts a billing cycle</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-input v-model.number="settings.feeCycleStartDay" type="number" dense outlined style="width: 80px" min="1" max="31" />
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
                                         <q-item-label>Auto-mark Absent</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <q-select
                                            v-model="settings.autoMarkAbsentMinutes"
                                            :options="['Default Absent']"
                                            dense
                                            outlined
                                            style="width: 150px"
                                         />
                                     </q-item-section>
                                 </q-item>
                                 <q-separator spaced />
                                 <q-item>
                                     <q-item-section>
                                         <q-item-label>Extra Class Visibility</q-item-label>
                                         <q-item-label caption>Time after end time to hide from list</q-item-label>
                                     </q-item-section>
                                     <q-item-section side>
                                         <div class="row no-wrap items-center q-gutter-x-sm">
                                             <q-input v-model.number="settings.extraClassVisibilityDays" type="number" dense outlined style="width: 70px" suffix="d" min="0" />
                                             <q-input v-model.number="settings.extraClassVisibilityHours" type="number" dense outlined style="width: 70px" suffix="h" min="0" />
                                         </div>
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



        <!-- Access Control / Security Tab -->
        <q-tab-panel name="security">
          <div class="text-h6 q-mb-md">System Security & Policies</div>

          <div class="row q-col-gutter-lg q-mb-xl">
               <!-- Administrator Management Table -->
               <div class="col-12">
                   <q-card bordered flat>
                       <q-card-section class="row items-center justify-between">
                           <div class="text-subtitle2">System Administrators</div>
                           <q-btn v-if="canEditSettings" label="Add New Admin" color="primary" size="sm" icon="add" @click="openAddAdmin" />
                       </q-card-section>
                       <q-separator />
                       <q-markup-table flat>
                           <thead>
                               <tr>
                                   <th class="text-left">Name</th>
                                   <th class="text-left">Email</th>
                                   <th class="text-left">Permissions</th>
                                   <th class="text-right">Actions</th>
                               </tr>
                           </thead>
                           <tbody>
                               <tr v-for="admin in adminList" :key="admin.id">
                                   <td class="text-left">{{ admin.name }} <q-badge v-if="admin.is_super_admin" color="purple" label="Super" /></td>
                                   <td class="text-left">{{ admin.email }}</td>
                                   <td class="text-left">
                                       <div class="row q-gutter-xs" style="max-width: 300px; overflow: hidden;">
                                            <q-badge v-for="p in admin.permissions" :key="p" color="grey-3" text-color="black" :label="p" />
                                       </div>
                                   </td>
                                   <td class="text-right">
                                       <q-btn v-if="canEditSettings" flat round icon="edit" color="blue" size="sm" @click="openEditAdmin(admin)" :disable="admin.is_super_admin" />
                                       <q-btn v-if="canEditSettings" flat round icon="delete" color="red" size="sm" @click="deleteAdmin(admin.id)" :disable="admin.is_super_admin" />
                                   </td>
                               </tr>
                               <tr v-if="adminList.length === 0">
                                   <td colspan="4" class="text-center text-grey">No other admins found.</td>
                               </tr>
                           </tbody>
                       </q-markup-table>
                   </q-card>
               </div>

    <!-- Verify Password Dialog -->
    <q-dialog v-model="showVerifyDialog" persistent>
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Verify Super Admin</div>
          <div class="text-caption">Please enter your password to proceed.</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input v-model="verifyPasswordInput" type="password" dense autofocus @keyup.enter="confirmVerifyPassword" label="Password" outlines />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Confirm" @click="confirmVerifyPassword" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Admin Form Dialog -->
    <q-dialog v-model="showAdminDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ editingAdminId ? 'Edit Admin' : 'Add New Admin' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none scroll" style="max-height: 70vh">
             <div class="row q-col-gutter-md">
                 <div class="col-12 col-md-6">
                     <q-input v-model="adminForm.name" label="Name" outlined dense />
                 </div>
                 <div class="col-12 col-md-6">
                     <q-input v-model="adminForm.email" label="Email" outlined dense />
                 </div>
                 <div class="col-12">
                     <q-input v-model="adminForm.password" :label="editingAdminId ? 'New Password (Leave blank to keep)' : 'Password'" type="text" outlined dense hint="Password is visible here for management" />
                 </div>

                 <div class="col-12"><q-separator class="q-my-sm" /></div>
                 <div class="col-12 text-weight-bold">Module Access</div>
                 <div class="col-12 text-caption text-negative">* Important: Please ensure 'Attendance' is selected.</div>

                 <!-- Granular Permissions -->
                 <div class="col-12">
                      <q-list bordered separator class="rounded-borders">
                          <!-- Dashboard -->
                          <q-expansion-item
                              expand-separator
                              icon="dashboard"
                              label="Dashboard"
                              :caption="getSelectedCount('dashboard')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="dashboard" label="View Dashboard" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="dashboard_broadcast" label="Send Broadcast" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="dashboard_export" label="Export Report" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Hall Management -->
                          <q-expansion-item
                              expand-separator
                              icon="meeting_room"
                              label="Hall Management"
                              :caption="getSelectedCount('halls')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="halls" label="View Halls" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="halls_add" label="Add New Hall" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Users -->
                          <q-expansion-item
                              expand-separator
                              icon="people"
                              label="Users"
                              :caption="getSelectedCount('users')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="users" label="View Users" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="users_add" label="Add New User" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Classes -->
                          <q-expansion-item
                              expand-separator
                              icon="school"
                              label="Classes"
                              :caption="getSelectedCount('classes')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="classes" label="View Classes" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="classes_add" label="Add New Class" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Attendance -->
                          <q-expansion-item
                              expand-separator
                              icon="fact_check"
                              label="Attendance"
                              :caption="getSelectedCount('attendance')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="attendance" label="View & Mark Attendance" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Cash Payment -->
                          <q-expansion-item
                              expand-separator
                              icon="payments"
                              label="Cash Payment"
                              :caption="getSelectedCount('payments')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="payments" label="View & Process Payments" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Finance -->
                          <q-expansion-item
                              expand-separator
                              icon="account_balance"
                              label="Finance"
                              :caption="getSelectedCount('finance')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="finance" label="View Finance" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="finance_pending" label="Pending Verification" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="finance_transactions" label="All Transactions" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="finance_uncollected" label="Uncollected Fees" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="finance_settlement" label="Teacher Settlement" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>

                          <!-- Settings -->
                          <q-expansion-item
                              expand-separator
                              icon="settings"
                              label="Settings"
                              :caption="getSelectedCount('settings')">
                              <q-card>
                                  <q-card-section>
                                      <q-checkbox v-model="adminForm.permissions" val="settings" label="View Settings" dense />
                                      <q-checkbox v-model="adminForm.permissions" val="settings_edit" label="Edit Settings" dense />
                                  </q-card-section>
                              </q-card>
                          </q-expansion-item>
                      </q-list>
                 </div>
             </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn color="primary" label="Save Admin" @click="saveAdmin" />
        </q-card-actions>
      </q-card>
    </q-dialog>



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
                  v-if="canEditSettings"
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
import { ref, onMounted, computed } from 'vue'

import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { useRouter } from 'vue-router'
import { useSettingsStore } from 'stores/settings-store'
import { useAuthStore } from 'stores/auth-store'

const $q = useQuasar()
const router = useRouter()
const settingsStore = useSettingsStore()
const authStore = useAuthStore()
const activeTab = ref('general')
const loadingProfile = ref(false)
const loadingSettings = ref(true) // Add loading state for settings

// Admin Management State
const adminList = ref([])
const showVerifyDialog = ref(false)
const showAdminDialog = ref(false)
const verifyPasswordInput = ref('')
const pendingAction = ref(null)
const editingAdminId = ref(null)

const adminForm = ref({
    name: '',
    email: '',
    password: '',
    permissions: []
})

// Helper function to count selected permissions for a module
const getSelectedCount = (modulePrefix) => {
    const count = adminForm.value.permissions.filter(p => p.startsWith(modulePrefix)).length
    return count > 0 ? `${count} selected` : 'None selected'
}

// Verify Super Admin Password Flow
const triggerSensitiveAction = (callback) => {
    // Ideally check if user is super admin first
    // For now, always ask password as requested
    verifyPasswordInput.value = ''
    pendingAction.value = callback
    showVerifyDialog.value = true
}

const confirmVerifyPassword = async () => {
    try {
        await api.post('/v1/admin/verify-password', { password: verifyPasswordInput.value })
        showVerifyDialog.value = false
        if (pendingAction.value) pendingAction.value()
    } catch (e) {
         console.error(e)
         $q.notify({ type: 'negative', message: 'Incorrect Password' })
    }
}

// CRUD
const fetchAdmins = async () => {
    try {
        const res = await api.get('/v1/admin/admins')
        adminList.value = res.data
    } catch (e) {
        console.error('Failed to fetch admins', e)
    }
}

const openAddAdmin = () => {
    triggerSensitiveAction(() => {
        editingAdminId.value = null
        adminForm.value = { name: '', email: '', password: '', permissions: [] }
        showAdminDialog.value = true
    })
}

const openEditAdmin = (admin) => {
    triggerSensitiveAction(() => {
        editingAdminId.value = admin.id
        adminForm.value = {
            name: admin.name,
            email: admin.email,
            password: admin.plain_password || '',
            permissions: admin.permissions || []
        }
        showAdminDialog.value = true
    })
}

const deleteAdmin = (id) => {
    triggerSensitiveAction(async () => {
        try {
             await api.delete(`/v1/admin/admins/${id}`)
             $q.notify({ type: 'positive', message: 'Admin deleted' })
             fetchAdmins()
        } catch (e) {
             $q.notify({ type: 'negative', message: e.response?.data?.message || 'Failed to delete' })
        }
    })
}

const saveAdmin = async () => {
    try {
        const payload = {
            name: adminForm.value.name,
            email: adminForm.value.email,
            password: adminForm.value.password,
            permissions: adminForm.value.permissions
        }

        if (editingAdminId.value) {
            await api.put(`/v1/admin/admins/${editingAdminId.value}`, payload)
        } else {
            await api.post('/v1/admin/admins', payload)
        }

        $q.notify({ type: 'positive', message: 'Admin saved successfully' })
        showAdminDialog.value = false
        fetchAdmins()

    } catch (e) {
        $q.notify({ type: 'negative', message: e.response?.data?.message || 'Failed to save' })
    }
}

// Institute Settings - Initialize with empty values, will be loaded from API
const settings = ref({
  instituteName: '',
  registrationNo: '#01',
  address: '',
  contactPhone: '',
  contactEmail: '',
  onlinePayments: false,
  smsAlerts: false,
  guestAccess: false,
  // System Controls
  blockTeacherRegistration: false,
  autoApproveClasses: false,
  autoApproveExtraClasses: false,
  // Teacher Financial Settings
  teacherFeeDeductionPercentage: 0,
  automationSettlementDate: 0,

  // General - New
  instituteLogo: null, // This is likely replaced by logoUrl
  logoUrl: null, // REQUIRED for API loop to pick it up
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
  studentIdSequenceStart: 20000,
  studentIdPrefix: 'STU',
  autoEnrollment: false,
  selfRegistration: true,

  lateFeePenalty: 0,
  gracePeriodDays: 5,
  feeCycleStartDay: 10,
  paymentGateway: false,
  partialPayments: true,



  minAttendancePercent: 80,
  autoMarkAbsentMinutes: 'Default Absent',
  attendanceReminderTime: '09:00',
  disableTeacherAttendance: false, // Feature limitation
  extraClassVisibilityDays: 2,
  extraClassVisibilityHours: 0,
  extraClassVisibilityTimeout: 48,

  backupFrequency: 'daily',
  dataRetentionMonths: 12,
  maintenanceMode: false,

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

// Logo Upload Functions
const logoInput = ref(null)

const handleLogoUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    $q.notify({ type: 'negative', message: 'Please select an image file' })
    return
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    $q.notify({ type: 'negative', message: 'Image size must be less than 2MB' })
    return
  }

  try {
    const formData = new FormData()
    formData.append('logo', file)

    const res = await api.post('/v1/admin/settings/upload-logo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data && res.data.logoUrl) {
       let fixedUrl = res.data.logoUrl
       // Fix localhost URL issues or relative path - FORCE 127.0.0.1:8000
       if (fixedUrl.includes('localhost') || fixedUrl.includes('127.0.0.1')) {
            // Replace any localhost/127 variants with clean 127.0.0.1:8000
            // First remove protocol if exists to simplify
            let cleanPath = fixedUrl.replace(/^https?:\/\/[^/]+/, '')
            // If it was just a domain, cleanPath might be empty? No, usually creates path.
            if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath
            fixedUrl = 'http://127.0.0.1:8000' + cleanPath
       } else if (fixedUrl.startsWith('/')) {
            fixedUrl = 'http://127.0.0.1:8000' + fixedUrl
       }

      settings.value.logoUrl = fixedUrl

      // Update settings store to show logo immediately
      settingsStore.logoUrl = fixedUrl
      localStorage.setItem('logoUrl', fixedUrl)

      $q.notify({ type: 'positive', message: 'Logo uploaded successfully' })
    }
  } catch (error) {
    console.error('Logo upload error:', error)
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to upload logo'
    })
  }
}

const removeLogo = async () => {
  try {
    await api.delete('/v1/admin/settings/remove-logo')
    settings.value.logoUrl = ''

    // Update settings store to remove logo immediately
    settingsStore.logoUrl = ''
    localStorage.removeItem('logoUrl')

    $q.notify({ type: 'positive', message: 'Logo removed successfully' })
  } catch (error) {
    console.error('Logo removal error:', error)
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to remove logo'
    })
  }
}

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
             // Force default #01 if empty
             if (!settings.value.registrationNo) settings.value.registrationNo = '#01'

             // Fix Logo URL on load - FORCE 127.0.0.1:8000
             if (settings.value.logoUrl) {
                let url = settings.value.logoUrl
                if (url.includes('localhost') || url.includes('127.0.0.1')) {
                    let cleanPath = url.replace(/^https?:\/\/[^/]+/, '')
                    if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath
                    settings.value.logoUrl = 'http://127.0.0.1:8000' + cleanPath
                } else if (url.startsWith('/')) {
                     settings.value.logoUrl = 'http://127.0.0.1:8000' + url
                }
             }
         }
         // Calculate days/hours from timeout
         if (settings.value.extraClassVisibilityTimeout !== undefined) {
             const total = parseInt(settings.value.extraClassVisibilityTimeout)
             settings.value.extraClassVisibilityDays = Math.floor(total / 24)
             settings.value.extraClassVisibilityHours = total % 24
         }
    } catch (e) {
        console.error('Failed to fetch settings', e)
    } finally {
        loadingSettings.value = false // Always set loading to false after attempt
    }

    // 3. Fetch Admins (Background)
    fetchAdmins()
})

// Save Institute Settings
const saveSettings = async () => {
  $q.loading.show({ message: 'Saving Settings...' })
  try {
      // Calculate timeout
      settings.value.extraClassVisibilityTimeout = (settings.value.extraClassVisibilityDays || 0) * 24 + (settings.value.extraClassVisibilityHours || 0)

      await api.post('/v1/admin/settings', settings.value)

      // Update the settings store to reflect changes immediately
      if (settings.value.instituteName) {
          settingsStore.instituteName = settings.value.instituteName
          localStorage.setItem('instituteName', settings.value.instituteName)
      }
      if (settings.value.appName) {
          settingsStore.appName = settings.value.appName
          localStorage.setItem('appName', settings.value.appName)
      }
      if (settings.value.instituteLogo) {
          settingsStore.instituteLogo = settings.value.instituteLogo
          localStorage.setItem('instituteLogo', settings.value.instituteLogo)
      }

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


const canEditSettings = computed(() => {
    const user = authStore.user
    if (!user) return false
    if (user.is_super_admin) return true
    return (user.permissions || []).includes('settings_edit')
})
</script>
