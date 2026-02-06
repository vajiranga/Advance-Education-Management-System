import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'
import { useAuthStore } from './auth-store'

export const useTeacherStore = defineStore('teacher', () => {
    const loading = ref(false)
    const courses = ref([])

    async function createClass(courseData) {
        loading.value = true
        try {
            const res = await api.post('/v1/courses', courseData)
            return { success: true, data: res.data }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error occurred' }
        } finally {
            loading.value = false
        }
    }

    async function updateClass(id, payload) {
        loading.value = true
        try {
            await api.put(`/v1/courses/${id}`, payload)
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error occurred' }
        } finally {
            loading.value = false
        }
    }

    async function deleteClass(id) {
        loading.value = true
        try {
            await api.delete(`/v1/courses/${id}`)
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error occurred' }
        } finally {
            loading.value = false
        }
    }

    async function fetchCourses(params = {}) {
        const auth = useAuthStore()
        if (!auth.token) return

        loading.value = true
        try {
            const res = await api.get('/v1/courses', { params })
            courses.value = res.data.data ? res.data.data : res.data
        } catch (e) {
            console.error('Fetch courses failed', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchBatches() {
        try {
            // Use APIv1 prefix if configured in axios boot, else /api/v1/...
            // boot/axios usually sets base url to /api. So /v1/batches
            // Wait, api.php uses 'v1' prefix group.
            const res = await api.get('/v1/batches')
            return res.data
        } catch (e) {
            console.error('Fetch batches failed', e)
            return []
        }
    }

    async function fetchSubjects() {
        try {
            const res = await api.get('/v1/subjects')
            return res.data
        } catch (e) {
            console.error('Fetch subjects failed', e)
            return []
        }
    }

    async function checkHallAvailability(params) {
        try {
            const res = await api.post('/v1/halls/check', params)
            return res.data
        } catch (e) {
            console.error(e)
            return []
        }
    }

    async function fetchStudentsForAttendance(courseId, date) {
        try {
            const res = await api.get('/v1/attendance/students', { params: { course_id: courseId, date } })
            return res.data // { data: [...] }
        } catch (e) {
            console.error(e)
            return { data: [] }
        }
    }

    async function saveAttendance(payload) {
        try {
            // Check if it's a bulk payload (has 'attendances' array)
            if (payload.attendances) {
                await api.post('/v1/attendance/bulk', payload)
            } else {
                await api.post('/v1/attendance', payload)
            }
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error' }
        }
    }

    return { createClass, updateClass, deleteClass, fetchCourses, fetchBatches, fetchSubjects, checkHallAvailability, fetchStudentsForAttendance, saveAttendance, loading, courses }
})
