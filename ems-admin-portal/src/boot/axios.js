import { boot } from 'quasar/wrappers'
import axios from 'axios'

const api = axios.create({ baseURL: 'http://localhost:8000/api' })

import { useAuthStore } from 'stores/auth-store'

export default boot(({ app, store }) => {
    app.config.globalProperties.$axios = axios
    app.config.globalProperties.$api = api

    const authStore = useAuthStore(store)
    authStore.init()
})

export { api }
