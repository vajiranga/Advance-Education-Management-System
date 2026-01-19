<template>
  <q-page class="landing-page bg-dark text-white overflow-hidden">
    
    <!-- Hero Section -->
    <section class="hero relative-position flex flex-center overflow-hidden">
      <!-- Three.js Background -->
      <div ref="heroContainer" class="absolute-full" style="z-index: 0;"></div>

      <div class="container relative-position z-10 text-center q-px-md">
        <q-chip color="primary" text-color="white" class="glass-chip q-mb-lg shine-effect" icon="verified">
           Since 1979 - The First & The Best
        </q-chip>
        
        <h1 class="hero-title text-weight-bolder q-mb-md leading-tight">
          අනාගතය ගොඩදාගන්න <br>
          <span class="text-gradient">සුපිරිම තැන</span>
        </h1>
        
        <div class="q-mb-xl relative-position inline-block">
           <h2 class="text-h2 text-weight-bolder q-my-none highlight-text leading-tight" style="font-size: 4rem;">
              ගිරිඋල්ල <br class="lt-md"> <span class="text-gradient-gold bounce-text">විද්‍යානන්ද විද්‍යාපීඨය</span>
           </h2>
        </div>

        <p class="hero-subtitle text-grey-4 q-mb-xl q-mx-auto">
          ඔයාගේ අධ්‍යාපනය ඊළඟ මට්ටමට ගෙනියන්න අපි සූදානම්. <br> අදම අපත් සමඟ එකතු වන්න!
        </p>
        
        <div class="row justify-center q-gutter-md">
          <q-btn 
            to="/register" 
            unelevated
            rounded
            color="primary" 
            size="lg" 
            label="Register Now" 
            no-caps 
            icon-right="arrow_forward"
            class="cta-btn shadow-20 glow-btn-pulse" 
          />
          <q-btn 
            to="/login"
            outline
            rounded
            color="white" 
            size="lg" 
            label="Login" 
            no-caps 
            class="cta-btn border-white-50 hover-glass" 
          />
        </div>

        <!-- Hero Graphic (Dashboard) -->
        <div class="q-mt-xl relative-position flex flex-center">
           <!-- Glow Background -->
           <div class="absolute-center bg-primary" style="width: 60%; height: 60%; filter: blur(80px); opacity: 0.2; z-index: 0;"></div>

           <!-- Main Image Container -->
           <div class="hero-graphic relative-position z-10 floating-container">
              <q-img 
                 src="/images/dashboard-ui.png" 
                 class="rounded-borders shadow-24 border-glass"
                 style="max-width: 900px; width: 100%; border-radius: 24px;"
              >
                  <!-- Overlay Badges (Inside Image Container to prevent overflow) -->
                  <div class="absolute-top-left q-ma-md">
                     <div class="glass-chip row items-center q-pa-sm rounded-borders bg-dark text-white shadow-10" style="backdrop-filter: blur(10px);">
                        <q-icon name="verified" color="blue-4" size="sm" class="q-mr-sm" />
                        <div class="text-weight-bold letter-spacing-1">VIDYANANDA PORTAL</div>
                     </div>
                  </div>

                  <div class="absolute-bottom-right q-ma-lg transform-translate">
                     <q-card class="bg-white text-dark q-pa-sm shadow-10 rounded-borders row items-center no-wrap" style="min-width: 160px">
                        <q-circular-progress
                          show-value
                          class="q-mr-sm"
                          :value="98"
                          size="40px"
                          :thickness="0.25"
                          color="green-5"
                          track-color="grey-3"
                        >
                          <span class="text-caption text-weight-bold">98%</span>
                        </q-circular-progress>
                        <div class="column">
                           <span class="text-caption text-grey-7 text-weight-bold">Performance</span>
                           <span class="text-xs text-green-7 text-weight-bolder">Excellent</span>
                        </div>
                     </q-card>
                  </div>
              </q-img>
           </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us -->
    <section class="q-py-xl relative-position z-10">
       <div class="container">
          <div class="text-center q-mb-xl">
             <div class="text-overline text-primary q-mb-sm tracking-widest">WHY CHOOSE US</div>
             <h2 class="text-h3 text-weight-bold q-mb-md">ඇයි අපිව තෝරාගන්නේ?</h2>
             <p class="text-grey-5 font-size-18">ක්ලාස් එකට ගිහින් නිකන් ඉන්න නෙමෙයි, වැඩක් කරලා ගොඩ යන්න වරෙන්.</p>
          </div>
          
          <div class="row q-col-gutter-lg">
             <div class="col-12 col-md-4" v-for="(feature, i) in features" :key="i">
                <q-card class="feature-card glass-panel-light full-height text-center hover-lift">
                   <q-card-section class="q-pa-xl">
                      <div class="icon-circle q-mb-lg q-mx-auto shadow-10" :class="`text-${feature.color} bg-${feature.color}-dim`">
                         <q-icon :name="feature.icon" size="32px" />
                      </div>
                      <div class="text-h5 text-weight-bold q-mb-sm">{{ feature.title }}</div>
                      <p class="text-grey-4">{{ feature.copy }}</p>
                   </q-card-section>
                </q-card>
             </div>
          </div>
       </div>
    </section>

    <!-- Classes Section (Based on Brochure) -->
    <section id="classes" class="q-py-xl bg-darker relative-position">
       <div class="container relative-position z-10">
           <div class="row items-center justify-between q-mb-xl">
              <div>
                 <h2 class="text-h3 text-weight-bold">Expert Lecture Panel</h2>
                 <p class="text-grey-5 text-h6">Shaping Futures from Grade 1 to 13</p>
              </div>
           </div>

           <div class="row q-col-gutter-lg">
              <div class="col-12 col-md-4 col-lg-3" v-for="subject in subjects" :key="subject.id">
                 <q-card class="subject-card glass-panel hover-border-gold transition-all duration-300 relative-position" style="min-height: 320px; display: flex; flex-direction: column;">
                    <div class="absolute-full bg-gradient-blue-vertical opacity-10"></div>
                    
                    <!-- Header -->
                    <div class="q-py-lg full-width text-center relative-position z-10" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); background: rgba(0,0,0,0.2);">
                        <div class="text-h4 text-weight-bolder text-gradient-gold letter-spacing-wide">{{ subject.name }}</div>
                    </div>
                    
                    <!-- Content -->
                    <q-card-section class="q-pa-lg relative-position z-10 full-width flex flex-center column col-grow">
                       <div class="text-subtitle1 text-grey-4 full-width row justify-center">
                          <div class="column q-gutter-y-md text-left" style="min-width: 160px;"> 
                              <div v-for="teacher in subject.teachers" :key="teacher" class="row items-center no-wrap teacher-row q-py-xs">
                                 <q-icon name="stars" size="18px" class="q-mr-md text-grey-7 transition-colors" />
                                 <span class="text-weight-medium text-body1">{{ teacher }}</span>
                              </div>
                          </div>
                       </div>
                    </q-card-section>
                 </q-card>
              </div>
           </div>
       </div>
    </section>

    <!-- Life at Vidyananda (Gallery) -->
    <section id="gallery" class="q-py-xl relative-position overflow-hidden">
       <div class="container text-center relative-position z-10">
           <h2 class="text-h3 text-weight-bold q-mb-lg">විද්‍යානන්දේ අපි</h2>
           <p class="text-grey-5 q-mb-xl text-h6">ඉගෙනීම විතරක් නෙමෙයි, ජීවිතයත් දිනන තැන.</p>

           <q-carousel
             animated
             v-model="slide"
             arrows
             navigation
             infinite
             :autoplay="2500"
             transition-prev="slide-right"
             transition-next="slide-left"
             height="500px"
             class="rounded-borders shadow-20 glass-panel container-fluid"
           >
             <q-carousel-slide :name="1" img-src="/images/sports-meet.png">
                <div class="absolute-bottom custom-caption">
                   <div class="text-h2">වාර්ෂික ක්‍රීඩා උත්සවය</div>
                   <div class="text-subtitle1">ජයග්‍රහණයේ සොඳුරු මතකයන්</div>
                </div>
             </q-carousel-slide>
             <q-carousel-slide :name="2" img-src="/images/prefects-day.png">
                <div class="absolute-bottom custom-caption">
                   <div class="text-h2">සිසු නායක දිනය</div>
                   <div class="text-subtitle1">නායකත්වයේ ඇරඹුම</div>
                </div>
             </q-carousel-slide>
             <q-carousel-slide :name="3" img-src="/images/environment-day.png">
                <div class="absolute-bottom custom-caption">
                   <div class="text-h2">පරිසර දින සැමරුම</div>
                   <div class="text-subtitle1">අපේ පරිසරය, අපේ අනාගතයයි</div>
                </div>
             </q-carousel-slide>
           </q-carousel>
       </div>
    </section>

    
    <!-- CTA Final -->
    <section class="q-py-xl">
       <div class="container">
          <div class="banner-cta rounded-xl q-pa-xl text-center relative-position overflow-hidden shadow-20">
             <!-- 3D Canvas Background -->
             <canvas ref="ctaCanvas" class="absolute-full full-width full-height bg-black" style="pointer-events: none;"></canvas>
             
             <!-- Gradient Overlay -->
             <div class="absolute-full bg-gradient-overlay" style="z-index: 1; pointer-events: none;"></div>

             <div class="relative-position z-10 content-box q-pa-lg">
                <h2 class="text-h3 text-weight-bolder q-mb-md text-white text-shadow-lg leading-tight">
                   තවත් බලන් ඉන්නවද?
                </h2>
                <p class="text-h6 text-grey-4 q-mb-xl text-shadow-md" style="max-width: 600px; margin-left: auto; margin-right: auto;">
                   <span class="text-primary text-weight-bold">හිත හිත ඉන්නෙ නැතුව</span>, අදම වැඩ පටන් ගන්න. <br>
                   <span class="text-gradient-gold text-weight-bold">The First & The Best.</span>
                </p>
                <q-btn 
                   to="/register" 
                   color="primary" 
                   text-color="white" 
                   size="xl" 
                   rounded 
                   label="Join Now" 
                   no-caps 
                   icon-right="rocket_launch"
                   class="shadow-20 hover-lift glow-btn-pulse" 
                   style="padding: 12px 48px; font-weight: 800; letter-spacing: 1px;"
                />
             </div>
          </div>
       </div>
    </section>

  </q-page>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import * as THREE from 'three'

const slide = ref(1)
const ctaCanvas = ref(null)
const heroContainer = ref(null)
let animationId = null
let heroAnimationId = null
let renderer, scene, camera, particlesMesh

// Mouse Interaction for Hero
let mouseX = 0
let mouseY = 0

function onDocumentMouseMove(event) {
   mouseX = event.clientX - window.innerWidth / 2
   mouseY = event.clientY - window.innerHeight / 2
}

// Hero 3D Animation (Three.js)
function initHero3D() {
   if (!heroContainer.value) return
   
   // Scene Setup
   scene = new THREE.Scene()
   // Fog for depth fading
   scene.fog = new THREE.FogExp2(0x050505, 0.002)

   camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000)
   camera.position.z = 200

   renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true })
   renderer.setSize(window.innerWidth, heroContainer.value.offsetHeight)
   renderer.setPixelRatio(window.devicePixelRatio)
   heroContainer.value.appendChild(renderer.domElement)

   // Particles
   const geometry = new THREE.BufferGeometry()
   const vertices = []
   const colors = []
   
   const color1 = new THREE.Color(0x2563EB) // Blue
   const color2 = new THREE.Color(0xFFD700) // Gold
   
   for (let i = 0; i < 2000; i++) {
        // Random Position
      const x = (Math.random() - 0.5) * 800
      const y = (Math.random() - 0.5) * 800
      const z = (Math.random() - 0.5) * 800
      vertices.push(x, y, z)
      
      // Random Color (Mix Blue & Gold)
      const mixedColor = Math.random() > 0.5 ? color1 : color2
      colors.push(mixedColor.r, mixedColor.g, mixedColor.b)
   }

   geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3))
   geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3))

   const material = new THREE.PointsMaterial({ 
       size: 3, 
       vertexColors: true,
       opacity: 0.8,
       transparent: true
   })

   particlesMesh = new THREE.Points(geometry, material)
   scene.add(particlesMesh)

   // Interaction
   document.addEventListener('mousemove', onDocumentMouseMove)

   // Animate
   const animateHero = () => {
      heroAnimationId = requestAnimationFrame(animateHero)
      
      particlesMesh.rotation.x += 0.0005
      particlesMesh.rotation.y += 0.001
      
      // Gentle Parallax
      camera.position.x += (mouseX * 0.1 - camera.position.x) * 0.05
      camera.position.y += (-mouseY * 0.1 - camera.position.y) * 0.05
      
      camera.lookAt(scene.position)

      renderer.render(scene, camera)
   }
   
   animateHero()
   
   // Resize Handle
   window.addEventListener('resize', onWindowResize)
}

function onWindowResize() {
    if (!camera || !renderer) return
    camera.aspect = window.innerWidth / window.innerHeight
    camera.updateProjectionMatrix()
    renderer.setSize(window.innerWidth, window.innerHeight)
}


// Knowledge Network Animation (CTA)
function init3DAnimation() {
  const canvas = ctaCanvas.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  let width = canvas.width = canvas.offsetWidth
  let height = canvas.height = canvas.offsetHeight
  
  // Resize handler
  const resizeObserver = new ResizeObserver(() => {
     width = canvas.width = canvas.offsetWidth
     height = canvas.height = canvas.offsetHeight
  })
  resizeObserver.observe(canvas)

  // Particle Settings
  const particles = []
  const numParticles = 80
  const connectionDistance = 120

  class Particle {
      constructor() {
          this.x = Math.random() * width
          this.y = Math.random() * height
          this.vx = (Math.random() - 0.5) * 0.5 // Slow, professional movement
          this.vy = (Math.random() - 0.5) * 0.5
          this.size = Math.random() * 2 + 1
          // Randomly gold or blue
          this.color = Math.random() > 0.5 ? 'rgba(37, 99, 235, ' : 'rgba(255, 215, 0, ' 
      }

      update() {
          this.x += this.vx
          this.y += this.vy

          // Bounce off edges
          if (this.x < 0 || this.x > width) this.vx *= -1
          if (this.y < 0 || this.y > height) this.vy *= -1
      }

      draw() {
          ctx.beginPath()
          ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2)
          ctx.fillStyle = this.color + '0.5)'
          ctx.fill()
      }
  }

  // Init Particles
  for (let i = 0; i < numParticles; i++) {
      particles.push(new Particle())
  }

  function animate() {
     ctx.clearRect(0, 0, width, height)
     
     // Update and Draw Particles
     particles.forEach(p => {
         p.update()
         p.draw()
     })

     // Draw Connections
     for (let i = 0; i < particles.length; i++) {
         for (let j = i + 1; j < particles.length; j++) {
             const dx = particles[i].x - particles[j].x
             const dy = particles[i].y - particles[j].y
             const distance = Math.sqrt(dx * dx + dy * dy)

             if (distance < connectionDistance) {
                 const opacity = 1 - (distance / connectionDistance)
                 ctx.beginPath()
                 ctx.strokeStyle = `rgba(255, 255, 255, ${opacity * 0.15})`
                 ctx.lineWidth = 1
                 ctx.moveTo(particles[i].x, particles[i].y)
                 ctx.lineTo(particles[j].x, particles[j].y)
                 ctx.stroke()
             }
         }
     }
     
     animationId = requestAnimationFrame(animate)
  }
  
  animate()
}

onMounted(() => {
   init3DAnimation()
   initHero3D()
})

onUnmounted(() => {
   if (animationId) cancelAnimationFrame(animationId)
   if (heroAnimationId) cancelAnimationFrame(heroAnimationId)
   document.removeEventListener('mousemove', onDocumentMouseMove)
   window.removeEventListener('resize', onWindowResize)
})


const subjects = [
   { 
      id: 1, 
      name: 'Mathematics', 
      teachers: [
         'රංජිත් ලන්සකාර', 
         'දිනාල් වීරසිංහ', 
         'සංජය සේනානායක', 
         'සංජීව සඳරුවන්'
      ], 
      color: 'blue' 
   },
   { 
      id: 2, 
      name: 'Science', 
      teachers: [
         'වර්ධන වීරසිංහ', 
         'සෙනරත් ලංකාතිලක', 
         'ප්‍රභා තුෂාරි'
      ], 
      color: 'green' 
   },
   { 
      id: 3, 
      name: 'English', 
      teachers: [
         'සඳරු විජේසිංහ', 
         'ජගත් සී. ජයසිංහ', 
         'දීපා හේමමාලි',
         'චමිලා ඉලංකෝන්'
      ], 
      color: 'purple' 
   },
   { 
      id: 4, 
      name: 'Commerce', 
      teachers: ['ජනක ප්‍රියදර්ශන'], 
      color: 'orange' 
   },
   { 
      id: 5, 
      name: 'Sinhala', 
      teachers: ['ජගත් රත්නසිරි'], 
      color: 'red' 
   },
   { 
      id: 6, 
      name: 'History', 
      teachers: ['සඳුන් ශ්‍රී අධිකාරි'], 
      color: 'brown' 
   },
   { 
      id: 7, 
      name: 'Tamil', 
      teachers: ['නිරෝෂණී බණ්ඩාර'], 
      color: 'teal' 
   },
   { 
      id: 8, 
      name: 'Music', 
      teachers: ['ඩිලානි අයේෂා'], 
      color: 'pink' 
   }
]

const features = [
  { 
     title: 'Expert Teachers', 
     copy: 'ගණිතය, විද්‍යාව, ඉංග්‍රීසි... විෂයන් ඕන එකකට හොඳම ගුරුවරුන් සමඟ.', 
     icon: 'school',
     color: 'blue'
  },
  { 
     title: 'Modern Facilities', 
     copy: 'වායු සමීකරණය කරන ලද පන්ති කාමර (AC), සුව පහසු ආසන සමඟ හොඳම තැන.', 
     icon: 'chair',
     color: 'purple'
  },
  { 
     title: 'Smart Student Portal', 
     copy: 'පැමිණීම, පන්ති ගාස්තු සහ විභාග ලකුණු දෙමාපියන්ට ගෙදර ඉඳන්ම බලාගන්න පුළුවන් System එකක් සමඟ තවත් පහසුකම් රැසක්.', 
     icon: 'smartphone',
     color: 'green'
  }
]
</script>

<style lang="scss" scoped>
.landing-page {
  font-family: 'Inter', sans-serif;
  background-color: #050505;
}

/* Grids & Backgrounds */
.grid-overlay {
  background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
  linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
}

.glow-orb {
   position: absolute;
   width: 600px;
   height: 600px;
   border-radius: 50%;
   filter: blur(100px);
   opacity: 0.2;
}
.orb-1 { top: -100px; left: -100px; background: #2563eb; }
.orb-2 { bottom: -100px; right: -100px; background: #7c3aed; }

/* Hero Typography */
.hero { min-height: 100vh; padding-top: 40px; position: relative; }
.hero-title { font-size: 3.5rem; line-height: 1.1; }
.text-gradient {
  background: linear-gradient(to right, #60a5fa, #a78bfa, #f472b6);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.text-gradient-gold {
  background: linear-gradient(to right, #FFD700, #FDB931);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  text-shadow: 0px 0px 30px rgba(253, 185, 49, 0.3);
}

/* Glassmorphism */
.glass-panel {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
}
.glass-panel-light {
   background: rgba(255, 255, 255, 0.02);
   border: 1px solid rgba(255, 255, 255, 0.05);
   border-radius: 20px;
}
.glass-chip {
   backdrop-filter: blur(8px);
   background: rgba(37, 99, 235, 0.2) !important;
   border: 1px solid rgba(37, 99, 235, 0.4);
}
.glass-card {
   background: rgba(20, 20, 20, 0.8);
   backdrop-filter: blur(10px);
   border: 1px solid rgba(255, 255, 255, 0.1);
   border-radius: 12px;
   min-width: 160px;
}
.glass-caption {
   background: rgba(0,0,0,0.7);
   backdrop-filter: blur(4px);
   padding: 10px;
}

.custom-caption {
  text-align: center;
  padding: 12px;
  color: white;
  background-color: rgba(0, 0, 0, .5);
  backdrop-filter: blur(4px);
  width: 100%;
}

/* Animations */
.hover-lift { transition: transform 0.3s ease; }
.hover-lift:hover { transform: translateY(-10px); }

.hover-scale { transition: transform 0.3s ease; }
.hover-scale:hover { transform: scale(1.05); }

.floating-container {
    animation: floating 6s ease-in-out infinite;
}

@keyframes floating {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}

.glow-on-hover {
  transition: box-shadow 0.3s ease;
}
.glow-on-hover:hover {
  box-shadow: 0 0 20px rgba(37, 99, 235, 0.6);
}

.shine-effect {
  position: relative;
  overflow: hidden;
  &::after {
    content: '';
    position: absolute;
    top: 0; left: -100%; width: 50%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shine 3s infinite;
  }
}
@keyframes shine {
  0% { left: -100%; }
  20% { left: 200%; }
  100% { left: 200%; }
}

/* Utilities */
.container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
.leading-tight { line-height: 1.1; }
.tracking-widest { letter-spacing: 0.15em; }
.bg-darker { background: #0a0a0a; }

.gradient-bg {
   background: linear-gradient(45deg, #2563eb, #7c3aed);
}

@media (max-width: 600px) {
   .hero-title { font-size: 2.5rem; }
   .hero-subtitle { font-size: 1rem; }
}

.bg-white-5 { background: rgba(255,255,255,0.05); }
.border-glass { border: 1px solid rgba(255,255,255,0.1); }

.sticky-filters {
  position: sticky;
  top: 0;
  background: rgba(10, 10, 10, 0.95);
  backdrop-filter: blur(10px);
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.hover-border {
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.05);
}
.hover-border:hover {
    border-color: rgba(37, 99, 235, 0.5);
    background: rgba(255,255,255,0.06);
    transform: translateY(-2px);
}

.ellipsis-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2; /* Fixes lint warning */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.2;
}

.bg-gradient-overlay {
    background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.8) 100%);
}

.text-shadow-lg { text-shadow: 0 4px 20px rgba(0,0,0,0.8); }
.text-shadow-md { text-shadow: 0 2px 10px rgba(0,0,0,0.8); }

.glow-btn {
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
    transition: all 0.3s ease;
}
.glow-btn:hover {
    box-shadow: 0 0 40px rgba(255, 255, 255, 0.8);
    transform: scale(1.05);
}
.letter-spacing-1 { letter-spacing: 1px; }

/* New 3D Text & Button Styles */
.bounce-text {
  display: inline-block;
  animation: bounce-float 3s ease-in-out infinite;
  text-shadow: 0 10px 20px rgba(0,0,0,0.5);
}
@keyframes bounce-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.glow-btn-pulse {
   position: relative;
   overflow: hidden;
   animation: pulse-glow 2s infinite;
}
@keyframes pulse-glow {
   0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
   70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
   100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
}

.hover-glass:hover {
   background: rgba(255, 255, 255, 0.1) !important;
   backdrop-filter: blur(10px);
   border-color: rgba(255,255,255,0.3) !important;
}

.highlight-text {
   position: relative;
   z-index: 10;
}

.bg-gradient-overlay {
    background: radial-gradient(circle at center, rgba(37, 99, 235, 0.1) 0%, rgba(5, 5, 5, 0.95) 100%);
}

.banner-cta {
   border: 1px solid rgba(255, 215, 0, 0.2);
   box-shadow: 0 0 50px rgba(37, 99, 235, 0.1);
}

.hover-border-gold {
   border: 1px solid rgba(255, 255, 255, 0.1);
   transition: all 0.3s ease;
}
.hover-border-gold:hover {
   border-color: rgba(255, 215, 0, 0.5);
   box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
   transform: translateY(-5px);
}

.teacher-row:hover .q-icon {
   color: #FFD700 !important;
}

.letter-spacing-wide { letter-spacing: 1px; }

.bg-gradient-blue-vertical {
    background: linear-gradient(to bottom, rgba(37, 99, 235, 0.05), transparent);
}
</style>
