import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const useCourseStore = defineStore('course', () => {
    const courses = ref([])
    const loading = ref(false)

    async function fetchCourses() {
        loading.value = true
        try {
            const res = await api.get('/courses')
            courses.value = res.data.data ? res.data.data : res.data // Handle paginate or collection
        } catch (e) {
            console.error('Fetch courses failed', e)
        } finally {
            loading.value = false
        }
    }

    async function addCourse(course) {
        await api.post('/courses', course)
        await fetchCourses()
    }

    async function updateStatus(id, status, note) {
        await api.put(`/courses/${id}/status`, { status, admin_note: note })
        await fetchCourses()
    }

    async function deleteCourse(id) {
        await api.delete(`/courses/${id}`)
        await fetchCourses()
    }

    return { courses, loading, fetchCourses, addCourse, updateStatus, deleteCourse }
})
