<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">My Courses</div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Manage your enrolled classes and learning progress</div>
      </div>
      <!-- Button Removed as requested -->
    </div>

    <!-- Tabs -->
    <q-tabs
      v-model="tab"
      dense
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
      class="q-mb-md"
      :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'"
    >
      <q-tab name="active" label="Your Classes" />
      <q-tab name="recommended" label="All Available Classes" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      <q-tab-panel name="active" class="q-pa-none">
        
        <div v-if="myCourses.length === 0" class="text-center text-grey q-pa-xl">
          <q-icon name="school" size="64px" :color="$q.dark.isActive ? 'grey-7' : 'grey-4'" />
          <div class="text-h6 q-mt-md" :class="$q.dark.isActive ? 'text-grey-4' : ''">No courses enrolled yet</div>
          <div class="text-caption">Go to 'All Available Classes' to enroll.</div>
        </div>

        <div v-else class="row q-col-gutter-lg">
          <div class="col-12 col-md-6 col-lg-4" v-for="course in myCourses" :key="course.id">
            <q-card class="course-card no-shadow full-height column" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
              <!-- Reverted to q-img -->
              <q-card-section class="q-pb-sm col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-1' : 'primary'" size="xs">Physical</q-chip>
                </div>
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl" :class="$q.dark.isActive ? 'text-white' : ''">{{ course.name }}</div>
                <div class="text-subtitle2 text-primary">{{ course.batch?.name }} - {{ course.subject?.name }}</div>
                
                <div class="text-subtitle2 row items-center q-mt-sm" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                  <q-icon name="person" size="xs" class="q-mr-xs" />
                  {{ course.teacher?.name }}
                </div>
                <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                  <q-icon name="meeting_room" class="q-mr-xs" />
                  {{ course.hall?.name || 'No Hall' }}
                  <span v-if="course.hall?.floor" class="text-caption q-ml-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'"> (Floor: {{ course.hall.floor }})</span>
                  <q-chip v-if="course.hall?.has_ac" dense :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-1' : 'blue'" size="xs" icon="ac_unit" class="q-ml-sm">AC</q-chip>
                </div>
                <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                  <q-icon name="group" class="q-mr-xs" />
                  {{ course.students_count || 0 }} Students
                </div>
                <div class="text-caption row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
                  <q-icon name="schedule" size="xs" class="q-mr-xs" />
                  {{ formatSchedule(course.schedule) }}
                </div>
                <div class="text-caption text-primary text-weight-bold q-mt-xs">
                  Fee: LKR {{ course.fee_amount }}
                </div>
                

              </q-card-section>

              <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

              <q-card-actions align="right">
                <q-btn flat dense icon="history" :color="$q.dark.isActive ? 'grey-5' : 'grey-7'" size="sm">
                  <q-tooltip>Recordings</q-tooltip>
                </q-btn>
                <q-btn unelevated color="primary" label="Enter Class" size="sm" icon-right="login" />
                <q-btn flat dense round icon="more_vert" size="sm" :color="$q.dark.isActive ? 'grey-5' : ''">
                   <q-menu :class="$q.dark.isActive ? 'bg-dark' : ''">
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
          <div class="col-12 col-md-6 col-lg-4" v-for="course in allCourses" :key="course.id">
            <q-card class="course-card no-shadow full-height column" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
              <!-- Reverted to q-img -->
              <q-card-section class="col-grow relative-position">
                <div class="absolute-top-right q-pa-sm">
                   <q-chip :color="$q.dark.isActive ? 'green-9' : 'green-1'" :text-color="$q.dark.isActive ? 'green-1' : 'green'" size="xs">Open</q-chip>
                </div>
                <div class="text-h6 text-weight-bold ellipsis q-pr-xl" :class="$q.dark.isActive ? 'text-white' : ''" :title="course.name">{{ course.name }}</div>
                <div class="text-subtitle2 text-primary">{{ course.batch?.name }} - {{ course.subject?.name }}</div>

                <div class="text-subtitle2 row items-center q-mt-sm" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                   <q-icon name="person" size="xs" class="q-mr-xs"/> {{ course.teacher?.name }}
                </div>
                <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                   <q-icon name="meeting_room" size="xs" class="q-mr-xs"/> {{ course.hall?.name || 'No Hall' }}
                </div>
                <div class="text-subtitle2 row items-center q-mt-xs" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                   <q-icon name="group" size="xs" class="q-mr-xs"/> {{ course.students_count || 0 }} Students
                </div>

                <div class="text-caption row items-center q-mt-md" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
                   <div class="row items-center"><q-icon name="schedule" size="xs" class="q-mr-xs"/> {{ formatSchedule(course.schedule) }}</div>
                </div>
                
                <div class="row items-center justify-between q-mt-auto">
                   <div class="text-subtitle1 text-primary text-weight-bold">LKR {{ course.fee_amount }}</div>
                   <q-btn 
                     unelevated 
                     :color="isEnrolled(course.id) ? 'grey' : 'primary'" 
                     :label="isEnrolled(course.id) ? 'Enrolled' : 'Enroll Now'" 
                     size="sm" 
                     @click="confirmEnroll(course)" 
                     :disable="isEnrolled(course.id)"
                   />
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
import { useStudentStore } from 'stores/student-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const route = useRoute()
const tab = ref(route.query.tab || 'active')

const studentStore = useStudentStore()
const { myCourses, allCourses } = storeToRefs(studentStore)

onMounted(() => {
    studentStore.fetchMyCourses()
    studentStore.fetchAllCourses()
})

const isEnrolled = (courseId) => {
    return myCourses.value.some(c => c.id === courseId)
}

const dropCourse = async (courseId) => {
    try {
        await api.post(`/v1/courses/${courseId}/drop`) 
        $q.notify({ type: 'info', message: 'Course Dropped' })
        studentStore.fetchMyCourses()
    } catch (err) {
       console.error(err)
       $q.notify({ type: 'negative', message: 'Failed to drop course' })
    }
}



// Confirm Enrollment
function confirmEnroll(course) {
    $q.dialog({
        title: 'Confirm Enrollment',
        message: `Do you want to enroll in <b>${course.name}</b>?<br>Fee: LKR ${course.fee_amount}`,
        html: true,
        cancel: true,
        persistent: true,
        ok: { label: 'Enroll Now', color: 'primary' }
    }).onOk(async () => {
        $q.loading.show()
        const success = await studentStore.enroll(course.id)
        $q.loading.hide()
        if (success) {
             $q.notify({ type: 'positive', message: 'Enrolled Successfully!' })
             // Store handles refreshing lists
        } else {
             $q.notify({ type: 'negative', message: 'Enrollment Failed' })
        }
    })
}

function formatSchedule(schedule) {
    if (!schedule) return 'TBA'
    if (typeof schedule === 'string') return schedule
    if (schedule.type === 'one-off') {
        return `${schedule.date} @ ${schedule.start}`
    }
    if (schedule.day) return `${schedule.day} | ${schedule.start} - ${schedule.end}`
    return 'Recurring'
}
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
