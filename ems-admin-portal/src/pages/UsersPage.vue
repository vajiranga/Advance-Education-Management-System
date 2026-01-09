<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">User Management</div>
      <q-btn color="primary" icon="person_add" label="Add New User" @click="showAddDialog = true" />
    </div>

    <q-card>
      <q-tabs
        v-model="tab"
        dense
        class="text-grey"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="teachers" label="Teachers" icon="school" />
        <q-tab name="students" label="Students" icon="groups" />
        <q-tab name="parents" label="Parents" icon="family_restroom" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="tab" animated>
        <!-- Teachers Panel -->
        <q-tab-panel name="teachers">
          <q-table
            :rows="teachers"
            :columns="teacherCols"
            row-key="id"
            flat
            :filter="search"
          >
             <template v-slot:body-cell-status="props">
                <q-chip :color="props.row.is_active ? 'green' : 'grey'" text-color="white" dense>
                  {{ props.row.is_active ? 'Active' : 'Inactive' }}
                </q-chip>
             </template>
          </q-table>
        </q-tab-panel>

        <!-- Students Panel -->
        <q-tab-panel name="students">
          <div class="row q-mb-sm">
             <q-input dense outlined v-model="search" placeholder="Search by Name or Barcode" class="col-4">
               <template v-slot:append><q-icon name="search" /></template>
             </q-input>
          </div>
          <q-table
            :rows="students"
            :columns="studentCols"
            row-key="id"
            flat
            :filter="search"
          >
             <template v-slot:body-cell-parent="props">
                <q-td :props="props">
                  <div class="text-weight-bold">{{ props.row.parent_name }}</div>
                  <div class="text-caption text-grey">{{ props.row.parent_phone }}</div>
                </q-td>
             </template>
          </q-table>
        </q-tab-panel>

        <!-- Parents Panel -->
        <q-tab-panel name="parents">
          <q-table
            :rows="parents"
            :columns="parentCols"
            row-key="id"
            flat
            :filter="search"
          >
            <template v-slot:body-cell-children="props">
                <q-td :props="props">
                  <q-chip v-for="child in props.row.children" :key="child" size="sm" color="blue-1" text-color="primary">
                    {{ child }}
                  </q-chip>
                </q-td>
             </template>
          </q-table>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- Add User Dialog -->
    <q-dialog v-model="showAddDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Register New User</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-select v-model="newUser.role" :options="['Teacher', 'Student', 'Parent']" label="User Role" outlined class="q-mb-md" />
          
          <q-input v-model="newUser.name" label="Full Name" outlined class="q-mb-md" />
          <q-input v-model="newUser.email" label="Email" type="email" outlined class="q-mb-md" />
          <q-input v-model="newUser.phone" label="Phone Number" outlined class="q-mb-md" />

          <!-- Student Specific Fields -->
          <div v-if="newUser.role === 'Student'">
            <q-input v-model="newUser.barcode" label="Barcode ID (Student ID)" outlined class="q-mb-md" />
            <q-input v-model="newUser.batch" label="Batch (e.g. 2026 A/L)" outlined class="q-mb-md" />
            
            <div class="text-subtitle2 q-mt-sm">Guardian Information</div>
            <q-input v-model="newUser.parentName" label="Guardian Name" outlined class="q-mb-sm" />
            <q-input v-model="newUser.parentPhone" label="Guardian Phone" outlined class="q-mb-md" />
          </div>

          <!-- Teacher Specific -->
           <div v-if="newUser.role === 'Teacher'">
            <q-input v-model="newUser.subject" label="Subject" outlined class="q-mb-md" />
          </div>

        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Register" color="primary" @click="saveUser" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { useUserStore } from 'stores/user-store'
import { storeToRefs } from 'pinia'

const $q = useQuasar()
const userStore = useUserStore()
const { teachers, students, parents } = storeToRefs(userStore)

const tab = ref('students')
const search = ref('')
const showAddDialog = ref(false)

const newUser = ref({
  role: 'Student',
  name: '',
  email: '',
  phone: '',
  barcode: '',
  batch: '',
  parentName: '',
  parentPhone: '',
  subject: ''
})

const saveUser = () => {
  if (newUser.value.role === 'Student') {
    userStore.addStudent({
      id: Math.floor(Math.random() * 1000),
      barcode: newUser.value.barcode,
      name: newUser.value.name,
      batch: newUser.value.batch,
      parent_name: newUser.value.parentName,
      parent_phone: newUser.value.parentPhone
    })
    
    // Auto-create parent logic (simplified)
    userStore.addParent({
        id: Math.floor(Math.random() * 1000),
        name: newUser.value.parentName,
        phone: newUser.value.parentPhone,
        children: [newUser.value.name]
    })
  } else if (newUser.value.role === 'Teacher') {
    userStore.addTeacher({
        id: Math.floor(Math.random() * 1000),
        name: newUser.value.name,
        email: newUser.value.email,
        subject: newUser.value.subject,
        is_active: true
    })
  }
  
  showAddDialog.value = false
  $q.notify({ type: 'positive', message: 'User Added Successfully' })
  
  // Reset Form
  newUser.value = { role: 'Student', name: '', email: '', phone: '', barcode: '', batch: '', parentName: '', parentPhone: '', subject: '' }
}

// Columns
const teacherCols = [
  { name: 'name', label: 'Name', field: 'name', align: 'left', sortable: true },
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left', sortable: true },
  { name: 'email', label: 'Email', field: 'email', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
]

const studentCols = [
  { name: 'barcode', label: 'ID', field: 'barcode', align: 'left', sortable: true },
  { name: 'name', label: 'Student Name', field: 'name', align: 'left', sortable: true },
  { name: 'batch', label: 'Batch', field: 'batch', align: 'left', sortable: true },
  { name: 'parent', label: 'Guardian Details', field: 'parent', align: 'left' }
]

const parentCols = [
  { name: 'name', label: 'Guardian Name', field: 'name', align: 'left', sortable: true },
  { name: 'phone', label: 'Contact', field: 'phone', align: 'left' },
  { name: 'children', label: 'Linked Students', field: 'children', align: 'left' }
]
</script>
