import therapistsData from '../../data/mock/therapists.json'

export default defineEventHandler((event) => {
  const query = getQuery(event)
  const clinicId = query.clinicId as string | undefined

  if (clinicId) {
    return therapistsData.filter((t: { clinicId: string }) => t.clinicId === clinicId)
  }

  return therapistsData
})
