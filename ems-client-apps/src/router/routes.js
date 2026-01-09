const routes = [
  {
    path: '/',
    redirect: '/student/dashboard'
  },
  {
    path: '/student',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', component: () => import('pages/IndexPage.vue') },
      { path: 'courses', component: () => import('pages/StudentCoursesPage.vue') },
      { path: 'exams', component: () => import('pages/StudentExamsPage.vue') },
      { path: 'payments', component: () => import('pages/StudentPaymentsPage.vue') },
      { path: 'watch/:id', component: () => import('pages/PlayerPage.vue') },
    ]
  },
  {
    path: '/parent',
    component: () => import('layouts/ParentLayout.vue'),
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', component: () => import('pages/ParentDashboard.vue') },
      { path: 'results', component: () => import('pages/ParentResults.vue') },
      { path: 'payments', component: () => import('pages/ParentFeesPage.vue') },
      { path: 'messages', component: () => import('pages/ParentChatPage.vue') }
    ]
  },
  {
    path: '/teacher',
    component: () => import('layouts/TeacherLayout.vue'),
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', component: () => import('pages/TeacherDashboardPage.vue') },
      { path: 'classes', component: () => import('pages/TeacherClassList.vue') },
      { path: 'students', component: () => import('pages/TeacherStudentList.vue') },
      { path: 'attendance', component: () => import('pages/TeacherAttendanceSheet.vue') },
      { path: 'exams', component: () => import('pages/TeacherExamBoard.vue') },
    ]
  },
  {
    path: '/login',
    component: () => import('layouts/LoginLayout.vue')
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
