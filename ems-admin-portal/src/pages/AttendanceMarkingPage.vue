<template>
  <q-page class="q-pa-md">
    <div class="row q-col-gutter-md">
       <!-- Sessions List (Left) -->
       <div class="col-12 col-md-4">
           <q-card class="my-card full-height">
               <q-card-section class="bg-primary text-white">
                   <div class="text-h6">Class Sessions</div>
                   <div class="text-caption">Select a session to mark attendance</div>
               </q-card-section>
               <q-card-section class="q-pa-none">
                   <div v-if="loadingSessions" class="row justify-center q-pa-md">
                       <q-spinner color="primary" />
                   </div>
                   <q-list v-else separator>
                       <q-item 
                          v-for="(sess, idx) in sessions" :key="idx" 
                          clickable 
                          v-ripple
                          :active="selectedSession === sess"
                          active-class="bg-blue-1 text-primary"
                          @click="selectSession(sess)"
                       >
                           <q-item-section>
                               <q-item-label class="text-weight-bold">{{ sess.course_name }}</q-item-label>
                               <q-item-label caption>{{ sess.date }} | {{ sess.start }} - {{ sess.end }}</q-item-label>
                               <q-item-label caption class="text-grey-8">{{ sess.teacher_name }}</q-item-label>
                           </q-item-section>
                           <q-item-section side>
                               <q-chip size="sm" :color="getStatusColor(sess.marked_status)" text-color="white">
                                   {{ sess.marked_status }}
                               </q-chip>
                           </q-item-section>
                       </q-item>
                   </q-list>
                   <div v-if="!loadingSessions && sessions.length === 0" class="text-center q-pa-md text-grey">
                       No upcoming or recent sessions found.
                   </div>
               </q-card-section>
           </q-card>
       </div>

       <!-- Marking Area (Right) -->
       <div class="col-12 col-md-8">
           <q-card v-if="selectedSession" class="my-card">
               <q-card-section class="row items-center justify-between">
                   <div>
                       <div class="text-h6">{{ selectedSession.course_name }}</div>
                       <div class="text-subtitle2 text-grey-7">{{ selectedSession.date }} ({{ selectedSession.start }})</div>
                   </div>
                   <div class="row items-center q-gutter-x-md">
                        <div class="text-caption text-weight-bold">
                            <span class="text-green q-mr-sm">Present: {{ counts.present }}</span>
                            <span class="text-red">Absent: {{ counts.absent }}</span>
                        </div>
                       <div class="row q-gutter-sm">
                           <q-btn flat dense color="primary" label="Mark All Present" icon="done_all" @click="markAllPresent" />
                           <q-btn unelevated color="primary" label="Save" icon="save" :loading="saving" @click="saveAttendance" />
                       </div>
                   </div>
               </q-card-section>
               <q-separator />
               <q-card-section class="q-pa-none">
                   <div v-if="loadingStudents" class="row justify-center q-pa-xl">
                       <q-spinner color="primary" size="3em" />
                   </div>
                   <q-table
                      v-else
                      flat
                      :rows="students"
                      :columns="columns"
                      row-key="id"
                      :pagination="{ rowsPerPage: 0 }"
                      hide-bottom
                   >
                      <template v-slot:body="props">
                          <q-tr :props="props">
                              <q-td key="name" :props="props">
                                  <div class="row items-center">
                                      <q-avatar size="sm" class="q-mr-sm" color="grey-3" text-color="primary">{{ props.row.name.charAt(0) }}</q-avatar>
                                      {{ props.row.name }}
                                  </div>
                              </q-td>
                              <q-td key="contact" :props="props">{{ props.row.phone || props.row.email }}</q-td>
                              <q-td key="status" :props="props">
                                  <q-btn-toggle
                                      v-model="props.row.attendance_status"
                                      flat
                                      dense
                                      :options="[
                                        {value: 'present', slot: 'present'},
                                        {value: 'absent', slot: 'absent'}
                                      ]"
                                  >
                                    <template v-slot:present>
                                        <q-btn round dense flat :color="props.row.attendance_status === 'present' ? 'green' : 'grey-4'" icon="check_circle" @click="props.row.attendance_status = 'present'" />
                                    </template>
                                    <template v-slot:absent>
                                        <q-btn round dense flat :color="props.row.attendance_status === 'absent' ? 'red' : 'grey-4'" icon="cancel" @click="props.row.attendance_status = 'absent'" />
                                    </template>
                                  </q-btn-toggle>
                              </q-td> 
                              <q-td key="note" :props="props">
                                  <q-input dense borderless v-model="props.row.attendance_note" placeholder="Note" />
                              </q-td>
                          </q-tr>
                      </template>
                   </q-table>
               </q-card-section>
           </q-card>
           
           <div v-else class="flex flex-center bg-grey-2 rounded-borders text-grey-7" style="height: 400px; border: 2px dashed #ccc;">
               <div class="text-center">
                   <q-icon name="touch_app" size="4em" />
                   <div class="text-h6 q-mt-md">Select a session to begin marking</div>
               </div>
           </div>
       </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const sessions = ref([])
const selectedSession = ref(null)
const students = ref([])
const loadingSessions = ref(false)
const loadingStudents = ref(false)
const saving = ref(false)

const columns = [
    { name: 'name', label: 'Student Name', align: 'left', field: 'name', sortable: true },
    { name: 'contact', label: 'Contact', align: 'left', field: 'phone' },
    { name: 'status', label: 'Status', align: 'center', field: 'attendance_status' },
    { name: 'note', label: 'Note', align: 'left', field: 'attendance_note' }
]

const counts = computed(() => {
    const present = students.value.filter(s => s.attendance_status === 'present').length
    const absent = students.value.length - present
    return { present, absent }
})

onMounted(() => {
    fetchSessions()
})

async function fetchSessions() {
    loadingSessions.value = true
    try {
        const res = await api.get('/v1/admin/attendance/dashboard')
        sessions.value = res.data.sessions
    } catch (e) {
        console.error('Error fetching sessions', e)
    } finally {
        loadingSessions.value = false
    }
}

async function selectSession(sess) {
    selectedSession.value = sess
    loadingStudents.value = true
    try {
        const res = await api.get('/v1/attendance/students', { 
            params: { course_id: sess.course_id, date: sess.date } 
        })
        students.value = res.data.data.map(s => ({
            ...s,
            attendance_status: s.attendance_status || 'absent', // Default to absent if not marked
            attendance_note: s.attendance_note || ''
        }))
    } catch (e) {
        console.error('Error fetching students', e)
        students.value = []
    } finally {
        loadingStudents.value = false
    }
}

function markAllPresent() {
    students.value.forEach(s => s.attendance_status = 'present')
}

async function saveAttendance() {
    if (!selectedSession.value) return
    saving.value = true
    try {
        const payload = {
            course_id: selectedSession.value.course_id,
            date: selectedSession.value.date,
            attendances: students.value.map(s => ({
                student_id: s.id,
                status: s.attendance_status,
                note: s.attendance_note
            }))
        }
        await api.post('/v1/attendance/bulk', payload)
        
        $q.notify({
            color: 'green-4',
            textColor: 'white',
            icon: 'cloud_done',
            message: 'Attendance Saved Successfully'
        })
        
        // Refresh sessions to update status
        fetchSessions()
        
    } catch (e) {
        console.error('Error saving attendance', e)
        $q.notify({
            color: 'red-5',
            textColor: 'white',
            icon: 'warning',
            message: 'Failed to save attendance'
        })
    } finally {
        saving.value = false
    }
}

function getStatusColor(status) {
    if (status === 'completed') return 'green'
    if (status === 'partial') return 'blue'
    return 'orange'
}
</script>
