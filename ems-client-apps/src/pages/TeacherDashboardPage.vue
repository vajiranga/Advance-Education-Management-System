<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <!-- Welcome Section -->
    <!-- Profile & ID Section -->
    <q-card class="q-mb-lg overflow-hidden" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'" flat bordered>
       <q-card-section>
         <div class="row items-center q-col-gutter-md">
           <!-- Profile Info -->
             <div class="col-12 col-md-8">
               <div class="row items-center">
                 <!-- Avatar Removed -->
                 <div>
                   <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-teal-2' : 'text-teal-9'">{{ authStore.user?.name || 'Teacher' }}</div>
                 <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">{{ displayTeacherId }}</div>
                 <div class="row q-gutter-x-sm q-mt-xs">
                     <q-chip size="sm" :color="$q.dark.isActive ? 'teal-9' : 'teal-1'" :text-color="$q.dark.isActive ? 'teal-1' : 'teal'" icon="verified_user" label="Verified Teacher" />
                     <q-chip size="sm" :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-1' : 'blue'" icon="email" :label="authStore.user?.email" />
                 </div>
               </div>
             </div>
           </div>
           
           <!-- Barcode Section -->
           <div class="col-12 col-md-4 text-center">
              <div class="q-pa-md rounded-borders inline-block" :class="$q.dark.isActive ? 'bg-white' : 'bg-grey-2'">
                <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 48px; line-height: 1; color: black !important;">
                  *{{ displayTeacherId }}*
                </div>
              </div>
              <div class="text-caption q-mt-sm" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">Teacher ID Barcode</div>
           </div>
         </div>
       </q-card-section>
    </q-card>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="no-shadow border-bottom-teal" :class="$q.dark.isActive ? 'bg-dark text-teal-2' : 'bg-white text-teal'">
          <q-card-section>
             <div class="row items-center justify-between">
                <div>
                   <div class="text-h4 text-weight-bold">{{ todayClassesCount }}</div>
                   <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Today's Classes</div>
                </div>
                <q-avatar :color="$q.dark.isActive ? 'teal-9' : 'teal-1'" :text-color="$q.dark.isActive ? 'teal-2' : 'teal'" icon="schedule" font-size="24px" />
             </div>
          </q-card-section>
        </q-card>
      </div>
       <div class="col-12 col-sm-6 col-md-3">
        <q-card class="no-shadow border-bottom-orange" :class="$q.dark.isActive ? 'bg-dark text-orange-2' : 'bg-white text-orange'">
          <q-card-section>
             <div class="row items-center justify-between">
                <div>
                   <div class="text-h4 text-weight-bold">{{ totalStudentsCount }}</div>
                   <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Total Students</div>
                </div>
                <q-avatar :color="$q.dark.isActive ? 'orange-9' : 'orange-1'" :text-color="$q.dark.isActive ? 'orange-2' : 'orange'" icon="groups" font-size="24px" />
             </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-lg">
       <!-- Schedule -->
       <div class="col-12 col-md-8">
          <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
             <q-card-section class="row items-center justify-between">
                <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Today's Schedule</div>
                <q-chip outline color="teal" label="Today" icon="calendar_today" />
             </q-card-section>
             <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
             <q-list separator>
                <q-item v-for="cls in schedule" :key="cls.id" class="q-py-md">
                   <q-item-section avatar>
                      <q-avatar :color="$q.dark.isActive ? 'grey-8' : 'grey-2'" :text-color="$q.dark.isActive ? 'teal-2' : 'teal'" class="text-weight-bold">{{ cls.time.split(' ')[0] }}</q-avatar>
                   </q-item-section>
                   <q-item-section>
                      <q-item-label class="text-weight-bold text-subtitle1" :class="$q.dark.isActive ? 'text-white' : ''">{{ cls.subject }} - {{ cls.batch }}</q-item-label>
                      <q-item-label caption class="row items-center" :class="$q.dark.isActive ? 'text-grey-4' : ''">
                          <q-icon name="meeting_room" class="q-mr-xs" /> 
                          {{ cls.hallName }}
                          <span v-if="cls.hallFloor" class="q-ml-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'"> (Floor: {{ cls.hallFloor }})</span>
                          <q-icon v-if="cls.hallAC" name="ac_unit" color="blue" size="xs" class="q-ml-sm" />
                      </q-item-label>
                   </q-item-section>
                   <q-item-section side>
                       <div class="row items-center q-gutter-sm">
                           <q-btn flat round icon="groups" color="primary" @click="viewStudents(cls.id)">
                               <q-tooltip>View Students</q-tooltip>
                           </q-btn>
                           <q-btn v-if="cls.status === 'upcoming'" md color="teal" label="Start Class" icon="play_arrow" unelevated />
                           <q-chip v-else :color="$q.dark.isActive ? 'green-9' : 'green-1'" :text-color="$q.dark.isActive ? 'green-2' : 'green'" label="Completed" />
                       </div>
                   </q-item-section>
                </q-item>
             </q-list>
          </q-card>
       
       <!-- My Extra Classes -->
       <div v-if="(upcomingExtraCourses || []).length > 0" class="q-mt-lg">
           <div class="text-h6 q-mb-sm" :class="$q.dark.isActive ? 'text-teal-2' : 'text-teal'">Upcoming Extra Classes</div>
           <q-list bordered separator class="rounded-borders" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
               <q-item v-for="cls in upcomingExtraCourses" :key="cls.id">
                   <q-item-section avatar>
                       <q-avatar :color="$q.dark.isActive ? 'teal-9' : 'teal-1'" :text-color="$q.dark.isActive ? 'teal-2' : 'teal'" icon="event" />
                   </q-item-section>
                   <q-item-section>
                       <q-item-label class="text-weight-bold text-h6" :class="$q.dark.isActive ? 'text-white' : 'text-teal-9'">{{ cls.name }}</q-item-label>
                       
                        <div class="row items-center q-gutter-x-sm q-mt-xs">
                           <q-badge :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-2' : 'blue'">
                               {{ cls.subject?.name || 'Subject' }} - {{ cls.batch?.name || 'Grade' }}
                           </q-badge>
                           <q-badge v-if="cls.status === 'approved'" color="green" label="APPROVED" />
                           <q-badge v-else color="orange" label="PENDING" />
                       </div>

                       <div class="q-mt-sm text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                           <q-icon name="meeting_room" class="q-mr-xs" /> {{ cls.hall?.name || 'Hall TBA' }}
                       </div>

                       <div class="text-caption q-mt-none" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
                            <q-icon name="event" class="q-mr-xs" /> 
                            {{ typeof cls.schedule === 'string' ? JSON.parse(cls.schedule).date : cls.schedule.date }}
                            <span class="q-mx-xs">|</span>
                            <q-icon name="schedule" class="q-mr-xs" />
                            {{ typeof cls.schedule === 'string' ? JSON.parse(cls.schedule).start : cls.schedule.start }}
                             - 
                            {{ typeof cls.schedule === 'string' ? JSON.parse(cls.schedule).end : cls.schedule.end }}
                       </div>

                       <div class="q-mt-xs text-weight-bold" :class="$q.dark.isActive ? 'text-green-3' : 'text-green-7'">
                            Fee: LKR {{ cls.fee_amount }}
                       </div>
                   </q-item-section>
                   <q-item-section side>
                       <div class="row items-center q-gutter-xs">
                           <q-btn flat round dense icon="more_vert" :color="$q.dark.isActive ? 'grey-4' : 'grey-7'">
                               <q-menu>
                                   <q-list style="min-width: 100px">
                                       <q-item clickable v-close-popup @click="viewStudents(cls.id)">
                                           <q-item-section>View Students</q-item-section>
                                       </q-item>
                                       <q-item clickable v-close-popup @click="openEditClassDialog(cls)">
                                           <q-item-section>Edit</q-item-section>
                                       </q-item>
                                       <q-item clickable v-close-popup @click="deleteClassHandler(cls)" class="text-negative">
                                           <q-item-section>Delete</q-item-section>
                                       </q-item>
                                   </q-list>
                               </q-menu>
                           </q-btn>
                       </div>
                   </q-item-section>
               </q-item>
           </q-list>
       </div>

       <div v-if="(pastExtraCourses || []).length > 0" class="q-mt-md">
           <div class="text-h6 q-mb-sm text-grey">Past Extra Classes</div>
           <q-list bordered separator class="rounded-borders" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
               <q-item v-for="cls in pastExtraCourses" :key="cls.id" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-1'">
                   <q-item-section avatar>
                       <q-avatar :color="$q.dark.isActive ? 'grey-8' : 'grey-3'" :text-color="$q.dark.isActive ? 'grey-4' : 'grey-7'" icon="history" />
                   </q-item-section>
                   <q-item-section>
                       <q-item-label :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ cls.name }}</q-item-label>
                       <q-item-label caption :class="$q.dark.isActive ? 'text-grey-5' : ''">
                           {{ typeof cls.schedule === 'string' ? JSON.parse(cls.schedule).date : cls.schedule.date }}
                       </q-item-label>
                   </q-item-section>
                   <q-item-section side>
                       <div class="row items-center q-gutter-xs">
                           <q-chip size="sm" :color="$q.dark.isActive ? 'grey-8' : 'grey'" text-color="white" label="Ended" />
                           <q-btn flat round dense icon="more_vert" :color="$q.dark.isActive ? 'grey-4' : 'grey-7'">
                               <q-menu>
                                   <q-list style="min-width: 100px">
                                       <q-item clickable v-close-popup @click="viewStudents(cls.id)">
                                           <q-item-section>View Students</q-item-section>
                                       </q-item>
                                       <q-item clickable v-close-popup @click="openEditClassDialog(cls)">
                                           <q-item-section>Edit</q-item-section>
                                       </q-item>
                                       <q-item clickable v-close-popup @click="deleteClassHandler(cls)" class="text-negative">
                                           <q-item-section>Delete</q-item-section>
                                       </q-item>
                                   </q-list>
                               </q-menu>
                           </q-btn>
                       </div>
                   </q-item-section>
               </q-item>
           </q-list>
       </div>
    </div>

       <!-- Quick Actions -->
       <div class="col-12 col-md-4">
          <q-card class="no-shadow q-mb-md" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
             <q-card-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">Quick Actions</div>
                <div class="row q-col-gutter-sm">
                   <div class="col-6">
                      <!-- New Class Button -->
                      <q-btn outline class="full-width" color="teal" icon="add" label="Add Extra Class" stack @click="openAddClassDialog" />
                   </div>
                   <div class="col-6">
                      <q-btn outline class="full-width" color="purple" icon="campaign" label="Parent Meeting" stack @click="openMeetingDialog" />
                   </div>
                   <!-- ... -->
                </div>
             </q-card-section>
          </q-card>
       </div>
    </div>

    <!-- Parent Meeting Dialog -->
    <q-dialog v-model="showMeetingDialog">
        <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
             <q-card-section>
                 <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Call Parent Meeting / Notice</div>
                 <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Notify parents about a meeting or important update.</div>
             </q-card-section>
             
             <q-card-section class="q-gutter-md">
                  <q-input v-model="meetingForm.title" label="Title (e.g. Term End Meeting)" outlined :rules="[val => !!val || 'Required']" :dark="$q.dark.isActive" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" />
                  
                  <q-input v-model="meetingForm.message" label="Message Details" type="textarea" outlined :rules="[val => !!val || 'Required']" :dark="$q.dark.isActive" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" />

                  <div class="row items-center justify-between q-pa-sm rounded-borders" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-2'">
                      <div :class="$q.dark.isActive ? 'text-white' : ''">Send to All My Classes?</div>
                      <q-toggle v-model="meetingForm.sendToAll" color="purple" />
                  </div>

                  <q-slide-transition>
                      <div v-if="!meetingForm.sendToAll">
                           <q-select 
                                v-model="meetingForm.courseId"
                                :options="activeRegularCourses"
                                option-label="displayName"
                                option-value="id"
                                label="Select Specific Class"
                                outlined
                                emit-value
                                map-options
                                :dark="$q.dark.isActive"
                                :bg-color="$q.dark.isActive ? 'grey-9' : 'white'"
                           />
                      </div>
                  </q-slide-transition>
             </q-card-section>

             <q-card-actions align="right">
                 <q-btn flat label="Cancel" v-close-popup :color="$q.dark.isActive ? 'grey-4' : 'primary'" />
                 <q-btn color="purple" label="Send Notice" @click="sendNotice" :loading="sendingNotice" />
             </q-card-actions>
        </q-card>
    </q-dialog>

    <!-- Add Extra Class Dialog -->
    <q-dialog v-model="showAddClassDialog">
      <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
        <q-card-section>
          <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Add Extra Class</div>
          <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Schedule a new session</div>
        </q-card-section>

        <q-card-section class="q-gutter-md">
            <!-- Select Parent Course -->
            <q-select 
               v-model="newClass.parentCourse" 
               :options="activeRegularCourses" 
               option-label="displayName" 
               option-value="id" 
               label="Select Existing Class" 
               outlined 
               emit-value 
               map-options 
               :bg-color="$q.dark.isActive ? 'grey-9' : 'white'"
               :dark="$q.dark.isActive"
               :rules="[val => !!val || 'Required']"
               @update:model-value="onParentCourseSelect"
            />

            <!-- Name of Extra Class (Auto-filled or Custom) -->
            <q-input v-model="newClass.name" label="Session Name (e.g. Revision for Exam)" outlined :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
            
            <q-input v-model="newClass.fee_amount" label="Fee (LKR)" type="number" outlined :rules="[val => val !== null && val !== '' || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
            
            <!-- Date & Time -->
            <div class="row q-col-gutter-sm">
                <div class="col-4">
                     <q-input v-model="newClass.date" type="date" label="Date" outlined stack-label :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                </div>
                <div class="col-4">
                     <q-input v-model="newClass.startTime" type="time" label="Start Time" outlined stack-label :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                </div>
                <div class="col-4">
                     <q-input v-model="newClass.endTime" type="time" label="End Time" outlined stack-label :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                </div>
            </div>

            <!-- Check Availability -->
            <q-btn label="Check Hall Availability" color="teal" outline class="full-width" @click="checkHalls" :disable="!newClass.date || !newClass.startTime || !newClass.endTime" />

            <!-- Hall Selection -->
            <div v-if="hallCheckPerformed">
                 <div v-if="availableHalls.length > 0">
                     <div class="text-subtitle2 q-mt-md" :class="$q.dark.isActive ? 'text-white' : ''">Available Halls:</div>
                     <q-list bordered separator class="rounded-borders" :class="$q.dark.isActive ? 'bg-grey-9 border-grey-7' : ''">
                         <q-item tag="label" v-for="hall in availableHalls" :key="hall.id" v-ripple class="q-pa-sm">
                             <q-item-section avatar>
                                 <q-radio v-model="newClass.hallId" :val="hall.id" :dark="$q.dark.isActive" />
                             </q-item-section>
                             <q-item-section>
                                 <q-item-label :class="$q.dark.isActive ? 'text-white' : ''">{{ hall.name }}</q-item-label>
                                 <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">
                                     Capacity: <strong>{{ hall.capacity }}</strong> Students
                                     <q-icon v-if="hall.has_ac" name="ac_unit" color="blue" />
                                 </q-item-label>
                             </q-item-section>
                         </q-item>
                     </q-list>
                 </div>
                 <div v-else class="text-negative q-mt-sm">No halls available for this time slot.</div>
            </div>

         </q-card-section>

         <q-card-actions align="right">
           <q-btn flat label="Cancel" v-close-popup :color="$q.dark.isActive ? 'grey-4' : 'primary'" />
           <q-btn color="primary" label="Book & Request" @click="submitClass" :loading="loading" :disable="!newClass.hallId" />
         </q-card-actions>
       </q-card>
     </q-dialog>

    <!-- Student List Dialog -->
    <q-dialog v-model="showStudentsDialog">
        <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
            <q-card-section>
                <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Student List</div>
                <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Total: {{ studentsList.length }}</div>
            </q-card-section>
            <q-card-section>
                <q-list separator bordered class="rounded-borders scroll" style="max-height: 400px" :class="$q.dark.isActive ? 'bg-grey-9 border-grey-8' : ''">
                     <q-item v-for="student in studentsList" :key="student.id">
                         <q-item-section avatar>
                             <q-avatar size="sm" color="teal" text-color="white">{{ student.name.charAt(0) }}</q-avatar>
                         </q-item-section>
                         <q-item-section>
                             <q-item-label :class="$q.dark.isActive ? 'text-white' : ''">{{ student.name }}</q-item-label>
                             <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">
                                 {{ student.phone || 'No Phone' }}
                             </q-item-label>
                         </q-item-section>
                         <q-item-section side>
                            <div class="column items-end q-gutter-xs">
                                 <!-- Enrollment Status -->
                                 <q-badge :color="student.pivot?.status === 'active' ? 'green' : 'orange'" rounded class="q-mb-xs">
                                     {{ student.pivot?.status }}
                                 </q-badge>

                                 <!-- Attendance Status -->
                                 <q-chip 
                                    size="xs" 
                                    :color="getAttendanceColor(student)" 
                                    text-color="white"
                                    clickable
                                    @click="toggleAttendance(student)"
                                 >
                                     {{ getAttendanceLabel(student) }}
                                 </q-chip>

                                 <!-- Payment Status -->
                                 <span class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">
                                     Fee: <span :class="hasPaid(student) ? 'text-green text-weight-bold' : 'text-orange'">{{ hasPaid(student) ? 'Paid' : 'Pending' }}</span>
                                 </span>
                             </div>
                         </q-item-section>
                     </q-item>
                     <div v-if="studentsList.length === 0" class="text-center text-grey q-pa-md">
                         No students enrolled.
                     </div>
                 </q-list>
            </q-card-section>
            <q-card-actions align="right">
                <q-btn flat label="Close" v-close-popup :color="$q.dark.isActive ? 'white' : 'primary'" />
            </q-card-actions>
        </q-card>
    </q-dialog>
 
    </q-page>
</template>

 <script setup>
 import { ref, computed, onMounted } from 'vue'
 import { useQuasar, date as qDate } from 'quasar'
 import { api } from 'boot/axios'
 import { useAuthStore } from 'stores/auth-store'
 import { useTeacherStore } from 'stores/teacher-store'
 import { storeToRefs } from 'pinia'
 
 const $q = useQuasar()
 const authStore = useAuthStore()
 const teacherStore = useTeacherStore()
 const { loading, courses } = storeToRefs(teacherStore)
 
 onMounted(() => {
     teacherStore.fetchCourses({ teacher_id: authStore.user?.id })
 })

 const displayTeacherId = computed(() => {
     let id = authStore.user?.username || '0000'
     if (id && id.startsWith('STU')) {
         return id.replace('STU', 'TCH')
     }
     return id
 })

 const activeRegularCourses = computed(() => {
     if (!courses.value) return []
     return courses.value
         .filter(c => c.status === 'approved' && (!c.type || c.type === 'regular'))
         .map(c => ({
             ...c,
             displayName: `${c.name} (${c.batch?.name})`
         }))
 })

 const schedule = computed(() => {
     const today = qDate.formatDate(Date.now(), 'dddd')
     const todayYMD = qDate.formatDate(Date.now(), 'YYYY-MM-DD')
     
     if (!courses.value) return []
     
     return courses.value.filter(c => {
          let s = c.schedule
          if (!s) return false
          if (typeof s === 'string') {
              try { s = JSON.parse(s) } catch { return false }
          }
          return s.day === today || s.date === todayYMD
     }).map(c => {
          let s = c.schedule
          if (typeof s === 'string') { try { s = JSON.parse(s) } catch { /* ignore */ } }
          
          return {
              id: c.id,
              time: s.start ? `${s.start} - ${s.end}` : 'TBA',
              subject: c.subject?.name || c.name,
              batch: c.batch?.name || 'Batch',
              hallName: c.hall?.name || 'TBA',
              hallFloor: c.hall?.floor,
              hallAC: c.hall?.has_ac,
              status: 'upcoming'  
          }
     })
 })

 const todayClassesCount = computed(() => schedule.value.length)

 const totalStudentsCount = computed(() => {
     if (!courses.value) return 0
     return courses.value
        .filter(c => c.status === 'approved')
        .reduce((acc, c) => acc + (c.students_count || 0), 0)
 })
 
 const showAddClassDialog = ref(false)
 const availableHalls = ref([])
 const hallCheckPerformed = ref(false)
 
 const newClass = ref({ 
     name: '', batchId: null, subjectId: null, fee_amount: 0,
     date: '', startTime: '', endTime: '', hallId: null,
     parentCourse: null
 })
 
 const extraCourses = computed(() => {
     if (!courses.value) return []
     return courses.value.filter(c => c.type === 'extra')
 })

 const upcomingExtraCourses = computed(() => {
     const today = new Date().toISOString().slice(0, 10)
     return extraCourses.value.filter(c => {
          let s = c.schedule
          if (!s) return false
          if (typeof s === 'string') { try { s = JSON.parse(s) } catch { return false } }
          
          const d = s.date || ''
          return d >= today
     }).sort((a,b) => {
         let da = typeof a.schedule === 'string' ? JSON.parse(a.schedule).date : a.schedule.date
         let db = typeof b.schedule === 'string' ? JSON.parse(b.schedule).date : b.schedule.date
         return (da || '').localeCompare(db || '')
     })
 })
 
 const pastExtraCourses = computed(() => {
     const today = new Date().toISOString().slice(0, 10)
     return extraCourses.value.filter(c => {
          let s = c.schedule
          if (!s) return false
          if (typeof s === 'string') { try { s = JSON.parse(s) } catch { return false } }
          
          const d = s.date || ''
          return d < today
     }).sort((a,b) => {
         let da = typeof a.schedule === 'string' ? JSON.parse(a.schedule).date : a.schedule.date
         let db = typeof b.schedule === 'string' ? JSON.parse(b.schedule).date : b.schedule.date
         return (db || '').localeCompare(da || '')
     })
 })

 const isEditMode = ref(false)
 const editingId = ref(null)

 function openAddClassDialog() {
    isEditMode.value = false
    editingId.value = null
    newClass.value = { 
        name: '', batchId: null, subjectId: null, fee_amount: 0, 
        date: '', startTime: '', endTime: '', hallId: null, parentCourse: null 
    }
    showAddClassDialog.value = true
    hallCheckPerformed.value = false
    availableHalls.value = []
 }

 function onParentCourseSelect(courseId) {
     const course = activeRegularCourses.value.find(c => c.id === courseId)
     if (course) {
         newClass.value.name = `Extra Class: ${course.name}`
         newClass.value.batchId = course.batch_id
         newClass.value.subjectId = course.subject_id
         newClass.value.fee_amount = 0
     }
 }
 
 function openEditClassDialog(cls) {
    isEditMode.value = true
    editingId.value = cls.id
    
    let sched = cls.schedule
    if (typeof sched === 'string') { try { sched = JSON.parse(sched) } catch { /* ignore */ } }
    
    newClass.value = {
        name: cls.name,
        batchId: cls.batch_id,
        subjectId: cls.subject_id,
        fee_amount: cls.fee_amount,
        parentCourse: cls.parent_course_id,
        hallId: cls.hall_id,
        date: sched?.date || '',
        startTime: sched?.start || '',
        endTime: sched?.end || ''
    }
    showAddClassDialog.value = true
    hallCheckPerformed.value = false
    availableHalls.value = []
 }

 async function deleteClassHandler(cls) {
     $q.dialog({
        title: 'Confirm Delete',
        message: 'Are you sure you want to delete this class?',
        cancel: true,
        persistent: true
     }).onOk(async () => {
         await teacherStore.deleteClass(cls.id)
         teacherStore.fetchCourses({ teacher_id: authStore.user?.id })
         $q.notify({ type: 'positive', message: 'Class Deleted' })
     })
 }
 
 async function checkHalls() {
     if (!newClass.value.date || !newClass.value.startTime || !newClass.value.endTime) return
     
     // Call API
     availableHalls.value = await teacherStore.checkHallAvailability({
         date: newClass.value.date,
         start_time: newClass.value.startTime,
         end_time: newClass.value.endTime
     })
     hallCheckPerformed.value = true
 }
 
 async function submitClass() {
    if (!newClass.value.parentCourse || !newClass.value.hallId) {
        $q.notify({ type: 'warning', message: 'Please select a course and hall' })
        return
    }

    const payload = {
        name: newClass.value.name,
        batch_id: newClass.value.batchId,
        subject_id: newClass.value.subjectId,
        teacher_id: authStore.user?.id,
        fee_amount: newClass.value.fee_amount,
        hall_id: newClass.value.hallId,
        type: 'extra',
        parent_course_id: newClass.value.parentCourse,
        schedule: {
            date: newClass.value.date,
            start: newClass.value.startTime,
            end: newClass.value.endTime,
            type: 'one-off'
        }
    }

    let res;
    if (isEditMode.value) {
        res = await teacherStore.updateClass(editingId.value, payload)
    } else {
        res = await teacherStore.createClass(payload)
    }

    if (res.success) {
        $q.notify({ type: 'positive', message: isEditMode.value ? 'Extra Class Updated' : 'Extra Class Requested.' })
        showAddClassDialog.value = false
        // Reset
        newClass.value = { 
            name: '', batchId: null, subjectId: null, fee_amount: 0, 
            date: '', startTime: '', endTime: '', hallId: null, parentCourse: null 
        }
        hallCheckPerformed.value = false
        isEditMode.value = false
        editingId.value = null
    } else {
        $q.notify({ type: 'negative', message: 'Failed: ' + (res.error || 'Unknown Error') })
    }
 }
    
 const showStudentsDialog = ref(false)
 const studentsList = ref([])
 const currentClassId = ref(null)

 async function viewStudents(courseId) {
     try {
         currentClassId.value = courseId
         const res = await api.get(`/v1/courses/${courseId}/students`)
         studentsList.value = res.data.data || res.data
         showStudentsDialog.value = true
     } catch (e) {
         console.error(e)
         $q.notify({ type: 'negative', message: 'Failed to fetch students' })
     }
 }
 
 function getAttendance(student) {
    if (student.attendances && student.attendances.length > 0) return student.attendances[0]
    return null
 }

 function getAttendanceLabel(student) {
    const att = getAttendance(student)
    return att ? att.status.toUpperCase() : 'MARK PRESENT'
 }

 function getAttendanceColor(student) {
    const att = getAttendance(student)
    if (!att) return 'grey'
    return att.status === 'present' ? 'green' : 'red'
 }

 function hasPaid(student) {
    return student.payments && student.payments.length > 0
 }

 async function toggleAttendance(student) {
    const att = getAttendance(student)
    const newStatus = (!att || att.status !== 'present') ? 'present' : 'absent'
    
    try {
        await api.post('/v1/attendances', {
            course_id: currentClassId.value,
            user_id: student.id,
            date: new Date().toISOString().split('T')[0],
            status: newStatus
        })
        // Optimized Refresh (Keep dialog open)
        const res = await api.get(`/v1/courses/${currentClassId.value}/students`)
        studentsList.value = res.data.data || res.data
    } catch(e) { console.error(e) }
 }

 // --- Meeting Logic ---
 const showMeetingDialog = ref(false)
 const sendingNotice = ref(false)
 const meetingForm = ref({
    title: '',
    message: '',
    sendToAll: false,
    courseId: null,
    type: 'meeting'
 })

 function openMeetingDialog() {
    meetingForm.value = {
        title: '',
        message: '',
        sendToAll: false,
        courseId: null,
        type: 'meeting'
    }
    showMeetingDialog.value = true
 }

 async function sendNotice() {
    if (!meetingForm.value.title || !meetingForm.value.message) {
        $q.notify({ type: 'warning', message: 'Please fill all details' })
        return
    }
    if (!meetingForm.value.sendToAll && !meetingForm.value.courseId) {
        $q.notify({ type: 'warning', message: 'Please select a class or choose Send All' })
        return
    }

    sendingNotice.value = true
    try {
        await api.post('/v1/notices', {
            title: meetingForm.value.title,
            message: meetingForm.value.message,
            type: meetingForm.value.type,
            course_id: meetingForm.value.sendToAll ? 'all' : meetingForm.value.courseId,
            scheduled_at: new Date().toISOString()
        })
        $q.notify({ type: 'positive', message: 'Notice Sent Successfully!' })
        showMeetingDialog.value = false
    } catch (e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Failed to send notice' })
    } finally {
        sendingNotice.value = false
    }
 }
 </script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap');

.border-light { border: 1px solid #eee; }
.border-bottom-teal { border-bottom: 3px solid teal; }
.border-bottom-orange { border-bottom: 3px solid orange; }
.border-bottom-blue { border-bottom: 3px solid #2196F3; }
.border-bottom-purple { border-bottom: 3px solid #9C27B0; }
</style>
