<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <!-- Top Bar with Selector -->
    <div class="row items-center justify-between q-mb-lg">
      <div class="row items-center q-gutter-x-md">
        <div
          class="text-h5 text-weight-bold"
          :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'"
        >
          Academic Progress
        </div>
        <div
          class="text-subtitle1"
          :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'"
          v-if="selectedChild"
        >
          for {{ selectedChild.name }}
        </div>
      </div>
      <q-btn
        icon="download"
        label="Report Card"
        outline
        :color="$q.dark.isActive ? 'deep-purple-2' : 'deep-purple'"
      />
    </div>

    <!-- Notices and Attendance -->
    <div class="row q-col-gutter-md q-mb-lg">
      <!-- Notices Section (Replaces Chart) -->
      <div class="col-12 col-md-8">
        <q-card
          class="no-shadow border-left-purple full-height"
          :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'"
        >
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="text-h6" :class="$q.dark.isActive ? 'text-white' : ''">
                Parent Meetings & Notices
              </div>
              <q-badge color="deep-purple" label="Updates" />
            </div>

            <q-list separator>
              <q-item v-for="notice in notices" :key="notice.id" class="q-px-none">
                <q-item-section avatar>
                  <q-avatar
                    :color="notice.type === 'meeting' ? 'orange' : 'teal'"
                    text-color="white"
                    :icon="notice.icon || 'campaign'"
                    size="md"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label
                    class="text-weight-bold"
                    :class="$q.dark.isActive ? 'text-white' : ''"
                    >{{ notice.title }}</q-item-label
                  >
                  <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : ''">{{
                    notice.message
                  }}</q-item-label>
                  <q-item-label caption class="text-xs text-grey" v-if="notice.course_name"
                    >{{ notice.course_name }} • {{ notice.teacher_name }}</q-item-label
                  >
                </q-item-section>
                <q-item-section side top>
                  <q-item-label caption>{{
                    new Date(notice.date).toLocaleDateString()
                  }}</q-item-label>
                </q-item-section>
              </q-item>

              <div v-if="notices.length === 0" class="text-center text-grey q-pa-md">
                No new notices or scheduled meetings.
              </div>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Attendance (Kept as it is real) -->
      <div class="col-12 col-md-4">
        <q-card
          class="text-center no-shadow full-height"
          :class="[
            $q.dark.isActive
              ? 'bg-dark text-green-2 border-dark'
              : 'bg-white text-purple-9 border-light',
          ]"
        >
          <q-card-section>
            <div class="text-h6 text-grey q-mb-md">Attendance This Month</div>
            <q-circular-progress
              show-value
              font-size="24px"
              :value="attendanceStats.percentage"
              size="120px"
              :thickness="0.2"
              color="green"
              track-color="grey-3"
              class="q-ma-md"
            >
              {{ attendanceStats.percentage }}%
            </q-circular-progress>
            <div class="text-subtitle2" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
              Present: {{ attendanceStats.present }} / {{ attendanceStats.total }} Days
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Subject Table -->
    <q-card
      class="no-shadow"
      :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'"
    >
      <q-card-section class="row items-center justify-between">
        <div class="text-h6" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">
          Detailed Results - {{ selectedTerm }}
        </div>
        <q-input
          dense
          outlined
          v-model="search"
          placeholder="Search subject..."
          class="dense-search"
          :dark="$q.dark.isActive"
          :bg-color="$q.dark.isActive ? 'grey-9' : ''"
        >
          <template v-slot:prepend><q-icon name="search" /></template>
        </q-input>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-table
          :rows="filteredResults"
          :columns="columns"
          row-key="id"
          flat
          :dark="$q.dark.isActive"
          :loading="loading"
          hide-pagination
          :rows-per-page-options="[0]"
        >
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { api } from 'boot/axios'
import { useAuthStore } from 'stores/auth-store'

const authStore = useAuthStore()
const selectedChild = computed(() => authStore.selectedChild)

const results = ref([])
const notices = ref([])
const loading = ref(false)
const search = ref('')
const selectedTerm = ref('Term 1 - 2026')

const attendanceStats = ref({
  percentage: 0,
  present: 0,
  total: 0,
})

// Computed
const filteredResults = computed(() => {
  if (!search.value) return results.value
  return results.value.filter((r) => r.subject.toLowerCase().includes(search.value.toLowerCase()))
})

// Watch Child Selection
watch(
  selectedChild,
  (newVal) => {
    if (newVal) fetchResults(newVal.id)
  },
  { immediate: true },
)

async function fetchResults(childId) {
  loading.value = true
  try {
    const res = await api.get(`/v1/parent/children/${childId}/results`)
    results.value = res.data

    // Fetch real notices
    const noticesRes = await api.get(`/v1/parent/children/${childId}/notices`)
    notices.value = noticesRes.data

    // Fetch Stats (Attendance)
    const statsRes = await api.get(`/v1/parent/children/${childId}/stats`)
    attendanceStats.value = {
      percentage: statsRes.data.attendance,
      present: statsRes.data.present_days || 0,
      total: statsRes.data.total_days || 0,
    }
  } catch (e) {
    console.error('Error fetching results', e)
  } finally {
    loading.value = false
  }
}

const columns = [
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left', sortable: true },
  { name: 'exam', label: 'Exam', field: 'exam', align: 'left', sortable: true },
  { name: 'marks', label: 'Marks', field: 'marks', align: 'center', sortable: true },
  { name: 'remarks', label: 'Feedback', field: 'remarks', align: 'left' },
]
</script>

<style scoped>
.border-left-purple {
  border-left: 4px solid #673ab7;
}
.border-light {
  border: 1px solid #eee;
}
.transition-generic {
  transition: all 0.5s ease;
}
</style>
