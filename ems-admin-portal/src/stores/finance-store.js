import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const useFinanceStore = defineStore('finance', () => {
    const transactions = ref([])
    const pendingTransactions = ref([])
    const stats = ref({
        revenue: 0,
        paid_teachers: 0,
        pending_fees: 0
    })
    const loading = ref(false)

    async function fetchTransactions(params = {}) {
        loading.value = true
        try {
            const res = await api.get('/v1/admin/payments/summary', { params })

            // Handle custom response format with stats
            if (res.data.stats) {
                transactions.value = res.data.data
                // Pending from list is still useful for table interactive buttons
                pendingTransactions.value = res.data.data.filter(t => t.status === 'pending')

                // Update global stats from backend
                stats.value.revenue = parseFloat(res.data.stats.total_revenue || 0)
                stats.value.pending_fees = parseFloat(res.data.stats.uncollected_fees || 0)
                // pending_count might be useful if we add a counter for it

            } else {
                // Fallback for old format (paginated directly)
                const all = res.data.data || res.data
                transactions.value = all
                pendingTransactions.value = all.filter(t => t.status === 'pending')
                // No accurate global stats in fallback
            }
        } catch (e) {
            console.error(e)
        } finally {
            loading.value = false
        }
    }

    async function approvePayment(id) {
        await api.post(`/v1/payments/${id}/approve`)
        await fetchTransactions()
    }

    async function rejectPayment(id, note) {
        await api.post(`/v1/payments/${id}/reject`, { note })
        await fetchTransactions()
    }

    const analyticsData = ref({ monthly: [], courses: [], methods: [] })

    async function fetchAnalytics() {
        try {
            const res = await api.get('/v1/admin/payments/analytics')
            analyticsData.value.monthly = res.data.monthly_revenue || []
            analyticsData.value.courses = res.data.course_revenue || []
            analyticsData.value.methods = res.data.payment_methods || []
        } catch (e) {
            console.error('Analytics fetch failed', e)
        }
    }

    async function exportReport(month) {
        try {
            const res = await api.get('/v1/admin/payments/export', { params: { month } })
            // Trigger Download
            const blob = new Blob([res.data.content], { type: 'text/csv' })
            const url = window.URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = res.data.file_name
            a.click()
            window.URL.revokeObjectURL(url)
            return true
        } catch (e) {
            console.error(e)
            return false
        }
    }

    async function generateFees(payload) {
        try {
            const res = await api.post('/v1/admin/payments/generate', payload)
            // Ideally trigger refresh of pending fees if admin view supports it (though this affects students)
            return { success: true, count: res.data.count, message: res.data.message }
        } catch (e) {
            return { success: false, error: e.response?.data?.message || 'Generation Failed' }
        }
    }

    const settlements = ref([])
    async function fetchSettlements(params = {}) {
        try {
            const res = await api.get('/v1/admin/payments/settlements', { params })
            settlements.value = res.data
        } catch (e) {
            console.error(e)
        }
    }

    const uncollectedFees = ref([])

    async function fetchUncollectedFees() {
        try {
            const res = await api.get('/v1/admin/payments/pending-list')
            uncollectedFees.value = res.data
        } catch (e) {
            console.error(e)
        }
    }

    async function recordCashPayment(payload) {
        try {
            const res = await api.post('/v1/admin/payments/record-cash', payload)
            return { success: true, message: res.data.message, payment: res.data.payment }
        } catch (e) {
            return { success: false, error: e.response?.data?.message || 'Recording Failed' }
        }
    }

    async function recordBatchPayment(payload) {
        try {
            // Re-using the standard payment store endpoint which supports fee_ids array
            const res = await api.post('/v1/payments', payload)
            return { success: true, message: 'Payment Recorded', payment: res.data.payment } // Standard response might vary, usually assumes success
        } catch (e) {
             console.error(e)
             return { success: false, error: e.response?.data?.message || e.response?.data?.errors || 'Recording Failed' }
        }
    }

    return {
        transactions,
        pendingTransactions,
        analyticsData,
        settlements,
        stats,
        loading,
        fetchTransactions,
        fetchAnalytics,
        fetchSettlements,
        fetchUncollectedFees,
        exportReport,
        generateFees,
        recordCashPayment,
        recordBatchPayment,
        approvePayment,
        rejectPayment,
        uncollectedFees
    }
})
