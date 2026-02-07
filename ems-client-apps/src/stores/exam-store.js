import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'src/services/api'

export const useExamStore = defineStore('exam', () => {
    const loading = ref(false)
    const exams = ref([])

    async function fetchExams(params = {}) {
        loading.value = true
        try {
            const res = await api.get('/v1/exams', { params })
            exams.value = res.data.data ? res.data.data : res.data
        } catch (e) {
            console.error('Fetch exams failed', e)
        } finally {
            loading.value = false
        }
    }

    async function createExam(payload) {
        loading.value = true
        try {
            const res = await api.post('/v1/exams', payload)
            // Refresh list
            return { success: true, data: res.data.exam }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error' }
        } finally {
            loading.value = false
        }
    }

    async function updateExam(id, payload) {
        loading.value = true
        try {
            await api.put(`/v1/exams/${id}`, payload)
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error' }
        } finally {
            loading.value = false
        }
    }

    async function fetchResults(examId) {
        loading.value = true
        try {
            const res = await api.get(`/v1/exams/${examId}/results`)
            return res.data // { exam: {...}, students: [...] }
        } catch (e) {
            console.error(e)
            return null
        } finally {
            loading.value = false
        }
    }

    async function saveResults(examId, payload) {
        loading.value = true
        try {
            await api.post(`/v1/exams/${examId}/results`, payload)
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error' }
        } finally {
            loading.value = false
        }
    }

    async function fetchMyExams() {
        loading.value = true
        try {
            const res = await api.get('/v1/my-exams')
            return res.data // { upcoming: [], results: [] }
        } catch (e) {
            console.error(e)
            return { upcoming: [], results: [] }
        } finally {
            loading.value = false
        }
    }

    return {
        loading,
        exams,
        fetchExams,
        createExam,
        updateExam,
        fetchResults,
        saveResults,
        fetchMyExams
    }
})
