<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-teal-2' : 'text-teal-9'">My Classes</div>
      <q-btn color="teal" icon="add" label="New Class" unelevated @click="openCreateDialog" />
    </div>

    <q-tabs
      v-model="tab"
      dense
      :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'"
      active-color="teal"
      indicator-color="teal"
      align="left"
      narrow-indicator
    >
      <q-tab name="active" label="Active Classes" />
      <q-tab name="pending" label="Pending Requests" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent q-mt-md">
       <q-tab-panel name="active" class="q-pa-none">
          <div class="row q-col-gutter-md">
              <div class="col-12 text-center text-grey q-py-xl" v-if="activeCourses.length === 0">
                  <q-icon name="class" size="4em" class="q-mb-sm opacity-50" />
                  <div>No active classes found.</div>
              </div>
              <div class="col-12 col-md-4" v-for="cls in activeCourses" :key="cls.id">
                  <q-card class="no-shadow border-top-teal full-height column" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
                     <q-card-section>
                        <div class="row items-center justify-between">
                           <q-badge :color="$q.dark.isActive ? 'teal-9' : 'teal-1'" :text-color="$q.dark.isActive ? 'teal-2' : 'teal'" :label="cls.batch?.name" />
                           <q-btn flat round icon="more_vert" dense color="grey" />
                        </div>
                        <div class="text-h6 q-mt-sm" :class="$q.dark.isActive ? 'text-white' : ''">{{ cls.name }}</div>
                        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ formatSchedule(cls.schedule) }}</div>
                     </q-card-section>

                     <q-card-section class="q-pt-none">
                         <div class="row q-gutter-x-lg q-my-sm" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
                            <div class="row items-center"><q-icon name="groups" class="q-mr-xs" /> {{ cls.students_count || 0 }} Students</div>
                            <div class="row items-center"><q-icon name="meeting_room" class="q-mr-xs" /> {{ cls.hall?.name || 'No Hall' }}</div>
                         </div>
                     </q-card-section>

                     <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

                     <q-card-actions align="right">
                        <q-btn flat color="teal" label="Syllabus" />
                        <q-btn flat color="teal" label="Materials" />
                     </q-card-actions>
                  </q-card>
              </div>
          </div>
       </q-tab-panel>

       <q-tab-panel name="pending" class="q-pa-none">
          <div class="row q-col-gutter-md">
              <div class="col-12 text-center text-grey q-py-xl" v-if="pendingCourses.length === 0">
                  <div>No pending requests.</div>
              </div>
               <div class="col-12 col-md-4" v-for="cls in pendingCourses" :key="cls.id">
                  <q-card class="no-shadow border-top-orange full-height column" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
                     <q-card-section>
                        <div class="row items-center justify-between">
                           <q-badge :color="$q.dark.isActive ? 'teal-9' : 'teal-1'" :text-color="$q.dark.isActive ? 'teal-2' : 'teal'" :label="cls.batch?.name" />
                           <q-chip size="sm" :color="$q.dark.isActive ? 'orange-9' : 'orange-1'" :text-color="$q.dark.isActive ? 'orange-2' : 'orange'" label="Pending Approval" />
                        </div>
                        <div class="text-h6 q-mt-sm" :class="$q.dark.isActive ? 'text-white' : ''">{{ cls.name }}</div>
                        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ formatSchedule(cls.schedule) }}</div>
                     </q-card-section>

                     <q-card-section class="q-pt-none">
                         <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-8'">
                            Request sent on {{ new Date(cls.created_at).toLocaleDateString() }}
                         </div>
                     </q-card-section>
                     
                     <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                     
                     <q-card-actions align="right">
                        <q-btn flat color="negative" icon="delete" label="Cancel Request" size="sm" @click="cancelRequest(cls.id)" />
                     </q-card-actions>
                  </q-card>
              </div>
          </div>
       </q-tab-panel>
    </q-tab-panels>

     <q-dialog v-model="showDialog">
        <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
            <q-card-section>
                <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Create New Class</div>
                <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Request a new course. Requires Admin Approval.</div>
            </q-card-section>

            <q-card-section class="q-gutter-md">
                <q-input v-model="newCourse.name" label="Class Name (e.g. Science Grade 6)" outlined :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                <q-select v-model="newCourse.subject_id" :options="subjects" option-label="name" option-value="id" label="Subject" emit-value map-options outlined :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                <q-select v-model="newCourse.batch_id" :options="batches" option-label="name" option-value="id" label="Batch / Grade" emit-value map-options outlined :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                <q-input v-model="newCourse.fee_amount" label="Fee (LKR)" type="number" outlined :rules="[val => !!val || 'Required']" :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                
                <div class="row q-col-gutter-sm">
                    <q-select class="col-4" v-model="newCourse.schedule.day" :options="days" label="Day" outlined :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                    <q-input class="col-4" v-model="newCourse.schedule.start" type="time" label="Start" outlined :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                    <q-input class="col-4" v-model="newCourse.schedule.end" type="time" label="End" outlined :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                </div>
                
                <q-select 
                    v-model="newCourse.hall_id" 
                    :options="halls" 
                    option-label="name" 
                    option-value="id" 
                    label="Select Hall (Based on Schedule)" 
                    emit-value 
                    map-options 
                    outlined 
                    :disable="halls.length === 0"
                    :hint="halls.length === 0 ? 'Set schedule to see available halls' : `${halls.length} halls available`"
                    :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" 
                    :dark="$q.dark.isActive"
                >
                    <template v-slot:option="scope">
                        <q-item v-bind="scope.itemProps" :class="$q.dark.isActive ? 'text-white' : ''">
                            <q-item-section>
                                <q-item-label>{{ scope.opt.name }}</q-item-label>
                                <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">Points: {{ scope.opt.capacity }} | Floor: {{ scope.opt.floor }}</q-item-label>
                            </q-item-section>
                            <q-item-section side v-if="scope.opt.has_ac">
                                <q-chip dense color="blue-1" text-color="blue" size="sm" icon="ac_unit" label="AC" />
                            </q-item-section>
                        </q-item>
                    </template>
                </q-select>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="Cancel" v-close-popup :color="$q.dark.isActive ? 'grey-4' : 'primary'" />
                <q-btn color="primary" label="Create Request" @click="submitCourse" :loading="loading" />
            </q-card-actions>
        </q-card>
     </q-dialog>
  </q-page>
</template>

<script setup>
import { onMounted, ref, reactive, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useTeacherStore } from 'stores/teacher-store'
import { useAuthStore } from 'stores/auth-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const teacherStore = useTeacherStore()
const authStore = useAuthStore()
const { courses, loading } = storeToRefs(teacherStore)

const tab = ref('active')
const showDialog = ref(false)
const batches = ref([])
const subjects = ref([])
const halls = ref([]) // Available Halls
const newCourse = reactive({
    name: '',
    subject_id: null,
    batch_id: null,
    fee_amount: '',
    hall_id: null,
    schedule: { day: 'Monday', start: '08:00', end: '10:00' }
})

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']

onMounted(() => {
    loadCourses()
})

// Watch schedule changes to fetch available halls
import { watch } from 'vue'
watch(() => newCourse.schedule, async (val) => {
    if (val.day && val.start && val.end) {
        halls.value = await teacherStore.checkHallAvailability({
            day: val.day, // Pass day explicitly
            start_time: val.start, 
            end_time: val.end 
        })
    }
}, { deep: true })

function loadCourses() {
    teacherStore.fetchCourses({ teacher_id: authStore.user?.id })
}

const activeCourses = computed(() => (courses.value || []).filter(c => (c.status === 'approved' || !c.status) && (!c.type || c.type === 'regular')))
const pendingCourses = computed(() => (courses.value || []).filter(c => c.status === 'pending' && (!c.type || c.type === 'regular')))

async function openCreateDialog() {
    showDialog.value = true
    if (batches.value.length === 0) batches.value = await teacherStore.fetchBatches()
    if (subjects.value.length === 0) subjects.value = await teacherStore.fetchSubjects()
}

async function submitCourse() {
    if (!newCourse.name || !newCourse.subject_id || !newCourse.batch_id || !newCourse.fee_amount) {
        $q.notify({ type: 'warning', message: 'Please fill required fields' })
        return
    }

    const payload = {
        name: newCourse.name,
        subject_id: newCourse.subject_id,
        batch_id: newCourse.batch_id,
        teacher_id: authStore.user?.id,
        fee_amount: newCourse.fee_amount,
        hall_id: newCourse.hall_id,
        schedule: newCourse.schedule 
    }

    const res = await teacherStore.createClass(payload)
    if (res.success) {
        showDialog.value = false // Close the form first
        
        $q.dialog({
            title: 'Request Sent Successfully',
            message: 'Your class request has been sent to the Admin. Please wait for approval.\nYou can check the status in the "Pending Requests" tab.',
            ok: { label: 'OK', color: 'primary' },
            persistent: true
        }).onOk(() => {
            // Optional: Any action after OK. 
            // We already switched tab effectively visually.
        })
        
        loadCourses()
        
        // Switch to Pending Tab
        tab.value = 'pending'

        // Reset
        newCourse.name = ''
        newCourse.fee_amount = ''
    } else {
        $q.notify({ type: 'negative', message: 'Failed: ' + res.error })
    }
}

async function cancelRequest(id) {
    $q.dialog({
        title: 'Confirm Cancellation',
        message: 'Are you sure you want to cancel this class request?',
        cancel: true,
        persistent: true
    }).onOk(async () => {
        const res = await teacherStore.deleteClass(id)
        if (res.success) {
            $q.notify({ type: 'positive', message: 'Request Cancelled' })
            loadCourses()
        } else {
            $q.notify({ type: 'negative', message: 'Failed to cancel' })
        }
    })
}

function formatSchedule(schedule) {
    if (!schedule) return 'Not Scheduled'
    if (typeof schedule === 'string') {
        try { schedule = JSON.parse(schedule) } catch { return schedule }
    }
    if (schedule.day) return `${schedule.day} | ${schedule.start || ''} - ${schedule.end || ''}`
    return 'Recurring'
}
</script>

<style scoped>
.border-top-teal { border-top: 4px solid teal; }
.border-top-orange { border-top: 4px solid orange; }
</style>
