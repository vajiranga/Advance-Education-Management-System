const routes = [
    {
        path: '/',
        component: () => import('layouts/MainLayout.vue'),
        children: [
            { path: '', component: () => import('pages/AppHome.vue') },
            { path: 'login', component: () => import('pages/AppLogin.vue') },
            { path: 'register', component: () => import('pages/AppRegister.vue') }
        ]
    },

    {
        path: '/:catchAll(.*)*',
        component: () => import('pages/ErrorNotFound.vue')
    }
]

export default routes
