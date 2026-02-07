<template>
  <q-page class="flex flex-center bg-gradient-to-br from-orange-100 to-red-100">
    <div class="text-center q-pa-xl">
      <!-- Maintenance Icon -->
      <q-icon name="construction" size="120px" color="orange-8" class="q-mb-lg animate-bounce" />

      <!-- Title -->
      <div class="text-h3 text-weight-bold text-grey-9 q-mb-md">
        System Under Maintenance
      </div>

      <!-- Message -->
      <div class="text-h6 text-grey-7 q-mb-xl" style="max-width: 600px; margin: 0 auto;">
        We're currently performing scheduled maintenance to improve your experience.
        <br />
        Please check back soon!
      </div>

      <!-- Animated Progress -->
      <q-linear-progress
        indeterminate
        color="orange"
        class="q-mb-lg"
        style="max-width: 400px; margin: 0 auto;"
      />

      <!-- Retry Button -->
      <q-btn
        color="primary"
        label="Retry Login"
        icon="refresh"
        size="lg"
        @click="retryLogin"
        class="q-mt-md"
      />

      <!-- Contact Info -->
      <div class="text-caption text-grey-6 q-mt-xl">
        If you need immediate assistance, please contact your institute.
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const retryLogin = () => {
  // Clear any existing tokens and redirect to login
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}

.animate-bounce {
  animation: bounce 2s infinite;
}

.bg-gradient-to-br {
  background: linear-gradient(to bottom right, #fff7ed, #fee2e2);
}

.from-orange-100 {
  --tw-gradient-from: #fff7ed;
}

.to-red-100 {
  --tw-gradient-to: #fee2e2;
}
</style>
