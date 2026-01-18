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
        <q-btn icon="add" label="New Institute" color="secondary" unelevated class="shadow-2" @click="openRegistration" />
      </div>
    </div>

    <!-- Stats Cards with Glassmorphism -->
    <div class="row q-col-gutter-md q-mb-xl">
      <div class="col-12 col-md-3" v-for="(stat, index) in stats" :key="index">
        <q-card class="stat-card glass-panel no-shadow q-py-sm cursor-pointer relative-position overflow-hidden">
          <div class="absolute-right q-ma-md opacity-20">
            <q-icon :name="stat.icon" size="6rem" :color="stat.color" />
          </div>
          <q-card-section>
            <div class="text-overline text-grey-8">{{ stat.title }}</div>
            <div class="text-h3 text-weight-bolder" :class="`text-${stat.color}`">{{ stat.value }}</div>
            <div class="row items-center q-mt-sm">
              <q-icon :name="stat.change >= 0 ? 'trending_up' : 'trending_down'" 
                :color="stat.change >= 0 ? 'green' : 'red'" class="q-mr-xs" />
              <span :class="stat.change >= 0 ? 'text-green' : 'text-red'" class="text-weight-bold">
                {{ Math.abs(stat.change) }}%
              </span>
              <span class="text-grey-6 q-ml-xs">vs last month</span>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Charts & Activity Row -->
    <div class="row q-col-gutter-md">
      <!-- Main Chart Area -->
      <div class="col-12 col-md-8">
        <q-card class="glass-panel no-shadow full-height">
          <q-card-section class="row items-center justify-between">
            <div class="text-h6 text-grey-9">Revenue Analytics</div>
            <q-select v-model="timeRange" :options="['Last 7 Days', 'Last Month', 'This Year']" dense outlined options-dense />
          </q-card-section>
          <q-card-section>
            <!-- Using ApexCharts -->
            <apexchart type="area" height="350" :options="chartOptions" :series="chartSeries"></apexchart>
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
                <q-avatar color="primary" text-color="white">{{ item.student?.name?.charAt(0) || 'U' }}</q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ item.student?.name }}</q-item-label>
                <q-item-label caption class="ellipsis">Paid for {{ item.course?.name }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge :color="item.status === 'paid' ? 'green' : 'orange'" :label="item.status" />
                <div class="text-caption text-weight-bold q-mt-xs">{{ item.amount }}</div>
              </q-item-section>
            </q-item>
            <div v-if="recentEnrollments.length === 0" class="text-center text-grey q-pa-md">No recent activity</div>
          </q-list>
        </q-card>
      </div>
    </div>

    <!-- Registration Dialog -->
    <q-dialog v-model="showRegistrationDialog">
      <q-card style="min-width: 400px" class="glass-panel">
        <q-card-section>
          <div class="text-h6 text-primary">Register New Institute</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="onSubmitRegistration" class="q-gutter-md">
            <q-input
              filled
              v-model="newInstitute.name"
              label="Institute Name"
              hint="e.g. Royal College"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Name is required']"
            />
            
            <q-input
              filled
              v-model="newInstitute.id"
              label="Institute ID (Subdomain)"
              prefix="https://"
              suffix=".ems.lk"
              hint="Unique identifier"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'ID is required']"
            />

            <q-input
              filled
              type="email"
              v-model="newInstitute.email"
              label="Admin Email"
              lazy-rules
              :rules="[ val => val && val.length > 0 || 'Email is required']"
            />

            <q-select
              filled
              v-model="newInstitute.plan_id"
              :options="['basic', 'pro', 'enterprise']"
              label="Subscription Plan"
            />

            <div align="right">
              <q-btn flat label="Cancel" color="grey" v-close-popup />
              <q-btn label="Create Institute" type="submit" color="primary" :loading="tenantStore.loading" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useFinanceStore } from 'stores/finance-store'
import { api } from 'boot/axios'

const financeStore = useFinanceStore()
const showRegistrationDialog = ref(false) // Keeping UI but might rename purpose later if needed
const timeRange = ref('Last Month')

const studentCount = ref(0)
const courseCount = ref(0)
const recentEnrollments = ref([])

onMounted(async () => {
  await Promise.all([
      financeStore.fetchAnalytics(),
      financeStore.fetchTransactions(),
      fetchCounts(),
      fetchRecentEnrollments()
  ])
})

async function fetchCounts() {
    try {
        const sRes = await api.get('/v1/users', { params: { role: 'student', per_page: 1 } })
        studentCount.value = sRes.data.total
        
        const cRes = await api.get('/v1/courses', { params: { per_page: 1 } })
        courseCount.value = cRes.data.total
    } catch (e) {
        console.error(e)
    }
}

async function fetchRecentEnrollments() {
     try {
         // Using payments as proxy for activity
         const res = await api.get('/v1/admin/payments/summary', { params: { page: 1, limit: 5 } })
         recentEnrollments.value = res.data.data
     } catch {
        // ignore
     }
}

// Dialog (Keeping placeholder)
const newInstitute = ref({ name: '', id: '', email: '', plan_id: 'pro', domain: '' })
const openRegistration = () => { showRegistrationDialog.value = true }
const onSubmitRegistration = () => {}

const stats = computed(() => [
  { title: 'Total Students', value: studentCount.value, icon: 'groups', color: 'primary', change: 0 },
  { title: 'Total Revenue', value: 'LKR ' + (financeStore.stats.revenue?.toLocaleString() || '0'), icon: 'payments', color: 'green', change: 12 },
  { title: 'Uncollected Fees', value: 'LKR ' + (financeStore.stats.pending_fees?.toLocaleString() || '0'), icon: 'warning', color: 'orange', change: -5 },
  { title: 'Active Courses', value: courseCount.value, icon: 'class', color: 'purple', change: 0 },
])

const chartSeries = computed(() => [{
  name: 'Revenue',
  data: financeStore.analyticsData.monthly.map(m => m.total)
}])

const chartOptions = computed(() => ({
  chart: {
    id: 'revenue-chart',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif'
  },
  colors: ['#10B981'],
  stroke: { curve: 'smooth', width: 3 },
  dataLabels: { enabled: false },
  xaxis: {
    categories: financeStore.analyticsData.monthly.map(m => m.month)
  },
  yaxis: {
      labels: { formatter: (val) => (val/1000).toFixed(0) + 'k' }
  },
  grid: {
    borderColor: '#f1f1f1',
  }
}))
</script>

<style lang="scss" scoped>
.glass-panel {
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  /* Optional: Add backdrop filter if placing on an image background */
}

.opacity-20 {
  opacity: 0.1;
}

.stat-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15) !important;
  }
}
</style>
