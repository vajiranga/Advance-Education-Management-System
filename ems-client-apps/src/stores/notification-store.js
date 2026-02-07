import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'src/services/api'
import { date } from 'quasar'

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)

    async function fetchNotifications() {
        loading.value = true
        try {
            const res = await api.get('/v1/notifications')
            notifications.value = res.data.data.map(n => ({
                id: n.id,
                title: n.title,
                message: n.message,
                type: n.type, // fee_due, payment_success, etc
                time: formatDate(n.created_at),
                read_at: n.read_at,
                data: n.data // JSON/Object
            }))

            unreadCount.value = res.data.meta.unread_count ||
                notifications.value.filter(n => !n.read_at).length

        } catch (e) {
            console.error('Failed to fetch notifications', e)
        } finally {
            loading.value = false
        }
    }

    async function markAsRead(id) {
        try {
            await api.post(`/v1/notifications/${id}/read`)
            const target = notifications.value.find(n => n.id === id)
            if (target && !target.read_at) {
                target.read_at = new Date().toISOString()
                unreadCount.value = Math.max(0, unreadCount.value - 1)
            }
        } catch (e) {
            console.error(e)
        }
    }

    async function markAllRead() {
        try {
            await api.post('/v1/notifications/mark-read')
            notifications.value.forEach(n => n.read_at = new Date().toISOString())
            unreadCount.value = 0
        } catch (e) {
            console.error(e)
        }
    }

    // Helper for "time ago"
    function formatDate(isoStr) {
        if (!isoStr) return ''
        const diff = date.getDateDiff(new Date(), new Date(isoStr), 'minutes')
        if (diff < 1) return 'Just now'
        if (diff < 60) return `${diff} mins ago`

        const hours = date.getDateDiff(new Date(), new Date(isoStr), 'hours')
        if (hours < 24) return `${hours} hours ago`

        return date.formatDate(isoStr, 'MMM D, YYYY')
    }

    return {
        notifications,
        unreadCount,
        loading,
        fetchNotifications,
        markAsRead,
        markAllRead
    }
})
