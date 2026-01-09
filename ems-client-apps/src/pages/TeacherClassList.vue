<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold text-teal-9">My Classes</div>
      <q-btn color="teal" icon="add" label="New Class" unelevated />
    </div>

    <div class="row q-col-gutter-md">
       <div class="col-12 col-md-4" v-for="cls in classes" :key="cls.id">
          <q-card class="no-shadow border-top-teal bg-white full-height column">
             <q-card-section>
                <div class="row items-center justify-between">
                   <q-badge color="teal-1" text-color="teal" :label="cls.grade" />
                   <q-btn flat round icon="more_vert" dense color="grey" />
                </div>
                <div class="text-h6 q-mt-sm">{{ cls.subject }}</div>
                <div class="text-caption text-grey">{{ cls.schedule }}</div>
             </q-card-section>

             <q-card-section class="q-pt-none">
                 <div class="row q-gutter-x-lg q-my-sm text-grey-8">
                    <div class="row items-center"><q-icon name="groups" class="q-mr-xs" /> {{ cls.students }} Students</div>
                    <div class="row items-center"><q-icon name="meeting_room" class="q-mr-xs" /> {{ cls.hall }}</div>
                 </div>
             </q-card-section>

             <q-separator />

             <q-card-actions align="right">
                <q-btn flat color="teal" label="Syllabus" />
                <q-btn flat color="teal" label="Materials" />
             </q-card-actions>
          </q-card>
       </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const classes = ref([
 { id: 1, subject: 'Mathematics', grade: 'Grade 10 - A', schedule: 'Mon & Thu | 08:00 AM - 10:00 AM', students: 45, hall: 'Main Hall' },
 { id: 2, subject: 'Mathematics (Revision)', grade: 'Grade 11', schedule: 'Sat | 08:00 AM - 12:00 PM', students: 120, hall: 'Auditorium' },
 { id: 3, subject: 'Combined Maths', grade: '2026 A/L', schedule: 'Tue | 02:00 PM - 05:00 PM', students: 30, hall: 'Hall 2' },
])
</script>

<style scoped>
.border-top-teal { border-top: 4px solid teal; }
</style>
