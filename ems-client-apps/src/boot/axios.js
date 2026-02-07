import { boot } from 'quasar/wrappers'
import axios from 'axios'
import { useAuthStore } from 'stores/auth-store'
import { api } from 'src/services/api'

// Request Interceptor: Attach Token & Handle Versioning
api.interceptors.request.use((config) => {
  // Prioritize Multi-Account Token
  let token = null;
  try {
      const accounts = JSON.parse(localStorage.getItem('auth_accounts') || '[]');
      const index = parseInt(localStorage.getItem('auth_active_index') || '0');
      if (Array.isArray(accounts) && accounts[index] && accounts[index].token) {
          token = accounts[index].token;
      }
  } catch (e) { console.error('Token parsing error', e); }

  // Fallback to legacy token
  if (!token) {
      token = localStorage.getItem('token');
  }

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  // INTELLIGENT ROUTING FIX:
  // If the request is NOT for login/register, and doesn't explicitly start with v1,
  // we assume it belongs to the V1 API namespace.
  const authEndpoints = ['/login', '/parent-login', '/register', '/sanctum/csrf-cookie'];
  const isAuth = authEndpoints.some(endpoint => config.url === endpoint || config.url.startsWith(endpoint + '?'));

  if (!isAuth && !config.url.startsWith('http') && !config.url.includes('/v1/')) {
    // If url starts with /, remove it to append clearly
    const cleanPath = config.url.startsWith('/') ? config.url.substring(1) : config.url;
    // Don't double prefix if it's already there (rare case)
    if (!cleanPath.startsWith('v1/')) {
       config.url = `v1/${cleanPath}`;
    }
  }

  return config
}, error => Promise.reject(error))

export default boot(({ app, store, router }) => {
  app.config.globalProperties.$axios = axios
  app.config.globalProperties.$api = api

  const authStore = useAuthStore(store)
  if(authStore) {
      // Initialize store if method exists
      if(typeof authStore.init === 'function') authStore.init()
  }

  // Response Interceptor: Handle 401 Logout
  api.interceptors.response.use(
    response => response,
    error => {
      if (error.response && error.response.status === 401) {
        if(authStore) authStore.logout()
        if(router.currentRoute.value.path !== '/login') {
            router.push('/login')
        }
      }
      return Promise.reject(error)
    }
  )
})

export { api }
