<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : 'text-primary'">My Profile</div>
    </div>

    <div class="row q-col-gutter-lg">
      <!-- Profile Card -->
      <div class="col-12 col-md-4">
        <q-card class="my-card text-center q-pa-lg full-height" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
          <q-avatar size="120px" font-size="60px" :color="$q.dark.isActive ? 'deep-purple-9' : 'primary'" text-color="white" class="shadow-5">
            {{ form.name.charAt(0) }}
          </q-avatar>
          <div class="text-h5 q-mt-lg text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ authStore.user?.username }}</div>
          <div class="text-subtitle1" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ form.role.toUpperCase() }}</div>
          
          <q-separator class="q-my-lg" :class="$q.dark.isActive ? 'bg-grey-8' : ''" />
          
          <div class="text-left q-gutter-y-sm">
             <div class="row items-center">
                <q-icon name="email" size="sm" class="q-mr-sm" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'"/>
                <span :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ form.email }}</span>
             </div>
             <div class="row items-center">
                <q-icon name="phone" size="sm" class="q-mr-sm" :class="$q.dark.isActive ? 'text-grey-5' : 'text-grey-7'"/>
                <span :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-8'">{{ form.phone || 'No Phone' }}</span>
             </div>
          </div>
        </q-card>
      </div>

      <!-- Edit Form -->
      <div class="col-12 col-md-8">
        <q-card class="my-card q-pa-lg" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
          <q-form @submit="updateProfile" class="q-gutter-y-md">
            
            <!-- Personal Info -->
            <div class="text-h6" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-primary'">Personal Information</div>
            
            <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-input 
                        v-model="form.name" 
                        label="Full Name" 
                        outlined 
                        dense 
                        :dark="$q.dark.isActive" 
                        :bg-color="$q.dark.isActive ? 'grey-9' : ''"
                        :input-class="$q.dark.isActive ? 'text-white' : ''"
                    >
                      <template v-slot:prepend><q-icon name="person" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input 
                        v-model="form.email" 
                        label="Email Address" 
                        outlined 
                        dense 
                        disable 
                        hint="Email cannot be changed" 
                        :dark="$q.dark.isActive" 
                        :bg-color="$q.dark.isActive ? 'grey-9' : ''"
                    >
                      <template v-slot:prepend><q-icon name="email" /></template>
                    </q-input>
                </div>
            </div>

            <!-- Contact Info -->
            <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" class="q-my-md"/>
            <div class="text-h6" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-primary'">Contact Details</div>

            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                 <q-input 
                    v-model="form.phone" 
                    label="Phone Number" 
                    outlined 
                    dense 
                    :dark="$q.dark.isActive" 
                    :bg-color="$q.dark.isActive ? 'grey-9' : ''"
                >
                   <template v-slot:prepend><q-icon name="phone" /></template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                 <q-input 
                    v-model="form.whatsapp" 
                    label="WhatsApp Number" 
                    outlined 
                    dense 
                    :dark="$q.dark.isActive" 
                    :bg-color="$q.dark.isActive ? 'grey-9' : ''"
                >
                   <template v-slot:prepend><q-icon name="chat" /></template>
                </q-input>
              </div>
            </div>

            <!-- Security -->
            <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" class="q-my-md"/>
            <div class="text-h6" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-primary'">Security</div>
            
            <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-input 
                        v-model="form.password" 
                        type="password" 
                        label="New Password" 
                        outlined 
                        dense 
                        :dark="$q.dark.isActive" 
                        :bg-color="$q.dark.isActive ? 'grey-9' : ''"
                    >
                       <template v-slot:prepend><q-icon name="lock" /></template>
                    </q-input>
                </div>
                <div class="col-12 col-md-6">
                    <q-input 
                        v-model="form.password_confirmation" 
                        type="password" 
                        label="Confirm Password" 
                        outlined 
                        dense 
                        :dark="$q.dark.isActive" 
                        :bg-color="$q.dark.isActive ? 'grey-9' : ''"
                    >
                       <template v-slot:prepend><q-icon name="lock_clock" /></template>
                    </q-input>
                </div>
            </div>

            <!-- Actions -->
            <div class="row justify-end q-mt-lg">
              <q-btn label="Update Profile" icon="save" type="submit" :color="$q.dark.isActive ? 'deep-purple-6' : 'primary'" :loading="loading" class="q-px-lg" />
            </div>

          </q-form>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from 'stores/auth-store'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const authStore = useAuthStore()
const loading = ref(false)

const form = ref({
  name: '',
  email: '',
  phone: '',
  whatsapp: '',
  role: 'student', // default
  password: '',
  password_confirmation: ''
})

onMounted(() => {
  if (authStore.user) {
    form.value.name = authStore.user.name || ''
    form.value.email = authStore.user.email || ''
    form.value.phone = authStore.user.phone || ''
    form.value.whatsapp = authStore.user.whatsapp || ''
    form.value.role = authStore.user.role || 'student'
  }
})

const updateProfile = async () => {
  loading.value = true
  try {
    const payload = {
        name: form.value.name,
        phone: form.value.phone,
        whatsapp: form.value.whatsapp,
        ...(form.value.password ? { password: form.value.password, password_confirmation: form.value.password_confirmation } : {})
    }

    const res = await api.put('/user/profile', payload)
    
    // Update store
    authStore.updateUserInAccount(res.data.user)

    $q.notify({ type: 'positive', message: 'Profile Updated Successfully' })
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (error) {
    console.error(error)
    $q.notify({ type: 'negative', message: error.response?.data?.message || 'Update Failed' })
  } finally {
    loading.value = false
  }
}
</script>
