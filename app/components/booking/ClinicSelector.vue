<script setup lang="ts">
import type { Clinic } from '~/types/booking'

defineProps<{
  clinics: Clinic[]
  selectedClinic: Clinic | null
  loading?: boolean
}>()

const emit = defineEmits<{
  select: [clinic: Clinic]
}>()

const clinicIcons: Record<string, string> = {
  'clinic-1': 'i-lucide-building-2',
  'clinic-2': 'i-lucide-heart-pulse',
  'clinic-3': 'i-lucide-stethoscope'
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold">
      เลือกคลินิก
    </h3>
    <div
      v-if="loading"
      class="flex justify-center p-8"
    >
      <LoadingSpinner />
    </div>
    <div
      v-else
      class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
      <button
        v-for="clinic in clinics"
        :key="clinic.id"
        type="button"
        :class="[
          'group relative overflow-hidden rounded-xl border-2 text-left transition-all duration-300',
          'hover:shadow-lg hover:shadow-primary/10 hover:-translate-y-0.5',
          'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
          selectedClinic?.id === clinic.id
            ? 'border-primary bg-primary/5 shadow-md ring-2 ring-primary/30'
            : 'border-border bg-card hover:border-primary/50'
        ]"
        @click="emit('select', clinic)"
      >
        <div class="relative aspect-[16/9] w-full overflow-hidden bg-muted">
          <img
            v-if="clinic.image"
            :src="resolveMediaUrl(clinic.image)"
            :alt="clinic.name"
            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
          >
          <div
            v-else
            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/20 to-primary/5"
          >
            <UIcon
              :name="clinicIcons[clinic.id] || 'i-lucide-building-2'"
              class="size-16 text-primary/60"
            />
          </div>
          <div
            v-if="selectedClinic?.id === clinic.id"
            class="absolute right-2 top-2 rounded-full bg-primary p-1.5"
          >
            <UIcon
              name="i-lucide-check"
              class="size-4 text-primary-foreground"
            />
          </div>
        </div>
        <div class="p-4">
          <h4 class="font-semibold text-foreground group-hover:text-primary">
            {{ clinic.name }}
          </h4>
          <p class="mt-1 flex items-start gap-1.5 text-sm text-muted">
            <UIcon
              name="i-lucide-map-pin"
              class="mt-0.5 size-4 shrink-0"
            />
            <span class="line-clamp-2">{{ clinic.address }}</span>
          </p>
          <p class="mt-2 flex items-center gap-1.5 text-sm text-muted">
            <UIcon
              name="i-lucide-phone"
              class="size-4 shrink-0"
            />
            {{ clinic.phone }}
          </p>
        </div>
      </button>
    </div>
  </div>
</template>
