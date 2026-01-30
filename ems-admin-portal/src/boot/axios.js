import { boot } from 'quasar/wrappers'
import axios from 'axios'

// Set default headers for compatibility with Laravel API
const api = axios.create({ 
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    withCredentials: true 
})

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
            if (error.response && error.response.status === 401) {
                authStore.logout()
                router.push('/login')
            }
            return Promise.reject(error)
        }
    )
})

export { api }
