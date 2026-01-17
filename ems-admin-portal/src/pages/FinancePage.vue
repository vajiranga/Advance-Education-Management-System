<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div>
        <div class="text-h5">Financial Overview</div>
        <div class="text-caption text-grey">Manage revenue and verify payments</div>
      </div>
      <div class="row q-gutter-sm">
           <q-btn unelevated color="teal" icon="add" label="Generate Fees" @click="showGenerateDialog = true" />
           <q-btn unelevated color="purple" icon="payments" label="Record Cash" @click="showCashDialog = true" />
           <q-btn flat icon="refresh" @click="financeStore.fetchTransactions()" />
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-4">
        <q-card class="bg-blue-9 text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR {{ stats.revenue.toLocaleString() }}</div>
            <div class="text-caption">Total Revenue (Collected)</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card class="bg-orange-8 text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ pendingTransactions.length }}</div>
            <div class="text-caption">Pending Approvals</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card class="bg-indigo-7 text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR {{ stats.pending_fees.toLocaleString() }}</div>
            <div class="text-caption">Pending Amount (Uncollected)</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row q-col-gutter-lg q-mb-lg">
      <div class="col-12 col-md-8">
        <q-card>
            <q-card-section class="row justify-between">
                <div class="text-h6">Revenue Analytics</div>
                <div class="row q-gutter-sm">
                   <q-select outlined dense v-model="reportMonth" :options="monthOptions" label="Month" style="min-width: 150px" />
                   <q-btn unelevated color="secondary" label="Generate Report" icon="download" @click="generateReport" :loading="reportLoading" />
                </div>
            </q-card-section>
            <div class="q-pa-md">
                 <VueApexCharts type="area" height="350" :options="chartOptions" :series="chartSeries" />
            </div>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
          <!-- Course Breakdown -->
          <q-card class="full-height">
              <q-card-section>
                  <div class="text-h6">Top Courses</div>
              </q-card-section>
               <q-list separator>
                   <q-item v-for="(course, i) in analyticsData.courses" :key="i">
                       <q-item-section>
                           <q-item-label>{{ course.course_name }}</q-item-label>
                           <q-linear-progress :value="0.8" class="q-mt-sm" color="primary" />
                       </q-item-section>
                       <q-item-section side>
                           <div class="text-weight-bold">LKR {{ parseInt(course.total).toLocaleString() }}</div>
                       </q-item-section>
                   </q-item>
                   <div v-if="analyticsData.courses.length === 0" class="text-center text-grey q-pa-md">No Data Available</div>
               </q-list>
          </q-card>
      </div>
    </div>

    <!-- Tabs -->
    <q-card>
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="left"
          narrow-indicator
        >
          <q-tab name="pending" label="Pending Verification" icon="hourglass_empty">
             <q-badge color="orange" floating v-if="pendingTransactions.length > 0">{{ pendingTransactions.length }}</q-badge>
          </q-tab>
          <q-tab name="all" label="All Transactions" icon="list" />
          <q-tab name="settlements" label="Teacher Settlements" icon="payments" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated>
          <!-- Pending Approvals Tab -->
          <q-tab-panel name="pending" class="q-pa-none">
             <!-- ... existing ... -->
             <q-table
                :rows="pendingTransactions"
                :columns="columns"
                row-key="id"
                flat
             >
                <template v-slot:body-cell-actions="props">
                    <q-td :props="props">
                       <q-btn size="sm" color="primary" label="Verify" @click="openVerifyDialog(props.row)" />
                    </q-td>
                </template>
                 <template v-slot:body-cell-student="props">
                    <q-td :props="props">
                       <div>{{ props.row.student?.name }}</div>
                       <div class="text-caption text-grey">{{ props.row.student?.username }}</div>
                    </q-td>
                </template>
             </q-table>
             <div v-if="pendingTransactions.length === 0" class="text-center q-pa-lg text-grey">
                No pending payments to verify.
             </div>
          </q-tab-panel>

          <!-- All Transactions Tab -->
          <q-tab-panel name="all" class="q-pa-none">
             <!-- ... existing ... -->
             <q-table
                :rows="transactions"
                :columns="columns"
                row-key="id"
                flat
                :filter="filter"
             >
                <template v-slot:top-right>
                    <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
                      <template v-slot:append>
                        <q-icon name="search" />
                      </template>
                    </q-input>
                </template>
                <template v-slot:body-cell-status="props">
                  <q-td :props="props">
                    <q-chip :color="getStatusColor(props.row.status)" text-color="white" size="sm">
                      {{ props.row.status }}
                    </q-chip>
                  </q-td>
                </template>
                 <template v-slot:body-cell-student="props">
                    <q-td :props="props">
                       <div>{{ props.row.student?.name }}</div>
                       <div class="text-caption text-grey">{{ props.row.student?.username }}</div>
                    </q-td>
                </template>
             </q-table>
          </q-tab-panel>

          <!-- Settlements Tab -->
          <q-tab-panel name="settlements" class="q-pa-none">
              <q-table
                 :rows="settlements"
                 :columns="settlementColumns"
                 row-key="teacher_id"
                 flat
              >
                  <template v-slot:body-cell-share="props">
                      <q-td :props="props" class="text-weight-bold text-positive">
                          LKR {{ parseFloat(props.row.teacher_share).toLocaleString() }}
                      </q-td>
                  </template>
              </q-table>
          </q-tab-panel>
        </q-tab-panels>
    </q-card>

    <!-- Verify Dialog -->
    <q-dialog v-model="showVerifyDialog" persistent maximized transition-show="slide-up" transition-hide="slide-down">
       <!-- ... existing dialog content ... -->
    </q-dialog>

    <!-- Generate Fees Dialog -->
    <q-dialog v-model="showGenerateDialog">
        <q-card style="min-width: 350px">
            <q-card-section>
                <div class="text-h6">Generate Monthly Fees</div>
            </q-card-section>

            <q-card-section>
                <q-input outlined v-model="genMonth" mask="####-##" label="Target Month (YYYY-MM)" class="q-mb-md">
                     <template v-slot:append>
                       <q-icon name="event" class="cursor-pointer">
                         <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                           <q-date v-model="genMonth" mask="YYYY-MM" minimal emit-immediately v-close-popup />
                         </q-popup-proxy>
                       </q-icon>
                     </template>
                </q-input>
                <q-input outlined v-model="genDueDate" type="date" label="Due Date" />
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="Cancel" color="primary" v-close-popup />
                <q-btn flat label="Generate" color="primary" @click="handleGenerate" :loading="generating" />
            </q-card-actions>
        </q-card>
    </q-dialog>

    <!-- Cash Payment Dialog -->
    <q-dialog v-model="showCashDialog">
       <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
          <q-card-section>
             <div class="text-h6">Record Cash Payment</div>
             <div class="text-caption text-grey">Manual entry for counter payments</div>
          </q-card-section>
          
          <q-card-section class="q-gutter-md">
             <!-- Student Search -->
             <q-select
                outlined
                v-model="cashStudent"
                use-input
                input-debounce="300"
                label="Search Student"
                :options="studentOptions"
                @filter="filterStudents"
                option-label="name"
                option-value="id"
                hint="Type name or ID"
             >
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      No results
                    </q-item-section>
                  </q-item>
                </template>
                <template v-slot:option="scope">
                  <q-item v-bind="scope.itemProps">
                    <q-item-section>
                      <q-item-label>{{ scope.opt.name }}</q-item-label>
                      <q-item-label caption>{{ scope.opt.username }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
             </q-select>

             <!-- Course Select -->
             <q-select
                v-if="cashStudent"
                outlined
                v-model="cashCourse"
                :options="cashStudentCourses"
                option-label="name"
                option-value="id"
                label="Select Course"
             />

             <div class="row q-col-gutter-sm">
                <div class="col-6">
                    <q-input 
                        outlined 
                        v-model="cashMonth" 
                        mask="####-##" 
                        label="Month (YYYY-MM)" 
                     >
                       <template v-slot:append>
                         <q-icon name="event" class="cursor-pointer">
                           <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                             <q-date v-model="cashMonth" mask="YYYY-MM" minimal emit-immediately v-close-popup />
                           </q-popup-proxy>
                         </q-icon>
                       </template>
                    </q-input>
                </div>
                <div class="col-6">
                    <q-input outlined v-model="cashAmount" type="number" label="Amount (LKR)" />
                </div>
             </div>

             <q-input outlined v-model="cashNote" label="Note" type="textarea" rows="2" />
          </q-card-section>

          <q-card-actions align="right">
             <q-btn flat label="Cancel" v-close-popup />
             <q-btn unelevated color="green" icon="payments" label="Record Payment" @click="handleCashPayment" :loading="cashProcessing" :disable="!cashStudent || !cashCourse || !cashAmount" />
          </q-card-actions>
       </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue' // Revert watch removal if needed or just keep cleaned
import { useFinanceStore } from 'stores/finance-store'
import { api } from 'boot/axios' // Need raw api for search
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'
import VueApexCharts from 'vue3-apexcharts'

const $q = useQuasar()
const financeStore = useFinanceStore()
const { transactions, pendingTransactions, stats, analyticsData, settlements } = storeToRefs(financeStore)

const tab = ref('pending')
const filter = ref('')
const showVerifyDialog = ref(false)

// const processing = ref(false) // Removed unused
const reportLoading = ref(false)
const reportMonth = ref(new Date().toISOString().slice(0, 7))
const monthOptions = ['2026-01', '2026-02', '2026-03']

// Generate Logic
const showGenerateDialog = ref(false)
const genMonth = ref(new Date().toISOString().slice(0, 7))
const genDueDate = ref(new Date(new Date().setDate(new Date().getDate() + 10)).toISOString().slice(0, 10)) // +10 days
const generating = ref(false)

// Cash Payment Logic
const showCashDialog = ref(false)
const cashStudent = ref(null)
const cashCourse = ref(null)
const cashStudentCourses = ref([])
const cashMonth = ref(new Date().toISOString().slice(0, 7))
const cashAmount = ref('')
const cashNote = ref('')
const cashProcessing = ref(false)
const studentOptions = ref([])

// Chart Configuration
// Chart Configuration
const chartOptions = ref({
  chart: {
    type: 'area',
    height: 350,
    toolbar: { show: false }
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' },
  xaxis: {
    categories: [], // Will be updated from API
  },
  yaxis: {
    labels: {
      formatter: (val) => { return 'LKR ' + (val / 1000).toFixed(0) + 'k' }
    }
  },
  tooltip: {
    y: {
      formatter: (val) => { return 'LKR ' + val }
    }
  },
  colors: ['#1976D2']
})

const chartSeries = ref([{
  name: 'Revenue',
  data: []
}])

// Watch for analytics data to update chart
watch(() => analyticsData.value, (newVal) => {
    if (newVal && newVal.monthly_revenue) {
        chartOptions.value = {
            ...chartOptions.value,
            xaxis: {
                categories: newVal.monthly_revenue.map(item => item.month)
            }
        }
        chartSeries.value = [{
            name: 'Revenue',
            data: newVal.monthly_revenue.map(item => item.total)
        }]
    }
}, { deep: true })


async function generateReport() {
    reportLoading.value = true
    try {
        const res = await financeStore.exportReport(reportMonth.value)
        if (res.success) {
            // Create Blob and Download
            const blob = new Blob([res.data], { type: 'text/csv' })
            const link = document.createElement('a')
            link.href = URL.createObjectURL(blob)
            link.download = `ems_report_${reportMonth.value}.csv`
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
            $q.notify({ type: 'positive', message: 'Report Downloaded' })
        } else {
            $q.notify({ type: 'negative', message: 'Export Failed' })
        }
    } catch(e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Export Error' })
    } finally {
        reportLoading.value = false
    }
}

const filterStudents = (val, update) => {
    update(async () => {
        try {
             const params = { role: 'student' }
             if (val) params.search = val
             
             const res = await api.get('/v1/users', { params })
             studentOptions.value = res.data.data 
        } catch(e) {
            console.error(e)
        }
    })
}

// Watch student selection to fetch courses
watch(cashStudent, async (newVal) => {
    if (newVal && newVal.id) {
        try {
            // Ideally backend should provide enrolled courses. 
            // We can reuse getAdminPaymentSummary filter or just fetch student details
            // For now, let's fetch all active courses (simplified) or specific enrollment endpoint
            // HACK: Fetch courses via public/admin endpoint. 
            // Better: use /v1/students/{id} if exists.
            // Let's assume we can fetch enrollments.
            const res = await api.get(`/v1/users/${newVal.id}/enrollments`)
             // map to course objects
            cashStudentCourses.value = res.data.map(e => e.course)
        } catch {
            // Fallback if that endpoint missing: Fetch all courses
             const res = await api.get('/v1/courses')
             cashStudentCourses.value = res.data.data || res.data // listing
        }
    } else {
        cashStudentCourses.value = []
        cashCourse.value = null
    }
})

watch(cashCourse, (newVal) => {
    if (newVal && newVal.fee_amount) {
        cashAmount.value = newVal.fee_amount
    }
})

async function handleCashPayment() {
    cashProcessing.value = true
    try {
        const res = await financeStore.recordCashPayment({
            student_id: cashStudent.value.id,
            course_id: cashCourse.value.id,
            amount: cashAmount.value,
            month: cashMonth.value,
            note: cashNote.value
        })

        if (res.success) {
            $q.notify({ type: 'positive', message: 'Payment Recorded & Receipt Generated (Simulated)' })
            showCashDialog.value = false
            financeStore.fetchTransactions()
            financeStore.fetchAnalytics() // Update totals
            
            // Print Receipt Logic (Admin Side)
            // Reuse the print logic here if needed
            printAdminReceipt(res.payment, cashStudent.value, cashCourse.value)
            
            // Reset
            cashStudent.value = null
            cashCourse.value = null
            cashAmount.value = ''
            cashNote.value = ''
        } else {
            $q.notify({ type: 'negative', message: res.error })
        }
    } finally {
        cashProcessing.value = false
    }
}

function printAdminReceipt(payment, student, course) {
     const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (!printWindow) return;

    const htmlContent = `
        <html>
        <!-- ... (Same CSS as Student Portal) ... -->
        <head>
            <title>Payment Receipt #${payment.id}</title>
            <style>
                body { font-family: 'Helvetica', sans-serif; padding: 40px; color: #333; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .title { font-size: 24px; font-weight: bold; margin: 0; }
                .subtitle { color: #666; margin-top: 5px; }
                .details { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                .details td { padding: 12px; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; width: 150px; }
                .total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; }
                .footer { margin-top: 50px; font-size: 12px; text-align: center; color: #999; }
                .status { display: inline-block; padding: 5px 10px; background: #e8f5e9; color: #2e7d32; border-radius: 4px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">EMS Institute</div>
                <div class="subtitle">Official Payment Receipt (OFFICE COPY)</div>
            </div>

            <table class="details">
                <tr><td class="label">Receipt No:</td><td>#${payment.id}</td></tr>
                <tr><td class="label">Date:</td><td>${new Date().toLocaleString()}</td></tr>
                <tr><td class="label">Student:</td><td>${student.name} (${student.username})</td></tr>
                <tr><td class="label">Course:</td><td>${course.name}</td></tr>
                <tr><td class="label">Month:</td><td>${payment.month}</td></tr>
                <tr><td class="label">Method:</td><td>CASH [Verified]</td></tr>
            </table>

            <div class="total">Total: LKR ${payment.amount}</div>

            <div class="footer">Recorded by Admin</div>
            <script>setTimeout(() => { window.print(); window.close(); }, 500);` + '<' + '/script>' + `
        </body></html>`;
    printWindow.document.write(htmlContent);
    printWindow.document.close();
}

const getStatusColor = (status) => {
  if (status === 'paid') return 'green'
  if (status === 'pending') return 'orange'
  if (status === 'rejected') return 'red'
  return 'grey'
}

// ... (existing functions) ...

onMounted(() => {
    financeStore.fetchTransactions()
    financeStore.fetchAnalytics()
    financeStore.fetchSettlements()
})

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'student', label: 'Student', field: 'student', align: 'left' },
  { name: 'amount', label: 'Amount (LKR)', field: 'amount', align: 'right' },
  { name: 'date', label: 'Date', field: 'created_at', format: val => new Date(val).toLocaleDateString(), align: 'right' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'right' }
]

const settlementColumns = [
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'payment_count', label: 'Payments', field: 'payment_count', align: 'center' },
  { name: 'total_collected', label: 'Collected (LKR)', field: 'total_collected', align: 'right' },
  { name: 'share', label: 'Teacher Share (80%)', field: 'teacher_share', align: 'right' }
]
async function handleGenerate() {
    generating.value = true
    try {
        const res = await financeStore.generateFees({
            month: genMonth.value,
            due_date: genDueDate.value
        })
        
        if (res.success) {
             $q.notify({ type: 'positive', message: res.message })
             showGenerateDialog.value = false
             // Refresh stats just in case
             financeStore.fetchTransactions()
        } else {
             $q.notify({ type: 'negative', message: 'Error: ' + res.error })
        }
    } finally {
        generating.value = false
    }
}
</script>
