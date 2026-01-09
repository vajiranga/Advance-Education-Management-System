import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useCourseStore = defineStore('course', () => {
    const savedCourses = localStorage.getItem('ems_courses')

    const courses = ref(savedCourses ? JSON.parse(savedCourses) : [
        { id: 1, grade: 'Grade 10', subject: 'Mathematics', name: 'G10 Maths Theory', teacher: 'Mr. Bandara', students: 45, max_students: 50, fee: 2500, schedule: 'Sat 8:00 AM', type: 'Physical' },
        { id: 2, grade: 'Grade 11', subject: 'Science', name: 'G11 Science Revision', teacher: 'Mrs. Silva', students: 98, max_students: 100, fee: 3000, schedule: 'Sun 10:00 AM', type: 'Hybrid' },
        { id: 3, grade: 'Grade 6', subject: 'English', name: 'Spoken English', teacher: 'Mr. Perera', students: 12, max_students: 40, fee: 1500, schedule: 'Mon 4:00 PM', type: 'Online' },
    ])

    watch(courses, (val) => localStorage.setItem('ems_courses', JSON.stringify(val)), { deep: true })

    function addCourse(course) {
        courses.value.push(course)
    }

    function deleteCourse(id) {
        courses.value = courses.value.filter(c => c.id !== id)
    }

    return { courses, addCourse, deleteCourse }
})
