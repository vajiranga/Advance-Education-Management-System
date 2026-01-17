import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const usePaymentStore = defineStore('payment', () => {
    const loading = ref(false)
    const history = ref([])
    const pending = ref([])

    async function fetchHistory(studentId = null) {
        loading.value = true
        try {
            const url = studentId ? `/v1/parent/children/${studentId}/payments` : '/v1/my-payments'
            const res = await api.get(url)
            history.value = res.data.data ? res.data.data : res.data
        } catch (e) {
            console.error('Fetch payments failed', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchPendingFees(studentId = null) {
        loading.value = true
        try {
            const url = studentId ? `/v1/parent/children/${studentId}/fees` : '/v1/my-due-fees'
            const res = await api.get(url)
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
            const isFormData = payload instanceof FormData
            await api.post('/v1/payments', payload, {
                headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {}
            })
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
