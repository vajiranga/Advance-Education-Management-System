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

    <!-- Class Schedule Calendar -->
    <div class="q-mb-xl">
      <div class="text-h6 text-weight-bold q-mb-md">My Class Schedule</div>
      <q-card class="no-shadow border-light">
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
                      <q-item-section side>
                        <q-btn flat dense round icon="more_vert" size="sm">
                          <q-menu>
                            <q-list style="min-width: 120px">
                              <q-item clickable v-close-popup>
                                <q-item-section>View Details</q-item-section>
                              </q-item>
                              <q-item clickable v-close-popup>
                                <q-item-section>Enter Class</q-item-section>
                              </q-item>
                            </q-list>
                          </q-menu>
                        </q-btn>
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

    <!-- Your Classes -->
    <div class="q-mb-xl">
      <div class="row items-center justify-between q-mb-md">
        <div class="text-h6 text-weight-bold">Your Classes</div>
        <q-btn flat color="primary" label="View All" to="/student/courses" no-caps />
      </div>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-sm-6 col-md-4" v-for="course in myCourses" :key="course.id">
           <q-card class="my-card no-shadow border-light hover-effect">
             <q-img :src="course.image" ratio="4/5">
               <div class="absolute-top-left q-pa-sm">
                 <q-chip :color="course.type === 'Physical' ? 'blue-1' : course.type === 'Hybrid' ? 'orange-1' : 'green-1'" 
                         :text-color="course.type === 'Physical' ? 'blue' : course.type === 'Hybrid' ? 'orange' : 'green'" 
                         size="sm">
                   {{ course.type }}
                 </q-chip>
               </div>
               <div class="absolute-bottom full-width bg-gradient-to-top q-pa-md">
                 <div class="text-h6 text-weight-bold text-white">{{ course.name }}</div>
               </div>
             </q-img>
             <q-card-section class="q-pb-sm">
               <div class="text-subtitle2 text-weight-medium">{{ course.title }}</div>
               <div class="text-caption text-grey row items-center q-mt-xs">
                 <q-icon name="person" size="xs" class="q-mr-xs" />
                 {{ course.teacher }}
               </div>
               <div class="text-caption text-grey row items-center q-mt-xs">
                 <q-icon name="schedule" size="xs" class="q-mr-xs" />
                 {{ course.schedule }}
               </div>
               <div class="text-caption text-primary text-weight-bold q-mt-xs">
                 Fee: LKR {{ course.fee }}
               </div>
               
               <div class="q-mt-sm">
                 <div class="row justify-between text-caption q-mb-xs">
                   <span>Enrollment: {{ course.enrolled }} / {{ course.capacity }}</span>
                   <span class="text-positive">{{ course.seatsLeft }} Seats Left</span>
                 </div>
                 <q-linear-progress 
                   rounded 
                   size="6px" 
                   :value="course.enrolled / course.capacity" 
                   :color="course.enrolled / course.capacity > 0.8 ? 'negative' : 'primary'" 
                   track-color="grey-3" 
                 />
               </div>
             </q-card-section>
             <q-separator />
             <q-card-actions align="right">
               <q-btn flat dense icon="visibility" color="grey-7" size="sm">
                 <q-tooltip>View Details</q-tooltip>
               </q-btn>
               <q-btn unelevated color="primary" label="Enter Class" size="sm" icon-right="login" />
             </q-card-actions>
           </q-card>
        </div>
      </div>
    </div>

    <!-- All Available Classes -->
    <div>
       <div class="row items-center justify-between q-mb-md">
          <div class="text-h6 text-weight-bold">All Available Classes</div>
          <q-btn flat color="primary" label="Browse All" to="/student/courses?tab=recommended" no-caps />
       </div>
       
       <q-scroll-area horizontal style="height: 420px;" class="bg-transparent">
          <div class="row no-wrap q-gutter-md">
             <div v-for="rec in recommendedCourses" :key="rec.id" style="width: 320px">
                <q-card class="my-card no-shadow border-light bg-white full-height flex column">
                   <q-img :src="rec.image" :ratio="4/3" class="bg-grey-3">
                     <template v-slot:error>
                       <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">
                         <q-icon name="image_not_supported" size="md" />
                       </div>
                     </template>
                   </q-img>
                   
                   <q-card-section class="q-grow flex column justify-between">
                     <div>
                       <div class="text-h6 text-weight-bold text-dark ellipsis" :title="rec.name">{{ rec.name }}</div>
                       <div class="text-subtitle2 text-grey-7 q-mb-xs">{{ rec.teacher }}</div>
                       <div class="row items-center q-gutter-sm text-caption text-grey-6 q-mb-md">
                          <div class="row items-center"><q-icon name="schedule" size="xs" class="q-mr-xs"/> {{ rec.schedule }}</div>
                       </div>
                     </div>
                     
                     <div class="row items-center justify-between q-mt-auto">
                       <div class="text-h6 text-primary text-weight-bold">LKR {{ rec.fee }}</div>
                       <q-btn unelevated color="primary" label="Enroll Now" />
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
import { ref, computed } from 'vue'

const selectedDate = ref(new Date().toISOString().split('T')[0]) // YYYY/MM/DD format

const myCourses = ref([
  { 
    id: 1, 
    name: 'Grade 10 - Mathematics', 
    title: 'G10 Maths Theory',
    subject: 'Mathematics', 
    teacher: 'Mr. Bandara', 
    schedule: 'Sat 8:00 AM', 
    fee: '2500',
    type: 'Physical',
    enrolled: 45,
    capacity: 50,
    seatsLeft: 5,
    image: 'https://images.unsplash.com/photo-1509228627152-72ae9ae6848d?w=800&h=1000&fit=crop' 
  },
  { 
    id: 2, 
    name: 'Grade 11 - Science', 
    title: 'G11 Science Revision',
    subject: 'Science', 
    teacher: 'Mrs. Silva', 
    schedule: 'Sun 10:00 AM', 
    fee: '3000',
    type: 'Hybrid',
    enrolled: 98,
    capacity: 100,
    seatsLeft: 2,
    image: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800&h=1000&fit=crop' 
  },
  { 
    id: 3, 
    name: 'Grade 6 - English', 
    title: 'Spoken English',
    subject: 'English', 
    teacher: 'Mr. Perera', 
    schedule: 'Mon 4:00 PM', 
    fee: '1500',
    type: 'Online',
    enrolled: 12,
    capacity: 40,
    seatsLeft: 28,
    image: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&h=1000&fit=crop' 
  }
])

const recommendedCourses = ref([
  { 
    id: 4, 
    name: 'Grade 10 - English', 
    title: 'English Literature',
    teacher: 'Mr. Perera', 
    fee: '1500', 
    schedule: 'Tue 3:00 PM',
    type: 'Physical',
    enrolled: 25,
    capacity: 40,
    seatsLeft: 15,
    image: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&h=1000&fit=crop' 
  },
  { 
    id: 5, 
    name: 'Grade 10 - History', 
    title: 'Sri Lankan History',
    teacher: 'Ms. Kamala', 
    fee: '1200', 
    schedule: 'Wed 2:00 PM',
    type: 'Hybrid',
    enrolled: 18,
    capacity: 30,
    seatsLeft: 12,
    image: 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=800&h=1000&fit=crop' 
  },
  { 
    id: 6, 
    name: 'Grade 10 - ICT', 
    title: 'Programming Basics',
    teacher: 'Mr. Tech', 
    fee: '2000', 
    schedule: 'Thu 5:00 PM',
    type: 'Online',
    enrolled: 35,
    capacity: 50,
    seatsLeft: 15,
    image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=1000&fit=crop' 
  },
  { 
    id: 7, 
    name: 'Physics - A/L', 
    title: 'Advanced Physics',
    teacher: 'Dr. Fernando', 
    fee: '3500', 
    schedule: 'Fri 4:00 PM',
    type: 'Physical',
    enrolled: 42,
    capacity: 45,
    seatsLeft: 3,
    image: 'https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?w=800&h=1000&fit=crop' 
  },
  { 
    id: 8, 
    name: 'Chemistry - A/L', 
    title: 'Chemistry Theory',
    teacher: 'Mrs. Nimalka', 
    fee: '3200', 
    schedule: 'Sat 9:00 AM',
    type: 'Hybrid',
    enrolled: 38,
    capacity: 40,
    seatsLeft: 2,
    image: 'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?w=800&h=1000&fit=crop' 
  }
])

// Calendar schedule data - maps dates to classes
const classSchedule = ref([
  { id: 1, date: '2026/01/11', subject: 'Grade 10 Mathematics', teacher: 'Mr. Bandara', time: '08:00 AM - 10:00 AM' },
  { id: 2, date: '2026/01/12', subject: 'Grade 10 Science', teacher: 'Mrs. Silva', time: '10:00 AM - 12:00 PM' },
  { id: 3, date: '2026/01/15', subject: 'Grade 10 Mathematics', teacher: 'Mr. Bandara', time: '08:00 AM - 10:00 AM' },
  { id: 4, date: '2026/01/18', subject: 'Grade 10 Mathematics', teacher: 'Mr. Bandara', time: '08:00 AM - 10:00 AM' },
  { id: 5, date: '2026/01/19', subject: 'Grade 10 Science', teacher: 'Mrs. Silva', time: '10:00 AM - 12:00 PM' },
  { id: 6, date: '2026/01/22', subject: 'Grade 10 Mathematics', teacher: 'Mr. Bandara', time: '08:00 AM - 10:00 AM' },
  { id: 7, date: '2026/01/25', subject: 'Grade 10 Mathematics', teacher: 'Mr. Bandara', time: '08:00 AM - 10:00 AM' },
  { id: 8, date: '2026/01/26', subject: 'Grade 10 Science', teacher: 'Mrs. Silva', time: '10:00 AM - 12:00 PM' },
  { id: 9, date: '2026/01/29', subject: 'Grade 10 Mathematics', teacher: 'Mr. Bandara', time: '08:00 AM - 10:00 AM' },
])

// Get all dates that have classes (for calendar highlighting)
const eventDates = computed(() => {
  return classSchedule.value.map(cls => cls.date)
})

// Get classes for a specific date
const getClassesForDate = (date) => {
  return classSchedule.value.filter(cls => cls.date === date)
}

// Format date header
const formatDateHeader = (dateStr) => {
  const date = new Date(dateStr)
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
  return date.toLocaleDateString('en-US', options)
}
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

.course-card-ratio {
  position: relative;
  overflow: hidden;
}

.bg-gradient-to-top {
  background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
}
</style>
