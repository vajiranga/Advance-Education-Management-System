<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Top Bar with Selector -->
    <div class="row items-center justify-between q-mb-lg">
      <div class="row items-center q-gutter-x-md">
         <div class="text-h5 text-weight-bold text-deep-purple">Academic Progress</div>
         <q-select 
            dense outlined 
            v-model="selectedTerm" 
            :options="['Term 1 2026', 'Term 3 2025', 'Term 2 2025']" 
            class="bg-white" 
            style="min-width: 150px"
         />
      </div>
      <q-btn icon="download" label="Report Card" outline color="deep-purple" />
    </div>

    <!-- Analytics Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
       <!-- Chart Section -->
       <div class="col-12 col-md-8">
          <q-card class="no-shadow border-left-purple bg-white full-height">
            <q-card-section>
               <div class="text-h6 q-mb-sm">Performance Analysis</div>
               <div class="text-caption text-grey q-mb-md">Marks variation across recent terms</div>
               
               <!-- Visual CSS Bar Chart -->
               <div class="row items-end justify-between q-px-md" style="height: 180px; border-bottom: 1px solid #ddd">
                  <div v-for="(stat, index) in chartData" :key="index" class="column items-center group">
                      <div class="relative-position">
                         <q-tooltip>{{ stat.marks }} Marks</q-tooltip>
                         <div 
                           class="bg-deep-purple-1 rounded-borders" 
                           :style="{ height: '150px', width: '40px', position: 'relative' }"
                         >
                            <div class="bg-deep-purple full-width absolute-bottom transition-generic" :style="{ height: (stat.marks) + '%' }"></div>
                         </div>
                      </div>
                      <div class="q-mt-sm text-caption text-grey-8">{{ stat.term }}</div>
                      <div class="text-weight-bold text-deep-purple text-caption">{{ stat.marks }}%</div>
                  </div>
               </div>
            </q-card-section>
          </q-card>
       </div>

       <!-- Summary Stats -->
       <div class="col-12 col-md-4">
          <div class="column q-gutter-y-md">
            <!-- Rank Card -->
            <q-card class="bg-deep-purple text-white text-center">
               <q-card-section>
                  <div class="text-h3 text-weight-bolder q-mb-sm">3rd</div>
                  <div class="text-subtitle1">Class Position</div>
               </q-card-section>
            </q-card>

            <!-- Average Score -->
            <q-card class="bg-white text-deep-purple text-center border-light">
               <q-card-section>
                  <div class="text-h4 text-weight-bold">85.5%</div>
                  <div class="text-subtitle1 text-grey-8">Average Score</div>
                  <q-linear-progress :value="0.85" color="deep-purple" class="q-mt-sm" />
               </q-card-section>
            </q-card>
             
             <!-- Attendance -->
             <q-card class="bg-white text-purple-9 text-center border-light">
               <q-card-section>
                  <div class="text-h4 text-weight-bold">92%</div>
                  <div class="text-subtitle1 text-grey-8">Attendance</div>
                   <q-linear-progress :value="0.92" color="green" class="q-mt-sm" />
               </q-card-section>
            </q-card>
          </div>
       </div>
    </div>

    <!-- Subject Table -->
    <q-card class="no-shadow border-light">
      <q-card-section class="row items-center justify-between">
        <div class="text-h6 text-grey-8">Detailed Results - {{ selectedTerm }}</div>
        <q-input dense outlined v-model="search" placeholder="Search subject..." class="dense-search">
           <template v-slot:prepend><q-icon name="search" /></template>
        </q-input>
      </q-card-section>
      
      <q-table
        :rows="results"
        :columns="columns"
        row-key="subject"
        flat
        :filter="search"
      >
         <template v-slot:body-cell-grade="props">
            <q-td :props="props">
               <q-chip :color="getGradeColor(props.row.grade)" text-color="white" size="sm" class="text-weight-bold shadow-1">
                  {{ props.row.grade }}
               </q-chip>
            </q-td>
         </template>
         <template v-slot:body-cell-trend="props">
            <q-td :props="props">
               <div class="row items-center no-wrap justify-center">
                  <q-icon :name="props.row.trend === 'up' ? 'trending_up' : 'trending_down'" 
                          :color="props.row.trend === 'up' ? 'green' : 'red'" 
                          class="q-mr-sm" />
                  <span class="text-caption text-grey">{{ props.row.diff }}%</span>
               </div>
            </q-td>
         </template>
         <template v-slot:body-cell-remarks="props">
            <q-td :props="props" class="text-italic text-grey-7">
               "{{ props.row.remarks }}"
            </q-td>
         </template>
      </q-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const selectedTerm = ref('Term 1 2026')
const search = ref('')

const chartData = ref([
 { term: 'Term 1', marks: 75 },
 { term: 'Term 2', marks: 82 },
 { term: 'Term 3', marks: 68 },
 { term: 'Term 1 (2026)', marks: 85 }
])

const columns = [
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left', sortable: true },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'center', sortable: true },
  { name: 'grade', label: 'Grade', field: 'grade', align: 'center' },
  { name: 'trend', label: 'Progress', field: 'trend', align: 'center' },
  { name: 'remarks', label: 'Teacher Remarks', field: 'remarks', align: 'left' }
]

const results = ref([
  { subject: 'Mathematics', marks: 95, grade: 'A', trend: 'up', diff: 5, remarks: 'Excellent performance. Keep it up!' },
  { subject: 'Science', marks: 88, grade: 'A', trend: 'down', diff: 2, remarks: 'Very good understanding of concepts.' },
  { subject: 'English', marks: 76, grade: 'B', trend: 'up', diff: 8, remarks: 'Needs to improve creative writing.' },
  { subject: 'History', marks: 82, grade: 'A', trend: 'up', diff: 3, remarks: 'Good memory of events.' },
  { subject: 'ICT', marks: 98, grade: 'A', trend: 'up', diff: 0, remarks: 'Outstanding practical skills.' },
  { subject: 'Religion', marks: 65, grade: 'C', trend: 'down', diff: 5, remarks: 'More attention needed.' }
])

const getGradeColor = (grade) => {
   if (grade === 'A') return 'green'
   if (grade === 'B') return 'blue'
   if (grade === 'C') return 'orange'
   return 'red'
}
</script>

<style scoped>
.border-left-purple {
  border-left: 4px solid #673AB7;
}
.border-light {
   border: 1px solid #eee;
}
.transition-generic {
   transition: all 0.5s ease;
}
</style>
