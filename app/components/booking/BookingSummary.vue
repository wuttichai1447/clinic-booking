<script setup lang="ts">
import type { Appointment, PaymentMethod } from '~/types/booking'

const props = defineProps<{
  appointment: Appointment
}>()

const paymentLabels: Record<PaymentMethod, string> = {
  credit_card: 'บัตรเครดิต/เดบิต',
  transfer: 'โอนเงิน',
  promptpay: 'พร้อมเพย์'
}

const isPendingVerification = computed(() => props.appointment.status === 'awaiting_verification')
const isConfirmed = computed(() => props.appointment.status === 'confirmed')
</script>

<template>
  <UCard>
    <template #header>
      <h3 class="text-lg font-semibold flex items-center gap-2">
        <UIcon
          :name="isConfirmed ? 'i-lucide-circle-check' : 'i-lucide-clock'"
          :class="isConfirmed ? 'text-emerald-500' : 'text-amber-500'"
        />
        {{ isPendingVerification ? 'ส่งหลักฐานแล้ว — รอยืนยัน' : 'การจองสำเร็จ' }}
      </h3>
    </template>
    <UAlert
      v-if="isPendingVerification"
      class="mb-4"
      color="warning"
      variant="subtle"
      title="รอแอดมินตรวจสอบการชำระเงิน"
      description="เมื่อยืนยันแล้วสถานะจะเปลี่ยนเป็น confirmed และจะแสดงใน 'การจองของฉัน'"
    />
    <div class="space-y-2 text-sm sm:text-base">
      <p><strong>เลขที่การจอง:</strong> <span class="font-mono">{{ appointment.id }}</span></p>
      <p>
        <strong>สถานะ:</strong>
        <UBadge
          :color="isConfirmed ? 'success' : 'warning'"
          variant="subtle"
          class="ml-1"
        >
          {{ isConfirmed ? 'ยืนยันแล้ว' : isPendingVerification ? 'รอยืนยันชำระ' : appointment.status }}
        </UBadge>
      </p>
      <p><strong>ชื่อ:</strong> {{ appointment.customerName }}</p>
      <p><strong>เบอร์โทร:</strong> {{ appointment.customerPhone }}</p>
      <p v-if="appointment.amount">
        <strong>ยอด:</strong> ฿{{ appointment.amount.toLocaleString() }}
      </p>
      <template v-if="appointment.paymentMethod">
        <p><strong>ชำระด้วย:</strong> {{ paymentLabels[appointment.paymentMethod] }}</p>
        <p
          v-if="appointment.paidAt"
          class="text-emerald-600 font-medium"
        >
          ✓ ชำระเงินแล้ว {{ new Date(appointment.paidAt).toLocaleString('th-TH') }}
        </p>
      </template>
    </div>
    <template #footer>
      <div class="flex flex-col sm:flex-row gap-2">
        <UButton
          to="/"
          variant="outline"
          class="justify-center"
        >
          จองครั้งใหม่
        </UButton>
        <UButton
          to="/my-bookings"
          class="justify-center"
        >
          ดูการจองของฉัน
        </UButton>
      </div>
    </template>
  </UCard>
</template>
