import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

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

    async function deleteHall(id) {
        await api.delete(`/v1/halls/${id}`)
        await fetchHalls()
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
