<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    
    <!-- Header: Child Selector -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">Attendance Records</div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Track class participation</div>
      </div>
      <div class="row items-center q-gutter-x-md">
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

    <!-- Empty State -->
    <div v-else-if="!groupedAttendance || groupedAttendance.length === 0" class="text-center q-py-xl" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
        <q-icon name="event_busy" size="4em" />
        <div class="text-h6 q-mt-md">No attendance records found for {{ selectedChild?.name }}</div>
    </div>

    <!-- Attendance List -->
    <div v-else class="row q-col-gutter-md">
        <div class="col-12" v-for="(record, index) in groupedAttendance" :key="index">
            <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                <q-expansion-item
                    expand-separator
                    :header-class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white'"
                >
                    <template v-slot:header>
                        <q-item-section avatar>
                            <q-circular-progress
                                show-value
                                font-size="12px"
                                :value="record.percentage"
                                size="50px"
                                :thickness="0.2"
                                :color="getHealthColor(record.percentage)"
                                :track-color="$q.dark.isActive ? 'grey-8' : 'grey-3'"
                                class="q-ma-md"
                            >
                                {{ record.percentage }}%
                            </q-circular-progress>
                        </q-item-section>

                        <q-item-section>
                            <q-item-label class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">{{ record.course_name }}</q-item-label>
                            <q-item-label caption>
                                <q-badge :color="$q.dark.isActive ? 'green-9' : 'green-1'" :text-color="$q.dark.isActive ? 'green-1' : 'green'" class="q-mr-xs">{{ record.present_sessions }} Present</q-badge>
                                <q-badge :color="$q.dark.isActive ? 'grey-8' : 'grey-2'" :text-color="$q.dark.isActive ? 'grey-4' : 'grey-8'">{{ record.total_sessions }} Total Sessions</q-badge>
                            </q-item-label>
                        </q-item-section>
                    </template>

                    <q-card-section class="q-pa-none">
                        <q-list separator :class="$q.dark.isActive ? 'bg-dark' : ''">
                            <q-item v-for="(hist, hIdx) in record.history" :key="hIdx" class="q-py-md">
                                <q-item-section avatar>
                                    <q-icon 
                                        :name="hist.status === 'present' ? 'check_circle' : 'cancel'" 
                                        :color="hist.status === 'present' ? 'green' : 'red'" 
                                        size="sm"
                                    />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label class="text-weight-medium" :class="$q.dark.isActive ? 'text-grey-3' : ''">{{ formatDate(hist.date) }}</q-item-label>
                                    <q-item-label caption v-if="hist.check_in" :class="$q.dark.isActive ? 'text-grey-5' : ''">Checked in: {{ formatTime(hist.check_in) }}</q-item-label>
                                </q-item-section>
                                <q-item-section side>
                                    <q-chip 
                                        size="sm" 
                                        :color="hist.status === 'present' ? ($q.dark.isActive ? 'green-9' : 'green-1') : ($q.dark.isActive ? 'red-9' : 'red-1')" 
                                        :text-color="hist.status === 'present' ? ($q.dark.isActive ? 'green-1' : 'green') : ($q.dark.isActive ? 'red-1' : 'red')" 
                                        class="text-uppercase"
                                    >
                                        {{ hist.status }}
                                    </q-chip>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-card-section>
                </q-expansion-item>
            </q-card>
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
const rawAttendance = ref([])
const loading = ref(false)

onMounted(async () => {
    try {
        const res = await api.get('/v1/parent/children')
        children.value = res.data
        if (children.value.length > 0) {
            selectedChild.value = children.value[0]
            fetchAttendance(selectedChild.value.id)
        }
    } catch (e) {
        console.error('Error fetching children', e)
    }
})

watch(selectedChild, (newVal) => {
    if(newVal) fetchAttendance(newVal.id)
})

async function fetchAttendance(childId) {
    loading.value = true
    try {
        const res = await api.get(`/v1/parent/children/${childId}/attendance`)
        rawAttendance.value = res.data
    } catch (e) {
        console.error('Error fetching attendance', e)
    } finally {
        loading.value = false
    }
}

const groupedAttendance = computed(() => {
    const groups = {}
    rawAttendance.value.forEach(rec => {
        if (!groups[rec.course_name]) {
            groups[rec.course_name] = {
                course_name: rec.course_name,
                history: [],
                total_sessions: 0,
                present_sessions: 0
            }
        }
        groups[rec.course_name].history.push(rec)
        groups[rec.course_name].total_sessions++
        if(rec.status === 'present') groups[rec.course_name].present_sessions++
    })

    return Object.values(groups).map(g => {
        g.percentage = g.total_sessions > 0 ? Math.round((g.present_sessions / g.total_sessions) * 100) : 0
        return g
    })
})

function getHealthColor(percent) {
    if (percent >= 80) return 'green'
    if (percent >= 60) return 'orange'
    return 'red'
}

function formatDate(dateString) {
    return qDate.formatDate(dateString, 'ddd, DD MMM YYYY')
}

function formatTime(timeString) {
    if(!timeString) return ''
    // Assuming backend sends H:i:s or similar, try to make it Date object if needed or just format string
    // If string like "14:30:00", Quasar might parse it if valid ISO. 
    // Backend API `format('h:i A')` might be better or handle here.
    return timeString // Simplified for now
}
</script>

<style scoped>
.border-light { border: 1px solid #eee; }
.border-dark { border: 1px solid #334155; }
</style>
