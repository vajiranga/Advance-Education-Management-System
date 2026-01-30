<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    
    <!-- Header / Student Profile -->
    <q-card class="my-card q-mb-lg overflow-hidden" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'" flat bordered>
      <q-card-section>
        <div class="row items-center q-col-gutter-md">
          <!-- Profile Info -->
          <div class="col-12 col-md-8">
            <div class="row items-center">
              <!-- Avatar Removed -->
              <div>
                <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ effectiveStudent?.name || 'Student' }}</div>
                <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">{{ displayId }}</div>
                <q-chip size="sm" :color="$q.dark.isActive ? 'grey-8' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-2' : 'primary'" icon="school">
                  Student
                </q-chip>
              </div>
            </div>
          </div>
          
          <!-- Barcode Section -->
          <div class="col-12 col-md-4 text-center">
             <div class="q-pa-sm rounded-borders inline-block" :class="$q.dark.isActive ? 'bg-white' : 'bg-grey-2'">
               <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 48px; line-height: 1; color: black !important;">
                 *{{ displayId }}*
               </div>
             </div>
             <div class="text-caption q-mt-sm" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">Scan at Entrance</div>
             <q-btn flat dense color="primary" icon="download" label="Download ID" class="q-mt-sm" size="sm" @click="downloadIdCard" :disable="!effectiveStudent"/>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Hidden ID Card Template -->
    <div style="position: fixed; left: -9999px; top: 0;">
        <div ref="idCardRef" id="id-card-element" style="width: 400px; height: 280px; background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%); color: white; border-radius: 12px; padding: 20px; font-family: 'Roboto', sans-serif; position: relative; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <!-- Decorative Circle -->
            <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            
            <div class="row items-center no-wrap full-height">
                 <!-- Photo Placeholder -->
                  <div style="width: 100px; height: 100px; background: white; border-radius: 8px; margin-right: 20px; display: flex; align-items: center; justify-content: center; color: #1976D2; font-weight: bold; font-size: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                       {{ effectiveStudent?.name?.charAt(0) || 'S' }}
                  </div>
                  
                  <!-- Details -->
                  <div style="flex: 1; z-index: 1;">
                       <div style="font-size: 12px; opacity: 0.8; letter-spacing: 1px; margin-bottom: 4px;">EMS STUDENT IDENTITY</div>
                       <div style="font-size: 20px; font-weight: bold; margin-bottom: 4px; line-height: 1.2;">{{ effectiveStudent?.name || 'Student Name' }}</div>
                       <div style="font-size: 14px; opacity: 0.9; margin-bottom: 12px;">{{ displayId }}</div>
                       
                       <div class="row q-col-gutter-xs">
                            <div class="col-6">
                                <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Grade</div>
                                <div style="font-size: 13px; font-weight: 500;">{{ effectiveStudent?.grade || 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Date of Birth</div>
                                <div style="font-size: 13px; font-weight: 500;">{{ effectiveStudent?.dob || 'N/A' }}</div>
                            </div>
                       </div>
                       
                       <div class="row q-col-gutter-xs q-mt-xs">
                           <div class="col-6">
                               <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase;">Phone</div>
                               <div style="font-size: 13px; font-weight: 500;">{{ effectiveStudent?.phone || 'N/A' }}</div>
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

    <!-- Schedule Section (Calendar) -->
    <div class="q-mb-lg">
       <div class="text-h6 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">My Class Schedule</div>
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
             :dark="$q.dark.isActive"
             :color="$q.dark.isActive ? 'primary' : ''"
             :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'"
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
                    
                    <!-- Regular Class Indicator (Tiny Dash) -->
                    <div v-if="hasRegularClass(scope.date)" class="absolute-bottom full-width flex justify-center" style="bottom: 2px">
                       <div :class="$q.dark.isActive ? 'bg-white' : 'bg-primary'" style="height: 2px; width: 12px; border-radius: 2px"></div>
                    </div>

                    <!-- Extra Class Indicator (Green Highlight/Circle) -->
                    <div v-if="hasExtraClass(scope.date)" class="absolute-full flex flex-center" style="background: rgba(76, 175, 80, 0.2); border-radius: 50%">
                    </div>
                 </div>
             </template>
           </q-date>
       </div>
    </div>
    
    <!-- Date Details Dialog -->
    <q-dialog v-model="showDateDialog">
        <q-card style="min-width: 350px">
            <q-card-section class="row items-center justify-between text-white" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-primary'">
                <div class="text-h6">{{ activeDate }}</div>
                <q-btn flat round dense icon="close" v-close-popup />
            </q-card-section>
            <q-card-section class="q-pa-md" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
                <div v-if="selectedDateClasses.length === 0" class="text-grey text-center q-py-md">
                    No classes scheduled for this date.
                </div>
                <q-list separator v-else>
                     <q-item v-for="cls in selectedDateClasses" :key="cls.id">
                         <q-item-section>
                             <q-item-label class="text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ cls.name }}</q-item-label>
                             <q-item-label caption :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">
                                 {{ formatSchedule(cls.schedule) }}
                             </q-item-label>
                             <q-item-label caption :class="$q.dark.isActive ? 'text-grey-5' : ''">
                                 {{ cls.teacher?.name }} | {{ cls.hall?.name || 'No Hall' }}
                             </q-item-label>
                         </q-item-section>
                         <q-item-section side>
                             <q-chip size="sm" 
                                :color="cls.type === 'extra' ? ($q.dark.isActive ? 'orange-10' : 'orange') : ($q.dark.isActive ? 'blue-10' : 'blue-1')" 
                                :text-color="cls.type === 'extra' ? 'white' : ($q.dark.isActive ? 'white' : 'blue')"
                             >
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
        <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">Your Classes</div>
        <q-btn flat color="primary" label="View All" to="/student/courses" no-caps />
      </div>
      
      <div v-if="loading" class="row justify-center q-my-md">
          <q-spinner color="primary" size="3em" />
      </div>
      
      <div class="row q-col-gutter-md" v-else>
        <div class="col-12 col-sm-6 col-md-4" v-for="course in regularCourses" :key="course.id">
           <q-card class="my-card no-shadow hover-effect full-height column" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
             <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'white' : 'primary'" size="xs">Physical</q-chip>
                </div>
                <!-- Title Row -->
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl" :class="$q.dark.isActive ? 'text-white' : ''">{{ course.name }}</div>
                <div class="text-subtitle2 text-primary">{{ course.batch?.name }} - {{ course.subject?.name }}</div>
                
                <!-- Info -->
                <div class="text-subtitle2 row items-center q-mt-sm" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                  <q-icon name="person" size="xs" class="q-mr-xs" />
                  {{ course.teacher?.name }}
                </div>
                <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                  <q-icon name="meeting_room" class="q-mr-xs" />
                  {{ course.hall?.name || 'No Hall' }}
                </div>
                <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                  <q-icon name="group" class="q-mr-xs" />
                  {{ course.students_count || 0 }} Students
                </div>
                <!-- Schedule -->
                <div class="text-caption row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
                  <q-icon name="schedule" size="xs" class="q-mr-xs" />
                  {{ formatSchedule(course.schedule) }}
                </div>
             </q-card-section>

             <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
             <q-card-actions align="right">
               <q-btn unelevated color="primary" label="Enter Class" size="sm" icon-right="login" />
             </q-card-actions>
           </q-card>
        </div>
      </div>
    </div>

    <!-- Upcoming Extra Classes Section -->
    <div class="q-mb-xl" v-if="upcomingExtraCourses.length > 0">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-orange-4' : 'text-orange-9'">Upcoming Extra Classes</div>
      </div>
      
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-4" v-for="course in upcomingExtraCourses" :key="course.id">
           <q-card class="my-card no-shadow hover-effect full-height column border-top-orange" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
             <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip :color="$q.dark.isActive ? 'orange-9' : 'orange-1'" :text-color="$q.dark.isActive ? 'white' : 'orange'" size="xs" icon="star">Upcoming</q-chip>
                </div>
                
                <div class="text-h6 text-weight-bold q-pr-xl" :class="$q.dark.isActive ? 'text-white' : ''">{{ course.name }}</div>
                
                <q-badge color="blue" transparent class="q-mt-xs">
                    For: {{ course.parent_course?.name }}
                </q-badge>
                
                <div class="q-mt-md text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                   <q-icon name="meeting_room" class="q-mr-xs" /> {{ course.hall?.name || 'No Hall' }}
                </div>
                
                <div class="text-caption q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                   <q-icon name="event" size="xs" class="q-mr-xs" />
                   {{ course.schedule?.date }}
                   <span class="q-mx-xs">|</span>
                   <q-icon name="schedule" size="xs" class="q-mr-xs" />
                   {{ course.schedule?.start }} - {{ course.schedule?.end }}
                </div>

                <div class="q-mt-sm text-weight-bold" :class="$q.dark.isActive ? 'text-green-4' : 'text-green-8'">
                   Fee: LKR {{ course.fee_amount }}
                </div>

             </q-card-section>
           </q-card>
        </div>
      </div>
    </div>

    <!-- Past Extra Classes Section -->
    <div class="q-mb-xl" v-if="pastExtraCourses.length > 0">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">Past Extra Classes</div>
      </div>
      
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-4" v-for="course in pastExtraCourses" :key="course.id">
           <q-card class="my-card no-shadow full-height column" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-1'">
             <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip :color="$q.dark.isActive ? 'grey-8' : 'grey-3'" :text-color="$q.dark.isActive ? 'grey-4' : 'grey-8'" size="xs" icon="history">Ended</q-chip>
                </div>
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ course.name }}</div>
                <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-grey-6' : 'text-grey-6'">For: {{ course.parent_course?.name }}</div>
                
                <div class="text-subtitle2 row items-center q-mt-sm" :class="$q.dark.isActive ? 'text-grey-6' : 'text-grey-6'">
                  <q-icon name="event" size="xs" class="q-mr-xs" />
                  {{ course.schedule?.date }}
                </div>
             </q-card-section>
           </q-card>
        </div>
      </div>
    </div>

    <!-- All Available Classes -->
    <div>
       <div class="row items-center justify-between q-mb-md">
          <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">All Available Classes</div>
       </div>
       
       <q-scroll-area horizontal style="height: 320px;" class="bg-transparent">
          <div class="row no-wrap q-gutter-md">
             <div v-for="rec in allCourses" :key="rec.id" style="width: 300px">
                <q-card class="my-card no-shadow full-height flex column" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                   <q-card-section class="col-grow relative-position">
                     <div class="absolute-top-right q-pa-sm">
                        <q-chip :color="$q.dark.isActive ? 'green-10' : 'green-1'" :text-color="$q.dark.isActive ? 'green-1' : 'green'" size="xs">Open</q-chip>
                     </div>
                     <div class="text-h6 text-weight-bold ellipsis q-pr-xl" :class="$q.dark.isActive ? 'text-white' : ''" :title="rec.name">{{ rec.name }}</div>
                     <div class="text-subtitle2 text-primary">{{ rec.batch?.name }} - {{ rec.subject?.name }}</div>

                     <div class="text-subtitle2 row items-center q-mt-sm" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                        <q-icon name="person" size="xs" class="q-mr-xs"/> {{ rec.teacher?.name }}
                     </div>
                     <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                        <q-icon name="meeting_room" size="xs" class="q-mr-xs"/> {{ rec.hall?.name || 'No Hall' }}
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
import html2canvas from 'html2canvas'

const $q = useQuasar()
const authStore = useAuthStore()
const idCardRef = ref(null)

async function downloadIdCard() {
    if(!idCardRef.value) return
    $q.loading.show({ message: 'Generating ID Card...' })
    try {
        const canvas = await html2canvas(idCardRef.value, {
            backgroundColor: null,
            scale: 2,
            useCORS: true
        })
        const link = document.createElement('a')
        link.download = `Student_ID_${displayId.value || 'student'}.png`
        link.href = canvas.toDataURL('image/png')
        link.click()
        $q.notify({ type: 'positive', message: 'ID Card Downloaded!' })
    } catch (e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Failed to generate ID' })
    } finally {
        $q.loading.hide()
    }
}
const studentStore = useStudentStore()
const { myCourses, allCourses, loading } = storeToRefs(studentStore)

const effectiveStudent = computed(() => {
    if(authStore.user?.role === 'parent') {
        return authStore.selectedChild || null // Fallback to null if no child selected
    }
    return authStore.user // Normal student login
})

const displayId = computed(() => {
    const id = effectiveStudent.value?.username || '0000'
    // Visual patch: Replace TCH with STD if it appears on student profile
    if (id && id.startsWith('TCH')) {
        return id.replace('TCH', 'STD')
    }
    return id
})

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



function hasRegularClass(date) {
    const classes = getClassesForDate(date)
    return classes.some(c => (!c.type || c.type === 'regular'))
}

function hasExtraClass(date) {
    const classes = getClassesForDate(date)
    return classes.some(c => c.type === 'extra')
}

// Updated getDayClass to be cleaner since we use custom indicators
function getDayClass(scope) {
    if (!scope) return ''
    if (scope.selected) return 'bg-primary text-white'
    
    const today = new Date().toISOString().slice(0, 10).replace(/-/g, '/')
    if (scope.date === today) return 'border-today'

    return ''
}

function isEnrolled(courseId) {
    if (!myCourses.value) return false
    return myCourses.value.some(c => c.id === courseId)
}

onMounted(() => {
    if (authStore.user) {
        studentStore.fetchMyCourses()
        studentStore.fetchAllCourses()
    }
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
