<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold text-deep-purple">Fees & Payments</div>
        <div class="text-caption text-grey-7">Safe & Secure Online Payments</div>
      </div>
      <div>
         <q-btn flat icon="receipt" label="Payment History" color="deep-purple" />
      </div>
    </div>

    <div class="row q-col-gutter-lg">
       <!-- Pending Dues -->
       <div class="col-12 col-md-7">
          <q-card class="bg-white no-shadow border-light q-mb-md">
            <q-card-section>
               <div class="text-h6 q-mb-md">Pending Dues</div>
               
               <div v-if="pendingFees.length > 0" class="column q-gutter-y-md">
                 <q-card v-for="fee in pendingFees" :key="fee.id" class="no-shadow bg-grey-1 q-pa-sm border-left-red">
                    <q-card-section horizontal class="items-center justify-between">
                       <q-card-section>
                          <div class="text-h6">{{ fee.title }}</div>
                          <div class="text-caption text-grey">Due: {{ fee.dueDate }}</div>
                          <q-chip size="sm" color="red-1" text-color="red" label="Overdue" v-if="fee.isOverdue" class="q-mt-xs"/>
                       </q-card-section>
                       <q-card-section class="text-right">
                          <div class="text-h5 text-primary text-weight-bold">{{ fee.amount }} LKR</div>
                          <q-btn unelevated color="deep-purple" label="Pay Now" class="q-mt-sm" @click="payNow(fee)" :loading="processingId === fee.id" />
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
          <q-card class="bg-white no-shadow border-light q-mb-md">
             <q-card-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm">Saved Payment Methods</div>
                <q-list bordered separator class="rounded-borders">
                   <q-item clickable v-ripple>
                      <q-item-section avatar><q-icon name="credit_card" color="blue" /></q-item-section>
                      <q-item-section>
                         <q-item-label>Visa ending in 4242</q-item-label>
                         <q-item-label caption>Expires 12/28</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-radio v-model="selectedCard" val="visa" />
                      </q-item-section>
                   </q-item>
                   <q-item clickable v-ripple>
                      <q-item-section avatar><q-icon name="add" /></q-item-section>
                      <q-item-section>Add New Card</q-item-section>
                   </q-item>
                </q-list>
             </q-card-section>
          </q-card>

          <!-- Recent Transactions -->
          <q-card class="bg-white no-shadow border-light">
             <q-card-section>
                <div class="text-subtitle1 text-weight-bold q-mb-sm">Recent Transactions</div>
                <q-scroll-area style="height: 300px;">
                  <q-timeline color="deep-purple">
                     <q-timeline-entry
                       v-for="history in transactionHistory"
                       :key="history.id"
                       :title="history.title"
                       :subtitle="history.date"
                       icon="done"
                     >
                       <div>
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
import { ref } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const selectedCard = ref('visa')
const processingId = ref(null)

const pendingFees = ref([
 { id: 1, title: 'Term 1 School Fees', amount: '15,000.00', dueDate: 'Jan 10, 2026', isOverdue: true },
 { id: 2, title: 'Sports Meet Contribution', amount: '2,000.00', dueDate: 'Feb 01, 2026', isOverdue: false }
])

const transactionHistory = ref([
  { id: 101, title: 'Book List Payment', date: 'Dec 15, 2025', amount: '4,500.00', method: 'Visa **4242' },
  { id: 102, title: 'Facility Fees - Term 3', date: 'Sept 01, 2025', amount: '10,000.00', method: 'Cash' },
  { id: 103, title: 'Term 2 School Fees', date: 'May 10, 2025', amount: '15,000.00', method: 'Visa **4242' }
])

const payNow = (fee) => {
   processingId.value = fee.id
   // Simulate Payment Process
   setTimeout(() => {
     $q.notify({
       type: 'positive',
       message: 'Payment Successful!',
       position: 'top',
       icon: 'check_circle'
     })
     
     // Move to History
     pendingFees.value = pendingFees.value.filter(f => f.id !== fee.id)
     transactionHistory.value.unshift({
        id: Date.now(),
        title: fee.title,
        date: 'Just now',
        amount: fee.amount,
        method: 'Visa **4242'
     })
     
     processingId.value = null
   }, 2000)
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
