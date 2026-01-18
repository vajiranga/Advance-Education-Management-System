import { defineStore } from 'pinia'
import { api } from 'boot/axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        accounts: JSON.parse(localStorage.getItem('auth_accounts')) || [],
        activeAccountIndex: parseInt(localStorage.getItem('auth_active_index') || 0),
        selectedChild: null
    }),

    getters: {
        user: (state) => state.accounts[state.activeAccountIndex]?.user || null,
        token: (state) => state.accounts[state.activeAccountIndex]?.token || null,
        allAccounts: (state) => state.accounts,
        currentChild: (state) => state.selectedChild
    },

    actions: {
        // Called when MainLayout mounts or on app init
        // Called when MainLayout mounts or on app init
        init() {
            // Check for token in URL (SSO from Landing)
            const urlParams = new URLSearchParams(window.location.search)
            const token = urlParams.get('token')

            if (token) {
                // Determine user role and redirect properly if needed
                // But addAccountFromToken fetches user, so we let it handle logic
                this.addAccountFromToken(token)

                // Clean URL
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: newUrl }, '', newUrl);
                return
            }

            this.setAxiosHeader()
            // Optional: verify token validity
        },

        async fetchUser() {
            if (!this.token) return
            try {
                const res = await api.get('/user')
                this.updateUserInAccount(res.data)
            } catch (e) {
                console.error('Fetch user failed', e)
            }
        },

        async addAccountFromToken(token) {
            // 1. Set header temporarily
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`
            try {
                // 2. Fetch User to identify
                const res = await api.get('/user')
                const user = res.data

                // 3. Check if exists
                const existing = this.accounts.findIndex(a => a.user.id === user.id)
                if (existing !== -1) {
                    // Update existing
                    this.accounts[existing] = { token, user }
                    this.activeAccountIndex = existing
                } else {
                    // Add new (max 4)
                    if (this.accounts.length >= 4) {
                        this.accounts.shift() // Remove oldest
                    }
                    this.accounts.push({ token, user })
                    this.activeAccountIndex = this.accounts.length - 1
                }
                this.persist()

                // Reload to apply state cleanly
                window.location.reload()
            } catch (e) {
                console.error('Failed to add account', e)
            }
        },

        switchAccount(index) {
            if (index >= 0 && index < this.accounts.length) {
                this.activeAccountIndex = index
                this.persist()
                this.setAxiosHeader()
                window.location.reload()
            }
        },

        updateUserInAccount(user) {
            if (this.accounts[this.activeAccountIndex]) {
                this.accounts[this.activeAccountIndex].user = user
                this.persist()
            }
        },

        logout() {
            if (this.accounts.length > 0) {
                this.accounts.splice(this.activeAccountIndex, 1)
                // Adjust index
                if (this.activeAccountIndex >= this.accounts.length) {
                    this.activeAccountIndex = Math.max(0, this.accounts.length - 1)
                }
                this.persist()

                if (this.accounts.length === 0) {
                    window.location.href = 'http://localhost:9003'
                } else {
                    window.location.reload()
                }
            } else {
                window.location.href = 'http://localhost:9003'
            }
        },

        persist() {
            localStorage.setItem('auth_accounts', JSON.stringify(this.accounts))
            localStorage.setItem('auth_active_index', this.activeAccountIndex)
        },

        setAxiosHeader() {
            const token = this.token
            if (token) {
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`
            } else {
                delete api.defaults.headers.common['Authorization']
            }
        },
        async login(credentials) {
            try {
                // Backend expects 'email' key which handles both email and username
                const payload = {
                    email: credentials.username,
                    password: credentials.password
                }

                const res = await api.post('/login', payload)
                const { user, token } = res.data

                this.addAccount({ user, token })

                return { success: true, role: user.role }
            } catch (e) {
                return {
                    success: false,
                    message: e.response?.data?.message || 'Login failed. Please check credentials.'
                }
            }
        },

        async loginParent(credentials) {
            try {
                const res = await api.post('/parent-login', {
                    phone: credentials.phone,
                    student_id: credentials.student_id
                })
                const { user, token } = res.data

                this.addAccount({ user, token })

                return { success: true, role: 'parent' }
            } catch (e) {
                return {
                    success: false,
                    message: e.response?.data?.message || 'Parent Login Failed.'
                }
            }
        },

        addAccount({ user, token }) {
            const existing = this.accounts.findIndex(a => a.user.id === user.id)
            if (existing !== -1) {
                this.accounts[existing] = { token, user }
                this.activeAccountIndex = existing
            } else {
                this.accounts.push({ token, user })
                this.activeAccountIndex = this.accounts.length - 1
            }
            this.persist()
            this.setAxiosHeader()
        }
    }
})
