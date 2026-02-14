import { defineStore } from 'pinia'
import { api } from 'src/services/api'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    instituteName: localStorage.getItem('instituteName') || '',
    appName: localStorage.getItem('appName') || '',
    instituteLogo: localStorage.getItem('instituteLogo') || null,
    logoUrl: localStorage.getItem('logoUrl') || '',
    disableTeacherAttendance: localStorage.getItem('disableTeacherAttendance') === 'true',
    whatsappContact: localStorage.getItem('whatsappContact') || '',
    paymentGateway: localStorage.getItem('paymentGateway') === 'true',
    enableBankTransfer: localStorage.getItem('enableBankTransfer') === 'true',
    instituteAddress: localStorage.getItem('instituteAddress') || '',
    institutePhone: localStorage.getItem('institutePhone') || '',
    instituteEmail: localStorage.getItem('instituteEmail') || '',
    workingDays: [
        { day: 'Monday', isOpen: true, start: '08:00', end: '17:00' },
        { day: 'Tuesday', isOpen: true, start: '08:00', end: '17:00' },
        { day: 'Wednesday', isOpen: true, start: '08:00', end: '17:00' },
        { day: 'Thursday', isOpen: true, start: '08:00', end: '17:00' },
        { day: 'Friday', isOpen: true, start: '08:00', end: '17:00' },
        { day: 'Saturday', isOpen: true, start: '08:00', end: '13:00' },
        { day: 'Sunday', isOpen: false, start: '08:00', end: '13:00' }
    ],
    specialHolidays: [],
    workStartTime: '08:00',
    workEndTime: '17:00',
    loading: false
  }),

  actions: {
    async fetchPublicSettings() {
      // Fix existing broken URL from storage immediately
      if (this.logoUrl && (this.logoUrl.includes('localhost') || this.logoUrl.includes('127.0.0.1'))) {
           let cleanPath = this.logoUrl.replace(/^https?:\/\/[^/]+/, '')
           if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath
           const newUrl = 'http://127.0.0.1:8000' + cleanPath
           if (this.logoUrl !== newUrl) {
               this.logoUrl = newUrl
               localStorage.setItem('logoUrl', newUrl)
           }
      }
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
            this.logoUrl = url
            localStorage.setItem('logoUrl', this.logoUrl)
          }

          // Working Hours & Holidays
          if (response.data.workingDays) {
             try {
                 this.workingDays = JSON.parse(response.data.workingDays)
             } catch { this.workingDays = [] }
          }
          if (response.data.specialHolidays) {
             try {
                 this.specialHolidays = JSON.parse(response.data.specialHolidays)
             } catch { this.specialHolidays = [] }
          }
          if (response.data.workStartTime) this.workStartTime = response.data.workStartTime
          if (response.data.workEndTime) this.workEndTime = response.data.workEndTime
          if (response.data.disableTeacherAttendance !== undefined) {
             const val = response.data.disableTeacherAttendance
             this.disableTeacherAttendance = (val === 'true' || val === true || val === '1' || val === 1)
             localStorage.setItem('disableTeacherAttendance', this.disableTeacherAttendance)
          }
          if (response.data.whatsappContact) {
            this.whatsappContact = response.data.whatsappContact
            localStorage.setItem('whatsappContact', this.whatsappContact)
          }
          if (response.data.paymentGateway !== undefined) {
             const val = response.data.paymentGateway
             this.paymentGateway = (val === 'true' || val === true || val === '1' || val === 1)
             localStorage.setItem('paymentGateway', this.paymentGateway)
          }
          if (response.data.enableBankTransfer !== undefined) {
             const val = response.data.enableBankTransfer
             this.enableBankTransfer = (val === 'true' || val === true || val === '1' || val === 1)
             localStorage.setItem('enableBankTransfer', this.enableBankTransfer)
          }
          if (response.data.instituteAddress) {
            this.instituteAddress = response.data.instituteAddress
            localStorage.setItem('instituteAddress', this.instituteAddress)
          }
          if (response.data.institutePhone) {
            this.institutePhone = response.data.institutePhone
            localStorage.setItem('institutePhone', this.institutePhone)
          }
          if (response.data.instituteEmail) {
            this.instituteEmail = response.data.instituteEmail
            localStorage.setItem('instituteEmail', this.instituteEmail)
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
