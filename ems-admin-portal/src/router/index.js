import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router'
import routes from './routes'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createWebHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  Router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token')
    const userStr = localStorage.getItem('user')
    let user = null
    try {
        if (userStr) user = JSON.parse(userStr)
    } catch (e) {
        console.error('Failed to parse user from storage', e)
    }

    // 1. Auth Check: If not public (login) and no token, redirect to login
    if (to.path !== '/login' && !token) {
      return next('/login')
    }

    // 2. Login Redirect: If going to login but already has token, redirect to home
    if (to.path === '/login' && token) {
      return next('/')
    }

    // 3. Permission Check
    if (to.meta.permission && user) {
        // Super Admin bypass
        if (user.is_super_admin) {
            return next()
        }

        const required = to.meta.permission
        const perms = user.permissions || []

        // Check access
        let hasAccess = perms.includes(required)

        // Special handling: 'settings_edit' grants 'settings' access
        if (required === 'settings' && perms.includes('settings_edit')) {
            hasAccess = true
        }

        // Special handling: Finance sub-permissions grant 'finance' access
        if (required === 'finance') {
            hasAccess = perms.includes('finance') ||
                       perms.includes('finance_pending') ||
                       perms.includes('finance_transactions') ||
                       perms.includes('finance_uncollected') ||
                       perms.includes('finance_settlement')
        }

        if (hasAccess) {
            next()
        } else {
            console.warn(`Access Denied: User lacks '${required}' permission for ${to.path}`)
            // If direct access (from === null or from.name === null), loading a disallowed page -> redirect to login or blank
            // If verifying failed, we should probably redirect to a safe default or show error.
            // For now, next(false) aborts navigation.
            next(false)
        }
    } else {
        next()
    }
  })

  return Router
}
