import { boot } from 'quasar/wrappers'
import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api' })

// Add Request Interceptor for Token
api.interceptors.request.use((config) => {
    // Manually getting token from localStorage to satisfy race conditions
    // Replicating auth-store logic:
    try {
        const accounts = JSON.parse(localStorage.getItem('auth_accounts') || '[]')
        const index = parseInt(localStorage.getItem('auth_active_index') || '0')
        const token = accounts[index]?.token

        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
    } catch {
        // ignore storage errors
    }
    return config
}, (error) => {
    return Promise.reject(error)
})

// Response Interceptor for 401
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            if (!window.location.hash.includes('/login')) {
                window.location.href = '#/login'
            }
        }
        return Promise.reject(error)
    }
)

export default boot(({ app }) => {
    app.config.globalProperties.$axios = axios
    app.config.globalProperties.$api = api
})

export { api }
