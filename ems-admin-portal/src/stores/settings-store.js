import { defineStore } from 'pinia'
import { api } from 'src/services/api'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    instituteName: localStorage.getItem('instituteName') || '',
    appName: localStorage.getItem('appName') || '',
    instituteLogo: localStorage.getItem('instituteLogo') || null,
    loading: false
  }),

  actions: {
    async fetchPublicSettings() {
      this.loading = true
      try {
        const response = await api.get('/v1/settings/config')
        if (response.data) {
          if (response.data.instituteName) {
            this.instituteName = response.data.instituteName
            localStorage.setItem('instituteName', this.instituteName)
          }
          if (response.data.appName) {
            this.appName = response.data.appName
            localStorage.setItem('appName', this.appName)
          }
          if (response.data.instituteLogo) {
            this.instituteLogo = response.data.instituteLogo
            localStorage.setItem('instituteLogo', this.instituteLogo)
          }
        }
      } catch (error) {
        console.error('Failed to fetch public settings:', error)
      } finally {
        this.loading = false
      }
    }
  }
})
