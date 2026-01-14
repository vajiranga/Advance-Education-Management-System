<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Welcome Section -->
    <!-- Profile & ID Section -->
    <q-card class="q-mb-lg bg-white overflow-hidden" flat bordered>
       <q-card-section>
         <div class="row items-center q-col-gutter-md">
           <!-- Profile Info -->
           <div class="col-12 col-md-8">
             <div class="row items-center">
               <q-avatar size="80px" class="q-mr-md font-roboto">
                 <img src="https://cdn.quasar.dev/img/avatar2.jpg">
               </q-avatar>
               <div>
                 <div class="text-h5 text-weight-bold text-teal-9">{{ authStore.user?.name || 'Teacher' }}</div>
                 <div class="text-subtitle1 text-grey-7">{{ authStore.user?.username || 'ID: ???' }}</div>
                 <div class="row q-gutter-x-sm q-mt-xs">
                     <q-chip size="sm" color="teal-1" text-color="teal" icon="verified_user" label="Verified Teacher" />
                     <q-chip size="sm" color="blue-1" text-color="blue" icon="email" :label="authStore.user?.email" />
                 </div>
               </div>
             </div>
           </div>
           
           <!-- Barcode Section -->
           <div class="col-12 col-md-4 text-center">
              <div class="bg-grey-2 q-pa-md rounded-borders inline-block">
                <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 48px; line-height: 1;">
                  *{{ authStore.user?.username || '000000' }}*
                </div>
              </div>
              <div class="text-caption text-grey q-mt-sm">Teacher ID Barcode</div>
           </div>
         </div>
       </q-card-section>
    </q-card>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-white text-teal border-bottom-teal no-shadow">
          <q-card-section>
             <div class="row items-center justify-between">
                <div>
                   <div class="text-h4 text-weight-bold">04</div>
                   <div class="text-caption text-grey">Today's Classes</div>
                </div>
                <q-avatar color="teal-1" text-color="teal" icon="schedule" font-size="24px" />
             </div>
          </q-card-section>
        </q-card>
      </div>
      <!-- Other stats omitted for brevity, keeping existing structure -->
       <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-white text-orange border-bottom-orange no-shadow">
          <q-card-section>
             <div class="row items-center justify-between">
                <div>
                   <div class="text-h4 text-weight-bold">128</div>
                   <div class="text-caption text-grey">Total Students</div>
                </div>
                <q-avatar color="orange-1" text-color="orange" icon="groups" font-size="24px" />
             </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row q-col-gutter-lg">
       <!-- Schedule -->
       <div class="col-12 col-md-8">
          <q-card class="no-shadow border-light full-height">
             <q-card-section class="row items-center justify-between">
                <div class="text-h6">Today's Schedule</div>
                <q-chip outline color="teal" label="Thursday, 8th Jan" icon="calendar_today" />
             </q-card-section>
             <q-separator />
             <q-list separator>
                <q-item v-for="cls in schedule" :key="cls.id" class="q-py-md">
                   <q-item-section avatar>
                      <q-avatar color="grey-2" text-color="teal" class="text-weight-bold">{{ cls.time.split(' ')[0] }}</q-avatar>
                   </q-item-section>
                   <q-item-section>
                      <q-item-label class="text-weight-bold text-subtitle1">{{ cls.subject }} - {{ cls.batch }}</q-item-label>
                      <q-item-label caption><q-icon name="meeting_room" /> {{ cls.hall }}</q-item-label>
                   </q-item-section>
                   <q-item-section side>
                       <q-btn v-if="cls.status === 'upcoming'" md color="teal" label="Start Class" icon="play_arrow" unelevated />
                       <q-chip v-else color="green-1" text-color="green" label="Completed" />
                   </q-item-section>
                </q-item>
             </q-list>
          </q-card>
       </div>

       <!-- Quick Actions -->
       <div class="col-12 col-md-4">
          <q-card class="no-shadow border-light q-mb-md">
             <q-card-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm">Quick Actions</div>
                <div class="row q-col-gutter-sm">
                   <div class="col-6">
                      <!-- New Class Button -->
                      <q-btn outline class="full-width" color="teal" icon="add" label="Add Extra Class" stack @click="openAddClassDialog" />
                   </div>
                   <div class="col-6">
                      <q-btn outline class="full-width" color="orange" icon="person_add" label="Add Student" stack />
                   </div>
                   <!-- ... -->
                </div>
             </q-card-section>
          </q-card>
       </div>
    </div>

    <!-- Add Extra Class Dialog -->
    <q-dialog v-model="showAddClassDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Add Extra Class</div>
          <div class="text-caption text-grey">Schedule a new session</div>
        </q-card-section>

        <q-card-section class="q-gutter-md">
           <!-- Name -->
           <q-input v-model="newClass.name" label="Class Name (e.g. Maths Revision)" outlined :rules="[val => !!val || 'Required']" />
           
           <!-- Grade (Filtered) -->
           <q-select 
              v-model="newClass.batch" 
              :options="filteredBatches" 
              option-label="name" 
              option-value="id" 
              label="Grade" 
              outlined 
              emit-value 
              map-options 
              :rules="[val => !!val || 'Required']" 
           />
           
           <!-- Subject -->
           <q-select 
              v-model="newClass.subject" 
              :options="subjects" 
              option-label="name" 
              option-value="id" 
              label="Subject" 
              outlined 
              emit-value 
              map-options 
              :rules="[val => !!val || 'Required']" 
           />

           <q-input v-model="newClass.fee_amount" label="Fee (LKR)" type="number" outlined />
           
           <!-- Date & Time -->
           <div class="row q-col-gutter-sm">
               <div class="col-4">
                    <q-input v-model="newClass.date" type="date" label="Date" outlined stack-label :rules="[val => !!val || 'Required']" />
               </div>
               <div class="col-4">
                    <q-input v-model="newClass.startTime" type="time" label="Start Time" outlined stack-label :rules="[val => !!val || 'Required']" />
               </div>
               <div class="col-4">
                    <q-input v-model="newClass.endTime" type="time" label="End Time" outlined stack-label :rules="[val => !!val || 'Required']" />
               </div>
           </div>

           <!-- Check Availability -->
           <q-btn label="Check Hall Availability" color="teal" outline class="full-width" @click="checkHalls" :disable="!newClass.date || !newClass.startTime || !newClass.endTime" />

           <!-- Hall Selection -->
           <div v-if="hallCheckPerformed">
                <div v-if="availableHalls.length > 0">
                    <div class="text-subtitle2 q-mt-md">Available Halls:</div>
                    <q-list bordered separator class="rounded-borders">
                        <q-item tag="label" v-for="hall in availableHalls" :key="hall.id" v-ripple class="q-pa-sm">
                            <q-item-section avatar>
                                <q-radio v-model="newClass.hallId" :val="hall.id" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>{{ hall.name }}</q-item-label>
                                <q-item-label caption>
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
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn color="primary" label="Book & Request" @click="submitClass" :loading="loading" :disable="!newClass.hallId" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar, date as qDate } from 'quasar'
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
             hall: c.hall?.name || 'TBA',
             status: 'upcoming' 
         }
    })
})

const showAddClassDialog = ref(false)
const batches = ref([])
const subjects = ref([])
const availableHalls = ref([])
const hallCheckPerformed = ref(false)

const newClass = ref({ 
    name: '', batch: null, subject: null, fee_amount: '',
    date: '', startTime: '', endTime: '', hallId: null
})

const filteredBatches = computed(() => {
    // Filter to show only 'Grade X' entries as requested (6-11)
    // Backend returns 'Grade 6', 'Grade 7'... '2026 A/L'.
    return batches.value.filter(b => b.name.includes('Grade'))
})

async function openAddClassDialog() {
   showAddClassDialog.value = true
   hallCheckPerformed.value = false
   availableHalls.value = []
   if (batches.value.length === 0) batches.value = await teacherStore.fetchBatches()
   if (subjects.value.length === 0) subjects.value = await teacherStore.fetchSubjects()
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
   if (!newClass.value.name || !newClass.value.batch || !newClass.value.hallId) {
       $q.notify({ type: 'warning', message: 'Please fill all required fields and select a hall' })
       return
   }

   const payload = {
       name: newClass.value.name,
       batch_id: newClass.value.batch,
       subject_id: newClass.value.subject,
       teacher_id: authStore.user?.id || 1,
       fee_amount: newClass.value.fee_amount || 0,
       hall_id: newClass.value.hallId,
       schedule: {
           date: newClass.value.date,
           start: newClass.value.startTime,
           end: newClass.value.endTime,
           type: 'one-off'
       }
   }

   const res = await teacherStore.createClass(payload)
   if (res.success) {
       $q.notify({ type: 'positive', message: 'Extra Class Requested. Pending Admin Approval.' })
       showAddClassDialog.value = false
       // Reset
       newClass.value = { name: '', batch: null, subject: null, fee_amount: '', date: '', startTime: '', endTime: '', hallId: null }
       hallCheckPerformed.value = false
   } else {
       $q.notify({ type: 'negative', message: 'Failed: ' + (res.error || 'Unknown Error') })
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
