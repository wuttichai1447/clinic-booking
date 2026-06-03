import { defineStore } from 'pinia'
import type { Clinic, Service, Therapist, TimeSlot, Appointment, PaymentMethod } from '~/types/booking'

export const useBookingStore = defineStore('booking', {
  state: () => ({
    currentStep: 0,
    clinic: null as Clinic | null,
    service: null as Service | null,
    therapist: null as Therapist | null,
    selectedDate: null as string | null,
    timeSlot: null as TimeSlot | null,
    customerName: '',
    customerPhone: '',
    customerEmail: '',
    notes: '',
    lastAppointment: null as Appointment | null,
    paymentMethod: null as PaymentMethod | null,
    promoCode: '' as string,
    pdpaAccepted: false
  }),

  getters: {
    canProceed(): boolean {
      switch (this.currentStep) {
        case 0: return !!this.clinic
        case 1: return !!this.service
        case 2: return !!this.selectedDate
        case 3: return !!this.therapist
        case 4: return !!this.timeSlot
        case 5: return !!(this.customerName && this.customerPhone)
        case 6: return !!this.lastAppointment
        case 7: return !!this.paymentMethod
        default: return false
      }
    },

    totalSteps(): number {
      return 8
    }
  },

  actions: {
    setClinic(clinic: Clinic | null) {
      this.clinic = clinic
      this.therapist = null
    },

    setService(service: Service | null) {
      this.service = service
    },

    setTherapist(therapist: Therapist | null) {
      this.therapist = therapist
    },

    setDate(date: string | null) {
      this.selectedDate = date
    },

    setTimeSlot(slot: TimeSlot | null) {
      this.timeSlot = slot
    },

    setCustomerInfo(data: { name: string, phone: string, email?: string, notes?: string }) {
      this.customerName = data.name
      this.customerPhone = data.phone
      this.customerEmail = data.email || ''
      this.notes = data.notes || ''
    },

    setLastAppointment(appointment: Appointment | null) {
      this.lastAppointment = appointment
    },

    setPaymentMethod(method: PaymentMethod | null) {
      this.paymentMethod = method
    },

    setPromoCode(code: string) {
      this.promoCode = code
    },

    setPdpaAccepted(value: boolean) {
      this.pdpaAccepted = value
    },

    nextStep() {
      if (this.currentStep < this.totalSteps - 1) {
        this.currentStep++
      }
    },

    prevStep() {
      if (this.currentStep > 0) {
        this.currentStep--
      }
    },

    goToStep(step: number) {
      if (step >= 0 && step < this.totalSteps) {
        this.currentStep = step
      }
    },

    reset() {
      this.currentStep = 0
      this.clinic = null
      this.service = null
      this.therapist = null
      this.selectedDate = null
      this.timeSlot = null
      this.customerName = ''
      this.customerPhone = ''
      this.customerEmail = ''
      this.notes = ''
      this.lastAppointment = null
      this.paymentMethod = null
      this.promoCode = ''
      this.pdpaAccepted = false
    },

    getBookingData() {
      return {
        currentStep: this.currentStep,
        clinic: this.clinic,
        service: this.service,
        therapist: this.therapist,
        selectedDate: this.selectedDate,
        timeSlot: this.timeSlot,
        customerName: this.customerName,
        customerPhone: this.customerPhone,
        customerEmail: this.customerEmail,
        notes: this.notes
      }
    }
  }
})
