<template>
  <q-page class="q-pa-md">
    <div class="row q-col-gutter-md">
       <!-- Sessions List (Left) -->
       <div class="col-12 col-md-4">
           <q-card class="my-card full-height column">
               <q-card-section class="bg-primary text-white q-pb-sm">
                   <div class="text-h6">Class Sessions</div>
                   <div class="text-caption">Select a session to mark attendance</div>
               </q-card-section>

               <q-card-section class="bg-primary q-pt-none q-pb-md">
                   <div class="row q-col-gutter-sm">
                       <div class="col">
                           <q-input dark dense outlined v-model="search" placeholder="Search Class/Teacher" class="bg-white-translucent rounded-borders">
                               <template v-slot:append><q-icon name="search" class="text-white" /></template>
                           </q-input>
                       </div>
                       <div class="col-auto">
                           <q-btn flat round dense icon="event" class="text-white" title="Filter by Date">
                               <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                   <q-date v-model="dateFilter" range>
                                       <div class="row items-center justify-end">
                                           <q-btn v-close-popup label="Close" color="primary" flat />
                                       </div>
                                   </q-date>
                               </q-popup-proxy>
                           </q-btn>
                       </div>
                   </div>
                   <div class="text-caption text-blue-2 q-mt-xs" v-if="dateLabel">
                       Currently viewing: {{ dateLabel }}
                       <q-btn flat dense size="sm" icon="close" color="white" @click="dateFilter = null" />
                   </div>
               </q-card-section>

               <q-card-section class="q-pa-none col scroll relative-position">
                   <div v-if="loadingSessions" class="row justify-center q-pa-md">
                       <q-spinner color="primary" />
                   </div>
                   <q-list v-else separator>
                       <template v-for="(group, date) in groupedSessions" :key="date">
                           <q-item-label header class="bg-grey-3 text-grey-8 text-weight-bold sticky-top q-px-md q-py-sm shadow-1" style="z-index:10">{{ formatDateHeader(date) }}</q-item-label>
                           <q-item
                              v-for="(sess, idx) in group" :key="sess.id + '_' + idx"
                              clickable
                              v-ripple
                              :active="selectedSession === sess"
                              active-class="bg-blue-1 text-primary"
                              @click="selectSession(sess)"
                           >
                               <q-item-section>
                                   <q-item-label class="text-weight-bold">{{ sess.course_name }}</q-item-label>
                                   <q-item-label caption>{{ sess.start }} - {{ sess.end }}</q-item-label>
                                   <q-item-label caption class="text-grey-8">{{ sess.teacher_name }}</q-item-label>
                               </q-item-section>
                               <q-item-section side>
                                   <q-chip size="sm" :color="getStatusColor(sess.marked_status)" text-color="white">
                                       {{ sess.marked_status }}
                                   </q-chip>
                               </q-item-section>
                           </q-item>
                       </template>
                   </q-list>
                   <div v-if="!loadingSessions && Object.keys(groupedSessions).length === 0" class="text-center q-pa-xl text-grey column flex-center">
                       <q-icon name="event_busy" size="4em" class="q-mb-md" />
                       <div>No sessions found for selected criteria.</div>
                   </div>
               </q-card-section>
           </q-card>
       </div>

       <!-- Marking Area (Right) -->
       <div class="col-12 col-md-8">
           <q-card v-if="selectedSession" class="my-card">
               <q-card-section class="row items-center justify-between">
                   <div>
                       <div class="text-h6">{{ selectedSession.course_name }}</div>
                       <div class="text-subtitle2 text-grey-7">{{ selectedSession.date }} ({{ selectedSession.start }})</div>
                   </div>
                   <div class="row items-center q-gutter-x-md">
                        <div class="text-caption text-weight-bold">
                            <span class="text-green q-mr-sm">Present: {{ counts.present }}</span>
                            <span class="text-red">Absent: {{ counts.absent }}</span>
                        </div>
                       <div class="row q-gutter-sm">
                           <q-btn flat dense color="primary" label="Mark All Present" icon="done_all" @click="markAllPresent" />
                           <q-btn unelevated color="primary" label="Save" icon="save" :loading="saving" @click="saveAttendance" />
                       </div>
                   </div>
               </q-card-section>

               <q-card-section class="q-py-sm bg-grey-1">
                    <div class="row q-col-gutter-sm items-center">
                       <div class="col-12 col-md-5">
                           <q-input
                               outlined
                               dense
                               v-model="quickSearch"
                               placeholder="Scan/Type Student ID & Enter"
                               @keyup.enter="onQuickMark"
                               ref="barcodeInput"
                               autofocus
                               bg-color="white"
                           >
                               <template v-slot:prepend>
                                   <q-icon name="qr_code_scanner" />
                               </template>
                               <template v-slot:append>
                                   <q-icon v-if="quickSearch" name="close" @click="quickSearch = ''" class="cursor-pointer" />
                                </template>
                           </q-input>
                       </div>
                       <div class="col-12 col-md-7 row justify-end q-gutter-x-sm">
                            <q-btn
                              outline
                              color="purple-8"
                              label="Record Payment"
                              icon="payment"
                              @click="openCashPaymentDialog"
                            />
                            <q-file
                               v-model="importFile"
                               label="Import CSV"
                               dense
                               outlined
                               accept=".csv"
                               style="max-width: 150px"
                               bg-color="white"
                               @update:model-value="handleImport"
                            >
                               <template v-slot:prepend><q-icon name="upload_file" /></template>
                            </q-file>
                            <q-btn outline color="green-7" label="Export CSV" icon="download" @click="exportCSV" />
                       </div>
                    </div>
               </q-card-section>

               <q-separator />
               <q-card-section class="q-pa-none">
                   <div v-if="loadingStudents" class="row justify-center q-pa-xl">
                       <q-spinner color="primary" size="3em" />
                   </div>
                   <q-table
                      v-else
                      flat
                      :rows="students"
                      :columns="columns"
                      row-key="id"
                      :pagination="tablePagination"
                      :rows-per-page-options="[100, 200, 500, 1000, 0]"
                      hide-bottom
                   >
                      <template v-slot:body="props">
                          <q-tr :props="props">
                              <q-td key="name" :props="props">
                                  <div class="row items-center">
                                      <q-avatar size="sm" class="q-mr-sm" color="grey-3" text-color="primary">{{ props.row.name.charAt(0) }}</q-avatar>
                                      {{ props.row.name }}
                                  </div>
                              </q-td>
                              <q-td key="contact" :props="props">{{ props.row.phone || props.row.email }}</q-td>
                              <q-td key="status" :props="props">
                                  <q-btn-toggle
                                      v-model="props.row.attendance_status"
                                      flat
                                      dense
                                      :options="[
                                        {value: 'present', slot: 'present'},
                                        {value: 'absent', slot: 'absent'}
                                      ]"
                                  >
                                    <template v-slot:present>
                                        <q-btn round dense flat :color="props.row.attendance_status === 'present' ? 'green' : 'grey-4'" icon="check_circle" @click="props.row.attendance_status = 'present'" />
                                    </template>
                                    <template v-slot:absent>
                                        <q-btn round dense flat :color="props.row.attendance_status === 'absent' ? 'red' : 'grey-4'" icon="cancel" @click="props.row.attendance_status = 'absent'" />
                                    </template>
                                  </q-btn-toggle>
                              </q-td>
                              <q-td key="note" :props="props">
                                  <q-input dense borderless v-model="props.row.attendance_note" placeholder="Note" />
                              </q-td>
                          </q-tr>
                      </template>
                   </q-table>
               </q-card-section>
           </q-card>

           <div v-else class="flex flex-center bg-grey-2 rounded-borders text-grey-7" style="height: 400px; border: 2px dashed #ccc;">
               <div class="text-center">
                   <q-icon name="touch_app" size="4em" />
                   <div class="text-h6 q-mt-md">Select a session to begin marking</div>
               </div>
           </div>
       </div>
    </div>

    <!-- Cash Payment Dialog -->
    <q-dialog v-model="showCashDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="row items-center bg-purple-1 q-pa-md">
          <div class="text-h6 text-purple">Record Cash Payment</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section class="q-gutter-md">
          <!-- Barcode Scanner -->
          <div>
            <q-input
              v-model="paymentForm.barcodeSearch"
              label="Scan Student Barcode/ID"
              outlined
              dense
              placeholder="Scan or type student ID and press Enter"
              @keyup.enter="searchStudent"
              autofocus
            >
              <template v-slot:prepend>
                <q-icon name="qr_code_scanner" />
              </template>
              <template v-slot:append>
                <q-icon v-if="paymentForm.barcodeSearch" name="close" @click="paymentForm.barcodeSearch = ''" class="cursor-pointer" />
              </template>
            </q-input>
          </div>

          <!-- Selected Student Info -->
          <q-card v-if="paymentForm.selectedStudent" class="bg-blue-1 q-pa-md">
            <div class="row items-center justify-between">
              <div>
                <div class="text-h6">{{ paymentForm.selectedStudent.name }}</div>
                <div class="text-caption text-grey-7">ID: {{ paymentForm.selectedStudent.username }}</div>
              </div>
              <q-btn flat round dense icon="close" @click="paymentForm.selectedStudent = null" />
            </div>
          </q-card>

          <!-- Enrolled Classes -->
          <div v-if="paymentForm.selectedStudent">
            <q-label>Select Class to Pay For</q-label>
            <q-list bordered separator>
              <q-item v-for="cls in enrolledClasses" :key="cls.id" clickable v-ripple @click="selectClassForPayment(cls)">
                <q-item-section>
                  <q-item-label><strong>{{ cls.course_name }}</strong></q-item-label>
                  <q-item-label caption>{{ cls.month_label }}</q-item-label>
                  <q-item-label caption>Fee: LKR {{ cls.amount }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-radio v-model="paymentForm.selectedClass" :val="cls" />
                </q-item-section>
              </q-item>
              <div v-if="enrolledClasses.length === 0" class="q-pa-md text-center text-grey">
                No pending fees found
              </div>
            </q-list>
          </div>

          <!-- Payment Amount -->
          <div v-if="paymentForm.selectedClass">
            <q-input
              v-model.number="paymentForm.amount"
              label="Payment Amount (LKR)"
              outlined
              type="number"
              :value="paymentForm.selectedClass.amount"
              min="0"
            />
          </div>

          <!-- Payment Note -->
          <div v-if="paymentForm.selectedClass">
            <q-input
              v-model="paymentForm.note"
              label="Notes (Optional)"
              outlined
              type="textarea"
              rows="3"
              placeholder="Any additional notes..."
            />
          </div>

          <!-- Error Display -->
          <q-banner v-if="paymentForm.error" class="bg-red-1 text-red-9 q-mt-md">
            {{ paymentForm.error }}
          </q-banner>

          <!-- Loading Indicator -->
          <div v-if="loadingStudentData" class="row justify-center q-pa-md">
            <q-spinner color="purple" />
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn
            label="Record Payment"
            color="purple"
            :disable="!paymentForm.selectedClass || !paymentForm.amount"
            :loading="processingPayment"
            @click="submitCashPayment"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { api } from 'boot/axios'
import { useQuasar, date, exportFile } from 'quasar'

const $q = useQuasar()
const sessions = ref([])
const selectedSession = ref(null)
const students = ref([])
const loadingSessions = ref(false)
const loadingStudents = ref(false)
const saving = ref(false)

const search = ref('')
const dateFilter = ref(null)
const tablePagination = ref({ rowsPerPage: 100 })

// Cash Payment Dialog
const showCashDialog = ref(false)
const loadingStudentData = ref(false)
const processingPayment = ref(false)
const enrolledClasses = ref([])

const paymentForm = ref({
  barcodeSearch: '',
  selectedStudent: null,
  selectedClass: null,
  amount: 0,
  note: '',
  error: ''
})

const columns = [
    { name: 'name', label: 'Student Name', align: 'left', field: 'name', sortable: true },
    { name: 'contact', label: 'Contact', align: 'left', field: 'phone' },
    { name: 'status', label: 'Status', align: 'center', field: 'attendance_status' },
    { name: 'note', label: 'Note', align: 'left', field: 'attendance_note' }
]

const filteredSessions = computed(() => {
    const list = sessions.value || []
    if (!search.value) return list

    const s = search.value.toLowerCase()
    return list.filter(sess =>
        (sess.course_name && sess.course_name.toLowerCase().includes(s)) ||
        (sess.teacher_name && sess.teacher_name.toLowerCase().includes(s))
    )
})

const groupedSessions = computed(() => {
    const groups = {}
    const list = filteredSessions.value
    if (!list || list.length === 0) return {}

    list.forEach(sess => {
        // Ensure date exists
        const d = sess.date || 'Unknown'
        if (!groups[d]) groups[d] = []
        groups[d].push(sess)
    })

    // Sort dates descending
    return Object.keys(groups).sort().reverse().reduce((acc, key) => {
        acc[key] = groups[key]
        return acc
    }, {})
})

const dateLabel = computed(() => {
    if (!dateFilter.value) return null
    if (typeof dateFilter.value === 'string') return dateFilter.value
    return `${dateFilter.value.from} - ${dateFilter.value.to}`
})

function formatDateHeader(dateStr) {
    if (dateStr === 'Unknown') return 'Unknown Date'
    const d = new Date(dateStr)

    // Normalize to start of day for accurate comp
    d.setHours(0,0,0,0)
    const today = new Date(); today.setHours(0,0,0,0)
    const tomorrow = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1)
    const yesterday = new Date(today); yesterday.setDate(yesterday.getDate() - 1)

    if (d.getTime() === today.getTime()) return `Today (${dateStr})`
    if (d.getTime() === tomorrow.getTime()) return `Tomorrow (${dateStr})`
    if (d.getTime() === yesterday.getTime()) return `Yesterday (${dateStr})`

    return date.formatDate(d, 'dddd, MMMM Do YYYY')
}

const counts = computed(() => {
    const present = students.value.filter(s => s.attendance_status === 'present').length
    const absent = students.value.length - present
    return { present, absent }
})

onMounted(() => {
    fetchSessions()
})

watch(dateFilter, () => {
    fetchSessions()
})

async function fetchSessions() {
    loadingSessions.value = true
    try {
        const params = {}
        if (dateFilter.value) {
            if (typeof dateFilter.value === 'string') {
                 params.from = dateFilter.value
                 params.to = dateFilter.value
            } else {
                 params.from = dateFilter.value.from
                 params.to = dateFilter.value.to
            }
        }
        const res = await api.get('/v1/admin/attendance/dashboard', { params })
        sessions.value = res.data.sessions
    } catch (e) {
        console.error('Error fetching sessions', e)
        $q.notify({ type: 'negative', message: 'Failed to refresh sessions' })
    } finally {
        loadingSessions.value = false
    }
}

async function selectSession(sess) {
    selectedSession.value = sess
    loadingStudents.value = true
    try {
        const res = await api.get('/v1/attendance/students', {
            params: { course_id: sess.course_id, date: sess.date }
        })
        students.value = res.data.data.map(s => ({
            ...s,
            attendance_status: s.attendance_status || 'absent', // Default to absent if not marked
            attendance_note: s.attendance_note || ''
        }))
    } catch (e) {
        console.error('Error fetching students', e)
        students.value = []
    } finally {
        loadingStudents.value = false
    }
}

function markAllPresent() {
    students.value.forEach(s => s.attendance_status = 'present')
}

async function saveAttendance() {
    if (!selectedSession.value) return
    saving.value = true
    try {
        const payload = {
            course_id: selectedSession.value.course_id,
            date: selectedSession.value.date,
            attendances: students.value.map(s => ({
                student_id: s.id,
                status: s.attendance_status,
                note: s.attendance_note
            }))
        }
        await api.post('/v1/attendance/bulk', payload)

        $q.notify({
            color: 'green-4',
            textColor: 'white',
            icon: 'cloud_done',
            message: 'Attendance Saved Successfully'
        })

        // Refresh sessions to update status
        fetchSessions()

    } catch (e) {
        console.error('Error saving attendance', e)
        $q.notify({
            color: 'red-5',
            textColor: 'white',
            icon: 'warning',
            message: 'Failed to save attendance'
        })
    } finally {
        saving.value = false
    }
}

function getStatusColor(status) {
    if (status === 'completed') return 'green'
    if (status === 'partial') return 'blue'
    return 'orange'
}

const quickSearch = ref('')
const importFile = ref(null)
const barcodeInput = ref(null)

function onQuickMark() {
    if (!quickSearch.value) return
    const query = quickSearch.value.trim().toLowerCase()

    // Find student by exact username match (ID) first, then loose name match
    const student = students.value.find(s =>
        (s.username && s.username.toLowerCase() === query) ||
        (s.name && s.name.toLowerCase() === query)
    )

    if (student) {
        student.attendance_status = 'present'
        $q.notify({ type: 'positive', message: `Marked Present: ${student.name}`, position: 'top', timeout: 500 })
        quickSearch.value = ''
    } else {
        $q.notify({ type: 'warning', message: 'Student ID not found', position: 'top', timeout: 1000 })
        quickSearch.value = ''
    }
}

function exportCSV() {
    if (!selectedSession.value || students.value.length === 0) return

    const headers = ['Student ID', 'Name', 'Phone', 'Status', 'Note']
    const rows = students.value.map(s => [
        s.username || '',
        s.name,
        s.phone || s.email || '',
        s.attendance_status,
        s.attendance_note || ''
    ])

    const content = [
        headers.join(','),
        ...rows.map(row => row.map(cell => `"${String(cell).replace(/"/g, '""')}"`).join(','))
    ].join('\r\n')

    const fileName = `attendance_${selectedSession.value.course_name}_${selectedSession.value.date}.csv`

    const status = exportFile(fileName, content, 'text/csv')

    if (!status) {
        $q.notify({ type: 'negative', message: 'Browser denied file download' })
    }
}

function handleImport(file) {
    if (!file) return

    const reader = new FileReader()
    reader.onload = (e) => {
        const text = e.target.result
        const lines = text.split(/\r\n|\n/)
        // Remove header if it looks like a header row
        if (lines.length > 0 && (lines[0].toLowerCase().includes('student') || lines[0].toLowerCase().includes('name') || lines[0].toLowerCase() === 'id')) {
            lines.shift()
        }

        let markedCount = 0

        lines.forEach(line => {
            if (!line.trim()) return

            // Flexible split: handle commas (CSV) or just raw lines
            const cols = line.split(',').map(c => c.replace(/^"|"$/g, '').trim())

            // Allow even a single column (Student ID only)
            if (cols.length === 0 || !cols[0]) return

            const id = cols[0].toLowerCase()

            // Optional columns
            const name = cols[1] ? cols[1].toLowerCase() : ''
            const statusRaw = cols.length > 2 ? (cols[3] || cols[2] || 'present').toLowerCase() : 'present' // Try to find status, default to present

            // Search primarily by ID (Username)
            let student = students.value.find(s => s.username && s.username.toLowerCase() === id)

            // Fallback: If not found by ID, try Name IF provided
            if (!student && name) {
                 student = students.value.find(s => s.name && s.name.toLowerCase() === name)
            }

            if (student) {
                // If the CSV explicitly says "absent", mark absent. Otherwise default to "present" (especially for ID-only lists)
                if (statusRaw.includes('absent') || statusRaw === '0' || statusRaw === 'false') {
                     student.attendance_status = 'absent'
                } else {
                     student.attendance_status = 'present'
                }
                markedCount++
            }
        })

        $q.notify({ type: 'positive', message: `Imported attendance for ${markedCount} students` })
        importFile.value = null
    }

    reader.readAsText(file) // Assumes CSV
}

// ========== CASH PAYMENT FUNCTIONS ==========

function openCashPaymentDialog() {
  paymentForm.value = {
    barcodeSearch: '',
    selectedStudent: null,
    selectedClass: null,
    amount: 0,
    note: '',
    error: ''
  }
  enrolledClasses.value = []
  showCashDialog.value = true
}

async function searchStudent() {
  if (!paymentForm.value.barcodeSearch.trim()) {
    paymentForm.value.error = 'Please enter a student ID'
    return
  }

  paymentForm.value.error = ''
  loadingStudentData.value = true

  try {
    // Search student by username/ID or barcode
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
      paymentForm.value.error = 'Student not found'
      return
    }

    paymentForm.value.selectedStudent = foundStudent
    await fetchStudentClasses(foundStudent.id)
    paymentForm.value.amount = 0
    paymentForm.value.selectedClass = null

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
    enrolledClasses.value = res.data || []

    if (enrolledClasses.value.length === 0) {
      paymentForm.value.error = 'No pending fees found for this student'
    }
  } catch (error) {
    console.error('Failed to fetch classes:', error)
    paymentForm.value.error = 'Failed to load student classes'
    enrolledClasses.value = []
  }
}

function selectClassForPayment(cls) {
  paymentForm.value.selectedClass = cls
  paymentForm.value.amount = cls.amount || 0
}

async function submitCashPayment() {
  if (!paymentForm.value.selectedStudent || !paymentForm.value.selectedClass || !paymentForm.value.amount) {
    $q.notify({ type: 'warning', message: 'Please fill all required fields' })
    return
  }

  processingPayment.value = true
  paymentForm.value.error = ''

  try {
    const payload = {
      student_id: paymentForm.value.selectedStudent.id,
      amount: paymentForm.value.amount,
      note: paymentForm.value.note || 'Cash Payment - Attendance',
      fee_ids: [paymentForm.value.selectedClass.id]
    }

    const res = await api.post('/v1/admin/payments/record-cash', payload)

    if (res.data && res.data.message) {
      $q.notify({
        type: 'positive',
        message: 'Payment recorded successfully!',
        timeout: 2000
      })

      // Print receipt if available
      if (res.data.payment) {
        printPaymentReceipt(res.data.payment, paymentForm.value.selectedStudent)
      }

      showCashDialog.value = false
      // Reset
      paymentForm.value = {
        barcodeSearch: '',
        selectedStudent: null,
        selectedClass: null,
        amount: 0,
        note: '',
        error: ''
      }
      enrolledClasses.value = []
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
