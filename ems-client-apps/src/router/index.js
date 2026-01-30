import { defineRouter } from '#q-app/wrappers'
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router'
import routes from './routes'

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  // Global Router Guard
  Router.beforeEach((to, from, next) => {
    const publicPages = ['/login', '/register', '/'];
    const authRequired = !publicPages.includes(to.path);

    let loggedIn = false;
    let user = null;

    try {
        const rawAccounts = localStorage.getItem('auth_accounts');

        // Robust check for existing data
        if (rawAccounts && rawAccounts !== 'undefined' && rawAccounts !== 'null') {
             const accounts = JSON.parse(rawAccounts);

             if (Array.isArray(accounts) && accounts.length > 0) {
                 // Try to get active account, otherwise default to the first one available
                 let activeIndex = parseInt(localStorage.getItem('auth_active_index') || '0');
                 if (isNaN(activeIndex) || activeIndex < 0 || activeIndex >= accounts.length) {
                     activeIndex = 0; // Fallback safe index
                 }

                 user = accounts[activeIndex]?.user;
                 loggedIn = !!user;
             }
        }
    } catch (e) {
        console.error('[Router] Auth check error:', e);
    }

    console.log(`[Router] Navigation: ${to.path} | LoggedIn: ${loggedIn} | User: ${user?.role}`);

    // 1. Auth Check - Redirect to Login if not authenticated
    if (authRequired && !loggedIn) {
      console.warn('[Router] Auth required. Redirecting to Login.');
      return next('/login');
    }

    // 2. Already Logged In -> Prevent visiting Login/Register manually
    if (loggedIn && publicPages.includes(to.path)) {
         if (to.path === '/login' || to.path === '/register') {
             if (user.role === 'student') return next('/student/dashboard');
             if (user.role === 'teacher') return next('/teacher/dashboard');
             if (user.role === 'parent') return next('/parent/dashboard');
             return next('/');
         }
    }

    // 3. Role Access Check
    if (loggedIn) {
        const roleRecord = to.matched.find(record => record.meta.role);

        if (roleRecord) {
             // Special Exception: Parent View
             if (roleRecord.meta.isParentView && user.role === 'parent') {
                return next();
             }

             if (user.role !== roleRecord.meta.role) {
                console.warn(`[Router] Role Mismatch! User: ${user.role}, Target: ${roleRecord.meta.role}`);

                // Strict Redirection based on user role
                if (user.role === 'student') return next('/student/dashboard');
                if (user.role === 'parent') return next('/parent/dashboard');
                if (user.role === 'teacher') return next('/teacher/dashboard');
                if (user.role === 'admin') return next('/admin/dashboard');

                return next('/');
             }
        }
    }

    next();
  });

  return Router
})
