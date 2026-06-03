<script setup lang="ts">
import type { BookingInvoice } from '~/types/booking'
import { parseApiError } from '~/utils/apiError'
import { saveLastBookingPhone } from '~/utils/bookingPhone'
import { clearBookingCheckout, loadBookingCheckout, saveBookingCheckout } from '~/utils/bookingSession'
import { clearStripePending, loadStripePending } from '~/utils/stripePending'

const route = useRoute()
const { store, fetchClinics, fetchServices, fetchTherapists, fetchTimeSlots, createAppointment, fetchInvoice } = useBooking()
const { confirmStripePayment } = usePayment()

const services = ref<Awaited<ReturnType<typeof fetchServices>>>([])
const therapists = ref<Awaited<ReturnType<typeof fetchTherapists>>>([])
const slots = ref<Awaited<ReturnType<typeof fetchTimeSlots>>>([])
const invoice = ref<BookingInvoice | null>(null)

const error = ref<string | null>(null)
const submitting = ref(false)

const steps = [
  'เลือกคลินิก',
  'เลือกบริการ',
  'เลือกวันที่',
  'เลือกนักบำบัด',
  'เลือกเวลา',
  'กรอกข้อมูล',
  'สรุปก่อนชำระ',
  'ชำระเงิน'
]

const { data: clinics, pending: loadingClinics } = useAsyncData(
  'clinics',
  () => fetchClinics(),
  { default: () => [], timeout: 10_000 }
)

watch(() => store.currentStep, async (step) => {
  if (step === 1 && store.clinic) {
    services.value = await fetchServices(store.clinic.id)
  } else if (step === 3 && store.clinic) {
    therapists.value = await fetchTherapists(store.clinic.id)
  } else if (step === 4 && store.therapist && store.selectedDate) {
    slots.value = await fetchTimeSlots(store.therapist.id, store.selectedDate, store.clinic?.id)
  }
}, { immediate: true })

watch(() => store.clinic, async (clinic) => {
  if (clinic && store.currentStep >= 3) {
    therapists.value = await fetchTherapists(clinic.id)
  }
})

const { authStore, applyUserToBookingStore } = useAuth()

async function handleStripeReturn() {
  const redirectStatus = route.query.redirect_status as string | undefined
  const piFromUrl = route.query.payment_intent as string | undefined
  const pending = loadStripePending()

  if (!pending?.appointmentId) {
    error.value = 'ไม่พบข้อมูลการจองหลังชำระเงิน — ลองดูที่ "การจองของฉัน"'
    return
  }

  if (redirectStatus === 'failed') {
    error.value = 'การชำระเงินไม่สำเร็จ กรุณาลองใหม่'
    clearStripePending()
    return
  }

  try {
    const paymentIntentId = piFromUrl ?? pending.paymentIntentId
    const updated = await confirmStripePayment(pending.appointmentId, paymentIntentId)
    clearStripePending()
    clearBookingCheckout()
    store.setLastAppointment(updated)
    store.goToStep(8)
    await navigateTo({ path: '/', query: {} }, { replace: true })
  } catch (e) {
    error.value = parseApiError(e, 'ยืนยันการชำระเงินกับระบบไม่สำเร็จ')
  }
}

function persistCheckout() {
  if (store.lastAppointment?.id && store.currentStep >= 6) {
    saveBookingCheckout({
      currentStep: store.currentStep,
      lastAppointment: store.lastAppointment
    })
  }
}

onMounted(async () => {
  if (route.query.payment === 'return') {
    await handleStripeReturn()
    return
  }

  const saved = loadBookingCheckout()
  if (saved?.lastAppointment?.id && saved.currentStep >= 6) {
    store.setLastAppointment(saved.lastAppointment)
    store.goToStep(saved.currentStep)
    if (saved.lastAppointment.id) {
      try {
        invoice.value = await fetchInvoice(saved.lastAppointment.id)
      } catch {
        invoice.value = null
      }
    }
    if (authStore.isLoggedIn) {
      applyUserToBookingStore()
    }
    return
  }

  store.reset()
  if (authStore.isLoggedIn) {
    applyUserToBookingStore()
  }
})

function onNext() {
  store.nextStep()
}

function onPrev() {
  store.prevStep()
}

async function onConfirmBooking() {
  submitting.value = true
  error.value = null
  try {
    const appointment = await createAppointment()
    saveLastBookingPhone(store.customerPhone)
    store.setLastAppointment(appointment)
    if (appointment.id) {
      invoice.value = await fetchInvoice(appointment.id)
    }
    store.nextStep()
    persistCheckout()
  } catch (e) {
    error.value = parseApiError(e, 'จองไม่สำเร็จ')
  } finally {
    submitting.value = false
  }
}

function onInvoiceContinue() {
  store.nextStep()
  persistCheckout()
}

async function onPaymentSuccess(appointment: import('~/types/booking').Appointment) {
  clearBookingCheckout()
  store.setLastAppointment(appointment)
  store.nextStep()
}

async function onPaymentPending(appointment: import('~/types/booking').Appointment) {
  store.setLastAppointment(appointment)
  store.nextStep()
  persistCheckout()
}
</script>

<template>
  <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <h1 class="mb-5 sm:mb-8 text-xl sm:text-2xl font-bold">
      จองนัดหมาย
    </h1>

    <UAlert
      v-if="authStore.ready && !authStore.isLoggedIn"
      class="mb-4"
      color="info"
      variant="subtle"
      title="แนะนำให้เข้าสู่ระบบ"
      description="ล็อกอินก่อนจองเพื่อผูกการจองกับบัญชีและดูรายการใน 'การจองของฉัน'"
    />

    <StepIndicator
      v-if="store.currentStep < 8"
      :current-step="store.currentStep"
      :total-steps="8"
      :steps="steps"
    />

    <div
      v-if="error"
      class="mt-4"
    >
      <ErrorMessage :message="error" />
    </div>

    <div class="mt-8">
      <div v-if="store.currentStep === 0">
        <ClinicSelector
          :clinics="clinics ?? []"
          :selected-clinic="store.clinic"
          :loading="loadingClinics"
          @select="store.setClinic($event)"
        />
      </div>

      <div v-else-if="store.currentStep === 1">
        <ServiceSelector
          :services="services"
          :selected-service="store.service"
          @select="store.setService($event)"
        />
      </div>

      <div v-else-if="store.currentStep === 2">
        <DateSelector
          :selected-date="store.selectedDate"
          @select="store.setDate($event)"
        />
      </div>

      <div v-else-if="store.currentStep === 3">
        <TherapistSelector
          :therapists="therapists"
          :selected-therapist="store.therapist"
          @select="store.setTherapist($event)"
        />
      </div>

      <div v-else-if="store.currentStep === 4">
        <TimeSlotSelector
          :slots="slots"
          :selected-slot="store.timeSlot"
          @select="store.setTimeSlot($event)"
        />
      </div>

      <div v-else-if="store.currentStep === 5">
        <PromoCodeInput />
        <ReviewForm @submit="onConfirmBooking" />
        <div
          v-if="submitting"
          class="mt-4"
        >
          <LoadingSpinner />
        </div>
      </div>

      <div v-else-if="store.currentStep === 6 && invoice">
        <BookingInvoice
          :invoice="invoice"
          @continue="onInvoiceContinue"
        />
      </div>

      <div v-else-if="store.currentStep === 6 && !invoice">
        <LoadingSpinner />
      </div>

      <div v-else-if="store.currentStep === 7">
        <ClientOnly>
          <PaymentForm
            @success="onPaymentSuccess"
            @pending="onPaymentPending"
          />
        </ClientOnly>
      </div>

      <div v-else-if="store.currentStep === 8 && store.lastAppointment">
        <BookingSummary :appointment="store.lastAppointment" />
        <div class="mt-6 flex flex-wrap gap-3">
          <UButton to="/my-bookings">
            ดูการจองของฉัน
          </UButton>
          <UButton
            variant="outline"
            @click="clearBookingCheckout(); store.reset(); invoice = null"
          >
            จองใหม่
          </UButton>
        </div>
      </div>
    </div>

    <div
      v-if="store.currentStep < 5 && store.currentStep >= 0"
      class="mt-8"
    >
      <Navigation
        :current-step="store.currentStep"
        :total-steps="8"
        :can-go-next="store.canProceed"
        @prev="onPrev"
        @next="onNext"
      />
    </div>
  </div>
</template>
