<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Class Management</div>
      <div class="q-gutter-md">

                 
          <q-btn color="primary" icon="add_card" label="Create New Class" @click="openAddDialog" />
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
                {label: 'Regular', value: 'regular'},
                {label: 'Extra', value: 'extra'}
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
                { label: 'Deleted', value: 'deleted', color: 'grey' }
              ]"
              color="primary"
              class="q-mb-md"
            />

            <q-input dense outlined v-model="search" placeholder="Search..." class="q-mb-md">
                <template v-slot:append><q-icon name="search" /></template>
            </q-input>
             <q-toggle v-model="showOnlyVacancies" label="Show Vacancies Only" />
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
                <q-btn v-if="statusTab === 'pending' && filteredCourses.length > 0" 
                        color="positive" icon="done_all" label="Approve All" 
                        @click="bulkApprove" />
                        
                <q-btn v-if="filteredCourses.length > 0" 
                        color="negative" icon="delete_sweep" label="Delete All" 
                        outline 
                        @click="bulkDelete" />
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
             <q-card class="my-card column full-height no-shadow border-light" :class="course.type === 'extra' ? 'border-top-orange' : 'border-top-primary'">
               <q-card-section class="col relative-position">
                 <div class="absolute-top-right q-pa-sm">
                    <q-chip :color="getStatusColor(course.status)" text-color="white" size="xs" icon="info">
                        {{ course.status === 'pending' ? 'PENDING' : (course.status ? course.status.toUpperCase() : 'UNKNOWN') }}
                    </q-chip>
                    <q-chip v-if="course.type === 'extra'" color="orange" text-color="white" size="xs" icon="star">EXTRA</q-chip>
                 </div>
                 <div class="text-h6 ellipsis q-pr-xl">{{ course.name }}</div>
                 <div class="text-subtitle2 text-primary">{{ course.batch?.name || 'Unknown Grade' }} - {{ course.subject?.name || 'Unknown Subject' }}</div>
                 <div class="text-subtitle2 text-grey-8 row items-center q-mt-sm">
                   <q-icon name="person" class="q-mr-xs" /> {{ course.teacher?.name || 'No Teacher' }}
                 </div>
                 <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                   <q-icon name="meeting_room" class="q-mr-xs" /> {{ course.hall?.name || 'No Hall' }}
                 </div>
                 <div class="text-subtitle2 text-grey-8 row items-center q-mt-xs">
                   <q-icon name="group" class="q-mr-xs" /> {{ course.students_count || 0 }} Students
                 </div>
                 <div class="text-caption text-grey q-mt-xs" v-if="course.admin_note">
                     <q-icon name="note" /> Note: {{ course.admin_note }}
                 </div>
               </q-card-section>

               <q-card-section class="q-pt-none">
                 <div class="row items-center justify-between q-mb-sm">
                   <div class="text-caption">
                     <q-icon name="schedule" color="primary" /> 
                     {{ formatSchedule(course.schedule) }}
                   </div>
                   <div class="text-caption text-weight-bold text-green">
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
                     <q-btn flat round color="primary" icon="edit" size="sm" @click="editCourse(course)">
                        <q-tooltip>Edit</q-tooltip>
                     </q-btn>
                     <q-btn flat round color="secondary" icon="group" size="sm" @click="manageStudents(course)">
                        <q-tooltip>Students</q-tooltip>
                     </q-btn>
                     <q-btn flat round color="negative" icon="delete" size="sm" @click="deleteCourse(course.id)">
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
          <div class="text-h6">Review Class</div>
          <div class="text-subtitle1">{{ reviewingCourse?.name }}</div>
        </q-card-section>

        <q-card-section>
            <q-input v-model="reviewNote" outlined type="textarea" label="Admin Note (Optional)" hint="Reason for rejection or approval note" />
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
                        :rules="[val => !!val || 'Teacher is required']"
                   >
                     <template v-slot:option="scope">
                        <q-item v-bind="scope.itemProps">
                          <q-item-section>
                            <q-item-label>{{ scope.opt.name }}</q-item-label>
                            <q-item-label caption>{{ scope.opt.email }} | {{ scope.opt.phone }}</q-item-label>
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
                                label="Grade/Batch *" 
                                :rules="[val => !!val || 'Required']" 
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
                                :rules="[val => !!val || 'Required']"
                                hint="Auto-filtered based on teacher's expertise"
                           />
                       </div>
                   </div>

                   <!-- Hall & Fee -->
                   <div class="row q-col-gutter-sm">
                       <div class="col-6">
                            <q-select 
                                outlined 
                                v-model="form.hall" 
                                :options="halls" 
                                option-label="name" 
                                label="Select Hall *" 
                                :rules="[val => !!val || 'Required']" 
                            />
                       </div>
                       <div class="col-6">
                            <q-input 
                                outlined 
                                v-model="form.fee" 
                                label="Class Fee (LKR) *" 
                                type="number" 
                                prefix="LKR"
                                :rules="[val => val > 0 || 'Invalid Amount']" 
                            />
                       </div>
                   </div>

                   <q-separator class="q-my-sm" />
                   <div class="text-subtitle2 text-grey-8">Class Schedule</div>

                   <div class="row q-col-gutter-sm">
                       <div class="col-4">
                           <q-select 
                                outlined 
                                v-model="form.day" 
                                :options="['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']" 
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

                   <q-input v-model="form.admin_note" outlined type="textarea" label="Admin Note / Remarks" rows="2" />

                   <div class="row justify-end q-mt-md">
                       <q-btn label="Cancel" flat color="grey" v-close-popup />
                       <q-btn :label="isEditMode ? 'Update Class' : 'Create Class'" type="submit" color="primary" :loading="submitting" />
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
                            outlined dense
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
                                <q-btn round dense flat icon="person_add" color="primary" @click="addSelectedStudents" :disable="!selectedStudents || selectedStudents.length === 0">
                                    <q-tooltip>Add Selected</q-tooltip>
                                </q-btn>
                            </template>
                            <template v-slot:option="scope">
                                <q-item v-bind="scope.itemProps">
                                    <q-item-section>
                                        <q-item-label>{{ scope.opt.name }}</q-item-label>
                                        <q-item-label caption>{{ scope.opt.username }} | {{ scope.opt.email }}</q-item-label>
                                    </q-item-section>
                                </q-item>
                            </template>
                            <template v-slot:no-option>
                                <q-item><q-item-section class="text-grey">Type to search by Name or ID</q-item-section></q-item>
                            </template>
                         </q-select>
                     </div>
                 </div>

                 <!-- Student List -->
                 <q-list separator bordered class="rounded-borders scroll" style="max-height: 400px">
                     <q-item v-for="student in studentsList" :key="student.id">
                         <q-item-section avatar>
                             <q-avatar size="sm" color="primary" text-color="white">{{ student.name.charAt(0) }}</q-avatar>
                         </q-item-section>
                         <q-item-section>
                             <q-item-label>{{ student.name }}</q-item-label>
                             <q-item-label caption>
                                 {{ student.phone || 'No Phone' }} | {{ new Date(student.pivot?.enrolled_at || student.created_at).toLocaleDateString() }}
                             </q-item-label>
                         </q-item-section>
                         <q-item-section side>
                             <q-chip size="xs" :color="student.pivot?.status === 'active' ? 'green' : 'red'" text-color="white">
                                 {{ student.pivot?.status }}
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
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { useCourseStore } from 'stores/course-store'
import { useUserStore } from 'stores/user-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const courseStore = useCourseStore()
const userStore = useUserStore()
const { courses, loading, subjects, batches, halls } = storeToRefs(courseStore)
const { teachers } = storeToRefs(userStore)

const statusTab = ref('all')
const search = ref('')
const showOnlyVacancies = ref(false)
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
        update(() => { searchResults.value = selectedStudents.value })
        return 
    }
    try {
        const res = await api.get('/users', { params: { role: 'student', search: val } })
        
        // Exclude Currently Enrolled Students AND Already Selected
        const enrolledIds = new Set(studentsList.value.map(u => String(u.id)))
        const selectedIds = new Set(selectedStudents.value.map(u => String(u.id)))
        
        const newOptions = res.data.filter(u => !selectedIds.has(String(u.id)) && !enrolledIds.has(String(u.id)))
        
        update(() => {
            searchResults.value = [ ...selectedStudents.value, ...newOptions ]
        })
    } catch { abort() }
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
    admin_note: ''
})



const viewType = ref('regular')

onMounted(async () => {
    courseStore.fetchCourses({ type: viewType.value })
    courseStore.fetchMetadata()
    await userStore.fetchTeachers()
    teacherOptions.value = teachers.value
})

watch(viewType, (newType) => {
    courseStore.fetchCourses({ type: newType })
})

const filteredCourses = computed(() => {
    return courses.value.filter(c => {
        // Type Filter (Safeguard)
        const cType = c.type || 'regular'
        if (cType !== viewType.value) return false

        // Status Filter
        if (statusTab.value !== 'all' && c.status !== statusTab.value) return false
        
        // Search
        if (search.value) {
            const s = search.value.toLowerCase()
            if (!c.name.toLowerCase().includes(s) && !c.teacher_name?.toLowerCase().includes(s)) return false
        }
        
        return true
    })
})

function getStatusColor(status) {
    if (status === 'approved') return 'green'
    if (status === 'rejected') return 'red'
    return 'orange'
}

function formatSchedule(schedule) {
    if (!schedule) return 'Not Set'
    if (typeof schedule === 'string') return schedule
    if (schedule.day) return `${schedule.day} ${schedule.start || ''}-${schedule.end || ''}`
    return JSON.stringify(schedule)
}

function filterTeachers (val, update) {
    if (val === '') {
        update(() => {
            teacherOptions.value = teachers.value
        })
        return
    }
    update(() => {
        const needle = val.toLowerCase()
        teacherOptions.value = teachers.value.filter(v => v.name.toLowerCase().indexOf(needle) > -1)
    })
}

const filteredSubjects = computed(() => {
    if (!form.value.teacher || !form.value.teacher.subjects || form.value.teacher.subjects.length === 0) {
        return subjects.value
    }
    const tSubjects = form.value.teacher.subjects.map(s => String(s).toLowerCase())
    return subjects.value.filter(sub => tSubjects.includes(sub.name.toLowerCase()) || tSubjects.includes(String(sub.id)))
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
    const t = teachers.value.find(u => u.name === course.teacher_name || u.id === course.teacher_id)
    const b = batches.value.find(x => x.id === course.batch_id || x.name === course.batch?.name)
    const s = subjects.value.find(x => x.id === course.subject_id || x.name === course.subject?.name)
    const h = halls.value.find(x => x.id === course.hall_id)

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
        admin_note: course.admin_note
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
            schedule: { day: form.value.day, start: form.value.startTime, end: form.value.endTime, type: 'recurring' },
            admin_note: form.value.admin_note,
            status: 'approved'
        }

        if (isEditMode.value) {
             await courseStore.updateCourse(form.value.id, payload)
             $q.notify({ type: 'positive', message: 'Class Updated Successfully' })
        } else {
            await courseStore.addCourse(payload)
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
        teacher: null, batch: null, subject: null, hall: null,
        fee: '', day: 'Monday', startTime: '08:00', endTime: '10:00', admin_note: ''
    }
}

// Review Functions
function openReviewDialog(course) {
    reviewingCourse.value = course
    reviewNote.value = course.admin_note || ''
    reviewDialog.value = true
}

async function submitReview(status) {
    try {
        await courseStore.updateStatus(reviewingCourse.value.id, status, reviewNote.value)
        $q.notify({ type: status === 'approved' ? 'positive' : 'negative', message: `Class ${status}` })
        reviewDialog.value = false
    } catch (e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Operation failed' })
    }
}

function deleteCourse(id) {
     $q.dialog({ title: 'Confirm', message: 'Delete this class?', cancel: true }).onOk(async () => {
         await courseStore.deleteCourse(id)
         $q.notify({ type: 'positive', message: 'Deleted' })
     })
}

function bulkDelete() {
     $q.dialog({ title: 'Confirm', message: 'Delete ALL visible classes? This cannot be undone.', cancel: true, color: 'negative' }).onOk(async () => {
         const ids = filteredCourses.value.map(c => c.id)
         await courseStore.bulkAction('delete', ids)
         $q.notify({ type: 'positive', message: 'Deleted All' })
     })
}

function bulkApprove() {
     $q.dialog({ title: 'Confirm', message: 'Approve ALL pending requests?', cancel: true, color: 'positive' }).onOk(async () => {
         const ids = filteredCourses.value.map(c => c.id)
         await courseStore.bulkAction('approve', ids)
         $q.notify({ type: 'positive', message: 'Approved All' })
     })
}

// Student Management Logic




async function addSelectedStudents() {
    if (!selectedStudents.value || selectedStudents.value.length === 0) return

    console.log('Attempting to add students:', selectedStudents.value)
    $q.loading.show()
    try {
        let addedCount = 0;
        let failCount = 0;
        
        for (const user of selectedStudents.value) {
            try {
                // Check if already in list (double check) - Type Safe
                if (studentsList.value.some(s => String(s.id) === String(user.id))) {
                    console.warn(`User ${user.name} already in list (client check)`)
                    failCount++;
                    continue;
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
             $q.notify({ type: 'warning', message: `${failCount} Students Not Added (Review API or Already Enrolled)` })
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
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.border-top-primary { border-top: 3px solid var(--q-primary); }
.border-top-orange { border-top: 3px solid orange; }
.border-light { border: 1px solid #e0e0e0; }
</style>
