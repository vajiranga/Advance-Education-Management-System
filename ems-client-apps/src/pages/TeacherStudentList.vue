<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-teal-2' : 'text-teal-9'">My Students</div>
      <div class="row q-gutter-x-sm">
         <q-input 
            dense 
            outlined 
            v-model="search" 
            placeholder="Search students..." 
            :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" 
            :dark="$q.dark.isActive"
         >
            <template v-slot:prepend><q-icon name="search" /></template>
         </q-input>
         <q-select 
            dense 
            outlined 
            v-model="filterClass" 
            :options="classOptions" 
            :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" 
            :dark="$q.dark.isActive" 
            style="min-width:200px" 
         />
      </div>
    </div>

    <q-card class="no-shadow" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white border-light'">
       <q-table
          :rows="students"
          :columns="columns"
          row-key="id"
          flat
          :filter="search"
          :dark="$q.dark.isActive"
          :class="$q.dark.isActive ? 'bg-dark text-white' : 'bg-white text-black'"
       >
          <template v-slot:body-cell-avatar="props">
             <q-td :props="props">
                <q-avatar size="32px">
                   <img :src="props.row.avatar" />
                </q-avatar>
             </q-td>
          </template>
          
          <!-- New Fees Slot -->
          <template v-slot:body-cell-fees="props">
             <q-td :props="props">
                <q-chip 
                    size="sm" 
                    :color="props.row.payment_status === 'paid' ? ($q.dark.isActive ? 'green-9' : 'green-1') : (props.row.payment_status === 'pending' ? ($q.dark.isActive ? 'orange-9' : 'orange-1') : 'red-1')" 
                    :text-color="props.row.payment_status === 'paid' ? ($q.dark.isActive ? 'green-2' : 'green') : (props.row.payment_status === 'pending' ? ($q.dark.isActive ? 'orange-2' : 'orange-8') : 'red')"
                    class="text-weight-bold"
                >
                   {{ props.row.payment_status === 'paid' ? 'PAID' : (props.row.payment_status === 'pending' ? 'PENDING' : 'OVERDUE') }}
                </q-chip>
             </q-td>
          </template>

          <template v-slot:body-cell-status="props">
             <q-td :props="props">
                <q-chip size="sm" :color="props.row.active ? ($q.dark.isActive ? 'green-9' : 'green-1') : ($q.dark.isActive ? 'red-9' : 'red-1')" :text-color="props.row.active ? ($q.dark.isActive ? 'green-2' : 'green') : ($q.dark.isActive ? 'red-2' : 'red')">
                   {{ props.row.active ? 'Active' : 'Inactive' }}
                </q-chip>
             </q-td>
          </template>
          <template v-slot:body-cell-actions="props">
             <q-td :props="props">
                <q-btn flat round icon="visibility" color="grey" size="sm" />
                <q-btn flat round icon="edit" color="blue" size="sm" />
             </q-td>
          </template>
       </q-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRoute } from 'vue-router'
import { useAuthStore } from 'stores/auth-store'

const $q = useQuasar()
const route = useRoute()
const authStore = useAuthStore()
const search = ref('')
const filterClass = ref(null) // Object: { label: 'All Classes', value: 'all' }
const classOptions = ref([{ label: 'All Classes', value: 'all' }])
const students = ref([])
const loading = ref(false)

const currentMonth = new Date().toLocaleString('default', { month: 'long' })

const columns = [
  { name: 'avatar', label: '', field: 'avatar', align: 'center' },
  { name: 'name', label: 'Student Name', field: 'name', align: 'left', sortable: true },
  { name: 'grade', label: 'Grade/Batch', field: 'grade', align: 'left', sortable: true },
  { name: 'fees', label: `Fees (${currentMonth})`, field: 'payment_status', align: 'center', sortable: true },
  { name: 'contact', label: 'Contact', field: 'contact', align: 'left' },
  { name: 'status', label: 'Status', field: 'active', align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
]

const fetchClasses = async () => {
    try {
        const params = { all: true }
        if (authStore.user?.id) {
            params.teacher_id = authStore.user.id
        }
        const res = await api.get('/v1/courses', { params })
        // Handle both pagination (res.data.data) and flat list (res.data)
        const rawData = Array.isArray(res.data) ? res.data : (res.data.data || [])
        
        const courses = rawData.map(c => ({
            label: c.name,
            value: c.id
        }))
        classOptions.value = [{ label: 'All Classes', value: 'all' }, ...courses]
        
        // Re-check query param now that options are loaded
        checkQueryParam()
    } catch (e) {
        console.error('Fetch classes error', e)
    }
}

const fetchStudents = async () => {
    loading.value = true
    try {
        const params = {}
        if (filterClass.value && filterClass.value.value !== 'all') {
            params.course_id = filterClass.value.value
        }
        if (search.value) {
            params.search = search.value
        }

        const res = await api.get('/v1/teacher/students', { params })
        students.value = res.data
    } catch (e) {
        console.error('Fetch students error', e)
        $q.notify({ type: 'negative', message: 'Failed to load students' })
    } finally {
        loading.value = false
    }
}

// Check for query param initially and on change
const checkQueryParam = () => {
    if (route.query.course_id) {
        // Wait for options to be populated if needed, though they should be by now
        const preSelected = classOptions.value.find(c => c.value == route.query.course_id)
        if (preSelected) {
            filterClass.value = preSelected
        } else {
            // Fallback or just 'all'
        }
    }
}

onMounted(async () => {
    await fetchClasses()
    checkQueryParam()
    // Explicitly call fetchStudents to ensure data load
    fetchStudents()
})

watch(() => route.query.course_id, () => {
    checkQueryParam()
})

watch(filterClass, () => {
    fetchStudents()
})

watch(search, () => {
    fetchStudents() // Could debounce this
})

// Refetch classes when user is identified (if not already)
watch(() => authStore.user, (val) => {
    if (val) {
        fetchClasses()
        fetchStudents()
    }
})
</script>




<style scoped>
.border-light { border: 1px solid #eee; }
</style>
