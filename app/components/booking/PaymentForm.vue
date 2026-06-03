<script setup lang="ts">
import type { Appointment, PaymentMethod } from '~/types/booking'
import type { PaymentConfig } from '~/composables/usePayment'
import { parseApiError } from '~/utils/apiError'
import { clearStripePending, saveStripePending } from '~/utils/stripePending'

const store = useBookingStore()
const {
  fetchPaymentConfig,
  fetchAppointment,
  createPaymentIntent,
  confirmStripePayment,
  submitManualPayment,
  completeDevPayment
} = usePayment()
const {
  ready: stripeReady,
  paymentComplete: stripePaymentComplete,
  confirming: stripeConfirming,
  error: stripeError,
  mountPaymentElement,
  confirmPayment: confirmStripePaymentElement,
  destroy: destroyStripeCheckout
} = useStripeCheckout()

const emit = defineEmits<{
  success: [appointment: Appointment]
  pending: [appointment: Appointment]
}>()

const config = ref<PaymentConfig | null>(null)
const selectedMethod = ref<PaymentMethod | null>(store.paymentMethod)
const manualReference = ref('')
const proofFile = ref<File | null>(null)
const loading = ref(false)
const initializing = ref(true)
const error = ref<string | null>(null)
const stripeStep = ref(false)
const paymentIntentId = ref<string | null>(null)
const stripeContainerId = 'stripe-payment-element'

const appointmentId = computed(() => store.lastAppointment?.id ?? '')
const amount = computed(() => store.lastAppointment?.amount ?? store.service?.price ?? 0)
const appointmentStatus = computed(() => store.lastAppointment?.status)

const displayError = computed(() => {
  const msg = error.value || stripeError.value
  return msg?.trim() || null
})

const stripeConfirmLoading = computed(() => loading.value || stripeConfirming.value)
const stripeConfirmDisabled = computed(
  () => !stripeReady.value || stripeConfirming.value || loading.value
)

const isPaid = computed(() => appointmentStatus.value === 'confirmed' || appointmentStatus.value === 'completed')
const awaitingManualVerification = computed(() => appointmentStatus.value === 'awaiting_verification')
const canPay = computed(() => ['awaiting_payment', 'pending'].includes(appointmentStatus.value ?? ''))

const paymentMethods = computed(() => {
  const c = config.value
  if (!c) return []
  return [
    { value: 'credit_card' as const, label: 'บัตรเครดิต/เดบิต', desc: c.stripeEnabled ? 'ชำระผ่าน Stripe (Visa, Mastercard)' : 'โหมดทดสอบ', icon: 'i-lucide-credit-card', enabled: c.methods.credit_card },
    { value: 'transfer' as const, label: 'โอนเงิน', desc: 'โอนแล้วกรอกเลขอ้างอิง — รอแอดมินยืนยัน', icon: 'i-lucide-landmark', enabled: c.methods.transfer },
    { value: 'promptpay' as const, label: 'พร้อมเพย์', desc: 'โอน/สแกนแล้วกรอกเลขอ้างอิง', icon: 'i-lucide-smartphone', enabled: c.methods.promptpay }
  ].filter(m => m.enabled)
})

async function refreshAppointment() {
  if (!appointmentId.value) return
  const apt = await fetchAppointment(appointmentId.value)
  store.setLastAppointment(apt)
}

onMounted(async () => {
  initializing.value = true
  error.value = null
  try {
    if (appointmentId.value) {
      await refreshAppointment()
    }
    config.value = await fetchPaymentConfig()
    if (config.value.stripeEnabled && canPay.value) {
      selectedMethod.value = 'credit_card'
    }
  } catch (e) {
    error.value = parseApiError(e, 'โหลดการตั้งค่าชำระเงินไม่สำเร็จ')
  } finally {
    initializing.value = false
  }
})

onUnmounted(() => {
  destroyStripeCheckout()
})

async function payWithDev(method: PaymentMethod) {
  if (!appointmentId.value) return
  const updated = await completeDevPayment(appointmentId.value, method)
  emit('success', updated)
}

async function startStripeCheckout() {
  if (!appointmentId.value || !config.value?.publishableKey) return
  if (!canPay.value) {
    error.value = 'การจองนี้ไม่สามารถชำระเงินได้ในสถานะปัจจุบัน'
    return
  }
  if (amount.value < 10 && config.value.stripeEnabled) {
    error.value = 'ยอดชำระต้องไม่ต่ำกว่า ฿10 สำหรับบัตรเครดิต'
    return
  }

  loading.value = true
  error.value = null
  try {
    const intent = await createPaymentIntent(appointmentId.value, 'credit_card')

    if (intent.devMode || intent.provider === 'dev') {
      await payWithDev('credit_card')
      return
    }

    if (!intent.clientSecret || !intent.paymentIntentId) {
      throw new Error('ไม่ได้รับ client secret จาก Stripe')
    }

    paymentIntentId.value = intent.paymentIntentId
    stripeStep.value = true
    await nextTick()
    await mountPaymentElement(
      stripeContainerId,
      intent.publishableKey ?? config.value.publishableKey,
      intent.clientSecret
    )
  } catch (e) {
    error.value = parseApiError(e, 'เตรียมชำระเงินไม่สำเร็จ')
  } finally {
    loading.value = false
  }
}

async function confirmStripe() {
  if (!appointmentId.value || !paymentIntentId.value) return
  if (loading.value || stripeConfirming.value) return

  loading.value = true
  error.value = null
  stripeError.value = null

  saveStripePending({
    appointmentId: appointmentId.value,
    paymentIntentId: paymentIntentId.value
  })

  try {
    const returnUrl = `${window.location.origin}/?payment=return`
    await confirmStripePaymentElement(returnUrl)
    const updated = await confirmStripePayment(appointmentId.value, paymentIntentId.value)
    clearStripePending()
    emit('success', updated)
  } catch (e) {
    error.value = stripeError.value ?? parseApiError(e, 'ชำระเงินไม่สำเร็จ')
    await refreshAppointment().catch(() => {})
  } finally {
    loading.value = false
  }
}

async function submitManual() {
  if (!appointmentId.value || !selectedMethod.value) return
  if (selectedMethod.value !== 'transfer' && selectedMethod.value !== 'promptpay') return

  loading.value = true
  error.value = null
  try {
    const updated = await submitManualPayment(
      appointmentId.value,
      selectedMethod.value,
      manualReference.value.trim(),
      proofFile.value
    )
    emit('pending', updated)
  } catch (e) {
    error.value = parseApiError(e, 'ส่งข้อมูลไม่สำเร็จ')
  } finally {
    loading.value = false
  }
}

async function onPay() {
  if (!selectedMethod.value || !canPay.value) return
  store.setPaymentMethod(selectedMethod.value)
  error.value = null

  if (config.value?.devMode && !config.value.stripeEnabled) {
    loading.value = true
    try {
      await payWithDev(selectedMethod.value)
    } catch (e) {
      error.value = parseApiError(e, 'ชำระเงินไม่สำเร็จ')
    } finally {
      loading.value = false
    }
    return
  }

  if (selectedMethod.value === 'credit_card') {
    await startStripeCheckout()
    return
  }

  if (selectedMethod.value === 'transfer' || selectedMethod.value === 'promptpay') {
    if (manualReference.value.trim().length < 4) {
      error.value = 'กรุณากรอกเลขอ้างอิงการโอน (อย่างน้อย 4 ตัวอักษร)'
      return
    }
    await submitManual()
  }
}

function backFromStripe() {
  stripeStep.value = false
  destroyStripeCheckout()
  paymentIntentId.value = null
  clearStripePending()
  error.value = null
}
</script>

<template>
  <div
    class="w-full py-6 sm:py-10 px-4 sm:px-6
              bg-gradient-to-br from-slate-900 to-blue-950 rounded-xl sm:rounded-2xl"
  >
    <div class="w-full max-w-2xl mx-auto">
      <div class="text-center mb-6 sm:mb-8">
        <h3 class="text-2xl sm:text-3xl font-bold text-white">
          ชำระเงิน
        </h3>
        <p class="text-slate-400 text-sm mt-2">
          <template v-if="config?.devMode">
            โหมดทดสอบ — ชำระจำลอง
          </template>
          <template v-else-if="config?.stripeEnabled">
            บัตรเครดิตผ่าน Stripe · โอน/พร้อมเพย์รอแอดมินยืนยัน
          </template>
          <template v-else>
            เลือกช่องทางชำระเงิน
          </template>
        </p>
      </div>

      <div class="bg-slate-900/70 backdrop-blur-md border border-slate-800 rounded-2xl p-5 sm:p-8 shadow-xl space-y-6">
        <div
          v-if="initializing"
          class="flex justify-center py-12"
        >
          <LoadingSpinner />
        </div>

        <template v-else>
          <div class="rounded-xl bg-slate-800/60 border border-slate-700 p-4 sm:p-5">
            <p class="text-slate-400 text-sm mb-1">
              ยอดชำระสุทธิ
            </p>
            <p class="text-3xl font-bold text-emerald-400 tabular-nums">
              ฿{{ amount.toLocaleString() }}
            </p>
            <p
              v-if="appointmentId"
              class="text-slate-500 text-xs mt-2 font-mono"
            >
              {{ appointmentId }}
            </p>
          </div>

          <UAlert
            v-if="isPaid"
            color="success"
            variant="subtle"
            title="ชำระเงินแล้ว"
            description="การจองนี้ยืนยันแล้ว — ดูรายละเอียดใน 'การจองของฉัน'"
          />

          <UAlert
            v-else-if="awaitingManualVerification"
            color="warning"
            variant="subtle"
            title="รอแอดมินยืนยันการโอน"
            description="ส่งหลักฐานแล้ว — ไม่ต้องชำระด้วยบัตรซ้ำ"
          />

          <ErrorMessage
            v-if="displayError"
            :message="displayError"
          />

          <div
            v-if="stripeStep && canPay"
            class="space-y-4"
          >
            <div
              :id="stripeContainerId"
              class="min-h-[200px] rounded-xl bg-white p-4"
            />
            <p
              v-if="stripeReady && !stripePaymentComplete"
              class="text-xs text-slate-400 text-center"
            >
              กรอกเลขบัตร วันหมดอายุ และ CVC ให้ครบ (ทดสอบ: 4242 4242 4242 4242)
            </p>
            <div class="flex flex-col sm:flex-row gap-3">
              <UButton
                variant="outline"
                color="neutral"
                class="flex-1 justify-center"
                :disabled="loading"
                @click="backFromStripe"
              >
                ย้อนกลับ
              </UButton>
              <UButton
                size="lg"
                class="flex-1 justify-center bg-emerald-500 hover:bg-emerald-600"
                :loading="stripeConfirmLoading"
                :disabled="stripeConfirmDisabled"
                @click="confirmStripe"
              >
                ยืนยันชำระเงิน
              </UButton>
            </div>
          </div>

          <template v-else-if="canPay">
            <div v-if="!config">
              <LoadingSpinner />
            </div>

            <template v-else>
              <div>
                <p class="text-slate-300 font-medium mb-3">
                  ช่องทางชำระเงิน
                </p>
                <div class="space-y-2">
                  <label
                    v-for="method in paymentMethods"
                    :key="method.value"
                    class="flex items-center gap-3 p-3 sm:p-4 rounded-xl border-2 cursor-pointer transition"
                    :class="selectedMethod === method.value
                      ? 'border-emerald-500 bg-emerald-500/10'
                      : 'border-slate-700 bg-slate-800/40 hover:border-slate-600'"
                  >
                    <input
                      v-model="selectedMethod"
                      type="radio"
                      :value="method.value"
                      class="sr-only"
                    >
                    <UIcon
                      :name="method.icon"
                      class="size-6 text-emerald-400 shrink-0"
                    />
                    <div class="min-w-0 flex-1">
                      <p class="font-medium text-white text-sm sm:text-base">{{ method.label }}</p>
                      <p class="text-xs sm:text-sm text-slate-400">{{ method.desc }}</p>
                    </div>
                  </label>
                </div>
              </div>

              <div
                v-if="selectedMethod === 'transfer' && config.bank.accountNumber"
                class="rounded-xl border border-slate-700 bg-slate-800/50 p-4 text-sm space-y-2"
              >
                <p class="font-medium text-white">
                  ข้อมูลบัญชีโอน
                </p>
                <p class="text-slate-300">
                  {{ config.bank.name }} · {{ config.bank.accountName }}
                </p>
                <p class="font-mono text-lg text-emerald-400">
                  {{ config.bank.accountNumber }}
                </p>
              </div>
              <div
                v-if="selectedMethod === 'promptpay' && config.promptpayId"
                class="rounded-xl border border-slate-700 bg-slate-800/50 p-4 text-sm space-y-2"
              >
                <p class="font-medium text-white">
                  พร้อมเพย์
                </p>
                <p class="font-mono text-lg text-emerald-400">
                  {{ config.promptpayId }}
                </p>
              </div>

              <UFormField
                v-if="selectedMethod === 'transfer' || selectedMethod === 'promptpay'"
                label="เลขอ้างอิง / เวลาที่โอน"
                size="lg"
                class="w-full"
                description="เช่น เลขที่สลิป หรือ 21/05/2026 14:30"
              >
                <UInput
                  v-model="manualReference"
                  size="lg"
                  class="w-full"
                  placeholder="กรอกหลังโอนเงินแล้ว"
                />
              </UFormField>

              <UFormField
                v-if="selectedMethod === 'transfer' || selectedMethod === 'promptpay'"
                label="อัปโหลดสลิปโอน (jpg, png, pdf)"
                size="lg"
                class="w-full"
              >
                <input
                  type="file"
                  accept="image/jpeg,image/png,application/pdf"
                  class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white"
                  @change="proofFile = ($event.target as HTMLInputElement).files?.[0] ?? null"
                >
              </UFormField>

              <UButton
                block
                size="xl"
                class="rounded-xl bg-emerald-500 hover:bg-emerald-600"
                :disabled="!selectedMethod || loading"
                :loading="loading"
                @click="onPay"
              >
                {{ selectedMethod === 'credit_card' ? 'ดำเนินการชำระด้วยบัตร' : 'ส่งหลักฐานและรอการยืนยัน' }}
              </UButton>
            </template>
          </template>

          <div
            v-else-if="isPaid"
            class="flex justify-center"
          >
            <UButton
              to="/my-bookings"
              size="lg"
            >
              ดูการจองของฉัน
            </UButton>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
