import {
  appendResponseHeader,
  defineEventHandler,
  getRequestHeaders,
  getRequestURL,
  readRawBody,
  setResponseHeader,
  setResponseStatus
} from 'h3'

/**
 * Proxy /admin and /admin/* to Laravel with cookie + redirect rewriting for Vercel.
 */
export default defineEventHandler(async (event) => {
  const requestUrl = getRequestURL(event)
  if (!requestUrl.pathname.startsWith('/admin')) {
    return
  }

  try {
    const config = useRuntimeConfig()
    const backendBase = String(config.public.laravelUrl).replace(/\/$/, '')
    const target = `${backendBase}${requestUrl.pathname}${requestUrl.search}`

    const incoming = getRequestHeaders(event)
    const headers = new Headers()

    if (incoming.cookie) {
      headers.set('cookie', incoming.cookie)
    }
    if (incoming['content-type']) {
      headers.set('content-type', incoming['content-type'])
    }
    if (incoming.accept) {
      headers.set('accept', incoming.accept)
    }

    headers.set('X-Forwarded-Host', requestUrl.host)
    headers.set('X-Forwarded-Proto', requestUrl.protocol.replace(':', ''))
    headers.set('X-Forwarded-For', incoming['x-forwarded-for'] || incoming['x-real-ip'] || '127.0.0.1')

    const method = event.method
    const rawBody = method === 'GET' || method === 'HEAD'
      ? undefined
      : await readRawBody(event, false)

    const body: BodyInit | undefined = rawBody
      ? Uint8Array.from(rawBody as Iterable<number>)
      : undefined

    const response = await fetch(target, {
      method,
      headers,
      body,
      redirect: 'manual'
    })

    setResponseStatus(event, response.status)

    const frontendOrigin = requestUrl.origin
    const skipHeaders = new Set([
      'set-cookie',
      'transfer-encoding',
      'connection',
      'content-encoding',
      'content-length'
    ])

    const setCookies = typeof response.headers.getSetCookie === 'function'
      ? response.headers.getSetCookie()
      : []

    for (const cookie of setCookies) {
      appendResponseHeader(event, 'set-cookie', cookie.replace(/;\s*Domain=[^;]*/gi, ''))
    }

    for (const [key, value] of response.headers.entries()) {
      const lower = key.toLowerCase()
      if (skipHeaders.has(lower)) {
        continue
      }

      if (lower === 'location') {
        let location = value
        if (location.startsWith(backendBase)) {
          location = location.replace(backendBase, frontendOrigin)
        }
        setResponseHeader(event, 'location', location)
        continue
      }

      setResponseHeader(event, lower, value)
    }

    // ArrayBuffer serializes to "{}" on Vercel — return text for Blade HTML
    return await response.text()
  } catch (error) {
    const message = error instanceof Error ? error.message : 'Admin proxy failed'
    setResponseStatus(event, 502)
    return `Admin proxy error: ${message}`
  }
})
