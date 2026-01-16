<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">Payments & Fees</div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Manage your class fees and view transaction history</div>
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
      class="q-mb-md"
      :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'"
    >
      <q-tab name="pending" label="Pending Fees" icon="pending_actions" />
      <q-tab name="history" label="Payment History" icon="receipt_long" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      
      <!-- Pending Fees Tab -->
      <q-tab-panel name="pending" class="q-pa-none">
         <div v-if="pending.length > 0">
           <div class="row q-col-gutter-md">
             <div class="col-12 col-md-6 col-lg-4" v-for="fee in pending" :key="fee.course_id">
               <q-card class="payment-card no-shadow q-pa-sm" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
                 <q-card-section>
                   <div class="row justify-between items-start">
                      <div>
                        <div class="text-subtitle1 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ fee.course_name }}</div>
                        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ fee.month }}</div>
                      </div>
                      <q-chip :color="$q.dark.isActive ? 'red-9' : 'red-1'" :text-color="$q.dark.isActive ? 'red-2' : 'red'" label="Due" size="sm" />
                   </div>
                   
                   <div class="text-h5 text-primary text-weight-bold q-mt-md">LKR {{ fee.amount }}</div>
                   <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">Due: Immediately</div>
                 </q-card-section>

                 <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

                 <q-card-actions align="right">
                   <q-btn unelevated color="primary" label="Pay Now" icon="payment" @click="payNow(fee)" />
                 </q-card-actions>
               </q-card>
             </div>
           </div>
        </div>
        <div v-else class="text-center text-grey q-pa-xl">
           <q-icon name="check_circle" size="64px" :color="$q.dark.isActive ? 'green-6' : 'green-4'" />
           <div class="text-h6 q-mt-md" :class="$q.dark.isActive ? 'text-grey-4' : ''">All fees paid! Great job.</div>
        </div>
      </q-tab-panel>

      <!-- History Tab -->
      <q-tab-panel name="history" class="q-pa-none">
         <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
           <q-table
             :rows="history"
             :columns="columns"
             row-key="id"
             flat
             :dark="$q.dark.isActive"
             :color="$q.dark.isActive ? 'primary' : ''"
           >
             <template v-slot:body-cell-status="props">
               <q-td :props="props">
                 <q-chip 
                    :color="$q.dark.isActive ? 'green-9' : 'green-1'" 
                    :text-color="$q.dark.isActive ? 'green-1' : 'green'" 
                    size="sm"
                    icon="check"
                 >
                   {{ props.row.status }}
                 </q-chip>
               </q-td>
             </template>
              <template v-slot:body-cell-amount="props">
                <q-td :props="props" class="text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">
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
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { usePaymentStore } from 'stores/payment-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const paymentStore = usePaymentStore()
const { pending, history } = storeToRefs(paymentStore)

const tab = ref('pending')

onMounted(() => {
    paymentStore.fetchPendingFees()
    paymentStore.fetchHistory()
})

const columns = [
  { name: 'date', label: 'Payment Date', field: 'created_at', format: val => new Date(val).toLocaleDateString(), align: 'left' },
  { name: 'course', label: 'Course', field: row => row.course?.name || 'Course', align: 'left' },
  { name: 'month', label: 'Month', field: 'month', align: 'left' },
  { name: 'amount', label: 'Amount', field: 'amount', align: 'right' },
  { name: 'method', label: 'Method', field: 'type', align: 'center' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
]

const payNow = (fee) => {
    $q.dialog({
        title: 'Confirm Payment',
        message: `Pay LKR ${fee.amount} for ${fee.course_name} (${fee.month})?`,
        cancel: true,
        persistent: true,
        ok: { label: 'Pay via Card', color: 'primary' }
    }).onOk(async () => {
        $q.loading.show({ message: 'Processing Payment...' })
        
        // Simulate Gateway Delay
        setTimeout(async () => {
            const payload = {
                fee_id: fee.id,
                amount: fee.amount,
                type: 'online', 
                note: 'Online Payment via Student Portal'
            }
            
            const res = await paymentStore.makePayment(payload)
            $q.loading.hide()
            
            if (res.success) {
                $q.notify({ type: 'positive', message: 'Payment Successful!' })
            } else {
                $q.notify({ type: 'negative', message: 'Payment Failed: ' + res.error })
            }
        }, 1500)
    })
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
