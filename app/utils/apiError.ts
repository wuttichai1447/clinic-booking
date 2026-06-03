import type { FetchError } from 'ofetch'

type LaravelErrorBody = {
  message?: string
  errors?: Record<string, string[]>
}

export function parseApiError(error: unknown, fallback = 'เกิดข้อผิดพลาด'): string {
  const fetchError = error as FetchError<LaravelErrorBody>
  const data = fetchError?.data

  if (fetchError?.statusCode === 0 || fetchError?.message?.includes('fetch')) {
    return 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้ — ตรวจว่า Laravel รันอยู่ (php artisan serve)'
  }

  if (data?.errors) {
    const messages = Object.values(data.errors).flat()
    if (messages.length) {
      return messages.join(', ')
    }
  }

  if (data?.message) {
    return data.message
  }

  if (error instanceof Error && error.message) {
    return error.message
  }

  return fallback
}
