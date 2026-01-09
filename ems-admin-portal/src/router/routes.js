
const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', component: () => import('pages/IndexPage.vue') },
      { path: 'institutes', component: () => import('pages/InstitutesPage.vue') },
      { path: 'users', component: () => import('pages/UsersPage.vue') },
      { path: 'courses', component: () => import('pages/CoursesPage.vue') },
      { path: 'finance', component: () => import('pages/FinancePage.vue') },
      { path: 'settings', component: () => import('pages/SettingsPage.vue') }
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
