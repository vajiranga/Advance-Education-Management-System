import { defineStore } from 'pinia'
import { api } from 'src/services/api'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    instituteName: localStorage.getItem('instituteName') || '',
    appName: localStorage.getItem('appName') || '',
    instituteLogo: localStorage.getItem('instituteLogo') || null,
    disableTeacherAttendance: localStorage.getItem('disableTeacherAttendance') === 'true',
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
          if (response.data.disableTeacherAttendance !== undefined) {
             const val = response.data.disableTeacherAttendance
             this.disableTeacherAttendance = (val === 'true' || val === true || val === '1' || val === 1)
             localStorage.setItem('disableTeacherAttendance', this.disableTeacherAttendance)
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
