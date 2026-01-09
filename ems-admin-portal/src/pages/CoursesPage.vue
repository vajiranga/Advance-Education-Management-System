<template>
  <q-page class="q-pa-md">
    <!-- Header Logs -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Course Management</div>
      <q-btn color="primary" icon="add_card" label="Create New Course" @click="openAddDialog" />
    </div>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-3">
        <q-card class="bg-indigo text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">12</div>
            <div class="text-caption">Active Courses</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-teal text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">450</div>
            <div class="text-caption">Total Students</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card class="bg-orange text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">35</div>
            <div class="text-caption">Total Vacancies</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Filters & Content -->
    <div class="row q-col-gutter-lg">
      <!-- Sidebar Filters -->
      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="text-subtitle1 q-mb-sm text-weight-bold">Filters</div>
            
            <q-select 
              v-model="filterGrade" 
              :options="['All', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11']" 
              label="Grade" 
              outlined 
              dense 
              class="q-mb-md" 
            />

            <q-select 
              v-model="filterSubject" 
              :options="filterSubjectOptions" 
              label="Subject" 
              outlined 
              dense 
              class="q-mb-md" 
            />

            <q-select 
              v-model="filterTeacher" 
              :options="filterTeacherOptions" 
              label="Teacher" 
              outlined 
              dense 
              class="q-mb-md" 
            />

            <q-toggle v-model="showOnlyVacancies" label="Show Vacancies Only" />
          </q-card-section>
        </q-card>
      </div>

      <!-- Course List -->
      <div class="col-12 col-md-9">
         <div class="row q-col-gutter-md">
           
           <div class="col-12 col-md-6 col-lg-4" v-for="course in filteredCourses" :key="course.id">
             <q-card class="my-card">
               <q-img src="https://cdn.quasar.dev/img/parallax2.jpg" style="height: 140px">
                 <div class="absolute-bottom text-subtitle2 text-center">
                   {{ course.grade }} - {{ course.subject }}
                 </div>
               </q-img>

               <q-card-section>
                 <div class="row no-wrap items-center">
                   <div class="col text-h6 ellipsis">{{ course.name }}</div>
                   <div class="col-auto text-caption text-grey">{{ course.type }}</div>
                 </div>
                 <div class="text-subtitle2 text-grey-8 row items-center">
                   <q-icon name="person" class="q-mr-xs" /> {{ course.teacher }}
                 </div>
               </q-card-section>

               <q-card-section class="q-pt-none">
                 <div class="row items-center justify-between q-mb-sm">
                   <div class="text-caption">
                     <q-icon name="schedule" color="primary" /> {{ course.schedule }}
                   </div>
                   <div class="text-caption text-weight-bold text-green">
                     Fee: LKR {{ course.fee }}
                   </div>
                 </div>

                 <!-- Occupancy Bar -->
                 <div class="q-mt-md">
                   <div class="row justify-between text-caption">
                     <span>Enrollment: {{ course.students }} / {{ course.max_students }}</span>
                     <span :class="course.max_students - course.students < 5 ? 'text-red' : 'text-green'">
                       {{ course.max_students - course.students }} Seats Left
                     </span>
                   </div>
                   <q-linear-progress 
                      rounded 
                      size="8px" 
                      :value="course.students / course.max_students" 
                      :color="course.students / course.max_students > 0.9 ? 'red' : 'primary'" 
                   />
                 </div>
               </q-card-section>

               <q-separator />

               <q-card-actions align="right">
                 <q-btn flat round color="primary" icon="edit" @click="editCourse(course)" />
                 <q-btn flat round color="secondary" icon="visibility" />
                 <q-btn flat round color="red" icon="delete" @click="deleteCourse(course.id)" />
               </q-card-actions>
             </q-card>
           </div>

         </div>
      </div>
    </div>

    <!-- Add Course Dialog -->
    <q-dialog v-model="showAddDialog">
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Create New Course</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="row q-col-gutter-md">
             <div class="col-6">
                <q-select v-model="newCourse.grade" :options="['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11']" label="Grade" outlined />
             </div>
             <div class="col-6">
                <q-select v-model="newCourse.subject" :options="subjectsList" label="Subject" outlined />
             </div>
             
             <div class="col-12">
               <q-input v-model="newCourse.name" label="Course Name (e.g. 2026 Mathematics Theory)" outlined />
             </div>

             <div class="col-6">
                <q-select v-model="newCourse.teacher" :options="teacherOptions" label="Assign Teacher" outlined />
             </div>
             <div class="col-6">
                <q-select v-model="newCourse.type" :options="['Physical', 'Online', 'Hybrid']" label="Class Type" outlined />
             </div>

             <div class="col-4">
               <q-input v-model="newCourse.fee" label="Monthly Fee (LKR)" type="number" outlined />
             </div>
             <div class="col-4">
               <q-input v-model="newCourse.max_students" label="Max Students" type="number" outlined />
             </div>
             
             <!-- Split Schedule -->
             <div class="col-4">
               <q-select v-model="newCourse.scheduleDay" :options="daysOfWeek" label="Day" outlined />
            </div>
             <div class="col-4">
               <q-select v-model="newCourse.scheduleTime" :options="timeSlots" label="Start Time" outlined />
            </div>
            <div class="col-4">
               <q-select v-model="newCourse.scheduleEndTime" :options="timeSlots" label="End Time" outlined />
            </div>

            <div class="col-12">
               <q-select v-model="newCourse.hallId" :options="hallOptions" label="Assign Hall (Optional)" outlined hint="Ensure capacity fits student count" />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Create Course" color="primary" @click="saveCourse" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useCourseStore } from 'stores/course-store'
import { useUserStore } from 'stores/user-store'
import { useHallStore } from 'stores/hall-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const courseStore = useCourseStore()
const userStore = useUserStore()
const hallStore = useHallStore()

const { courses } = storeToRefs(courseStore)
const { teachers } = storeToRefs(userStore)
const { halls } = storeToRefs(hallStore)

const showAddDialog = ref(false)

// Filters
const filterGrade = ref('All')
const filterSubject = ref('All')
const filterTeacher = ref('All')
const showOnlyVacancies = ref(false)

// Ordered Subject List (English)
const subjectsList = [
  'Mathematics', 'Science', 'English Language', 'Sinhala (Mother Tongue)', 'History', 'Geography',
  'Buddhism', 'Catholicism', 'Christianity', 'Islam', 'Hinduism',
  'Civic Education', 'Health & Physical Education', 'ICT',
  'Tamil Language',
  'Art', 'Dancing', 'Music', 'Drama & Theatre',
  'Practical & Technical Skills (PTS)', 'Agriculture', 'Home Economics'
]

// Schedule Options
const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
const timeSlots = [
  '8:00 AM', '8:30 AM', '9:00 AM', '9:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
  '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM', 
  '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM', '6:00 PM', '6:30 PM', '7:00 PM'
]

// Computed Options
const teacherOptions = computed(() => teachers.value.map(t => t.name))
const filterTeacherOptions = computed(() => ['All', ...teachers.value.map(t => t.name)])
const filterSubjectOptions = computed(() => ['All', ...subjectsList])
const hallOptions = computed(() => halls.value.map(h => ({ label: `${h.name} (Cap: ${h.capacity})`, value: h.id, capacity: h.capacity })))

const newCourse = ref({
  grade: '', subject: '', name: '', teacher: '', fee: '', max_students: 50, 
  scheduleDay: 'Saturday', scheduleTime: '8:00 AM', scheduleEndTime: '10:00 AM',
  hallId: null, type: 'Physical'
})

const filteredCourses = computed(() => {
  return courses.value.filter(course => {
    const matchGrade = filterGrade.value === 'All' || course.grade === filterGrade.value
    const matchSubject = filterSubject.value === 'All' || course.subject === filterSubject.value
    const matchTeacher = filterTeacher.value === 'All' || course.teacher === filterTeacher.value
    const matchVacancy = !showOnlyVacancies.value || (course.max_students - course.students > 0)
    
    return matchGrade && matchSubject && matchTeacher && matchVacancy
  })
})

// Edit Logic
const isEditMode = ref(false)
const editingId = ref(null)

const openAddDialog = () => {
  isEditMode.value = false
  newCourse.value = { 
    grade: '', subject: '', name: '', teacher: '', fee: '', max_students: 50, 
    scheduleDay: 'Saturday', scheduleTime: '8:00 AM', scheduleEndTime: '10:00 AM', 
    hallId: null, type: 'Physical' 
  } 
  showAddDialog.value = true
}

const editCourse = (course) => {
  isEditMode.value = true
  editingId.value = course.id
  
  // Parse Schedule
  // Existing format: Saturday at 8:00 AM (Legacy) or Saturday 8:00 AM - 10:00 AM (New)
  // Simple heuristic for demo
  const parts = course.schedule.split(' ')
  // Improve robust parsing if needed, assumed format day time - time
  
  newCourse.value = { 
    ...course,
    scheduleDay: 'Saturday', 
    scheduleTime: '8:00 AM',
    scheduleEndTime: '10:00 AM',
    hallId: course.hallId ? { label: course.hallName, value: course.hallId } : null
  }
  showAddDialog.value = true
}

const deleteCourse = (id) => {
  if(confirm('Are you sure you want to delete this course?')) {
    courseStore.deleteCourse(id)
    $q.notify({ type: 'negative', message: 'Course Deleted' })
  }
}

const saveCourse = () => {
  // Logic: Check Hall Capacity
  if (newCourse.value.hallId) {
     const selectedHall = halls.value.find(h => h.id === newCourse.value.hallId.value)
     if (selectedHall && parseInt(newCourse.value.max_students) > selectedHall.capacity) {
        $q.notify({ type: 'warning', message: `Warning: Max students (${newCourse.value.max_students}) exceeds Hall Capacity (${selectedHall.capacity})` })
        // We allow proceed for now but warn
     }
  }

  const fullSchedule = `${newCourse.value.scheduleDay} ${newCourse.value.scheduleTime} - ${newCourse.value.scheduleEndTime}`
  
  const courseData = {
    ...newCourse.value,
    hallId: newCourse.value.hallId?.value || null,
    hallName: newCourse.value.hallId?.label || 'Not Assigned',
    schedule: fullSchedule,
    students: 0
  }

  if (isEditMode.value) {
     const index = courses.value.findIndex(c => c.id === editingId.value)
     if (index !== -1) {
       courses.value[index] = { ...courses.value[index], ...courseData }
       $q.notify({ type: 'positive', message: 'Course Updated Successfully' })
     }
  } else {
    courseStore.addCourse({ id: Math.random(), ...courseData })
    $q.notify({ type: 'positive', message: 'Course Created Successfully' })
  }
  showAddDialog.value = false
}
</script>
