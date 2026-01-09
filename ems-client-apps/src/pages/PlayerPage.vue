<template>
  <q-page class="q-pa-md bg-black window-height overflow-hidden">
    <div class="row full-height q-col-gutter-md">
      <!-- Main Video Area -->
      <div class="col-12 col-md-9 full-height flex column">
        
        <div class="row items-center q-mb-sm">
           <q-btn flat round icon="arrow_back" color="white" @click="$router.back()" />
           <div class="text-h6 text-white text-weight-bold">Physics Mechanics</div>
        </div>

        <div class="col bg-grey-10 rounded-borders overflow-hidden relative-position shadow-24">
           <SecureVideoPlayer
             title="Newton's Second Law"
             :lesson-number="5"
             thumbnail="https://cdn.quasar.dev/img/mountains.jpg"
             video-url="http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4"
             watermark-text="Kasun Perera - EMS ID: 8821"
             session-ip="192.168.1.15"
             class="fit"
           />
        </div>

        <!-- Description / Tabs -->
        <div class="q-mt-md">
          <q-tabs v-model="activeTab" class="text-grey" active-color="primary" align="left" narrow-indicator>
            <q-tab name="overview" label="Overview" />
            <q-tab name="resources" label="Tutes & Resources" />
            <q-tab name="discussion" label="Q&A" />
          </q-tabs>
          
          <q-tab-panels v-model="activeTab" class="bg-transparent text-white animated fadeIn">
             <q-tab-panel name="overview">
               <div class="text-body1">
                 In this lesson, we will explore the fundamental principles of Newton's laws of motion.
               </div>
             </q-tab-panel>
          </q-tab-panels>
        </div>
      </div>

      <!-- Playlist / Chat -->
      <div class="col-12 col-md-3 gt-sm full-height scroll">
         <div class="bg-grey-9 rounded-borders full-height q-pa-md">
           <div class="text-subtitle1 text-white text-weight-bold q-mb-md">Course Content</div>
           
           <q-list dark separator>
             <q-item clickable v-ripple active active-class="bg-primary text-white">
               <q-item-section avatar>
                 <q-icon name="play_circle" />
               </q-item-section>
               <q-item-section>
                 <q-item-label>Day 05 - Newton's Laws</q-item-label>
                 <q-item-label caption class="text-grey-4">2 hr 15 mins</q-item-label>
               </q-item-section>
             </q-item>

             <q-item clickable v-ripple v-for="n in 5" :key="n">
               <q-item-section avatar>
                 <q-icon name="lock" color="grey" />
               </q-item-section>
               <q-item-section>
                 <q-item-label class="text-grey">Day 0{{ n + 5 }} - Upcoming</q-item-label>
                 <q-item-label caption class="text-grey-6">Available on Jan {{ 10 + n }}</q-item-label>
               </q-item-section>
             </q-item>
           </q-list>
         </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import SecureVideoPlayer from 'components/SecureVideoPlayer.vue'

const activeTab = ref('overview')
</script>
