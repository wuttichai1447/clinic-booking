<script setup lang="ts">
const props = defineProps<{
  currentStep: number
  totalSteps: number
  steps: string[]
}>()

const progress = computed(() => ((props.currentStep + 1) / props.totalSteps) * 100)
const currentLabel = computed(() => props.steps[props.currentStep] ?? '')

const stepperItems = computed(() =>
  props.steps.map((title, index) => ({ value: index, title }))
)
</script>

<template>
  <div class="mb-6 sm:mb-8">
    <div class="xl:hidden space-y-3">
      <div class="flex items-center justify-between gap-3 text-sm">
        <span class="font-medium text-foreground whitespace-nowrap">
          ขั้นที่ {{ currentStep + 1 }}/{{ totalSteps }}
        </span>
        <span class="text-muted text-right truncate">
          {{ currentLabel }}
        </span>
      </div>
      <div
        class="h-2 rounded-full bg-muted overflow-hidden"
        role="progressbar"
        :aria-valuenow="currentStep + 1"
        :aria-valuemin="1"
        :aria-valuemax="totalSteps"
      >
        <div
          class="h-full rounded-full bg-primary transition-all duration-300 ease-out"
          :style="{ width: `${progress}%` }"
        />
      </div>
      <div class="flex justify-between gap-0.5 px-0.5">
        <span
          v-for="(_, index) in steps"
          :key="index"
          class="h-1 flex-1 rounded-full transition-colors"
          :class="index <= currentStep ? 'bg-primary/70' : 'bg-muted'"
        />
      </div>
    </div>

    <div class="hidden xl:block overflow-x-auto pb-1 -mx-1 px-1">
      <UStepper
        :model-value="currentStep"
        :items="stepperItems"
        class="min-w-[720px]"
      />
    </div>
  </div>
</template>
