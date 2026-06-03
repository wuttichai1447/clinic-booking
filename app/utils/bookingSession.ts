import type { Appointment } from '~/types/booking'

const KEY = 'booking:checkout'

export interface BookingCheckoutSnapshot {
  currentStep: number
  lastAppointment: Appointment
}

export function saveBookingCheckout(snapshot: BookingCheckoutSnapshot) {
  if (!import.meta.client) return
  sessionStorage.setItem(KEY, JSON.stringify(snapshot))
}

export function loadBookingCheckout(): BookingCheckoutSnapshot | null {
  if (!import.meta.client) return null
  const raw = sessionStorage.getItem(KEY)
  if (!raw) return null
  try {
    return JSON.parse(raw) as BookingCheckoutSnapshot
  } catch {
    return null
  }
}

export function clearBookingCheckout() {
  if (!import.meta.client) return
  sessionStorage.removeItem(KEY)
}
