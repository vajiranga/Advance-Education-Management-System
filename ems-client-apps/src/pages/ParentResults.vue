<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <!-- Top Bar with Selector -->
    <div class="row items-center justify-between q-mb-lg">
      <div class="row items-center q-gutter-x-md">
         <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">Academic Progress</div>
         <q-select 
            dense outlined 
            v-model="selectedChild" 
            :options="children" 
            option-label="name"
            label="Select Child"
            :bg-color="$q.dark.isActive ? 'dark' : 'white'"
            :dark="$q.dark.isActive" 
            style="min-width: 200px"
         />
      </div>
      <q-btn icon="download" label="Report Card" outline :color="$q.dark.isActive ? 'deep-purple-2' : 'deep-purple'" />
    </div>

    <!-- Analytics Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
       <!-- Chart Section -->
       <div class="col-12 col-md-8">
          <q-card class="no-shadow border-left-purple full-height" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
            <q-card-section>
               <div class="text-h6 q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">Performance Analysis</div>
               <div class="text-caption q-mb-md" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Marks variation across recent terms</div>
               
               <!-- Visual CSS Bar Chart -->
               <div class="row items-end justify-between q-px-md" :style="{ height: '180px', borderBottom: $q.dark.isActive ? '1px solid #444' : '1px solid #ddd' }">
                  <div v-for="(stat, index) in chartData" :key="index" class="column items-center group">
                      <div class="relative-position">
                         <q-tooltip>{{ stat.marks }} Marks</q-tooltip>
                         <div 
                           class="rounded-borders"
                           :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-deep-purple-1'" 
                           :style="{ height: '150px', width: '40px', position: 'relative' }"
                         >
                            <div class="bg-deep-purple full-width absolute-bottom transition-generic" :style="{ height: (stat.marks) + '%' }"></div>
                         </div>
                      </div>
                      <div class="q-mt-sm text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ stat.term }}</div>
                      <div class="text-weight-bold text-caption" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">{{ stat.marks }}%</div>
                  </div>
               </div>
            </q-card-section>
          </q-card>
       </div>

       <!-- Summary Stats -->
       <div class="col-12 col-md-4">
          <div class="column q-gutter-y-md">
            <!-- Rank Card -->
            <q-card class="text-white text-center" :class="$q.dark.isActive ? 'bg-deep-purple-9' : 'bg-deep-purple'">
               <q-card-section>
                  <div class="text-h3 text-weight-bolder q-mb-sm">3rd</div>
                  <div class="text-subtitle1">Class Position</div>
               </q-card-section>
            </q-card>

            <!-- Average Score -->
            <q-card class="text-center no-shadow" :class="[$q.dark.isActive ? 'bg-dark text-deep-purple-2 border-dark' : 'bg-white text-deep-purple border-light']">
               <q-card-section>
                  <div class="text-h4 text-weight-bold">85.5%</div>
                  <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">Average Score</div>
                  <q-linear-progress :value="0.85" :color="$q.dark.isActive ? 'deep-purple-2' : 'deep-purple'" class="q-mt-sm" />
               </q-card-section>
            </q-card>
             
             <!-- Attendance -->
             <q-card class="text-center no-shadow" :class="[$q.dark.isActive ? 'bg-dark text-green-2 border-dark' : 'bg-white text-purple-9 border-light']">
               <q-card-section>
                  <div class="text-h4 text-weight-bold">92%</div>
                  <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">Attendance</div>
                   <q-linear-progress :value="0.92" color="green" class="q-mt-sm" />
               </q-card-section>
            </q-card>
          </div>
       </div>
    </div>

    <!-- Subject Table -->
    <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
      <q-card-section class="row items-center justify-between">
        <div class="text-h6" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">Detailed Results - {{ selectedTerm }}</div>
        <q-input dense outlined v-model="search" placeholder="Search subject..." class="dense-search" :dark="$q.dark.isActive" :bg-color="$q.dark.isActive ? 'grey-9' : ''">
           <template v-slot:prepend><q-icon name="search" /></template>
        </q-input>
      </q-card-section>
      
      <q-table
        :rows="results"
        :columns="columns"
        row-key="subject"
        flat
        :filter="search"
        :dark="$q.dark.isActive"
        :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black'"
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
                  <span class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ props.row.diff }}%</span>
               </div>
            </q-td>
         </template>
         <template v-slot:body-cell-remarks="props">
            <q-td :props="props" class="text-italic" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'">
               "{{ props.row.remarks }}"
            </q-td>
         </template>
      </q-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { api } from 'boot/axios'

const children = ref([])
const selectedChild = ref(null)
const results = ref([])
const loading = ref(false)
const search = ref('')

// On Mount: Fetch Children
onMounted(async () => {
    try {
        const res = await api.get('/v1/parent/children')
        children.value = res.data
        if (children.value.length > 0) {
            selectedChild.value = children.value[0]
            fetchResults(selectedChild.value.id)
        }
    } catch (e) {
        console.error('Error fetching children', e)
    }
})

// Watch Child Selection
watch(selectedChild, (newVal) => {
    if(newVal) fetchResults(newVal.id)
})

async function fetchResults(childId) {
    loading.value = true
    try {
        const res = await api.get(`/v1/parent/children/${childId}/results`)
        results.value = res.data
    } catch (e) {
        console.error('Error fetching results', e)
    } finally {
        loading.value = false
    }
}

// Chart Data (Mocking trend for now based on fetched results or keeping static if no historical data)
const chartData = ref([
 { term: 'Term 1', marks: 75 },
 { term: 'Term 2', marks: 82 },
 { term: 'Term 3', marks: 68 },
 { term: 'Last Exam', marks: 85 }
])

const columns = [
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left', sortable: true },
  { name: 'exam', label: 'Exam', field: 'exam', align: 'left', sortable: true },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'center', sortable: true },
  { name: 'grade', label: 'Grade', field: 'grade', align: 'center' },
  { name: 'trend', label: 'Progress', field: 'trend', align: 'center' },
  { name: 'remarks', label: 'Remarks', field: 'remarks', align: 'left' }
]

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
