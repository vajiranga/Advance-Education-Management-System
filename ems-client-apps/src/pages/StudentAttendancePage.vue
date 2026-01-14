<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="q-mb-lg">
      <div class="text-h5 text-weight-bold text-primary">My Attendance</div>
      <div class="text-caption text-grey">Track your class participation</div>
    </div>

    <div v-if="loading" class="row justify-center q-py-xl">
        <q-spinner color="primary" size="3em" />
    </div>

    <div v-else-if="attendanceHistory.length === 0" class="text-center text-grey q-py-xl">
        <q-icon name="assignment_late" size="4em" />
        <div class="text-h6 q-mt-md">No attendance records found</div>
    </div>

    <div v-else class="row q-col-gutter-md">
        <div class="col-12" v-for="(record, index) in attendanceHistory" :key="index">
            <q-card class="no-shadow border-light">
                <q-expansion-item
                    expand-separator
                    header-class="bg-white"
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
                                track-color="grey-3"
                                class="q-ma-md"
                            >
                                {{ record.percentage }}%
                            </q-circular-progress>
                        </q-item-section>

                        <q-item-section>
                            <q-item-label class="text-h6">{{ record.course_name }}</q-item-label>
                            <q-item-label caption>
                                <q-badge color="green-1" text-color="green" class="q-mr-xs">{{ record.present_sessions }} Present</q-badge>
                                <q-badge color="grey-2" text-color="grey-8">{{ record.total_sessions }} Total Sessions</q-badge>
                            </q-item-label>
                        </q-item-section>
                    </template>

                    <q-card-section class="q-pa-none">
                        <q-list separator>
                            <q-item v-for="(hist, hIdx) in record.history" :key="hIdx" class="q-py-md">
                                <q-item-section avatar>
                                    <q-icon 
                                        :name="hist.status === 'present' ? 'check_circle' : 'cancel'" 
                                        :color="hist.status === 'present' ? 'green' : 'red'" 
                                        size="sm"
                                    />
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label class="text-weight-medium">{{ formatDate(hist.date) }}</q-item-label>
                                    <q-item-label caption v-if="hist.in_time">Checked in: {{ formatTime(hist.in_time) }}</q-item-label>
                                </q-item-section>
                                <q-item-section side>
                                    <q-chip 
                                        size="sm" 
                                        :color="hist.status === 'present' ? 'green-1' : 'red-1'" 
                                        :text-color="hist.status === 'present' ? 'green' : 'red'" 
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
import { onMounted } from 'vue'
import { useStudentStore } from 'stores/student-store'
import { storeToRefs } from 'pinia'
import { date as qDate } from 'quasar'

const studentStore = useStudentStore()
const { attendanceHistory, loading } = storeToRefs(studentStore)

onMounted(() => {
    studentStore.fetchAttendanceHistory()
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
    return qDate.formatDate(timeString, 'h:mm A')
}
</script>

<style scoped>
.border-light { border: 1px solid #eee; }
</style>
