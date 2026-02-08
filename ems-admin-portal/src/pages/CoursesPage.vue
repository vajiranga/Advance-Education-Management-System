<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Class Management</div>
      <div class="q-gutter-md">
        <q-btn v-if="authStore.hasPermission('classes_add')" color="primary" icon="add_card" label="Create New Class" @click="openAddDialog" />
      </div>
    </div>

    <!-- Content -->
    <div class="row q-col-gutter-lg">
      <!-- Sidebar Filters -->
      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="text-subtitle1 q-mb-sm text-weight-bold">Filters</div>

            <div class="text-caption text-grey-7 q-mb-xs">Class Type</div>
            <q-btn-toggle
              v-model="viewType"
              class="q-mb-md full-width"
              spread
              no-caps
              unelevated
              toggle-color="primary"
              color="white"
              text-color="primary"
              :options="[
                { label: 'Regular', value: 'regular' },
                { label: 'Extra', value: 'extra' },
              ]"
            />

            <div class="text-caption text-grey-7 q-mb-xs">Status</div>
            <q-option-group
              v-model="statusTab"
              :options="[
                { label: 'All Classes', value: 'all' },
                { label: 'Approved', value: 'approved', color: 'green' },
                { label: 'Pending', value: 'pending', color: 'orange' },
                { label: 'Rejected', value: 'rejected', color: 'red' },
                { label: 'Deleted', value: 'deleted', color: 'grey' },
              ]"
              color="primary"
              class="q-mb-md"
            />

            <q-input dense outlined v-model="search" placeholder="Search..." class="q-mb-md">
              <template v-slot:append><q-icon name="search" /></template>
            </q-input>
          </q-card-section>
        </q-card>
      </div>

      <!-- Course List -->
      <div class="col-12 col-md-9">
        <!-- Header Actions (Moved from top) -->
        <div class="row items-center justify-between q-mb-md">
          <div class="text-h6 text-grey-8">
            {{ filteredCourses.length }}
            {{ viewType === 'regular' ? 'Regular Classes' : 'Extra Classes' }} Found
          </div>
          <div class="q-gutter-sm">
            <q-btn
              v-if="statusTab === 'pending' && filteredCourses.length > 0"
              color="positive"
              icon="done_all"
              label="Approve All"
              @click="bulkApprove"
            />

            <q-btn
              v-if="filteredCourses.length > 0"
              color="negative"
              icon="delete_sweep"
              label="Delete All"
              outline
              @click="bulkDelete"
            />
          </div>
        </div>

        <div v-if="loading" class="row justify-center q-pa-lg">
          <q-spinner size="40px" color="primary" />
        </div>
        <div v-else-if="filteredCourses.length === 0" class="text-center text-grey q-pa-lg">
          No classes found.
        </div>
        <div class="row q-col-gutter-md" v-else>
          <div class="col-12 col-md-6 col-lg-4" v-for="course in filteredCourses" :key="course.id">
            <q-card
              class="my-card column full-height no-shadow border-light"
              :class="course.type === 'extra' ? 'border-top-orange' : 'border-top-primary'"
            >
              <q-card-section class="col relative-position q-pb-none">
                <div class="absolute-top-right q-pa-sm" style="z-index: 10">
                  <q-chip
                    :color="getStatusColor(course.status)"
                    text-color="white"
                    size="xs"
                    icon="info"
                  >
                    {{
                      course.status === 'pending'
                        ? 'PENDING'
                        : course.status
                          ? course.status.toUpperCase()
                          : 'UNKNOWN'
                    }}
                  </q-chip>
                  <q-chip
                    v-if="course.type === 'extra'"
                    color="orange"
                    text-color="white"
                    size="xs"
                    icon="star"
                    >EXTRA</q-chip
                  >
                </div>

                <div class="text-h6 two-line-clamp q-pr-xl q-mb-xs" :title="course.name">
                  {{ course.name }}
                </div>

                <div class="text-subtitle2 text-primary two-line-clamp" :title="`${course.batch?.name || 'Unknown Grade'} - ${course.subject?.name || 'Unknown Subject'}`">
                  {{ course.batch?.name || 'Unknown Grade' }} -
                  {{ course.subject?.name || 'Unknown Subject' }}
                </div>

                <div class="q-mt-md">
                   <div class="text-body2 text-grey-9 row items-center q-mb-xs ellipsis" :title="course.teacher?.name">
                     <q-icon name="person" class="q-mr-sm text-primary" size="xs" />
                     {{ course.teacher?.name || 'No Teacher' }}
                   </div>

                   <div class="text-caption text-grey-8 row items-center q-mb-xs ellipsis">
                     <q-icon name="meeting_room" class="q-mr-sm text-grey" size="xs" />
                     {{ course.hall?.name || 'No Hall' }}
                   </div>

                   <div class="text-caption text-grey-8 row items-center q-mb-xs">
                     <q-icon name="group" class="q-mr-sm text-grey" size="xs" />
                     {{ course.students_count || 0 }} Students
                   </div>


                </div>
              </q-card-section>

              <q-card-section class="q-pt-sm">
                <div class="row no-wrap items-center justify-between">
                  <div class="col ellipsis text-caption q-mr-sm" :title="formatSchedule(course.schedule)">
                    <q-icon name="schedule" color="primary" class="q-mr-xs" />
                    {{ formatSchedule(course.schedule) }}
                  </div>
                  <div class="col-auto text-caption text-weight-bold text-green" style="white-space: nowrap">
                    Fee: LKR {{ course.fee_amount }}
                  </div>
                </div>
              </q-card-section>

              <q-separator />

              <q-card-actions align="right">
                <div v-if="course.status === 'pending'">
                  <q-btn flat color="positive" label="Review" @click="openReviewDialog(course)" />
                </div>
                <div v-else>
                  <q-btn
                    flat
                    round
                    color="primary"
                    icon="edit"
                    size="sm"
                    @click="editCourse(course)"
                  >
                    <q-tooltip>Edit</q-tooltip>
                  </q-btn>
                  <q-btn
                    flat
                    round
                    color="secondary"
                    icon="group"
                    size="sm"
                    @click="manageStudents(course)"
                  >
                    <q-tooltip>Students</q-tooltip>
                  </q-btn>
                  <q-btn
                    flat
                    round
                    color="negative"
                    icon="delete"
                    size="sm"
                    @click="deleteCourse(course.id)"
                  >
                    <q-tooltip>Delete</q-tooltip>
                  </q-btn>
                </div>
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Dialog -->
    <q-dialog v-model="reviewDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Review Class Request</div>
          <div class="text-subtitle1 text-primary q-mb-sm">{{ reviewingCourse?.name }}</div>

          <q-list bordered separator class="rounded-borders bg-grey-1">
            <q-item>
              <q-item-section>
                <q-item-label caption>Teacher</q-item-label>
                <q-item-label>{{ reviewingCourse?.teacher?.name }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-item-label caption>Type</q-item-label>
                <q-chip
                  size="sm"
                  color="orange"
                  text-color="white"
                  v-if="reviewingCourse?.type === 'extra'"
                  >EXTRA</q-chip
                >
                <q-chip size="sm" color="blue" text-color="white" v-else>REGULAR</q-chip>
              </q-item-section>
            </q-item>

            <q-item class="bg-blue-1">
              <q-item-section avatar>
                <q-icon name="meeting_room" color="primary" />
              </q-item-section>
              <q-item-section>
                <q-item-label caption class="text-primary text-weight-bold"
                  >Requested Hall</q-item-label
                >
                <q-item-label class="text-h6">{{
                  reviewingCourse?.hall?.name || 'No Hall Selected'
                }}</q-item-label>
                <q-item-label caption v-if="reviewingCourse?.hall"
                  >Capacity: {{ reviewingCourse?.hall?.capacity }} | AC:
                  {{ reviewingCourse?.hall?.has_ac ? 'Yes' : 'No' }}</q-item-label
                >
              </q-item-section>
            </q-item>

            <q-item>
              <q-item-section>
                <q-item-label caption>Schedule</q-item-label>
                <q-item-label>{{ formatSchedule(reviewingCourse?.schedule) }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-item-label caption>Fee</q-item-label>
                <q-item-label class="text-weight-bold"
                  >LKR {{ reviewingCourse?.fee_amount }}</q-item-label
                >
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="reviewNote"
            outlined
            type="textarea"
            label="Admin Note (Optional)"
            hint="Reason for rejection or approval note"
          />
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn color="negative" label="Reject" @click="submitReview('rejected')" />
          <q-btn color="positive" label="Approve" @click="submitReview('approved')" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">{{ isEditMode ? 'Edit Class' : 'Create New Class' }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section class="scroll" style="max-height: 70vh">
          <q-form @submit="saveCourse" class="q-gutter-md">
            <!-- Teacher Select -->
            <q-select
              outlined
              v-model="form.teacher"
              :options="teacherOptions"
              option-label="name"
              label="Select Teacher *"
              use-input
              @filter="filterTeachers"
              @update:model-value="onTeacherSelect"
              :rules="[(val) => !!val || 'Teacher is required']"
            >
              <template v-slot:option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section>
                    <q-item-label>{{ scope.opt.name }}</q-item-label>
                    <q-item-label caption
                      >{{ scope.opt.email }} | {{ scope.opt.phone }}</q-item-label
                    >
                  </q-item-section>
                </q-item>
              </template>
              <template v-slot:no-option>
                <q-item><q-item-section class="text-grey">No results</q-item-section></q-item>
              </template>
            </q-select>

            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <!-- Grade/Batch -->
                <q-select
                  outlined
                  v-model="form.batch"
                  :options="batches"
                  option-label="name"
                  label="Grade *"
                  :rules="[(val) => !!val || 'Required']"
                />
              </div>
              <div class="col-6">
                <!-- Subject (Filtered) -->
                <q-select
                  outlined
                  v-model="form.subject"
                  :options="filteredSubjects"
                  option-label="name"
                  label="Subject *"
                  :rules="[(val) => !!val || 'Required']"
                  hint="Auto-filtered based on teacher's expertise"
                />
              </div>
            </div>

            <!-- Schedule (Moved Up) -->
            <q-separator class="q-my-sm" />
            <div class="text-subtitle2 text-grey-8 q-mb-sm">Class Schedule (Select to filter halls)</div>
            <div class="row q-col-gutter-sm q-mb-md">
              <div class="col-4">
                <q-select
                  outlined
                  v-model="form.day"
                  :options="[
                    'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
                  ]"
                  label="Day"
                />
              </div>
              <div class="col-4">
                <q-input outlined v-model="form.startTime" type="time" label="Start Time" />
              </div>
              <div class="col-4">
                <q-input outlined v-model="form.endTime" type="time" label="End Time" />
              </div>
            </div>

            <!-- Hall & Fee -->
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-select
                  outlined
                  v-model="form.hall"
                  :options="availableHalls"
                  :loading="checkingHall"
                  :disable="checkingHall"
                  option-label="name"
                  label="Select Hall (Filtered) *"
                  :rules="[(val) => !!val || 'Required']"
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>
                          {{ scope.opt.name }}
                        </q-item-label>
                        <q-item-label caption class="text-grey-7">
                          <q-icon name="group" size="xs" /> {{ scope.opt.capacity }} |
                          <span v-if="scope.opt.floor">Floor: {{ scope.opt.floor }}</span>
                        </q-item-label>
                      </q-item-section>
                      <q-item-section side v-if="scope.opt.has_ac">
                        <q-chip
                          dense
                          color="blue-1"
                          text-color="blue"
                          size="sm"
                          icon="ac_unit"
                          label="AC"
                        />
                      </q-item-section>
                    </q-item>
                  </template>
                  <template v-slot:selected-item="scope">
                    <div class="row items-center">
                      {{ scope.opt.name }}
                      <q-chip
                        v-if="scope.opt.has_ac"
                        dense
                        color="blue-1"
                        text-color="blue"
                        size="xs"
                        icon="ac_unit"
                        class="q-ml-sm"
                        label="AC"
                      />
                    </div>
                  </template>
                </q-select>
              </div>
              <div class="col-6">
                <q-input
                  outlined
                  v-model="form.fee"
                  label="Class Fee (LKR) *"
                  type="number"
                  prefix="LKR"
                  :rules="[(val) => val > 0 || 'Invalid Amount']"
                />
              </div>
            </div>





            <div class="row justify-end q-mt-md">
              <q-btn label="Cancel" flat color="grey" v-close-popup />
              <q-btn
                :label="isEditMode ? 'Update Class' : 'Create Class'"
                type="submit"
                color="primary"
                :loading="submitting"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Student Management Dialog -->
    <q-dialog v-model="showStudentsDialog">
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Manage Students - {{ selectedCourse?.name }}</div>
        </q-card-section>
        <q-card-section>
          <!-- Add Student -->
          <div class="row q-col-gutter-sm items-center q-mb-md">
            <div class="col-grow">
              <q-select
                outlined
                dense
                v-model="selectedStudents"
                use-input
                input-debounce="300"
                label="Search & Add Students"
                :options="searchResults"
                @filter="searchStudentFn"
                option-label="name"
                option-value="id"
                multiple
                use-chips
                stack-label
              >
                <template v-slot:append>
                  <q-btn
                    round
                    dense
                    flat
                    icon="person_add"
                    color="primary"
                    @click="addSelectedStudents"
                    :disable="!selectedStudents || selectedStudents.length === 0"
                  >
                    <q-tooltip>Add Selected</q-tooltip>
                  </q-btn>
                </template>
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.name }}</q-item-label>
                      <q-item-label caption
                        >{{ scope.opt.username }} | {{ scope.opt.email }}</q-item-label
                      >
                    </q-item-section>
                  </q-item>
                </template>
                <template v-slot:no-option>
                  <q-item
                    ><q-item-section class="text-grey"
                      >Type to search by Name or ID</q-item-section
                    ></q-item
                  >
                </template>
              </q-select>
            </div>
          </div>

          <!-- Student List -->
          <q-list separator bordered class="rounded-borders scroll" style="max-height: 400px">
            <q-item v-for="student in studentsList" :key="student.id">
              <q-item-section avatar>
                <q-avatar size="sm" color="primary" text-color="white">{{
                  student.name.charAt(0)
                }}</q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ student.name }}</q-item-label>
                <q-item-label caption>
                  {{ student.phone || 'No Phone' }} |
                  {{
                    new Date(student.pivot?.enrolled_at || student.created_at).toLocaleDateString()
                  }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-chip
                  size="xs"
                  :color="student.pivot?.status === 'active' ? 'green' : 'red'"
                  text-color="white"
                >
                  {{ student.pivot?.status }}
                </q-chip>
                <q-chip size="xs" :color="getFeeColor(student)" text-color="white">
                  {{ getFeeStatus(student) }}
                </q-chip>
              </q-item-section>
            </q-item>
            <div v-if="studentsList.length === 0" class="text-center text-grey q-pa-md">
              No students enrolled.
            </div>
          </q-list>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar, date } from 'quasar'
import { api } from 'boot/axios'
import { useCourseStore } from 'stores/course-store'
import { useUserStore } from 'stores/user-store'
import { useAuthStore } from 'stores/auth-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const courseStore = useCourseStore()
const userStore = useUserStore()
const authStore = useAuthStore()
const { courses, loading, subjects, batches, halls } = storeToRefs(courseStore)
const { teachers } = storeToRefs(userStore)

const statusTab = ref('all')
const search = ref('')

const teacherOptions = ref([])

// Student Management Logic
const selectedCourse = ref(null)
const showStudentsDialog = ref(false)
const studentsList = ref([])
const searchResults = ref([])
const selectedStudents = ref([])

async function manageStudents(course) {
  selectedCourse.value = course
  studentsList.value = await courseStore.fetchStudents(course.id)
  selectedStudents.value = []
  showStudentsDialog.value = true
}

async function searchStudentFn(val, update, abort) {
  if (val.length < 2) {
    update(() => {
      searchResults.value = selectedStudents.value
    })
    return
  }
  try {
    const res = await api.get('/users', { params: { role: 'student', search: val } })

    // Exclude Currently Enrolled Students AND Already Selected
    const enrolledIds = new Set(studentsList.value.map((u) => String(u.id)))
    const selectedIds = new Set(selectedStudents.value.map((u) => String(u.id)))

    const newOptions = res.data.filter(
      (u) => !selectedIds.has(String(u.id)) && !enrolledIds.has(String(u.id)),
    )

    update(() => {
      searchResults.value = [...selectedStudents.value, ...newOptions]
    })
  } catch {
    abort()
  }
}

// Review Logic Refs
const reviewDialog = ref(false)
const reviewingCourse = ref(null)
const reviewNote = ref('')

// Add/Edit Logic Refs
const showAddDialog = ref(false)
const isEditMode = ref(false)
const submitting = ref(false)

const form = ref({
  teacher: null,
  batch: null,
  subject: null,
  hall: null,
  fee: '',
  day: 'Monday',
  startTime: '08:00',
  endTime: '10:00',

})

const viewType = ref('regular')

// Hall Availability Logic
const checkingHall = ref(false)
const availableHalls = ref([]) // Initialize empty, sync with watch

async function checkHallAvailability() {
  // Basic validation
  if (!form.value.day || !form.value.startTime || !form.value.endTime) {
      availableHalls.value = halls.value
      return
  }

  // If schedule is incomplete string ??
  if (form.value.startTime.length < 5 || form.value.endTime.length < 5) return

  checkingHall.value = true
  try {
    const payload = {
       day: form.value.day,
       start_time: form.value.startTime, // HH:mm
       end_time: form.value.endTime,
       exclude_course_id: isEditMode.value ? form.value.id : null
    }
    const res = await api.post('v1/halls/check', payload)

    // Sort halls by name/number for better UX
    availableHalls.value = res.data.sort((a,b) => a.name.localeCompare(b.name))

    // Warn if selected hall is not available
    if (form.value.hall && !availableHalls.value.find(h => h.id === form.value.hall.id)) {
        // Optional: clear selection or show warning
        // form.value.hall = null
        $q.notify({ type: 'warning', message: 'Selected hall is busy at this time!' })
    }

  } catch (e) {
    console.error('Check Hall Error', e)
    availableHalls.value = halls.value
  } finally {
    checkingHall.value = false
  }
}

watch([() => form.value.day, () => form.value.startTime, () => form.value.endTime], () => {
   checkHallAvailability()
})

// Sync initial halls or fallback
watch(halls, (val) => {
   if (availableHalls.value.length === 0) availableHalls.value = val
}, { immediate: true })


onMounted(async () => {
  courseStore.fetchCourses({ type: viewType.value, all: true })
  courseStore.fetchMetadata()
  await userStore.fetchTeachers()
  teacherOptions.value = teachers.value
})

watch(viewType, (newType) => {
  courseStore.fetchCourses({ type: newType, all: true })
})

const filteredCourses = computed(() => {
  return courses.value.filter((c) => {
    // Type Filter (Safeguard)
    const cType = c.type || 'regular'
    if (cType !== viewType.value) return false

    // Status Filter
    if (statusTab.value !== 'all' && c.status !== statusTab.value) return false

    // Search
    if (search.value) {
      const s = search.value.toLowerCase()
      if (!c.name.toLowerCase().includes(s) && !c.teacher_name?.toLowerCase().includes(s))
        return false
    }

    return true
  })
})

function getFeeStatus(student) {
  if (!student.fees || student.fees.length === 0) return 'NO FEE'
  const fee = student.fees[0]
  return fee.status === 'paid' ? 'PAID' : 'DUE'
}

function getFeeColor(student) {
  if (!student.fees || student.fees.length === 0) return 'grey'
  const fee = student.fees[0]
  return fee.status === 'paid' ? 'green' : 'orange'
}

function getStatusColor(status) {
  if (status === 'approved') return 'green'
  if (status === 'rejected') return 'red'
  return 'orange'
}

function formatSchedule(schedule) {
  if (!schedule) return 'Not Set'
  try {
     if (typeof schedule === 'string') schedule = JSON.parse(schedule)
  } catch {
     return schedule
  }

  if (schedule.date) {
      const d = new Date(schedule.date)
      const formattedDate = date.isValid(d) ? date.formatDate(d, 'MMM D, YYYY') : schedule.date
      return `${formattedDate} (${schedule.start || ''} - ${schedule.end || ''})`
  }

  if (schedule.day) return `${schedule.day} ${schedule.start || ''}-${schedule.end || ''}`
  return JSON.stringify(schedule)
}

function filterTeachers(val, update) {
  if (val === '') {
    update(() => {
      teacherOptions.value = teachers.value
    })
    return
  }
  update(() => {
    const needle = val.toLowerCase()
    teacherOptions.value = teachers.value.filter((v) => v.name.toLowerCase().indexOf(needle) > -1)
  })
}

const filteredSubjects = computed(() => {
  if (
    !form.value.teacher ||
    !form.value.teacher.subjects ||
    form.value.teacher.subjects.length === 0
  ) {
    return subjects.value
  }
  const tSubjects = form.value.teacher.subjects.map((s) => String(s).toLowerCase())
  return subjects.value.filter(
    (sub) => tSubjects.includes(sub.name.toLowerCase()) || tSubjects.includes(String(sub.id)),
  )
})

function onTeacherSelect() {
  form.value.subject = null
}

function openAddDialog() {
  isEditMode.value = false
  resetForm()
  showAddDialog.value = true
}

function editCourse(course) {
  isEditMode.value = true
  const t = teachers.value.find((u) => u.name === course.teacher_name || u.id === course.teacher_id)
  const b = batches.value.find((x) => x.id === course.batch_id || x.name === course.batch?.name)
  const s = subjects.value.find(
    (x) => x.id === course.subject_id || x.name === course.subject?.name,
  )
  const h = halls.value.find((x) => x.id === course.hall_id)

  form.value = {
    id: course.id,
    teacher: t || null,
    batch: b || null,
    subject: s || null,
    hall: h || null,
    fee: course.fee_amount,
    day: course.schedule?.day || 'Monday',
    startTime: course.schedule?.start || '08:00',
    endTime: course.schedule?.end || '10:00',

  }
  showAddDialog.value = true
}

async function saveCourse() {
  submitting.value = true
  try {
    const payload = {
      name: `${form.value.batch?.name} - ${form.value.subject?.name}`,
      teacher_id: form.value.teacher?.id,
      batch_id: form.value.batch?.id,
      subject_id: form.value.subject?.id,
      hall_id: form.value.hall?.id,
      fee_amount: form.value.fee,
      schedule: {
        day: form.value.day,
        start: form.value.startTime,
        end: form.value.endTime,
        type: 'recurring',
      },

      status: 'approved',
    }

    const fetchParams = { type: viewType.value, all: true }

    if (isEditMode.value) {
      await courseStore.updateCourse(form.value.id, payload, fetchParams)
      $q.notify({ type: 'positive', message: 'Class Updated Successfully' })
    } else {
      await courseStore.addCourse(payload, fetchParams)
      $q.notify({ type: 'positive', message: 'Class Created Successfully' })
    }
    showAddDialog.value = false
  } catch (e) {
    console.error(e)
    $q.notify({ type: 'negative', message: 'Failed to save class' })
  } finally {
    submitting.value = false
  }
}

function resetForm() {
  form.value = {
    teacher: null,
    batch: null,
    subject: null,
    hall: null,
    fee: '',
    day: 'Monday',
    startTime: '08:00',
    endTime: '10:00',
  }
  // Reset halls ensures we start clean, though watcher will trigger momentarily
  availableHalls.value = halls.value
}

// Review Functions
function openReviewDialog(course) {
  reviewingCourse.value = course
  reviewNote.value = course.admin_note || ''
  reviewDialog.value = true
}

async function submitReview(status) {
  try {
    const fetchParams = { type: viewType.value, all: true }
    await courseStore.updateStatus(reviewingCourse.value.id, status, reviewNote.value, fetchParams)
    $q.notify({ type: status === 'approved' ? 'positive' : 'negative', message: `Class ${status}` })
    reviewDialog.value = false
  } catch (e) {
    console.error(e)
    $q.notify({ type: 'negative', message: 'Operation failed' })
  }
}

function deleteCourse(id) {
  $q.dialog({ title: 'Confirm', message: 'Delete this class?', cancel: true }).onOk(async () => {
    const fetchParams = { type: viewType.value, all: true }
    await courseStore.deleteCourse(id, fetchParams)
    $q.notify({ type: 'positive', message: 'Deleted' })
  })
}

function bulkDelete() {
  $q.dialog({
    title: 'Confirm',
    message: 'Delete ALL visible classes? This cannot be undone.',
    cancel: true,
    color: 'negative',
  }).onOk(async () => {
    const ids = filteredCourses.value.map((c) => c.id)
    const fetchParams = { type: viewType.value, all: true }
    await courseStore.bulkAction('delete', ids, fetchParams)
    $q.notify({ type: 'positive', message: 'Deleted All' })
  })
}

function bulkApprove() {
  $q.dialog({
    title: 'Confirm',
    message: 'Approve ALL pending requests?',
    cancel: true,
    color: 'positive',
  }).onOk(async () => {
    const ids = filteredCourses.value.map((c) => c.id)
    const fetchParams = { type: viewType.value, all: true }
    await courseStore.bulkAction('approve', ids, fetchParams)
    $q.notify({ type: 'positive', message: 'Approved All' })
  })
}

// Student Management Logic

async function addSelectedStudents() {
  if (!selectedStudents.value || selectedStudents.value.length === 0) return

  console.log('Attempting to add students:', selectedStudents.value)
  $q.loading.show()
  try {
    let addedCount = 0
    let failCount = 0

    for (const user of selectedStudents.value) {
      try {
        // Check if already in list (double check) - Type Safe
        if (studentsList.value.some((s) => String(s.id) === String(user.id))) {
          console.warn(`User ${user.name} already in list (client check)`)
          failCount++
          continue
        }

        const userId = user.id || user
        await courseStore.enrollStudent(selectedCourse.value.id, userId)
        addedCount++
      } catch (e) {
        console.warn(`Failed to enroll ${user.name}`, e)
        failCount++
      }
    }

    // Force Refresh
    studentsList.value = await courseStore.fetchStudents(selectedCourse.value.id)

    if (selectedCourse.value) {
      const current = selectedCourse.value.students_count || 0
      selectedCourse.value.students_count = current + addedCount
    }

    if (addedCount > 0) {
      $q.notify({ type: 'positive', message: `${addedCount} Students Added` })
    }
    if (failCount > 0) {
      $q.notify({
        type: 'warning',
        message: `${failCount} Students Not Added (Review API or Already Enrolled)`,
      })
    }

    selectedStudents.value = []
  } catch (e) {
    console.error('Add Students Error:', e)
    $q.notify({ type: 'negative', message: 'Process Failed' })
  } finally {
    $q.loading.hide()
  }
}
</script>

<style scoped>
.my-card {
  transition: all 0.3s;
}
.my-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.border-top-primary {
  border-top: 3px solid var(--q-primary);
}
.border-top-orange {
  border-top: 3px solid orange;
}
.border-light {
  border: 1px solid #e0e0e0;
}
.two-line-clamp {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: normal;
  line-height: 1.2em;
  height: 2.4em; /* Optional: enforce height */
}
</style>
