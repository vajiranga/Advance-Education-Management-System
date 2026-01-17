<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">Attendance</div>
      <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Manage your class participation</div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="row justify-center q-py-xl">
        <q-spinner color="primary" size="3em" />
    </div>

    <!-- Main Content -->
    <div v-else class="row q-col-gutter-lg">
        
       <!-- Upcoming Classes Section (Next 24 Hours) -->
       <div class="col-12">
          <div class="text-h6 q-mb-md flex items-center">
             <q-icon name="event" class="q-mr-sm text-primary" /> Upcoming Classes (Next 24h)
          </div>
          
          <div v-if="upcoming.length === 0" class="q-pa-lg text-center bg-white rounded-borders shadow-1" :class="$q.dark.isActive ? 'bg-dark' : ''">
              <div class="text-grey">No upcoming classes in the next 24 hours.</div>
          </div>

          <div v-else class="row q-col-gutter-md">
             <div class="col-12 col-md-6" v-for="(cls, idx) in upcoming" :key="'up'+idx">
                <q-card class="my-card bordered-card" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-blue-1'">
                   <q-card-section>
                      <div class="text-subtitle2 text-primary">UPCOMING CLASS</div>
                      <div class="text-h6 text-weight-bold q-mt-xs">{{ cls.course_name }}</div>
                      <div class="text-subtitle1 q-mt-sm">
                         <q-icon name="schedule" /> {{ cls.start }} - {{ cls.end }}
                      </div>
                      <div class="text-caption text-grey-8 q-mt-xs">
                         Date: {{ formatDate(cls.date) }} <span v-if="cls.type === 'extra'" class="text-orange q-ml-sm">(Extra Class)</span>
                      </div>
                   </q-card-section>
                   <q-separator />
                   <q-card-actions align="right" class="bg-primary text-white">
                      <div class="text-weight-bold q-px-sm">
                          Please remember to attend!
                      </div>
                   </q-card-actions>
                </q-card>
             </div>
          </div>
       </div>

       <!-- Recent Status Section (Last 7 Days) -->
       <div class="col-12 q-mt-lg">
          <div class="text-h6 q-mb-md flex items-center">
             <q-icon name="history" class="q-mr-sm text-primary" /> Recent Status
          </div>

          <div v-if="recent.length === 0" class="q-pa-lg text-center bg-white rounded-borders shadow-1" :class="$q.dark.isActive ? 'bg-dark' : ''">
              <div class="text-grey">No recent classes in the last 7 days.</div>
          </div>

          <q-list v-else separator bordered class="rounded-borders bg-white" :class="$q.dark.isActive ? 'bg-dark' : ''">
             <q-item v-for="(sess, sIdx) in recent" :key="'rec'+sIdx" class="q-py-md">
                 <q-item-section avatar>
                    <q-avatar :color="sess.status === 'present' ? 'green-1' : 'red-1'" :text-color="sess.status === 'present' ? 'green' : 'red'">
                       <q-icon :name="sess.status === 'present' ? 'check' : 'close'" />
                    </q-avatar>
                 </q-item-section>
                 
                 <q-item-section>
                    <q-item-label class="text-weight-bold">{{ sess.course_name }}</q-item-label>
                    <q-item-label caption>{{ formatDate(sess.date) }} @ {{ sess.time }}</q-item-label>
                 </q-item-section>
                 
                 <q-item-section side>
                    <q-chip 
                        :color="sess.status === 'present' ? 'green' : 'red'" 
                        text-color="white"
                        class="text-uppercase text-weight-bold"
                    >
                        {{ sess.status }}
                    </q-chip>
                 </q-item-section>
             </q-item>
          </q-list>
       </div>
       
       <!-- Link to Full History -->
       <div class="col-12 flex justify-center q-mt-md">
           <q-btn flat color="primary" label="View Full Attendance History" icon="timeline" @click="showHistory = !showHistory" />
       </div>

       <!-- Full History Expansion -->
        <div v-if="showHistory" class="col-12">
            <div class="text-h6 q-mb-sm">All Time History</div>
            <div v-if="attendanceHistory.length === 0" class="text-center text-grey q-py-md">No records.</div>
            <div v-else class="row q-col-gutter-md">
                <div class="col-12" v-for="(record, index) in attendanceHistory" :key="index">
                    <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                        <q-expansion-item expand-separator>
                            <template v-slot:header>
                                <q-item-section>
                                    <q-item-label class="text-subtitle1">{{ record.course_name }}</q-item-label>
                                    <q-item-label caption>{{ record.percentage }}% Attendance</q-item-label>
                                </q-item-section>
                                <q-item-section side>
                                    <q-circular-progress show-value font-size="10px" :value="record.percentage" size="40px" :color="getHealthColor(record.percentage)">
                                        {{ record.percentage }}%
                                    </q-circular-progress>
                                </q-item-section>
                            </template>
                            <q-card-section>
                                <q-list dense>
                                    <q-item v-for="(h, i) in record.history" :key="i">
                                        <q-item-section>{{ h.date }}</q-item-section>
                                        <q-item-section side :class="h.status==='present'?'text-green':'text-red'">{{ h.status }}</q-item-section>
                                    </q-item>
                                </q-list>
                            </q-card-section>
                        </q-expansion-item>
                    </q-card>
                </div>
            </div>
        </div>

    </div>
  </q-page>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useStudentStore } from 'stores/student-store'
import { storeToRefs } from 'pinia'
import { date as qDate } from 'quasar'

const studentStore = useStudentStore()
const { dashboardData, attendanceHistory, loading } = storeToRefs(studentStore)
const showHistory = ref(false)

const upcoming = computed(() => dashboardData.value.upcoming || [])
const recent = computed(() => dashboardData.value.recent || [])

onMounted(() => {
    studentStore.fetchDashboard()
    studentStore.fetchAttendanceHistory()
})

function formatDate(dateString) {
    return qDate.formatDate(dateString, 'ddd, DD MMM')
}

function getHealthColor(percent) {
    if (percent >= 80) return 'green'
    if (percent >= 60) return 'orange'
    return 'red'
}
</script>

<style scoped>
.bordered-card {
    border: 1px solid rgba(0,0,0,0.1);
}
.bg-dark-page {
    background: #121212;
}
</style>
