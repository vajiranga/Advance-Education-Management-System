<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Facility Management (Halls)</div>

      <div class="row q-gutter-sm items-center">
        <q-chip color="secondary" text-color="white" icon="event">
          {{ formattedDate }}
        </q-chip>
        <q-btn color="primary" icon="add_location" label="Add New Hall" @click="openAddDialog" />
      </div>
    </div>

    <!-- Overview Stats -->
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
            <div class="text-h4 text-weight-bold">{{ halls.length - availableHalls }}</div>
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
            <q-select
              v-model="filterDay"
              :options="daysOfWeek"
              label="Day"
              outlined
              dense
              bg-color="white"
            />
          </div>
          <div class="col-md-3">
            <q-select
              v-model="filterTime"
              :options="timeSlots"
              label="Time Check"
              outlined
              dense
              bg-color="white"
            />
          </div>
          <div class="col-md-3">
            <q-btn
              color="primary"
              icon="search"
              label="Check Availability"
              @click="checkAvailability"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <div v-if="hallStore.loading" class="row justify-center q-pa-lg">
      <q-spinner size="40px" color="primary" />
    </div>

    <!-- Halls Grid -->
    <div class="row q-col-gutter-lg items-stretch" v-else>
      <div class="col-12 col-md-4 col-lg-3 flex" v-for="hall in processedHalls" :key="hall.id">
        <q-card
          :class="hall.isOccupied ? 'bg-red-1' : 'bg-green-1'"
          class="my-card column full-width full-height justify-between"
        >
          <div>
            <q-card-section>
              <div class="row justify-between items-start">
                <div>
                  <div class="text-h6">{{ hall.name }}</div>
                  <div class="text-caption text-grey-8" v-if="hall.hall_number || hall.floor">
                    {{ hall.hall_number ? `No: ${hall.hall_number}` : '' }}
                    {{ hall.floor ? `| Floor: ${hall.floor}` : '' }}
                  </div>
                </div>
                <div class="column items-center">
                  <q-icon
                    :name="hall.isOccupied ? 'lock' : 'lock_open'"
                    size="sm"
                    :color="hall.isOccupied ? 'red' : 'green'"
                  />
                  <q-icon v-if="hall.has_ac" name="ac_unit" color="blue" size="xs" class="q-mt-xs"
                    ><q-tooltip>AC Available</q-tooltip></q-icon
                  >
                </div>
              </div>
              <div class="text-subtitle2 text-grey q-mt-sm">
                <q-icon name="group" /> Capacity: {{ hall.capacity }}
              </div>
            </q-card-section>

            <q-separator />

            <q-card-section class="col-grow">
              <div v-if="hall.isOccupied">
                <div class="text-weight-bold text-red">Occupied</div>
                <div class="text-caption text-weight-medium">{{ hall.currentClass?.name }}</div>
                <div class="text-caption">
                  <q-icon name="person" /> {{ hall.currentClass?.teacher }}
                </div>
                <div class="text-caption text-grey">{{ hall.currentClass?.timeRange }}</div>
              </div>
              <div v-else>
                <div class="text-weight-bold text-green">Available</div>
                <div class="text-caption text-grey">Free for selected time</div>
              </div>
            </q-card-section>
          </div>

          <q-card-actions align="right" class="q-mt-auto">
            <div class="row q-gutter-xs">
              <q-btn flat round color="grey" icon="edit" size="sm" @click="editHall(hall)">
                <q-tooltip>Edit Details</q-tooltip>
              </q-btn>
              <q-btn flat round color="negative" icon="delete" size="sm" @click="deleteHall(hall)">
                <q-tooltip>Delete Hall</q-tooltip>
              </q-btn>
              <q-btn flat color="primary" label="Schedule" @click="viewSchedule(hall)" />
            </div>
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
                <strong>{{ event.day }}</strong> <br />
                Teacher: {{ event.teacher }} <br />
                Grade: {{ event.grade }}
              </div>
            </q-timeline-entry>
          </q-timeline>
          <div v-if="hallEvents.length === 0" class="text-center text-grey q-pa-md">
            No classes scheduled for this hall.
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">{{ isEditMode ? 'Edit Hall' : 'Add New Hall' }}</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveHall" class="q-gutter-md">
            <q-input
              outlined
              v-model="form.name"
              label="Hall Name (Optional)"
              hint="e.g. Main Auditorium"
            />
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-input outlined v-model="form.hall_number" label="Hall Number" />
              </div>
              <div class="col-6">
                <q-input outlined v-model="form.floor" label="Floor Number" />
              </div>
            </div>
            <q-input
              outlined
              v-model="form.capacity"
              type="number"
              label="Student Capacity *"
              :rules="[(val) => val > 0 || 'Required']"
            />
            <q-toggle v-model="form.has_ac" label="Air Conditioned (AC)" />

            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancel" v-close-popup />
              <q-btn
                :label="isEditMode ? 'Update' : 'Sav Hall'"
                type="submit"
                color="primary"
                :loading="submitting"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { date, useQuasar } from 'quasar'
import { useCourseStore } from 'stores/course-store'
import { useHallStore } from 'stores/hall-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
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
  '8:00 AM',
  '8:30 AM',
  '9:00 AM',
  '9:30 AM',
  '10:00 AM',
  '10:30 AM',
  '11:00 AM',
  '11:30 AM',
  '12:00 PM',
  '12:30 PM',
  '1:00 PM',
  '1:30 PM',
  '2:00 PM',
  '2:30 PM',
  '3:00 PM',
  '3:30 PM',
  '4:00 PM',
  '4:30 PM',
  '5:00 PM',
  '5:30 PM',
  '6:00 PM',
  '6:30 PM',
  '7:00 PM',
]

const filterDay = ref('Saturday') // Default
const filterTime = ref('8:00 AM')

// Processed Halls (With Occupancy Status)
const processedHalls = ref([])

const checkAvailability = () => {
  processedHalls.value = halls.value.map((hall) => {
    const activeCourse = courses.value.find((c) => {
      if (c.hall_id !== hall.id) return false

      const schedule = c.schedule // assuming object { day, start, end } or similar

      // Simple check: If Day Matches
      if ((schedule?.day || 'Monday') !== filterDay.value) return false

      // In real world, convert times to minutes and check range.
      // Here, just checking if start time is remarkably close or matches
      if ((schedule?.start || '08:00') === convertTo24Hour(filterTime.value)) return true

      return false
    })

    if (activeCourse) {
      return {
        ...hall,
        isOccupied: true,
        currentClass: {
          name: activeCourse.name,
          teacher: activeCourse.teacher?.name || 'Unknown',
          timeRange: `${activeCourse.schedule?.start} - ${activeCourse.schedule?.end}`,
        },
      }
    }
    return { ...hall, isOccupied: false, currentClass: null }
  })
}

function convertTo24Hour(timeStr) {
  // very basic converter for '8:00 AM' -> '08:00'
  // This is just a helper for the demo logic
  const [time, modifier] = timeStr.split(' ')
  let [hours, minutes] = time.split(':')
  if (hours === '12') {
    hours = '00'
  }
  if (modifier === 'PM') {
    hours = parseInt(hours, 10) + 12
  }
  return `${String(hours).padStart(2, '0')}:${minutes}`
}

// Add/Edit Logic
const showAddDialog = ref(false)
const isEditMode = ref(false)
const submitting = ref(false)
const form = ref({
  id: null,
  name: '',
  hall_number: '',
  floor: '',
  capacity: 50,
  has_ac: false,
})

function openAddDialog() {
  isEditMode.value = false
  form.value = { name: '', hall_number: '', floor: '', capacity: 50, has_ac: false }
  showAddDialog.value = true
}

function editHall(hall) {
  isEditMode.value = true
  form.value = { ...hall }
  showAddDialog.value = true
}

async function saveHall() {
  submitting.value = true
  try {
    if (isEditMode.value) {
      await hallStore.updateHall(form.value.id, form.value)
      $q.notify({ type: 'positive', message: 'Hall Updated' })
    } else {
      await hallStore.addHall(form.value)
      $q.notify({ type: 'positive', message: 'Hall Created' })
    }
    showAddDialog.value = false
    checkAvailability() // Refresh view
  } catch (e) {
    console.error(e)
    $q.notify({ type: 'negative', message: 'Operation Failed' })
  } finally {
    submitting.value = false
  }
}

function deleteHall(hall) {
  $q.dialog({ title: 'Confirm', message: 'Delete this Hall?', cancel: true }).onOk(async () => {
    await hallStore.deleteHall(hall.id)
    $q.notify({ type: 'positive', message: 'Hall Deleted' })
    checkAvailability()
  })
}

// Initial Run
onMounted(async () => {
  await Promise.all([
    hallStore.fetchHalls(),
    courseStore.fetchCourses(), // Need courses to check occupancy
  ])
  checkAvailability() // Run initial check
})

// Watch filters
watch([filterDay, filterTime, halls, courses], () => checkAvailability())

const availableHalls = computed(() => {
  return processedHalls.value ? processedHalls.value.filter((h) => !h.isOccupied).length : 0
})

const viewSchedule = (hall) => {
  selectedHall.value = hall
  // Find all courses for this hall
  const hallCourses = courses.value.filter((c) => c.hall_id === hall.id)

  hallEvents.value = hallCourses.map((c) => ({
    name: c.name,
    day: c.schedule?.day || 'Unknown',
    time: `${c.schedule?.start || ''} - ${c.schedule?.end || ''}`,
    teacher: c.teacher?.name || 'Unknown',
    grade: c.batch?.name || '',
    type: 'Class',
  }))

  showScheduleDialog.value = true
}
</script>

<style scoped>
.my-card {
  transition: all 0.3s;
}
.my-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>
