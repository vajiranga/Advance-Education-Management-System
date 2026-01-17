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
            // Logic to separate pending vs completed or just store all
            // Ideally backend returns paginated. For now assuming simple list.
            const all = res.data.data

            transactions.value = all
            pendingTransactions.value = all.filter(t => t.status === 'pending')

            // Calculate stats locally for now (Backend should ideally provide this)
            stats.value.revenue = all.filter(t => t.status === 'paid').reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)
            stats.value.pending_fees = all.filter(t => t.status === 'pending').reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)

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

    const analyticsData = ref({ monthly: [], courses: [] })

    async function fetchAnalytics() {
        try {
            const res = await api.get('/v1/admin/payments/analytics')
            analyticsData.value.monthly = res.data.monthly_revenue || []
            analyticsData.value.courses = res.data.course_revenue || []
        } catch (e) {
            console.error('Analytics fetch failed', e)
        }
    }

    async function downloadReport(month) {
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
    async function fetchSettlements() {
        try {
            const res = await api.get('/v1/admin/payments/settlements')
            settlements.value = res.data
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
        downloadReport,
        generateFees,
        recordCashPayment,
        approvePayment,
        rejectPayment
    }
})
