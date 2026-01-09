<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container class="login-container">
      <div ref="vantaRef" class="vanta-bg"></div>

      <div class="row justify-center items-center window-height relative-position z-top">
        <div class="col-11 col-sm-8 col-md-6 col-lg-4">
          <q-card class="glass-panel q-pa-lg text-white" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(10px)">
            <q-card-section class="text-center">
              <q-avatar size="80px" class="q-mb-md shadow-5">
                <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg" />
              </q-avatar>
              <div class="text-h4 text-weight-bolder q-mb-xs">Student Portal</div>
              <div class="text-subtitle2 text-grey-4">Log in to your learning space</div>
            </q-card-section>

            <q-card-section>
              <q-form @submit="onSubmit" class="q-gutter-md">
                <q-input
                  dark
                  filled
                  v-model="barcodeId"
                  label="Student ID / Barcode"
                  hint="Scan your ID card or type ID"
                >
                  <template v-slot:prepend>
                    <q-icon name="qr_code_scanner" />
                  </template>
                </q-input>

                <q-input
                  dark
                  filled
                  v-model="password"
                  label="Password"
                  type="password"
                >
                  <template v-slot:prepend>
                    <q-icon name="lock" />
                  </template>
                </q-input>

                <div>
                  <q-btn label="Start Learning" type="submit" color="primary" class="full-width q-py-sm text-weight-bold shadow-3" unelevated size="lg" />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import * as THREE from 'three'
import DOTS from 'vanta/dist/vanta.dots.min'

const vantaRef = ref(null)
const vantaEffect = ref(null)

const barcodeId = ref('')
const password = ref('')
const router = useRouter()

onMounted(() => {
  vantaEffect.value = DOTS({
    el: vantaRef.value,
    THREE: THREE,
    mouseControls: true,
    touchControls: true,
    gyroControls: false,
    minHeight: 200.00,
    minWidth: 200.00,
    scale: 1.00,
    scaleMobile: 1.00,
    color: 0x6366f1,
    color2: 0xec4899,
    backgroundColor: 0x111827,
    size: 3.00,
    spacing: 35.00
  })
})

onBeforeUnmount(() => {
  if (vantaEffect.value) {
    vantaEffect.value.destroy()
  }
})

const onSubmit = () => {
  if (barcodeId.value === '8821' && password.value === '1234') {
     router.push('/feed')
  } else if (barcodeId.value === 'parent' && password.value === '1234') {
     router.push('/parent')
  } else {
    // Show error (using Quasar Notify would be better, but console for now if $q not setup)
    alert('Invalid Credentials! Try ID: 8821, Pass: 1234')
  }
}
</script>

<style lang="scss" scoped>
.vanta-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}
</style>
