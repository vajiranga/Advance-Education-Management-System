<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">
          Payments
        </div>
        <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">
          Manage your class fees and view transaction history
        </div>
      </div>
    </div>

    <div class="row items-center q-mb-sm">
      <q-checkbox
        v-model="selectAll"
        label="Select All"
        dense
        size="sm"
        @update:model-value="toggleSelectAll"
        v-if="tab === 'pending' && pending.length > 0"
      />
      <q-tabs
        v-model="tab"
        dense
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
        class="col"
        :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'"
      >
        <q-tab name="pending" label="Pending Fees" icon="pending_actions" />
        <q-tab name="history" label="Payment History" icon="receipt_long" />
      </q-tabs>
    </div>

    <q-tab-panels v-model="tab" animated class="bg-transparent">
      <!-- Pending Fees Tab -->
      <q-tab-panel name="pending" class="q-pa-none">
        <div v-if="pending.length > 0">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6 col-lg-4" v-for="fee in pending" :key="fee.course_id">
              <q-card
                class="payment-card no-shadow q-pa-sm"
                :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'"
              >
                <q-card-section>
                  <div class="row justify-between items-start">
                    <div class="row items-start">
                      <q-checkbox
                        v-model="selectedFeeIds"
                        :val="fee.id"
                        dense
                        size="sm"
                        class="q-mr-sm"
                      />
                      <div>
                        <div
                          class="text-subtitle1 text-weight-bold"
                          :class="$q.dark.isActive ? 'text-white' : ''"
                        >
                          {{ fee.course_name }}
                        </div>
                        <div
                          class="text-caption"
                          :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'"
                        >
                          {{ fee.month }}
                        </div>
                      </div>
                    </div>
                    <q-chip
                      :color="$q.dark.isActive ? 'red-9' : 'red-1'"
                      :text-color="$q.dark.isActive ? 'red-2' : 'red'"
                      label="Due"
                      size="sm"
                    />
                  </div>

                  <div class="text-h5 text-primary text-weight-bold q-mt-md">
                    LKR {{ fee.amount }}
                  </div>
                  <div v-if="fee.late_fee > 0" class="text-caption text-negative q-mt-xs">
                    <q-icon name="warning" size="xs" />
                    Includes Late Fee: LKR {{ fee.late_fee }}
                  </div>
                  <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey'">
                    Due: Immediately
                  </div>
                </q-card-section>

                <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

                <q-card-actions align="right">
                  <q-btn
                    unelevated
                    color="primary"
                    label="Pay Now"
                    icon="payment"
                    @click="openPayDialog(fee)"
                  />
                </q-card-actions>
              </q-card>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-grey q-pa-xl">
          <q-icon
            name="check_circle"
            size="64px"
            :color="$q.dark.isActive ? 'green-6' : 'green-4'"
          />
          <div class="text-h6 q-mt-md" :class="$q.dark.isActive ? 'text-grey-4' : ''">
            All fees paid! Great job.
          </div>
        </div>
      </q-tab-panel>

      <!-- History Tab -->
      <q-tab-panel name="history" class="q-pa-none">
        <q-card
          class="no-shadow"
          :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'"
        >
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
                  :color="getStatusColor(props.row.status)"
                  :text-color="getStatusTextColor(props.row.status)"
                  size="sm"
                  :icon="getStatusIcon(props.row.status)"
                >
                  {{ props.row.status }}
                </q-chip>
              </q-td>
            </template>
            <template v-slot:body-cell-amount="props">
              <q-td
                :props="props"
                class="text-weight-bold"
                :class="$q.dark.isActive ? 'text-white' : ''"
              >
                LKR {{ props.row.amount }}
              </q-td>
            </template>
            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  v-if="props.row.status === 'paid'"
                  round
                  flat
                  dense
                  icon="print"
                  color="grey"
                  @click="printReceipt(props.row)"
                  title="Download/Print Receipt"
                />
              </q-td>
            </template>
          </q-table>
        </q-card>
      </q-tab-panel>
    </q-tab-panels>

    <!-- Bulk Pay Sticky Footer -->
    <q-page-sticky
      position="bottom"
      :offset="[0, 18]"
      v-if="selectedCount > 0 && tab === 'pending'"
    >
      <q-card
        class="shadow-10 q-px-lg q-py-sm rounded-borders"
        :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-white'"
      >
        <div class="row items-center q-gutter-x-md">
          <div class="text-subtitle1">
            Selected: <span class="text-weight-bold text-primary">{{ selectedCount }}</span>
          </div>
          <div class="text-h6 text-weight-bold text-primary">LKR {{ totalSelectedAmount }}</div>
          <q-btn
            unelevated
            rounded
            color="primary"
            label="Pay Selected"
            icon="payment"
            @click="openBulkPayDialog"
          />
        </div>
      </q-card>
    </q-page-sticky>

    <!-- Payment Dialog -->
    <q-dialog v-model="showPaymentDialog">
      <q-card style="min-width: 400px" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
        <q-card-section>
          <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">Make Payment</div>
          <div class="text-subtitle2 text-primary" v-if="isBulkPayment">
            Paying for {{ selectedFees.length }} Items
          </div>
          <div class="text-subtitle2 text-primary" v-else>
            {{ selectedFees[0]?.course_name }} - {{ selectedFees[0]?.month }}
          </div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="text-h5 text-center q-mb-md font-weight-bold">
            Total: LKR {{ totalPayAmount }}
          </div>

          <!-- Notice when both payment methods are disabled -->
          <q-banner v-if="!settingsStore.paymentGateway && !settingsStore.enableBankTransfer" class="bg-orange-2 text-orange-9 q-mb-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" color="orange" />
            </template>
            Only cash payments are currently available. Please contact the institute to make a payment.
          </q-banner>

          <div v-else class="text-subtitle2 q-mb-sm" :class="$q.dark.isActive ? 'text-grey-4' : ''">
            Select Payment Method:
          </div>
          <div v-if="settingsStore.paymentGateway || settingsStore.enableBankTransfer" class="q-gutter-sm">
            <q-radio
              v-if="settingsStore.paymentGateway"
              v-model="paymentMethod"
              val="online"
              label="Online Payment (Card/Gateway)"
              :dark="$q.dark.isActive"
            />
            <q-radio
              v-if="settingsStore.enableBankTransfer"
              v-model="paymentMethod"
              val="bank_transfer"
              label="Bank Transfer (Upload Slip)"
              :dark="$q.dark.isActive"
            />
          </div>

          <div v-if="paymentMethod === 'bank_transfer'" class="q-mt-md">
            <q-card flat bordered class="bg-grey-2 q-pa-sm">
                <div class="text-caption text-grey-8 q-mb-sm">
                   To complete your payment:
                   <ol class="q-pl-md q-my-xs">
                     <li>Click the button below to send details via WhatsApp.</li>
                     <li>Attach your payment slip in the WhatsApp chat.</li>
                     <li>Return here and click "Confirm Payment".</li>
                   </ol>
                </div>

                <div class="text-subtitle2 text-weight-bold q-mb-xs">Bank Details:</div>
                <div class="text-caption text-grey-8 q-mb-sm">
                   Bank: Bank of Ceylon (BOC)<br />
                   Account: 123-456-7890<br />
                   Name: {{ settingsStore.instituteName || 'EMS Institute' }}<br />
                   Branch: Colombo
                </div>

                <q-btn
                  type="a"
                  :href="whatsappUrl"
                  target="_blank"
                  color="green-6"
                  icon="assignment"
                  label="Send Slip via WhatsApp"
                  class="full-width q-mb-sm"
                  no-caps
                />
             </q-card>
          </div>

        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn
            flat
            label="Cancel"
            v-close-popup
            :color="$q.dark.isActive ? 'grey-5' : 'grey-7'"
          />
          <q-btn
            v-if="settingsStore.paymentGateway || settingsStore.enableBankTransfer"
            unelevated
            color="primary"
            label="Confirm Payment"
            @click="submitPayment"
            :loading="processing"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import { usePaymentStore } from 'stores/payment-store'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { useAuthStore } from 'stores/auth-store'
import { useSettingsStore } from 'stores/settings-store'

const $q = useQuasar()
const paymentStore = usePaymentStore()
const { pending, history } = storeToRefs(paymentStore)
const route = useRoute()
const authStore = useAuthStore()
const settingsStore = useSettingsStore()

const tab = ref('pending')
const showPaymentDialog = ref(false)
const selectedFees = ref([]) // Array of selected fees for payment dialog
const selectedFeeIds = ref([]) // IDs selected by checkboxes
const selectAll = ref(false)

const paymentMethod = ref('online')
const slipFile = ref(null)
const processing = ref(false)

// Function to determine context student ID
const getContextStudentId = () => {
  if (route.meta.isParentView) {
    // Assuming authStore has a selectedChild or similar state
    // Since ParentLayout manages selected child locally, we might need a shared store state.
    // However, looking at ParentLayout, it uses a local ref.
    // We should move 'selectedChild' to authStore to make it accessible here.
    return authStore.selectedChild?.id
  }
  return null // Defaults to logged in user
}

const fetchData = () => {
  const studentId = getContextStudentId()
  paymentStore.fetchPendingFees(studentId)
  paymentStore.fetchHistory(studentId)
}

onMounted(() => {
  fetchData()
})

if (route.meta.isParentView) {
  watch(
    () => authStore.selectedChild,
    () => {
      fetchData()
    },
  )
}

const columns = [
  {
    name: 'date',
    label: 'Payment Date',
    field: 'created_at',
    format: (val) => new Date(val).toLocaleDateString(),
    align: 'left',
  },
  { name: 'course', label: 'Course', field: (row) => row.course?.name || 'Course', align: 'left' },
  { name: 'month', label: 'Month', field: 'month', align: 'left' },
  { name: 'amount', label: 'Amount', field: 'amount', align: 'right' },
  { name: 'method', label: 'Method', field: 'type', align: 'center' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'actions', label: '', align: 'right' },
]

function getStatusColor(status) {
  if (status === 'paid') return $q.dark.isActive ? 'green-9' : 'green-1'
  if (status === 'pending') return $q.dark.isActive ? 'orange-9' : 'orange-1'
  return 'grey-3'
}

function getStatusTextColor(status) {
  if (status === 'paid') return $q.dark.isActive ? 'green-1' : 'green-8'
  if (status === 'pending') return $q.dark.isActive ? 'orange-1' : 'orange-8'
  return 'grey-8'
}

function getStatusIcon(status) {
  if (status === 'paid') return 'check_circle'
  if (status === 'pending') return 'hourglass_empty'
  return 'info'
}

function printReceipt(payment) {
  const printWindow = window.open('', '_blank', 'width=800,height=600')
  if (!printWindow) return

  const htmlContent =
    `
        <html>
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
                <div class="title">${settingsStore.instituteName || 'EMS Institute'}</div>
                <div class="subtitle">Official Payment Receipt</div>
            </div>

            <table class="details">
                <tr>
                    <td class="label">Receipt No:</td>
                    <td>#${payment.id}</td>
                </tr>
                <tr>
                    <td class="label">Date:</td>
                    <td>${new Date(payment.created_at).toLocaleDateString()} ${new Date(payment.created_at).toLocaleTimeString()}</td>
                </tr>
                 <tr>
                    <td class="label">Student:</td>
                    <td>${payment.student_name || 'Student'}</td> <!-- Assuming FE might need to enrich this if not present -->
                </tr>
                <tr>
                    <td class="label">Course:</td>
                    <td>${payment.course?.name || 'Unknown Course'}</td>
                </tr>
                <tr>
                    <td class="label">Month:</td>
                    <td>${payment.month}</td>
                </tr>
                <tr>
                    <td class="label">Payment Method:</td>
                    <td>${payment.type.toUpperCase()}</td>
                </tr>
                <tr>
                    <td class="label">Status:</td>
                    <td><span class="status">${payment.status.toUpperCase()}</span></td>
                </tr>
            </table>

            <div class="total">
                Total Amount: LKR ${payment.amount}
            </div>

            <div class="footer">
                <p>Thank you for your payment!</p>
                <p>Generated on ${new Date().toLocaleString()}</p>
            </div>

            <script>
                setTimeout(() => { window.print(); window.close(); }, 500);
            ` +
    '<' +
    '/script>' +
    `
        </body>
        </html>
    `

  printWindow.document.write(htmlContent)
  printWindow.document.close()
}

const openPayDialog = (fee) => {
  selectedFees.value = [fee] // Single pay
  paymentMethod.value = 'online'
  slipFile.value = null
  showPaymentDialog.value = true
}

const openBulkPayDialog = () => {
  selectedFees.value = pending.value.filter((f) => selectedFeeIds.value.includes(f.id))
  paymentMethod.value = 'online'
  slipFile.value = null
  showPaymentDialog.value = true
}

const toggleSelectAll = (val) => {
  if (val) {
    selectedFeeIds.value = pending.value.map((f) => f.id)
  } else {
    selectedFeeIds.value = []
  }
}

const selectedCount = computed(() => selectedFeeIds.value.length)
const totalSelectedAmount = computed(() => {
  return pending.value
    .filter((f) => selectedFeeIds.value.includes(f.id))
    .reduce((sum, f) => sum + parseFloat(f.amount), 0)
})

const isBulkPayment = computed(() => selectedFees.value.length > 1)
const totalPayAmount = computed(() =>
  selectedFees.value.reduce((sum, f) => sum + parseFloat(f.amount), 0),
)



const whatsappUrl = computed(() => {
    const student = authStore.user
    const fees = selectedFees.value
    let message = `*Payment Verification Request*\n\n`
    message += `Student ID: ${student?.username || 'N/A'}\n`
    message += `Name: ${student?.name || 'N/A'}\n`
    message += `Total Amount: LKR ${totalPayAmount.value}\n\n`
    message += `*Selected Classes:*\n`
    fees.forEach(f => {
        message += `- ${f.course_name} (${f.month})\n`
    })
    message += `\n*Note to Student:* Please attach your payment slip image to this message.`

    // Use contact from settings or fallback
    const contact = settingsStore.whatsappContact || '94771234567'
    return `https://wa.me/${contact}?text=${encodeURIComponent(message)}`
})

const submitPayment = async () => {
    // No file check needed for WhatsApp flow


  processing.value = true

  // Simulate delay
  setTimeout(async () => {
    let payload

    if (paymentMethod.value === 'bank_transfer') {
      const formData = new FormData()

      // Append multiple fee_ids
      selectedFees.value.forEach((fee, index) => {
        formData.append(`fee_ids[${index}]`, fee.id)
      })

      formData.append('amount', totalPayAmount.value)
      formData.append('type', 'bank_transfer')
      formData.append('note', 'WhatsApp Verification Pending')
      // No slip file sent
      payload = formData
    } else {
      payload = {
        fee_ids: selectedFees.value.map((f) => f.id),
        amount: totalPayAmount.value,
        type: 'online',
        note: 'Online Payment via Student Portal',
      }
    }

    const res = await paymentStore.makePayment(payload)
    processing.value = false
    showPaymentDialog.value = false

    if (res.success) {
      $q.notify({
        type: 'positive',
        message:
          'Payment Submitted! Status: ' +
          (paymentMethod.value === 'bank_transfer' ? 'Pending Approval' : 'Paid'),
      })
    } else {
      $q.notify({ type: 'negative', message: 'Payment Failed: ' + res.error })
    }
  }, 1000)
}
</script>

<style scoped>
.payment-card {
  border-radius: 12px;
  border-left: 4px solid #e53935;
}
.border-light {
  border: 1px solid #eee;
}
</style>
