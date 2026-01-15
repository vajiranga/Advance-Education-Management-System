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
    <!-- Schedule Section (Calendar) -->
    <div class="q-mb-lg">
       <div class="text-h6 text-weight-bold q-mb-sm">My Class Schedule</div>
       <div class="flex justify-center">
           <q-date
             v-model="calendarDate"
             :key="myCourses.length"
             minimal
             flat
             bordered
             class="full-width"
             style="max-width: 800px"
             today-btn
             @update:model-value="onDateClick"
           >
             <template v-slot:default="scope">
                <div v-if="scope"
                   class="fit column items-center justify-center relative-position q-pa-xs rounded-borders cursor-pointer"
                   :class="getDayClass(scope)"
                   @click="onDateClick(scope.date)"
                >
                   <div :class="scope.today ? 'text-bold' : ''">
                      {{ scope.day }}
                   </div>
                   <div v-if="getDayBadge(scope.date)" class="q-mt-xs bg-primary" style="width: 4px; height: 4px; border-radius: 50%"></div>
                   <q-badge
                      v-if="getDayBadge(scope.date)"
                      rounded
                      color="red"
                      text-color="white"
                      floating
                      transparent
                      style="top: 2px; right: 2px; transform: scale(0.8);"
                   >
                       {{ getDayBadge(scope.date) }}
                   </q-badge>
                </div>
             </template>
           </q-date>
       </div>
    </div>

    <!-- Date Details Dialog -->
    <q-dialog v-model="showDateDialog">
        <q-card style="min-width: 350px">
            <q-card-section class="row items-center justify-between bg-primary text-white">
                <div class="text-h6">{{ activeDate }}</div>
                <q-btn flat round dense icon="close" v-close-popup />
            </q-card-section>
            <q-card-section class="q-pa-md">
                <div v-if="selectedDateClasses.length === 0" class="text-grey text-center q-py-md">
                    No classes scheduled for this date.
                </div>
                <q-list separator v-else>
                     <q-item v-for="cls in selectedDateClasses" :key="cls.id">
                         <q-item-section>
                             <q-item-label class="text-weight-bold">{{ cls.name }}</q-item-label>
                             <q-item-label caption class="text-grey-7">
                                 {{ formatSchedule(cls.schedule) }}
                             </q-item-label>
                             <q-item-label caption>
                                 {{ cls.teacher?.name }} | {{ cls.hall?.name || 'No Hall' }}
                             </q-item-label>
                         </q-item-section>
                         <q-item-section side>
                             <q-chip size="sm" :color="cls.type === 'extra' ? 'orange' : 'blue-1'" :text-color="cls.type === 'extra' ? 'white' : 'blue'">
                                 {{ cls.type === 'extra' ? 'Extra' : 'Regular' }}
                             </q-chip>
                         </q-item-section>
                     </q-item>
                </q-list>
            </q-card-section>
        </q-card>
    </q-dialog>

    <!-- Your Regular Classes -->
    <div class="q-mb-xl">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold">Your Classes</div>
        <q-btn flat color="primary" label="View All" to="/student/courses" no-caps />
      </div>
      
      <div v-if="loading" class="row justify-center q-my-md">
          <q-spinner color="primary" size="3em" />
      </div>
      
      <div class="row q-col-gutter-md" v-else>
        <div class="col-12 col-sm-6 col-md-4" v-for="course in regularCourses" :key="course.id">
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
        <div v-if="regularCourses.length === 0" class="col-12 text-center text-grey q-py-lg">
            No regular classes enrolled.
        </div>
      </div>
    </div>

    <!-- Upcoming Extra Classes Section -->
    <div class="q-mb-xl" v-if="upcomingExtraCourses.length > 0">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold text-orange-9">Upcoming Extra Classes</div>
      </div>
      
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-4" v-for="course in upcomingExtraCourses" :key="course.id">
           <q-card class="my-card no-shadow border-light hover-effect full-height column border-top-orange">
             <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip color="orange-1" text-color="orange" size="xs" icon="star">Upcoming</q-chip>
                </div>
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl">{{ course.name }}</div>
                <div class="text-subtitle2 text-grey-8">For: {{ course.parent_course?.name }}</div>
                
                <div class="text-subtitle2 text-grey-8 row items-center q-mt-sm">
                  <q-icon name="event" size="xs" class="q-mr-xs" />
                  {{ course.schedule?.date }}
                </div>
                <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                  <q-icon name="schedule" size="xs" class="q-mr-xs" />
                  {{ course.schedule?.start }} - {{ course.schedule?.end }}
                </div>
                <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                  <q-icon name="meeting_room" class="q-mr-xs" />
                  {{ course.hall?.name || 'No Hall' }}
                </div>
             </q-card-section>
           </q-card>
        </div>
      </div>
    </div>

    <!-- Past Extra Classes Section -->
    <div class="q-mb-xl" v-if="pastExtraCourses.length > 0">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold text-grey-7">Past Extra Classes</div>
      </div>
      
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-4" v-for="course in pastExtraCourses" :key="course.id">
           <q-card class="my-card no-shadow border-light full-height column bg-grey-1">
             <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip color="grey-3" text-color="grey-8" size="xs" icon="history">Ended</q-chip>
                </div>
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl text-grey-8">{{ course.name }}</div>
                <div class="text-subtitle2 text-grey-6">For: {{ course.parent_course?.name }}</div>
                
                <div class="text-subtitle2 text-grey-6 row items-center q-mt-sm">
                  <q-icon name="event" size="xs" class="q-mr-xs" />
                  {{ course.schedule?.date }}
                </div>
                <div class="text-subtitle2 text-grey-6 row items-center q-mt-xs">
                  <q-icon name="schedule" size="xs" class="q-mr-xs" />
                  {{ course.schedule?.start }} - {{ course.schedule?.end }}
                </div>
             </q-card-section>
           </q-card>
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
import { onMounted, ref, computed } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { useStudentStore } from 'stores/student-store'
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { myCourses, allCourses, loading } = storeToRefs(studentStore)

const regularCourses = computed(() => (myCourses.value || []).filter(c => c.type === 'regular' || !c.type))
const extraCourses = computed(() => (myCourses.value || []).filter(c => c.type === 'extra'))

const upcomingExtraCourses = computed(() => {
    const today = new Date().toISOString().slice(0, 10)
    return extraCourses.value.filter(c => {
         const d = c.schedule?.date || ''
         return d >= today
    }).sort((a,b) => (a.schedule?.date || '').localeCompare(b.schedule?.date))
})

const pastExtraCourses = computed(() => {
    const today = new Date().toISOString().slice(0, 10)
    return extraCourses.value.filter(c => {
         const d = c.schedule?.date || ''
         return d < today
    }).sort((a,b) => (b.schedule?.date || '').localeCompare(a.schedule?.date))
})

const calendarDate = ref(new Date().toISOString().slice(0, 10).replace(/-/g, '/'))
const showDateDialog = ref(false)
const selectedDateClasses = ref([])
const activeDate = ref('')

function getClassesForDate(dateStr) {
    if (!myCourses.value) return []
    
    // Safe parse YYYY/MM/DD
    const parts = dateStr.split('/')
    const y = parseInt(parts[0])
    const m = parseInt(parts[1])
    const d = parseInt(parts[2])
    const date = new Date(y, m - 1, d)
    
    // Manual Day Lookup
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    const dayName = days[date.getDay()]
    
    const normalizedDate = `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`

    // Parse Regular (Check Day OR Date)
    const regular = regularCourses.value.filter(c => {
         let s = c.schedule
         if (typeof s === 'string') { try { s = JSON.parse(s) } catch { return false } }
         if (!s) return false
         
         // Match Recurring Day
         if (s.day && s.day === dayName) return true
         
         // Match Specific Date (if regular one-off)
         if (s.date && s.date === normalizedDate) return true
         
         return false
    })

    const extra = extraCourses.value.filter(c => {
         let s = c.schedule
         if (typeof s === 'string') { try { s = JSON.parse(s) } catch { return false } }
         return s?.date === normalizedDate
    })
    
    return [...regular, ...extra].sort((a,b) => {
         // Sort by time
         const getStart = (c) => {
             let s = typeof c.schedule === 'string' ? JSON.parse(c.schedule) : c.schedule
             return s?.start || ''
         }
         return getStart(a).localeCompare(getStart(b))
    })
}

function onDateClick(date) {
    activeDate.value = date
    selectedDateClasses.value = getClassesForDate(date)
    showDateDialog.value = true
}

function getDayBadge(date) {
    const classes = getClassesForDate(date)
    return classes.length > 0 ? classes.length : null
}

function getDayClass(scope) {
    if (!scope) return ''
    if (scope.selected) return 'bg-primary text-white'
    // check if today
    const today = new Date().toISOString().slice(0, 10).replace(/-/g, '/')
    if (scope.date === today) return 'border-today'
    
    // Check types
    const classes = getClassesForDate(scope.date)
    if (classes.length === 0) return ''
    
    const hasRegular = classes.some(c => (!c.type || c.type === 'regular'))
    const hasExtra = classes.some(c => c.type === 'extra')
    
    if (hasRegular && hasExtra) return 'bg-purple-1 text-purple'
    if (hasRegular) return 'bg-blue-1 text-blue'
    if (hasExtra) return 'bg-orange-1 text-orange'
    return ''
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
.border-today {
    border: 2px solid var(--q-primary);
    font-weight: bold;
    border-radius: 4px;
}
</style>
