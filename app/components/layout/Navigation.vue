<script setup lang="ts">
defineProps<{
  currentStep: number
  totalSteps: number
  canGoNext?: boolean
}>()

const emit = defineEmits<{
  prev: []
  next: []
}>()
</script>

<template>
  <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 sm:gap-4 mt-6 sm:mt-8">
    <UButton
      variant="ghost"
      class="w-full sm:w-auto justify-center"
      :disabled="currentStep === 0"
      @click="emit('prev')"
    >
      ย้อนกลับ
    </UButton>
    <UButton
      v-if="currentStep < totalSteps - 1"
      class="w-full sm:w-auto justify-center"
      :disabled="!canGoNext"
      @click="emit('next')"
    >
      ถัดไป
    </UButton>
  </div>
</template>
