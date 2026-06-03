<script setup lang="ts">
import { parseDate, today } from '@internationalized/date'
import type { CalendarDate } from '@internationalized/date'

const props = defineProps<{
  selectedDate: string | null
  minDate?: string
}>()

const emit = defineEmits<{
  select: [date: string]
}>()

const modelValue = computed({
  get: () => {
    if (!props.selectedDate) return undefined
    try {
      return parseDate(props.selectedDate)
    } catch {
      return undefined
    }
  },
  set: (value: CalendarDate | undefined) => {
    if (value) {
      const [y, m, d] = [value.year, value.month, value.day]
      const str = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`
      emit('select', str)
    }
  }
})

const minValue = today('Asia/Bangkok')

function formatDate(dateStr: string) {
  const d = new Date(dateStr)
  const options: Intl.DateTimeFormatOptions = { weekday: 'short', day: 'numeric', month: 'short' }
  return d.toLocaleDateString('th-TH', options)
}
</script>

<template>
  <div class="space-y-6">
    <h3 class="text-lg font-semibold">
      เลือกวันที่
    </h3>

    <UCalendar
      v-model="modelValue"
      :min-value="minValue"
      color="primary"
      :month-controls="true"
      :year-controls="true"
      weekday-format="short"
    />

    <p
      v-if="selectedDate"
      class="text-sm text-muted"
    >
      เลือกแล้ว: {{ formatDate(selectedDate) }}
    </p>
  </div>
</template>
