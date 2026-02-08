<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <q-icon name="point_of_sale" size="md" color="purple" class="q-mr-sm" />
      <div>
        <div class="text-h5 text-weight-bold">Cash Payment</div>
        <div class="text-caption text-grey">Record cash payments by scanning student barcode or ID</div>
      </div>
    </div>

    <div class="row q-col-gutter-lg">
      <!-- Left Side - Scanner & Student Lookup -->
      <div class="col-12 col-md-5">
        <q-card class="full-height">
          <q-card-section class="bg-purple text-white">
            <div class="text-h6">
              <q-icon name="qr_code_scanner" class="q-mr-sm" />
              Student Lookup
            </div>
          </q-card-section>

          <q-card-section class="q-gutter-md">
            <!-- Barcode Scanner Input -->
            <q-input
              ref="barcodeInput"
              v-model="paymentForm.barcodeSearch"
              label="Scan Barcode / Enter Student ID"
              outlined
              placeholder="Scan or type student ID and press Enter"
              @keyup.enter="searchStudent"
              autofocus
            >
              <template v-slot:prepend>
                <q-icon name="qr_code_scanner" color="purple" />
              </template>
              <template v-slot:append>
                <q-btn v-if="paymentForm.barcodeSearch" icon="close" flat round dense @click="paymentForm.barcodeSearch = ''" />
                <q-btn icon="search" flat round dense color="purple" @click="searchStudent" />
              </template>
            </q-input>

            <!-- Loading -->
            <div v-if="loadingStudentData" class="row justify-center q-pa-lg">
              <q-spinner-dots color="purple" size="40px" />
              <div class="q-ml-sm text-grey">Searching student...</div>
            </div>

            <!-- Student Card -->
            <q-card v-if="paymentForm.selectedStudent" flat bordered class="bg-blue-1">
              <q-card-section>
                <div class="row items-center q-gutter-md">
                  <q-avatar size="50px" color="purple" text-color="white" icon="person" />
                  <div class="col">
                    <div class="text-h6">{{ paymentForm.selectedStudent.name }}</div>
                    <div class="text-caption text-grey-7">
                      <q-icon name="badge" size="xs" /> ID: {{ paymentForm.selectedStudent.username }}
                    </div>
                    <div v-if="paymentForm.selectedStudent.phone" class="text-caption text-grey-7">
                      <q-icon name="phone" size="xs" /> {{ paymentForm.selectedStudent.phone }}
                    </div>
                  </div>
                  <q-btn flat round icon="close" color="grey" @click="clearStudent" />
                </div>
              </q-card-section>
            </q-card>

            <!-- No Student Placeholder -->
            <div v-if="!paymentForm.selectedStudent && !loadingStudentData" class="text-center q-pa-xl text-grey-5">
              <q-icon name="person_search" size="4em" />
              <div class="q-mt-sm text-body1">Scan barcode or enter student ID</div>
            </div>

            <!-- Error -->
            <q-banner v-if="paymentForm.error" class="bg-red-1 text-red-9 rounded-borders">
              <template v-slot:avatar>
                <q-icon name="error" color="red" />
              </template>
              {{ paymentForm.error }}
            </q-banner>
          </q-card-section>
        </q-card>
      </div>

      <!-- Right Side - Pending Fees & Payment -->
      <div class="col-12 col-md-7">
        <!-- Pending Fees List -->
        <q-card v-if="paymentForm.selectedStudent">
          <q-card-section class="bg-orange-1">
            <div class="row items-center justify-between">
              <div class="text-h6 text-orange-9">
                <q-icon name="receipt_long" class="q-mr-sm" />
                Pending Fees - Select to Pay
              </div>
              <q-badge color="orange" :label="`${enrolledClasses.length} pending`" />
            </div>
          </q-card-section>

          <q-separator />

          <div v-if="enrolledClasses.length === 0" class="q-pa-xl text-center text-grey">
            <q-icon name="check_circle" size="3em" color="green" />
            <div class="q-mt-sm text-body1">No pending fees for this student</div>
          </div>

          <q-list v-else separator>
            <q-item
              v-for="cls in enrolledClasses"
              :key="cls.id"
              clickable
              v-ripple
              @click="toggleFeeSelection(cls)"
            >
              <q-item-section avatar>
                <q-checkbox
                  v-model="cls.selected"
                  color="purple"
                  @update:model-value="updateTotalAmount"
                />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">{{ cls.course_name }}</q-item-label>
                <q-item-label caption>
                  <q-icon name="calendar_month" size="xs" /> {{ cls.month_label }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-item-label class="text-weight-bold text-purple">LKR {{ Number(cls.amount).toLocaleString() }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>

          <q-separator />

          <!-- Total Amount Summary -->
          <q-card-section v-if="selectedFeesCount > 0" class="bg-green-1">
            <div class="row items-center justify-between">
              <div>
                <div class="text-caption text-grey-8">Total Amount</div>
                <div class="text-body2">{{ selectedFeesCount }} fee{{ selectedFeesCount > 1 ? 's' : '' }} selected</div>
              </div>
              <div class="text-h5 text-green-9 text-weight-bold">
                LKR {{ totalAmount.toLocaleString() }}
              </div>
            </div>
          </q-card-section>

          <q-card-actions class="q-pa-md" v-if="selectedFeesCount > 0">
            <q-btn
              flat
              label="Clear Selection"
              color="grey"
              icon="clear_all"
              @click="clearSelection"
            />
            <q-space />
            <q-btn
              label="Record Payment"
              color="purple"
              icon="payment"
              size="lg"
              :loading="processingPayment"
              @click="submitCashPayment"
            />
          </q-card-actions>
        </q-card>

        <!-- Empty State when no student -->
        <q-card v-if="!paymentForm.selectedStudent" class="full-height">
          <q-card-section class="flex flex-center" style="min-height: 400px">
            <div class="text-center text-grey-5">
              <q-icon name="point_of_sale" size="6em" />
              <div class="text-h6 q-mt-md">Ready to Record Payment</div>
              <div class="text-body2 q-mt-sm">Scan a student barcode or enter their ID to begin</div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Recent Payments -->
        <q-card v-if="recentPayments.length > 0" class="q-mt-md">
          <q-card-section class="bg-grey-2">
            <div class="text-h6 text-grey-8">
              <q-icon name="history" class="q-mr-sm" />
              Recent Payments (This Session)
            </div>
          </q-card-section>
          <q-separator />
          <q-list separator>
            <q-item v-for="(p, idx) in recentPayments" :key="idx">
              <q-item-section avatar>
                <q-icon name="check_circle" color="green" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ p.studentName }}</q-item-label>
                <q-item-label caption>{{ p.courseName }} - {{ p.time }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-item-label class="text-weight-bold text-green">LKR {{ Number(p.amount).toLocaleString() }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, nextTick, computed } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const barcodeInput = ref(null)
const loadingStudentData = ref(false)
const processingPayment = ref(false)
const enrolledClasses = ref([])
const recentPayments = ref([])

const paymentForm = ref({
  barcodeSearch: '',
  selectedStudent: null,
  error: ''
})

const totalAmount = computed(() => {
  return enrolledClasses.value
    .filter(cls => cls.selected)
    .reduce((sum, cls) => sum + Number(cls.amount), 0)
})

const selectedFeesCount = computed(() => {
  return enrolledClasses.value.filter(cls => cls.selected).length
})

function toggleFeeSelection(cls) {
  cls.selected = !cls.selected
}

function updateTotalAmount() {
  // Trigger reactivity
}

function clearSelection() {
  enrolledClasses.value.forEach(cls => {
    cls.selected = false
  })
}

async function searchStudent() {
  if (!paymentForm.value.barcodeSearch.trim()) {
    paymentForm.value.error = 'Please enter a student ID'
    return
  }

  paymentForm.value.error = ''
  paymentForm.value.selectedStudent = null
  enrolledClasses.value = []
  loadingStudentData.value = true

  try {
    const searchQuery = paymentForm.value.barcodeSearch.trim()
    const res = await api.get('/v1/users', {
      params: {
        search: searchQuery,
        role: 'student',
        per_page: 5
      }
    })

    const foundStudent = res.data.data?.[0]
    if (!foundStudent) {
      paymentForm.value.error = 'Student not found. Please check the ID and try again.'
      return
    }

    paymentForm.value.selectedStudent = foundStudent
    await fetchStudentClasses(foundStudent.id)

  } catch (error) {
    console.error('Search error:', error)
    paymentForm.value.error = error.response?.data?.message || 'Failed to search student'
  } finally {
    loadingStudentData.value = false
  }
}

async function fetchStudentClasses(studentId) {
  try {
    const res = await api.get(`/v1/admin/students/${studentId}/pending-fees`)
    enrolledClasses.value = (res.data || []).map(fee => ({
      ...fee,
      selected: true // Auto-select all by default
    }))

    if (enrolledClasses.value.length === 0) {
      paymentForm.value.error = 'No pending fees found for this student'
    }
  } catch (error) {
    console.error('Failed to fetch classes:', error)
    paymentForm.value.error = 'Failed to load student classes'
    enrolledClasses.value = []
  }
}

function clearStudent() {
  paymentForm.value.selectedStudent = null
  paymentForm.value.error = ''
  enrolledClasses.value = []
  nextTick(() => barcodeInput.value?.focus())
}

function clearForm() {
  paymentForm.value = {
    barcodeSearch: '',
    selectedStudent: null,
    error: ''
  }
  enrolledClasses.value = []
  nextTick(() => barcodeInput.value?.focus())
}

async function submitCashPayment() {
  const selectedFees = enrolledClasses.value.filter(cls => cls.selected)

  if (!paymentForm.value.selectedStudent || selectedFees.length === 0) {
    $q.notify({ type: 'warning', message: 'Please select at least one fee to pay' })
    return
  }

  processingPayment.value = true
  paymentForm.value.error = ''

  try {
    const payload = {
      student_id: paymentForm.value.selectedStudent.id,
      amount: totalAmount.value,
      note: `Cash Payment for ${selectedFees.length} fee(s)`,
      fee_ids: selectedFees.map(fee => fee.id)
    }

    const res = await api.post('/v1/admin/payments/record-cash', payload)

    if (res.data && res.data.message) {
      $q.notify({
        type: 'positive',
        message: `Payment of LKR ${totalAmount.value.toLocaleString()} recorded for ${paymentForm.value.selectedStudent.name}`,
        timeout: 3000
      })

      // Track recent payment
      recentPayments.value.unshift({
        studentName: paymentForm.value.selectedStudent.name,
        courseName: selectedFees.map(f => f.course_name).join(', '),
        amount: totalAmount.value,
        time: new Date().toLocaleTimeString()
      })

      // Print receipt
      if (res.data.payment) {
        printPaymentReceipt(res.data.payment, paymentForm.value.selectedStudent)
      }

      // Reset form for next payment
      clearForm()
    }

  } catch (error) {
    console.error('Payment error:', error)
    paymentForm.value.error = error.response?.data?.message || error.response?.data?.error || 'Failed to record payment'
    $q.notify({
      type: 'negative',
      message: paymentForm.value.error
    })
  } finally {
    processingPayment.value = false
  }
}

function printPaymentReceipt(payment, student) {
  try {
    const printWindow = window.open('', '_blank', 'width=800,height=600')
    if (!printWindow) {
      $q.notify({ type: 'warning', message: 'Please enable popups to print receipt' })
      return
    }

    const receiptHtml = `
      <html>
        <head>
          <title>Payment Receipt</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 40px; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
            .title { font-size: 24px; font-weight: bold; }
            table { width: 100%; margin-bottom: 30px; }
            td { padding: 10px; border-bottom: 1px solid #eee; }
            .label { font-weight: bold; width: 150px; }
            .total { font-size: 18px; font-weight: bold; text-align: right; }
            .footer { margin-top: 50px; text-align: center; color: #999; font-size: 12px; }
          </style>
        </head>
        <body>
          <div class="header">
            <div class="title">PAYMENT RECEIPT</div>
            <div>#${payment.id}</div>
          </div>

          <table>
            <tr><td class="label">Student Name:</td><td>${student.name}</td></tr>
            <tr><td class="label">Student ID:</td><td>${student.username}</td></tr>
            <tr><td class="label">Amount Paid:</td><td>LKR ${payment.amount?.toLocaleString() || 0}</td></tr>
            <tr><td class="label">Payment Type:</td><td>${payment.type || 'Cash'}</td></tr>
            <tr><td class="label">Date:</td><td>${new Date().toLocaleString()}</td></tr>
          </table>

          <div class="total">Total: LKR ${payment.amount?.toLocaleString() || 0}</div>

          <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This is a computer-generated receipt.</p>
          </div>
        </body>
      </html>
    `

    printWindow.document.write(receiptHtml)
    printWindow.document.close()
    setTimeout(() => printWindow.print(), 250)
  } catch (error) {
    console.error('Print error:', error)
  }
}
</script>
