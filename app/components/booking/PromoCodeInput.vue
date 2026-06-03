<script setup lang="ts">
import { parseApiError } from '~/utils/apiError'

const store = useBookingStore()
const { validatePromo } = useBooking()

const code = ref(store.promoCode)
const preview = ref<{ discountAmount: number, amount: number, promotionCode: string } | null>(null)
const error = ref<string | null>(null)
const loading = ref(false)

async function apply() {
  if (!code.value.trim() || !store.service) return
  loading.value = true
  error.value = null
  try {
    const result = await validatePromo(code.value.trim(), store.service.price)
    preview.value = result
    store.setPromoCode(code.value.trim())
  } catch (e) {
    preview.value = null
    store.setPromoCode('')
    error.value = parseApiError(e)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <UCard class="mb-4">
    <p class="font-medium mb-2">
      รหัสโปรโมชั่น
    </p>
    <div class="flex flex-col sm:flex-row gap-2">
      <UInput
        v-model="code"
        placeholder="เช่น WELCOME10"
        class="w-full sm:flex-1"
      />
      <UButton
        class="w-full sm:w-auto shrink-0 justify-center"
        :loading="loading"
        @click="apply"
      >
        ใช้โค้ด
      </UButton>
    </div>
    <p
      v-if="preview"
      class="text-emerald-600 text-sm mt-2"
    >
      ✓ {{ preview.promotionCode }} — ลด ฿{{ preview.discountAmount.toLocaleString() }} → ชำระ ฿{{ preview.amount.toLocaleString() }}
    </p>
    <p
      v-if="error"
      class="text-red-600 text-sm mt-2"
    >
      {{ error }}
    </p>
  </UCard>
</template>
