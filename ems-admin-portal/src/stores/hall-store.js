import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'src/services/api'

export const useHallStore = defineStore('hall', () => {
    const halls = ref([])
    const loading = ref(false)

    async function fetchHalls() {
        loading.value = true
        try {
            const res = await api.get('/v1/halls')
            halls.value = res.data
        } catch (e) {
            console.error('Fetch halls failed', e)
        } finally {
            loading.value = false
        }
    }

    async function addHall(hall) {
        await api.post('/v1/halls', hall)
        await fetchHalls()
    }

    async function updateHall(id, hall) {
        await api.put(`/v1/halls/${id}`, hall)
        await fetchHalls()
    }

    async function deleteHall(id, force = false) {
        try {
            const url = force ? `/v1/halls/${id}?force=true` : `/v1/halls/${id}`
            await api.delete(url)
            await fetchHalls()
        } catch (e) {
            // If backend returns a message (like 409 conflict), pass it up
            if (e.response && e.response.data) {
                 // Pass the full data object so UI can check for 'confirmation_needed'
                const error = new Error(e.response.data.message || 'Deletion Failed')
                error.data = e.response.data
                throw error
            }
            throw e // Rethrow generic error
        }
    }

    return {
        halls,
        loading,
        fetchHalls,
        addHall,
        updateHall,
        deleteHall
    }
})
