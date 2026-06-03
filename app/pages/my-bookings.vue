<script setup lang="ts">
import type { Appointment } from '~/types/booking'
import { parseApiError } from '~/utils/apiError'
import { formatTimeSlotId } from '~/utils/bookingPhone'
import { loadAuthToken } from '~/utils/authToken'

definePageMeta({ middleware: 'auth' })

const { authStore } = useAuth()
const {
  fetchMyAppointments,
  fetchAppointmentsByPhone,
  cancelAppointment,
  fetchRefundPolicy
} = useBooking()

const appointments = ref<Appointment[]>([])
const phoneSearch = ref('')
const loading = ref(true)
const error = ref<string | null>(null)
const actionLoading = ref<string | null>(null)
const rescheduleTarget = ref<Appointment | null>(null)
const rescheduleOpen = ref(false)

const config = useRuntimeConfig()
const apiBase = config.public.apiBase as string

function statusLabel(status?: string) {
  const map: Record<string, string> = {
    awaiting_payment: 'รอชำระเงิน',
    awaiting_verification: 'รอยืนยันชำระ',
    confirmed: 'ยืนยันแล้ว',
    pending: 'รอดำเนินการ',
    cancelled: 'ยกเลิก',
    completed: 'เสร็จสิ้น'
  }
  return status ? (map[status] ?? status) : '-'
}

async function loadMine() {
  loading.value = true
  error.value = null
  try {
    appointments.value = await fetchMyAppointments()
  } catch (e) {
    error.value = parseApiError(e, 'โหลดข้อมูลไม่สำเร็จ')
    appointments.value = []
  } finally {
    loading.value = false
  }
}

async function searchByPhone() {
  if (phoneSearch.value.trim().length < 8) {
    error.value = 'กรุณากรอกเบอร์อย่างน้อย 8 หลัก'
    return
  }
  loading.value = true
  error.value = null
  try {
    appointments.value = await fetchAppointmentsByPhone(phoneSearch.value.trim())
  } catch (e) {
    error.value = parseApiError(e, 'ค้นหาไม่สำเร็จ')
  } finally {
    loading.value = false
  }
}

async function openReceipt(id: string) {
  const token = loadAuthToken()
  if (!token) return
  try {
    const html = await $fetch<string>(`${apiBase}/appointments/${id}/receipt`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'text/html' }
    })
    const w = window.open('', '_blank')
    if (w) {
      w.document.write(html)
      w.document.close()
    }
  } catch (e) {
    error.value = parseApiError(e, 'เปิดใบเสร็จไม่สำเร็จ')
  }
}

async function onCancel(appt: Appointment) {
  if (!appt.id || !confirm('ยกเลิกการจองนี้?')) return
  actionLoading.value = appt.id
  try {
    const policy = await fetchRefundPolicy(appt.id)
    if (policy.eligible && policy.amount > 0) {
      if (!confirm(`คืนเงินตามนโยบายประมาณ ฿${policy.amount} (${policy.policy})`)) return
    }
    await cancelAppointment(appt.id, 'ยกเลิกโดยลูกค้า')
    await loadMine()
  } catch (e) {
    error.value = parseApiError(e, 'ยกเลิกไม่สำเร็จ')
  } finally {
    actionLoading.value = null
  }
}

function openReschedule(appt: Appointment) {
  rescheduleTarget.value = appt
  rescheduleOpen.value = true
}

async function onRescheduleSuccess() {
  rescheduleTarget.value = null
  await loadMine()
}

onMounted(() => {
  loadMine()
})
</script>

<template>
  <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <h1 class="text-xl sm:text-2xl font-bold mb-2">
      การจองของฉัน
    </h1>

    <p class="mb-4 text-muted text-sm">
      สวัสดี {{ authStore.user?.name }} —
      <NuxtLink to="/profile" class="text-primary hover:underline">โปรไฟล์</NuxtLink>
    </p>
    <p class="mb-6 text-xs text-muted rounded-lg border border-default bg-muted/30 px-3 py-2">
      นโยบายยกเลิก: ล่วงหน้า 24 ชม. คืนเงินเต็ม · 12–24 ชม. คืนบางส่วน · ภายใน 2 ชม. ก่อนนัดไม่คืน
      (ระบบจะแสดงยอดคืนก่อนยืนยันเมื่อกดยกเลิก)
    </p>

    <div class="flex flex-col sm:flex-row gap-2 mb-6">
      <UInput
        v-model="phoneSearch"
        placeholder="ค้นหาด้วยเบอร์โทร"
        class="flex-1"
      />
      <UButton @click="searchByPhone">
        ค้นหา
      </UButton>
      <UButton variant="outline" @click="loadMine">
        รายการของฉัน
      </UButton>
    </div>

    <ErrorMessage v-if="error" :message="error" class="mb-4" />

    <div v-if="loading" class="flex justify-center p-8">
      <LoadingSpinner />
    </div>

    <div v-else-if="appointments.length" class="space-y-4">
      <p class="text-sm text-muted">
        พบ {{ appointments.length }} รายการ
      </p>
      <UCard v-for="appt in appointments" :key="appt.id">
        <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
          <p class="font-mono text-sm">
            {{ appt.id }}
          </p>
          <UBadge
            :color="appt.status === 'confirmed' ? 'success' : appt.status === 'cancelled' ? 'error' : 'warning'"
            variant="subtle"
          >
            {{ statusLabel(appt.status) }}
          </UBadge>
        </div>
        <div class="grid gap-2 text-sm sm:grid-cols-2">
          <p v-if="appt.clinicName">
            <span class="text-muted">คลินิก ·</span> {{ appt.clinicName }}
          </p>
          <p v-if="appt.serviceName">
            <span class="text-muted">บริการ ·</span> {{ appt.serviceName }}
          </p>
          <p>
            <span class="text-muted">วันเวลา ·</span>
            {{ appt.date }} {{ formatTimeSlotId(appt.timeSlotId) }} น.
          </p>
          <p v-if="appt.amount">
            <span class="text-muted">ยอด ·</span> ฿{{ appt.amount.toLocaleString() }}
          </p>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
          <UButton
            v-if="appt.status === 'confirmed'"
            size="sm"
            variant="outline"
            @click="openReceipt(appt.id!)"
          >
            ดาวน์โหลดใบเสร็จ
          </UButton>
          <UButton
            v-if="['awaiting_payment', 'awaiting_verification', 'confirmed'].includes(appt.status ?? '')"
            size="sm"
            variant="outline"
            :loading="actionLoading === appt.id"
            @click="openReschedule(appt)"
          >
            เลื่อนนัด
          </UButton>
          <UButton
            v-if="appt.status !== 'cancelled'"
            size="sm"
            color="error"
            variant="soft"
            :loading="actionLoading === appt.id"
            @click="onCancel(appt)"
          >
            ยกเลิกการจอง
          </UButton>
        </div>
      </UCard>
    </div>

    <UCard v-else>
      <p class="font-medium mb-1">
        ยังไม่มีการจอง
      </p>
      <template #footer>
        <UButton to="/">
          จองนัดหมาย
        </UButton>
      </template>
    </UCard>

    <RescheduleModal
      v-model:open="rescheduleOpen"
      :appointment="rescheduleTarget"
      @success="onRescheduleSuccess"
    />
  </div>
</template>
