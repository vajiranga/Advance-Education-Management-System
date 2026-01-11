import { boot } from 'quasar/wrappers'
import { api } from 'boot/axios'

export default boot(({ router }) => {
    router.beforeEach((to, from, next) => {
        // Check if token is in the URL query parameters
        const urlParams = new URLSearchParams(window.location.search)
        const tokenFromUrl = urlParams.get('token')

        if (tokenFromUrl) {
            // Save token to localStorage
            localStorage.setItem('auth_token', tokenFromUrl)

            // Set default auth header
            api.defaults.headers.common['Authorization'] = 'Bearer ' + tokenFromUrl

            // Remove token from URL for cleaner look
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.hash
            window.history.replaceState({ path: newUrl }, '', newUrl)
        } else {
            // If no token in URL, check localStorage
            const storedToken = localStorage.getItem('auth_token')
            if (storedToken) {
                api.defaults.headers.common['Authorization'] = 'Bearer ' + storedToken
            }
        }

        next()
    })
})
