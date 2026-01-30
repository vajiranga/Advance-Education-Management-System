<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-teal-2' : 'text-teal-9'">Exams & Marks</div>
       <q-btn color="teal" icon="add" label="New Exam" @click="openCreateDialog" />
    </div>

    <!-- Exam Cards -->
    <div v-if="loading" class="row justify-center q-pa-lg">
         <q-spinner size="40px" color="teal" />
    </div>
    
    <div class="row q-col-gutter-md" v-else>
       <div class="col-12 col-md-6" v-for="exam in exams" :key="exam.id">
          <q-card class="no-shadow border-left-teal" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
             <q-card-section>
                <div class="row items-start justify-between">
                   <div>
                      <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">{{ exam.title }}</div>
                      <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ exam.course?.name || 'Unknown Class' }}</div>
                      <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-8'">{{ new Date(exam.date).toLocaleDateString() }}</div>
                   </div>
                   <q-chip outline :color="$q.dark.isActive ? 'teal-2' : 'teal'" size="sm">Max: {{ exam.max_marks }}</q-chip>
                </div>
                
                <q-separator class="q-my-md" :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                
                <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">
                    {{ exam.description || 'No description provided.' }}
                </div>
             </q-card-section>
             <q-card-actions align="right">
                <q-btn flat :color="$q.dark.isActive ? 'teal-2' : 'teal'" label="Enter Marks" @click="openResultsDialog(exam.id)" />
             </q-card-actions>
          </q-card>
       </div>
       <div v-if="exams.length === 0" class="col-12 text-center text-grey q-pa-xl">
           No exams found. Create one to get started.
       </div>
    </div>

    <!-- Create/Edit Exam Dialog -->
    <q-dialog v-model="showCreateDialog">
        <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
            <q-card-section>
                <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Create New Exam</div>
            </q-card-section>
            <q-card-section class="q-gutter-md">
                 <!-- Course Select -->
                 <q-select 
                    v-model="newExam.course" 
                    :options="teacherCourses" 
                    option-label="displayName" 
                    option-value="id"
                    label="Select Class" 
                    outlined 
                    :rules="[val => !!val || 'Required']"
                    :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" 
                    :dark="$q.dark.isActive"
                 />
                 
                 <q-input v-model="newExam.title" label="Exam Title (e.g. Term 1 Test)" outlined :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                 
                 <div class="row q-col-gutter-nm">
                     <div class="col-6">
                         <q-input v-model="newExam.date" type="date" label="Date" outlined stack-label :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                     </div>
                     <div class="col-6">
                         <q-input v-model="newExam.max_marks" type="number" label="Max Marks" outlined :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                     </div>
                 </div>
                 
                 <q-input v-model="newExam.description" type="textarea" label="Description / Topics" outlined :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
            </q-card-section>
            <q-card-actions align="right">
                <q-btn flat label="Cancel" v-close-popup :color="$q.dark.isActive ? 'grey-4' : 'primary'" />
                <q-btn color="teal" label="Create" @click="createExam" :loading="loading" />
            </q-card-actions>
        </q-card>
    </q-dialog>

    <!-- Grades/Marks Dialog -->
    <q-dialog v-model="showResultsDialog" maximized transition-show="slide-up" transition-hide="slide-down">
         <q-card :class="$q.dark.isActive ? 'bg-dark-page' : ''">
             <q-bar :class="$q.dark.isActive ? 'bg-teal-9 text-white' : 'bg-teal text-white'">
                 <div class="text-subtitle2">{{ selectedExam?.title }} - Marks Entry</div>
                 <q-space />
                 <q-btn dense flat icon="close" v-close-popup />
             </q-bar>
             
             <q-card-section>
                 <div v-if="loadingResults" class="row justify-center q-pa-lg">
                     <q-spinner color="teal" size="30px" />
                 </div>
                 <div v-else>
                     <div class="row items-center justify-between q-mb-md">
                         <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-white' : ''">Student List ({{ resultList.length }})</div>
                         <q-checkbox v-model="publishResults" label="Publish Results to Students" color="teal" :dark="$q.dark.isActive" />
                     </div>
                     
                     <q-list separator bordered :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                             <!-- Header -->
                             <q-item :class="$q.dark.isActive ? 'bg-grey-9 text-white' : 'bg-grey-2 text-weight-bold'">
                                 <q-item-section>Student Name</q-item-section>
                                 <q-item-section>Marks (Max: {{ selectedExam?.max_marks }})</q-item-section>
                                 <q-item-section>Feedback (Optional)</q-item-section>
                             </q-item>
                             
                             <q-item v-for="student in resultList" :key="student.id">
                                 <q-item-section>
                                     <q-item-label :class="$q.dark.isActive ? 'text-white' : ''">{{ student.name }}</q-item-label>
                                     <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">{{ student.username }}</q-item-label>
                                 </q-item-section>
                                 <q-item-section>
                                     <q-input 
                                         v-model.number="student.marks" 
                                         type="number" 
                                         dense outlined 
                                         :rules="[val => val <= selectedExam?.max_marks || 'Max exceeded']"
                                         style="max-width: 100px"
                                         :bg-color="$q.dark.isActive ? 'grey-9' : 'white'"
                                         :dark="$q.dark.isActive"
                                     />
                                 </q-item-section>
                                 <q-item-section>
                                     <q-input v-model="student.feedback" dense outlined placeholder="Good job..." :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" :dark="$q.dark.isActive" />
                                 </q-item-section>
                             </q-item>
                             
                             <div v-if="resultList.length === 0" class="text-center text-grey q-pa-md">
                                 No students found in this class.
                             </div>
                     </q-list>
                 </div>
             </q-card-section>
             
             <q-card-actions align="right" :class="$q.dark.isActive ? 'bg-dark text-teal' : 'bg-white text-teal'">
                 <q-btn flat label="Cancel" v-close-popup :color="$q.dark.isActive ? 'white' : 'teal'" />
                 <q-btn label="Save All Results" color="teal" @click="saveResults" :loading="saving" />
             </q-card-actions>
         </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useExamStore } from 'stores/exam-store'
import { useTeacherStore } from 'stores/teacher-store'
import { useAuthStore } from 'stores/auth-store'
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const examStore = useExamStore()
const teacherStore = useTeacherStore()
const authStore = useAuthStore()

const { exams, loading } = storeToRefs(examStore)
const { courses } = storeToRefs(teacherStore)

// Results Logic
const showResultsDialog = ref(false)
const selectedExam = ref(null)
const resultList = ref([])
const loadingResults = ref(false)
const saving = ref(false)
const publishResults = ref(true) // Default to true

async function openResultsDialog(examId) {
    showResultsDialog.value = true
    loadingResults.value = true
    
    // Fetch info
    const res = await examStore.fetchResults(examId)
    
    if (res) {
        selectedExam.value = res.exam
        // Check if any published status exists
        const publishedCount = res.students.filter(s => s.is_published).length
        publishResults.value = publishedCount > 0
        
        resultList.value = res.students.map(s => ({
            id: s.id,
            name: s.name,
            username: s.username,
            marks: s.marks, // might be null
            feedback: s.feedback || ''
        }))
    }
    
    loadingResults.value = false
}

async function saveResults() {
    if(!selectedExam.value) return 
    
    saving.value = true
    
    const payload = {
        is_published: publishResults.value,
        results: resultList.value.map(s => ({
            student_id: s.id,
            marks: s.marks || 0,
            feedback: s.feedback
        }))
    }
    
    const res = await examStore.saveResults(selectedExam.value.id, payload)
    saving.value = false
    
    if (res.success) {
        $q.notify({ type: 'positive', message: 'Results Saved' })
        showResultsDialog.value = false
    } else {
        $q.notify({ type: 'negative', message: 'Failed: ' + res.error })
    }
}

onMounted(async () => {
    // Fetch Exams for this teacher
    await examStore.fetchExams({ teacher_id: authStore.user?.id })
    // Ensure courses are loaded for the dropdown
    if (courses.value.length === 0) {
        await teacherStore.fetchCourses({ teacher_id: authStore.user?.id })
    }
})

// Helper for Dropdown
const teacherCourses = computed(() => {
    return courses.value
        .filter(c => c.status === 'approved')
        .map(c => ({...c, displayName: `${c.name} (${c.batch?.name})`}))
})

// Create Logic
const showCreateDialog = ref(false)
const newExam = ref({ course: null, title: '', date: '', max_marks: 100, description: '' })

function openCreateDialog() {
    newExam.value = { course: null, title: '', date: new Date().toISOString().split('T')[0], max_marks: 100, description: '' }
    showCreateDialog.value = true
}

async function createExam() {
    if(!newExam.value.course || !newExam.value.title) {
        $q.notify({ type: 'warning', message: 'Please fill required fields' })
        return
    }

    const payload = {
        course_id: newExam.value.course.id,
        title: newExam.value.title,
        date: newExam.value.date,
        max_marks: newExam.value.max_marks,
        description: newExam.value.description
    }

    const res = await examStore.createExam(payload)
    if(res.success) {
        $q.notify({ type: 'positive', message: 'Exam Created' })
        showCreateDialog.value = false
        examStore.fetchExams({ teacher_id: authStore.user?.id })
    } else {
         $q.notify({ type: 'negative', message: 'Failed: ' + res.error })
    }
}

</script>

<style scoped>
.border-left-teal { border-left: 4px solid teal; }
</style>
