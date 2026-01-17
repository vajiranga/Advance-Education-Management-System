import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from 'boot/axios'

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

    async function fetchStudents() {
        loading.value = true
        try {
            const res = await api.get('/v1/users', { params: { role: 'student' } })
            students.value = res.data.data
        } catch (err) {
            console.error(err)
        } finally {
            loading.value = false
        }
    }

    async function fetchParents() {
        loading.value = true
        try {
            const res = await api.get('/v1/users', { params: { role: 'parent' } })
            parents.value = res.data.data
        } catch (err) {
            console.error(err)
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
