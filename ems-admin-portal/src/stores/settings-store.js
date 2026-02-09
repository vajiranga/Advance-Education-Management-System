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
          if (response.data.logoUrl) {
           let url = response.data.logoUrl

           // Force 127.0.0.1:8000 for local development to avoid port mismatch issues
           if (url.includes('localhost') || url.includes('127.0.0.1')) {
                let cleanPath = url.replace(/^https?:\/\/[^/]+/, '')
                if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath
                url = 'http://127.0.0.1:8000' + cleanPath
           }
           else if (url.startsWith('/')) {
                url = 'http://127.0.0.1:8000' + url
           }

            this.logoUrl = url
            localStorage.setItem('logoUrl', this.logoUrl)
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
