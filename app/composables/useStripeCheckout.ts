import type { Stripe, StripeElements, StripePaymentElement } from '@stripe/stripe-js'

let stripeInstance: Stripe | null = null
let loadedPublishableKey: string | null = null
let elementsInstance: StripeElements | null = null
let paymentElementInstance: StripePaymentElement | null = null

async function loadStripeJs(publishableKey: string) {
  if (!import.meta.client) {
    throw new Error('Stripe ใช้ได้เฉพาะในเบราว์เซอร์')
  }
  const { loadStripe } = await import('@stripe/stripe-js')
  return loadStripe(publishableKey)
}

function formatStripeError(message: string, code?: string): string {
  if (code === 'incomplete_payment_details') {
    return 'กรุณากรอกข้อมูลบัตรให้ครบก่อนยืนยัน'
  }
  return message
}

export function useStripeCheckout() {
  const mounting = ref(false)
  const ready = ref(false)
  const paymentComplete = ref(false)
  const error = ref<string | null>(null)
  const confirming = ref(false)

  async function mountPaymentElement(containerId: string, publishableKey: string, clientSecret: string) {
    mounting.value = true
    error.value = null
    ready.value = false
    paymentComplete.value = false

    try {
      if (!stripeInstance || loadedPublishableKey !== publishableKey) {
        stripeInstance = await loadStripeJs(publishableKey)
        loadedPublishableKey = publishableKey
      }
      if (!stripeInstance) {
        throw new Error('ไม่สามารถโหลด Stripe ได้')
      }

      destroy()

      elementsInstance = stripeInstance.elements({
        clientSecret,
        appearance: { theme: 'stripe' }
      })
      paymentElementInstance = elementsInstance.create('payment', {
        layout: 'tabs',
        paymentMethodOrder: ['card']
      })
      paymentElementInstance.on('change', (event) => {
        paymentComplete.value = event.complete
        if (event.complete) {
          error.value = null
        }
      })

      const el = document.getElementById(containerId)
      if (!el) {
        throw new Error('ไม่พบพื้นที่แสดงฟอร์มชำระเงิน')
      }
      el.innerHTML = ''
      await paymentElementInstance.mount(`#${containerId}`)
      ready.value = true
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'โหลดฟอร์มชำระเงินไม่สำเร็จ'
      throw e
    } finally {
      mounting.value = false
    }
  }

  async function confirmPayment(returnUrl: string) {
    if (confirming.value) {
      return
    }
    if (!stripeInstance || !elementsInstance) {
      throw new Error('Stripe ยังไม่พร้อม')
    }

    confirming.value = true
    error.value = null

    try {
      const { error: submitError } = await elementsInstance.submit()
      if (submitError) {
        const msg = formatStripeError(submitError.message ?? 'ข้อมูลบัตรไม่ครบ', submitError.code)
        error.value = msg
        throw new Error(msg)
      }

      const { error: stripeError } = await stripeInstance.confirmPayment({
        elements: elementsInstance,
        confirmParams: {
          return_url: returnUrl
        },
        redirect: 'if_required'
      })

      if (stripeError) {
        const msg = formatStripeError(stripeError.message ?? 'ชำระเงินไม่สำเร็จ', stripeError.code)
        error.value = msg
        throw new Error(msg)
      }
    } catch (e) {
      if (e instanceof Error && !error.value) {
        error.value = e.message
      }
      throw e
    } finally {
      confirming.value = false
    }
  }

  function destroy() {
    paymentElementInstance?.unmount()
    paymentElementInstance = null
    elementsInstance = null
    ready.value = false
    paymentComplete.value = false
  }

  onUnmounted(() => {
    destroy()
  })

  return {
    mounting,
    ready,
    paymentComplete,
    confirming,
    error,
    mountPaymentElement,
    confirmPayment,
    destroy
  }
}
