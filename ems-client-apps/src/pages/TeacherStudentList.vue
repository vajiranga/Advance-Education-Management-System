<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold text-teal-9">My Students</div>
      <div class="row q-gutter-x-sm">
         <q-input dense outlined v-model="search" placeholder="Search students..." bg-color="white">
            <template v-slot:prepend><q-icon name="search" /></template>
         </q-input>
         <q-select dense outlined v-model="filterClass" :options="['All Classes', 'Grade 10', 'Grade 11']" bg-color="white" style="min-width:150px" />
      </div>
    </div>

    <q-card class="no-shadow border-light">
       <q-table
          :rows="students"
          :columns="columns"
          row-key="id"
          flat
          :filter="search"
       >
          <template v-slot:body-cell-avatar="props">
             <q-td :props="props">
                <q-avatar size="32px">
                   <img :src="props.row.avatar" />
                </q-avatar>
             </q-td>
          </template>
          <template v-slot:body-cell-status="props">
             <q-td :props="props">
                <q-chip size="sm" :color="props.row.active ? 'green-1' : 'red-1'" :text-color="props.row.active ? 'green' : 'red'">
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
import { ref } from 'vue'

const search = ref('')
const filterClass = ref('All Classes')

const columns = [
  { name: 'avatar', label: '', field: 'avatar', align: 'center' },
  { name: 'name', label: 'Student Name', field: 'name', align: 'left', sortable: true },
  { name: 'grade', label: 'Grade/Batch', field: 'grade', align: 'left', sortable: true },
  { name: 'parent', label: 'Guardian', field: 'parent', align: 'left' },
  { name: 'contact', label: 'Contact', field: 'contact', align: 'left' },
  { name: 'status', label: 'Status', field: 'active', align: 'center' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'center' }
]

const students = ref([
 { id: 1, name: 'Kasun Perera', grade: 'Grade 10 - A', parent: 'Mr. Sarath', contact: '071-2233445', active: true, avatar: 'https://cdn.quasar.dev/img/boy-avatar.png' },
 { id: 2, name: 'Nethmi Silva', grade: 'Grade 10 - A', parent: 'Mrs. Silva', contact: '077-8899001', active: true, avatar: 'https://cdn.quasar.dev/img/avatar2.jpg' },
 { id: 3, name: 'Amila Fernando', grade: 'Grade 11', parent: 'Mr. Fernando', contact: '075-1122334', active: false, avatar: 'https://cdn.quasar.dev/img/avatar4.jpg' },
 // Add more mock data...
])
</script>

<style scoped>
.border-light { border: 1px solid #eee; }
</style>
