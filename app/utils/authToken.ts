const TOKEN_KEY = 'auth:token'
const USER_KEY = 'auth:user'

export function saveAuthSession(token: string, user: object) {
  if (!import.meta.client) return
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))
}

export function loadAuthToken(): string | null {
  if (!import.meta.client) return null
  return localStorage.getItem(TOKEN_KEY)
}

export function loadAuthUser<T>(): T | null {
  if (!import.meta.client) return null
  const raw = localStorage.getItem(USER_KEY)
  if (!raw) return null
  try {
    return JSON.parse(raw) as T
  } catch {
    return null
  }
}

export function clearAuthSession() {
  if (!import.meta.client) return
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
}
