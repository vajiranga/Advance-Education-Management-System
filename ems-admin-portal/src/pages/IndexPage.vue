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
            <q-item v-for="n in 5" :key="n" clickable v-ripple>
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white">{{ n }}</q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label>Student {{ n }}</q-item-label>
                <q-item-label caption>Enrolled in Physics 2026</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge color="secondary" label="Paid" />
              </q-item-section>
            </q-item>
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
import { useQuasar } from 'quasar'
import { useTenantStore } from 'stores/tenant-store'

const $q = useQuasar()
const tenantStore = useTenantStore()

const showRegistrationDialog = ref(false)
const timeRange = ref('Last Month')

onMounted(() => {
  tenantStore.fetchTenants()
})

const newInstitute = ref({
  name: '',
  id: '',
  email: '',
  plan_id: 'pro',
  domain: ''
})

const openRegistration = () => {
  showRegistrationDialog.value = true
}

const onSubmitRegistration = async () => {
  try {
    // Auto generate domain based on ID if not set
    newInstitute.value.domain = `${newInstitute.value.id}.ems.lk`
    
    await tenantStore.createTenant(newInstitute.value)
    
    $q.notify({
      color: 'positive',
      position: 'top',
      message: 'Institute created successfully! Environment provisioning started.',
      icon: 'check'
    })
    showRegistrationDialog.value = false
    
    // Reset form
    newInstitute.value = { name: '', id: '', email: '', plan_id: 'pro', domain: '' }
  } catch (error) {
    $q.notify({
      color: 'negative',
      position: 'top',
      message: 'Failed to create institute. Please check the ID uniqueness.',
      icon: 'report_problem'
    })
  }
}

const stats = computed(() => [
  { title: 'Registered Institutes', value: tenantStore.tenants.length, icon: 'domain', color: 'primary', change: 100 },
  { title: 'Total Revenue', value: '$124,500', icon: 'payments', color: 'secondary', change: 12.5 },
  { title: 'System Load', value: '45%', icon: 'memory', color: 'accent', change: -2.4 },
  { title: 'Uptime', value: '99.9%', icon: 'cloud_done', color: 'positive', change: 0 },
])

// Chart Data (Mock)
const chartOptions = ref({
  chart: {
    id: 'revenue-chart',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif'
  },
  colors: ['#2563EB', '#10B981'],
  stroke: { curve: 'smooth', width: 3 },
  dataLabels: { enabled: false },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
  },
  grid: {
    borderColor: '#f1f1f1',
  }
})

const chartSeries = ref([{
  name: 'Income',
  data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
}])
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
