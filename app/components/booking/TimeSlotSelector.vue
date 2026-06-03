<script setup lang="ts">
import type { TimeSlot } from '~/types/booking'

const props = defineProps<{
  slots: TimeSlot[]
  selectedSlot: TimeSlot | null
  loading?: boolean
}>()

const emit = defineEmits<{
  select: [slot: TimeSlot]
}>()

const availableSlots = computed(() => props.slots.filter(s => s.available))
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">
      เลือกเวลา
    </h3>
    <div
      v-if="loading"
      class="flex justify-center p-8"
    >
      <LoadingSpinner />
    </div>
    <div
      v-else
      class="flex flex-wrap gap-2"
    >
      <UButton
        v-for="slot in availableSlots"
        :key="slot.id"
        :variant="selectedSlot?.id === slot.id ? 'solid' : 'outline'"
        :color="selectedSlot?.id === slot.id ? 'primary' : 'neutral'"
        :disabled="!slot.available"
        @click="slot.available && emit('select', slot)"
      >
        {{ slot.time }}
      </UButton>
    </div>
  </div>
</template>
