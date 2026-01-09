import { defineStore } from 'pinia'
import { api } from 'boot/axios'

export const useTenantStore = defineStore('tenant', {
    state: () => ({
        tenants: [],
        loading: false
    }),

    actions: {
        async fetchTenants() {
            this.loading = true
            try {
                const response = await api.get('/v1/tenants')
                this.tenants = response.data
            } catch (error) {
                console.error('Error fetching tenants:', error)
                throw error
            } finally {
                this.loading = false
            }
        },

        async createTenant(tenantData) {
            this.loading = true
            try {
                const response = await api.post('/v1/tenants', tenantData)
                this.tenants.push(response.data.tenant)
                return response.data
            } catch (error) {
                console.error('Error creating tenant:', error)
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})
