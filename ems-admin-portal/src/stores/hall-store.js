import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useHallStore = defineStore('hall', () => {
    // Generate Halls 01-20 with varying capacities
    const halls = ref(Array.from({ length: 20 }, (_, i) => ({
        id: i + 1,
        name: `Hall ${String(i + 1).padStart(2, '0')}`,
        capacity: i < 5 ? 30 : (i < 15 ? 50 : 100), // Halls 1-5: 30, 6-15: 50, 16-20: 100
        facilities: ['AC', 'Projector']
    })))

    return { halls }
})
