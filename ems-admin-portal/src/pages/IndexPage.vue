<template>
  <q-page class="q-pa-md bg-slate-50">
    <!-- Header Section -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h4 text-primary text-weight-bold">Dashboard</div>
        <div class="text-subtitle1 text-grey-7">Overview of your Educational Empire</div>
      </div>
      <div class="row q-gutter-sm">
        <q-btn icon="download" label="Export Report" color="primary" flat class="glass-btn" />
        <q-btn
          icon="campaign"
          label="Send Broadcast"
          color="secondary"
          unelevated
          class="shadow-2"
          @click="openBroadcastDialog"
        />
      </div>
    </div>

    <!-- Stats Cards with Glassmorphism -->
    <div class="row q-col-gutter-md q-mb-xl items-stretch">
      <div class="col-12 col-sm-6 col-md-3" v-for="(stat, index) in stats" :key="index">
        <q-card :class="`stat-card bg-${stat.color} text-white column justify-between full-height`">
          <q-card-section class="q-pa-md stats-content col column justify-between">
            <!-- Header -->
            <div class="row items-center justify-between q-mb-sm">
              <div class="text-caption text-uppercase opacity-80 letter-spacing">
                {{ stat.title }}
              </div>
              <q-icon :name="stat.icon" size="32px" class="opacity-50" />
            </div>

            <!-- Value Area -->
            <div class="q-my-md relative-position">
              <!-- Check if value starts with LKR to handle currency styling specifically -->
              <div
                v-if="String(stat.value).startsWith('LKR')"
                :class="['number-display text-weight-bold', getFontSizeClass(stat.value)]"
              >
                <span class="text-h5 opacity-80" style="vertical-align: middle; margin-right: 4px"
                  >LKR</span
                >
                <span style="vertical-align: middle">{{
                  String(stat.value).replace('LKR ', '')
                }}</span>
              </div>
              <div
                v-else
                :class="['text-weight-bold number-display', getFontSizeClass(stat.value)]"
              >
                {{ stat.value }}
              </div>
            </div>

            <!-- Footer -->
            <div class="text-caption row items-center">
              <q-icon
                :name="stat.change >= 0 ? 'arrow_upward' : 'arrow_downward'"
                size="14px"
                class="q-mr-xs"
              />
              <span class="text-weight-bold">{{ Math.abs(stat.change) }}%</span>
              <span class="opacity-80 q-ml-xs">from last month</span>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Pending Actions Widget -->
    <div class="row q-col-gutter-md q-mb-lg" v-if="pendingActions.total_pending > 0">
      <div class="col-12">
        <q-card class="bg-red-1" flat bordered>
          <q-card-section class="row items-center justify-between">
            <div class="row items-center q-gutter-sm">
               <q-icon name="warning" color="negative" size="md" />
               <div class="text-h6 text-negative">Action Required</div>
            </div>
            <!-- <q-btn label="View All" flat color="negative" /> -->
          </q-card-section>

          <q-separator />

          <q-card-section class="q-pa-none">
             <q-list separator>
                 <q-item v-if="pendingActions.pending_classes > 0" clickable v-ripple @click="$router.push('/classes?status=pending')">
                    <q-item-section avatar>
                        <q-avatar color="orange-1" text-color="orange" icon="class" />
                    </q-item-section>
                    <q-item-section>
                        <q-item-label class="text-weight-bold">{{ pendingActions.pending_classes }} Class Approvals Pending</q-item-label>
                        <q-item-label caption>Teachers are waiting for class approval</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-btn label="Review" color="primary" size="sm" unelevated to="/classes?status=pending" />
                    </q-item-section>
                 </q-item>

                 <q-item v-if="pendingActions.pending_payments > 0" clickable v-ripple @click="$router.push('/admin/payments?status=pending')">
                    <q-item-section avatar>
                        <q-avatar color="green-1" text-color="green" icon="payments" />
                    </q-item-section>
                    <q-item-section>
                        <q-item-label class="text-weight-bold">{{ pendingActions.pending_payments }} Payment Verifications Pending</q-item-label>
                        <q-item-label caption>Bank slips uploaded by students</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-btn label="Verify" color="primary" size="sm" unelevated to="/admin/payments?status=pending" />
                    </q-item-section>
                 </q-item>
             </q-list>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Charts & Activity Row -->
    <div class="row q-col-gutter-md">
      <!-- Today's Classes Area -->
      <div class="col-12 col-md-8">
        <q-card class="glass-panel no-shadow full-height">
          <q-card-section class="row items-center justify-between">
            <div class="text-h6 text-grey-9">Today's Classes</div>
            <q-input
              v-model="selectedDate"
              type="date"
              outlined
              dense
              label="Select Date"
              @update:model-value="fetchTodayClasses"
            />
          </q-card-section>
          <q-card-section class="q-pt-none">
            <q-table
              :rows="todayClasses"
              :columns="classColumns"
              row-key="id"
              flat
              :pagination="{ rowsPerPage: 10 }"
              :loading="loadingClasses"
            >
              <template v-slot:body-cell-type="props">
                <q-td :props="props">
                  <q-badge
                    :color="props.row.is_extra ? 'orange' : 'blue'"
                    :label="props.row.is_extra ? 'Extra' : 'Regular'"
                  />
                </q-td>
              </template>
              <template v-slot:body-cell-time="props">
                <q-td :props="props">
                  {{ props.row.start_time }} - {{ props.row.end_time }}
                </q-td>
              </template>
              <template v-slot:no-data>
                <div class="text-center text-grey q-pa-md">
                  <q-icon name="event_busy" size="48px" class="q-mb-sm" />
                  <div>No classes scheduled for this date</div>
                </div>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </div>

      <!-- Recent Activity / Leaderboard -->
      <div class="col-12 col-md-4">
        <q-card class="glass-panel no-shadow full-height">
          <q-card-section>
            <div class="text-h6 text-grey-9">Recent Enrollments</div>
          </q-card-section>
          <q-list separator>
            <q-item v-for="(item, i) in recentEnrollments" :key="i" clickable v-ripple>
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white">{{
                  item.student?.name?.charAt(0) || 'U'
                }}</q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ item.student?.name }}</q-item-label>
                <q-item-label caption class="ellipsis"
                  >Paid for {{ item.course?.name }}</q-item-label
                >
              </q-item-section>
              <q-item-section side>
                <q-badge
                  :color="item.status === 'paid' ? 'green' : 'orange'"
                  :label="item.status"
                />
                <div class="text-caption text-weight-bold q-mt-xs">{{ item.amount }}</div>
              </q-item-section>
            </q-item>
            <div v-if="recentEnrollments.length === 0" class="text-center text-grey q-pa-md">
              No recent activity
            </div>
          </q-list>
        </q-card>
      </div>
    </div>

    <!-- Broadcast Dialog -->
    <q-dialog v-model="showBroadcastDialog">
      <q-card style="min-width: 500px" class="glass-panel">
        <q-card-section>
          <div class="text-h6 text-primary">Send General Notice</div>
          <div class="text-caption text-grey">
            This message will be visible to ALL parents/students.
          </div>
        </q-card-section>

        <q-card-section class="q-pt-none q-gutter-md">
          <q-input
            v-model="broadcastForm.title"
            label="Title"
            outlined
            :rules="[(val) => !!val || 'Required']"
          />

          <q-select
            v-model="broadcastForm.target"
            :options="[
              { label: 'Everyone (Students, Teachers, Parents)', value: 'all' },
              { label: 'Students Only', value: 'student' },
              { label: 'Teachers Only', value: 'teacher' },
              { label: 'Parents Only', value: 'parent' }
            ]"
            label="Target Audience"
            outlined
            emit-value
            map-options
          />

          <q-input
            v-model="broadcastForm.message"
            label="Message"
            type="textarea"
            outlined
            :rules="[(val) => !!val || 'Required']"
          />

          <div class="bg-blue-1 text-blue-9 q-pa-sm rounded-borders">
            <q-icon name="info" /> Sending to <strong>{{ targetLabel }}</strong>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn
            label="Send Broadcast"
            color="secondary"
            @click="sendBroadcast"
            :loading="sending"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFinanceStore } from 'stores/finance-store'
import { api } from 'boot/axios'

import { useQuasar } from 'quasar'
// ... imports

const $q = useQuasar()
const financeStore = useFinanceStore()
const studentCount = ref(0)
const courseCount = ref(0)
const recentEnrollments = ref([])
const showBroadcastDialog = ref(false)
const sending = ref(false)
const broadcastForm = ref({ title: '', message: '', target: 'all' })
const pendingActions = ref({ pending_classes: 0, pending_payments: 0, total_pending: 0 })

// Today's Classes
const todayClasses = ref([])
const loadingClasses = ref(false)
const selectedDate = ref(new Date().toISOString().split('T')[0])

const classColumns = [
  { name: 'name', label: 'Class Name', align: 'left', field: 'name', sortable: true },
  { name: 'teacher', label: 'Teacher', align: 'left', field: 'teacher', sortable: true },
  { name: 'subject', label: 'Subject', align: 'left', field: 'subject', sortable: true },
  { name: 'grade', label: 'Grade', align: 'center', field: 'grade', sortable: true },
  { name: 'hall', label: 'Hall', align: 'left', field: 'hall', sortable: true },
  { name: 'time', label: 'Time', align: 'center', field: 'start_time' },
  { name: 'type', label: 'Type', align: 'center', field: 'type' }
]

// Helper for display text
const targetLabel = computed(() => {
  const map = {
    all: 'All Students, Teachers & Parents',
    student: 'All Active Students',
    teacher: 'All Teachers',
    parent: 'All Parents'
  }
  return map[broadcastForm.value.target] || 'Users'
})

onMounted(async () => {
  await Promise.all([
    financeStore.fetchAnalytics(),
    financeStore.fetchTransactions(),
    fetchCounts(),
    fetchPendingActions(),
    fetchRecentEnrollments(),
    fetchTodayClasses()
  ])
})

async function fetchPendingActions() {
    try {
        const res = await api.get('/v1/admin/dashboard/pending')
        pendingActions.value = res.data
    } catch (e) {
        console.error('Failed to fetch pending actions', e)
    }
}

async function fetchTodayClasses() {
  loadingClasses.value = true
  try {
    const res = await api.get('/v1/admin/classes/today', {
      params: { date: selectedDate.value }
    })
    todayClasses.value = res.data
  } catch (e) {
    console.error('Failed to fetch today classes:', e)
    $q.notify({ type: 'negative', message: 'Failed to load classes' })
  } finally {
    loadingClasses.value = false
  }
}

async function fetchCounts() {
  try {
    const sRes = await api.get('/v1/users', { params: { role: 'student', per_page: 1 } })
    studentCount.value = sRes.data.total

    const cRes = await api.get('/v1/courses', { params: { per_page: 1, status: 'approved', type: 'regular' } })
    courseCount.value = cRes.data.total
  } catch (e) {
    console.error(e)
  }
}

async function fetchRecentEnrollments() {
  try {
    const res = await api.get('/v1/admin/payments/summary', { params: { page: 1, limit: 5 } })
    recentEnrollments.value = res.data.data
  } catch {
    /* ignore */
  }
}

function openBroadcastDialog() {
  broadcastForm.value = { title: '', message: '', target: 'all' }
  showBroadcastDialog.value = true
}

async function sendBroadcast() {
  if (!broadcastForm.value.title || !broadcastForm.value.message) {
    $q.notify({ type: 'warning', message: 'Please fill all details' })
    return
  }

  sending.value = true
  try {
    await api.post('/v1/notices', {
      title: broadcastForm.value.title,
      message: broadcastForm.value.message,
      type: 'notice',
      course_id: 'all', // System wide
      target_audience: broadcastForm.value.target,
      scheduled_at: new Date().toISOString(),
    })
    $q.notify({ type: 'positive', message: 'Broadcast Sent' })
    showBroadcastDialog.value = false
  } catch (e) {
    console.error(e)
    $q.notify({ type: 'negative', message: 'Failed to send' })
  } finally {
    sending.value = false
  }
}

// Logic to determine font size based on string length
function getFontSizeClass(val) {
  const str = String(val)
  // Adjust length check since we remove "LKR " for sizing if present
  const pureStr = str.replace('LKR ', '')

  if (pureStr.length > 12) return 'text-h4' // For Billions (e.g. 1,000,000,000)
  if (pureStr.length > 9) return 'text-h3' // For Millions
  return 'text-h2' // Standard
}

const stats = computed(() => [
  {
    title: 'Total Students',
    value: studentCount.value,
    icon: 'school',
    color: 'blue',
    change: 5.0,
  },
  {
    title: 'Total Revenue',
    value: 'LKR ' + (financeStore.stats.revenue?.toLocaleString() || '0'),
    icon: 'payments',
    color: 'green',
    change: 12.4,
  },
  {
    title: 'Uncollected Fees',
    value: 'LKR ' + (financeStore.stats.pending_fees?.toLocaleString() || '0'),
    icon: 'account_balance_wallet',
    color: 'orange',
    change: -3.2,
  },
  {
    title: 'Active Courses',
    value: courseCount.value,
    icon: 'menu_book',
    color: 'purple',
    change: 2.1,
  },
])
</script>

<style lang="scss" scoped>
.glass-panel {
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.stat-card {
  border-radius: 16px;
  /* Removed min-height to rely on flex stretch */
  transition:
    transform 0.2s,
    box-shadow 0.2s;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  }
}

.opacity-50 {
  opacity: 0.5;
}
.opacity-80 {
  opacity: 0.8;
}
.letter-spacing {
  letter-spacing: 0.5px;
}

/* Custom Gradients */
.bg-blue {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-green {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
.bg-orange {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.bg-purple {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.number-display {
  line-height: 1; /* Tighter line height to prevent overflow */
}

/* Responsive Font Sizes override if needed */
.text-h2 {
  font-size: 3.5rem;
}
.text-h3 {
  font-size: 2.5rem;
}
.text-h4 {
  font-size: 1.8rem;
}
</style>
