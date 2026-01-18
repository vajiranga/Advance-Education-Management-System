<template>
  <q-page class="landing-page">
    <!-- Hero Section -->
    <section class="hero relative-position flex flex-center text-white overflow-hidden">
      <div class="hero-bg absolute-full"></div>
      <div class="container relative-position z-10 q-pa-md text-center">
        <q-badge color="orange-5" class="q-mb-md q-py-xs q-px-sm text-subtitle2 shadow-2">
          🚀 New Version 3.0 Released
        </q-badge>
        
        <h1 class="text-h2 text-md-h1 text-weight-bolder leading-tight q-mb-lg hero-title" data-aos="fade-up">
          The Future of <br>
          <span class="text-gradient">Education Management</span>
        </h1>
        
        <p class="text-h6 text-grey-4 q-mb-xl hero-subtitle" data-aos="fade-up" data-aos-delay="100">
          Simplify institute operations, engage students, and grow your business with the world's most advanced all-in-one LMS platform.
        </p>
        
        <div class="row justify-center q-gutter-md q-mb-xl" data-aos="fade-up" data-aos-delay="200">
          <q-btn 
            to="/register" 
            color="secondary" 
            size="xl" 
            label="Get Started Free" 
            no-caps 
            icon-right="arrow_forward"
            class="cta-btn shadow-10" 
          />
          <q-btn 
            flat 
            color="white" 
            size="xl" 
            label="Watch Demo" 
            no-caps 
            icon="play_circle"
            class="cta-btn-outline" 
          />
        </div>

        <!-- Dashboard Preview Mockup -->
        <div class="dashboard-preview q-mx-auto shadow-24 rounded-borders bg-white overflow-hidden" data-aos="fade-up" data-aos-delay="400">
           <div class="row items-center q-pa-sm bg-grey-3 border-bottom">
              <div class="row q-gutter-sm q-ml-sm">
                 <div class="circle bg-red-4"></div>
                 <div class="circle bg-yellow-4"></div>
                 <div class="circle bg-green-4"></div>
              </div>
              <div class="col text-center text-grey-7 text-caption">EMS Dashboard</div>
           </div>
           <div class="q-pa-md row relative-position" style="height: 300px; background: #f8fafc;">
               <!-- Mock Sidebar -->
               <div class="col-2 bg-white q-mr-md rounded-borders shadow-1 hidden sm-block"></div>
               <!-- Mock Content -->
               <div class="col column q-gutter-md">
                  <div class="row q-gutter-md">
                     <div class="col bg-white rounded-borders shadow-1" style="height: 100px;"></div>
                     <div class="col bg-white rounded-borders shadow-1" style="height: 100px;"></div>
                     <div class="col bg-white rounded-borders shadow-1" style="height: 100px;"></div>
                  </div>
                  <div class="col bg-white rounded-borders shadow-1"></div>
               </div>
               
               <!-- Floating Badge -->
               <q-card class="absolute-bottom-right q-ma-lg bg-dark text-white shadow-10 floating-card" style="width: 200px">
                  <q-card-section class="row items-center">
                     <q-icon name="verified" color="green-4" size="md" class="q-mr-sm" />
                     <div>
                        <div class="text-caption text-grey">Attendance</div>
                        <div class="text-weight-bold">98.5% Marked</div>
                     </div>
                  </q-card-section>
               </q-card>
           </div>
        </div>
      </div>
    </section>

    <!-- Trusted By -->
    <section class="bg-white q-py-lg border-bottom">
       <div class="container">
          <p class="text-center text-grey-5 text-uppercase text-caption q-mb-md text-weight-bold tracking-widest">Trusted by 500+ Institutes Worldwide</p>
          <div class="row justify-center items-center q-gutter-xl opacity-50 grayscale hover-grayscale-0 transition-all">
             <div class="text-h6 text-grey-8 text-weight-bold">CAMPUS<span class="text-primary">ONE</span></div>
             <div class="text-h6 text-grey-8 text-weight-bold">ED<span class="text-secondary">XPERT</span></div>
             <div class="text-h6 text-grey-8 text-weight-bold">SCHOLAR<span class="text-accent">HUB</span></div>
             <div class="text-h6 text-grey-8 text-weight-bold">UNI<span class="text-positive">VERSITY</span></div>
          </div>
       </div>
    </section>

    <!-- Features Section -->
    <section class="q-py-xl section-features bg-grey-1">
      <div class="container relative-position">
        <div class="text-center q-mb-xl">
           <q-chip color="blue-1" text-color="blue-9" class="q-mb-md">POWERFUL FEATURES</q-chip>
           <h2 class="text-h3 text-weight-bold text-dark q-mb-md">Everything you need to run your institute</h2>
           <p class="text-grey-7 text-h6" style="max-width: 600px; margin: 0 auto">Stop using multiple tools. EMS brings your students, exams, and payments into one seamless platform.</p>
        </div>

        <div class="row q-col-gutter-lg">
          <div class="col-12 col-md-4" v-for="(feature, i) in features" :key="i">
            <q-card class="feature-card no-shadow bg-white rounded-xl h-full transition-hover">
              <q-card-section class="q-pa-xl">
                <div class="icon-box q-mb-lg" :class="`bg-${feature.color}-1 text-${feature.color}`">
                   <q-icon :name="feature.icon" size="32px" />
                </div>
                <div class="text-h5 text-weight-bold q-mb-sm text-dark">{{ feature.title }}</div>
                <div class="text-grey-7 leading-relaxed">{{ feature.description }}</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="q-py-xl bg-dark text-white relative-position overflow-hidden">
       <div class="absolute-right full-height" style="width: 50%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05));"></div>
       <div class="container relative-position z-10">
          <div class="row justify-center text-center q-col-gutter-xl">
             <div class="col-6 col-md-3">
                <div class="text-h2 text-weight-bold text-secondary counter">500+</div>
                <div class="text-subtitle1 text-grey-4">Institutes</div>
             </div>
             <div class="col-6 col-md-3">
                <div class="text-h2 text-weight-bold text-accent counter">1M+</div>
                <div class="text-subtitle1 text-grey-4">Active Students</div>
             </div>
             <div class="col-6 col-md-3">
                <div class="text-h2 text-weight-bold text-primary counter">10K+</div>
                <div class="text-subtitle1 text-grey-4">Daily Exams</div>
             </div>
             <div class="col-6 col-md-3">
                <div class="text-h2 text-weight-bold text-positive counter">$5M+</div>
                <div class="text-subtitle1 text-grey-4">Payments Processed</div>
             </div>
          </div>
       </div>
    </section>

    <!-- CTA Section -->
    <section class="q-py-xl bg-white">
       <div class="container">
          <div class="bg-gradient-primary rounded-xl q-pa-xl text-center text-white relative-position overflow-hidden shadow-20">
             <div class="absolute-full" style="background: url('https://cdn.quasar.dev/img/mesh.png'); opacity: 0.1"></div>
             <div class="relative-position z-10">
                <h2 class="text-h3 text-weight-bold q-mb-md">Ready to transform your education business?</h2>
                <p class="text-h6 text-blue-1 q-mb-xl">Join hundreds of institutes already scaling with EMS.</p>
                <q-btn to="/register" color="white" text-color="primary" size="xl" label="Start Your Free Trial" no-caps class="shadow-5" />
                <div class="q-mt-md text-blue-2 text-caption">No credit card required • 14-day free trial</div>
             </div>
          </div>
       </div>
    </section>
  </q-page>
</template>

<script setup>
const features = [
  { 
     title: 'LMS & Exams', 
     description: 'Upload high-quality videos with DRM protection and conduct secure MCQ exams with automated grading and result analysis.', 
     icon: 'school',
     color: 'blue'
  },
  { 
     title: 'Smart Attendance', 
     description: 'Mark attendance via QR Code, NFC, or Facial Recognition. Notify parents instantly with SMS and App notifications.', 
     icon: 'qr_code_scanner',
     color: 'purple'
  },
  { 
     title: 'Automated Payments', 
     description: 'Collect fees online, manage partial payments, and generate automated slips. Seamless integration with local payment gateways.', 
     icon: 'payments',
     color: 'green'
  },
  { 
     title: 'Student Analytics', 
     description: 'Track student performance trends, attendance history, and exam results with AI-powered insights for teachers.', 
     icon: 'analytics',
     color: 'orange'
  },
  { 
     title: 'Mobile Apps', 
     description: 'Dedicated mobile apps for Students, Parents, and Teachers. Keep everyone connected on the go.', 
     icon: 'smartphone',
     color: 'teal'
  },
  { 
     title: 'Institute Management', 
     description: 'Manage staff payroll, inventory, and multiple branches from a single powerful admin dashboard.', 
     icon: 'domain',
     color: 'red'
  }
]
</script>

<style lang="scss" scoped>
.hero {
  min-height: 100vh;
  padding-top: 80px;
  background: #0f172a;
}

.hero-bg {
  background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 60%);
  &::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: 
       linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
       linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 50px 50px;
    opacity: 0.5;
  }
}

.text-gradient {
  background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 24px;
}

.dashboard-preview {
  max-width: 900px;
  border: 1px solid rgba(255,255,255,0.1);
  transform: perspective(1000px) rotateX(2deg);
  transition: transform 0.5s ease;
  &:hover {
     transform: perspective(1000px) rotateX(0deg);
  }
}

.circle { width: 10px; height: 10px; border-radius: 50%; }

.floating-card {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.feature-card {
  border: 1px solid #f1f5f9;
  transition: all 0.3s ease;
  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border-color: #e2e8f0;
  }
}

.icon-box {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
}

.cta-btn {
  font-weight: 700;
  padding: 12px 32px;
}
</style>
