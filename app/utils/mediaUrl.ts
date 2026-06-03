/**
 * แปลง path รูปจาก Laravel (/storage/...) ให้โหลดได้จาก frontend
 */
export function resolveMediaUrl(path: string | null | undefined): string | undefined {
  if (!path) return undefined
  if (path.startsWith('http://') || path.startsWith('https://')) return path

  const config = useRuntimeConfig()
  const base = (config.public.laravelUrl as string).replace(/\/$/, '')

  if (path.startsWith('/')) {
    return `${base}${path}`
  }

  return `${base}/${path}`
}
