import type { AuthUser } from '~/types/auth'

export interface ProfileData {
  id: number
  name: string
  email: string
  phone: string | null
  pdpaAcceptedAt: string | null
}

export function useProfile() {
  const { get, patch, post } = useApi()

  async function fetchProfile() {
    return await get<ProfileData>('/me/profile')
  }

  async function updateProfile(data: { name?: string, phone?: string }) {
    return await patch<ProfileData>('/me/profile', data)
  }

  async function changePassword(currentPassword: string, password: string, password_confirmation: string) {
    return await post<{ message: string }>('/me/password', {
      currentPassword,
      password,
      password_confirmation
    })
  }

  return { fetchProfile, updateProfile, changePassword }
}
