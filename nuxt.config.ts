// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxt/ui',
    '@pinia/nuxt'
  ],

  components: [
    {
      path: '~/components',
      pathPrefix: false
    }
  ],

  devtools: {
    enabled: process.env.NODE_ENV === 'development'
  },

  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    // ใช้ตอน SSR (เรียก Laravel โดยตรง ไม่ผ่าน Vue Router)
    apiBackend: process.env.NUXT_API_BACKEND || 'http://127.0.0.1:8000/api/v1',
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api/v1',
      laravelUrl: process.env.NUXT_LARAVEL_URL || 'http://127.0.0.1:8000',
      stripePublishableKey: process.env.NUXT_PUBLIC_STRIPE_KEY || ''
    }
  },

  build: {
    transpile: ['vue']
  },

  routeRules: {
    '/': { ssr: true },
    // Proxy API ทั้ง SSR และ browser — ไม่ให้ Vue Router จับ path /api/v1/*
    '/api/v1/**': {
      proxy: `${process.env.NUXT_API_BACKEND || 'http://127.0.0.1:8000/api/v1'}/**`
    },
    // แอดมิน Blade — proxy ผ่าน server/middleware/admin-proxy.ts (รองรับ session บน Vercel)
    // รูปที่อัปโหลดใน Laravel (storage/app/public)
    '/storage/**': {
      proxy: `${(process.env.NUXT_LARAVEL_URL || 'http://127.0.0.1:8000').replace(/\/$/, '')}/storage/**`
    }
  },

  compatibilityDate: '2025-01-15',

  // ป้องกัน Vue ซ้ำ (npm + pnpm) → SSR error "Cannot read properties of null (reading 'ce')"
  vite: {
    resolve: {
      dedupe: ['vue', '@vue/runtime-core', '@vue/runtime-dom', '@vue/server-renderer']
    },
    ssr: {
      // Stripe.js เป็น browser-only — อย่า bundle ตอน SSR (กัน dev timeout 60s)
      external: ['@stripe/stripe-js']
    }
  },

  eslint: {
    config: {
      stylistic: {
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  }
})
