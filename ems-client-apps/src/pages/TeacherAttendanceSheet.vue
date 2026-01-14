<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold text-teal-9">Attendance Management</div>
      <div class="row items-center q-gutter-x-sm">
         <div class="text-subtitle2">Date:</div>
         <q-input dense outlined v-model="selectedDate" type="date" class="bg-white" />
      </div>
    </div>
    
    <!-- Class Selector -->
    <q-card class="q-mb-md no-shadow border-light">
       <q-card-section class="row items-center q-gutter-x-md">
          <div class="text-subtitle1 text-grey-7">Select Class:</div>
          <q-select 
            dense 
            outlined 
            v-model="selectedClass" 
            :options="courses" 
            option-label="name"
            option-value="id" 
            label="Choose Class"
            style="min-width: 250px" 
          />
          <q-btn color="teal" label="Load Students" icon="refresh" @click="loadStudents" :disable="!selectedClass" />
       </q-card-section>
    </q-card>

    <!-- Attendance Sheet -->
    <q-card class="no-shadow border-light" v-if="attendanceList.length > 0">
       <q-card-section>
          <div class="row items-center justify-between q-mb-md">
             <div class="text-h6">Mark Attendance</div>
             <div class="row q-gutter-x-sm">
                <q-chip color="green-1" text-color="green">Present: {{ presentCount }}</q-chip>
                <q-chip color="red-1" text-color="red">Absent: {{ absentCount }}</q-chip>
             </div>
          </div>
          
          <q-separator />

          <q-list separator>
             <q-item v-for="student in attendanceList" :key="student.id" class="q-py-sm">
                <q-item-section avatar>
                   <q-avatar size="32px"><img :src="student.avatar" /></q-avatar>
                </q-item-section>
                <q-item-section>
                   <q-item-label class="text-weight-bold">{{ student.name }}</q-item-label>
                   <q-item-label caption>{{ student.idNumber }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                   <div class="row q-gutter-x-md">
                      <q-btn 
                         round 
                         :color="student.status === 'present' ? 'green' : 'grey-3'" 
                         :text-color="student.status === 'present' ? 'white' : 'grey'" 
                         icon="check" 
                         @click="student.status = 'present'" 
                         unelevated
                      />
                      <q-btn 
                         round 
                         :color="student.status === 'absent' ? 'red' : 'grey-3'" 
                         :text-color="student.status === 'absent' ? 'white' : 'grey'" 
                         icon="close" 
                         @click="student.status = 'absent'" 
                         unelevated
                      />
                      <!-- Added Late/Excused toggle if needed, or keep simple -->
                   </div>
                </q-item-section>
             </q-item>
          </q-list>
          
          <q-separator />
          
          <div class="row justify-end q-pa-md">
             <q-btn color="teal" label="Save Attendance" icon="save" @click="saveAttendance" :loading="loading" />
          </div>
       </q-card-section>
    </q-card>
    
    <div v-if="!selectedClass && attendanceList.length === 0" class="text-center text-grey q-mt-xl">
        Please select a class to mark attendance.
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTeacherStore } from 'stores/teacher-store'
import { useAuthStore } from 'stores/auth-store'
import { storeToRefs } from 'pinia'
import { useQuasar, date as qDate } from 'quasar'

const $q = useQuasar()
const teacherStore = useTeacherStore()
const authStore = useAuthStore()
const { courses, loading } = storeToRefs(teacherStore)

const selectedClass = ref(null)
const selectedDate = ref(qDate.formatDate(Date.now(), 'YYYY-MM-DD'))
const attendanceList = ref([])

onMounted(() => {
    // Ensure courses are loaded
    if (courses.value.length === 0) {
        teacherStore.fetchCourses({ teacher_id: authStore.user?.id })
    }
})

async function loadStudents() {
    if (!selectedClass.value) return
    
    $q.loading.show()
    const res = await teacherStore.fetchStudentsForAttendance(selectedClass.value.id, selectedDate.value)
    $q.loading.hide()
    
    // Map backend data to UI
    attendanceList.value = res.students.map(s => ({
        id: s.id,
        name: s.name,
        idNumber: s.username, // Using username as ID (e.g. STU...)
        avatar: s.avatar || 'https://cdn.quasar.dev/img/boy-avatar.png',
        status: s.status || 'present' // Default to present if not marked
    }))
    
    if (res.is_marked) {
        $q.notify({ type: 'info', message: 'Loaded existing attendance record.' })
    }
}

async function saveAttendance() {
    if (attendanceList.value.length === 0) return

    $q.loading.show()
    const payload = {
        course_id: selectedClass.value.id,
        date: selectedDate.value,
        attendances: attendanceList.value.map(s => ({
            student_id: s.id,
            status: s.status
        }))
    }
    
    const res = await teacherStore.saveAttendance(payload)
    $q.loading.hide()
    
    if (res.success) {
        $q.notify({ type: 'positive', message: 'Attendance Saved Successfully' })
    } else {
        $q.notify({ type: 'negative', message: 'Failed: ' + res.error })
    }
}

const presentCount = computed(() => attendanceList.value.filter(s => s.status === 'present').length)
const absentCount = computed(() => attendanceList.value.filter(s => s.status === 'absent').length)
</script>

<style scoped>
.border-light { border: 1px solid #eee; }
</style>
