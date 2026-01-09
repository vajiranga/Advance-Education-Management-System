<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold">My Courses</div>
        <div class="text-caption text-grey-7">Manage your enrolled classes and learning progress</div>
      </div>
      <q-btn icon="add" label="Enroll New Course" color="primary" flat />
    </div>

    <!-- Tabs -->
    <q-tabs
      v-model="tab"
      dense
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
      class="text-grey q-mb-md"
    >
      <q-tab name="active" label="Active Courses" />
      <q-tab name="completed" label="Completed" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      <q-tab-panel name="active" class="q-pa-none">
        
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-md-6 col-lg-4" v-for="course in courses" :key="course.id">
            <q-card class="course-card border-light no-shadow">
              <q-img :src="course.image" height="160px">
                <div class="absolute-top-right bg-transparent">
                  <q-chip color="white" text-color="primary" icon="circle" size="sm" v-if="course.isLive">
                    LIVE NOW
                  </q-chip>
                </div>
              </q-img>
              
              <q-card-section>
                 <div class="row justify-between items-start">
                    <div>
                       <div class="text-h6 text-weight-bold ellipsis">{{ course.title }}</div>
                       <div class="text-subtitle2 text-grey-7">{{ course.teacher }}</div>
                    </div>
                 </div>
                 
                 <div class="q-mt-md">
                    <div class="row justify-between text-caption q-mb-xs">
                       <span>Attendance</span>
                       <span>{{ course.attendance }}%</span>
                    </div>
                    <q-linear-progress rounded size="6px" :value="course.attendance / 100" color="green" track-color="green-1" />
                 </div>
              </q-card-section>

              <q-separator />

              <q-card-section class="q-py-sm bg-grey-1">
                 <div class="row items-center justify-between text-caption">
                    <div class="row items-center text-grey-8">
                       <q-icon name="schedule" class="q-mr-xs" />
                       {{ course.schedule }}
                    </div>
                    <div class="text-primary text-weight-bold">
                       Next: {{ course.nextClass }}
                    </div>
                 </div>
              </q-card-section>

              <q-card-actions align="right">
                <q-btn flat color="secondary" icon="history" label="Recordings" />
                <q-btn unelevated color="primary" label="Enter Class" icon-right="login" />
              </q-card-actions>
            </q-card>
          </div>
        </div>

      </q-tab-panel>

      <q-tab-panel name="completed">
        <div class="text-center text-grey q-pa-xl">
           <q-icon name="school" size="64px" color="grey-4" />
           <div class="text-h6 q-mt-md">No completed courses yet</div>
        </div>
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const tab = ref('active')

const courses = ref([
 {
    id: 1,
    title: 'Grade 10 Mathematics',
    teacher: 'Mr. Bandara',
    image: 'https://cdn.quasar.dev/img/parallax2.jpg',
    attendance: 85,
    schedule: 'Saturdays 8:00 AM',
    nextClass: 'Tomorrow',
    isLive: false
 },
 {
    id: 2,
    title: 'Grade 10 Science',
    teacher: 'Mrs. Silva',
    image: 'https://cdn.quasar.dev/img/quasar.jpg',
    attendance: 92,
    schedule: 'Sundays 10:00 AM',
    nextClass: 'Sunday',
    isLive: false
 },
 {
    id: 3,
    title: 'English Revision',
    teacher: 'Mr. Perera',
    image: 'https://cdn.quasar.dev/img/mountains.jpg',
    attendance: 60,
    schedule: 'Mondays 4:00 PM',
    nextClass: 'Mon, 12th Jan',
    isLive: true
 }
])
</script>

<style scoped>
.course-card {
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}
.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.border-light {
   border: 1px solid #eee;
}
</style>
