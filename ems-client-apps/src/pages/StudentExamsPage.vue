<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">Exams & Results</div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Track your examination schedules and performance</div>
      </div>
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
      <q-tab name="upcoming" label="Upcoming Exams" icon="event" />
      <q-tab name="results" label="My Results" icon="assignment_turned_in" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      
      <!-- Upcoming Exams Tab -->
      <q-tab-panel name="upcoming" class="q-pa-none">
        <div v-if="upcomingExams.length > 0">
           <div class="row q-col-gutter-md">
             <div class="col-12 col-md-6" v-for="exam in upcomingExams" :key="exam.id">
               <q-card class="exam-card no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                 <q-card-section>
                   <div class="row justify-between items-center">
                      <q-chip :color="$q.dark.isActive ? 'blue-9' : 'blue-1'" :text-color="$q.dark.isActive ? 'blue-1' : 'primary'" icon="event">
                        {{ exam.date }}
                      </q-chip>
                      <q-chip outline :color="$q.dark.isActive ? 'grey-4' : 'grey'" size="sm">{{ exam.duration }}</q-chip>
                   </div>
                   <div class="text-h6 q-mt-sm" :class="$q.dark.isActive ? 'text-white' : ''">{{ exam.subject }}</div>
                   <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ exam.title }}</div>
                   <div class="text-caption q-mt-xs" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
                     <q-icon name="schedule" /> {{ exam.time }} &nbsp;|&nbsp; <q-icon name="place" /> {{ exam.venue }}
                   </div>
                 </q-card-section>
                 <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
                 <q-card-actions align="right">
                   <q-btn flat color="primary" label="View Syllabus" />
                 </q-card-actions>
               </q-card>
             </div>
           </div>
        </div>
        <div v-else class="text-center text-grey q-pa-xl">
           <q-icon name="event_busy" size="64px" :color="$q.dark.isActive ? 'grey-7' : 'grey-4'" />
           <div class="text-h6 q-mt-md" :class="$q.dark.isActive ? 'text-grey-4' : ''">No upcoming exams</div>
        </div>
      </q-tab-panel>

      <!-- Results Tab -->
      <q-tab-panel name="results" class="q-pa-none">
         <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
           <q-table
             :rows="results"
             :columns="columns"
             row-key="id"
             flat
             :dark="$q.dark.isActive"
             :color="$q.dark.isActive ? 'primary' : ''"
           >
             <template v-slot:body-cell-grade="props">
               <q-td :props="props">
                 <q-chip 
                    :color="getGradeColor(props.row.grade)" 
                    text-color="white" 
                    size="sm"
                    class="text-weight-bold"
                 >
                   {{ props.row.grade }}
                 </q-chip>
               </q-td>
             </template>
             <template v-slot:body-cell-mark="props">
                <q-td :props="props" class="text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">
                   {{ props.row.mark }}%
                </q-td>
             </template>
           </q-table>
         </q-card>
         
         <!-- Performance Chart Placeholder -->
         <q-card class="q-mt-md no-shadow q-pa-md" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
            <div class="text-subtitle1 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">Performance Analysis</div>
            <div class="text-caption text-center" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'" style="height: 150px; border: 2px dashed; border-radius: 8px; line-height: 150px; border-color: inherit;">
               [Performance Chart Component]
            </div>
         </q-card>
      </q-tab-panel>

    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useExamStore } from 'stores/exam-store'

const tab = ref('upcoming')

const examStore = useExamStore()

onMounted(async () => {
    const data = await examStore.fetchMyExams()
    upcomingExams.value = data.upcoming.map(e => ({
        id: e.id,
        subject: e.subject || 'Subject',
        title: e.title,
        date: new Date(e.date).toLocaleDateString(),
        time: 'TBA', // Backend currently just stores date
        duration: 'TBA',
        venue: 'Main Hall',
        description: e.description
    }))
    
    results.value = data.results.map(r => ({
        id: r.id,
        subject: r.subject,
        exam: r.exam_title,
        date: new Date(r.date).toLocaleDateString(),
        mark: Number(r.marks), // Ensure number
        grade: r.grade || '-',
        feedback: r.feedback
    }))
})

const upcomingExams = ref([])
const results = ref([])

const columns = [
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left' },
  { name: 'exam', label: 'Exam Title', field: 'exam', align: 'left' },
  { name: 'date', label: 'Date', field: 'date', align: 'left' },
  { name: 'mark', label: 'Marks', field: 'mark', align: 'center' },
  { name: 'grade', label: 'Grade', field: 'grade', align: 'center' },
]

const getGradeColor = (grade) => {
   if (!grade) return 'grey'
   if (grade === 'A') return 'green'
   if (grade === 'B') return 'blue'
   if (grade === 'C') return 'orange'
   if (grade === 'S') return 'brown'
   return 'red'
}
</script>

<style scoped>
.exam-card {
  border-radius: 12px;
  border-left: 4px solid #1976D2;
}
.border-light {
   border: 1px solid #eee;
}
.border-dark {
   border: 1px solid #334155;
}
</style>
