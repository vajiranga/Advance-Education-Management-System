<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold">Payments & Fees</div>
        <div class="text-caption text-grey-7">Manage your class fees and view transaction history</div>
      </div>
    </div>

    <!-- Tabs -->
    <q-tabs
      v-model="tab"
      dense
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
      class="text-grey q-mb-md"
    >
      <q-tab name="pending" label="Pending Fees" icon="pending_actions" />
      <q-tab name="history" label="Payment History" icon="receipt_long" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      
      <!-- Pending Fees Tab -->
      <q-tab-panel name="pending" class="q-pa-none">
        <div v-if="pendingFees.length > 0">
           <div class="row q-col-gutter-md">
             <div class="col-12 col-md-6 col-lg-4" v-for="fee in pendingFees" :key="fee.id">
               <q-card class="payment-card border-light no-shadow q-pa-sm">
                 <q-card-section>
                   <div class="row justify-between items-start">
                      <div>
                        <div class="text-subtitle1 text-weight-bold">{{ fee.course }}</div>
                        <div class="text-caption text-grey">{{ fee.month }}</div>
                      </div>
                      <q-chip color="red-1" text-color="red" label="Due" size="sm" />
                   </div>
                   
                   <div class="text-h5 text-primary text-weight-bold q-mt-md">LKR {{ fee.amount }}</div>
                   <div class="text-caption text-grey">Due Date: {{ fee.dueDate }}</div>
                 </q-card-section>

                 <q-separator />

                 <q-card-actions align="right">
                   <q-btn unelevated color="primary" label="Pay Now" icon="payment" @click="payNow(fee)" />
                 </q-card-actions>
               </q-card>
             </div>
           </div>
        </div>
        <div v-else class="text-center text-grey q-pa-xl">
           <q-icon name="check_circle" size="64px" color="green-4" />
           <div class="text-h6 q-mt-md">All fees paid! Great job.</div>
        </div>
      </q-tab-panel>

      <!-- History Tab -->
      <q-tab-panel name="history" class="q-pa-none">
         <q-card class="no-shadow border-light">
           <q-table
             :rows="history"
             :columns="columns"
             row-key="id"
             flat
           >
             <template v-slot:body-cell-status="props">
               <q-td :props="props">
                 <q-chip 
                    color="green-1" 
                    text-color="green" 
                    size="sm"
                    icon="check"
                 >
                   {{ props.row.status }}
                 </q-chip>
               </q-td>
             </template>
              <template v-slot:body-cell-amount="props">
                <q-td :props="props" class="text-weight-bold">
                   LKR {{ props.row.amount }}
                </q-td>
             </template>
           </q-table>
         </q-card>
      </q-tab-panel>

    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const tab = ref('pending')

const pendingFees = ref([
 {
    id: 1,
    course: 'Grade 10 Mathematics',
    month: 'January 2026',
    amount: '2,500.00',
    dueDate: 'Jan 10, 2026'
 },
 {
    id: 2,
    course: 'Grade 10 Science',
    month: 'January 2026',
    amount: '3,000.00',
    dueDate: 'Jan 15, 2026'
 }
])

const columns = [
  { name: 'date', label: 'Payment Date', field: 'date', align: 'left' },
  { name: 'course', label: 'Course / Description', field: 'course', align: 'left' },
  { name: 'amount', label: 'Amount', field: 'amount', align: 'right' },
  { name: 'method', label: 'Method', field: 'method', align: 'center' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
]

const history = ref([
  { id: 101, date: 'Dec 10, 2025', course: 'Grade 10 Mathematics - Dec', amount: '2,500.00', method: 'Online Card', status: 'Success' },
  { id: 102, date: 'Dec 12, 2025', course: 'Grade 10 Science - Dec', amount: '3,000.00', method: 'Cash', status: 'Success' },
])

const payNow = (fee) => {
   $q.loading.show({ message: 'Processing Payment...' })
   setTimeout(() => {
      $q.loading.hide()
      $q.notify({ type: 'positive', message: `Payment for ${fee.course} Successful!` })
      
      // Move from pending to history (Demo Logic)
      pendingFees.value = pendingFees.value.filter(f => f.id !== fee.id)
      history.value.unshift({
         id: Date.now(),
         date: new Date().toLocaleDateString(),
         course: `${fee.course} - ${fee.month}`,
         amount: fee.amount,
         method: 'Online Card',
         status: 'Success'
      })
   }, 2000)
}
</script>

<style scoped>
.payment-card {
  border-radius: 12px;
  border-left: 4px solid #E53935;
}
.border-light {
   border: 1px solid #eee;
}
</style>
