<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold text-teal-9">Exams & Marks</div>
       <q-btn color="purple" icon="upload" label="Upload Marks" />
    </div>

    <!-- Exam Cards -->
    <div class="row q-col-gutter-md">
       <div class="col-12 col-md-6" v-for="exam in exams" :key="exam.id">
          <q-card class="no-shadow border-left-teal bg-white">
             <q-card-section>
                <div class="row items-start justify-between">
                   <div>
                      <div class="text-h6">{{ exam.title }}</div>
                      <div class="text-caption text-grey">{{ exam.batch }}</div>
                   </div>
                   <q-chip :color="getStatusColor(exam.status)" text-color="white" size="sm">{{ exam.status.toUpperCase() }}</q-chip>
                </div>
                
                <q-separator class="q-my-md" />
                
                <div class="row q-col-gutter-sm text-center">
                   <div class="col-4">
                      <div class="text-h6">{{ exam.average }}</div>
                      <div class="text-caption text-grey">Average</div>
                   </div>
                   <div class="col-4">
                      <div class="text-h6">{{ exam.highest }}</div>
                      <div class="text-caption text-grey">High Score</div>
                   </div>
                   <div class="col-4">
                      <div class="text-h6">{{ exam.submitted }}/{{ exam.total }}</div>
                      <div class="text-caption text-grey">Graded</div>
                   </div>
                </div>
             </q-card-section>
             <q-card-actions align="right">
                <q-btn flat color="teal" label="View Results" />
                <q-btn flat color="teal" label="Edit Marks" />
             </q-card-actions>
          </q-card>
       </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const exams = ref([
 { id: 1, title: 'Term 1 Mathematics', batch: 'Grade 10 - A', status: 'completed', average: '72%', highest: '98%', submitted: 45, total: 45 },
 { id: 2, title: 'Pop Quiz - Algebra', batch: 'Grade 11 - Revision', status: 'grading', average: '-', highest: '-', submitted: 80, total: 120 },
])

const getStatusColor = (status) => {
   if (status === 'completed') return 'green'
   if (status === 'grading') return 'orange'
   return 'grey'
}
</script>

<style scoped>
.border-left-teal { border-left: 4px solid teal; }
</style>
