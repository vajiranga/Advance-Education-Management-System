import { defineStore } from 'pinia'
import { api } from 'src/services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || '',
        user: JSON.parse(localStorage.getItem('user') || 'null')
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        hasPermission: (state) => (permission) => {
            if (!state.user) return false
            // Super admin bypass or simple permission check
            if (state.user.role === 'super_admin' || state.user.is_super_admin) return true
            return state.user.permissions?.includes(permission) || false
        }
    },
    actions: {
        async login(email, password) {
            const res = await api.post('/login', { email, password })

            if (res.data.user.role !== 'admin' && res.data.user.role !== 'super_admin') {
                throw new Error('Access Denied. Admin privileges required.')
            }

            this.token = res.data.token
            this.user = res.data.user
            localStorage.setItem('token', this.token)
            localStorage.setItem('user', JSON.stringify(this.user))
            api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        },
        logout() {
            this.token = ''
            this.user = null
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            delete api.defaults.headers.common['Authorization']
        },
        init() {
            if (this.token) {
                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
            }
        }
    }
})
