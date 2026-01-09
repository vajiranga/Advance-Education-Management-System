<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Welcome Section -->
    <div class="q-mb-lg">
       <div class="text-h5 text-weight-bold text-teal-9">Teacher Dashboard</div>
       <div class="text-subtitle2 text-grey-7">Good Morning, Mr. Bandara 👋</div>
    </div>

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
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-white text-blue border-bottom-blue no-shadow">
          <q-card-section>
             <div class="row items-center justify-between">
                <div>
                   <div class="text-h4 text-weight-bold">92%</div>
                   <div class="text-caption text-grey">Avg. Attendance</div>
                </div>
                <q-avatar color="blue-1" text-color="blue" icon="fact_check" font-size="24px" />
             </div>
          </q-card-section>
        </q-card>
      </div>
       <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-white text-purple border-bottom-purple no-shadow">
          <q-card-section>
             <div class="row items-center justify-between">
                <div>
                   <div class="text-h4 text-weight-bold">05</div>
                   <div class="text-caption text-grey">Pending Papers</div>
                </div>
                <q-avatar color="purple-1" text-color="purple" icon="assignment_turned_in" font-size="24px" />
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
                <q-chip outline color="teal" label="Thursday, 8th Jan" icon="calender_today" />
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
                      <q-btn outline class="full-width" color="teal" icon="add" label="New Class" stack />
                   </div>
                   <div class="col-6">
                      <q-btn outline class="full-width" color="orange" icon="person_add" label="Add Student" stack />
                   </div>
                   <div class="col-6">
                      <q-btn outline class="full-width" color="blue" icon="upload" label="Upload Marks" stack />
                   </div>
                   <div class="col-6">
                      <q-btn outline class="full-width" color="purple" icon="notifications" label="Send Notice" stack />
                   </div>
                </div>
             </q-card-section>
          </q-card>
       </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const schedule = ref([
 { id: 1, time: '08:00 AM', subject: 'Mathematics', batch: 'Grade 10 - A', hall: 'Main Hall', status: 'completed' },
 { id: 2, time: '10:30 AM', subject: 'Mathematics', batch: 'Grade 11 - Revision', hall: 'Hall 02', status: 'upcoming' },
 { id: 3, time: '02:00 PM', subject: 'Combined Maths', batch: '2026 A/L', hall: 'Auditorium', status: 'upcoming' },
])
</script>

<style scoped>
.border-light { border: 1px solid #eee; }
.border-bottom-teal { border-bottom: 3px solid teal; }
.border-bottom-orange { border-bottom: 3px solid orange; }
.border-bottom-blue { border-bottom: 3px solid #2196F3; }
.border-bottom-purple { border-bottom: 3px solid #9C27B0; }
</style>
