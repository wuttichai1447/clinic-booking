<script setup lang="ts">
import type { Therapist } from '~/types/booking'

defineProps<{
  therapists: Therapist[]
  selectedTherapist: Therapist | null
  loading?: boolean
}>()

const emit = defineEmits<{
  select: [therapist: Therapist]
}>()

function therapistImage(therapist: Therapist) {
  return resolveMediaUrl(therapist.image)
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">
      เลือกนักกายภาพบำบัด
    </h3>
    <div
      v-if="loading"
      class="flex justify-center p-8"
    >
      <LoadingSpinner />
    </div>
    <div
      v-else
      class="grid gap-4 sm:grid-cols-2"
    >
      <button
        v-for="therapist in therapists"
        :key="therapist.id"
        type="button"
        :class="[
          'group relative flex items-center gap-4 overflow-hidden rounded-xl border-2 p-4 text-left transition-all duration-300',
          'hover:shadow-lg hover:shadow-primary/10 hover:-translate-y-0.5',
          'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
          selectedTherapist?.id === therapist.id
            ? 'border-primary bg-primary/5 shadow-md ring-2 ring-primary/30'
            : 'border-border bg-card hover:border-primary/50'
        ]"
        @click="emit('select', therapist)"
      >
        <div class="relative shrink-0">
          <UAvatar
            :src="therapistImage(therapist)"
            :alt="therapist.name"
            icon="i-lucide-user-round"
            size="3xl"
            :class="[
              '!size-24 sm:!size-28 ring-2 transition-all duration-300',
              selectedTherapist?.id === therapist.id ? 'ring-primary' : 'ring-border group-hover:ring-primary/50'
            ]"
          />
          <div
            v-if="selectedTherapist?.id === therapist.id"
            class="absolute -right-1 -top-1 rounded-full bg-primary p-1"
          >
            <UIcon
              name="i-lucide-check"
              class="size-3 text-primary-foreground"
            />
          </div>
        </div>
        <div class="min-w-0 flex-1">
          <h4 class="font-semibold text-foreground group-hover:text-primary">
            {{ therapist.name }}
          </h4>
          <UBadge
            v-if="therapist.specialty"
            :color="selectedTherapist?.id === therapist.id ? 'primary' : 'neutral'"
            variant="subtle"
            size="sm"
            class="mt-2"
          >
            {{ therapist.specialty }}
          </UBadge>
        </div>
        <UIcon
          name="i-lucide-chevron-right"
          class="size-5 shrink-0 text-muted transition-transform group-hover:translate-x-1 group-hover:text-primary"
        />
      </button>
    </div>
  </div>
</template>
