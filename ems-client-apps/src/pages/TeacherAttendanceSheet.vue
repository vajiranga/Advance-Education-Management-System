<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold text-teal-9">Attendance Management</div>
      <q-badge color="orange" text-color="white" label="Today: 2026-01-08" class="q-pa-sm text-subtitle2" />
    </div>
    
    <!-- Class Selector -->
    <q-card class="q-mb-md no-shadow border-light">
       <q-card-section class="row items-center q-gutter-x-md">
          <div class="text-subtitle1 text-grey-7">Select Class:</div>
          <q-select dense outlined v-model="selectedClass" :options="['Grade 10 - Mathematics', 'Grade 11 - Revision']" style="min-width: 250px" />
          <q-btn color="teal" label="Load Students" icon="refresh" />
       </q-card-section>
    </q-card>

    <!-- Attendance Sheet -->
    <q-card class="no-shadow border-light">
       <q-card-section>
          <div class="row items-center justify-between q-mb-md">
             <div class="text-h6">Mark Attendance</div>
             <div class="row q-gutter-x-sm">
                <q-chip color="green-1" text-color="green">Present: {{ presentCount }}</q-chip>
                <q-chip color="red-1" text-color="red">Absent: {{ absentCount }}</q-chip>
             </div>
          </div>
          
          <q-separator />

          <q-list separator>
             <q-item v-for="student in attendanceList" :key="student.id" class="q-py-sm">
                <q-item-section avatar>
                   <q-avatar size="32px"><img :src="student.avatar" /></q-avatar>
                </q-item-section>
                <q-item-section>
                   <q-item-label class="text-weight-bold">{{ student.name }}</q-item-label>
                   <q-item-label caption>{{ student.idNumber }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                   <div class="row q-gutter-x-md">
                      <q-btn 
                         round 
                         :color="student.status === 'present' ? 'green' : 'grey-3'" 
                         :text-color="student.status === 'present' ? 'white' : 'grey'" 
                         icon="check" 
                         @click="student.status = 'present'" 
                         unelevated
                      />
                      <q-btn 
                         round 
                         :color="student.status === 'absent' ? 'red' : 'grey-3'" 
                         :text-color="student.status === 'absent' ? 'white' : 'grey'" 
                         icon="close" 
                         @click="student.status = 'absent'" 
                         unelevated
                      />
                   </div>
                </q-item-section>
             </q-item>
          </q-list>
          
          <q-separator />
          
          <div class="row justify-end q-pa-md">
             <q-btn color="teal" label="Save Attendance" icon="save" />
          </div>
       </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'

const selectedClass = ref('Grade 10 - Mathematics')

const attendanceList = ref([
 { id: 101, name: 'Kasun Perera', idNumber: 'EMS-1001', avatar: 'https://cdn.quasar.dev/img/boy-avatar.png', status: 'present' },
 { id: 102, name: 'Nethmi Silva', idNumber: 'EMS-1002', avatar: 'https://cdn.quasar.dev/img/avatar2.jpg', status: 'present' },
 { id: 103, name: 'Amila Fernando', idNumber: 'EMS-1003', avatar: 'https://cdn.quasar.dev/img/avatar4.jpg', status: 'absent' },
 { id: 104, name: 'Devin De Alwis', idNumber: 'EMS-1004', avatar: 'https://cdn.quasar.dev/img/boy-avatar.png', status: 'present' },
])

const presentCount = computed(() => attendanceList.value.filter(s => s.status === 'present').length)
const absentCount = computed(() => attendanceList.value.filter(s => s.status === 'absent').length)
</script>

<style scoped>
.border-light { border: 1px solid #eee; }
</style>
