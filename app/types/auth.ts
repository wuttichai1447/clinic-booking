export interface AuthUser {
  id: number
  name: string
  email: string
  phone: string
  role: string
}

export interface AuthResponse {
  token: string
  user: AuthUser
  message?: string
}
