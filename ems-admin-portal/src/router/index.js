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
    // Lazy load store to avoid circular dependency or early init issues
    // Note: In Quasar, we can import store here if needed, or check localStorage directly for speed,
    // but using store is cleaner. We need to import useAuthStore at top.
    // However, inside this function 'store' (Pinia instance) is available via closure if we use it, 
    // but 'export default function (/* { store } */)' is currently commented out.
    // Let's use localStorage fallback for simplicity if store access is slightly complex here without imports.
    // BETTER: Import useAuthStore.

    const token = localStorage.getItem('token')
    if (to.path !== '/login' && !token) {
      next('/login')
    } else {
      next()
    }
  })

  return Router
}
