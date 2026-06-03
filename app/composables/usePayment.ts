import type { Appointment, PaymentMethod } from '~/types/booking'

export interface PaymentConfig {
  devMode: boolean
  stripeEnabled: boolean
  publishableKey: string | null
  methods: {
    credit_card: boolean
    transfer: boolean
    promptpay: boolean
  }
  bank: {
    name: string | null
    accountName: string | null
    accountNumber: string | null
  }
  promptpayId: string | null
}

export interface PaymentIntentResponse {
  paymentId?: string
  provider: 'stripe' | 'dev'
  clientSecret?: string
  paymentIntentId?: string
  publishableKey?: string
  devMode?: boolean
  amount: number
  message?: string
}

export function usePayment() {
  const { get, post, postForm } = useApi()

  async function fetchPaymentConfig() {
    return await get<PaymentConfig>('/payments/config')
  }

  async function fetchAppointment(appointmentId: string) {
    return await get<Appointment>(`/appointments/${appointmentId}`)
  }

  async function createPaymentIntent(appointmentId: string, method: PaymentMethod) {
    return await post<PaymentIntentResponse>(`/appointments/${appointmentId}/payments/intent`, { method })
  }

  async function confirmStripePayment(appointmentId: string, paymentIntentId: string) {
    return await post<Appointment>(`/appointments/${appointmentId}/payments/confirm`, {
      paymentIntentId
    })
  }

  async function submitManualPayment(
    appointmentId: string,
    method: 'transfer' | 'promptpay',
    reference: string,
    proofFile?: File | null
  ) {
    const form = new FormData()
    form.append('method', method)
    form.append('reference', reference)
    if (proofFile) {
      form.append('proof', proofFile)
    }
    return await postForm<Appointment>(`/appointments/${appointmentId}/payments/submit-manual`, form)
  }

  async function completeDevPayment(appointmentId: string, method: PaymentMethod) {
    return await post<Appointment>(`/appointments/${appointmentId}/payments/dev-complete`, { method })
  }

  return {
    fetchPaymentConfig,
    fetchAppointment,
    createPaymentIntent,
    confirmStripePayment,
    submitManualPayment,
    completeDevPayment
  }
}
