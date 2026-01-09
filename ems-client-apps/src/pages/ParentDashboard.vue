<template>
  <q-page class="q-pa-md bg-grey-1">
    <!-- Header: Child Selector -->
    <div class="row items-center justify-between q-mb-lg bg-white q-pa-md rounded-borders shadow-1">
      <div>
        <div class="text-subtitle2 text-grey-7">Viewing details for:</div>
        <div class="text-h6 text-primary text-weight-bold">{{ selectedChild.name }}</div>
      </div>
      <q-btn-dropdown color="primary" flat label="Switch Child" icon="child_care">
        <q-list>
          <q-item v-for="child in children" :key="child.id" clickable v-close-popup @click="selectedChild = child">
            <q-item-section avatar>
              <q-avatar icon="face" color="primary" text-color="white" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ child.name }}</q-item-label>
              <q-item-label caption>{{ child.batch }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </div>

    <!-- Quick Stats -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3">
        <q-card class="bg-indigo-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">85%</div>
            <div class="text-caption">Avg. Attendance</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="bg-purple-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">3rd</div>
            <div class="text-caption">Rank in Class</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="bg-orange-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">LKR 5k</div>
            <div class="text-caption">Due Payments</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="bg-green-6 text-white text-center q-py-sm">
          <q-card-section>
            <div class="text-h4 text-weight-bold">A</div>
            <div class="text-caption">Last Exam Grade</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Detailed Sections -->
    <div class="row q-col-gutter-lg">
      <!-- Attendance Calendar -->
      <div class="col-12 col-md-8">
        <q-card class="full-height">
          <q-card-section>
            <div class="text-h6">Monthly Attendance</div>
          </q-card-section>
          <q-separator />
          <q-date
            v-model="attendanceDates"
            minimal
            read-only
            :events="events"
            :event-color="(date) => date[5] % 2 === 0 ? 'teal' : 'orange'"
            class="full-width no-shadow"
          />
        </q-card>
      </div>

      <!-- Notifications / Feed -->
      <div class="col-12 col-md-4">
        <q-card class="full-height">
          <q-card-section>
            <div class="text-h6">Recent Activity</div>
          </q-card-section>
          <q-list separator>
             <q-item v-for="n in 4" :key="n">
               <q-item-section avatar>
                 <q-icon :name="n % 2 == 0 ? 'warning' : 'check_circle'" :color="n % 2 == 0 ? 'red' : 'green'" />
               </q-item-section>
               <q-item-section>
                 <q-item-label class="text-weight-bold">{{ n % 2 == 0 ? 'Absent for Physics Class' : 'Attended Chemistry Class' }}</q-item-label>
                 <q-item-label caption>Yesterday at {{ 2 + n }}:00 PM</q-item-label>
               </q-item-section>
             </q-item>
          </q-list>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const children = [
  { id: 1, name: 'Kasun Perera', batch: '2026 A/L Physics' },
  { id: 2, name: 'Nimali Perera', batch: '2028 O/L' }
]

const selectedChild = ref(children[0])
const attendanceDates = ref('2026/01/09')
const events = ['2026/01/01', '2026/01/05', '2026/01/06']
</script>
