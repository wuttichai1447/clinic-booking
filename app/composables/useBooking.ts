import { useBookingStore } from '~/stores/booking'
import type { Appointment, Clinic, Service, Therapist, TimeSlot } from '~/types/booking'

export function useBooking() {
  const store = useBookingStore()
  const { get, post } = useApi()

  async function fetchClinics() {
    return await get<Clinic[]>('/clinics')
  }

  async function fetchServices(clinicId?: string) {
    return await get<Service[]>('/services', clinicId ? { clinicId } : undefined)
  }

  async function fetchTherapists(clinicId?: string) {
    return await get<Therapist[]>('/therapists', clinicId ? { clinicId } : undefined)
  }

  async function fetchTimeSlots(therapistId?: string, date?: string, clinicId?: string) {
    return await get<TimeSlot[]>('/slots', {
      therapistId,
      date,
      clinicId
    })
  }

  async function createPaymentIntent(appointmentId: string, method: string) {
    return await post<{
      paymentId: string
      provider: string
      clientSecret?: string
      publishableKey?: string
      devMode?: boolean
      amount: number
    }>(`/appointments/${appointmentId}/payments/intent`, { method })
  }

  async function completeDevPayment(appointmentId: string, method: string) {
    return await post<Appointment>(
      `/appointments/${appointmentId}/payments/dev-complete`,
      { method }
    )
  }

  async function fetchAppointmentsByPhone(phone: string) {
    return await get<Appointment[]>('/appointments', { phone })
  }

  async function fetchMyAppointments() {
    return await get<Appointment[]>('/me/appointments')
  }

  async function createAppointment(): Promise<Appointment> {
    const state = store.getBookingData()
    if (!state.clinic || !state.service || !state.therapist || !state.selectedDate || !state.timeSlot) {
      throw new Error('กรุณากรอกข้อมูลให้ครบถ้วน')
    }

    const body: Record<string, unknown> = {
      clinicId: state.clinic.id,
      serviceId: state.service.id,
      therapistId: state.therapist.id,
      date: state.selectedDate,
      timeSlotId: state.timeSlot.id,
      customerName: state.customerName,
      customerPhone: state.customerPhone,
      customerEmail: state.customerEmail || undefined,
      notes: state.notes || undefined
    }
    if (store.promoCode.trim()) {
      body.promoCode = store.promoCode.trim()
    }
    body.pdpaAccepted = store.pdpaAccepted

    return await post<Appointment>('/appointments', body)
  }

  async function cancelAppointment(appointmentId: string, reason?: string) {
    return await post<Appointment>(`/appointments/${appointmentId}/cancel`, { reason: reason ?? '' })
  }

  async function rescheduleAppointment(appointmentId: string, date: string, timeSlotId: string) {
    return await post<Appointment>(`/appointments/${appointmentId}/reschedule`, { date, timeSlotId })
  }

  async function fetchRefundPolicy(appointmentId: string) {
    return await get<{ eligible: boolean, amount: number, policy: string }>(
      `/appointments/${appointmentId}/refund-policy`
    )
  }

  async function validatePromo(code: string, subtotal: number) {
    return await post<import('~/types/booking').PricingPreview>('/promotions/validate', {
      code,
      subtotal
    })
  }

  async function fetchInvoice(appointmentId: string) {
    return await get<import('~/types/booking').BookingInvoice>(`/appointments/${appointmentId}/invoice`)
  }

  async function applyPromo(appointmentId: string, code: string) {
    return await post<{ appointment: Appointment, pricing: import('~/types/booking').PricingPreview }>(
      `/appointments/${appointmentId}/apply-promo`,
      { code }
    )
  }

  return {
    store,
    fetchClinics,
    fetchServices,
    fetchTherapists,
    fetchTimeSlots,
    createAppointment,
    createPaymentIntent,
    completeDevPayment,
    fetchAppointmentsByPhone,
    fetchMyAppointments,
    cancelAppointment,
    rescheduleAppointment,
    fetchRefundPolicy,
    validatePromo,
    fetchInvoice,
    applyPromo
  }
}
