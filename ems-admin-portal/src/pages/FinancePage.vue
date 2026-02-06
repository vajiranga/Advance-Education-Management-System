<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div>
        <div class="text-h5">Financial Overview</div>
        <div class="text-caption text-grey">Manage revenue and verify payments</div>
      </div>
      <div class="row q-gutter-sm">
        <div class="row q-gutter-sm items-center bg-grey-2 q-pa-sm rounded-borders">
             <q-select v-model="filterYear" :options="yearOptions" label="Year" dense outlined bg-color="white" style="width: 100px" />
             <q-select v-model="filterMonth" :options="monthFilterOptions" label="Month" dense outlined bg-color="white" style="width: 140px" emit-value map-options />
             <div class="text-caption text-grey-8 q-px-sm column">
                <span class="text-weight-bold">Billing Cycle</span>
                <span style="font-size: 10px">{{ cycleDateRange }}</span>
             </div>
        </div>
        <q-btn
          unelevated
          color="purple"
          icon="payments"
          label="Record Cash"
          @click="showCashDialog = true"
        />
        <q-btn flat icon="refresh" @click="refreshAll" :loading="refreshing" round />
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-blue-9 text-white full-height">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR {{ stats.revenue.toLocaleString() }}</div>
            <div class="text-caption">Total Revenue (Collected)</div>
            <div class="text-caption text-blue-2 q-mt-sm">
              <q-icon name="trending_up" /> Growth: +5% (Est)
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-green-8 text-white full-height">
          <q-card-section>
            <!-- Assuming 20% Net Income for now -->
            <div class="text-h4 text-weight-bold">
              LKR {{ (stats.revenue * 0.2).toLocaleString() }}
            </div>
            <div class="text-caption">Net Profit (Institute Share)</div>
            <div class="text-caption text-green-2 q-mt-sm">After Teacher Payouts (80%)</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-orange-8 text-white full-height">
          <q-card-section>
            <div class="text-h4 text-weight-bold">{{ pendingTransactions.length }}</div>
            <div class="text-caption">Pending Verifications</div>
            <div class="text-caption text-orange-2 q-mt-sm">Action Required</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-sm-6 col-md-3">
        <q-card class="bg-indigo-7 text-white full-height">
          <q-card-section>
            <div class="text-h4 text-weight-bold">
              LKR {{ stats.pending_fees.toLocaleString() }}
            </div>
            <div class="text-caption">Uncollected Fees</div>
            <q-linear-progress
              :value="collectionRate"
              color="white"
              track-color="indigo-4"
              class="q-mt-sm"
              rounded
            />
            <div class="text-caption text-indigo-1 q-mt-xs text-right">
              Collection Rate: {{ (isNaN(collectionRate) ? 0 : collectionRate * 100).toFixed(1) }}%
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Revenue Analytics Row -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-6">
        <q-card class="full-height">
          <q-card-section class="row justify-between">
            <div class="text-h6">Revenue Analytics</div>
            <div class="row q-gutter-sm">
              <q-select
                outlined
                dense
                v-model="reportMonth"
                :options="monthOptions"
                label="Month"
                style="min-width: 120px"
              />
              <q-btn
                flat
                round
                color="secondary"
                icon="download"
                @click="generateReport"
                :loading="reportLoading"
              />
            </div>
          </q-card-section>
          <div class="q-pa-md">
            <VueApexCharts type="area" height="300" :options="chartOptions" :series="chartSeries" />
          </div>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <q-card class="full-height">
          <q-card-section><div class="text-h6">Payment Methods</div></q-card-section>
          <div class="q-pa-md flex flex-center">
            <VueApexCharts type="donut" height="250" :options="pieOptions" :series="pieSeries" />
          </div>
        </q-card>
      </div>

      <div class="col-12 col-md-3">
        <!-- Course Breakdown -->
        <q-card class="full-height scroll">
          <q-card-section>
            <div class="text-h6">Top Courses</div>
          </q-card-section>
          <q-list separator dense>
            <q-item v-for="(course, i) in analyticsData.courses" :key="i">
              <q-item-section>
                <q-item-label class="text-caption">{{ course.course_name }}</q-item-label>
                <q-linear-progress :value="0.8" class="q-mt-xs" color="primary" size="xs" />
              </q-item-section>
              <q-item-section side>
                <div class="text-caption text-weight-bold">
                  LKR {{ (parseInt(course.total) / 1000).toFixed(1) }}k
                </div>
              </q-item-section>
            </q-item>
            <div v-if="analyticsData.courses.length === 0" class="text-center text-grey q-pa-md">
              No Data Available
            </div>
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
          <q-badge color="orange" floating v-if="pendingTransactions.length > 0">{{
            pendingTransactions.length
          }}</q-badge>
        </q-tab>
        <q-tab name="all" label="All Transactions" icon="list" />
        <q-tab name="uncollected" label="Uncollected Fees" icon="money_off">
          <q-badge color="red" floating v-if="uncollectedFees.length > 0">{{
            uncollectedFees.length
          }}</q-badge>
        </q-tab>
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
            :pagination="tablePagination"
            :rows-per-page-options="[100, 200, 500, 1000, 0]"
          >
            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  size="sm"
                  color="primary"
                  label="Verify"
                  @click="openVerifyDialog(props.row)"
                />
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
            :pagination="tablePagination"
            :rows-per-page-options="[100, 200, 500, 1000, 0]"
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

        <!-- Uncollected Fees Tab -->
        <q-tab-panel name="uncollected" class="q-pa-none">
          <q-table
            :rows="uncollectedFees"
            :columns="uncollectedColumns"
            row-key="id"
            flat
            :pagination="tablePagination"
            :rows-per-page-options="[100, 200, 500, 1000, 0]"
            :filter="uncollectedFilter"
          >
            <template v-slot:top-right>
              <q-input
                borderless
                dense
                debounce="300"
                v-model="uncollectedFilter"
                placeholder="Search Student or Course"
              >
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </template>
            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <q-chip color="red-1" text-color="red" label="Pending" size="sm" icon="warning" />
              </q-td>
            </template>

            <template v-slot:body-cell-amount="props">
              <q-td :props="props" class="text-weight-bold"> LKR {{ props.value }} </q-td>
            </template>
          </q-table>
          <div v-if="uncollectedFees.length === 0" class="text-center q-pa-lg text-grey">
            No pending fees found.
          </div>
        </q-tab-panel>

        <!-- Settlements Tab -->
        <q-tab-panel name="settlements" class="q-pa-none">
          <!-- Month Selector -->
          <div class="row items-center q-pa-md justify-between">
            <div class="text-h6">Teacher Settlements</div>
            <div class="row q-gutter-sm items-center">
              <span>Month:</span>
              <q-input outlined dense v-model="settlementMonth" mask="####-##" style="width: 150px">
                <template v-slot:append>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-date
                        v-model="settlementMonth"
                        mask="YYYY-MM"
                        minimal
                        emit-immediately
                        v-close-popup
                      />
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
          </div>

          <q-table
            :rows="settlements"
            :columns="settlementColumns"
            row-key="teacher_id"
            flat
            :pagination="tablePagination"
            :rows-per-page-options="[100, 200, 500, 1000, 0]"
          >
            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn
                  size="sm"
                  color="primary"
                  label="Paysheet"
                  @click="openPaysheet(props.row)"
                  icon="print"
                />
              </q-td>
            </template>

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
    <!-- Verify Dialog -->
    <q-dialog v-model="showVerifyDialog" persistent transition-show="scale" transition-hide="scale">
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center justify-between bg-primary text-white">
          <div class="text-h6">Verify Payment</div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedPayment" class="q-pt-md">
          <div class="row q-col-gutter-md">
            <!-- Slip Image Preview -->
            <div class="col-12 col-md-6">
              <div class="text-subtitle2 q-mb-xs">Payment Slip</div>
              <q-img
                :src="
                  selectedPayment.slip_image
                    ? 'http://localhost:8000/storage/' + selectedPayment.slip_image
                    : ''
                "
                style="height: 300px; border: 1px solid #ccc; background: #f5f5f5"
                fit="contain"
                class="rounded-borders"
              >
                <template v-slot:error>
                  <div class="absolute-full flex flex-center bg-grey-2 text-grey">
                    <div class="text-center">
                      <q-icon name="broken_image" size="md" />
                      <div class="text-caption">No Image Available</div>
                    </div>
                  </div>
                </template>
              </q-img>
              <div class="q-mt-sm text-center" v-if="selectedPayment.slip_image">
                <q-btn
                  flat
                  size="sm"
                  color="primary"
                  label="Open Full Image"
                  type="a"
                  :href="'http://localhost:8000/storage/' + selectedPayment.slip_image"
                  target="_blank"
                  icon="open_in_new"
                />
              </div>
            </div>

            <!-- Details -->
            <div class="col-12 col-md-6">
              <q-list separator>
                <q-item>
                  <q-item-section>
                    <q-item-label caption>Student</q-item-label>
                    <q-item-label class="text-weight-bold">{{
                      selectedPayment.student?.name
                    }}</q-item-label>
                    <q-item-label caption>{{ selectedPayment.student?.username }}</q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section>
                    <q-item-label caption>Amount</q-item-label>
                    <q-item-label class="text-h6 text-primary"
                      >LKR {{ selectedPayment.amount }}</q-item-label
                    >
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section>
                    <q-item-label caption>Date</q-item-label>
                    <q-item-label>{{
                      new Date(selectedPayment.created_at).toLocaleString()
                    }}</q-item-label>
                  </q-item-section>
                </q-item>

                <q-item>
                  <q-item-section>
                    <q-item-label caption>Type</q-item-label>
                    <q-item-label>
                      <q-chip
                        size="sm"
                        :color="selectedPayment.type === 'online' ? 'purple' : 'teal'"
                        text-color="white"
                      >
                        {{ selectedPayment.type?.toUpperCase() }}
                      </q-chip>
                      <span v-if="selectedPayment.is_manual" class="text-caption text-grey">
                        (Manual)</span
                      >
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>

              <div class="q-mt-md">
                <q-input
                  v-model="verificationNote"
                  label="Admin Note (Optional)"
                  placeholder="Reason for rejection etc."
                  outlined
                  type="textarea"
                  rows="3"
                />
              </div>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn
            color="negative"
            label="Reject"
            icon="close"
            @click="processVerification('rejected')"
            :loading="processingVerify"
          />
          <q-btn
            color="positive"
            label="Approve"
            icon="check"
            @click="processVerification('paid')"
            :loading="processingVerify"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Generate Fees Dialog -->
    <q-dialog v-model="showGenerateDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Generate Monthly Fees</div>
        </q-card-section>

        <q-card-section class="q-gutter-md">
          <q-input outlined v-model="genMonth" label="Target Month" mask="####-##-##">
            <template v-slot:append>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="genMonth" mask="YYYY-MM-DD" minimal v-close-popup="true" />
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>

          <q-input outlined v-model="genDueDate" label="Due Date" mask="####-##-##">
            <template v-slot:append>
              <q-icon name="event" class="cursor-pointer">
                <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                  <q-date v-model="genDueDate" mask="YYYY-MM-DD" minimal v-close-popup="true" />
                </q-popup-proxy>
              </q-icon>
            </template>
          </q-input>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn
            flat
            label="Generate"
            color="primary"
            @click="handleGenerate"
            :loading="generating"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Cash Payment Dialog -->
    <q-dialog v-model="showCashDialog" @show="focusSearch">
      <q-card style="min-width: 500px" :class="$q.dark.isActive ? 'bg-dark' : ''">
        <q-card-section class="row items-center justify-between bg-primary text-white">
          <div class="text-h6">Record Cash Payment</div>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-gutter-md q-pt-md">
          <!-- Barcode / Search Input -->
          <q-input
            outlined
            v-model="cashSearchQuery"
            label="Scan Student ID / Type Name"
            placeholder="Press Enter to search"
            hint="Barcode Ready: Scan ID to auto-select student"
            @keyup.enter="searchStudentForCash"
            ref="cashSearchInput"
            autofocus
            :loading="cashSearching"
          >
            <template v-slot:prepend>
              <q-icon name="qr_code_scanner" />
            </template>
            <template v-slot:append>
              <q-icon name="search" class="cursor-pointer" @click="searchStudentForCash" />
            </template>
          </q-input>

          <!-- Selected Student Display -->
          <transition name="fade">
            <div
              v-if="cashStudent"
              class="q-pa-sm bg-blue-1 rounded-borders border-blue text-left row items-center"
            >
              <q-avatar
                color="primary"
                text-color="white"
                icon="person"
                size="md"
                class="q-mr-md"
              />
              <div>
                <div class="text-weight-bold text-primary">{{ cashStudent.name }}</div>
                <div class="text-caption text-grey-8">
                  {{ cashStudent.username }} | {{ cashStudent.phone || 'No Phone' }}
                </div>
              </div>
              <q-space />
              <q-btn flat round color="negative" icon="close" size="sm" @click="clearCashStudent" />
            </div>
          </transition>

          <!-- Pending Fees Table -->
          <div v-if="cashStudent">
            <div v-if="cashPendingFees.length > 0">
              <div class="text-caption text-grey-8 q-mb-xs">Select Pending Fees:</div>
              <q-table
                flat
                bordered
                dense
                :rows="cashPendingFees"
                :columns="[
                  { name: 'course', label: 'Course', field: 'course_name', align: 'left' },
                  { name: 'month', label: 'Month', field: 'month_label', align: 'left' },
                  {
                    name: 'amount',
                    label: 'Amount',
                    field: 'amount',
                    align: 'right',
                    format: (val) => 'LKR ' + val,
                  },
                ]"
                row-key="id"
                selection="multiple"
                v-model:selected="selectedFees"
                :rows-per-page-options="[0]"
                hide-bottom
                class="sticky-header-table"
                style="max-height: 250px"
              />

              <div
                class="row justify-between items-center q-mt-md bg-blue-1 q-pa-md rounded-borders"
              >
                <div class="text-subtitle1 text-weight-bold">Total Payment:</div>
                <div class="text-h5 text-primary text-weight-bold">
                  LKR {{ Number(cashAmount).toLocaleString() }}
                </div>
              </div>
            </div>
            <div v-else class="text-center q-pa-lg text-grey">
              <q-icon name="check_circle" size="md" color="positive" class="q-mb-sm" />
              <div>No pending fees found for this student.</div>
            </div>
          </div>

          <q-input outlined v-model="cashNote" label="Note (Optional)" type="textarea" rows="2" />
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md bg-grey-1">
          <q-btn flat label="Cancel" v-close-popup color="grey" />
          <q-btn
            unelevated
            color="green-7"
            icon="print"
            label="Record & Print"
            @click="handleCashPayment"
            :loading="cashProcessing"
            :disable="!cashStudent || selectedFees.length === 0"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Paysheet Dialog -->
    <q-dialog v-model="showPaysheetDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Settlement Details</div>
          <div class="text-subtitle2">{{ activeSettlement?.teacher_name }}</div>
        </q-card-section>

        <q-card-section>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-4">
              <q-input
                v-model.number="instituteCommission"
                label="Institute Commission (%)"
                type="number"
                outlined
                dense
                :rules="[(val) => val >= 0 && val <= 100]"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-input
                v-model.number="bonusAmount"
                label="Bonus / Addition (LKR)"
                type="number"
                outlined
                dense
              />
              <q-input
                v-model="bonusNote"
                label="Reason"
                dense
                outlined
                class="q-mt-xs"
                style="font-size: 11px"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-input
                v-model.number="deductionAmount"
                label="Deduction (LKR)"
                type="number"
                outlined
                dense
              />
              <q-input
                v-model="deductionNote"
                label="Reason"
                dense
                outlined
                class="q-mt-xs"
                style="font-size: 11px"
              />
            </div>
          </div>

          <div class="q-mt-lg">
            <div class="row justify-between q-py-xs">
              <span>Total Collected:</span>
              <span class="text-weight-bold"
                >LKR {{ (activeSettlement?.total_collected || 0).toLocaleString() }}</span
              >
            </div>
            <div class="row justify-between q-py-xs text-red">
              <span>Institute Share ({{ instituteCommission }}%):</span>
              <span>- LKR {{ instituteShareAmount.toLocaleString() }}</span>
            </div>
            <div class="row justify-between q-py-xs text-positive" v-if="bonusAmount > 0">
              <span>Bonus/Adjustment:</span>
              <span>+ LKR {{ bonusAmount.toLocaleString() }}</span>
            </div>
            <div class="row justify-between q-py-xs text-orange" v-if="deductionAmount > 0">
              <span>Deduction:</span>
              <span>- LKR {{ deductionAmount.toLocaleString() }}</span>
            </div>
            <q-separator class="q-my-sm" />
            <div class="row justify-between q-py-sm">
              <span class="text-h6">Net Pay:</span>
              <span class="text-h6 text-green">LKR {{ activeShare.toLocaleString() }}</span>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" v-close-popup />
          <q-btn color="primary" icon="print" label="Print / View PDF" @click="printPaysheet" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Hidden Print Template -->
    <div class="print-container" v-if="activeSettlement">
      <div class="text-center q-mb-xl">
        <h2>Education Management System</h2>
        <h3>Teacher Paysheet</h3>
        <div class="text-grey">{{ new Date().toLocaleDateString() }}</div>
      </div>

      <div class="q-mb-lg">
        <strong>Teacher:</strong> {{ activeSettlement.teacher_name }} <br />
        <strong>ID:</strong> {{ activeSettlement.teacher_id }} <br />
        <strong>Month:</strong>
        {{ new Date().toLocaleString('default', { month: 'long', year: 'numeric' }) }}
      </div>

      <table class="print-table">
        <thead>
          <tr>
            <th>Description</th>
            <th class="text-right">Count/Ref</th>
            <th class="text-right">Amount (LKR)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Total Enrolled Students</td>
            <td class="text-right">{{ activeSettlement.total_students }}</td>
            <td class="text-right">-</td>
          </tr>
          <tr>
            <td>Unpaid Fees (Pending)</td>
            <td class="text-right">{{ activeSettlement.pending_count }}</td>
            <td class="text-right text-red">
              ({{ (activeSettlement.total_pending || 0).toLocaleString() }})
            </td>
          </tr>
          <tr>
            <td><strong>Total Fee Collections</strong></td>
            <td class="text-right">{{ activeSettlement.payment_count }}</td>
            <td class="text-right">
              <strong>{{ (activeSettlement.total_collected || 0).toLocaleString() }}</strong>
            </td>
          </tr>
          <tr>
            <td>Institute Commission ({{ instituteCommission }}%)</td>
            <td></td>
            <td class="text-right text-red">- {{ instituteShareAmount.toLocaleString() }}</td>
          </tr>
          <tr v-if="bonusAmount > 0">
            <td>
              Bonus / Addition (Fixed)
              <span v-if="bonusNote" class="text-italic text-grey q-ml-sm">({{ bonusNote }})</span>
            </td>
            <td></td>
            <td class="text-right text-positive">+ {{ bonusAmount.toLocaleString() }}</td>
          </tr>
          <tr v-if="deductionAmount > 0">
            <td>
              Deduction (Fixed)
              <span v-if="deductionNote" class="text-italic text-grey q-ml-sm"
                >({{ deductionNote }})</span
              >
            </td>
            <td></td>
            <td class="text-right text-red">- {{ deductionAmount.toLocaleString() }}</td>
          </tr>
          <tr class="total-row">
            <td><strong>Net Payable Amount</strong></td>
            <td></td>
            <td class="text-right">
              <strong>{{ activeShare.toLocaleString() }}</strong>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="row justify-between" style="margin-top: 100px">
        <div
          class="text-center"
          style="border-top: 1px solid black; width: 200px; padding-top: 10px"
        >
          Prepared By (Admin)
        </div>
        <div
          class="text-center"
          style="border-top: 1px solid black; width: 200px; padding-top: 10px"
        >
          Teacher Signature
        </div>
      </div>

      <div style="margin-top: 50px; font-size: 10px; color: grey">
        Generated by EMS Admin Portal on {{ new Date().toLocaleString() }}
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue' // Revert watch removal if needed or just keep cleaned
import { useFinanceStore } from 'stores/finance-store'
import { api } from 'boot/axios' // Need raw api for search
import { storeToRefs } from 'pinia'
import { useQuasar } from 'quasar'
import VueApexCharts from 'vue3-apexcharts'

const $q = useQuasar()
const financeStore = useFinanceStore()
const { transactions, pendingTransactions, stats, analyticsData, settlements, uncollectedFees } =
  storeToRefs(financeStore)

const tablePagination = ref({
  rowsPerPage: 100,
})

const pieOptions = ref({
  chart: { type: 'donut', toolbar: { show: false } },
  labels: [],
  colors: ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0'],
  legend: { position: 'bottom', fontSize: '11px' },
  dataLabels: { enabled: false },
})
const pieSeries = ref([])

const collectionRate = computed(() => {
  const total = (stats.value.revenue || 0) + (stats.value.pending_fees || 0)
  if (total === 0) return 0
  return (stats.value.revenue || 0) / total
})

const tab = ref('pending')
const filter = ref('')
const uncollectedFilter = ref('')
const reportLoading = ref(false)
const showVerifyDialog = ref(false)
const processingVerify = ref(false)
const selectedPayment = ref(null)
const verificationNote = ref('')

// Filter Logic
const filterYear = ref(new Date().getFullYear())
const filterMonth = ref(new Date().getMonth() + 1) // 1-12
const feeCycleStartDay = ref(10) // Default

const yearOptions = computed(() => {
    const y = new Date().getFullYear()
    return [y - 1, y, y + 1]
})

const monthFilterOptions = [
    { label: 'January', value: 1 },
    { label: 'February', value: 2 },
    { label: 'March', value: 3 },
    { label: 'April', value: 4 },
    { label: 'May', value: 5 },
    { label: 'June', value: 6 },
    { label: 'July', value: 7 },
    { label: 'August', value: 8 },
    { label: 'September', value: 9 },
    { label: 'October', value: 10 },
    { label: 'November', value: 11 },
    { label: 'December', value: 12 }
]

const cycleDateRange = computed(() => {
    const y = filterYear.value
    const m = filterMonth.value
    const d = feeCycleStartDay.value

    // Start Date
    const start = new Date(y, m - 1, d)
    // End Date (Next month same day)
    const end = new Date(y, m, d)

    return `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`
})

const apiParams = computed(() => {
    const y = filterYear.value
    const m = filterMonth.value
    const d = feeCycleStartDay.value

    // Start: Y-m-d
    const start = new Date(y, m - 1, d)

    // End: Next Month Day - 1 (The day BEFORE the next cycle starts)
    // Date(y, m, d) is Start of next cycle.
    // Date(y, m, d - 1) is End of current cycle.
    const end = new Date(y, m, d - 1)

    const formatDate = (date) => {
        return date.toISOString().slice(0, 10)
    }

    return {
        start_date: formatDate(start),
        end_date: formatDate(end)
    }
})

function openVerifyDialog(row) {
  selectedPayment.value = row
  verificationNote.value = ''
  showVerifyDialog.value = true
}

async function processVerification(status) {
  if (!selectedPayment.value) return

  console.log('Processing Verification - Status:', status) // Debug Log
  processingVerify.value = true
  try {
    const payload = {
      admin_note: verificationNote.value,
    }

    let endpoint = ''
    if (status === 'paid') {
      endpoint = `/v1/payments/${selectedPayment.value.id}/approve`
    } else {
      endpoint = `/v1/payments/${selectedPayment.value.id}/reject`
    }
    console.log('Endpoint:', endpoint) // Debug Log

    await api.post(endpoint, payload)

    $q.notify({
      type: status === 'paid' ? 'positive' : 'negative',
      message: `Payment ${status === 'paid' ? 'Approved' : 'Rejected'}`,
    })

    showVerifyDialog.value = false
    financeStore.fetchTransactions() // Refresh list
  } catch (e) {
    console.error(e)
    // Show specific error from backend if available
    const msg = e.response?.data?.message || 'Operation Failed'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    processingVerify.value = false
  }
}

const reportMonth = ref(new Date().toISOString().slice(0, 7))
const monthOptions = ['2026-01', '2026-02', '2026-03']

const showGenerateDialog = ref(false)
const genMonth = ref(new Date().toISOString().slice(0, 10))
const genDueDate = ref(
  new Date(new Date().setDate(new Date().getDate() + 10)).toISOString().slice(0, 10),
)
const generating = ref(false)

const showCashDialog = ref(false)
const cashStudent = ref(null)
const cashPendingFees = ref([])
const selectedFees = ref([])
const cashAmount = ref('')
const cashNote = ref('')
const cashProcessing = ref(false)

const chartOptions = ref({
  chart: { type: 'area', height: 350, toolbar: { show: false } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' },
  xaxis: { categories: [] },
  yaxis: { labels: { formatter: (val) => 'LKR ' + (val / 1000).toFixed(0) + 'k' } },
  tooltip: { y: { formatter: (val) => 'LKR ' + val } },
  colors: ['#1976D2'],
})

const cashSearchQuery = ref('')
const cashSearching = ref(false)
const cashSearchInput = ref(null)

// Focus helper
function focusSearch() {
  // Small delay for dialog animation
  setTimeout(() => {
    if (cashSearchInput.value) cashSearchInput.value.focus()
  }, 300)
}

function clearCashStudent() {
  cashStudent.value = null
  cashPendingFees.value = []
  selectedFees.value = []
  cashAmount.value = ''
  cashSearchQuery.value = ''
  focusSearch()
}

async function searchStudentForCash() {
  if (!cashSearchQuery.value) return
  cashSearching.value = true
  try {
    // Search by ID (exact) or Name (partial)
    const params = { role: 'student', search: cashSearchQuery.value }
    // If it looks like a barcode (pure numbers/id), assume exact match intent
    // The API likely does 'LIKE %query%'
    const res = await api.get('/v1/users', { params })

    const results = res.data.data
    if (results.length >= 1) {
      // If exact ID match exists, prioritize it
      const exact = results.find(
        (s) => s.username.toLowerCase() === cashSearchQuery.value.toLowerCase(),
      )
      const target = exact || results[0]

      cashStudent.value = target
      $q.notify({
        type: 'positive',
        message: `Student Found: ${target.name}`,
        position: 'top',
        timeout: 1000,
      })
      cashSearchQuery.value = ''
    } else {
      $q.notify({ type: 'negative', message: 'Student Not Found', position: 'top' })
      cashSearchInput.value.select() // Select text for retry
    }
  } catch (e) {
    console.error(e)
  } finally {
    cashSearching.value = false
  }
}

const chartSeries = ref([
  {
    name: 'Revenue',
    data: [],
  },
])

// Watch for analytics data to update chart
watch(
  () => analyticsData.value,
  (newVal) => {
    // Fix: Use 'monthly' instead of 'monthly_revenue' as defined in analyticsData structure
    if (newVal && newVal.monthly) {
      chartOptions.value = {
        ...chartOptions.value,
        xaxis: {
          categories: newVal.monthly.map((item) => item.month),
        },
      }
      chartSeries.value = [
        {
          name: 'Revenue',
          data: newVal.monthly.map((item) => item.total),
        },
      ]
    }

    // Pie Chart
    if (newVal && newVal.methods) {
      pieOptions.value = {
        ...pieOptions.value,
        labels: newVal.methods.map((m) => m.type),
      }
      pieSeries.value = newVal.methods.map((m) => m.count)
    }
  },
  { deep: true },
)

async function generateReport() {
  reportLoading.value = true
  try {
    const res = await financeStore.exportReport(apiParams.value)
    if (res) {
       // Download logic handled in store now usually, but if store returns boolean:
       // The store logic I updated handles download.
       $q.notify({ type: 'positive', message: 'Report Downloaded' })
    } else {
       // Failure handled in store
    }
  } catch (e) {
    console.error(e)
    $q.notify({ type: 'negative', message: 'Export Error' })
  } finally {
    reportLoading.value = false
  }
}


// Watch student selection to fetch PENDING FEES
watch(cashStudent, async (newVal) => {
  if (newVal && newVal.id) {
    try {
      // Fetch pending fees for this student
      const res = await api.get(`/v1/admin/students/${newVal.id}/pending-fees`)
      cashPendingFees.value = res.data

      // Auto-Select All by default
      selectedFees.value = cashPendingFees.value.map((fee) => fee)
    } catch (e) {
      console.error(e)
      $q.notify({ type: 'warning', message: 'Could not fetch pending fees' })
      cashPendingFees.value = []
    }
  } else {
    cashPendingFees.value = []
    selectedFees.value = []
  }
})

// Compute Total Amount based on Selection
watch(
  selectedFees,
  (newVal) => {
    const total = newVal.reduce((sum, fee) => sum + parseFloat(fee.amount || 0), 0)
    cashAmount.value = total
  },
  { deep: true },
)

async function handleCashPayment() {
  if (selectedFees.value.length === 0) {
    $q.notify({ type: 'warning', message: 'No fees selected' })
    return
  }

  cashProcessing.value = true
  try {
    const payload = {
      student_id: cashStudent.value.id, // Optional depending on backend, but good for context
      fee_ids: selectedFees.value.map((f) => f.id),
      amount: cashAmount.value, // Total amount
      type: 'cash',
      note: cashNote.value,
    }

    const res = await financeStore.recordBatchPayment(payload)

    if (res.success) {
      $q.notify({ type: 'positive', message: 'Payment Recorded Successfully' })
      showCashDialog.value = false
      financeStore.fetchTransactions()
      financeStore.fetchAnalytics()

      // Print Receipt Logic (Enhanced for Batch)
      printAdminReceipt(
        res.payment || { id: 'BATCH', amount: cashAmount.value },
        cashStudent.value,
        selectedFees.value,
      )

      // Reset
      clearCashStudent()
    } else {
      $q.notify({ type: 'negative', message: res.error })
    }
  } finally {
    cashProcessing.value = false
  }
}

function printAdminReceipt(payment, student, course) {
  const printWindow = window.open('', '_blank', 'width=800,height=600')
  if (!printWindow) return

  const htmlContent =
    `
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
            <script>setTimeout(() => { window.print(); window.close(); }, 500);` +
    '<' +
    '/script>' +
    `
        </body></html>`
  printWindow.document.write(htmlContent)
  printWindow.document.close()
}

const getStatusColor = (status) => {
  if (status === 'paid') return 'green'
  if (status === 'pending') return 'orange'
  if (status === 'rejected') return 'red'
  return 'grey'
}

// ... (existing functions) ...

/* Update Imports - already done at top */

/* Add Columns */
const uncollectedColumns = [
  {
    name: 'student_name',
    label: 'Student',
    field: (row) => row.student?.name || 'Unknown',
    align: 'left',
    sortable: true,
  },
  {
    name: 'course_name',
    label: 'Course',
    field: (row) => row.course?.name || 'Unknown',
    align: 'left',
    sortable: true,
  },
  { name: 'month', label: 'Month', field: 'month', align: 'center', sortable: true },
  { name: 'amount', label: 'Amount (LKR)', field: 'amount', align: 'right', sortable: true },
  { name: 'days_overdue', label: 'Status', field: 'status', align: 'center' },
]

/* Update onMounted */
const refreshing = ref(false)

async function fetchSettings() {
    try {
        const res = await api.get('/v1/admin/settings')
        if (res.data && res.data.feeCycleStartDay) {
            feeCycleStartDay.value = parseInt(res.data.feeCycleStartDay)
        }
    } catch (e) {
        console.warn('Failed to fetch settings for fee cycle', e)
    }
}

async function refreshAll() {
  refreshing.value = true
  try {
    const params = apiParams.value
    await Promise.all([
      financeStore.fetchTransactions(params),
      financeStore.fetchAnalytics(params),
      financeStore.fetchSettlements(params),
      financeStore.fetchUncollectedFees(),
    ])
    $q.notify({ type: 'positive', message: 'Dashboard Updated', timeout: 500, position: 'top' })
  } finally {
    refreshing.value = false
  }
}

onMounted(async () => {
  await fetchSettings()
  // 10th of the month logic
  const today = new Date()
  if (today.getDate() <= 10) {
    // Go back to previous month
    today.setMonth(today.getMonth() - 1)
  }

  // Set filters
  filterYear.value = today.getFullYear()
  filterMonth.value = today.getMonth() + 1

  refreshAll()
})

watch(apiParams, () => {
    refreshAll()
})

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'student', label: 'Student', field: 'student', align: 'left' },
  { name: 'amount', label: 'Amount (LKR)', field: 'amount', align: 'right' },
  {
    name: 'date',
    label: 'Date',
    field: 'created_at',
    format: (val) => new Date(val).toLocaleDateString(),
    align: 'right',
  },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'right' },
]

const settlementColumns = [
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left', sortable: true },
  {
    name: 'total_students',
    label: 'Total Students',
    field: 'total_students',
    align: 'center',
    sortable: true,
  },
  { name: 'payment_count', label: 'Paid', field: 'payment_count', align: 'center', sortable: true },
  {
    name: 'pending_count',
    label: 'Pending',
    field: 'pending_count',
    align: 'center',
    sortable: true,
  },
  {
    name: 'total_collected',
    label: 'Collected (LKR)',
    field: 'total_collected',
    align: 'right',
    format: (val) => parseFloat(val).toLocaleString(),
    sortable: true,
  },
  {
    name: 'share',
    label: 'Default Share (80%)',
    field: 'teacher_share',
    align: 'right',
    format: (val) => parseFloat(val).toLocaleString(),
    sortable: true,
  },
  { name: 'actions', label: 'Action', field: 'actions', align: 'right' },
]
const showPaysheetDialog = ref(false)
const activeSettlement = ref(null)
const instituteCommission = ref(20)

const settlementMonth = ref(new Date().toISOString().slice(0, 7))
const bonusAmount = ref(0)
const deductionAmount = ref(0)
const bonusNote = ref('')
const deductionNote = ref('')

const instituteShareAmount = computed(() => {
  if (!activeSettlement.value) return 0
  const collected = parseFloat(activeSettlement.value.total_collected || 0)
  return collected * (instituteCommission.value / 100)
})

const activeShare = computed(() => {
  if (!activeSettlement.value) return 0
  const collected = parseFloat(activeSettlement.value.total_collected || 0)
  const baseShare = collected - instituteShareAmount.value
  return baseShare + (bonusAmount.value || 0) - (deductionAmount.value || 0)
})

function openPaysheet(row) {
  activeSettlement.value = row
  instituteCommission.value = 20
  bonusAmount.value = 0
  deductionAmount.value = 0
  bonusNote.value = ''
  deductionNote.value = ''
  showPaysheetDialog.value = true
}

function printPaysheet() {
  window.print()
}

watch(settlementMonth, (newVal) => {
  financeStore.fetchSettlements({ month: newVal })
})

async function handleGenerate() {
  // Validate inputs
  if (!genMonth.value || !genDueDate.value) {
    $q.notify({ type: 'warning', message: 'Please select month and due date' })
    return
  }

  generating.value = true
  try {
    const monthValue = genMonth.value.slice(0, 7) // Extract Y-m format
    const res = await financeStore.generateFees({
      month: monthValue,
      due_date: genDueDate.value,
    })

    if (res.success) {
      const message =
        res.count === 0 ? 'All fees for this month are already generated.' : res.message

      $q.notify({
        type: res.count > 0 ? 'positive' : 'info',
        message: message,
      })

      showGenerateDialog.value = false
      // Refresh all tabs
      await refreshAll()
    } else {
      $q.notify({ type: 'negative', message: 'Error: ' + res.error })
    }
  } catch (error) {
    console.error('Generate fees error:', error)
    $q.notify({ type: 'negative', message: 'Failed to generate fees' })
  } finally {
    generating.value = false
  }
}
</script>

<style scoped>
.border-left-primary {
  border-left: 4px solid #1976d2;
}
.border-left-green {
  border-left: 4px solid #4caf50;
}
.border-left-orange {
  border-left: 4px solid #ff9800;
}
.border-left-purple {
  border-left: 4px solid #9c27b0;
}

.print-container {
  display: none;
  font-family: 'Times New Roman', serif;
}
.print-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
.print-table th,
.print-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}
.print-table .text-right {
  text-align: right;
}
.total-row td {
  border-top: 2px solid black;
  font-weight: bold;
}

@media print {
  body > * {
    display: none !important;
  }
  .q-dialog__backdrop {
    display: none !important;
  }

  .print-container {
    display: block !important;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    z-index: 9999;
    padding: 40px;
  }
}
</style>
