export default defineEventHandler(async (event) => {
  const body = await readBody<{
    clinicId: string
    serviceId: string
    therapistId: string
    date: string
    timeSlotId: string
    customerName: string
    customerPhone: string
    customerEmail?: string
    notes?: string
  }>(event)

  const appointment = {
    ...body,
    id: `appt-${Date.now()}`,
    status: 'confirmed',
    createdAt: new Date().toISOString()
  }

  return appointment
})
