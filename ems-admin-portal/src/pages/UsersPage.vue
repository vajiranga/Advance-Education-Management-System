<template>
  <q-page class="q-pa-md">
    <div class="text-h4 q-mb-md">User Management</div>
    
    <div class="row q-col-gutter-md">
      <div class="col-12">
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

          <q-tab-panels v-model="tab" class="q-pa-md">
            <!-- Teachers Panel -->
            <q-tab-panel name="teachers">
              <q-table
                :rows="teachers"
                :columns="teacherCols"
                row-key="id"
                :filter="filter"
              >
                <template v-slot:top-right>
                  <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
                    <template v-slot:append>
                      <q-icon name="search" />
                    </template>
                  </q-input>
                  <q-btn color="primary" label="Add New User" icon="add" class="q-ml-md" @click="openAddUserDialog('teacher')" />
                </template>
                <template v-slot:body-cell-plain_password="props">
                  <q-td :props="props">
                    <div class="row items-center no-wrap">
                      <span v-if="visiblePasswords[props.row.id]">{{ props.row.plain_password }}</span>
                      <span v-else>••••••••</span>
                      <q-btn flat round dense icon="visibility" size="sm" class="q-ml-sm" @click="togglePassword(props.row.id)" />
                    </div>
                  </q-td>
                </template>
                <template v-slot:body-cell-actions="props">
                  <q-td :props="props">
                    <q-btn flat round dense color="primary" icon="edit" @click="editUser(props.row)" />
                    <q-btn flat round dense color="negative" icon="delete" @click="deleteUser(props.row)" />
                  </q-td>
                </template>
              </q-table>
            </q-tab-panel>

            <!-- Students Panel -->
            <q-tab-panel name="students">
               <q-table
                :rows="students"
                :columns="studentCols"
                row-key="id"
                :filter="filter"
                selection="multiple"
                v-model:selected="selectedStudents"
              >
                <template v-slot:top-right>
                  <div class="row q-gutter-sm">
                      <q-btn v-if="selectedStudents.length > 0" 
                            color="secondary" 
                            icon="class" 
                            :label="`Add ${selectedStudents.length} to Class`" 
                            @click="openBulkEnrollDialog" 
                      />
                      
                      <q-input borderless dense debounce="300" v-model="filter" placeholder="Search by Name or Barcode">
                        <template v-slot:append>
                          <q-icon name="search" />
                        </template>
                      </q-input>
                      <q-btn color="primary" label="Add New User" icon="add" @click="openAddUserDialog('student')" />
                  </div>
                </template>
                <template v-slot:body-cell-plain_password="props">
                  <q-td :props="props">
                     <div class="row items-center no-wrap">
                      <span v-if="visiblePasswords[props.row.id]">{{ props.row.plain_password }}</span>
                      <span v-else>••••••••</span>
                      <q-btn flat round dense icon="visibility" size="sm" class="q-ml-sm" @click="togglePassword(props.row.id)" />
                    </div>
                  </q-td>
                </template>
                <template v-slot:body-cell-guardian="props">
                  <q-td :props="props">
                    <div v-if="props.row.parent_name">
                      <div class="text-weight-bold">{{ props.row.parent_name }}</div>
                      <div class="text-caption">{{ props.row.parent_phone }}</div>
                    </div>
                    <div v-else class="text-grey-5">-</div>
                  </q-td>
                </template>
                <template v-slot:body-cell-actions="props">
                  <q-td :props="props">
                    <q-btn flat round dense color="primary" icon="edit" @click="editUser(props.row)" />
                    <q-btn flat round dense color="negative" icon="delete" @click="deleteUser(props.row)" />
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
                :filter="filter"
              >
                <template v-slot:top-right>
                  <q-input borderless dense debounce="300" v-model="filter" placeholder="Search">
                    <template v-slot:append>
                      <q-icon name="search" />
                    </template>
                  </q-input>
                  <q-btn color="primary" label="Add New User" icon="add" class="q-ml-md" @click="openAddUserDialog('parent')" />
                </template>
                <template v-slot:body-cell-plain_password="props">
                  <q-td :props="props">
                     <div class="row items-center no-wrap">
                      <span v-if="visiblePasswords[props.row.id]">{{ props.row.plain_password }}</span>
                      <span v-else>••••••••</span>
                      <q-btn flat round dense icon="visibility" size="sm" class="q-ml-sm" @click="togglePassword(props.row.id)" />
                    </div>
                  </q-td>
                </template>
                <template v-slot:body-cell-actions="props">
                  <q-td :props="props">
                    <q-btn flat round dense color="primary" icon="edit" @click="editUser(props.row)" />
                    <q-btn flat round dense color="negative" icon="delete" @click="deleteUser(props.row)" />
                  </q-td>
                </template>
              </q-table>
            </q-tab-panel>
          </q-tab-panels>
        </q-card>
      </div>
    </div>

    <!-- Bulk Enroll Dialog -->
    <q-dialog v-model="bulkEnrollDialog">
        <q-card style="min-width: 400px">
            <q-card-section>
                <div class="text-h6">Add {{ selectedStudents.length }} Students to Class</div>
            </q-card-section>
            
            <q-card-section>
                 <q-select
                    outlined
                    v-model="selectedClass"
                    :options="courseStore.courses"
                    option-label="name"
                    label="Select Class"
                    :loading="courseStore.loading"
                 >
                    <template v-slot:option="scope">
                        <q-item v-bind="scope.itemProps">
                            <q-item-section>
                                <q-item-label>{{ scope.opt.name }}</q-item-label>
                                <q-item-label caption v-if="scope.opt.teacher">{{ scope.opt.teacher.name }}</q-item-label>
                            </q-item-section>
                        </q-item>
                    </template>
                 </q-select>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="Cancel" v-close-popup />
                <q-btn color="primary" label="Enroll All" @click="submitBulkEnroll" :loading="enrolling" />
            </q-card-actions>
        </q-card>
    </q-dialog>

    <!-- Add/Edit User Dialog -->
    <q-dialog v-model="addUserDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">{{ isEditMode ? 'Edit User' : 'Add New ' + selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1) }}</div>
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pt-none q-mt-md scroll" style="max-height: 70vh">
          <q-form @submit="saveUser" class="q-gutter-md">
            
            <q-input outlined v-model="form.name" label="Full Name *" :rules="[val => !!val || 'Required']" />
            
            <q-input outlined v-model="form.email" label="Email" :rules="[val => !val || /.+@.+\..+/.test(val) || 'Invalid Email']">
               <template v-slot:hint >Only required for Teachers. Recommended for others.</template>
            </q-input>
            
            <div class="row q-col-gutter-sm">
                <div class="col-6">
                    <q-input outlined v-model="form.phone" label="Phone Number" mask="###-#######" hint="Format: 07x-xxxxxxx" />
                </div>
                <div class="col-6">
                    <q-input outlined v-model="form.whatsapp" label="WhatsApp Number" mask="###-#######" />
                </div>
            </div>

             <div class="row q-col-gutter-sm">
                <div class="col-6">
                    <q-select outlined v-model="form.gender" :options="['Male', 'Female']" label="Gender" />
                </div>
                <div class="col-6">
                    <q-input outlined v-model="form.dob" type="date" label="Birthday" stack-label />
                </div>
            </div>

             <q-input outlined v-model="form.password" :label="isEditMode ? 'New Password (Leave blank to keep)' : 'Password *'" type="password" :rules="isEditMode ? [] : [val => !!val || 'Required', val => val.length >= 8 || 'Min 8 chars']" />

            <!-- Student Specific Fields -->
            <div v-if="selectedRole === 'student'">
                <q-separator class="q-my-sm" />
                <div class="text-subtitle2 q-mb-sm">Academic Details</div>
                <div class="row q-col-gutter-sm">
                    <div class="col-6">
                         <q-select outlined v-model="form.grade" :options="['Grade 6','Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','2025 A/L','2026 A/L']" label="Grade/Batch" />
                    </div>
                    <div class="col-6">
                        <q-input outlined v-model="form.school" label="School" />
                    </div>
                </div>
                
                <q-separator class="q-my-sm" />
                <div class="text-subtitle2 q-mb-sm">Parent/Guardian Details</div>
                 <q-input outlined v-model="form.parent_name" label="Parent Name" />
                 <div class="row q-col-gutter-sm">
                    <div class="col-6">
                        <q-input outlined v-model="form.parent_phone" label="Parent Phone" mask="###-#######" />
                    </div>
                    <div class="col-6">
                        <q-input outlined v-model="form.parent_email" label="Parent Email" />
                    </div>
                 </div>
            </div>

            <!-- Teacher Specific Fields -->
            <div v-if="selectedRole === 'teacher'">
                 <q-separator class="q-my-sm" />
                 <q-input outlined v-model="form.nic" label="NIC Number" />
                 <q-input outlined v-model="form.experience" label="Teaching Experience" />
            </div>

            <div class="row justify-end q-mt-md">
              <q-btn label="Cancel" color="negative" flat v-close-popup />
              <q-btn :label="isEditMode ? 'Update' : 'Save User'" type="submit" color="primary" :loading="loading" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { api } from 'boot/axios'
import { useUserStore } from 'stores/user-store'
import { useCourseStore } from 'stores/course-store' // Import Course Store
import { useQuasar } from 'quasar'

const $q = useQuasar()
const userStore = useUserStore()
const courseStore = useCourseStore() // Init Store

const tab = ref('students')
const filter = ref('')
const visiblePasswords = ref({})
const loading = ref(false)

const teachers = computed(() => userStore.teachers)
const students = computed(() => userStore.students)
const parents = computed(() => userStore.parents)

// Selection State
const selectedStudents = ref([])

// Bulk Enroll Dialog State
const bulkEnrollDialog = ref(false)
const selectedClass = ref(null)
const enrolling = ref(false)

function openBulkEnrollDialog() {
    if (selectedStudents.value.length === 0) return
    courseStore.fetchCourses() // Ensure courses are loaded
    selectedClass.value = null
    bulkEnrollDialog.value = true
}

async function submitBulkEnroll() {
    if (!selectedClass.value) {
        $q.notify({ type: 'warning', message: 'Please select a class' })
        return
    }
    
    enrolling.value = true
    try {
        const studentIds = selectedStudents.value.map(s => s.id)
        await courseStore.bulkEnroll(selectedClass.value.id, studentIds)
        
        $q.notify({ type: 'positive', message: `Successfully enrolled ${studentIds.length} students` })
        bulkEnrollDialog.value = false
        selectedStudents.value = [] // Clear selection
    } catch (e) {
        console.error(e)
        $q.notify({ type: 'negative', message: 'Failed to enroll some or all students' })
    } finally {
        enrolling.value = false
    }
}

// Columns Definitions
const commonCols = [
  { name: 'name', label: 'Name', align: 'left', field: 'name', sortable: true },
  { name: 'plain_password', label: 'Password', align: 'left', field: 'plain_password' }
]

const teacherCols = [
  { name: 'username', label: 'Teacher ID', align: 'left', field: 'username', sortable: true },
  ...commonCols,
  { name: 'email', label: 'Email', align: 'left', field: 'email' },
  { name: 'phone', label: 'Phone', align: 'left', field: 'phone' },
  { name: 'nic', label: 'NIC', align: 'left', field: 'nic' },
  { name: 'experience', label: 'Experience', align: 'left', field: 'experience' },
  { name: 'actions', label: 'Actions', align: 'right' }
]

const studentCols = [
  { name: 'username', label: 'Student ID', align: 'left', field: 'username', sortable: true },
  { name: 'name', label: 'Student Name', align: 'left', field: 'name', sortable: true },
  { name: 'dob', label: 'Birthday', align: 'left', field: 'dob' },
  { name: 'gender', label: 'Gender', align: 'left', field: 'gender' },
  { name: 'phone', label: 'Phone', align: 'left', field: 'phone' },
  { name: 'whatsapp', label: 'WhatsApp', align: 'left', field: 'whatsapp' },
  { name: 'email', label: 'Email', align: 'left', field: 'email' },
  { name: 'grade', label: 'Grade/Batch', align: 'left', field: 'grade' },
  { name: 'school', label: 'School', align: 'left', field: 'school' },
  { name: 'guardian', label: 'Guardian Details', align: 'left', field: 'parent_name' },
  { name: 'plain_password', label: 'Password', align: 'left', field: 'plain_password' },
  { name: 'actions', label: 'Actions', align: 'right' }
]

const parentCols = [
  { name: 'username', label: 'Parent ID', align: 'left', field: 'username', sortable: true },
  ...commonCols,
  { name: 'email', label: 'Email', align: 'left', field: 'email' },
  { name: 'whatsapp', label: 'WhatsApp', align: 'left', field: 'whatsapp' },
  { name: 'phone', label: 'Phone', align: 'left', field: 'phone' },
  { name: 'actions', label: 'Actions', align: 'right' }
]

onMounted(async () => {
  await userStore.fetchTeachers()
  await userStore.fetchStudents()
  await userStore.fetchParents()
})

function togglePassword(id) {
    visiblePasswords.value[id] = !visiblePasswords.value[id]
}

// Add/Edit Logic
const addUserDialog = ref(false)
const isEditMode = ref(false)
const selectedRole = ref('student')
const editingId = ref(null)

const form = ref({
    name: '',
    email: '',
    phone: '',
    whatsapp: '',
    gender: '',
    dob: '',
    password: '',
    grade: '',
    school: '',
    parent_name: '',
    parent_phone: '',
    parent_email: '',
    nic: '',
    experience: ''
})

function resetForm() {
    form.value = {
        name: '', email: '', phone: '', whatsapp: '', gender: '', dob: '', password: '',
        grade: '', school: '', parent_name: '', parent_phone: '', parent_email: '', nic: '', experience: ''
    }
}

function openAddUserDialog(role) {
    isEditMode.value = false
    selectedRole.value = role
    resetForm()
    addUserDialog.value = true
}

function editUser(row) {
    isEditMode.value = true
    selectedRole.value = row.role
    editingId.value = row.id
    
    // Fill form
    form.value = { ...row, password: '' } // Clear password field for update, but keep other data
    addUserDialog.value = true
}

async function saveUser() {
    loading.value = true
    try {
        const payload = { ...form.value, role: selectedRole.value }
        // Clean empty password if edit mode
        if (isEditMode.value && !payload.password) delete payload.password

        if (isEditMode.value) {
            await api.put(`/v1/users/${editingId.value}`, payload)
            $q.notify({ type: 'positive', message: 'User updated successfully' })
        } else {
            await api.post('/v1/users', payload)
            $q.notify({ type: 'positive', message: 'User created successfully' })
        }
        
        addUserDialog.value = false
        // Refresh store
        await userStore.fetchTeachers()
        await userStore.fetchStudents()
        await userStore.fetchParents()
    } catch (error) {
        console.error(error)
        $q.notify({ type: 'negative', message: error.response?.data?.message || 'Operation failed' })
    } finally {
        loading.value = false
    }
}

function deleteUser(row) {
    $q.dialog({
        title: 'Confirm',
        message: 'Are you sure you want to delete this user?',
        cancel: true,
        persistent: true
    }).onOk(async () => {
        try {
            await api.delete(`/v1/users/${row.id}`)
            $q.notify({ type: 'positive', message: 'User deleted' })
            await userStore.fetchTeachers()
            await userStore.fetchStudents()
            await userStore.fetchParents()
        } catch (error) {
            console.error(error)
            $q.notify({ type: 'negative', message: 'Delete failed' })
        }
    })
}
</script>
