
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', component: () => import('pages/IndexPage.vue'), meta: { permission: 'dashboard' } },
      { path: 'institutes', component: () => import('pages/InstitutesPage.vue'), meta: { permission: 'halls' } },
      { path: 'users', component: () => import('pages/UsersPage.vue'), meta: { permission: 'users' } },
      { path: 'courses', component: () => import('pages/CoursesPage.vue'), meta: { permission: 'classes' } },
      { path: 'finance', component: () => import('pages/FinancePage.vue'), meta: { permission: 'finance' } },
      { path: 'attendance', component: () => import('pages/AttendanceMarkingPage.vue'), meta: { permission: 'attendance' } },
      { path: 'cashpayment', component: () => import('pages/CashPaymentPage.vue'), meta: { permission: 'payments' } },
      { path: 'settings', component: () => import('pages/SettingsPage.vue'), meta: { permission: 'settings' } }
    ]
  },
  {
    path: '/login',
    component: () => import('layouts/LoginLayout.vue')
  },
  {
    path: '/maintenance',
    component: () => import('pages/MaintenancePage.vue')
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
