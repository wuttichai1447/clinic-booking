<script setup lang="ts">
const store = useBookingStore()
const { authStore, applyUserToBookingStore } = useAuth()

const form = reactive({
  customerName: store.customerName,
  customerPhone: store.customerPhone,
  customerEmail: store.customerEmail,
  notes: store.notes
})

onMounted(() => {
  if (authStore.isLoggedIn) {
    applyUserToBookingStore()
    form.customerName = store.customerName
    form.customerPhone = store.customerPhone
    form.customerEmail = store.customerEmail
  }
})

watch(() => store.customerName, (v) => {
  form.customerName = v
})
watch(() => store.customerPhone, (v) => {
  form.customerPhone = v
})
watch(() => store.customerEmail, (v) => {
  form.customerEmail = v
})
watch(() => store.notes, (v) => {
  form.notes = v
})

const pdpaAccepted = computed({
  get: () => store.pdpaAccepted,
  set: (v: boolean) => store.setPdpaAccepted(v)
})

const emit = defineEmits<{
  submit: []
}>()

function onSubmit() {
  if (!pdpaAccepted.value) return
  store.setCustomerInfo({
    name: form.customerName,
    phone: form.customerPhone,
    email: form.customerEmail,
    notes: form.notes
  })
  emit('submit')
}
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center
              bg-gradient-to-br from-slate-900 to-blue-950 px-4 py-12"
  >
    <div class="w-full max-w-3xl">
      <!-- Header -->
      <div class="text-center mb-10">
        <h3 class="text-3xl font-bold text-white">
          ข้อมูลผู้จอง
        </h3>
        <p class="text-slate-400 text-sm mt-2">
          กรุณากรอกข้อมูลให้ครบถ้วนเพื่อยืนยันการจอง
        </p>
      </div>

      <!-- Card -->
      <div
        class="bg-slate-900/70 backdrop-blur-md
                  border border-slate-800
                  rounded-2xl p-8 shadow-xl"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
          <!-- ชื่อ -->
          <div class="md:col-span-2 w-full">
            <UFormField
              label="ชื่อ-นามสกุล"
              class="w-full"
            >
              <UInput
                v-model="form.customerName"
                placeholder="กรอกชื่อ-นามสกุล"
                size="lg"
                class="w-full"
                required
              />
            </UFormField>
          </div>

          <!-- เบอร์ -->
          <div class="w-full">
            <UFormField
              label="เบอร์โทรศัพท์"
              class="w-full"
            >
              <UInput
                v-model="form.customerPhone"
                type="tel"
                placeholder="กรอกเบอร์โทรศัพท์"
                size="lg"
                class="w-full"
                required
              />
            </UFormField>
          </div>

          <!-- อีเมล -->
          <div class="w-full">
            <UFormField
              label="อีเมล (ไม่บังคับ)"
              class="w-full"
            >
              <UInput
                v-model="form.customerEmail"
                type="email"
                placeholder="กรอกอีเมล"
                size="lg"
                class="w-full"
              />
            </UFormField>
          </div>

          <!-- หมายเหตุ -->
          <div class="md:col-span-2 w-full">
            <UFormField
              label="หมายเหตุ (ไม่บังคับ)"
              class="w-full"
            >
              <UTextarea
                v-model="form.notes"
                placeholder="กรอกหมายเหตุเพิ่มเติม"
                :rows="4"
                size="lg"
                class="w-full"
              />
            </UFormField>
          </div>
        </div>

        <div class="mt-6">
          <PdpaConsent v-model="pdpaAccepted" />
        </div>

        <!-- Button -->
        <UButton
          block
          size="xl"
          class="mt-8 rounded-xl bg-emerald-500
                 hover:bg-emerald-600 transition"
          :disabled="!pdpaAccepted"
          @click="onSubmit"
        >
          ยืนยันการจอง
        </UButton>
      </div>
    </div>
  </div>
</template>
