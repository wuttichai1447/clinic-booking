<script setup lang="ts">
import { parseApiError } from '~/utils/apiError'

definePageMeta({ middleware: 'auth' })
const { authStore } = useAuth()
const { fetchProfile, updateProfile, changePassword } = useProfile()

useHead({ title: 'โปรไฟล์ของฉัน' })

const profile = ref({ name: '', phone: '' })
const pwd = reactive({
  currentPassword: '',
  password: '',
  password_confirmation: ''
})
const loading = ref(true)
const saving = ref(false)
const pwdSaving = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

onMounted(async () => {
  try {
    const p = await fetchProfile()
    profile.value = { name: p.name, phone: p.phone ?? '' }
  } catch (e) {
    error.value = parseApiError(e, 'โหลดโปรไฟล์ไม่สำเร็จ')
  } finally {
    loading.value = false
  }
})

async function saveProfile() {
  saving.value = true
  error.value = null
  success.value = null
  try {
    const updated = await updateProfile(profile.value)
    authStore.user = { ...authStore.user!, name: updated.name, phone: updated.phone ?? undefined } as typeof authStore.user
    success.value = 'บันทึกโปรไฟล์แล้ว'
  } catch (e) {
    error.value = parseApiError(e, 'บันทึกไม่สำเร็จ')
  } finally {
    saving.value = false
  }
}

async function savePassword() {
  pwdSaving.value = true
  error.value = null
  success.value = null
  try {
    await changePassword(pwd.currentPassword, pwd.password, pwd.password_confirmation)
    success.value = 'เปลี่ยนรหัสผ่านแล้ว'
    pwd.currentPassword = ''
    pwd.password = ''
    pwd.password_confirmation = ''
  } catch (e) {
    error.value = parseApiError(e, 'เปลี่ยนรหัสผ่านไม่สำเร็จ')
  } finally {
    pwdSaving.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-lg mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-xl sm:text-2xl font-bold mb-6">
      โปรไฟล์ของฉัน
    </h1>

    <LoadingSpinner v-if="loading" />

    <template v-else>
      <ErrorMessage v-if="error" :message="error" class="mb-4" />
      <UAlert v-if="success" color="success" variant="subtle" :title="success" class="mb-4" />

      <UCard class="mb-6">
        <form class="space-y-4" @submit.prevent="saveProfile">
          <UFormField label="ชื่อ-นามสกุล">
            <UInput v-model="profile.name" required />
          </UFormField>
          <UFormField label="เบอร์โทร" description="ใช้ผูกกับการจองใน my-bookings">
            <UInput v-model="profile.phone" type="tel" required />
          </UFormField>
          <p class="text-sm text-muted">
            อีเมล: {{ authStore.user?.email }}
          </p>
          <UButton type="submit" :loading="saving">
            บันทึก
          </UButton>
        </form>
      </UCard>

      <UCard>
        <h2 class="font-semibold mb-4">
          เปลี่ยนรหัสผ่าน
        </h2>
        <form class="space-y-4" @submit.prevent="savePassword">
          <UFormField label="รหัสผ่านปัจจุบัน">
            <UInput v-model="pwd.currentPassword" type="password" required />
          </UFormField>
          <UFormField label="รหัสผ่านใหม่">
            <UInput v-model="pwd.password" type="password" required />
          </UFormField>
          <UFormField label="ยืนยันรหัสผ่านใหม่">
            <UInput v-model="pwd.password_confirmation" type="password" required />
          </UFormField>
          <UButton type="submit" variant="outline" :loading="pwdSaving">
            เปลี่ยนรหัสผ่าน
          </UButton>
        </form>
      </UCard>
    </template>
  </div>
</template>
