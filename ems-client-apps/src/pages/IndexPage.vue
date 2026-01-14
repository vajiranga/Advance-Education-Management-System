<template>
  <q-page class="q-pa-md bg-grey-1">
    
    <!-- Header / Student Profile -->
    <q-card class="my-card q-mb-lg bg-white overflow-hidden" flat bordered>
      <q-card-section>
        <div class="row items-center q-col-gutter-md">
          <!-- Profile Info -->
          <div class="col-12 col-md-8">
            <div class="row items-center">
              <q-avatar size="80px" class="q-mr-md">
                <img src="https://cdn.quasar.dev/img/boy-avatar.png">
              </q-avatar>
              <div>
                <div class="text-h5 text-weight-bold">{{ authStore.user?.name || 'Student' }}</div>
                <div class="text-subtitle1 text-grey-7">{{ authStore.user?.username || 'ID: ???' }}</div>
                <q-chip size="sm" color="blue-1" text-color="primary" icon="school">
                  Student
                </q-chip>
              </div>
            </div>
          </div>
          
          <!-- Barcode Section -->
          <div class="col-12 col-md-4 text-center">
             <div class="bg-grey-2 q-pa-sm rounded-borders inline-block">
               <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 48px; line-height: 1;">
                 *{{ authStore.user?.username || '0000' }}*
               </div>
             </div>
             <div class="text-caption text-grey q-mt-sm">Scan at Entrance</div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Schedule Section -->
    <div class="q-mb-lg">
       <div class="text-h6 text-weight-bold q-mb-sm">My Class Schedule</div>
       <q-card flat bordered class="bg-white">
           <q-tabs v-model="selectedDay" dense active-color="primary" indicator-color="primary" class="text-grey" align="justify" :breakpoint="0">
                <q-tab v-for="day in days" :key="day" :name="day" :label="day.substring(0,3)" />
           </q-tabs>
           <q-separator />
           <q-tab-panels v-model="selectedDay" animated>
                <q-tab-panel v-for="day in days" :key="day" :name="day" class="q-pa-md">
                     <div v-if="getClassesForDay(day).length === 0" class="text-center text-grey q-py-sm">
                         <q-icon name="event_busy" size="sm" /> No classes scheduled for {{ day }}
                     </div>
                     <q-list separator v-else>
                         <q-item v-for="cls in getClassesForDay(day)" :key="cls.id" clickable v-ripple class="rounded-borders q-mb-xs">
                             <q-item-section avatar>
                                 <q-avatar color="blue-1" text-color="blue" font-size="14px">{{ cls.schedule?.start || 'TBA' }}</q-avatar>
                             </q-item-section>
                             <q-item-section>
                                 <q-item-label class="text-weight-bold">{{ cls.name }}</q-item-label>
                                 <q-item-label caption>{{ cls.teacher?.name }} | {{ cls.hall?.name || 'No Hall' }}</q-item-label>
                             </q-item-section>
                             <q-item-section side>
                                 <q-chip size="sm" color="green-1" text-color="green">Active</q-chip>
                             </q-item-section>
                         </q-item>
                     </q-list>
                </q-tab-panel>
           </q-tab-panels>
       </q-card>
    </div>

    <!-- Your Classes -->
    <div class="q-mb-xl">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold">Your Classes</div>
        <q-btn flat color="primary" label="View All" to="/student/courses" no-caps />
      </div>
      
      <div v-if="loading" class="row justify-center q-my-md">
          <q-spinner color="primary" size="3em" />
      </div>
      
      <div class="row q-col-gutter-md" v-else>
        <div class="col-12 col-sm-6 col-md-4" v-for="course in myCourses" :key="course.id">
           <q-card class="my-card no-shadow border-light hover-effect full-height column">
             <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip color="blue-1" text-color="primary" size="xs">Physical</q-chip>
                </div>
                <!-- Title Row -->
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl">{{ course.name }}</div>
                <div class="text-subtitle2 text-primary">{{ course.batch?.name }} - {{ course.subject?.name }}</div>
                
                <!-- Teacher Info -->
                <div class="text-subtitle2 text-grey-8 row items-center q-mt-sm">
                  <q-icon name="person" size="xs" class="q-mr-xs" />
                  {{ course.teacher?.name }}
                </div>
                <!-- Hall Info -->
                <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                  <q-icon name="meeting_room" class="q-mr-xs" />
                  {{ course.hall?.name || 'No Hall' }}
                </div>
                <!-- Student Count -->
                <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                  <q-icon name="group" class="q-mr-xs" />
                  {{ course.students_count || 0 }} Students
                </div>
                <!-- Schedule Info -->
                <div class="text-caption text-grey row items-center q-mt-xs">
                  <q-icon name="schedule" size="xs" class="q-mr-xs" />
                  {{ formatSchedule(course.schedule) }}
                </div>
             </q-card-section>

             <q-separator />
             <q-card-actions align="right">
               <q-btn unelevated color="primary" label="Enter Class" size="sm" icon-right="login" />
             </q-card-actions>
           </q-card>
        </div>
        <div v-if="myCourses.length === 0" class="col-12 text-center text-grey q-py-lg">
            No enrolled classes yet.
        </div>
      </div>
    </div>

    <!-- All Available Classes -->
    <div>
       <div class="row items-center justify-between q-mb-md">
          <div class="text-h6 text-weight-bold">All Available Classes</div>
       </div>
       
       <q-scroll-area horizontal style="height: 320px;" class="bg-transparent">
          <div class="row no-wrap q-gutter-md">
             <div v-for="rec in allCourses" :key="rec.id" style="width: 300px">
                <q-card class="my-card no-shadow border-light bg-white full-height flex column">
                   <q-card-section class="col-grow relative-position">
                     <div class="absolute-top-right q-pa-sm">
                        <q-chip color="green-1" text-color="green" size="xs">Open</q-chip>
                     </div>
                     <div class="text-h6 text-weight-bold ellipsis q-pr-xl" :title="rec.name">{{ rec.name }}</div>
                     <div class="text-subtitle2 text-primary">{{ rec.batch?.name }} - {{ rec.subject?.name }}</div>

                     <div class="text-subtitle2 text-grey-8 row items-center q-mt-sm">
                        <q-icon name="person" size="xs" class="q-mr-xs"/> {{ rec.teacher?.name }}
                     </div>
                     <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                        <q-icon name="meeting_room" size="xs" class="q-mr-xs"/> {{ rec.hall?.name || 'No Hall' }}
                     </div>
                     <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                        <q-icon name="group" size="xs" class="q-mr-xs"/> {{ rec.students_count || 0 }} Students
                     </div>

                     <div class="text-caption text-grey row items-center q-mt-md">
                        <q-icon name="schedule" size="xs" class="q-mr-xs"/> {{ formatSchedule(rec.schedule) }}
                     </div>
                     
                     <div class="row items-center justify-between q-mt-auto">
                       <div class="text-subtitle1 text-primary text-weight-bold">LKR {{ rec.fee_amount }}</div>
                       <q-btn 
                         unelevated 
                         :color="isEnrolled(rec.id) ? 'grey' : 'primary'" 
                         :label="isEnrolled(rec.id) ? 'Enrolled' : 'Enroll Now'" 
                         size="sm" 
                         @click="confirmEnroll(rec)" 
                         :disable="isEnrolled(rec.id)"
                       />
                     </div>
                   </q-card-section>
                </q-card>
             </div>
          </div>
       </q-scroll-area>
    </div>

  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useStudentStore } from 'stores/student-store'
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { myCourses, allCourses, loading } = storeToRefs(studentStore)

const selectedDay = ref(new Date().toLocaleDateString('en-US', { weekday: 'long' }))
const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']

function getClassesForDay(day) {
    if (!myCourses.value) return []
    return myCourses.value.filter(c => {
         // Handle different schedule formats
         let s = c.schedule
         if (!s) return false
         if (typeof s === 'string') {
             try { s = JSON.parse(s) } catch { return s.includes(day) }
         }
         return s.day === day || s.date === day // Basic check
    }).sort((a,b) => {
         let t1 = typeof a.schedule === 'object' ? a.schedule.start : ''
         let t2 = typeof b.schedule === 'object' ? b.schedule.start : ''
         return t1.localeCompare(t2)
    })
}

function isEnrolled(courseId) {
    if (!myCourses.value) return false
    return myCourses.value.some(c => c.id === courseId)
}

onMounted(() => {
    studentStore.fetchMyCourses()
    studentStore.fetchAllCourses()
})

// Confirm Enrollment
function confirmEnroll(course) {
    $q.dialog({
        title: 'Confirm Enrollment',
        message: `Do you want to enroll in <b>${course.name}</b>?<br>Fee: LKR ${course.fee_amount}`,
        html: true,
        cancel: true,
        persistent: true,
        ok: { label: 'Enroll Now', color: 'primary' }
    }).onOk(async () => {
        await enroll(course.id)
    })
}

async function enroll(courseId) {
    $q.loading.show()
    const success = await studentStore.enroll(courseId)
    $q.loading.hide()
    if(success) {
        $q.notify({type: 'positive', message: 'Enrolled Successfully!'})
    } else {
        $q.notify({type: 'negative', message: 'Enrollment Failed'})
    }
}

function formatSchedule(schedule) {
    if (!schedule) return 'TBA'
    if (typeof schedule === 'string') return schedule
    if (schedule.type === 'one-off') {
        return `${schedule.date} @ ${schedule.start}`
    }
    if (schedule.day) return `${schedule.day} | ${schedule.start} - ${schedule.end}`
    return 'Recurring'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap');

.border-light {
  border: 1px solid #e0e0e0;
}
.hover-effect:hover {
  transform: translateY(-2px);
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>
