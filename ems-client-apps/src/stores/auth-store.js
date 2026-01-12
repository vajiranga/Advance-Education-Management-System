import { defineStore } from 'pinia'
import { api } from 'boot/axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        // accounts: Array of { token, user }
        accounts: JSON.parse(localStorage.getItem('auth_accounts')) || [],
        activeAccountIndex: parseInt(localStorage.getItem('auth_active_index') || 0)
    }),

    getters: {
        user: (state) => state.accounts[state.activeAccountIndex]?.user || null,
        token: (state) => state.accounts[state.activeAccountIndex]?.token || null,
        allAccounts: (state) => state.accounts
    },

    actions: {
        // Called when MainLayout mounts or on app init
        init() {
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
                    window.location.href = 'http://localhost:9000/login'
                } else {
                    window.location.reload()
                }
            } else {
                window.location.href = 'http://localhost:9000/login'
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
        }
    }
})
