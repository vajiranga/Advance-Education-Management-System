import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'src/services/api'

export const useUserStore = defineStore('user', () => {

    const teachers = ref([])
    const students = ref([])
    const parents = ref([])
    const loading = ref(false)

    async function fetchTeachers() {
        loading.value = true
        try {
            const res = await api.get('/v1/users', { params: { role: 'teacher' } })
            teachers.value = res.data.data
        } catch (err) {
            console.error(err)
        } finally {
            loading.value = false
        }
    }

    async function fetchStudents(params = {}) {
        loading.value = true
        try {
            // Default params
            const query = {
                role: 'student',
                page: params.page || 1,
                per_page: params.rowsPerPage || 20,
                search: params.filter || ''
            }
            const res = await api.get('/v1/users', { params: query })
            students.value = res.data.data
            return res.data // Return full object for pagination meta
        } catch (err) {
            console.error(err)
            return null
        } finally {
            loading.value = false
        }
    }

    async function fetchParents(params = {}) {
        loading.value = true
        try {
            const query = {
                role: 'parent',
                page: params.page || 1,
                per_page: params.rowsPerPage || 20,
                search: params.filter || ''
            }
            const res = await api.get('/v1/users', { params: query })
            parents.value = res.data.data
            return res.data
        } catch (err) {
            console.error(err)
            return null
        } finally {
            loading.value = false
        }
    }

    // Actions to add (mock, but could be real POST)
    // For now we assume registration happens via Landing Page or Modal which should call API.
    // I will update the saveUser in UsersPage to call API instead of push.

    // KEEPING these for compatibility if needed, but ideally we should save to DB
    async function addStudent(studentData) {
        // This should engage with the register API or similar
        // For now, let's just re-fetch or push manually
        students.value.push(studentData)
    }

    return {
        teachers,
        students,
        parents,
        loading,
        fetchTeachers,
        fetchStudents,
        fetchParents,
        addStudent
    }
})
