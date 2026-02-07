import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'src/services/api'

export const useCourseStore = defineStore('course', () => {
    const courses = ref([])
    const subjects = ref([])
    const batches = ref([])
    const halls = ref([])
    const loading = ref(false)

    async function fetchCourses(params = {}) {
        loading.value = true
        try {
            // Add cache buster
            const queryParams = { ...params, _t: Date.now() }
            const res = await api.get('/v1/courses', { params: queryParams })
            courses.value = res.data.data ? res.data.data : res.data
        } catch (e) {
            console.error('Fetch courses failed', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchMetadata() {
        try {
            const [subRes, batchRes, hallRes] = await Promise.all([
                api.get('/v1/subjects'),
                api.get('/v1/batches'),
                api.get('/v1/halls')
            ])
            subjects.value = subRes.data
            batches.value = batchRes.data
            halls.value = hallRes.data
        } catch (e) {
            console.error('Fetch metadata failed', e)
        }
    }

    async function addCourse(course, params = {}) {
        await api.post('/v1/courses', course)
        await fetchCourses(params)
    }

    async function updateCourse(id, course, params = {}) {
        const res = await api.put(`/v1/courses/${id}`, course)

        // Manual Local Update to ensure UI reflects change immediately
        const updatedCourse = res.data.course
        const index = courses.value.findIndex(c => c.id === id)
        if (index !== -1 && updatedCourse) {
            // Merge or replace
            courses.value[index] = { ...courses.value[index], ...updatedCourse }
            // If relations (batch, subject) were sent as IDs only, we might lose nested objects in the view
            // So we still do a fetch, but this provides immediate feedback for basics
        }

        await fetchCourses(params)
    }

    async function updateStatus(id, status, note, params = {}) {
        await api.put(`/v1/courses/${id}/status`, { status, admin_note: note })
        await fetchCourses(params)
    }

    async function deleteCourse(id, params = {}) {
        await api.delete(`/v1/courses/${id}`)
        await fetchCourses(params)
    }

    async function bulkAction(action, ids, params = {}) {
        await api.post('/v1/courses/bulk', { action, ids })
        await fetchCourses(params)
    }

    async function fetchStudents(courseId) {
        const res = await api.get(`/v1/courses/${courseId}/students`) // Custom route or added method
        return res.data.data || res.data
    }

    async function enrollStudent(courseId, userId) {
        await api.post('/v1/enroll', { course_id: courseId, user_id: userId })
    }

    async function bulkEnroll(courseId, userIds) {
        // Create array of promises
        const promises = userIds.map(uid => api.post('/v1/enroll', { course_id: courseId, user_id: uid }))
        await Promise.all(promises)
    }

    return {
        courses, subjects, batches, halls, loading,
        fetchCourses, fetchMetadata, addCourse, updateStatus, deleteCourse, bulkAction, updateCourse,
        fetchStudents, enrollStudent, bulkEnroll
    }
})
// Forced HMR Update 2
