import { parseApiError } from '~/utils/apiError'
import { loadAuthToken } from '~/utils/authToken'

export function useApi() {
  const config = useRuntimeConfig()
  const BASE_URL = (import.meta.server
    ? config.apiBackend
    : config.public.apiBase) as string

  function buildHeaders(): Record<string, string> {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
    const token = loadAuthToken()
    if (token) {
      headers.Authorization = `Bearer ${token}`
    }
    return headers
  }

  async function get<T>(path: string, params?: Record<string, string | undefined>): Promise<T> {
    const filtered = params
      ? Object.fromEntries(
        Object.entries(params).filter(([, v]) => v != null)
      ) as Record<string, string>
      : undefined
    const url = filtered
      ? `${BASE_URL}${path}?${new URLSearchParams(filtered).toString()}`
      : `${BASE_URL}${path}`

    try {
      return (await $fetch(url, {
        headers: buildHeaders(),
        timeout: 30_000,
        retry: 2,
        retryDelay: 2_000
      })) as T
    } catch (error) {
      throw new Error(parseApiError(error))
    }
  }

  async function post<T>(path: string, body: Record<string, unknown>): Promise<T> {
    try {
      return (await $fetch(`${BASE_URL}${path}`, {
        method: 'POST',
        headers: buildHeaders(),
        body,
        // Render free tier "cold start" อาจใช้เวลาปลุกเซิร์ฟเวอร์ 30-60 วินาที
        timeout: 60_000
      })) as T
    } catch (error) {
      throw new Error(parseApiError(error))
    }
  }

  async function postForm<T>(path: string, formData: FormData): Promise<T> {
    const headers: Record<string, string> = { Accept: 'application/json' }
    const token = loadAuthToken()
    if (token) {
      headers.Authorization = `Bearer ${token}`
    }
    try {
      return (await $fetch(`${BASE_URL}${path}`, {
        method: 'POST',
        headers,
        body: formData,
        timeout: 60_000
      })) as T
    } catch (error) {
      throw new Error(parseApiError(error))
    }
  }

  async function patch<T>(path: string, body: Record<string, unknown>): Promise<T> {
    try {
      return (await $fetch(`${BASE_URL}${path}`, {
        method: 'PATCH',
        headers: buildHeaders(),
        body,
        timeout: 30_000
      })) as T
    } catch (error) {
      throw new Error(parseApiError(error))
    }
  }

  return {
    get,
    post,
    postForm,
    patch
  }
}
