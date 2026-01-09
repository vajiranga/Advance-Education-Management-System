<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">Financial Overview</div>
      <div class="text-caption text-grey">Last Updated: Just now</div>
    </div>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-4">
        <q-card class="bg-blue-9 text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR 450,000</div>
            <div class="text-caption">Total Revenue (This Month)</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card class="bg-green-7 text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR 120,000</div>
            <div class="text-caption">Teacher Payments (Paid)</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-4">
        <q-card class="bg-orange-8 text-white">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR 45,000</div>
            <div class="text-caption">Pending Student Fees</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Charts & Lists -->
    <div class="row q-col-gutter-lg">
      <!-- Recent Transactions -->
      <div class="col-12 col-md-8">
        <q-card>
          <q-card-section>
            <div class="text-h6">Recent Transactions</div>
          </q-card-section>
          <q-table
            :rows="transactions"
            :columns="columns"
            row-key="id"
            flat
          >
            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <q-chip :color="props.row.status === 'Completed' ? 'green' : 'orange'" text-color="white" small>
                  {{ props.row.status }}
                </q-chip>
              </q-td>
            </template>
          </q-table>
        </q-card>
      </div>

       <!-- Quick Actions -->
      <div class="col-12 col-md-4">
        <q-card class="q-mb-md">
          <q-card-section>
            <div class="text-h6">Quick Actions</div>
          </q-card-section>
          <q-list separator>
            <q-item clickable v-ripple>
              <q-item-section avatar><q-icon name="add" color="primary" /></q-item-section>
              <q-item-section>Record Offline Payment</q-item-section>
            </q-item>
             <q-item clickable v-ripple>
              <q-item-section avatar><q-icon name="payments" color="green" /></q-item-section>
              <q-item-section>Process Teacher Salary</q-item-section>
            </q-item>
             <q-item clickable v-ripple>
              <q-item-section avatar><q-icon name="receipt" color="grey" /></q-item-section>
              <q-item-section>Generate Monthly Report</q-item-section>
            </q-item>
          </q-list>
        </q-card>

        <!-- Mock Chart Place -->
        <q-card>
           <q-card-section>
             <div class="text-h6">Revenue Trend</div>
             <div class="text-center q-pa-lg text-grey">
               [Chart Component Placeholder]
             </div>
           </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'student', label: 'Student', field: 'student', align: 'left' },
  { name: 'course', label: 'Course', field: 'course', align: 'left' },
  { name: 'amount', label: 'Amount (LKR)', field: 'amount', align: 'right' },
  { name: 'date', label: 'Date', field: 'date', align: 'right' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
]

const transactions = ref([
  { id: '#TRX-001', student: 'Kasun Perera', course: 'Grade 10 Maths', amount: '2,500', date: '2026-01-09', status: 'Completed' },
  { id: '#TRX-002', student: 'Nimali Silva', course: 'Grade 11 Science', amount: '3,000', date: '2026-01-08', status: 'Pending' },
  { id: '#TRX-003', student: 'Amal Perera', course: 'English Spoken', amount: '1,500', date: '2026-01-08', status: 'Completed' },
   { id: '#TRX-004', student: 'Kamal Gunarathne', course: 'Grade 10 History', amount: '2,000', date: '2026-01-07', status: 'Completed' },
])
</script>
