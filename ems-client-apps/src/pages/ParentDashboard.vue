<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Header: Child Selector -->
    <div class="row items-center justify-between q-mb-lg bg-white q-pa-md rounded-borders shadow-1">
      <div>
        <div class="text-subtitle2 text-grey-7">Viewing details for:</div>
        <div class="text-h6 text-primary text-weight-bold">{{ selectedChild.name }}</div>
      </div>
      <q-btn-dropdown color="primary" flat label="Switch Child" icon="child_care">
        <q-list>
          <q-item v-for="child in children" :key="child.id" clickable v-close-popup @click="selectedChild = child">
            <q-item-section avatar>
              <q-avatar icon="face" color="primary" text-color="white" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ child.name }}</q-item-label>
              <q-item-label caption>{{ child.batch }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3">
        <q-card class="bg-indigo-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">85%</div>
            <div class="text-caption">Avg. Attendance</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="bg-purple-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">3rd</div>
            <div class="text-caption">Rank in Class</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="bg-orange-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR 5k</div>
            <div class="text-caption">Due Payments</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="bg-green-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">A</div>
            <div class="text-caption">Last Exam Grade</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Detailed Sections -->
    <div class="row q-col-gutter-lg">
      <!-- Child's Class Schedule -->\n      <div class="col-12 col-md-8">
        <q-card class="full-height">
          <q-card-section>
            <div class="text-h6">{{ selectedChild.name }}'s Class Schedule</div>
            <div class="text-caption text-grey-7">View upcoming classes and schedule</div>
          </q-card-section>
          <q-separator />
          <q-card-section>
            <div class="row q-col-gutter-md">
              <!-- Calendar -->
              <div class="col-12 col-md-7">
                <q-date
                  v-model="selectedDate"
                  :events="eventDates"
                  event-color="primary"
                  flat
                  minimal
                  class="full-width"
                />
              </div>
              
              <!-- Schedule Details for Selected Date -->
              <div class="col-12 col-md-5">
                <div class="q-pa-md bg-grey-1 rounded-borders" style="min-height: 300px;">
                  <div class="text-subtitle1 text-weight-bold q-mb-md">
                    {{ formatDateHeader(selectedDate) }}
                  </div>
                  
                  <div v-if="getClassesForDate(selectedDate).length > 0">
                    <q-list separator>
                      <q-item v-for="cls in getClassesForDate(selectedDate)" :key="cls.id" class="q-pa-sm">
                        <q-item-section avatar>
                          <q-avatar color="primary" text-color="white" size="40px">
                            <q-icon name="schedule" />
                          </q-avatar>
                        </q-item-section>
                        <q-item-section>
                          <q-item-label class="text-weight-bold">{{ cls.subject }}</q-item-label>
                          <q-item-label caption>{{ cls.teacher }}</q-item-label>
                          <q-item-label caption class="text-primary">
                            <q-icon name="access_time" size="xs" /> {{ cls.time }}
                          </q-item-label>
                        </q-item-section>
                      </q-item>
                    </q-list>
                  </div>
                  
                  <div v-else class="text-center text-grey q-pa-xl">
                    <q-icon name="event_busy" size="48px" color="grey-4" />
                    <div class="text-subtitle2 q-mt-sm">No classes scheduled</div>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Notifications / Feed -->
      <div class="col-12 col-md-4">
        <q-card class="full-height">
          <q-card-section>
            <div class="text-h6">Recent Activity</div>
          </q-card-section>
          <q-list separator>
             <q-item v-for="n in 4" :key="n">
               <q-item-section avatar>
                 <q-icon :name="n % 2 == 0 ? 'warning' : 'check_circle'" :color="n % 2 == 0 ? 'red' : 'green'" />
               </q-item-section>
               <q-item-section>
                 <q-item-label class="text-weight-bold">{{ n % 2 == 0 ? 'Absent for Physics Class' : 'Attended Chemistry Class' }}</q-item-label>
                 <q-item-label caption>Yesterday at {{ 2 + n }}:00 PM</q-item-label>
               </q-item-section>
             </q-item>
          </q-list>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const children = [
  { id: 1, name: 'Kasun Perera', batch: '2026 A/L Physics' },
  { id: 2, name: 'Nimali Perera', batch: '2028 O/L' }
]

const selectedChild = ref(children[0])

// Calendar State
const selectedDate = ref('2026/01/09')
const eventDates = ['2026/01/09', '2026/01/05', '2026/01/06', '2026/01/10']

// Mock Class Data
const classesCache = {
  '2026/01/09': [
    { id: 1, subject: 'Physics (Theory)', teacher: 'Mr. Sarath', time: '08:30 AM - 10:30 AM' },
    { id: 2, subject: 'Chemistry (Revision)', teacher: 'Mrs. Silva', time: '11:00 AM - 01:00 PM' }
  ],
  '2026/01/05': [
    { id: 3, subject: 'Mathematics', teacher: 'Mr. Perera', time: '02:00 PM - 04:00 PM' }
  ],
  '2026/01/06': [
    { id: 4, subject: 'Biology', teacher: 'Dr. Gunawardena', time: '08:30 AM - 10:30 AM' }
  ]
}

// Helper Functions
const formatDateHeader = (dateStr) => {
  if (!dateStr) return 'Select a date'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}

const getClassesForDate = (dateStr) => {
  return classesCache[dateStr] || []
}
</script>
