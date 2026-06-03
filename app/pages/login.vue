<script setup lang="ts">
import { parseApiError } from '~/utils/apiError'

definePageMeta({
  layout: 'default'
})

useHead({ title: 'เข้าสู่ระบบ' })

const route = useRoute()
const { login } = useAuth()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref<string | null>(route.query.error as string || null)

const errorMessages: Record<string, string> = {
  oauth_failed: 'เข้าสู่ระบบด้วย Google/Facebook ไม่สำเร็จ',
  oauth_not_configured: 'ยังไม่ได้ตั้งค่า OAuth — ใส่ GOOGLE_CLIENT_ID และ GOOGLE_CLIENT_SECRET ใน backend/.env แล้วรัน php artisan config:clear',
  not_customer: 'บัญชีนี้ไม่ใช่บัญชีลูกค้า'
}

async function onSubmit() {
  loading.value = true
  error.value = null
  try {
    await login(email.value.trim(), password.value)
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/my-bookings'
    await navigateTo(redirect)
  } catch (e) {
    error.value = parseApiError(e, 'เข้าสู่ระบบไม่สำเร็จ')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-lg mx-auto px-4 sm:px-6 py-8 sm:py-12">
    <h1 class="mb-2 text-xl sm:text-2xl font-bold text-center">
      เข้าสู่ระบบ
    </h1>
    <p class="mb-8 text-center text-muted text-sm">
      สำหรับลูกค้าที่สมัครสมาชิกแล้ว
    </p>

    <UCard class="w-full shadow-sm" :ui="{ body: 'w-full p-4 sm:p-6' }">
      <form
        class="w-full space-y-5"
        @submit.prevent="onSubmit"
      >
        <UFormField label="อีเมล" size="lg" class="w-full">
          <UInput
            v-model="email"
            type="email"
            size="lg"
            class="w-full"
            required
            autocomplete="email"
            placeholder="you@example.com"
          />
        </UFormField>
        <UFormField label="รหัสผ่าน" size="lg" class="w-full">
          <UInput
            v-model="password"
            type="password"
            size="lg"
            class="w-full"
            required
            autocomplete="current-password"
            placeholder="••••••••"
          />
        </UFormField>

        <ErrorMessage
          v-if="error"
          :message="errorMessages[error] ?? error"
        />

        <UButton
          type="submit"
          block
          size="lg"
          class="w-full"
          :loading="loading"
        >
          เข้าสู่ระบบ
        </UButton>

        <SocialLoginButtons />
      </form>

      <template #footer>
        <p class="text-sm text-center text-muted">
          ยังไม่มีบัญชี?
          <NuxtLink
            to="/register"
            class="text-primary font-medium hover:underline"
          >
            สมัครสมาชิก
          </NuxtLink>
        </p>
      </template>
    </UCard>
  </div>
</template>
