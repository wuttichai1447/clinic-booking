const KEY = 'booking_stripe_pending'

export interface StripePendingPayment {
  appointmentId: string
  paymentIntentId: string
}

export function saveStripePending(data: StripePendingPayment) {
  if (!import.meta.client) return
  sessionStorage.setItem(KEY, JSON.stringify(data))
}

export function loadStripePending(): StripePendingPayment | null {
  if (!import.meta.client) return null
  const raw = sessionStorage.getItem(KEY)
  if (!raw) return null
  try {
    return JSON.parse(raw) as StripePendingPayment
  } catch {
    return null
  }
}

export function clearStripePending() {
  if (!import.meta.client) return
  sessionStorage.removeItem(KEY)
}
