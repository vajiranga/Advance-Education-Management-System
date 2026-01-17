import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

export const useStudentStore = defineStore('student', () => {
    const myCourses = ref([])
    const allCourses = ref([])
    const attendanceHistory = ref([])
    const loading = ref(false)

    async function fetchAttendanceHistory() {
        loading.value = true
        try {
            const res = await api.get('/v1/attendance/my-history')
            attendanceHistory.value = res.data
        } catch (e) {
            console.error(e)
            attendanceHistory.value = []
        } finally {
            loading.value = false
        }
    }

    async function fetchMyCourses() {
        loading.value = true
        try {
            const res = await api.get('/v1/my-courses')
            myCourses.value = res.data.data || res.data // Support paginated or simple list
        } catch (e) {
            console.error('Fetch my courses failed', e)
        } finally {
            loading.value = false
        }
    }

    async function fetchAllCourses() {
        loading.value = true
        try {
            // Fetch all approved courses
            const res = await api.get('/v1/courses?status=approved') // Explicitly request approved courses
            allCourses.value = res.data.data || res.data
        } catch (e) {
            console.error('Fetch all courses failed', e)
        } finally {
            loading.value = false
        }
    }

    async function enroll(courseId) {
        try {
            await api.post('/v1/enroll', { course_id: courseId })
            await fetchMyCourses()
            return true
        } catch (e) {
            console.error(e)
            return false
        }
    }

    const dashboardData = ref({ upcoming: [], recent: [] })

    async function fetchDashboard() {
        loading.value = true
        try {
            const res = await api.get('/v1/attendance/dashboard')
            dashboardData.value = res.data
        } catch (e) {
            console.error('Fetch dashboard failed', e)
        } finally {
            loading.value = false
        }
    }

    return { myCourses, allCourses, attendanceHistory, dashboardData, fetchDashboard, fetchMyCourses, fetchAttendanceHistory, fetchAllCourses, enroll, loading }
})
