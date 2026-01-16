<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <!-- Header: Child Selector -->
    <div class="row items-center justify-between q-mb-lg q-pa-md rounded-borders shadow-1" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
      <div>
        <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">Viewing details for:</div>
        <div class="text-h6 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">
            <template v-if="selectedChild">{{ selectedChild.name }}</template>
            <template v-else>No Child Selected</template>
        </div>
      </div>
      <q-btn-dropdown :color="$q.dark.isActive ? 'primary' : 'primary'" flat label="Switch Child" icon="child_care">
        <q-list :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black'">
          <q-item v-for="child in children" :key="child.id" clickable v-close-popup @click="selectedChild = child" :active-class="$q.dark.isActive ? 'bg-grey-8' : 'bg-blue-1'">
            <q-item-section avatar>
              <q-avatar icon="face" color="primary" text-color="white" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ child.name }}</q-item-label>
              <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">{{ child.grade }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-lg" v-if="selectedChild">
      <div class="col-6 col-md-3">
        <q-card class="text-white text-center q-py-sm" :class="$q.dark.isActive ? 'bg-indigo-9' : 'bg-indigo-6'">
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ stats.attendance }}%</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-indigo-2' : 'text-indigo-1'">Avg. Attendance</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="text-white text-center q-py-sm" :class="$q.dark.isActive ? 'bg-purple-9' : 'bg-purple-6'">
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ stats.rank }}</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-purple-2' : 'text-purple-1'">Rank in Class</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="text-white text-center q-py-sm" :class="$q.dark.isActive ? 'bg-orange-9' : 'bg-orange-6'">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR {{ stats.due_fees / 1000 }}k</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-orange-2' : 'text-orange-1'">Due Payments</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="text-white text-center q-py-sm" :class="$q.dark.isActive ? 'bg-green-9' : 'bg-green-6'">
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ stats.last_grade }}</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-green-2' : 'text-green-1'">Last Exam Grade</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Detailed Sections -->
    <div class="row q-col-gutter-lg" v-if="selectedChild">
      <!-- Child's Class Schedule -->
      <div class="col-12 col-md-8">
        <q-card class="full-height" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
          <q-card-section>
            <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">{{ selectedChild.name }}'s Class Schedule</div>
            <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">View upcoming classes and schedule</div>
          </q-card-section>
          <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
          <q-card-section>
            <div class="row q-col-gutter-md">
              <!-- Calendar -->
              <div class="col-12 col-md-7">
                <q-date
                  v-model="selectedDate"
                  flat
                  minimal
                  class="full-width"
                  :dark="$q.dark.isActive"
                  :color="$q.dark.isActive ? 'primary' : ''"
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
              
              <!-- Schedule Details for Selected Date -->
              <div class="col-12 col-md-5">
                <div class="q-pa-md rounded-borders full-height relative-position" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-1'">
                  <div class="text-subtitle1 text-weight-bold q-mb-md" :class="$q.dark.isActive ? 'text-white' : ''">
                    {{ formatDateHeader(selectedDate) }}
                  </div>
                  
                  <div v-if="selectedDateDetails.length === 0" class="text-center text-grey q-pa-xl absolute-center full-width">
                    <q-icon name="event_busy" size="48px" :color="$q.dark.isActive ? 'grey-7' : 'grey-4'" />
                    <div class="text-subtitle2 q-mt-sm" :class="$q.dark.isActive ? 'text-grey-5' : ''">No classes scheduled</div>
                  </div>

                  <q-list v-else separator class="q-mt-md">
                      <q-item v-for="cls in selectedDateDetails" :key="cls.id" class="q-px-none">
                          <q-item-section>
                              <q-item-label class="text-weight-bold">{{ cls.name }}</q-item-label>
                              <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">
                                  {{ cls.teacher?.name }}
                              </q-item-label>
                              <q-item-label caption>
                                  <q-icon name="schedule" size="xs" class="q-mr-xs"/>
                                  {{ formatSchedule(cls.schedule) }}
                              </q-item-label>
                          </q-item-section>
                          <q-item-section side>
                              <q-badge :color="cls.type === 'extra' ? 'orange' : 'blue'">
                                  {{ cls.type === 'extra' ? 'Extra' : 'Regular' }}
                              </q-badge>
                          </q-item-section>
                      </q-item>
                  </q-list>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Notifications / Feed -->
      <div class="col-12 col-md-4">
        <q-card class="full-height" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
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
                 <q-item-label class="text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ act.title }}</q-item-label>
                 <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">{{ new Date(act.date).toLocaleDateString() }}</q-item-label>
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
        <div class="text-h6 text-weight-bold q-mb-md" :class="$q.dark.isActive ? 'text-white' : ''">Enrolled Courses</div>
        <div class="row q-col-gutter-md">
            <!-- Filter out Extra classes from this view, show only Regular courses -->
            <div class="col-12 col-sm-6 col-md-4" v-for="course in regularCourses" :key="course.id">
                <q-card class="no-shadow full-height" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                    <q-card-section>
                        <div class="text-h6 ellipsis" :class="$q.dark.isActive ? 'text-white' : 'text-primary'" :title="course.name">{{ course.name }}</div>
                        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">
                            {{ course.subject?.name }} | {{ course.batch?.name }}
                        </div>
                        <div class="row items-center q-mt-sm">
                            <q-avatar size="24px" class="q-mr-sm">
                                <img :src="course.teacher?.avatar || 'https://cdn.quasar.dev/img/boy-avatar.png'">
                            </q-avatar>
                            <div :class="$q.dark.isActive ? 'text-grey-3' : 'text-grey-8'">{{ course.teacher?.name }}</div>
                        </div>
                    </q-card-section>
                    <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                    <q-card-actions align="right">
                        <q-chip size="sm" :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-1' : 'primary'">
                            {{ formatSchedule(course.schedule) }}
                        </q-chip>
                    </q-card-actions>
                </q-card>
            </div>
        </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { api } from 'boot/axios'
const children = ref([])
const selectedChild = ref(null)
const stats = ref({
    attendance: 0,
    rank: '-',
    due_fees: 0,
    last_grade: '-',
    recent_activity: []
})
const loading = ref(true)

// Calendar State (Mocked scheduled for now, future enhance)
const selectedDate = ref('2026/01/16') 

onMounted(async () => {
    loading.value = true
    try {
        const res = await api.get('/v1/parent/children')
        children.value = res.data
        if (children.value.length > 0) {
            selectedChild.value = children.value[0]
            fetchChildStats(selectedChild.value.id)
        } 
    } catch (e) {
        console.error('Failed to fetch children', e)
    } finally {
        loading.value = false
    }
})

const childCourses = ref([])
const selectedDateDetails = ref([])
const showDateDialog = ref(false)

// Watch for child switch
watch(selectedChild, (newVal) => {
    if(newVal) {
        fetchChildStats(newVal.id)
        fetchChildCourses(newVal.id)
    }
})

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
const regularCourses = computed(() => (childCourses.value || []).filter(c => c.type === 'regular' || !c.type))
const extraCourses = computed(() => (childCourses.value || []).filter(c => c.type === 'extra'))

function getClassesForDate(dateStr) {
    if (!childCourses.value) return []
    
    const parts = dateStr.split('/')
    const y = parseInt(parts[0])
    const m = parseInt(parts[1])
    const d = parseInt(parts[2])
    const date = new Date(y, m - 1, d)
    
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    const dayName = days[date.getDay()]
    const normalizedDate = `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`

    const regular = regularCourses.value.filter(c => {
         let s = c.schedule
         if (typeof s === 'string') { try { s = JSON.parse(s) } catch { return false } }
         if (!s) return false
         if (s.day && s.day === dayName) return true
         if (s.date && s.date === normalizedDate) return true
         return false
    })

    const extra = extraCourses.value.filter(c => {
         let s = c.schedule
         if (typeof s === 'string') { try { s = JSON.parse(s) } catch { return false } }
         return s?.date === normalizedDate
    })
    
    return [...regular, ...extra]
}

function onDateClick(date) {
    selectedDate.value = date
    selectedDateDetails.value = getClassesForDate(date)
    showDateDialog.value = true // We can use a dialog or just show in the side panel
}

function hasRegularClass(date) {
    const classes = getClassesForDate(date)
    return classes.some(c => (!c.type || c.type === 'regular'))
}

function hasExtraClass(date) {
    const classes = getClassesForDate(date)
    return classes.some(c => c.type === 'extra')
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
  return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

const formatSchedule = (schedule) => {
    if (!schedule) return 'TBA'
    if (typeof schedule === 'string') return schedule
    if (schedule.type === 'one-off') return `${schedule.start} - ${schedule.end}`
    if (schedule.day) return `${schedule.start} - ${schedule.end}`
    return 'Recurring'
}

</script>
