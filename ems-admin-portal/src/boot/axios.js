import { boot } from 'quasar/wrappers'
import axios from 'axios'

// Set default headers for compatibility with Laravel API
import { api } from 'src/services/api'

// Add Request Interceptor for Token
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
}, error => Promise.reject(error))

import { useAuthStore } from 'stores/auth-store'

export default boot(({ app, store, router }) => {
    app.config.globalProperties.$axios = axios
    app.config.globalProperties.$api = api

    const authStore = useAuthStore(store)
    authStore.init()

    api.interceptors.response.use(
        response => response,
        error => {
            // Handle 401 Unauthorized
            if (error.response && error.response.status === 401) {
                authStore.logout()
                router.push('/login')
            }

            // Handle 503 Maintenance Mode
            if (error.response && error.response.status === 503) {
                const maintenanceMode = error.response.data?.maintenance_mode

                if (maintenanceMode) {
                    // Force logout
                    authStore.logout()
                    localStorage.removeItem('token')
                    localStorage.removeItem('user')

                    // Redirect to maintenance page
                    router.push('/maintenance')
                }
            }

            return Promise.reject(error)
        }
    )
})

export { api }
