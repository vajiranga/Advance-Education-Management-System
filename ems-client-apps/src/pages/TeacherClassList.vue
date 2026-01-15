<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5 text-weight-bold text-teal-9">My Classes</div>
      <q-btn color="teal" icon="add" label="New Class" unelevated @click="openCreateDialog" />
    </div>

    <q-tabs
      v-model="tab"
      dense
      class="text-grey"
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
                  <q-card class="no-shadow border-top-teal bg-white full-height column">
                     <q-card-section>
                        <div class="row items-center justify-between">
                           <q-badge color="teal-1" text-color="teal" :label="cls.batch?.name" />
                           <q-btn flat round icon="more_vert" dense color="grey" />
                        </div>
                        <div class="text-h6 q-mt-sm">{{ cls.name }}</div>
                        <div class="text-caption text-grey">{{ formatSchedule(cls.schedule) }}</div>
                     </q-card-section>

                     <q-card-section class="q-pt-none">
                         <div class="row q-gutter-x-lg q-my-sm text-grey-8">
                            <div class="row items-center"><q-icon name="groups" class="q-mr-xs" /> {{ cls.students_count || 0 }} Students</div>
                            <div class="row items-center"><q-icon name="meeting_room" class="q-mr-xs" /> {{ cls.hall?.name || 'No Hall' }}</div>
                         </div>
                     </q-card-section>

                     <q-separator />

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
                  <q-card class="no-shadow border-top-orange bg-white full-height column">
                     <q-card-section>
                        <div class="row items-center justify-between">
                           <q-badge color="teal-1" text-color="teal" :label="cls.batch?.name" />
                           <q-chip size="sm" color="orange-1" text-color="orange" label="Pending Approval" />
                        </div>
                        <div class="text-h6 q-mt-sm">{{ cls.name }}</div>
                        <div class="text-caption text-grey">{{ formatSchedule(cls.schedule) }}</div>
                     </q-card-section>

                     <q-card-section class="q-pt-none">
                         <div class="text-grey-8 text-caption">
                            Request sent on {{ new Date(cls.created_at).toLocaleDateString() }}
                         </div>
                     </q-card-section>
                  </q-card>
              </div>
          </div>
       </q-tab-panel>
    </q-tab-panels>

     <q-dialog v-model="showDialog">
        <q-card style="min-width: 500px">
            <q-card-section>
                <div class="text-h6">Create New Class</div>
                <div class="text-caption text-grey">Request a new course. Requires Admin Approval.</div>
            </q-card-section>

            <q-card-section class="q-gutter-md">
                <q-input v-model="newCourse.name" label="Class Name (e.g. Science Grade 6)" outlined :rules="[val => !!val || 'Required']" />
                <q-select v-model="newCourse.subject_id" :options="subjects" option-label="name" option-value="id" label="Subject" emit-value map-options outlined :rules="[val => !!val || 'Required']" />
                <q-select v-model="newCourse.batch_id" :options="batches" option-label="name" option-value="id" label="Batch / Grade" emit-value map-options outlined :rules="[val => !!val || 'Required']" />
                <q-input v-model="newCourse.fee_amount" label="Fee (LKR)" type="number" outlined :rules="[val => !!val || 'Required']" />
                
                <div class="row q-col-gutter-sm">
                    <q-select class="col-4" v-model="newCourse.schedule.day" :options="days" label="Day" outlined />
                    <q-input class="col-4" v-model="newCourse.schedule.start" type="time" label="Start" outlined />
                    <q-input class="col-4" v-model="newCourse.schedule.end" type="time" label="End" outlined />
                </div>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="Cancel" v-close-popup />
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
const newCourse = reactive({
    name: '',
    subject_id: null,
    batch_id: null,
    fee_amount: '',
    hall_id: null,
    schedule: { day: 'Monday', start: '', end: '' }
})

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']

onMounted(() => {
    loadCourses()
})

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
