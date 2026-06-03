<script setup lang="ts">
import { parseApiError } from '~/utils/apiError'

useHead({ title: 'สมัครสมาชิก' })

const { register } = useAuth()

const pdpaAccepted = ref(false)
const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
})
const loading = ref(false)
const error = ref<string | null>(null)

async function onSubmit() {
  loading.value = true
  error.value = null
  try {
    await register({ ...form, pdpaAccepted: true })
    await navigateTo('/my-bookings')
  } catch (e) {
    error.value = parseApiError(e, 'สมัครสมาชิกไม่สำเร็จ')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-lg mx-auto px-4 sm:px-6 py-8 sm:py-12">
    <h1 class="mb-2 text-xl sm:text-2xl font-bold text-center">
      สมัครสมาชิก
    </h1>
    <p class="mb-8 text-center text-muted text-sm">
      สร้างบัญชีเพื่อดูการจองและระบุตัวตนเมื่อจองนัด
    </p>

    <UCard class="w-full shadow-sm" :ui="{ body: 'w-full p-4 sm:p-6' }">
      <form
        class="w-full space-y-5"
        @submit.prevent="onSubmit"
      >
        <UFormField label="ชื่อ-นามสกุล" size="lg" class="w-full">
          <UInput
            v-model="form.name"
            size="lg"
            class="w-full"
            required
            placeholder="ชื่อ นามสกุล"
          />
        </UFormField>
        <UFormField label="อีเมล" size="lg" class="w-full">
          <UInput
            v-model="form.email"
            type="email"
            size="lg"
            class="w-full"
            required
            placeholder="you@example.com"
          />
        </UFormField>
        <UFormField
          label="เบอร์โทร"
          size="lg"
          class="w-full"
          description="ใช้ยืนยันตัวตนและผูกกับการจอง"
        >
          <UInput
            v-model="form.phone"
            type="tel"
            size="lg"
            class="w-full"
            required
            placeholder="08xxxxxxxx"
          />
        </UFormField>
        <UFormField label="รหัสผ่าน" size="lg" class="w-full">
          <UInput
            v-model="form.password"
            type="password"
            size="lg"
            class="w-full"
            required
            autocomplete="new-password"
            placeholder="••••••••"
          />
        </UFormField>
        <UFormField label="ยืนยันรหัสผ่าน" size="lg" class="w-full">
          <UInput
            v-model="form.password_confirmation"
            type="password"
            size="lg"
            class="w-full"
            required
            autocomplete="new-password"
            placeholder="••••••••"
          />
        </UFormField>

        <PdpaConsent v-model="pdpaAccepted" class="text-default" />

        <ErrorMessage
          v-if="error"
          :message="error"
        />

        <UButton
          type="submit"
          block
          size="lg"
          class="w-full"
          :loading="loading"
          :disabled="!pdpaAccepted"
        >
          สมัครสมาชิก
        </UButton>

        <SocialLoginButtons />
      </form>

      <template #footer>
        <p class="text-sm text-center text-muted">
          มีบัญชีแล้ว?
          <NuxtLink
            to="/login"
            class="text-primary font-medium hover:underline"
          >
            เข้าสู่ระบบ
          </NuxtLink>
        </p>
      </template>
    </UCard>
  </div>
</template>
