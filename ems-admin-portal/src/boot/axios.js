import { boot } from 'quasar/wrappers'
import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api' })

// Add Request Interceptor for Token
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
}, error => Promise.reject(error))

import { useAuthStore } from 'stores/auth-store'

export default boot(({ app, store }) => {
    app.config.globalProperties.$axios = axios
    app.config.globalProperties.$api = api

    const authStore = useAuthStore(store)
    authStore.init()

    api.interceptors.response.use(
        response => response,
        error => {
            if (error.response && (error.response.status === 401 || error.response.status === 403)) {
                // Determine if it's just a permissions issue or invalid token
                // const isForbidden = error.response.status === 403

                // Only logout on 401 or if we want to force logout on 403 (often safer for this admin app)
                // authStore.logout()
                // router.push('/login')
            }
            return Promise.reject(error)
        }
    )
})

export { api }
