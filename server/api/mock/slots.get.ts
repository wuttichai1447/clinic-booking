import slotsData from '../../data/mock/slots.json'

export default defineEventHandler(() => {
  const slots = Object.entries(slotsData).map(([time, available]) => ({
    id: time.replace(':', '-'),
    time,
    available
  }))
  return slots
})
