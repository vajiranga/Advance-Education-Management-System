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
                <div class="text-h5 text-weight-bold">Kasun Perera</div>
                <div class="text-subtitle1 text-grey-7">Grade 10 - 2026 A/L Batch</div>
                <q-chip size="sm" color="blue-1" text-color="primary" icon="school">
                  Science Stream
                </q-chip>
              </div>
            </div>
          </div>
          
          <!-- Barcode Section -->
          <div class="col-12 col-md-4 text-center">
             <div class="bg-grey-2 q-pa-sm rounded-borders inline-block">
               <!-- Simulated Barcode -->
               <div style="font-family: 'Libre Barcode 39', sans-serif; font-size: 48px; line-height: 1;">
                 *STU8821*
               </div>
               <div class="text-caption text-weight-bold letter-spacing-2 q-mt-xs">STU-8821</div>
             </div>
             <div class="text-caption text-grey q-mt-sm">Scan at Entrance</div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- My Courses -->
    <div class="q-mb-xl">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold">My Courses</div>
        <q-btn flat color="primary" label="View All" to="/student/courses" no-caps />
      </div>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-4" v-for="course in myCourses" :key="course.id">
           <q-card class="my-card no-shadow border-light hover-effect">
             <q-img :src="course.image" height="150px">
               <div class="absolute-bottom text-subtitle2">
                 {{ course.subject }}
               </div>
             </q-img>
             <q-card-section>
               <div class="text-h6 ellipsis">{{ course.name }}</div>
               <div class="text-caption text-grey">{{ course.teacher }}</div>
             </q-card-section>
             <q-separator />
             <q-card-actions align="right">
                <q-chip color="green-1" text-color="green" size="sm" icon="schedule">
                  {{ course.schedule }}
                </q-chip>
                <q-btn flat color="primary" label="Enter Class" />
             </q-card-actions>
           </q-card>
        </div>
      </div>
    </div>

    <!-- Recommended for You -->
    <div>
       <div class="row items-center justify-between q-mb-md">
          <div class="text-h6 text-weight-bold">Recommended for Grade 10</div>
          <q-btn flat color="primary" label="Browse All" no-caps />
       </div>
       
       <q-scroll-area horizontal style="height: 260px;" class="bg-transparent">
          <div class="row no-wrap q-gutter-md">
             <div v-for="rec in recommendedCourses" :key="rec.id" style="width: 280px">
                <q-card class="my-card no-shadow border-light bg-white">
                   <q-img :src="rec.image" height="120px" />
                   <q-card-section>
                      <div class="text-subtitle1 text-weight-bold ellipsis">{{ rec.name }}</div>
                      <div class="text-caption text-grey">{{ rec.teacher }}</div>
                      <div class="q-mt-sm text-primary text-weight-bold">LKR {{ rec.fee }}</div>
                   </q-card-section>
                   <q-card-actions>
                      <q-btn unelevated color="primary" class="full-width" label="Enroll Now" size="sm" />
                   </q-card-actions>
                </q-card>
             </div>
          </div>
       </q-scroll-area>
    </div>

  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const myCourses = ref([
  { id: 1, name: 'Grade 10 Mathematics', subject: 'Mathematics', teacher: 'Mr. Bandara', schedule: 'Sat 8:00 AM', image: 'https://cdn.quasar.dev/img/parallax2.jpg' },
  { id: 2, name: 'Grade 10 Science', subject: 'Science', teacher: 'Mrs. Silva', schedule: 'Sun 10:00 AM', image: 'https://cdn.quasar.dev/img/quasar.jpg' }
])

const recommendedCourses = ref([
  { id: 3, name: 'Grade 10 English', teacher: 'Mr. Perera', fee: '1500', image: 'https://cdn.quasar.dev/img/mountains.jpg' },
  { id: 4, name: 'Grade 10 History', teacher: 'Ms. Kamala', fee: '1200', image: 'https://cdn.quasar.dev/img/parallax1.jpg' },
  { id: 5, name: 'Grade 10 ICT', teacher: 'Mr. Tech', fee: '2000', image: 'https://cdn.quasar.dev/img/cat.jpg' }
])
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap');

.letter-spacing-2 {
  letter-spacing: 2px;
}
.border-light {
  border: 1px solid #e0e0e0;
}
.hover-effect:hover {
  transform: translateY(-2px);
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>
