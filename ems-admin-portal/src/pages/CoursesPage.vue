<template>
  <q-page class="q-pa-md">
    <!-- Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Course Management</div>
      <q-btn color="primary" icon="add_card" label="Create New Course" @click="openAddDialog" />
    </div>

    <!-- Status Tabs -->
    <q-tabs
      v-model="statusTab"
      dense
      class="text-grey q-mb-md"
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
    >
      <q-tab name="all" label="All Courses" />
      <q-tab name="pending" label="Pending Approval" icon="pending" class="text-orange" />
      <q-tab name="approved" label="Approved" icon="check_circle" class="text-green" />
      <q-tab name="rejected" label="Rejected" icon="cancel" class="text-red" />
    </q-tabs>

    <!-- Filters & Content -->
    <div class="row q-col-gutter-lg">
      <!-- Sidebar Filters -->
      <div class="col-12 col-md-3">
        <q-card>
          <q-card-section>
            <div class="text-subtitle1 q-mb-sm text-weight-bold">Filters</div>
            <q-input dense outlined v-model="search" placeholder="Search..." class="q-mb-md">
                <template v-slot:append><q-icon name="search" /></template>
            </q-input>
             <q-toggle v-model="showOnlyVacancies" label="Show Vacancies Only" />
          </q-card-section>
        </q-card>
      </div>

      <!-- Course List -->
      <div class="col-12 col-md-9">
         <div v-if="loading" class="row justify-center q-pa-lg">
             <q-spinner size="40px" color="primary" />
         </div>
         <div v-else-if="filteredCourses.length === 0" class="text-center text-grey q-pa-lg">
             No courses found.
         </div>
         <div class="row q-col-gutter-md" v-else>
           <div class="col-12 col-md-6 col-lg-4" v-for="course in filteredCourses" :key="course.id">
             <q-card class="my-card column full-height">
               <q-img :src="course.cover_image_url || 'https://cdn.quasar.dev/img/parallax2.jpg'" style="height: 140px">
                 <div class="absolute-bottom text-subtitle2 text-center">
                   {{ course.batch?.name || 'Unknown Grade' }} - {{ course.subject?.name || 'Unknown Subject' }}
                 </div>
                 <div class="absolute-top-right q-pa-sm">
                    <q-chip :color="getStatusColor(course.status)" text-color="white" size="sm" icon="info">
                        {{ course.status ? course.status.toUpperCase() : 'UNKNOWN' }}
                    </q-chip>
                 </div>
               </q-img>

               <q-card-section class="col">
                 <div class="text-h6 ellipsis">{{ course.name }}</div>
                 <div class="text-subtitle2 text-grey-8 row items-center">
                   <q-icon name="person" class="q-mr-xs" /> {{ course.teacher?.name || 'No Teacher' }}
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
                     <q-btn flat round color="primary" icon="edit" @click="editCourse(course)" />
                     <q-btn flat round color="negative" icon="delete" @click="deleteCourse(course.id)" />
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
          <div class="text-h6">Review Course</div>
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

    <!-- Add/Edit Dialog Placeholder (Logic needs to be updated for Backend IDs) -->
    <q-dialog v-model="showAddDialog">
       <q-card>
           <q-card-section>
               <div class="text-h6">Add/Edit Course</div>
               <div class="text-caption text-red">Note: UI needs to fetch Subjects/Batches IDs to work with Backend. Currently blocked until those APIs are ready. Please approve courses created by Teachers/Seeders.</div>
           </q-card-section>
           <q-card-actions align="right">
               <q-btn flat label="Close" v-close-popup />
           </q-card-actions>
       </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useCourseStore } from 'stores/course-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const courseStore = useCourseStore()
const { courses, loading } = storeToRefs(courseStore)

const statusTab = ref('all')
const search = ref('')
const showOnlyVacancies = ref(false)

// Review Logic
const reviewDialog = ref(false)
const reviewingCourse = ref(null)
const reviewNote = ref('')

onMounted(() => {
    courseStore.fetchCourses()
})

const filteredCourses = computed(() => {
    return courses.value.filter(c => {
        // Status Filter
        if (statusTab.value !== 'all' && c.status !== statusTab.value) return false
        
        // Search
        if (search.value) {
            const s = search.value.toLowerCase()
            if (!c.name.toLowerCase().includes(s) && !c.teacher?.name.toLowerCase().includes(s)) return false
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
    // If array or object, format nicely
    return JSON.stringify(schedule)
}

function openReviewDialog(course) {
    reviewingCourse.value = course
    reviewNote.value = course.admin_note || ''
    reviewDialog.value = true
}

async function submitReview(status) {
    try {
        await courseStore.updateStatus(reviewingCourse.value.id, status, reviewNote.value)
        $q.notify({ type: status === 'approved' ? 'positive' : 'negative', message: `Course ${status}` })
        reviewDialog.value = false
    } catch (e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Operation failed' })
    }
}

function deleteCourse(id) {
     $q.dialog({ title: 'Confirm', message: 'Delete this course?', cancel: true }).onOk(async () => {
         await courseStore.deleteCourse(id)
         $q.notify({ type: 'positive', message: 'Deleted' })
     })
}

// Disable Add/Edit for this step as validation makes it complex without proper dropdown data
const showAddDialog = ref(false)
function openAddDialog() {
    showAddDialog.value = true
}
function editCourse(course) {
    console.log('Edit', course)
    // Implement edit later
    showAddDialog.value = true
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
</style>
