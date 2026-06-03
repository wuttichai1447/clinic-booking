import type { Clinic, Service, Therapist, TimeSlot } from '~/types/booking'
import clinicsData from '~/data/mock/clinics.json'
import servicesData from '~/data/mock/services.json'
import therapistsData from '~/data/mock/therapists.json'
import slotsData from '~/data/mock/slots.json'

export function useMockData() {
  function getClinics(): Clinic[] {
    return clinicsData as Clinic[]
  }

  function getServices(clinicId?: string): Service[] {
    const services = servicesData as Service[]
    if (clinicId) {
      return services.filter(s => !s.clinicId || s.clinicId === clinicId)
    }
    return services
  }

  function getTherapists(clinicId?: string): Therapist[] {
    const therapists = therapistsData as Therapist[]
    if (clinicId) {
      return therapists.filter(t => t.clinicId === clinicId)
    }
    return therapists
  }

  function getTimeSlots(): TimeSlot[] {
    return Object.entries(slotsData as Record<string, boolean>).map(([time, available]) => ({
      id: time.replace(':', '-'),
      time,
      available
    }))
  }

  return {
    getClinics,
    getServices,
    getTherapists,
    getTimeSlots
  }
}
