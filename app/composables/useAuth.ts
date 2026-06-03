import type { AuthResponse, AuthUser } from '~/types/auth'
import { saveAuthSession } from '~/utils/authToken'

export function useAuth() {
  const authStore = useAuthStore()
  const { get, post } = useApi()

  async function register(payload: {
    name: string
    email: string
    phone: string
    password: string
    password_confirmation: string
    pdpaAccepted: boolean
  }) {
    const res = await post<AuthResponse>('/auth/register', payload)
    authStore.setSession(res.token, res.user)
    return res
  }

  async function login(email: string, password: string) {
    const res = await post<AuthResponse>('/auth/login', { email, password })
    authStore.setSession(res.token, res.user)
    return res
  }

  async function logout() {
    try {
      if (authStore.token) {
        await post<{ message: string }>('/auth/logout', {})
      }
    } finally {
      authStore.clear()
    }
  }

  async function fetchMe(): Promise<AuthUser | null> {
    if (!authStore.token) return null
    try {
      const res = await get<{ user: AuthUser }>('/auth/me')
      authStore.user = res.user
      if (authStore.token) {
        saveAuthSession(authStore.token, res.user)
      }
      return res.user
    } catch {
      authStore.clear()
      return null
    }
  }

  function applyUserToBookingStore() {
    const user = authStore.user
    if (!user) return
    const booking = useBookingStore()
    booking.setCustomerInfo({
      name: user.name,
      phone: user.phone,
      email: user.email
    })
  }

  return {
    authStore,
    register,
    login,
    logout,
    fetchMe,
    applyUserToBookingStore
  }
}
