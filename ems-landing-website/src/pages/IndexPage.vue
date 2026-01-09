<template>
  <q-page>
    <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center bg-dark text-white relative-position">
      <div ref="vantaRef" class="absolute-full" style="z-index: 0; opacity: 0.6"></div>
      
      <div class="container text-center relative-position z-top q-pa-md">
        <div class="text-h2 text-weight-bolder q-mb-md leading-tight" data-aos="fade-up">
          The Future of <span class="gradient-text">Education Management</span>
        </div>
        <div class="text-h5 text-grey-4 q-mb-xl" style="max-width: 700px; margin: 0 auto" data-aos="fade-up" data-aos-delay="100">
          Seamlessly manage students, conduct online exams, and monetize your courses with our all-in-one platform.
        </div>
        <div class="row justify-center q-gutter-md" data-aos="fade-up" data-aos-delay="200">
          <q-btn color="secondary" size="xl" label="Book a Demo" no-caps icon-right="arrow_forward" class="shadow-10" />
          <q-btn outline color="white" size="xl" label="View Pricing" no-caps />
        </div>
      </div>
    </section>

    <!-- Features Grid -->
    <section class="q-py-xl bg-white">
      <div class="container">
        <div class="text-center q-mb-xl">
          <div class="text-overline text-secondary">Why Choose Us?</div>
          <div class="text-h3 text-weight-bold text-primary">Everything you need to run your institute</div>
        </div>

        <div class="row q-col-gutter-lg">
          <div class="col-12 col-md-4" v-for="(feature, i) in features" :key="i" data-aos="fade-up" :data-aos-delay="i * 100">
            <q-card class="feature-card no-shadow bg-grey-1 bordered h-full">
              <q-card-section class="q-pa-lg">
                <q-icon :name="feature.icon" size="50px" color="secondary" class="q-mb-md" />
                <div class="text-h5 text-weight-bold q-mb-sm">{{ feature.title }}</div>
                <div class="text-grey-7">{{ feature.description }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats / Trust -->
    <section class="q-py-xl bg-slate-900 text-white">
      <div class="container">
        <div class="row justify-center text-center q-col-gutter-xl">
          <div class="col-6 col-md-3">
            <div class="text-h2 text-weight-bold text-accent">500+</div>
            <div class="text-subtitle1">Institutes</div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-h2 text-weight-bold text-secondary">1M+</div>
            <div class="text-subtitle1">Students</div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-h2 text-weight-bold text-green-4">99.9%</div>
            <div class="text-subtitle1">Uptime</div>
          </div>
        </div>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import * as THREE from 'three'
import GLOBE from 'vanta/dist/vanta.globe.min'
import AOS from 'aos'
import 'aos/dist/aos.css'

const vantaRef = ref(null)
const vantaEffect = ref(null)

const features = [
  { title: 'LMS & Exams', description: 'Upload videos with DRM protection and conduct MCQ exams with auto-marking.', icon: 'school' },
  { title: 'Smart Attendance', description: 'QR Code, NFC, and Face Recognition driven attendance marking.', icon: 'qr_code_scanner' },
  { title: 'Payment Gateway', description: 'Automated slip verification and seamless online payments.', icon: 'payments' }
]

onMounted(() => {
  AOS.init()
  
  try {
    vantaEffect.value = GLOBE({
      el: vantaRef.value,
      THREE: THREE,
      mouseControls: true,
      touchControls: true,
      gyroControls: false,
      minHeight: 200.00,
      minWidth: 200.00,
      scale: 1.00,
      scaleMobile: 1.00,
      color: 0x3b82f6,
      backgroundColor: 0x0f172a
    })
  } catch (e) {
    console.warn("Vanta WebGL Error", e)
  }
})

onBeforeUnmount(() => {
  if (vantaEffect.value) {
    vantaEffect.value.destroy()
  }
})
</script>

<style lang="scss" scoped>
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.bg-slate-900 {
  background-color: #0F172A;
}
</style>
