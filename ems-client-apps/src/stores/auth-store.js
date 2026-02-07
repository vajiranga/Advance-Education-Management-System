import { defineStore } from 'pinia'
import { api } from 'src/services/api'

export const useAuthStore = defineStore('auth', {
    state: () => {
        // Robustly parse accounts
        let accounts = [];
        try {
            const stored = localStorage.getItem('auth_accounts');
            if (stored) accounts = JSON.parse(stored);
        } catch (e) {
            console.warn('Failed to parse auth_accounts, resetting...', e);
            localStorage.removeItem('auth_accounts');
        }

        // SAFETY: Filter out bad data from local storage to prevent finding 'null' users later
        if (!Array.isArray(accounts)) accounts = [];
        accounts = accounts.filter(a => a && a.user && a.token);

        let index = parseInt(localStorage.getItem('auth_active_index') || '0');
        if (Number.isNaN(index) || index < 0 || index >= accounts.length) {
            index = 0;
        }

        return {
            accounts: accounts,
            activeAccountIndex: index,
            selectedChild: null,
            // REMOVED 'token' and 'user' from state to fix Pinia Getter Conflict
        }
    },

    getters: {
        user: (state) => state.accounts[state.activeAccountIndex]?.user || null,
        token: (state) => state.accounts[state.activeAccountIndex]?.token || null,
        allAccounts: (state) => state.accounts,
        currentChild: (state) => state.selectedChild,
        isAuthenticated: (state) => !!(state.accounts[state.activeAccountIndex]?.token),
        getUser: (state) => state.accounts[state.activeAccountIndex]?.user || null,
        getUserRole: (state) => state.accounts[state.activeAccountIndex]?.user?.role || 'guest'
    },

    actions: {
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

        // --- COMPATIBILITY METHODS ---
        // These bridge the gap between simple login calls and your multi-account system
        setToken(token) {
            // We need a user object to add an account properly.
            // If called in isolation, we might only check if current account needs update
            if (this.accounts[this.activeAccountIndex]) {
                this.accounts[this.activeAccountIndex].token = token;
                this.persist();
                this.setAxiosHeader();
            }
        },

        setUser(user) {
             if (this.accounts[this.activeAccountIndex]) {
                this.accounts[this.activeAccountIndex].user = user;
                this.persist();
            }
        },
        // -----------------------------

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
                this.addAccount({ user: res.data, token })
                window.location.reload()
            } catch (e) {
                console.error('Failed to add account', e)
            }
        },

        addAccount({ user, token }) {
            // FIX: Ensure user object is valid
            if (!user || !user.id) {
                console.error("AddAccount error: Invalid user data", user);
                return;
            }

            // FIX: Safely check existing accounts (check a.user exists)
            const existing = this.accounts.findIndex(a => a.user && a.user.id === user.id)

            if (existing !== -1) {
                this.accounts[existing] = { token, user }
                this.activeAccountIndex = existing
            } else {
                if (this.accounts.length >= 4) {
                    this.accounts.shift()
                }
                this.accounts.push({ token, user })
                this.activeAccountIndex = this.accounts.length - 1
            }
            this.persist()
            this.setAxiosHeader()
        },

        switchAccount(index) {
            if (index >= 0 && index < this.accounts.length) {
                this.activeAccountIndex = index
                this.persist() // Critical: Save the index immediately
                this.setAxiosHeader()

                // Force navigation to the dashboard of the NEW account role
                const newRole = this.accounts[index].user.role;
                let target = '/';
                if (newRole === 'student') target = '/student/dashboard';
                else if (newRole === 'teacher') target = '/teacher/dashboard';
                else if (newRole === 'parent') target = '/parent/dashboard';

                // We use href to force a clean full reload at the new location
                // This prevents state from the previous user leaking
                window.location.href = '#' + target;
                window.location.reload();
            }
        },

        updateUserInAccount(user) {
            if (this.accounts[this.activeAccountIndex]) {
                this.accounts[this.activeAccountIndex].user = user
                this.persist()
            }
        },

        logout(redirectPath = '/') {
            // Remove ONLY the current account to prevent logging out other users (e.g., Parent)
            if (this.accounts.length > 0) {
                this.accounts.splice(this.activeAccountIndex, 1)
            }

            // Reset index to ensure it points to a valid account if any remain
            this.activeAccountIndex = 0
            this.persist()

            // Redirect to specified path (default: Login)
            window.location.href = redirectPath
        },

        persist() {
            localStorage.setItem('auth_accounts', JSON.stringify(this.accounts))
            localStorage.setItem('auth_active_index', this.activeAccountIndex)
        },

        setAxiosHeader() {
            // Use 'this.token' which accesses the getter
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
                    message: e.response?.data?.message || 'Login failed.'
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
                console.error(e)
                return { success: false, message: 'Parent Login Failed.' }
            }
        }
    }
})
