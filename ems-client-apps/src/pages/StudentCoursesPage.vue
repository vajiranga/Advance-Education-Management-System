<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold">My Courses</div>
        <div class="text-caption text-grey-7">Manage your enrolled classes and learning progress</div>
      </div>
      <q-btn icon="add" label="Enroll New Course" color="primary" flat @click="openEnrollDialog" />
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
      <q-tab name="active" label="Your Classes" />
      <q-tab name="recommended" label="Recommended Classes" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      <q-tab-panel name="active" class="q-pa-none">
        
        <div v-if="courses.length === 0" class="text-center text-grey q-pa-xl">
          <q-icon name="school" size="64px" color="grey-4" />
          <div class="text-h6 q-mt-md">No courses enrolled yet</div>
          <q-btn color="primary" label="Enroll New Course" class="q-mt-md" @click="openEnrollDialog" />
        </div>

        <div v-else class="row q-col-gutter-lg">
          <div class="col-12 col-md-6 col-lg-4" v-for="course in courses" :key="course.id">
            <q-card class="course-card border-light no-shadow">
              <q-img :src="course.image" ratio="4/5">
                <div class="absolute-top-left q-pa-sm">
                  <q-chip :color="course.type === 'Physical' ? 'blue-1' : course.type === 'Hybrid' ? 'orange-1' : 'green-1'" 
                          :text-color="course.type === 'Physical' ? 'blue' : course.type === 'Hybrid' ? 'orange' : 'green'" 
                          size="sm">
                    {{ course.type }}
                  </q-chip>
                </div>
                <div class="absolute-bottom full-width bg-gradient-to-top q-pa-md">
                  <div class="text-h6 text-weight-bold text-white">{{ course.title }}</div>
                </div>
              </q-img>
              
              <q-card-section class="q-pb-sm">
                <div class="text-subtitle2 text-weight-medium">{{ course.subject }}</div>
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
                    <span>Attendance: {{ course.attendance }}%</span>
                    <span :class="course.attendance >= 75 ? 'text-positive' : 'text-warning'">
                      {{ course.attendance >= 75 ? 'Good' : 'Low' }}
                    </span>
                  </div>
                  <q-linear-progress 
                    rounded 
                    size="6px" 
                    :value="course.attendance / 100" 
                    :color="course.attendance >= 75 ? 'positive' : 'warning'" 
                    track-color="grey-3" 
                  />
                </div>
              </q-card-section>

              <q-separator />

              <q-card-actions align="right">
                <q-btn flat dense icon="history" color="grey-7" size="sm">
                  <q-tooltip>Recordings</q-tooltip>
                </q-btn>
                <q-btn flat dense icon="visibility" color="grey-7" size="sm">
                  <q-tooltip>View Details</q-tooltip>
                </q-btn>
                <q-btn unelevated color="primary" label="Enter Class" size="sm" icon-right="login" />
                <q-btn flat dense round icon="more_vert" size="sm">
                   <q-menu>
                      <q-list style="min-width: 100px">
                        <q-item clickable v-close-popup @click="dropCourse(course.id)" class="text-negative">
                          <q-item-section>Drop Course</q-item-section>
                        </q-item>
                      </q-list>
                   </q-menu>
                </q-btn>
              </q-card-actions>
            </q-card>
          </div>
        </div>

      </q-tab-panel>

      <q-tab-panel name="recommended" class="q-pa-none">
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-md-6 col-lg-4" v-for="course in recommendedCourses" :key="course.id">
            <q-card class="course-card border-light no-shadow">
              <q-img :src="course.image" :ratio="4/3" class="bg-grey-3">
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-3 text-grey-7">
                    <q-icon name="image_not_supported" size="md" />
                  </div>
                </template>
              </q-img>
              
              <q-card-section>
                <div class="text-h6 text-weight-bold text-dark ellipsis">{{ course.title }}</div>
                <div class="text-subtitle2 text-grey-7 q-mb-xs">{{ course.teacher }}</div>
                <div class="row items-center q-gutter-sm text-caption text-grey-6 q-mb-md">
                   <div class="row items-center"><q-icon name="schedule" size="xs" class="q-mr-xs"/> {{ course.schedule }}</div>
                </div>
                
                <div class="row items-center justify-between q-mt-sm">
                  <div class="text-h6 text-primary text-weight-bold">LKR {{ course.fee }}</div>
                  <q-btn unelevated color="primary" label="Enroll Now" @click="enrollInCourse(course.id)" />
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>
    </q-tab-panels>
    <q-dialog v-model="showEnrollDialog">
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Enroll in a New Course</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-list separator>
            <q-item v-for="course in availableCourses" :key="course.id" class="q-py-md">
              <q-item-section avatar>
                 <q-avatar rounded size="50px">
                   <img :src="course.cover_image_url || 'https://cdn.quasar.dev/img/parallax2.jpg'">
                 </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">{{ course.name }}</q-item-label>
                <q-item-label caption lines="1">Batch: {{ course.batch?.name }} | {{ course.subject?.name }}</q-item-label>
                <q-item-label caption class="text-primary">{{ course.teacher?.name }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                 <div class="text-weight-bold q-mb-xs">{{ course.fee_amount }} LKR</div>
                 <q-btn unelevated color="primary" label="Enroll" size="sm" @click="enrollInCourse(course.id)" :disable="isEnrolled(course.id)" />
              </q-item-section>
            </q-item>
          </q-list>
          <div v-if="availableCourses.length === 0" class="text-center text-grey q-pa-md">
             No available courses found.
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
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'

const $q = useQuasar()
const route = useRoute()
const tab = ref(route.query.tab || 'active')
const showEnrollDialog = ref(false)
const courses = ref([
  {
    id: 1,
    title: 'Grade 10 - Mathematics',
    subject: 'G10 Maths Theory',
    teacher: 'Mr. Bandara',
    schedule: 'Sat 8:00 AM',
    fee: '2500',
    type: 'Physical',
    attendance: 85,
    image: 'https://images.unsplash.com/photo-1509228627152-72ae9ae6848d?w=800&h=1000&fit=crop'
  },
  {
    id: 2,
    title: 'Grade 11 - Science',
    subject: 'G11 Science Revision',
    teacher: 'Mrs. Silva',
    schedule: 'Sun 10:00 AM',
    fee: '3000',
    type: 'Hybrid',
    attendance: 92,
    image: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800&h=1000&fit=crop'
  },
  {
    id: 3,
    title: 'Grade 6 - English',
    subject: 'Spoken English',
    teacher: 'Mr. Perera',
    schedule: 'Mon 4:00 PM',
    fee: '1500',
    type: 'Online',
    attendance: 60,
    image: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&h=1000&fit=crop'
  }
])
const availableCourses = ref([])

onMounted(() => {
    // Using dummy data for now
    console.log('Courses loaded:', courses.value.length)
})

const fetchMyCourses = async () => {
    try {
        const res = await api.get('/my-courses')
        if (res.data && res.data.length > 0) {
            courses.value = res.data
        }
        // else keep dummy data
    } catch (err) {
        console.error('Failed to fetch courses, using dummy data:', err)
        // Keep dummy data that's already set
    }
}

const openEnrollDialog = async () => {
    showEnrollDialog.value = true
    try {
        // Fetch all courses (assuming paginated, getting first page for now)
        const res = await api.get('/courses') 
        availableCourses.value = res.data.data // Laravel paginate returns { data: [...] }
    } catch (err) {
        console.error(err)
        $q.notify({ type: 'negative', message: 'Failed to load courses' })
    }
}

const isEnrolled = (courseId) => {
    return courses.value.some(c => c.id === courseId)
}

const enrollInCourse = async (courseId) => {
    try {
        await api.post('/enroll', { course_id: courseId })
        $q.notify({ type: 'positive', message: 'Enrolled Successfully!' })
        await fetchMyCourses()
        showEnrollDialog.value = false // Optional: keep open if multiple
    } catch (err) {
        $q.notify({ type: 'negative', message: err.response?.data?.message || 'Enrollment failed' })
    }
}

const dropCourse = async (courseId) => {
    try {
        await api.post(`/courses/${courseId}/drop`)
        $q.notify({ type: 'info', message: 'Course Dropped' })
        fetchMyCourses()
    } catch (err) {
       console.error(err)
    }
}

// Dummy recommended courses data
const recommendedCourses = ref([
    {
        id: 101,
        title: 'Advanced Physics - A/L',
        teacher: 'Mr. Jayantha Fernando',
        image: 'https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?w=800&h=1000&fit=crop',
        schedule: 'Wednesdays 4:00 PM',
        fee: '3500'
    },
    {
        id: 102,
        title: 'Chemistry - Theory & Practical',
        teacher: 'Mrs. Nimalka Silva',
        image: 'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?w=800&h=1000&fit=crop',
        schedule: 'Fridays 3:00 PM',
        fee: '3200'
    },
    {
        id: 103,
        title: 'English Language Skills',
        teacher: 'Ms. Sanduni Perera',
        image: 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&h=1000&fit=crop',
        schedule: 'Saturdays 2:00 PM',
        fee: '2500'
    },
    {
        id: 104,
        title: 'Combined Mathematics',
        teacher: 'Mr. Kamal Bandara',
        image: 'https://images.unsplash.com/photo-1509228627152-72ae9ae6848d?w=800&h=1000&fit=crop',
        schedule: 'Sundays 9:00 AM',
        fee: '4000'
    },
    {
        id: 105,
        title: 'Biology - Complete Course',
        teacher: 'Dr. Chaminda Wijayasinghe',
        image: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=800&h=1000&fit=crop',
        schedule: 'Tuesdays 5:00 PM',
        fee: '3800'
    },
    {
        id: 106,
        title: 'ICT - Programming & Theory',
        teacher: 'Mr. Nuwan Dissanayake',
        image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=1000&fit=crop',  
        schedule: 'Thursdays 6:00 PM',
        fee: '3000'
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
.bg-gradient-to-top {
  background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
}
</style>

