<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">Fees & Payments</div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">Safe & Secure Online Payments</div>
      </div>
      <div>
         <q-btn flat icon="receipt" label="Payment History" :color="$q.dark.isActive ? 'deep-purple-2' : 'deep-purple'" />
      </div>
    </div>

    <div class="row q-col-gutter-lg">
       <!-- Pending Dues -->
       <div class="col-12 col-md-7">
          <q-card class="no-shadow q-mb-md" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
            <q-card-section>
               <div class="text-h6 q-mb-md" :class="$q.dark.isActive ? 'text-white' : ''">Pending Dues</div>
               
               <div v-if="pendingFees.length > 0" class="column q-gutter-y-md">
                 <q-card v-for="fee in pendingFees" :key="fee.id" class="no-shadow q-pa-sm border-left-red" :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-1'">
                    <q-card-section horizontal class="items-center justify-between">
                       <q-card-section>
                          <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">{{ fee.title }}</div>
                          <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">Due: {{ fee.dueDate }}</div>
                          <q-chip size="sm" :color="$q.dark.isActive ? 'red-9' : 'red-1'" :text-color="$q.dark.isActive ? 'red-2' : 'red'" label="Overdue" v-if="fee.isOverdue" class="q-mt-xs"/>
                       </q-card-section>
                       <q-card-section class="text-right">
                          <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-primary'">{{ fee.amount }} LKR</div>
                          <q-btn unelevated :color="$q.dark.isActive ? 'deep-purple-6' : 'deep-purple'" label="Pay Now" class="q-mt-sm" @click="payNow(fee)" :loading="processingId === fee.id" />
                       </q-card-section>
                    </q-card-section>
                 </q-card>
               </div>

               <div v-else class="text-center q-pa-lg text-grey">
                  <q-icon name="check_circle" size="64px" color="green-4" />
                  <div class="text-subtitle1 q-mt-md">No pending dues!</div>
               </div>
            </q-card-section>
          </q-card>
       </div>

       <!-- Payment History & Methods -->
       <div class="col-12 col-md-5">
          <!-- Saved Cards -->
          <q-card class="no-shadow q-mb-md" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
             <q-card-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">Saved Payment Methods</div>
                <q-list bordered separator class="rounded-borders" :class="$q.dark.isActive ? 'bg-grey-9 border-grey-8' : ''">
                   <q-item clickable v-ripple :class="$q.dark.isActive ? 'text-white' : ''">
                      <q-item-section avatar><q-icon name="credit_card" color="blue" /></q-item-section>
                      <q-item-section>
                         <q-item-label>Visa ending in 4242</q-item-label>
                         <q-item-label caption :class="$q.dark.isActive ? 'text-grey-5' : ''">Expires 12/28</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-radio v-model="selectedCard" val="visa" :dark="$q.dark.isActive" />
                      </q-item-section>
                   </q-item>
                   <q-item clickable v-ripple :class="$q.dark.isActive ? 'text-white' : ''">
                      <q-item-section avatar><q-icon name="add" :color="$q.dark.isActive ? 'white' : 'grey-8'" /></q-item-section>
                      <q-item-section>Add New Card</q-item-section>
                   </q-item>
                </q-list>
             </q-card-section>
          </q-card>

          <!-- Recent Transactions -->
          <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
             <q-card-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm" :class="$q.dark.isActive ? 'text-white' : ''">Recent Transactions</div>
                <q-scroll-area style="height: 300px;">
                  <q-timeline :color="$q.dark.isActive ? 'deep-purple-2' : 'deep-purple'" :dark="$q.dark.isActive">
                     <q-timeline-entry
                       v-for="history in transactionHistory"
                       :key="history.id"
                       :title="history.title"
                       :subtitle="history.date"
                       icon="done"
                     >
                       <div :class="$q.dark.isActive ? 'text-grey-4' : ''">
                         Paid <b>{{ history.amount }} LKR</b> via {{ history.method }}
                       </div>
                     </q-timeline-entry>
                  </q-timeline>
                </q-scroll-area>
             </q-card-section>
          </q-card>
       </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q = useQuasar()
const selectedCard = ref('visa')
const processingId = ref(null)

const pendingFees = ref([])
const transactionHistory = ref([])

const fetchFees = async () => {
    try {
        const response = await api.get('/v1/parent/fees/due')
        pendingFees.value = response.data.map(fee => ({
            id: fee.id,
            title: `${fee.student_name} - ${fee.course_name} (${fee.month_label})`,
            amount: parseFloat(fee.amount).toFixed(2),
            dueDate: new Date(fee.due_date).toLocaleDateString(),
            isOverdue: fee.is_overdue,
            month: fee.month_label
        }))
    } catch (error) {
        console.error('Error fetching fees', error)
    }
}

const fetchHistory = async () => {
    try {
        const response = await api.get('/v1/my-payments')
        transactionHistory.value = response.data.data.map(payment => ({
            id: payment.id,
            title: `${payment.course?.name || 'Course'} - ${payment.month}`,
            date: new Date(payment.created_at).toLocaleDateString(),
            amount: parseFloat(payment.amount).toFixed(2),
            method: payment.type === 'card' ? 'Visa **4242' : payment.type // Mock card display
        }))
    } catch (error) {
        console.error('Error fetching history', error)
    }
}

onMounted(() => {
    fetchFees()
    fetchHistory()
})

const payNow = async (fee) => {
   processingId.value = fee.id
   
   try {
       await api.post('/v1/payments', {
           fee_id: fee.id,
           amount: fee.amount,
           type: 'card', // Hardcoded for demo
           note: 'Paid via Parent Portal'
       })

       $q.notify({
         type: 'positive',
         message: 'Payment Successful!',
         position: 'top',
         icon: 'check_circle'
       })
       
       // Refresh Data
       await fetchFees()
       await fetchHistory()

   } catch (error) {
       $q.notify({
         type: 'negative',
         message: 'Payment Failed: ' + (error.response?.data?.message || 'Unknown Error'),
         position: 'top'
       })
   } finally {
       processingId.value = null
   }
}
</script>

<style scoped>
.border-light {
  border: 1px solid #eee;
}
.border-left-red {
  border-left: 4px solid #F44336;
}
</style>
