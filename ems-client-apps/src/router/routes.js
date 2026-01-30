const routes = [
  {
    path: '/',
    component: () => import('layouts/LandingLayout.vue'),
    children: [
      { path: '', component: () => import('pages/LandingPage.vue') }
    ]
  },
  {
    path: '/register',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: '', component: () => import('pages/RegisterPage.vue') }
    ]
  },
  {
    path: '/login',
    component: () => import('layouts/LoginLayout.vue')
  },
  {
    path: '/student',
    component: () => import('layouts/MainLayout.vue'),
    meta: { role: 'student' },
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', component: () => import('pages/IndexPage.vue') },
      { path: 'courses', component: () => import('pages/StudentCoursesPage.vue') },
      { path: 'exams', component: () => import('pages/StudentExamsPage.vue') },
      { path: 'attendance', component: () => import('pages/StudentAttendancePage.vue') },
      { path: 'payments', component: () => import('pages/StudentPaymentsPage.vue') },
      { path: 'watch/:id', component: () => import('pages/PlayerPage.vue') },
      { path: 'profile', component: () => import('pages/student/StudentProfilePage.vue') },
    ]
  },
  {
    path: '/parent',
    component: () => import('layouts/ParentLayout.vue'),
    meta: { role: 'parent' },
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', component: () => import('pages/ParentDashboard.vue') },
      { path: 'results', component: () => import('pages/ParentResults.vue') },
      { path: 'payments', component: () => import('pages/StudentPaymentsPage.vue'), meta: { role: 'parent', isParentView: true } },
      { path: 'attendance', component: () => import('pages/ParentAttendancePage.vue') },

      { path: 'profile', component: () => import('pages/ParentProfilePage.vue') }
    ]
  },
  {
    path: '/teacher',
    component: () => import('layouts/TeacherLayout.vue'),
    meta: { role: 'teacher' },
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', component: () => import('pages/TeacherDashboardPage.vue') },
      { path: 'classes', component: () => import('pages/TeacherClassList.vue') },
      { path: 'attendance', component: () => import('pages/TeacherAttendanceSheet.vue') },
      { path: 'exams', component: () => import('pages/TeacherExamBoard.vue') },
    ]
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
