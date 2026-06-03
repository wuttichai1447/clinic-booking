const STORAGE_KEY = 'booking:lastPhone'

export function saveLastBookingPhone(phone: string) {
  if (import.meta.client && phone.trim()) {
    localStorage.setItem(STORAGE_KEY, phone.trim())
  }
}

export function loadLastBookingPhone(): string {
  if (import.meta.client) {
    return localStorage.getItem(STORAGE_KEY) ?? ''
  }
  return ''
}

export function formatTimeSlotId(slotId?: string): string {
  if (!slotId) return '-'
  return slotId.includes(':') ? slotId : slotId.replace(/-/g, ':')
}
