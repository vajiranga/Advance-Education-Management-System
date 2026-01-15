import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const useCourseStore = defineStore('course', () => {
    const courses = ref([])
    const subjects = ref([])
    const batches = ref([])
    const halls = ref([])
    const loading = ref(false)

    async function fetchCourses(params = {}) {
        loading.value = true
        try {
            const res = await api.get('/v1/courses', { params })
            courses.value = res.data.data ? res.data.data : res.data // Handle paginate or collection
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

    async function addCourse(course) {
        await api.post('/v1/courses', course)
        await fetchCourses()
    }

    async function updateCourse(id, course) {
        await api.put(`/v1/courses/${id}`, course)
        await fetchCourses()
    }

    async function updateStatus(id, status, note) {
        await api.put(`/v1/courses/${id}/status`, { status, admin_note: note })
        await fetchCourses()
    }

    async function deleteCourse(id) {
        await api.delete(`/v1/courses/${id}`)
        await fetchCourses()
    }

    async function bulkAction(action, ids) {
        await api.post('/v1/courses/bulk', { action, ids })
        await fetchCourses()
    }

    async function fetchStudents(courseId) {
        const res = await api.get(`/v1/courses/${courseId}/students`) // Custom route or added method
        return res.data.data || res.data
    }

    async function enrollStudent(courseId, userId) {
        await api.post('/v1/enroll', { course_id: courseId, user_id: userId })
    }

    return {
        courses, subjects, batches, halls, loading,
        fetchCourses, fetchMetadata, addCourse, updateStatus, deleteCourse, bulkAction, updateCourse,
        fetchStudents, enrollStudent
    }
})
// Forced HMR Update
