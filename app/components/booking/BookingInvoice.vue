<script setup lang="ts">
import type { BookingInvoice } from '~/types/booking'
import { formatTimeSlotId } from '~/utils/bookingPhone'

defineProps<{
  invoice: BookingInvoice
}>()

const emit = defineEmits<{
  continue: []
}>()

function printInvoice() {
  window.print()
}
</script>

<template>
  <UCard class="print:shadow-none">
    <template #header>
      <div class="flex justify-between items-start">
        <div>
          <h3 class="text-lg font-semibold">
            ใบสรุปรายการจอง (ก่อนชำระเงิน)
          </h3>
          <p class="text-sm text-muted">
            เลขที่ {{ invoice.invoiceNumber }}
          </p>
        </div>
        <UButton
          variant="outline"
          size="sm"
          class="print:hidden"
          @click="printInvoice"
        >
          พิมพ์ / PDF
        </UButton>
      </div>
    </template>

    <div class="space-y-4 text-sm">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <p class="font-medium text-muted">
            ลูกค้า
          </p>
          <p>{{ invoice.customer.name }}</p>
          <p>{{ invoice.customer.phone }}</p>
          <p v-if="invoice.customer.email">
            {{ invoice.customer.email }}
          </p>
        </div>
        <div>
          <p class="font-medium text-muted">
            การนัดหมาย
          </p>
          <p>{{ invoice.booking.clinicName }}</p>
          <p>{{ invoice.booking.serviceName }}</p>
          <p>{{ invoice.booking.therapistName }}</p>
          <p>{{ invoice.booking.date }} · {{ formatTimeSlotId(invoice.booking.timeSlotId) }}</p>
        </div>
      </div>

      <table class="w-full border-t border-b py-3">
        <thead>
          <tr class="text-left text-muted">
            <th class="py-2">
              รายการ
            </th>
            <th class="py-2 text-right">
              จำนวนเงิน
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(line, i) in invoice.lineItems"
            :key="i"
          >
            <td class="py-1">
              {{ line.description }}
            </td>
            <td
              class="py-1 text-right"
              :class="line.amount < 0 ? 'text-emerald-600' : ''"
            >
              {{ line.amount < 0 ? '-' : '' }}฿{{ Math.abs(line.amount).toLocaleString() }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="text-right space-y-1">
        <p>ยอดก่อนส่วนลด: ฿{{ invoice.subtotal.toLocaleString() }}</p>
        <p
          v-if="invoice.discountAmount"
          class="text-emerald-600"
        >
          ส่วนลด: -฿{{ invoice.discountAmount.toLocaleString() }}
        </p>
        <p class="text-xl font-bold">
          ยอดชำระสุทธิ: ฿{{ invoice.total.toLocaleString() }}
        </p>
      </div>

      <UAlert
        :color="invoice.stripeReady ? 'success' : 'warning'"
        variant="subtle"
        :title="invoice.stripeReady ? 'พร้อมชำระด้วย Stripe' : 'โหมดทดสอบ'"
        :description="invoice.paymentNote"
      />
    </div>

    <template #footer>
      <UButton
        block
        size="lg"
        @click="emit('continue')"
      >
        ดำเนินการชำระเงิน
      </UButton>
    </template>
  </UCard>
</template>
