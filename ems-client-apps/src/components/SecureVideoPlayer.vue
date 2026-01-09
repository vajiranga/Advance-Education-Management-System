<template>
  <q-card class="column full-height bg-black text-white overflow-hidden">
    <!-- Video Player Container -->
    <div class="col relative-position bg-grey-10 flex flex-center video-wrapper">
      <div v-if="!isPlaying" class="absolute-full flex flex-center cursor-pointer" @click="startVideo">
        <q-img :src="thumbnail" class="absolute-full opacity-50" fit="cover" />
        <q-btn round color="primary" icon="play_arrow" size="xl" class="z-top shadow-10 pulse-anim" />
      </div>
      
      <video
        v-else
        ref="videoRef"
        class="full-width full-height"
        controls
        autoplay
        controlsList="nodownload"
        @contextmenu.prevent
      >
        <source :src="videoUrl" type="video/mp4" />
        Your browser does not support the video tag.
      </video>

      <!-- Watermark Overlay (Anti-Piracy) -->
      <div class="absolute-top-right q-ma-md text-white opacity-30 text-caption select-none pointer-events-none" 
           style="z-index: 50; text-shadow: 1px 1px 2px black;">
        {{ watermarkText }}
        <div class="text-xs">{{ sessionIp }}</div>
      </div>
      
      <!-- Dynamic Floating Watermark -->
      <div class="floating-watermark text-grey-6 text-caption select-none" :style="watermarkStyle">
        {{ watermarkText }}
      </div>
    </div>

    <!-- Video Controls & Info -->
    <q-card-section class="bg-grey-9 q-py-sm">
      <div class="row items-center justify-between">
        <div>
          <div class="text-subtitle1 text-weight-bold">{{ title }}</div>
          <div class="text-caption text-grey-4">Lesson {{ lessonNumber }}</div>
        </div>
        <div class="row q-gutter-sm">
          <q-btn flat round icon="skip_previous" color="white" />
          <q-btn flat round :icon="isPlaying ? 'pause' : 'play_arrow'" color="white" @click="togglePlay" />
          <q-btn flat round icon="skip_next" color="white" />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  title: String,
  lessonNumber: Number,
  thumbnail: String,
  videoUrl: String,
  watermarkText: String,
  sessionIp: String
})

const videoRef = ref(null)
const isPlaying = ref(false)
const watermarkPos = ref({ x: 10, y: 10 })
let watermarkInterval = null

const startVideo = () => {
  isPlaying.value = true
}

const togglePlay = () => {
  if (!videoRef.value) return
  if (videoRef.value.paused) {
    videoRef.value.play()
    isPlaying.value = true
  } else {
    videoRef.value.pause()
    isPlaying.value = false
  }
}

// Randomly move watermark
const moveWatermark = () => {
  watermarkPos.value = {
    x: Math.random() * 80 + 10, // 10% to 90%
    y: Math.random() * 80 + 10
  }
}

const watermarkStyle = computed(() => ({
  position: 'absolute',
  left: `${watermarkPos.value.x}%`,
  top: `${watermarkPos.value.y}%`,
  opacity: 0.2, // Low opacity check
  pointerEvents: 'none',
  zIndex: 100
}))

onMounted(() => {
  watermarkInterval = setInterval(moveWatermark, 5000)
  moveWatermark()
})

onUnmounted(() => {
  clearInterval(watermarkInterval)
})
</script>

<style lang="scss" scoped>
.opacity-50 { opacity: 0.5; }
.opacity-30 { opacity: 0.3; }
.select-none { user-select: none; }
.pointer-events-none { pointer-events: none; }

.pulse-anim {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
  70% { box-shadow: 0 0 0 20px rgba(99, 102, 241, 0); }
  100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
}

.floating-watermark {
  transition: all 2s ease-in-out;
}
</style>
