import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useUserStore = defineStore('user', () => {
    // Initialize from LocalStorage or Default Mock Data
    const savedTeachers = localStorage.getItem('ems_teachers')
    const savedStudents = localStorage.getItem('ems_students')
    const savedParents = localStorage.getItem('ems_parents')

    const teachers = ref(savedTeachers ? JSON.parse(savedTeachers) : [
        { id: 1, name: 'Mr. Bandara', email: 'bandara@school.com', subject: 'Physics', is_active: true },
        { id: 2, name: 'Mrs. Silva', email: 'silva@school.com', subject: 'Chemistry', is_active: true }
    ])

    const students = ref(savedStudents ? JSON.parse(savedStudents) : [
        { id: 101, barcode: '8821', name: 'Kasun Perera', batch: '2026 A/L', parent_name: 'Sunil Perera', parent_phone: '0771234567' },
        { id: 102, barcode: '8822', name: 'Nimali Silva', batch: '2027 A/L', parent_name: 'Kamal Silva', parent_phone: '0719876543' }
    ])

    const parents = ref(savedParents ? JSON.parse(savedParents) : [
        { id: 501, name: 'Sunil Perera', phone: '0771234567', children: ['Kasun Perera'] },
        { id: 502, name: 'Kamal Silva', phone: '0719876543', children: ['Nimali Silva', 'Amal Silva'] }
    ])

    // Watch & Save to LocalStorage
    watch(teachers, (val) => localStorage.setItem('ems_teachers', JSON.stringify(val)), { deep: true })
    watch(students, (val) => localStorage.setItem('ems_students', JSON.stringify(val)), { deep: true })
    watch(parents, (val) => localStorage.setItem('ems_parents', JSON.stringify(val)), { deep: true })

    // Actions
    function addTeacher(teacher) {
        teachers.value.push(teacher)
    }

    function addStudent(student) {
        students.value.push(student)
    }

    function addParent(parent) {
        parents.value.push(parent)
    }

    return { teachers, students, parents, addTeacher, addStudent, addParent }
})
