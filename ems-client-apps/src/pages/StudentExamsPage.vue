<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold">Exams & Results</div>
        <div class="text-caption text-grey-7">Track your examination schedules and performance</div>
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
      class="text-grey q-mb-md"
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
               <q-card class="exam-card border-light no-shadow">
                 <q-card-section>
                   <div class="row justify-between items-center">
                      <q-chip color="blue-1" text-color="primary" icon="event">
                        {{ exam.date }}
                      </q-chip>
                      <q-chip outline color="grey" size="sm">{{ exam.duration }}</q-chip>
                   </div>
                   <div class="text-h6 q-mt-sm">{{ exam.subject }}</div>
                   <div class="text-subtitle2 text-grey-8">{{ exam.title }}</div>
                   <div class="text-caption text-grey q-mt-xs">
                     <q-icon name="schedule" /> {{ exam.time }} &nbsp;|&nbsp; <q-icon name="place" /> {{ exam.venue }}
                   </div>
                 </q-card-section>
                 <q-separator />
                 <q-card-actions align="right">
                   <q-btn flat color="primary" label="View Syllabus" />
                 </q-card-actions>
               </q-card>
             </div>
           </div>
        </div>
        <div v-else class="text-center text-grey q-pa-xl">
           <q-icon name="event_busy" size="64px" color="grey-4" />
           <div class="text-h6 q-mt-md">No upcoming exams</div>
        </div>
      </q-tab-panel>

      <!-- Results Tab -->
      <q-tab-panel name="results" class="q-pa-none">
         <q-card class="no-shadow border-light">
           <q-table
             :rows="results"
             :columns="columns"
             row-key="id"
             flat
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
                <q-td :props="props" class="text-weight-bold">
                   {{ props.row.mark }}%
                </q-td>
             </template>
           </q-table>
         </q-card>
         
         <!-- Performance Chart Placeholder -->
         <q-card class="q-mt-md no-shadow border-light q-pa-md">
            <div class="text-subtitle1 text-weight-bold q-mb-sm">Performance Analysis</div>
            <div class="text-grey text-caption text-center" style="height: 150px; border: 2px dashed #eee; border-radius: 8px; line-height: 150px;">
               [Performance Chart Component]
            </div>
         </q-card>
      </q-tab-panel>

    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const tab = ref('upcoming')

const upcomingExams = ref([
 {
    id: 1,
    subject: 'Science',
    title: 'Term 1 Evaluation',
    date: 'Jan 15, 2026',
    time: '08:30 AM',
    duration: '2 Hours',
    venue: 'Main Hall'
 },
 {
    id: 2,
    subject: 'Mathematics',
    title: 'Monthly Test - Algebra',
    date: 'Jan 20, 2026',
    time: '10:00 AM',
    duration: '1 Hour',
    venue: 'Classroom 5B'
 }
])

const columns = [
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left' },
  { name: 'exam', label: 'Exam Title', field: 'exam', align: 'left' },
  { name: 'date', label: 'Date', field: 'date', align: 'left' },
  { name: 'mark', label: 'Marks', field: 'mark', align: 'center' },
  { name: 'grade', label: 'Grade', field: 'grade', align: 'center' },
]

const results = ref([
  { id: 101, subject: 'English', exam: 'Unit Test 3', date: 'Dec 10, 2025', mark: 85, grade: 'A' },
  { id: 102, subject: 'History', exam: 'Term End Exam', date: 'Nov 25, 2025', mark: 62, grade: 'C' },
  { id: 103, subject: 'ICT', exam: 'Practical Test', date: 'Nov 20, 2025', mark: 90, grade: 'A' },
  { id: 104, subject: 'Mathematics', exam: 'Pop Quiz', date: 'Nov 15, 2025', mark: 45, grade: 'S' },
])

const getGradeColor = (grade) => {
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
</style>
