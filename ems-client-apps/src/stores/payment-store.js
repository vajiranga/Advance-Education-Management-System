import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const usePaymentStore = defineStore('payment', () => {
    const loading = ref(false)
    const history = ref([])
    const pending = ref([])

    async function fetchHistory() {
        loading.value = true
        try {
            const res = await api.get('/v1/my-payments')
            history.value = res.data.data ? res.data.data : res.data
        } catch (e) {
            console.error('Fetch payments failed', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchPendingFees() {
        loading.value = true
        try {
            const res = await api.get('/v1/my-due-fees')
            pending.value = res.data
        } catch (e) {
            console.error(e)
        } finally {
            loading.value = false
        }
    }

    async function makePayment(payload) {
        loading.value = true
        try {
            await api.post('/v1/payments', payload)
            // Refresh
            await fetchPendingFees()
            await fetchHistory()
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error processing payment' }
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        history,
        pending,
        fetchHistory,
        fetchPendingFees,
        makePayment
    }
})
