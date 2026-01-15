import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const useTeacherStore = defineStore('teacher', () => {
    const loading = ref(false)
    const courses = ref([])

    async function createClass(courseData) {
        loading.value = true
        try {
            await api.post('/v1/courses', courseData)
            return { success: true }
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
            return res.data
        } catch (e) {
            console.error(e)
            return { students: [] }
        }
    }

    async function saveAttendance(payload) {
        try {
            await api.post('/v1/attendance', payload)
            return { success: true }
        } catch (e) {
            console.error(e)
            return { success: false, error: e.response?.data?.message || 'Error' }
        }
    }

    return { createClass, updateClass, deleteClass, fetchCourses, fetchBatches, fetchSubjects, checkHallAvailability, fetchStudentsForAttendance, saveAttendance, loading, courses }
})
