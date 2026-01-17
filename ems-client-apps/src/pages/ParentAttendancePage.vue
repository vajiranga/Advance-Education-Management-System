<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    
    <!-- Header: Child Selector -->
    <div class="row items-center justify-between q-mb-lg">
      <div class="col-12 col-sm-6">
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">Attendance</div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Manage class participation</div>
      </div>
      <div class="col-12 col-sm-6 row justify-end items-center q-gutter-x-md q-mt-sm q-mt-sm-none">
         <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : ''">Child:</div>
         <q-select 
            dense outlined 
            v-model="selectedChild" 
            :options="children" 
            option-label="name"
            :bg-color="$q.dark.isActive ? 'dark' : 'white'"
            :dark="$q.dark.isActive" 
            style="min-width: 200px"
         />
      </div>
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
              <div class="text-grey">No upcoming classes for {{ selectedChild?.name }} in the next 24 hours.</div>
          </div>

          <div v-else class="row q-col-gutter-md">
             <div class="col-12 col-md-6" v-for="(cls, idx) in upcoming" :key="'up'+idx">
                <q-card class="my-card bordered-card" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-blue-1'">
                   <q-card-section>
                      <div class="text-subtitle2 text-primary">UPCOMING CLASS: {{ selectedChild?.name }}</div>
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
                          Please ensure they attend!
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
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { api } from 'boot/axios'
import { date as qDate } from 'quasar'

const children = ref([])
const selectedChild = ref(null)
const dashboardData = ref({ upcoming: [], recent: [] })
const loading = ref(false)

const upcoming = computed(() => dashboardData.value.upcoming || [])
const recent = computed(() => dashboardData.value.recent || [])

onMounted(async () => {
    try {
        const res = await api.get('/v1/parent/children')
        children.value = res.data
        if (children.value.length > 0) {
            selectedChild.value = children.value[0]
            fetchDashboard(selectedChild.value.id)
        }
    } catch (e) {
        console.error('Error fetching children', e)
    }
})

watch(selectedChild, (newVal) => {
    if(newVal) fetchDashboard(newVal.id)
})

async function fetchDashboard(childId) {
    loading.value = true
    try {
        const res = await api.get(`/v1/attendance/dashboard`, { params: { student_id: childId } })
        dashboardData.value = res.data
    } catch (e) {
        console.error('Error fetching dashboard', e)
    } finally {
        loading.value = false
    }
}

function formatDate(dateString) {
    return qDate.formatDate(dateString, 'ddd, DD MMM')
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
