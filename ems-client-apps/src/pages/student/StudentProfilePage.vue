<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="text-h6 text-weight-bold q-mb-md">My Profile</div>

    <div class="row q-col-gutter-md">
      <!-- Profile Card -->
      <div class="col-12 col-md-4">
        <q-card class="my-card text-center q-pa-lg">
          <q-avatar size="100px" font-size="50px" color="primary" text-color="white">
            {{ form.name.charAt(0) }}
          </q-avatar>
          <div class="text-h6 q-mt-md">{{ authStore.user?.username }}</div>
          <div class="text-subtitle2 text-grey">{{ form.role.toUpperCase() }}</div>
        </q-card>
      </div>

      <!-- Edit Form -->
      <div class="col-12 col-md-8">
        <q-card class="my-card q-pa-md">
          <q-form @submit="updateProfile" class="q-gutter-md">
            
            <q-input v-model="form.name" label="Full Name" outlined dense />
            <q-input v-model="form.email" label="Email" outlined dense disable hint="Email cannot be changed" />
            
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                 <q-input v-model="form.phone" label="Phone Number" outlined dense />
              </div>
              <div class="col-12 col-md-6">
                 <q-input v-model="form.whatsapp" label="WhatsApp Number" outlined dense />
              </div>
            </div>

            <q-separator class="q-my-md" />
            <div class="text-subtitle2 q-mb-sm">Change Password (Optional)</div>
            
            <q-input v-model="form.password" type="password" label="New Password" outlined dense />
            <q-input v-model="form.password_confirmation" type="password" label="Confirm Password" outlined dense />

            <div class="row justify-end">
              <q-btn label="Update Profile" type="submit" color="primary" :loading="loading" />
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
