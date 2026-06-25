import type { FetchError } from 'ofetch'

type LaravelErrorBody = {
  message?: string
  errors?: Record<string, string[]>
}

export function parseApiError(error: unknown, fallback = 'เกิดข้อผิดพลาด'): string {
  const fetchError = error as FetchError<LaravelErrorBody>
  const data = fetchError?.data
  const rawMessage = fetchError?.message ?? (error instanceof Error ? error.message : '')

  if (
    fetchError?.name === 'TimeoutError'
    || /timeout|aborted|aborterror/i.test(rawMessage)
  ) {
    return 'เซิร์ฟเวอร์กำลังตื่นจากโหมดพัก (Render free tier) — กรุณารอสักครู่แล้วลองกดยืนยันอีกครั้ง'
  }

  if (fetchError?.statusCode === 0 || /fetch failed|network|<no response>/i.test(rawMessage)) {
    return 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้ — กรุณารอสักครู่แล้วลองใหม่อีกครั้ง'
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
