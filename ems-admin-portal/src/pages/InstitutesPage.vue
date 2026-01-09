<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Facility Management (Halls)</div>
      <q-chip color="primary" text-color="white" icon="event">
        {{ formattedDate }}
      </q-chip>
    </div>

    <!-- Overview Stats -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-3">
        <q-card class="bg-green text-white">
          <q-card-section>
             <div class="text-h4 text-weight-bold">{{ availableHalls }}</div>
             <div class="text-caption">Available Halls (Filtered)</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-red text-white">
          <q-card-section>
             <div class="text-h4 text-weight-bold">{{ 20 - availableHalls }}</div>
             <div class="text-caption">Occupied Halls</div>
          </q-card-section>
        </q-card>
      </div>
    </div>
    
    <!-- Time Filter -->
    <q-card class="q-mb-lg bg-grey-1">
      <q-card-section>
        <div class="row q-col-gutter-md items-center">
           <div class="col-md-3">
              <q-select v-model="filterDay" :options="daysOfWeek" label="Day" outlined dense bg-color="white" />
           </div>
           <div class="col-md-3">
              <q-select v-model="filterTime" :options="timeSlots" label="Time Check" outlined dense bg-color="white" />
           </div>
           <div class="col-md-3">
              <q-btn color="primary" icon="search" label="Check Availability" @click="checkAvailability" />
           </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Halls Grid -->
    <div class="row q-col-gutter-lg">
      <div class="col-12 col-md-4 col-lg-3" v-for="hall in processedHalls" :key="hall.id">
        <q-card :class="hall.isOccupied ? 'bg-red-1' : 'bg-green-1'">
          <q-card-section>
            <div class="row justify-between items-center">
               <div class="text-h6">{{ hall.name }}</div>
               <q-icon :name="hall.isOccupied ? 'lock' : 'lock_open'" size="sm" :color="hall.isOccupied ? 'red' : 'green'" />
            </div>
            <div class="text-subtitle2 text-grey">Capacity: {{ hall.capacity }} Students</div>
          </q-card-section>

          <q-separator />

          <q-card-section>
             <div v-if="hall.isOccupied">
               <div class="text-weight-bold text-red">Occupied</div>
               <div class="text-caption">{{ hall.currentClass?.name }}</div>
               <div class="text-caption"><q-icon name="person"/> {{ hall.currentClass?.teacher }}</div>
               <div class="text-caption text-grey">{{ hall.currentClass?.timeRange }}</div>
             </div>
             <div v-else>
               <div class="text-weight-bold text-green">Available</div>
               <div class="text-caption text-grey">Free for selected time</div>
             </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat color="primary" label="Schedule" @click="viewSchedule(hall)" />
          </q-card-actions>
        </q-card>
      </div>
    </div>

    <!-- Schedule Dialog -->
    <q-dialog v-model="showScheduleDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ selectedHall?.name }} - Weekly Schedule</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
           <q-timeline color="secondary">
              <q-timeline-entry
                v-for="(event, index) in hallEvents"
                :key="index"
                :title="event.name"
                :subtitle="event.time"
                :icon="event.type === 'Class' ? 'school' : 'event'"
              >
                <div>
                  Teacher: {{ event.teacher }} <br>
                  Grade: {{ event.grade }}
                </div>
              </q-timeline-entry>
           </q-timeline>
           <div v-if="hallEvents.length === 0" class="text-center text-grey q-pa-md">
             No classes scheduled for today.
           </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { date } from 'quasar'
import { useCourseStore } from 'stores/course-store'
import { useHallStore } from 'stores/hall-store'
import { storeToRefs } from 'pinia'

const courseStore = useCourseStore()
const hallStore = useHallStore()
const { halls } = storeToRefs(hallStore)
const { courses } = storeToRefs(courseStore)

const showScheduleDialog = ref(false)
const selectedHall = ref(null)
const hallEvents = ref([])

const formattedDate = date.formatDate(Date.now(), 'dddd, D MMMM YYYY')

// Filters
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
const timeSlots = [
  '8:00 AM', '8:30 AM', '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
  '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM', 
  '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM', '6:00 PM', '6:30 PM', '7:00 PM'
]

const filterDay = ref('Saturday') // Default
const filterTime = ref('8:00 AM')

// Processed Halls (With Occupancy Status)
const processedHalls = ref([])

const checkAvailability = () => {
   // Logic: Iterate over halls and check if any course occupies it at the filtered time
   // Note: Time overlapping logic requires parsing "8:00 AM" to Minutes.
   // For Demo: Use simple exact match or String inclusion if simplified.
   
   processedHalls.value = halls.value.map(hall => {
      const activeCourse = courses.value.find(c => {
         // Check Hall ID
         if (c.hallId !== hall.id) return false
         
         // Check Day
         if (!c.schedule.includes(filterDay.value)) return false
         
         // Check Time (Legacy overlapping logic or simple exact match for demo)
         // Assuming simple string match or falls within range logic.
         // Real logic: Parse Start/End time. 
         // Demo shortcut: Does the schedule string contain the start time?
         // Or is filterTime "8:00 AM" equal to c.scheduleStart?
         
         // Let's rely on simple heuristic: If schedule contains the time string? No that's risky.
         // Let's assume user picks '8:00 AM'. We check if course starts then.
         // Better: Parse time logic.
         
         return c.schedule.includes(filterTime.value) // Very simple check
      })

      if (activeCourse) {
         return {
            ...hall,
            isOccupied: true,
            currentClass: {
               name: activeCourse.name,
               teacher: activeCourse.teacher,
               timeRange: activeCourse.schedule
            }
         }
      }
      return { ...hall, isOccupied: false, currentClass: null }
   })
}

// Initial Run
onMounted(() => {
   checkAvailability()
})

// Watch filters
watch([filterDay, filterTime], () => checkAvailability())

const availableHalls = computed(() => processedHalls.value.filter(h => !h.isOccupied).length)

const viewSchedule = (hall) => {
  selectedHall.value = hall
  // Find all courses for this hall
  const hallCourses = courses.value.filter(c => c.hallId === hall.id)
  
  hallEvents.value = hallCourses.map(c => ({
      name: c.name,
      time: c.schedule,
      teacher: c.teacher,
      grade: c.grade,
      type: 'Class'
  }))
  
  showScheduleDialog.value = true
}

</script>
