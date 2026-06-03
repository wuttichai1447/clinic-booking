<script setup lang="ts">
import type { Appointment, TimeSlot } from '~/types/booking'
import { parseApiError } from '~/utils/apiError'

const props = defineProps<{
  appointment: Appointment | null
}>()

const open = defineModel<boolean>('open', { default: false })

const emit = defineEmits<{
  success: []
}>()

const { fetchTimeSlots, rescheduleAppointment } = useBooking()

const selectedDate = ref<string | null>(null)
const selectedSlot = ref<TimeSlot | null>(null)
const slots = ref<TimeSlot[]>([])
const slotsLoading = ref(false)
const submitting = ref(false)
const error = ref<string | null>(null)

watch(open, (isOpen) => {
  if (isOpen && props.appointment) {
    selectedDate.value = props.appointment.date
    selectedSlot.value = null
    error.value = null
    void loadSlots()
  }
})

watch(selectedDate, () => {
  selectedSlot.value = null
  void loadSlots()
})

async function loadSlots() {
  const appt = props.appointment
  if (!appt?.therapistId || !selectedDate.value) {
    slots.value = []
    return
  }
  slotsLoading.value = true
  error.value = null
  try {
    slots.value = await fetchTimeSlots(appt.therapistId, selectedDate.value, appt.clinicId)
    if (!slots.value.length) {
      error.value = 'ไม่มีช่วงเวลาว่างในวันนี้ (อาจเป็นวันหยุดหรือจองเต็มแล้ว)'
    }
  } catch (e) {
    error.value = parseApiError(e, 'โหลดช่วงเวลาไม่สำเร็จ')
    slots.value = []
  } finally {
    slotsLoading.value = false
  }
}

function onSelectDate(date: string) {
  selectedDate.value = date
}

function onSelectSlot(slot: TimeSlot) {
  selectedSlot.value = slot
}

async function submit() {
  const appt = props.appointment
  if (!appt?.id || !selectedDate.value || !selectedSlot.value) {
    error.value = 'กรุณาเลือกวันที่และเวลา'
    return
  }
  submitting.value = true
  error.value = null
  try {
    await rescheduleAppointment(appt.id, selectedDate.value, selectedSlot.value.id)
    open.value = false
    emit('success')
  } catch (e) {
    error.value = parseApiError(e, 'เลื่อนนัดไม่สำเร็จ')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="open"
    title="เลื่อนนัดหมาย"
    :ui="{ content: 'max-w-lg w-full' }"
  >
    <template #body>
      <div v-if="appointment" class="space-y-5">
        <p class="text-sm text-muted">
          {{ appointment.clinicName }} · {{ appointment.serviceName }}
          <br>
          นัดเดิม: {{ appointment.date }} {{ appointment.timeSlotId?.replace('-', ':') }}
        </p>

        <DateSelector
          :selected-date="selectedDate"
          @select="onSelectDate"
        />

        <TimeSlotSelector
          v-if="selectedDate"
          :slots="slots"
          :selected-slot="selectedSlot"
          :loading="slotsLoading"
          @select="onSelectSlot"
        />

        <ErrorMessage v-if="error" :message="error" />
      </div>
    </template>

    <template #footer>
      <div class="flex justify-end gap-2 w-full">
        <UButton variant="ghost" color="neutral" @click="open = false">
          ยกเลิก
        </UButton>
        <UButton
          color="primary"
          :loading="submitting"
          :disabled="!selectedDate || !selectedSlot"
          @click="submit"
        >
          ยืนยันเลื่อนนัด
        </UButton>
      </div>
    </template>
  </UModal>
</template>
