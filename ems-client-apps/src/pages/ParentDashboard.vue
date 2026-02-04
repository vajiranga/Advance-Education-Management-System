<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <!-- Header: Child Selector -->
    <!-- Header: Child Info (Simplified) -->
    <div
      class="row items-center justify-between q-mb-lg q-pa-md rounded-borders shadow-1"
      :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'"
    >
      <div>
        <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">
          Viewing details for:
        </div>
        <div
          class="text-h6 text-weight-bold"
          :class="$q.dark.isActive ? 'text-white' : 'text-primary'"
        >
          <template v-if="selectedChild">{{ selectedChild.name }}</template>
          <template v-else>No Child Selected</template>
        </div>
      </div>
      <!-- Switch Child Removed (Administered via Sidebar/Layout) -->
    </div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-lg" v-if="selectedChild">
      <div class="col-12 col-md-4">
        <q-card
          class="text-white text-center q-py-sm height-100"
          :class="$q.dark.isActive ? 'bg-indigo-9' : 'bg-indigo-6'"
        >
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ stats.attendance }}%</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-indigo-2' : 'text-indigo-1'">
              Avg. Attendance
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card
          class="text-white text-center q-py-sm height-100"
          :class="$q.dark.isActive ? 'bg-orange-9' : 'bg-orange-6'"
        >
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR {{ stats.due_fees }}</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-orange-2' : 'text-orange-1'">
              Due Payments
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card
          class="text-white text-center q-py-sm height-100"
          :class="$q.dark.isActive ? 'bg-green-9' : 'bg-green-6'"
        >
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ stats.last_grade }}</div>
            <div
              class="text-subtitle2"
              v-if="stats.last_exam_subject && stats.last_exam_subject !== 'N/A'"
            >
              {{ stats.last_exam_subject }}
            </div>
            <div
              class="text-subtitle2"
              v-if="stats.last_exam_marks && stats.last_exam_marks !== '-'"
            >
              Marks: {{ stats.last_exam_marks }}
            </div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-green-2' : 'text-green-1'">
              Last Exam Result
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Detailed Sections -->
    <div class="row q-col-gutter-lg" v-if="selectedChild">
      <!-- Child's Class Schedule -->
      <div class="col-12 col-md-8">
        <!-- Replaced with Student App Style Schedule -->
        <div class="q-mb-lg">
           <div class="text-h6 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">{{ selectedChild?.name }}'s Class Schedule</div>
           <div class="flex justify-center">
               <q-date
                 v-model="selectedDate"
                 :key="childCourses.length"
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
      </div>

      <!-- Notifications / Feed -->
      <div class="col-12 col-md-4">
        <q-card
          class="full-height"
          :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'"
        >
          <q-card-section>
            <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Recent Activity</div>
          </q-card-section>
          <q-list separator>
            <q-item v-for="(act, idx) in stats.recent_activity" :key="idx">
              <q-item-section avatar>
                <q-icon
                  :name="act.status === 'present' ? 'check_circle' : 'warning'"
                  :color="act.status === 'present' ? 'green' : 'red'"
                />
              </q-item-section>
              <q-item-section>
                <q-item-label
                  class="text-weight-bold"
                  :class="$q.dark.isActive ? 'text-white' : ''"
                  >{{ act.title }}</q-item-label
                >
                <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">{{
                  new Date(act.date).toLocaleDateString()
                }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="stats.recent_activity.length === 0">
              <q-item-section class="text-center text-grey">No recent activity</q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>
    </div>

    <!-- Enrolled Courses List -->
    <div class="q-mt-lg" v-if="selectedChild && childCourses.length > 0">
      <div class="text-h6 text-weight-bold q-mb-md" :class="$q.dark.isActive ? 'text-white' : ''">
        Enrolled Courses
      </div>
      <div class="row q-col-gutter-md">
        <!-- Filter out Extra classes from this view, show only Regular courses -->
        <div class="col-12 col-sm-6 col-md-4" v-for="course in regularCourses" :key="course.id">
          <q-card
            class="no-shadow full-height"
            :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'"
          >
            <q-card-section>
              <div
                class="text-h6 ellipsis"
                :class="$q.dark.isActive ? 'text-white' : 'text-primary'"
                :title="course.name"
              >
                {{ course.name }}
              </div>
              <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">
                {{ course.subject?.name }} | {{ course.batch?.name }}
              </div>
              <div class="row items-center q-mt-sm">
                <q-avatar size="24px" class="q-mr-sm" color="primary" text-color="white">
                  <span class="text-caption text-weight-bold">{{
                    course.teacher?.name?.charAt(0) || 'T'
                  }}</span>
                </q-avatar>
                <div :class="$q.dark.isActive ? 'text-grey-3' : 'text-grey-8'">
                  {{ course.teacher?.name }}
                </div>
              </div>
            </q-card-section>
            <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
            <q-card-actions align="right">
              <q-chip
                size="sm"
                :color="$q.dark.isActive ? 'blue-9' : 'blue-1'"
                :text-color="$q.dark.isActive ? 'blue-1' : 'primary'"
              >
                {{ formatSchedule(course.schedule) }}
              </q-chip>
            </q-card-actions>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Date Details Dialog -->
    <q-dialog v-model="showDateDialog">
        <q-card style="min-width: 350px">
            <q-card-section class="row items-center justify-between text-white" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-primary'">
                <div class="text-h6">{{ formatDateHeader(selectedDate) }}</div>
                <q-btn flat round dense icon="close" v-close-popup />
            </q-card-section>
            <q-card-section class="q-pa-md" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
                <div v-if="selectedDateDetails.length === 0" class="text-grey text-center q-py-md">
                    No classes scheduled for this date.
                </div>
                <q-list separator v-else>
                     <q-item v-for="cls in selectedDateDetails" :key="cls.id">
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
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { api } from 'boot/axios'
import { useAuthStore } from 'stores/auth-store' // Added Import
const authStore = useAuthStore()

// Use computed directly from store instead of local ref
const selectedChild = computed(() => authStore.selectedChild)

const stats = ref({
  attendance: 0,
  rank: '-',
  due_fees: 0,
  last_grade: '-',
  recent_activity: [],
})
const loading = ref(true)

// Calendar State (Mocked scheduled for now, future enhance)
const selectedDate = ref(new Date().toISOString().slice(0, 10).replace(/-/g, '/'))

onMounted(async () => {
  loading.value = true
  try {
    // We rely on Layout to fetch children usually, but okay to fetch here for safety
    // But mainly we react to selectedChild availability
    if (selectedChild.value) {
      fetchChildStats(selectedChild.value.id)
      fetchChildCourses(selectedChild.value.id)
    }
  } catch (e) {
    console.error('Failed to init dashboard', e)
  } finally {
    loading.value = false
  }
})

const childCourses = ref([])
const selectedDateDetails = ref([])
const showDateDialog = ref(false)

// Watch for child switch in Store
watch(
  selectedChild,
  (newVal) => {
    if (newVal) {
      fetchChildStats(newVal.id)
      fetchChildCourses(newVal.id)
    }
  },
  { immediate: true },
) // Trigger immediately if already set

async function fetchChildStats(childId) {
  try {
    const res = await api.get(`/v1/parent/children/${childId}/stats`)
    stats.value = res.data
  } catch (e) {
    console.error(e)
  }
}

async function fetchChildCourses(childId) {
  try {
    const res = await api.get(`/v1/parent/children/${childId}/courses`)
    childCourses.value = res.data
  } catch (e) {
    console.error(e)
  }
}

// Calendar Logic
const regularCourses = computed(() =>
  (childCourses.value || []).filter((c) => c.type === 'regular' || !c.type),
)
const extraCourses = computed(() => (childCourses.value || []).filter((c) => c.type === 'extra'))

function getClassesForDate(dateStr) {
  if (!childCourses.value) return []

  // Safe parse YYYY/MM/DD
  const parts = dateStr.split('/')
  const y = parseInt(parts[0])
  const m = parseInt(parts[1])
  const d = parseInt(parts[2])
  const date = new Date(y, m - 1, d)

  // Manual Day Lookup
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const dayName = days[date.getDay()]
  
  const normalizedDate = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`

  const regular = regularCourses.value.filter((c) => {
    let s = c.schedule
    if (typeof s === 'string') {
      try {
        s = JSON.parse(s)
      } catch {
        return false
      }
    }
    if (!s) return false

     // Match Recurring Day
     if (s.day && s.day === dayName) return true
     
     // Match Specific Date (if regular one-off)
     if (s.date && s.date === normalizedDate) return true

    return false
  })

  // Filter Extra Classes
  const extra = extraCourses.value.filter((c) => {
    let s = c.schedule
    if (typeof s === 'string') {
      try {
        s = JSON.parse(s)
      } catch {
        return false
      }
    }
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
  selectedDate.value = date
  selectedDateDetails.value = getClassesForDate(date)
  showDateDialog.value = true
}

function hasRegularClass(date) {
  const classes = getClassesForDate(date)
  return classes.some((c) => !c.type || c.type === 'regular')
}

function hasExtraClass(date) {
  const classes = getClassesForDate(date)
  return classes.some((c) => c.type === 'extra')
}

function getDayClass(scope) {
  if (!scope) return ''
  if (scope.selected) return 'bg-primary text-white'
  const today = new Date().toISOString().slice(0, 10).replace(/-/g, '/')
  if (scope.date === today) return 'border-today'
  return ''
}

// Helper Functions
const formatDateHeader = (dateStr) => {
  if (!dateStr) return 'Select a date'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatSchedule = (schedule) => {
  if (!schedule) return 'TBA'
  if (typeof schedule === 'string') return schedule
  if (schedule.type === 'one-off') return `${schedule.start} - ${schedule.end}`
  if (schedule.day) return `${schedule.start} - ${schedule.end}`
  return 'Recurring'
}
</script>
<style scoped>
.rounded-circle {
  border-radius: 50% !important;
}
.border-today {
  border: 1px solid #1976d2;
  border-radius: 50%;
}
</style>
   
 